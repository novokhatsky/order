<!DOCTYPE html>
<html>

<head>
    <link href="<?=BASE_URL?>css/foundation.min.css" rel="stylesheet" type="text/css">
    <title>Формирование заявки</title>
    <meta name="viewport" content="width=device-width">
    <meta charset="utf-8">
    <style>
        table {
            border-top: 1px solid grey;
            border-collapse: collapse;
            width: 100%;
        }

        tr:nth-child(even) {
            background-color: #F0F0F0;
        }

        .num {
            width: 4rem;
        }

        input[type='number'] {
            -moz-appearance: textfield;
        }

        input::-webkit-outer-spin-button,
        input::-webkit-inner-spin-button {
            -webkit-appearance: none;
        }

        .full-width {
            max-width: 95%;
        }

        .v-mid {
            width: 200px;
            display: inline-block;
            vertical-align: middle;
        }

        .footer {
            position: fixed;
            bottom: 1rem;
            overflow: hidden;
            width: 95%;
            text-align: right;
            padding: 0.5rem;
        }
    </style>
</head>

<body>
<br>
<a name="top"></a>
<div id="app" class="row full-width">
    <div>Адрес доставки: <?=$address_name?></div>
    <div class="small-12 large-12 columns">
        <a href="<?=BASE_URL?>" class="button" v-on:click="clearLocal()">назад</a>
        <input type="button" value="заполненые" id="fill" class="button" v-on:click="switch_fill">
        <input type="button" class="button" value="отправить" v-on:click.once="saveFact">
        <input type="date" v-model="dt" class="v-mid">
        <input type="text" v-model="seek_str" v-on:click="reset" class="v-mid">
        <input type="checkbox" v-model="big_font"> крупный шрифт
    </div>

    <div class="msg">{{ message }}</div>

    <div class="small-12 medium-12 columns">
        <button
            v-for="group in groups"
            v-key="group.vid"
            v-on:click="select_group(group.vid)"
            class="button">
            {{group.vid}}
        </button>
    </div>

    <div v-if="select_weight()" class="small-12 medium-12 columns" style="text-align: center">
        <input type="radio" value="все" v-model="tort_size"> все
        <input type="radio" value="большой" v-model="tort_size"> большие
        <input type="radio" value="средний" v-model="tort_size"> средние
        <input type="radio" value="маленький" v-model="tort_size"> маленькие
    </div>

    <div class="small-12 medium-12 columns">
        <table>
            <tr>
                <th style="width: 45%">наименование</th>
                <th style="width: 14%">кол-во</th>
                <th style="width: 8%">цена</th>
                <th style="width: 5%">скидка</th>
                <th style="width: 8%">цена со скидкой</th>
                <th style="width: 8%">сумма</th>
                <th style="width: 11%"></th>
            </tr>
            <tr v-for="(tovar, index) in tovars" v-key="tovar.index">
                <td style="text-align: right;">{{tovar.name}}</td>
                <td><input type="tel" class="num" step="1" min="0" v-model="tovar.kol_vo"
                    style="width: 80%; margin: 0.5em 0.5em"></td>
                <td style="text-align: center">{{ tovar.cost }} руб.</td>
                <td style="text-align: center">{{ tovar.discont }}%</td>
                <td style="text-align: center">{{ tovar.cost_w_discont }} руб.</td>
                <td style="text-align: center">{{ tovar.cost_w_discont * tovar.kol_vo }} руб.</td>
                <td></td>
            </tr>
        </table>
        <br><br><br>
    </div>

    <div class="footer">
        <div style="float: left;"><a href="#top">Наверх</a></div>
        <div style="float: right;">
            {{active_group}} заказано {{vsego}} шт. на {{ summa }} руб.
        </div>
    </div>

    <div class="medium-12 large-12 columns">
        <p>Комментарий к заявке:</p>
        <textarea v-model="comments" rows="5"></textarea>
    </div>
</div>

<script src="../js/vue.min.js"></script>
<script src="../js/vue-resource.min.js"></script>

<script>
    Vue.use(VueResource);
    var app = new Vue({
        el: '#app',
        data: {
            server: '<?php echo "http://" . $_SERVER["HTTP_HOST"]; ?><?=BASE_URL?>',
            tovars: [],
            message: '',
            saved_tovars: [],
            only_filled: false,
            groups: [],
            active_group: '',
            dt: '<?=$dt->format('Y-m-d')?>',
            big_font: false,
            seek_str: '',
            tort_size: 'все',
            comments: '',
        },

        computed: {
            vsego: function() {
                let summa = 0;

                for (let i = 0; i < this.tovars.length; i++) {
                        summa = summa + Number(this.tovars[i].kol_vo);
                }

                return summa;
            },
            summa: function() {
                let summa = 0;

                for (let i = 0; i < this.tovars.length; i++) {
                        summa = summa + Number(this.tovars[i].kol_vo * this.tovars[i].cost_w_discont);
                }

                return summa;
            },
        },

        watch: {
            big_font: function() {
                if (this.big_font) {
                    document.body.style = "font-size: 1.5rem";
                } else {
                    document.body.style = "font-size: 1rem";
                }
            },

            seek_str: function() {
                if (this.active_group == '') {
                    return;
                }

                this.rememberTovars();
                this.select_goods();
            },

            tort_size: function() {
                this.rememberTovars();
                this.select_goods();
            },

        },

        methods: {

            select_weight: function() {
                return this.active_group == 'Торты весовые';
            },

            select_size: function() {
                if (this.active_group != 'Торты весовые' || this.tort_size == 'все') {
                    return;
                }

                const select_size = this.tort_size;

                this.tovars = this.tovars.filter(function(el) {
                    return el.name.includes(select_size);
                });
            },

            switch_fill: function () {
                this.only_filled = !this.only_filled;

                button = document.getElementById("fill");
                button.style.background = (this.only_filled) ? "#00c117" : "#2ba6cb";

                this.rememberTovars();
                console.log('rememberT');

                this.active_group = '';
                this.tovars = [];

                if (!this.only_filled) {
                    this.select_group(this.groups[0].vid);
                    return;
                }

                for (let key of this.saved_tovars.keys()) {
                    let el = this.saved_tovars.get(key);

                    if (el.kol_vo > 0) {
                        this.tovars.push({
                            name: key,
                            guid: el.guid,
                            guid_trait: el.guid_trait,
                            kol_vo: el.kol_vo,
                            cost: el.cost,
                            discont: el.discont,
                            cost_w_discont: el.cost_w_discont
                        });
                    }
                }
                this.tovars.sort((a, b) => a.name > b.name ? 1 : -1); 
            },

            reset: function () {
                this.seek_str = '';
            },

            saveInLocal: function() {
                console.log('rememberLocal');
                localStorage.setItem('goods', JSON.stringify([...this.saved_tovars]));
            },

            clearLocal: function() {
                localStorage.setItem('goods', JSON.stringify([]));
            },

            select_group: function (group) {

                if (this.only_filled) {
                    this.switch_fill();
                }

                this.rememberTovars();

                this.active_group = group;

                this.select_goods();
            },

            select_goods: function () {
                active_group = this.active_group.replace('/', '_');
                this.$http.get(this.server + "goods/" + active_group + "/" + this.seek_str).then(
                    function(otvet) {
                        this.tovars = otvet.data;
                        this.restoreTovars();
                        this.select_size();
                    },
                    function (errr) {
                        console.log(errr);
                    }
                );
            },

            rememberTovars: function() {
                for (let i = 0; i < this.tovars.length; i++) {
                    if (this.tovars[i].kol_vo > 0 || this.saved_tovars.has(this.tovars[i].name)) {
                        this.saved_tovars.set(
                            this.tovars[i].name,
                            {
                                kol_vo: this.tovars[i].kol_vo,
                                guid: this.tovars[i].guid,
                                guid_trait: this.tovars[i].guid_trait,
                                cost: this.tovars[i].cost,
                                discont: this.tovars[i].discont,
                                cost_w_discont: this.tovars[i].cost_w_discont
                            }
                        );
                    }
                }

                // запоминаем в локальном хранилище
                this.saveInLocal();
            },

            restoreTovars: function() {
                for (let i = 0; i < this.tovars.length; i++) {
                    let name = this.tovars[i].name;
                    if (this.saved_tovars.has(name) && this.saved_tovars.get(name).kol_vo > 0) {
                        this.tovars[i].kol_vo = this.saved_tovars.get(name).kol_vo;
                    }

                }
            },

            saveFact: function () {
                this.rememberTovars();
                // формируем массив для отправки
                let send_tovars = [];

                for (let key of this.saved_tovars.keys()) {

                    let el = this.saved_tovars.get(key);

                    if (el.kol_vo > 0) {
                        send_tovars.push({
                            guid: el.guid,
                            kol_vo: el.kol_vo,
                            guid_trait: el.guid_trait,
                            cost: el.cost_w_discont
                        });
                    }
                }

                // сделать: проверка на корректность заполнения
                if (send_tovars.length != 0) {
                    const data = {dt: this.dt, comments: this.comments, tovars: send_tovars};

                    this.$http.post(this.server + "save/", data).then(
                        function(otvet) {
                            // сделать проверку?
                            if (otvet.data.error == 1) {
                                this.message = otvet.data.msg;
                            } else {
                                // сделать перенаправление
                                this.clearLocal();
                                document.location.href = this.server + 'ok/' + otvet.data.msg;
                            }
                        },
                        function(errr) {
                            console.log(errr);
                        }
                    );
                }
            },

            getGroups: function () {
                this.$http.get(this.server + "groups").then(
                    function(otvet) {
                        this.groups = otvet.data;
                    },
                    function(errr) {
                        console.log(errr);
                    }
                );
            },
        },

        created: function() {
            this.saved_tovars = new Map(
                JSON.parse(localStorage.getItem('goods'))
            );

            //this.getTovars();
            this.getGroups();
        }
    })
</script>

</body>
</html>
