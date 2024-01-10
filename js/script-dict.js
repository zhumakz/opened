'use strict';


//  компонент DICT
    function com_dict(param) {
        return new Vue({
            el: '#' + param['name'],
            data: {
                lang: param['lang'],
                comp: param['name'],
                data: {
                    select_all: false,
                    select: [],
                    edit: {},
                    items: {},
                    items_dict: {},
                    total_all: 0,
                    total_search: 0,
                    total_items: 0,
                    search: '',
                    filter: param['filter'],
                    filter_done: false,
                    filter_set: ''
                },
                oper: param['oper'],
                struct: param['struct'],
                sort: param['sort'],
                page: param['page'],
                page_go: '',
                page_send: param['page'],
                view: param['view'],
                search: {
                    data: ''
                },
                load: false,
                error: '',
                dict: {},
                modal: param['modal'] || false
            },
            created: function() {
                this.head = param['head'];
                this.struct_valid = param['struct_valid'];
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
                }
            },
            watch: {
                view: function() {
                    if (this.page > this.page_total) {
                        if (this.page_total == 0) this.page = 1;
                        else this.page = this.page_total;
                    }
                    this.getData();
                },
                'data.select': function() {
                    this.data.select_all = (this.data.select.length == this.data.total_items && this.data.total_items);
                }
            },
            mounted: function() {
                if (!this.data.total_items) this.getData();
            },
            methods: {
                syncCriteria: function() {
                    //  подготовка параметров
                    this.load = true;
                    let param = this.getDataForQuery();
                    param['@MET'] = 'dict_sync_criteria';
                //  запрос
                    query(param, e => {this.load = false});
                },
                closeModal: function() {
                    $('div[ref="rate"]').modal('hide');
                },
                openRate(i) {;
                    RATE.data.filter.id_item = this.data.items[i].id_item;
                    RATE.data.filter.id_class = this.data.items[i].id_class;
                    $('div[ref="rate"]').modal('show');
                },
                resetSearch: function() { // сброс поиска
                    this.search.data = '';
                    this.getData();
                },
                filterSet: function() {
                    this.filter_done = true;
                    if (Object.keys(this.data.filter).length == 0) return true;
                    let temp = [];
                    for (let i in this.data.filter) {

                        if ('id_level_1' in this.data.filter) {
                            if (this.data.filter['id_level_1'] == null) this.data.filter['id_level_2'] = null;
                        }
                        if ('id_level_2' in this.data.filter) {
                            if (this.data.filter['id_level_2'] == null) this.data.filter['id_level_3'] = null;
                        }

                        if (this.data.filter[i]) {
                            if (i in this.dict) {
                                temp.push(this.data.filter[i]);
                                this.struct[i] = this.data.filter[i];
                            }
                        }
                        else if (this.head[i].need) this.filter_done = false;
                    }
                    if (this.filter_done && this.filter_set != temp.join()) {
                        this.$set(this.data, 'edit', {});
                        this.getData();
                    }
                    this.filter_set = temp.join();
                    return this.filter_set;
                },
                changePage: function(p) { // выбор страницы
                    this.page_send = p;
                    this.getData();
                },
                checkValid: function(i) { // проверка ввода необходимых полей
                    let v = this.data.edit[i];
                    let reg_mail = /^[\w-\.]+@[\w-]+\.[a-z]{2,4}$/i;
                    return eval(this.struct_valid) ? true : false;
                },
                getValue: function(item, i, h) { // подготовка данных для представления
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
                        if (!item) return '<span class="text-danger op-4">' + this.lang.int['022'] + '</span>';
                        if (!Object.keys(item).length) return '';
                        item = Object.values(item);
                        if (item.length != 0) {
                            if (this.head[h].dict) {
                                for (let it in item) {
                                    if (!this.data.search) item[it] = this.data.items_dict[h][item[it]] + ' <span class="op-4">(' + item[it] + ')</span>';
                                    else item[it] = this.highlightData(this.data.items_dict[h][item[it]], h) + ' <span class="op-4">(' + this.highlightData(item[it], h) + ')</span>';
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
                getClassTr: function(id) { // определение стиля строки данных
                    if (id in this.data.edit) return 'table-danger';
                    if (this.data.select.indexOf(id) >= 0) return 'table-secondary';
                    return '';
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
                        param['@MET'] = 'dict_get_data';
                    //  запрос
                        query(param, e => this.processingQuery(e));
                },
                saveData: function(id) { // сохранение данных
                    //  проверка доступа к запросу
                        if (!(id in this.data.edit)) return false;
                        if (!this.checkValid(id)) {
                            this.$set(this.data.edit[id], 'error', this.lang.err['200']);
                            return false;
                        }
                    //  подготовка параметров
                        this.$set(this.data.edit[id], 'load', true);
                        let param = this.getDataForQuery();
                        param['@MET'] = 'dict_save_data';
                        param['DATA'] = JSON.stringify(this.data.edit[id]);
                    //  запрос
                        query(param, e => this.processingQuery(e, id));
                },
                deleteData: function(id) { // удаление данных
                    //  проверка доступа к запросу
                        if (!(id in this.data.edit)) return false;
                    //  подготовка параметров
                        this.$set(this.data.edit[id], 'load', true);
                        let param = this.getDataForQuery();
                        param['@MET'] = 'dict_delete_data';
                        param['DATA'] = id;
                    //  запрос
                        query(param, e => this.processingQuery(e, id));
                },
                deleteDataSelect: function() { // удаление выделенных данных
                    //  проверка доступа к запросу
                        if (!this.data.select.length) return false;
                    //  подготовка параметров
                        this.load = true;
                        let param = this.getDataForQuery();
                        param['@MET'] = 'dict_delete_select_data';
                        param['DATA'] = JSON.stringify(this.data.select);
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
                                //  обработка ответа массового удаления
                                    if ('delete_select' in e.res) {
                                        let temp = [];
                                        let t = 0;
                                        for (let i in e.res.delete_select) {
                                            if (t++ > 4) {
                                                temp.push('...');
                                                break;
                                            }
                                            else temp.push('<b>ID ' + e.res.delete_select[i].id + '</b> - ' + this.lang.err[e.res.delete_select[i].error]);
                                        }
                                        this.error = temp.join('<br />');
                                    }
                                //  инициализация данных
                                    this.$set(this.data, 'items', e.res['items']);
                                    this.data.items_dict = e.res['items_dict'];
                                    this.data.total_all = e.res['total_all'];
                                    this.data.total_search = e.res['total_search'];
                                    this.data.total_items = Object.keys(e.res['items']).length;
                                    this.data.search = e.res['search'];
                                    this.page = e.res['page'];
                                //  обработка словарей
                                    if ('dict' in e.res) {
                                        for (let d in e.res['dict']) {
                                            let temp = {
                                                data: Object.values(e.res['dict'][d].data),
                                                total: e.res['dict'][d].total
                                            };
                                            this.$set(this.dict, d, temp); 
                                            for (let i in e.res['dict'][d].data) {
                                                if (!(d in this.data.items_dict)) this.data.items_dict[d] = {};
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

                    //  бработка массива выделенных
                        if (this.data.select.length) {
                            let temp = [];
                            for (let i in this.data.items) {
                                let index = this.data.select.indexOf(this.data.items[i].id);
                                if (index >= 0) temp.push(this.data.items[i].id);
                            }
                            this.data.select = temp;
                        }

                    //  обработка массива редактируемых
                        if (Object.keys(this.data.edit).length) {
                            let temp = {};
                            for (let i in this.data.items) {
                                let id = this.data.items[i].id;
                                if (id in this.data.edit) temp[id] = this.data.edit[id];
                            }
                            if ('N' in this.data.edit) temp.N = this.data.edit.N;
                            this.data.edit = temp;
                        }
                }
            },
            template: `
                <div class="card shadow position-relative">

                    <!-- ◉◉◉◉◉ ЗАГОЛОВОК ◉◉◉◉◉ -->
                    <div class="card-header d-flex align-items-center px-3">
                        <i :class="icon" class="fas fa-lg mr-3"></i>
                        <template v-if="modal">
                            <h6 v-if="data['items_dict']['id_item']" class="flex-grow-1 m-0 font-weight-bold">
                                {{ data['items_dict']['id_item'][data.filter['id_item']] }}
                            </h6>
                            <button @click="closeModal()" class="btn btn-sm btn-outline-primary border-0">
                                <i class="fas fa-times"></i>
                            </button>
                        </template>
                        <template v-else>
                            <h6 class="flex-grow-1 m-0 font-weight-bold">
                                {{ lang.com['001'].toUpperCase() }}
                            </h6>
                            <button @click="getData()" class="btn btn-sm btn-outline-primary border-0">
                                <i :class="{'fa-spin': load}" class="fas fa-sync-alt"></i>
                            </button>
                        </template>
                    </div>

                    <div class="card-body pb-2">

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
                            <div class="row" :key="filterSet()">
                                <div v-for="(f, fi) in data.filter" :class="head[fi].view" class="mb-4 w-100">
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

                        <!-- ◉◉◉◉◉ ПАГИНАЦИЯ ◉◉◉◉◉ -->
                        <c-pagination
                            :page="page"
                            :page_total="page_total"
                            :total_all="data.total_all"
                            :total_search="data.total_search"
                            :top="true"
                        />

                        <div v-if="filter_done" class="table-responsive mb-3">
                            <table class="table table-sm table-hover border-bottom m-0">
                                <thead class="thead-light">

                                    <!-- ◉◉◉◉◉ ШАПКА ТАБЛИЦЫ ◉◉◉◉◉ -->
                                    <c-table-head />

                                    <!-- ◉◉◉◉◉ ФОРМА ДОБАВЛЕНИЯ ◉◉◉◉◉ -->
                                    <c-form-tr
                                        v-if="data.edit.N"
                                        :edit_id="'N'"
                                        :load="data.edit.N.load"
                                    />

                                    <!-- ◉◉◉◉◉ МЕНЮ ТАБЛИЦЫ ◉◉◉◉◉ -->
                                    <c-table-menu v-if="this.oper.add || (this.data.select.length && this.oper.del)" />

                                </thead>
                                <tbody>

                                    <!-- ◉◉◉◉◉ ДАННЫЕ ЕСТЬ ◉◉◉◉◉ -->
                                    <template v-if="this.data.total_items">
                                        <template v-for="(item, i) in data.items">

                                            <!-- ◉◉◉◉◉ ФОРМА РЕДАКТИРОВАНИЯ ◉◉◉◉◉ -->
                                            <c-form-tr
                                                v-if="data.edit[item.id] && data.edit[item.id].id"
                                                :key="i"
                                                :edit_id="item.id"
                                                :load="data.edit[item.id].load"
                                            />

                                            <!-- ◉◉◉◉◉ ПРЕДСТАВЛЕНИЕ / ФОРМА УДАЛЕНИЯ ◉◉◉◉◉ -->
                                            <tr 
                                                v-else
                                                :key="i"
                                                :class="getClassTr(item.id)"
                                                class="form-height position-relative"
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

                                                        <!-- ◉◉◉◉◉ ID ◉◉◉◉◉ -->
                                                        <c-col-id
                                                            v-if="hi == 'id'"
                                                            :edit_id="item.id"
                                                            :i="i"
                                                        />

                                                        <c-select-2-upd
                                                            v-else-if="head[hi].edit == 'c-select-2-upd'"
                                                            :lng="Object.keys($root.lang.suf)"
                                                            :head="head"
                                                            :model="[['data', 'items', i], head[hi].data]"
                                                            :dict="dict[head[hi].data]"
                                                            :rely="data.items[i][head[hi].rely]"
                                                            :prepend="head[hi].prep"
                                                            :placeholder="head[hi].info"
                                                        />
                                                        <template v-else>
                                                            <div v-if="h.edit == 'c-checkbox'" class="font-weight-bold text-center">
                                                                <span v-if="item[hi]" class="text-success">{{ lang.int['020'] }}</span>
                                                                <span v-else class="text-danger">{{ lang.int['021'] }}</span>
                                                            </div>
                                                            <template v-else>
                                                                <div style="max-height: 20rem; overflow-y: auto;">
                                                                    <div v-html="getValue(item[hi], i, hi)" class="text-break" />
                                                                    <template v-if="h.parn">
                                                                        <div
                                                                            v-for="(c, ci) in h.parn"
                                                                            :class="head[c].view"
                                                                            class="text-break small"
                                                                        >
                                                                            <span v-if="head[c].name">{{ head[c].name }}:</span>
                                                                            <span v-html="getValue(item[c], i, c)" class="font-weight-bold" />
                                                                        </div>
                                                                    </template>
                                                                </div>
                                                            </template>
                                                        </template>
                                                    </td>

                                                    <!-- ◉◉◉◉◉ ПОЛЕ КНОПОК ◉◉◉◉◉ -->
                                                    <td v-else-if="hi == '@-action'" class="align-middle text-center px-3">

                                                        <!-- ◉◉◉◉◉ КНОПКИ ФОРМЫ УДАЛЕНИЯ ◉◉◉◉◉ -->
                                                        <template v-if="data.edit[item.id]">
                                                            <div class="text-danger small my-1">{{ lang.int['004'] }}</div>
                                                            <button 
                                                                :key="'yes-del-' + i"
                                                                @click="!data.edit[item.id].load ? deleteData(item.id) : false"
                                                                class="btn btn-sm btn-outline-danger border-0 p-1"
                                                            ><i class="fas fa-check fa-fw"></i></button>
                                                            <button
                                                                :key="'no-del-' + i"
                                                                @click="!data.edit[item.id].load ? $delete(data.edit, item.id) : false" 
                                                                class="btn btn-sm btn-outline-secondary border-0 p-1"
                                                            ><i class="fas fa-times fa-fw"></i></button>
                                                        </template>

                                                        <!-- ◉◉◉◉◉ КНОПКИ ПРЕДСТАВЛЕНИЯ ◉◉◉◉◉ -->
                                                        <template v-else>
                                                            <button
                                                                v-if="oper.edit"
                                                                :key="'edit-' + i"
                                                                @click="$set(data.edit, item.id, JSON.parse(JSON.stringify(item)))" 
                                                                class="btn btn-sm btn-outline-info border-0 p-1"
                                                            ><i class="far fa-edit fa-fw"></i></button>
                                                            <button
                                                                v-if="oper.del"
                                                                :key="'del-' + i"
                                                                @click="$set(data.edit, item.id, {})"
                                                                class="btn btn-sm btn-outline-danger border-0 p-1"
                                                            ><i class="far fa-trash-alt fa-fw"></i></button>
                                                            <a
                                                                v-if="oper.excel"
                                                                :key="'excel-' + i"
                                                                :href="'/excel.php?type=rate&id=' + item.id_item + '&class=' + item.id_class"
                                                                class="btn btn-sm btn-outline-success border-0 p-1"
                                                                target="_blank"
                                                            ><i class="far fa-file-excel fa-fw"></i></a>
                                                            <button
                                                                v-if="oper.rate"
                                                                :key="'rate-' + i"
                                                                @click="openRate(i)"
                                                                class="btn btn-sm btn-outline-primary border-0 p-1"
                                                            ><i class="fas fa-flag-checkered fa-fw"></i></button>
                                                        </template>

                                                    </td>

                                                </template>

                                                <!-- ◉◉◉◉◉ ОШИБКА / АНИМАЦИЯ ЗАГРУЗКИ ◉◉◉◉◉ -->
                                                <template v-if="data.edit[item.id]">
                                                    <div v-if="data.edit[item.id].load" class="loader"></div>
                                                    <c-error-dynamic
                                                        v-if="data.edit[item.id].error"
                                                        @close="!load ? $root.data.edit[item.id].error = false : false"
                                                        :rounded="false"
                                                        :level="1"
                                                        :error="data.edit[item.id].error"
                                                    />
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

                    <div v-show="load" class="loader"></div>

                </div>
            `       
        });
    }