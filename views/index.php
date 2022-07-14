<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Заявка на фабрику</title>
    <link href="<?=BASE_URL?>css/foundation.min.css" rel="stylesheet" type="text/css">
    <meta name="viewport" content="width=device-width">
</head>
<body>
<br>
<div id="app">
    <div class="row">
        <div class="large-4 columns">
            <h3><?=$user_name?></h3>
        </div>
    </div>

    <div class="row">
        <div class="large-8 columns">Адрес доставки:</div>
        <div class="large-8 columns">
            <select v-model="guid" id="address">
                <?php
                    foreach ($addresses as $address) {
                        echo '<option value="' . $address['guid'] . '">';
                        echo $address['name'];
                        echo '</option>';
                    }
                ?>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="large-4 columns">
            <a v-bind:href="'new/' + guid" class="button expanded">Новая заявка</a>
        </div>
    </div>

    <div class="row">
        <div class="large-4 columns">
            <a href="journal" class="button expand expanded">Журнал заявок</a>
        </div>
    </div>

    <div class="row">
        <div class="large-4 columns">
            <a href="logout" class="button expanded">Выход</a>
        </div>
    </div>
</div>

<script src="../js/vue.min.js"></script>
<script src="../js/vue-resource.min.js"></script>

<script>
    Vue.use(VueResource);
    var app = new Vue({
        el: '#app',
        data: {
            guid: '',
        },

        computed: {
        },

        watch: {
        },

        methods: {

        },

        created: function() {
            this.$nextTick(() => {
                const el = document.getElementById('address')
                el.selectedIndex = 0;
                this.guid = el.value;
            });
        }
    })
</script>
</body>
</html>
