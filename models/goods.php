<?php

namespace order\models;

class Goods
{
    private $db;


    public function __construct($db)
    {
        $this->db = $db;
    }


    function list($guid_client, $view_nomenclature, $seek = '')
    {
        $group = str_replace('_', '/', $view_nomenclature);

        $seek = '%' . $seek . '%';

        $query = '
            select
                n.guid as guid,
                t.guid as guid_trait,
                concat(n.name, if(t.name is null, "", concat(", ", t.name, ", шт."))) as name,
                "" as kol_vo,
                d.amount as discont,
                if(t.guid is null,
                     (select cost
                        from production.price where guid_nomenclature = n.guid),
                     (select cost
                        from production.price where guid_nomenclature = n.guid and guid_trait = t.guid)
                ) as cost,
                if(t.guid is null,
                     (select round(cost*(100 - d.amount)/100)
                        from production.price where guid_nomenclature = n.guid),
                     (select round(cost*(100 - d.amount)/100)
                        from production.price where guid_nomenclature = n.guid and guid_trait = t.guid)
                ) as cost_w_discont
            from
                production.discounts as d
                join production.nomenclature as n on d.view_nomenclature = n.vid
                left join production.trait as t on (n.guid = guid_nomenclature and t.metka_delete = 0)
            where
                d.guid_client = :guid_client
                and d.view_nomenclature = :view_nomenclature
                and archive = 0
                and n.name like :seek
            having
                cost is not null
                and
                cost != 0
            order by
                n.name, t.sort
        ';

        return $this->db->getList($query,
            [
                'guid_client' => $guid_client,
                'view_nomenclature' => $view_nomenclature,
                'seek' => $seek,
            ]
        );
    }


    public function groups($guid_client)
    {
        $query = '
            select
                view_nomenclature as vid
            from
                production.discounts
            where
                guid_client = :guid_client
            order by
                view_nomenclature
        ';

        return $this->db->getList($query, ['guid_client' => $guid_client]);
    }
}
