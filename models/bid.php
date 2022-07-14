<?php

namespace order\models;

Class Bid
{
    private $db;


    public function __construct($db)
    {
        $this->db = $db;
    }


    function getToDate($id_factura)
    {
        return $this->db->getValue(
            'select dt from factura where id_factura = :id_factura',
            ['id_factura' => $id_factura]
        );
    }


    function getCreateDate($id_factura)
    {
        return $this->db->getValue(
            'select dt_create from factura where id_factura = :id_factura',
            ['id_factura' => $id_factura]
        );
    }


    function getList($guid_client)
    {
        return $this->db->getList('
            select
                id_factura, dt_create, dt, pa.name as name
            from
                factura as f
                join production.addresses as pa on f.guid_storage = pa.guid
            where
                pa.guid_client = :guid_client
                and
                f.type_bid = 1
            order by
                f.type_bid = 1
            limit
                50
            ',
            ['guid_client' => $guid_client]
        );
    }


    function getBodyFact($id_factura)
    {
        return $this->db->getList('
            select
                if(
                    guid_trait is null,
                    n.name,
                    concat(n.name, ", ", (select name from production.trait as pp where pp.guid = guid_trait))
                ) as name,
                kol_vo
            from
                p_body
                join production.nomenclature as n on guid_goods = n.guid
            where
                id_factura = :id_factura
            order by
                n.name
            ',
            ['id_factura' => $id_factura]
        );
    }


    function save($guid_storage, $bid)
    {
        $dt = $bid['dt'];
        $comments = $bid['comments'];
        $data = $bid['tovars'];

        $this->db->beginTransaction();

        $query = 'insert into factura (guid_storage, dt, type_bid, comments) values (:storage, :dt, 1, :comments)';

        $id_factura = $this->db->insertData($query, [
            'storage' => $guid_storage,
            'dt' => $dt,
            'comments' => $comments,
        ]);

        if (!$id_factura) {
            $this->db->rollBack();
            return -1;
        }

        $stmt = $this->db->prepare('
            insert into p_body
                (id_factura, guid_goods, guid_trait, kol_vo, cost)
            values
                (:id, :guid, :guid_trait, :kol_vo, :cost)
        ');

        foreach ($data as $row) {
            if (!$stmt->execute([
                                    'id'         => $id_factura,
                                    'guid'       => $row['guid'],
                                    'kol_vo'     => $row['kol_vo'],
                                    'guid_trait' => $row['guid_trait'],
                                    'cost'       => $row['cost'],
                                ])) {
                $this->db->rollBack();
                return -1;
            }
        }

        $this->db->commit();

        // тестовый магазин, его в 1С не выгружаем
        $unload1C = $guid_storage !== '123';

        // заявку с номеров $id_fact нужно выгрузить в web
        $this->unloadInvoice([['id_factura' => $id_factura]], $unload1C);

        return true;
    }


    public function unloadFactInterval($dt_st, $dt_en, $uid_author)
    {
        if ($uid_author === '') {
            $stmt = Db::mysqli()->prepare('
                select id_factura
                from factura
                where dt between ? and ?
                order by id_factura');
            $stmt->bind_param('ss', $dt_st, $dt_en);
        } else {
            $stmt = Db::mysqli()->prepare('
                select id_factura
                from factura join users on using (id_user)
                where dt between ? and ? and uid = ?
                order by id_factura');
            $stmt->bind_param('sss', $dt_st, $dt_en, $uid_author);
        }

        return self::unloadData(Db::getList($stmt));
    }


    /*
        выгрузка факутур в 1С
        $invoices массив фактур
        $unloadTo1C - признак выгрузки только в файл
    */
    public function unloadInvoice($invoices, $unloadTo1C = true)
    {
        // массив фактур выгружаем в массив
        $data = $this->invoiceInArray($invoices);

        // выгружаем массив в файл
        $this->unloadToFile($data);

        // отправка в 1С, если указан признак выгрузка_в_1C
        $status = $unloadTo1C ? $this->statusUnload1C($data) : 200;

        // сохранение статуса выгрузки
        $this->updateStatus($invoices, $status);
    }


    /*
        данные из фактуры заносим в массив
    */
    private function invoiceInArray($invoices)
    {
        $data = [];

        foreach ($invoices as $invoice) {
            $data[] = $this->facturaInArray($invoice['id_factura']); 
        }

        return $data;
    }


    private function facturaInArray($id_factura)
    {
        $query = '
            select
                date_format(dt, "%d.%m.%Y 00:00:00") as dt_wish,
                date_format(dt_create, "%d.%m.%Y %H:%i:00") as dt_doc,
                guid_storage,
                comments
            from
                factura
            where
                id_factura = :id_factura
        ';

        $factura = $this->db->getRow($query, ['id_factura' => $id_factura]);

        $query = '
            select
                guid_goods, guid_trait, kol_vo, cost, cast(kol_vo * cost as decimal(10,2)) as summa
            from
                p_body
            where
                id_factura = :id_factura
            order by
                guid_goods
        ';

        $goods = $this->db->getList($query, ['id_factura' => $id_factura]);

        $body = [];
        $summa = 0;
        foreach ($goods as $row) {
            $body[] = [
                'Товар'          => $row['guid_goods'],
                'Упаковка'       => '',
                'Характеристика' => ($row['guid_trait']) ? $row['guid_trait'] : '',
                'Цена'           => $row['cost'],
                'Количество'     => $row['kol_vo'],
                'Сумма'          => $row['summa'],
            ];

            $summa += $row['summa'];
        }

        $data = [
            'Номер'          => $id_factura,
            'ДатаДокумента'  => $factura['dt_doc'],
            'ДатаОтгрузки'   => $factura['dt_wish'],
            'Клиент'         => $factura['guid_storage'],
            'СуммаДокумента' => (string)$summa,
            'Комментарий'    => $factura['comments'],
            'ТабличнаяЧасть' => $body
        ];

        return $data;
    }


    /*
        обновляем статус фактур
    */
    private function updateStatus($invoices, $status)
    {
        foreach ($invoices as $invoice) {

            $this->db->updateData(
                'update factura set status = :status where id_factura = :id_factura',
                [
                    'status'     => $status,
                    'id_factura' => $invoice['id_factura'],
                ]
            );

            if ((int)$status !== 200) {
                error_log('send zakaz ' . $invoice['id_factura'] . ' JSON code: ' . $status);
            }
        }
    }


    public function statusUnload1C($data)
    {
        $data_string = json_encode($data, JSON_UNESCAPED_UNICODE);

        $curl = curl_init('http://192.168.30.6/erp/hs/GetExch/zakazklienta_json');
        //$curl = curl_init('http://corp.antonovdvor.ru:38080/mvc/savestorage');
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
        //curl_setopt($curl, CURLOPT_POST, 1);

        // wait sec to connect
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        // max time in seconds to allow curl execute
        curl_setopt($curl, CURLOPT_TIMEOUT, 40);

        curl_setopt($curl, CURLOPT_USERPWD, "WebAgent:Crazy123");
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
        // Принимаем в виде массива. (false - в виде объекта)
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
           'Content-Type: application/json',
           'Content-Length: ' . strlen($data_string))
        );
        $result = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        return $http_code;
    }


    public function unloadToFile($data, $prefix = 'order_')
    {
        if (is_array($data)) {
            $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        }

        $filename = $prefix . bin2hex(random_bytes(6));
        $fullpath = "/home/sasha/smb/www/${filename}.txt";

        if ($handle = fopen($fullpath, 'w')) {
            fwrite($handle, $data);
            fclose($handle);
        }
    }
}

