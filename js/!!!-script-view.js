'use strict';


//  компонент VIEW_TARGET
    function com_view_target(param) {
        return new Vue({
            el: '#' + param['name'],
            data: {
                lang: param['lang'],
                comp: param['name'],
                data: {
                    legend: {},
                    items: {},
                    items_dict: {},
                    total_all: 0,
                    total_search: 0,
                    total_items: 0,
                    search: '',
                    filter: param['filter']
                },
                sort: param['sort'],
                page: param['page'],
                page_go: '',
                page_send: param['page'],
                view: param['view'],
                search: {
                    data: ''
                },
                load: true,
                error: '',
                dict: {}
            },
            created: function() {
                this.head = param['head'];
                this.search_fields = param['search_fields'];
                this.icon = param['icon'];
                this.view_select = param['view_select'];
                this.search.field = '';
            },
            computed: {
                page_total: function() {
                    let temp = Math.ceil(this.data.total_search / this.view);
                    if (temp == 0) return 1;
                    return temp;
                },
                filter_set: function() {
                    if (Object.keys(this.data.filter).length == 0) return true;
                    let temp = [];
                    for (let i in this.data.filter) {

                        if ('id_level' in this.data.filter) if (this.data.filter['id_level'] == null) this.data.filter['id_field'] = null;
                        if ('id_field' in this.data.filter) if (this.data.filter['id_field'] == null) this.data.filter['id_class'] = null;
                        if ('id_class' in this.data.filter) if (this.data.filter['id_class'] == null) this.data.filter['id_item'] = null;
                        if ('id_item' in this.data.filter) if (this.data.filter['id_item'] == null) this.data.filter['id_sec'] = null;
                        if ('id_sec' in this.data.filter) if (this.data.filter['id_sec'] == null) this.data.filter['id_subsec'] = null;

                        if (this.data.filter[i]) {
                            if (i in this.dict) {
                                temp.push(this.data.filter[i]);
                            }
                        }
                    }
                    return temp.join();
                }
            },
            watch: {
                filter_set: function() {
                    if (Object.keys(this.data.filter).length != 0) this.getData();
                },
                view: function() {
                    if (this.page > this.page_total) {
                        if (this.page_total == 0) this.page = 1;
                        else this.page = this.page_total;
                    }
                    this.getData();
                },
            },
            mounted: function() {
                if (!this.data.total_items) this.getData();
            },
            methods: {
                resetSearch: function() { // сброс поиска
                    this.search.data = '';
                    this.getData();
                },
                changePage: function(p) { // выбор страницы
                    this.page_send = p;
                    this.getData();
                },
                getValue: function(item, i, h) { // подготовка данных для представления
                    if (item == 'undefined' || item == '') return '';
                    //  мультиязычные поля (редактируемые)
                    if (this.head[h].lang) {
                        if (item == null) {
                            for (let l in this.lang.suf) {
                                if (this.data.items[i][this.head[h].lang + l] != null) {
                                    item = this.data.items[i][this.head[h].lang + l];
                                    return '<span class="font-weight-bold text-danger">' + l.toUpperCase() + ':</span> ' + item;
                                }
                            }
                            return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>'
                        }
                        else return this.data.search ? this.highlightData(item, h) : item;
                    }
                    //  словари
                    if (this.head[h].dict) {
                        if (item == null) return '';
                        if (typeof item == 'string') item = {item};
                        if (!item) {
                            if (h == 'id_level_1' || h == 'id_level_2' || h == 'id_level_3') return '';
                            else return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>';
                        }
                        if (!Object.keys(item).length) return '';
                        item = Object.values(item);
                        if (item.length != 0) {
                            if (this.head[h].dict) {
                                for (let it in item) {
                                    if (this.data.search) item[it] = this.data.items_dict[h][item[it]];
                                    else item[it] = this.highlightData(this.data.items_dict[h][item[it]], h);
                                    if (this.head[h].edit && this.head[h].edit != 'c-select-2') {
                                        let st = 'small';
                                        if (this.head[h].chil) st = '';
                                        item[it] = '<div class="rounded border-left-secondary border bg-light ' +  st + ' font-weight-bold my-1 mr-1 px-1">' + item[it] + '</div>';
                                    }
                                }
                            }
                            item = item.join('');
                        }
                        return item;
                    }
                    return this.data.search ? this.highlightData(item, h) : item;
                },
                highlightData: function(e, h) { // подсветка результата поиска
                    if (e == null) return '';
                    if (this.data.search == '') return e;
                    if (h in this.search_fields) {
                        if (this.search.field != '' && this.search.field != h) return e;
                        return e.replaceAll(new RegExp(this.data.search, 'gi'), (match) => '<span class="bg-warning text-dark p-0 m-0">' + match + '</span>');
                    }
                    return e;
                },
                getDataForQuery: function() { // формирование параметров запроса
                    return {
                        '@COM': this.comp,
                        'PAGE': this.page_send,
                        'VIEW': this.view,
                        'SORT': JSON.stringify(this.sort),
                        'SEARCH': JSON.stringify(this.search),
                        'FILTER': JSON.stringify(this.data.filter)
                    };
                },
                getData: function() { // запрос данных
                    //  подготовка параметров
                        this.load = true;
                        $('#load-graf-1').show();
                        let param = this.getDataForQuery();
                        param['@MET'] = this.comp.replace('-', '_') + '_get_data';
                    //  запрос
                        query(param, e => this.processingQuery(e));
                },
                processingQuery: function(e, id = false) { // обработка ответа
                    //  отключение анимации
                        this.load = false;
                        $('#load-graf-1').hide();

                    //  обработка ответа
                        if ('error' in e.res) {   //  ошибка
                            if (e.res.error == 'message') {
                                if (id) this.$set(this.data.edit[id], 'error', e.res.error_message);
                                else this.error = e.res.error_message;
                            }
                            else {
                                if (id) this.$set(this.data.edit[id], 'error', this.lang.err[e.res.error] || this.lang.com[e.res.error] || this.lang.err['001']);
                                else this.error = this.lang.err[e.res.error] || this.lang.com[e.res.error] || this.lang.err['001'];
                            }
                        }
                        else {  //  ошибки нет
                            if ('items' in e.res) {
                                //  очистка ошибки
                                    if (id) this.$delete(this.data.edit[id], 'error');
                                    else this.error = '';
                                //  инициализация данных
                                    this.$set(this.data, 'items', e.res['items']);
                                    this.data.items_dict = e.res['items_dict'];
                                    this.data.legend = e.res['legend'];
                                    this.data.total_all = e.res['total_all'];
                                    this.data.total_search = e.res['total_search'];
                                    this.data.total_items = Object.keys(e.res['items']).length;
                                    this.data.search = e.res['search'];
                                    this.page = e.res['page'];
                                //  обработка словарей
                                    if ('dict' in e.res) {
                                        for (let d in e.res['dict']) {
                                            let temp = {
                                                data: Object.keys(e.res['dict'][d].data) == 0 ? {} : Object.values(e.res['dict'][d].data),
                                                total: e.res['dict'][d].total
                                            };
                                            this.$set(this.dict, d, temp); 
                                            for (let i in e.res['dict'][d].data) {
                                                if (!(d in this.data.items_dict)) this.data.items_dict[d] ={};
                                                this.data.items_dict[d][e.res['dict'][d].data[i].id] = e.res['dict'][d].data[i].text;
                                            }
                                        }
                                    }
                                //  закрытие формы
                                    if (id) this.$delete(this.data.edit, id);
                            }
                            else {  //  ошибка получения данных
                                this.error = this.lang.err[102];
                                this.$set(this.data, 'items', {});
                                this.$set(this.data, 'edit', {});
                                this.data.items_dict = {};
                                this.data.total_all = 0;
                                this.data.total_search = 0;
                                this.data.search = '';
                                this.page = 1;
                                this.dict = {};
                            }
                        }
                }
            },
            template: `
                <div class="pt-3">

                    <!-- ◉◉◉◉◉ ОШИБКА ◉◉◉◉◉ -->
                    <c-error-dynamic
                        v-if="error"
                        @close="error = false"
                        :rounded="true"
                        :level="2"
                        :error="error"
                    />

                    <!-- ◉◉◉◉◉ ФИЛЬТР ◉◉◉◉◉ -->
                    <template v-if="data.filter">
                        <div class="row">
                            <div
                                v-for="(f, fi) in data.filter"
                                v-if="head[fi].edit"
                                :class="head[fi].view"
                                class="mb-3 w-100"
                            >
                                <template v-if="head[fi]">
                                    <component
                                        v-if="dict[head[fi].data]"
                                        :is="head[fi].edit"
                                        :head="head[fi]"
                                        :model="[['data', 'filter'], head[fi].data]"
                                        :dict="dict[head[fi].data]"
                                        :prepend="head[fi].prep"
                                        :placeholder="head[fi].info"
                                    />
                                </template>
                            </div>
                        </div>
                    </template>

                    <div v-if="Object.keys(data.legend).length" class="mb-3 px-3 py-2 rounded alert-secondary">
                        <b>{{ lang.com['012'] }}</b>
                        <table class="mt-1 font-weight-bold small">
                            <tr class="text-secondary">
                                <td class="py-0 align-top"><b>-</b></td>
                                <td class="pl-3 py-0 align-top">{{ lang.com['013'] }}</td>
                            </tr>
                            <tr v-for="val in data.legend" :style="'color: #' + val.color">
                                <td class="py-0 align-top"><b>{{ val.val }}</b></td>
                                <td class="pl-3 py-0 align-top">{{ val.val_text }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex">

                        <div class="flex-grow-1">
                            <!-- ◉◉◉◉◉ ПАГИНАЦИЯ ◉◉◉◉◉ -->
                            <c-pagination
                                :page="page"
                                :page_total="page_total"
                                :total_all="data.total_all"
                                :total_search="data.total_search"
                                :top="true"
                            />
                        </div>

                        <button
                            v-if="this.data.total_items"
                            @click="alert(1)"
                            class="btn btn-sm btn-primary mb-3 ml-3"
                        >
                            <i class="fas fa-file-excel fa-fw"></i>
                            <span class="px-1 d-none d-sm-inline-block">{{ $root.lang.int['050'] }}</span>
                        </button>

                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-hover border-bottom m-0">
                            <thead class="thead-light">

                                <!-- ◉◉◉◉◉ ШАПКА ТАБЛИЦЫ ◉◉◉◉◉ -->
                                <c-table-head />

                            </thead>
                            <tbody>

                                <!-- ◉◉◉◉◉ ДАННЫЕ ЕСТЬ ◉◉◉◉◉ -->
                                <template v-if="this.data.total_items">
                                    <template v-for="(item, i) in data.items">
                                        <!-- ◉◉◉◉◉ ПРЕДСТАВЛЕНИЕ ◉◉◉◉◉ -->
                                        <tr 
                                            :key="i"
                                            class="form-height position-relative"
                                            :class="{'text-secondary': item.id_val == null}"
                                            :style="item.id_val != null ? 'color: #' + data.legend[item.id_val].color : ''"
                                        >
                                            <template
                                                v-for="(h, hi) in head"
                                                v-if="h.type != '@-filter' && !h.chil"
                                            >
                                                <td
                                                    v-if="h.data"
                                                    :class="h.view"
                                                    class="align-middle px-3"
                                                >
                                                    <div style="max-height: 20rem; overflow-y: auto;">
                                                        <div v-html="getValue(item[hi], i, hi)" class="text-break" />
                                                        <template v-if="h.parn">
                                                            <div
                                                                v-for="(c, ci) in h.parn"
                                                                class="text-break small"
                                                            >
                                                                <span v-if="head[c].name">{{ head[c].name }}:</span>
                                                                <span v-html="getValue(item[c], i, c)" class="font-weight-bold" />
                                                            </div>
                                                        </template>
                                                    </div>
                                                </td>
                                            </template>

                                        </tr>
                                    </template>
                                </template>

                                <!-- ◉◉◉◉◉ ДАННЫХ НЕТ ◉◉◉◉◉ -->
                                <template v-else>
                                    <tr>
                                        <td :colspan="Object.keys(head).length" class="px-3 py-2">
                                            {{ lang.int[160] }}
                                            <template v-if="data.search">
                                                <div class="text-danger small">{{ lang.int[161] }}</div>
                                            </template>
                                        </td>
                                    </tr>
                                </template>

                            </tbody>
                        </table>
                    </div>

                    <!-- ◉◉◉◉◉ ПАГИНАЦИЯ ◉◉◉◉◉ -->
                    <c-pagination
                        :page="page"
                        :page_total="page_total"
                        :total_all="data.total_all"
                        :total_search="data.total_search"
                    />

                </div>
            `       
        });
    }

//  компонент VIEW_VAL
    function com_view_val(param) {
        return new Vue({
            el: '#' + param['name'],
            data: {
                lang: param['lang'],
                comp: param['name'],
                data: {
                    legend: {},
                    items: {},
                    items_dict: {},
                    total_all: 0,
                    total_search: 0,
                    total_items: 0,
                    search: '',
                    filter: param['filter']
                },
                sort: param['sort'],
                page: param['page'],
                page_go: '',
                page_send: param['page'],
                view: param['view'],
                search: {
                    data: ''
                },
                load: true,
                error: '',
                dict: {},
                dict_val: {}
            },
            created: function() {
                this.head = param['head'];
                this.search_fields = param['search_fields'];
                this.icon = param['icon'];
                this.view_select = param['view_select'];
                this.search.field = '';
            },
            computed: {
                page_total: function() {
                    let temp = Math.ceil(this.data.total_all / this.view);
                    if (temp == 0) return 1;
                    return temp;
                },
                filter_set: function() {
                    if (Object.keys(this.data.filter).length == 0) return true;
                    let temp = [];
                    for (let i in this.data.filter) {

                        if ('id_level' in this.data.filter) if (this.data.filter['id_level'] == null) this.data.filter['id_field'] = null;
                        if ('id_field' in this.data.filter) if (this.data.filter['id_field'] == null) this.data.filter['id_class'] = null;
                        if ('id_class' in this.data.filter) if (this.data.filter['id_class'] == null) this.data.filter['id_item'] = null;
                        if ('id_item' in this.data.filter) if (this.data.filter['id_item'] == null) this.data.filter['id_sec'] = null;
                        if ('id_sec' in this.data.filter) if (this.data.filter['id_sec'] == null) this.data.filter['id_subsec'] = null;
                        if ('id_subsec' in this.data.filter) if (this.data.filter['id_subsec'] == null) this.data.filter['id_target'] = null;

                        if (this.data.filter[i]) {
                            if (i in this.dict) {
                                temp.push(this.data.filter[i]);
                            }
                        }
                    }
                    return temp.join();
                }
            },
            watch: {
                filter_set: function() {
                    if (Object.keys(this.data.filter).length != 0) this.getData();
                },
                view: function() {
                    if (this.page > this.page_total) {
                        if (this.page_total == 0) this.page = 1;
                        else this.page = this.page_total;
                    }
                    this.getData();
                },
            },
            mounted: function() {
                if (!this.data.total_items) this.getData();
            },
            methods: {
                resetSearch: function() { // сброс поиска
                    this.search.data = '';
                    this.getData();
                },
                changePage: function(p) { // выбор страницы
                    this.page_send = p;
                    this.getData();
                },
                getValue: function(item, i, h) { // подготовка данных для представления
                    if (item == 'undefined' || item == '') return '';
                    //  мультиязычные поля (редактируемые)
                    if (this.head[h].lang) {
                        if (item == null) {
                            for (let l in this.lang.suf) {
                                if (this.data.items[i][this.head[h].lang + l] != null) {
                                    item = this.data.items[i][this.head[h].lang + l];
                                    return '<span class="font-weight-bold text-danger">' + l.toUpperCase() + ':</span> ' + item;
                                }
                            }
                            return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>'
                        }
                        else return this.data.search ? this.highlightData(item, h) : item;
                    }
                    //  словари
                    if (this.head[h].dict) {
                        if (typeof item == 'string') item = {item};
                        if (!item) {
                            if (h == 'id_level_1' || h == 'id_level_2' || h == 'id_level_3') return '';
                            else return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>';
                        }
                        if (!Object.keys(item).length) return '';
                        item = Object.values(item);
                        if (item.length != 0) {
                            if (this.head[h].dict) {
                                for (let it in item) {
                                    if (this.data.search) item[it] = this.data.items_dict[h][item[it]];
                                    else item[it] = this.highlightData(this.data.items_dict[h][item[it]], h);
                                    if (this.head[h].edit && this.head[h].edit != 'c-select-2') {
                                        let st = 'small';
                                        if (this.head[h].chil) st = '';
                                        item[it] = '<div class="rounded border-left-secondary border bg-light ' +  st + ' font-weight-bold my-1 mr-1 px-1">' + item[it] + '</div>';
                                    }
                                }
                            }
                            item = item.join('');
                        }
                        return item;
                    }
                    return this.data.search ? this.highlightData(item, h) : item;
                },
                highlightData: function(e, h) { // подсветка результата поиска
                    if (this.data.search == '') return e;
                    if (h in this.search_fields) {
                        if (this.search.field != '' && this.search.field != h) return e;
                        return e.replaceAll(new RegExp(this.data.search, 'gi'), (match) => '<span class="bg-warning text-dark p-0 m-0">' + match + '</span>');
                    }
                    return e;
                },
                getDataForQuery: function() { // формирование параметров запроса
                    return {
                        '@COM': this.comp,
                        'PAGE': this.page_send,
                        'VIEW': this.view,
                        'SORT': JSON.stringify(this.sort),
                        'SEARCH': JSON.stringify(this.search),
                        'FILTER': JSON.stringify(this.data.filter)
                    };
                },
                getData: function() { // запрос данных
                    //  подготовка параметров
                        this.load = true;
                        let param = this.getDataForQuery();
                        param['@MET'] = this.comp.replace('-', '_') + '_get_data';
                    //  запрос
                        query(param, e => this.processingQuery(e));
                },
                processingQuery: function(e, id = false) { // обработка ответа
                    //  отключение анимации
                        if (id) this.$delete(this.data.edit[id], 'load');
                        else this.load = false;

                    //  обработка ответа
                        if ('error' in e.res) {   //  ошибка
                            if (e.res.error == 'message') {
                                if (id) this.$set(this.data.edit[id], 'error', e.res.error_message);
                                else this.error = e.res.error_message;
                            }
                            else {
                                if (id) this.$set(this.data.edit[id], 'error', this.lang.err[e.res.error] || this.lang.com[e.res.error] || this.lang.err['001']);
                                else this.error = this.lang.err[e.res.error] || this.lang.com[e.res.error] || this.lang.err['001'];
                            }
                        }
                        else {  //  ошибки нет
                            if ('items' in e.res) {
                                //  очистка ошибки
                                    if (id) this.$delete(this.data.edit[id], 'error');
                                    else this.error = '';
                                //  инициализация данных
                                    this.$set(this.data, 'items', e.res['items']);
                                    this.data.items_dict = e.res['items_dict'];
                                    this.data.legend = e.res['legend'];
                                    this.data.total_all = e.res['total_all'];
                                    this.data.total_search = e.res['total_search'];
                                    this.data.total_items = Object.keys(e.res['items']).length;
                                    this.data.search = e.res['search'];
                                    this.page = e.res['page'];
                                //  обработка словарей
                                    if ('dict' in e.res) {
                                        for (let d in e.res['dict']) {
                                            console.log(d);
                                            console.log(e.res['dict'][d]);
                                            let temp = {
                                                data: Object.keys(e.res['dict'][d].data) == 0 ? {} : Object.values(e.res['dict'][d].data),
                                                total: e.res['dict'][d].total
                                            };
                                            this.$set(this.dict, d, temp); 
                                            for (let i in e.res['dict'][d].data) {
                                                if (!(d in this.data.items_dict)) this.data.items_dict[d] ={};
                                                this.data.items_dict[d][e.res['dict'][d].data[i].id] = e.res['dict'][d].data[i].text;
                                            }
                                        }
                                    }
                                    this.dict_val = e.res.dict_val;
                                //  закрытие формы
                                    if (id) this.$delete(this.data.edit, id);
                            }
                            else {  //  ошибка получения данных
                                this.error = this.lang.err[102];
                                this.$set(this.data, 'items', {});
                                this.$set(this.data, 'edit', {});
                                this.data.items_dict = {};
                                this.data.total_all = 0;
                                this.data.total_search = 0;
                                this.data.search = '';
                                this.page = 1;
                                this.dict = {};
                            }
                        }
                }
            },
            template: `
                <div>

                    <!-- ◉◉◉◉◉ ОШИБКА ◉◉◉◉◉ -->
                    <c-error-dynamic
                        v-if="error"
                        @close="error = false"
                        :rounded="true"
                        :level="2"
                        :error="error"
                    />

                    <!-- ◉◉◉◉◉ ФИЛЬТР ◉◉◉◉◉ -->
                    <template v-if="data.filter">
                        <div class="row">
                            <div
                                v-for="(f, fi) in data.filter"
                                v-if="head[fi].edit"
                                :class="head[fi].view"
                                class="mb-3 w-100"
                            >
                                <template v-if="head[fi]">
                                    <component
                                        v-if="dict[head[fi].data]"
                                        :is="head[fi].edit"
                                        :head="head[fi]"
                                        :model="[['data', 'filter'], head[fi].data]"
                                        :dict="dict[head[fi].data]"
                                        :prepend="head[fi].prep"
                                        :placeholder="head[fi].info"
                                    />
                                </template>
                            </div>
                        </div>
                    </template>

                    <div v-if="Object.keys(data.legend).length" class="mb-3 px-3 py-2 rounded alert-secondary">
                        <b>{{ lang.com['012'] }}</b>
                        <table class="mt-1 font-weight-bold small">
                            <tr class="text-secondary">
                                <td class="py-0 pr-3 align-top"><b>-</b></td>
                                <td class="py-0 align-top">{{ lang.com['013'] }}</td>
                            </tr>
                            <tr v-for="val in data.legend" :style="'color: #' + val.color">
                                <td class="py-0 pr-3 align-top"><b>{{ val.val }}</b></td>
                                <td class="py-0 align-top">{{ val.val_text }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex">
                        <div class="flex-grow-1">
                            <!-- ◉◉◉◉◉ ПАГИНАЦИЯ ◉◉◉◉◉ -->
                            <c-pagination
                                :page="page"
                                :page_total="page_total"
                                :total_all="data.total_all"
                                :total_search="data.total_search"
                                :top_no_list="true"
                            />
                        </div>
                        <button
                            v-if="this.data.total_items"
                            @click="alert(1)"
                            class="btn btn-sm btn-primary mb-3 ml-3"
                        >
                            <i class="fas fa-file-excel fa-fw"></i>
                            <span class="px-1 d-none d-sm-inline-block">{{ $root.lang.int['050'] }}</span>
                        </button>
                    </div>

                    <div class="table-responsive pb-3">
                        <table class="table table-sm table-hover m-0">
                            <tbody>
                                <tr v-for="item in data.items">
                                    <td class="border-0 w-20 align-middle py-2 px-0">{{ item.title }}</td>
                                    <td class="border-0 align-middle py-2 px-1">
                                        <div class="d-flex small">
                                            <template v-for="val in item.data">
                                                <div 
                                                v-if="val.value != 0"
                                                    class="text-nowrap text-truncate p-1"
                                                    :class="{'text-secondary': val.id_val == '-'}"
                                                    :style="val.id_val != '-' ? 'color: #' + dict_val[val.id_val]['color'] : ''"
                                                ><b>{{ val.value }}%</b>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="border-0 align-middle w-100 px-0">
                                        <div class="d-flex align-items-center w-100">
                                            <template v-for="val in item.data">
                                                <div 
                                                    v-if="val.value != 0"
                                                    class="align-self-stretch py-1"
                                                    :class="{'bg-secondary op-4': val.id_val == '-'}"
                                                    :style="(val.id_val != '-' ? 'background: #' + dict_val[val.id_val]['color'] : '') + '; width: ' + val.value + '%'"
                                                >&nbsp;</div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- ◉◉◉◉◉ ПАГИНАЦИЯ ◉◉◉◉◉ -->
                    <c-pagination
                        :page="page"
                        :page_total="page_total"
                        :total_all="data.total_all"
                        :total_search="data.total_search"
                    />

                    <div v-show="load" class="loader rounded"></div>

                </div>
            `       
        });
    }

//  компонент VIEW_CRITERIA
    function com_view_criteria(param) {
        return new Vue({
            el: '#' + param['name'],
            data: {
                lang: param['lang'],
                comp: param['name'],
                data: {
                    legend: {},
                    items: {},
                    items_dict: {},
                    total_all: 0,
                    total_search: 0,
                    total_items: 0,
                    search: '',
                    filter: param['filter']
                },
                sort: param['sort'],
                page: param['page'],
                page_go: '',
                page_send: param['page'],
                view: param['view'],
                search: {
                    data: ''
                },
                load: true,
                error: '',
                dict: {},
                dict_val: {}
            },
            created: function() {
                this.head = param['head'];
                this.search_fields = param['search_fields'];
                this.icon = param['icon'];
                this.view_select = param['view_select'];
                this.search.field = '';
            },
            computed: {
                page_total: function() {
                    let temp = Math.ceil(this.data.total_search / this.view);
                    if (temp == 0) return 1;
                    return temp;
                },
                filter_set: function() {
                    if (Object.keys(this.data.filter).length == 0) return true;
                    let temp = [];
                    for (let i in this.data.filter) {
                        if (this.data.filter[i]) {

                            if ('id_level' in this.data.filter) if (this.data.filter['id_level'] == null) this.data.filter['id_field'] = null;
                            if ('id_field' in this.data.filter) if (this.data.filter['id_field'] == null) this.data.filter['id_class'] = null;
                            if ('id_class' in this.data.filter) if (this.data.filter['id_class'] == null) this.data.filter['id_item'] = null;
                            if ('id_item' in this.data.filter) if (this.data.filter['id_item'] == null) this.data.filter['id_sec'] = null;
                            if ('id_sec' in this.data.filter) if (this.data.filter['id_sec'] == null) this.data.filter['id_subsec'] = null;
                            if ('id_subsec' in this.data.filter) if (this.data.filter['id_subsec'] == null) this.data.filter['id_target'] = null;

                            if ('id_level_1' in this.data.filter) if (this.data.filter['id_level_1'] == null) this.data.filter['id_level_2'] = null;
                            if ('id_level_2' in this.data.filter) if (this.data.filter['id_level_2'] == null) this.data.filter['id_level_3'] = null;

                            if (i in this.dict) {
                                temp.push(this.data.filter[i]);
                            }
                        }
                    }
                    return temp.join();
                }
            },
            watch: {
                filter_set: function() {
                    if (Object.keys(this.data.filter).length != 0) this.getData();
                },
                view: function() {
                    if (this.page > this.page_total) {
                        if (this.page_total == 0) this.page = 1;
                        else this.page = this.page_total;
                    }
                    this.getData();
                },
            },
            mounted: function() {
                if (!this.data.total_items) this.getData();
            },
            methods: {
                resetSearch: function() { // сброс поиска
                    this.search.data = '';
                    this.getData();
                },
                changePage: function(p) { // выбор страницы
                    this.page_send = p;
                    this.getData();
                },
                getValue: function(item, i, h) { // подготовка данных для представления
                    if (item == 'undefined' || item == '') return '';
                    //  мультиязычные поля (редактируемые)
                    if (this.head[h].lang) {
                        if (item == null) {
                            for (let l in this.lang.suf) {
                                if (this.data.items[i][this.head[h].lang + l] != null) {
                                    item = this.data.items[i][this.head[h].lang + l];
                                    return '<span class="font-weight-bold text-danger">' + l.toUpperCase() + ':</span> ' + item;
                                }
                            }
                            return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>'
                        }
                        else return this.data.search ? this.highlightData(item, h) : item;
                    }
                    //  словари
                    if (this.head[h].dict) {
                        if (typeof item == 'string') item = {item};
                        if (!item) {
                            if (h == 'id_level_1' || h == 'id_level_2' || h == 'id_level_3') return '';
                            else return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>';
                        }
                        if (!Object.keys(item).length) return '';
                        item = Object.values(item);
                        if (item.length != 0) {
                            if (this.head[h].dict) {
                                for (let it in item) {
                                    if (this.data.search) item[it] = this.data.items_dict[h][item[it]];
                                    else item[it] = this.highlightData(this.data.items_dict[h][item[it]], h);
                                    if (this.head[h].edit && this.head[h].edit != 'c-select-2') {
                                        let st = 'small';
                                        if (this.head[h].chil) st = '';
                                        item[it] = '<div class="rounded border-left-secondary border bg-light ' +  st + ' font-weight-bold my-1 mr-1 px-1">' + item[it] + '</div>';
                                    }
                                }
                            }
                            item = item.join('');
                        }
                        return item;
                    }
                    return this.data.search ? this.highlightData(item, h) : item;
                },
                highlightData: function(e, h) { // подсветка результата поиска
                    if (this.data.search == '') return e;
                    if (h in this.search_fields) {
                        if (this.search.field != '' && this.search.field != h) return e;
                        return e.replaceAll(new RegExp(this.data.search, 'gi'), (match) => '<span class="bg-warning text-dark p-0 m-0">' + match + '</span>');
                    }
                    return e;
                },
                getDataForQuery: function() { // формирование параметров запроса
                    return {
                        '@COM': this.comp,
                        'PAGE': this.page_send,
                        'VIEW': this.view,
                        'SORT': JSON.stringify(this.sort),
                        'SEARCH': JSON.stringify(this.search),
                        'FILTER': JSON.stringify(this.data.filter)
                    };
                },
                getData: function() { // запрос данных
                    //  подготовка параметров
                        this.load = true;
                        let param = this.getDataForQuery();
                        param['@MET'] = this.comp.replace('-', '_') + '_get_data';
                    //  запрос
                        query(param, e => this.processingQuery(e));
                },
                processingQuery: function(e, id = false) { // обработка ответа
                    //  отключение анимации
                        this.load = false;

                    //  обработка ответа
                        if ('error' in e.res) {   //  ошибка
                            if (e.res.error == 'message') {
                                if (id) this.$set(this.data.edit[id], 'error', e.res.error_message);
                                else this.error = e.res.error_message;
                            }
                            else {
                                if (id) this.$set(this.data.edit[id], 'error', this.lang.err[e.res.error] || this.lang.com[e.res.error] || this.lang.err['001']);
                                else this.error = this.lang.err[e.res.error] || this.lang.com[e.res.error] || this.lang.err['001'];
                            }
                        }
                        else {  //  ошибки нет
                            if ('items' in e.res) {
                                //  очистка ошибки
                                    this.error = '';
                                //  инициализация данных
                                    this.$set(this.data, 'items', e.res['items']);
                                    this.data.items_dict = e.res['items_dict'];
                                    this.data.legend = e.res['legend'];
                                    this.data.total_all = e.res['total_all'];
                                    this.data.total_search = e.res['total_search'];
                                    this.data.total_items = Object.keys(e.res['items']).length;
                                    this.data.search = e.res['search'];
                                    this.page = e.res['page'];
                                //  обработка словарей
                                    if ('dict' in e.res) {
                                        for (let d in e.res['dict']) {
                                            let temp = {
                                                data: Object.keys(e.res['dict'][d].data) == 0 ? {} : Object.values(e.res['dict'][d].data),
                                                total: e.res['dict'][d].total
                                            };
                                            this.$set(this.dict, d, temp); 
                                            for (let i in e.res['dict'][d].data) {
                                                if (!(d in this.data.items_dict)) this.data.items_dict[d] ={};
                                                this.data.items_dict[d][e.res['dict'][d].data[i].id] = e.res['dict'][d].data[i].text;
                                            }
                                        }
                                    }
                                    this.dict_val = e.res.dict_val;
                                //  закрытие формы
                                    if (id) this.$delete(this.data.edit, id);
                            }
                            else {  //  ошибка получения данных
                                this.error = this.lang.err[102];
                                this.$set(this.data, 'items', {});
                                this.$set(this.data, 'edit', {});
                                this.data.items_dict = {};
                                this.data.total_all = 0;
                                this.data.total_search = 0;
                                this.data.search = '';
                                this.page = 1;
                                this.dict = {};
                            }
                        }
                }
            },
            template: `
                <div class="pb-3">

                    <!-- ◉◉◉◉◉ ОШИБКА ◉◉◉◉◉ -->
                    <c-error-dynamic
                        v-if="error"
                        @close="error = false"
                        :rounded="true"
                        :level="2"
                        :error="error"
                    />

                    <!-- ◉◉◉◉◉ ФИЛЬТР ◉◉◉◉◉ -->
                    <template v-if="data.filter">
                        <div class="row">
                            <div
                                v-for="(f, fi) in data.filter"
                                v-if="head[fi].edit"
                                :class="head[fi].view"
                                class="mb-3 w-100"
                            >
                                <template v-if="head[fi]">
                                    <component
                                        v-if="dict[head[fi].data]"
                                        :is="head[fi].edit"
                                        :head="head[fi]"
                                        :model="[['data', 'filter'], head[fi].data]"
                                        :dict="dict[head[fi].data]"
                                        :prepend="head[fi].prep"
                                        :placeholder="head[fi].info"
                                    />
                                </template>
                            </div>
                        </div>
                    </template>

                    <div v-if="Object.keys(data.legend).length" class="mb-3 px-3 py-2 rounded alert-secondary">
                        <b>{{ lang.com['012'] }}</b>
                        <table class="mt-1 font-weight-bold small">
                            <tr class="text-secondary">
                                <td class="py-0 pr-3 align-top"><b>-</b></td>
                                <td class="py-0 align-top">{{ lang.com['013'] }}</td>
                            </tr>
                            <tr v-for="val in data.legend" :style="'color: #' + val.color">
                                <td class="py-0 pr-3 align-top"><b>{{ val.val }}</b></td>
                                <td class="py-0 align-top">{{ val.val_text }}</td>
                            </tr>
                        </table>
                    </div>

                    <div class="d-flex">
                        <div class="flex-grow-1"></div>
                        <button
                            v-if="this.data.total_items"
                            @click="alert(1)"
                            class="btn btn-sm btn-primary mb-3 ml-3"
                        >
                            <i class="fas fa-file-excel fa-fw"></i>
                            <span class="px-1 d-none d-sm-inline-block">{{ $root.lang.int['050'] }}</span>
                        </button>
                    </div>

                    <div class="table-responsive pb-3">
                        <table class="table table-sm table-hover m-0">
                            <tbody>
                                <tr v-for="item in data.items">
                                    <td class="border-0 w-20 align-middle py-2 px-0 small" style="line-height: 1.1rem" v-html="item.title"></td>
                                    <td class="border-0 align-middle py-2 px-1">
                                        <div class="d-flex small">
                                            <template v-for="val in item.data">
                                                <div 
                                                v-if="val.value != 0"
                                                    class="text-nowrap text-truncate p-1"
                                                    :class="{'text-secondary': val.id_val == '-'}"
                                                    :style="val.id_val != '-' ? 'color: #' + dict_val[val.id_val]['color'] : ''"
                                                ><b>{{ val.value }}%</b>
                                                </div>
                                            </template>
                                        </div>
                                    </td>
                                    <td class="border-0 align-middle w-100 px-0">
                                        <div class="d-flex align-items-center w-100">
                                            <template v-for="val in item.data">
                                                <div 
                                                    v-if="val.value != 0"
                                                    class="align-self-stretch py-1"
                                                    :class="{'bg-secondary op-4': val.id_val == '-'}"
                                                    :style="(val.id_val != '-' ? 'background: #' + dict_val[val.id_val]['color'] : '') + '; width: ' + val.value + '%'"
                                                >&nbsp;</div>
                                            </template>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-show="load" class="loader rounded"></div>

                </div>
            `       
        });
    }