'use strict';


document.addEventListener('DOMContentLoaded', () => {


        $(window).resize(() => resize_tr());
        $(window).scroll(() => error_scroll());


        function resize_tr() {
            error_scroll();
            $('.form-height').each((i, e) => {
                let h = $(e).find('.error-front > div');
                if (h.length == 0) h = 0;
                else h = $(e).find('.error-front > div')[0].offsetHeight + 20;
                $(e).height(h);
            });
        }


        function error_scroll() {
            $('.error-front-level-2').each((i, e) => {
                //  параметры области просмотра
                    let scroll = window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop;
                    let heightBody = $(window).height();
                //  параметры окна сообщения
                    let mes = $(e).find('div')[0];
                    let heightMessage = $(mes).outerHeight();
                //  параметры панели
                    let topCard = $(e).offset().top;
                    let heightCard = $(e).outerHeight();
                    let topField = (topCard - scroll) < 0 ? 0 : (topCard - scroll);
                    let bottomField = heightBody < (topCard + heightCard - scroll) ? heightBody : topCard + heightCard - scroll;
                //  расчет положения
                    let top = (bottomField - topField - heightMessage) / 2 + scroll + topField;
                    if (top < topCard + 20) top = topCard + 20;
                    if (bottomField - heightMessage < 20) top = topCard + heightCard - heightMessage - 20;
                    $(mes).offset({top: top});
            });
        }


    //  компонент - модальное окно
        Vue.component('c-modal', {
            props: ['size', 'view'],
            watch: {
                view: function() {
                    $(this.$refs[this.$root.$options.el]).modal(this.view ? 'show' : 'hide');
                }
            },
            template: `
                <div :ref="this.$root.$options.el" class="modal fade" data-backdrop="static" tabindex="-1">
                    <div :class="size" class="modal-dialog shadow-lg">
                        <div class="modal-content">
                            <slot />
                        </div>
                    </div>
                </div>
            `
        });


    //  компонент - заголовок
        Vue.component('c-table-head', {
            methods: {
                changeSort: function(name) { // выбор сортировки
                    let LINK = this.$root;
                    if (name in LINK.sort) LINK.sort[name] = !LINK.sort[name];
                    else {
                        for (let i in LINK.sort) LINK.$delete(LINK.sort, i);
                        LINK.$set(LINK.sort, name, true);
                    }
                    this.$root.getData();
                },
                changeSelectAll: function() { // выбрать все данные
                    let LINK = this.$root.data;
                    LINK.select_all = !LINK.select_all;
                    LINK.select = [];
                    if (LINK.select_all) {
                        for (let i in LINK.items) {
                            LINK.select.push(LINK.items[i].id);
                        }
                    }
                },
            },
            template: `
                <tr class="align-middle">
                    <th
                        v-for="(h, hi) in $root.head"
                        v-if="h.type != '@-filter' && !h.chil"
                        :class="h.view"
                        class="text-nowrap px-3 border-0"
                        scope="col"
                    >
                        <template v-if="hi == 'id'">
                            <div v-if="$root.oper.del" class="custom-control custom-checkbox d-inline-block">
                                <input
                                    :id="$root.comp + '-select-all'"
                                    v-model="$root.data.select_all"
                                    @click="changeSelectAll()"
                                    type="checkbox"
                                    class="custom-control-input"
                                />
                                <label
                                    :for="$root.comp + '-select-all'"
                                    class="custom-control-label"
                                >{{ h.name }}</label>
                            </div>
                            <template v-else>{{ h.name }}</template>
                        </template>
                        <template v-else>{{ h.name }}</template>
                        <a
                            v-if="h.sort"
                            @click="changeSort(hi)"
                            :class="typeof $root.sort[hi] == 'undefined' ? 'text-muted' : 'text-primary'"
                            class="ml-2"
                            type="button"
                        >
                            <i v-show="$root.sort[hi] || !(hi in $root.sort)" class="fas fa-sort-amount-down-alt"></i>
                            <i v-show="!$root.sort[hi] && (hi in $root.sort)" class="fas fa-sort-amount-down"></i>
                        </a>
                    </th>
                </tr>
            `
        });


    //  компонент - меню
        Vue.component('c-table-menu', {
            template: `
                <tr class="align-middle">
                    <td class="bg-white py-0 px-2" :colspan="Object.keys($root.head).length">
                        <button
                            v-if="!$root.data.edit.N && $root.oper.add"
                            @click="$root.$set($root.data.edit, 'N', JSON.parse(JSON.stringify($root.struct)))"
                            class="btn btn-sm btn-outline-primary pl-1 my-3 mr-2"
                        >
                            <i class="fas fa-plus fa-fw mr-1"></i>{{ $root.lang.int['001'] }}
                        </button>
                        <button
                            v-if="$root.data.select.length && $root.oper.del"
                            @click="$root.deleteDataSelect()"
                            class="btn btn-sm btn-outline-danger pl-1 my-3 mr-2"
                        >
                            <i class="far fa-trash-alt fa-fw mr-1"></i>
                            {{ $root.lang.int['006'] + ' - '}}
                            <b>{{ $root.data.select.length }}</b>
                        </button>
                        <button
                            v-if="$root.oper.sync"
                            @click="$root.syncCriteria()"
                            class="btn btn-sm btn-outline-success pl-2 my-3 mr-2"
                        >
                            <i class="fas fa-project-diagram fa-fw mr-1"></i>
                            {{ $root.lang.int['010']}}
                        </button>
                    </td>
                </tr>
            `
        });


    //  компонент - пагинация
        Vue.component('c-pagination', {
            props: ['page', 'page_total',  'total_all', 'total_search', 'top', 'top_no_list'],
            computed: {
                button: function() {
                    let temp = [1];
                    let i, start, end;
                    if (4 - this.page >= 0) {
                        start = 2;
                        end = this.page + 3;
                    }
                    else {
                        temp.push('...');
                        start = this.page - 3;
                        end = this.page + 3;
                    }
                    for (i = start; i <= end; i++) {
                        if (i > this.page_total) break;
                        temp.push(i);
                    }
                    if (this.page_total >= i) {
                        temp.push('...');
                        temp.push(this.page_total);
                    }
                    let res = {
                        page: [],
                        select: false
                    };
                    for (let i in temp) {
                        if (temp[i] == '...') {
                            if (temp[i - 1] == (temp[parseInt(i) + 1] - 1)) continue;
                            else res.select = true;
                        }
                        res.page.push(temp[i]);
                    }
                    return res;
                }
            },
            template: `
                <div
                    v-if="total_all || $root.data.search"
                    :class="top ? 'flex-wrap-reverse' : 'flex-nowrap'"
                    class="d-flex"
                >
                    <!-- ◉◉◉◉◉ ПАГИНАЦИЯ ◉◉◉◉◉ -->
                    <template v-if="total_all > $root.view_select[0]">
                        <template v-if="button.page.length > 1">
                            <nav class="mr-3 mb-3 d-none d-lg-block">
                                <ul class="pagination pagination-sm m-0">
                                    <li
                                        v-for="p in button.page"
                                        :class="{ active: page == p, 'disabled border-0': p == '...' }"
                                        class="page-item"
                                    >
                                        <button @click="$root.changePage(p)" class="page-link">{{ p }}</button>
                                    </li>
                                </ul>
                            </nav>
                            <div class="input-group input-group-sm mr-3 mb-3 w-auto d-lg-none">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="far fa-sticky-note"></i></span>
                                </div>
                                <select
                                    @change="$root.changePage($root.page_send)"
                                    v-model="$root.page_send"
                                    class="custom-select w-auto"
                                >
                                    <option
                                        v-for="p in button.page"
                                        :disabled="p == '...'"
                                    >{{ p }}</option>
                                </select>
                            </div>
                            <form @submit.prevent="$root.changePage($root.page_go)" class="p-0" style="width: 7rem">
                                <div class="input-group input-group-sm mb-3" w-100>
                                    <input v-model.number="$root.page_go" type="text" class="pagination form-control" />
                                    <div class="input-group-append w-auto">
                                        <button
                                            :disabled="!$root.page_go"
                                            :class="$root.page_go ? 'btn-primary' : 'btn-secondary'"
                                            class="btn"
                                            type="submit"
                                        >
                                            <i class="fas fa-fast-forward"></i>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </template>
                    </template>
                    <div class="flex-grow-1"></div>
                    <div v-if="(total_all != 0 || $root.data.search)">
                        <!-- ◉◉◉◉◉ ПОИСК ◉◉◉◉◉ -->
                        <form v-if="top" @submit.prevent="$root.search.data ? $root.getData() : false" class="p-0">
                            <div class="input-group input-group-sm mb-3">
                                <div class="input-group-prepend">
                                    <span
                                        :class="$root.data.search ? 'text-danger' : ''" 
                                        class="input-group-text font-weight-bold"
                                    >{{ total_search }} / {{ total_all }}</span>
                                </div>
                                <div v-if="$root.data.search" class="input-group-prepend">
                                    <button
                                        @click="$root.resetSearch()"
                                        class="btn btn-danger"
                                        type="button"
                                    ><i class="fas fa-search-minus"></i></button>
                                </div>
                                <select v-model="$root.search.field" type="text" class="custom-select">
                                    <option value="">{{ $root.lang.int['030'] }}</option>
                                    <option
                                        v-for="(f, i) in $root.search_fields"
                                        :value="i"
                                    >{{ f }}</option>
                                </select>
                                <input v-model.trim="$root.search.data" type="text" class="form-control">
                                <div class="input-group-append">
                                    <button
                                        :disabled="$root.search.data == '' && $root.data.search == ''"
                                        :class="$root.search.data ? 'btn-primary' : 'btn-secondary'"
                                        class="btn"
                                        type="submit"
                                    >
                                        <i class="fas fa-search-plus"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                        <!-- ◉◉◉◉◉ ПЕРЕКЛЮЧЕНИЕ КОЛИЧЕСТВА СТРОК ВЫВОДА ◉◉◉◉◉ -->
                        <div v-else-if="!top_no_list" class="input-group input-group-sm mb-3">
                            <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fas fa-list"></i></span>
                            </div>
                            <select v-model="$root.view" class="custom-select">
                                <option v-for="(v, i) in $root.view_select" :value="v">{{ v }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            `    
        });


    //  компонента - форма редактирования
        Vue.component('c-form-tr', {
            props: ['edit_id', 'load'],
            template: `
                <tr
                    :class="edit_id == 'N' ? 'table-primary' : 'table-info'"
                    class="form-height position-relative"
                >
                    <td
                        v-for="(h, hi) in $root.head"
                        v-if="h.type != '@-filter' && !h.chil"
                        :class="h.view"
                        class="align-middle px-3"
                    >
                        <c-form-td
                            :edit_id="edit_id"
                            :h="h"
                            :hi="hi"
                            :load="load"
                        />
                        <template v-if="h.parn">
                            <template v-for="c in h.parn">
                                <c-form-td
                                    :edit_id="edit_id"
                                    :h="$root.head[c]"
                                    :hi="c"
                                    :load="load"
                                />
                            </template>
                        </template>                   
                    </td>
                    <c-error-dynamic
                        v-if="$root.data.edit[edit_id].error"
                        @close="!load ? $root.data.edit[edit_id].error = false : false"
                        :rounded="false"
                        :level="1"
                        :error="$root.data.edit[edit_id].error"
                    />
                    <div v-if="load" class="loader"></div>
                </tr>
            ` 
        });


    //  компонента - элемент формы редактирования
        Vue.component('c-form-td', {
            props: ['edit_id', 'h', 'hi', 'load'],
            template: `
                <div>
                    <template v-if="h.edit">
                        <template v-if="!h.rely || $root.data.edit[edit_id][h.rely]">
                            <component
                                :is="h.edit"
                                :lng="Object.keys($root.lang.suf)"
                                :head="h"
                                :model="[['data', 'edit', edit_id], h.data]"
                                :dict="$root.dict[h.data]"
                                :rely="$root.data.edit[edit_id][h.rely]"
                                :prepend="h.prep"
                                :placeholder="h.info"
                                :class="{'py-1': h.edit != 'c-input-lng'}"
                            />
                        </template>
                        <div v-else-if="h.rely">
                            <div class="text-danger small">{{ $root.lang.int['008'] }}</div>
                            <div class="font-weight-bold">{{ $root.head[h.rely].null }}</div>
                        </div>
                    </template>

                    <template v-else-if="hi == '@-action'">
                        <div v-if="edit_id == 'N'" class="text-primary small my-1">{{ $root.lang.int['002'] }}</div>
                        <div v-else class="text-info small my-1">{{ $root.lang.int['003'] }}</div>
                        <button
                            @click="!load ? $root.saveData(edit_id) : false"
                            :disabled="!$root.checkValid(edit_id)"
                            :class="edit_id == 'N' ? 'btn-outline-primary' : 'btn-outline-info'"
                            class="btn btn-sm border-0 p-1"
                        >
                            <i class="fas fa-check fa-fw"></i>
                        </button>
                        <button @click="!load ? $delete($root.data.edit, edit_id) : false" class="btn btn-sm btn-outline-secondary border-0 p-1">
                            <i class="fas fa-times fa-fw"></i>
                        </button>
                    </template>

                    <template v-else-if="hi == 'id'">
                        <template v-if="edit_id == 'N'">✚</template>
                        <c-col-id
                            v-else
                            :edit_id="edit_id"
                        />
                    </template>

                    <template v-else>
                        <div 
                            :class="{'text-break small': h.chil}" 
                            style="max-height: 20rem; overflow-y: auto;"
                        >
                            <span v-if="h.chil && h.name">{{ h.name }}:</span>
                            <span v-html="$root.getValue($root.data.edit[edit_id][hi], edit_id, hi)" class="font-weight-bold" />
                        </div>
                    </template>
                </div>
            `
        });


    //  компонента - элемент формы редактирования
        Vue.component('c-col-id', {
            props: ['edit_id', 'i'],
            template: `
                <div>
                    <div v-if="$root.oper.del" class="custom-control custom-checkbox">
                        <input
                            v-model="$root.data.select"
                            :value="edit_id"
                            :id="$root.comp + '-' + edit_id"
                            type="checkbox"
                            class="custom-control-input"
                        />
                        <label
                            :key="$root.comp + '-' + edit_id"
                            :for="$root.comp + '-' + edit_id"
                            v-html="$root.getValue(edit_id, i, 'id')"
                            class="custom-control-label"
                        />
                    </div>
                    <div
                        v-else
                        v-html="$root.getValue(edit_id, i, 'id')"
                    />
                </div>
            `
        });


    //  компонент - многострочное поле ввода
        Vue.component('c-textarea', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
            },
            template: `
                <div class="input-group input-group-sm was-validated">
                    <textarea
                        v-model.trim="data[model[1]]"
                        :placeholder="placeholder"
                        :required="head.need ? true : false"
                        class="form-control rounded-sm"
                        style="min-height: 6rem"
                    />
                    <div v-if="!head.need && data[model[1]]">
                        <button @click="data[model[1]] = ''" class="btn btn-sm btn-outline-danger border-0 p-1 ml-1">
                            <i class="fas fa-minus fa-fw"></i>
                        </button>
                    </div>
                </div>
            `
        });


    //  компонент - поле ввода
        Vue.component('c-input', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
            },
            template: `
                <div class="input-group input-group-sm was-validated">
                    <input
                        v-model.trim="data[model[1]]"
                        :placeholder="placeholder"
                        :required="head.need ? true : false"
                        type="text"
                        class="form-control"
                    />
                </div>
            `
        });


        Vue.component('c-input-password', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
            },
            template: `
                <div class="input-group input-group-sm was-validated">
                    <input
                        v-model.trim="data[model[1]]"
                        :placeholder="placeholder"
                        :required="head.need ? true : false"
                        type="password"
                        class="form-control"
                        maxlength="32"
                    />
                </div>
            `
        });


    //  компонент - поле ввода
        Vue.component('c-input-mail', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
            },
            computed: {
                required: function() {
                    if (!this.head.need) return true;
                    return /^[\w-\.]+@[\w-]+\.[a-z]{2,4}$/i.test(this.data[this.model[1]]);
                }
            },
            template: `
                <div class="input-group input-group-sm">
                    <input
                        v-model.trim="data[model[1]]"
                        :placeholder="placeholder"
                        :class="required ? 'is-valid' : 'is-invalid'"
                        class="form-control"
                        type="text"
                    />
                </div>
            `
        });


    //  компонент - многоязычное поле ввода
        Vue.component('c-input-lng', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
            },
            computed: {
                required: function() {
                    for (let i in this.lng) {
                        if (this.data[this.model[1] + '_' + this.lng[i]] != '' && this.data[this.model[1] + '_' + this.lng[i]] != null) return false;
                    }
                    return true;
                }
            },
            template: `
                <table class="table table-sm table-borderless was-validated m-0">
                    <tr v-for="i in lng" class="nohover">
                        <td class="p-0 py-1">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend w-100">
                                    <span class="input-group-text border-secondary border-right-0 w-100">
                                        {{ i.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </td>
                        <td class="p-0 py-1 w-100">
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend w-100"></div>
                                <input
                                    v-model.trim="data[model[1] + '_' + i]"
                                    :required="head.need ? required : false"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </td>
                    </tr>
                </table>
            `
        });


    //  компонент - селект
        Vue.component('c-select-2', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder', 'filter_set'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) this.data = this.data[this.model[0][i]];
                this.init = this.data[this.model[1]];
            },
            watch: {
                rely: function() {
                    if (this.dict.total > this.head.ajax) $(this.$el).find('select').empty();
                    this.setSelect();
                },
                dict: function() {
                    this.setSelect();
                }
            },
            computed: {
                changeData: function() {
                    let temp = [];
                    if (this.head.rely && this.head.rely != this.head.data) {
                        for (let i in this.dict.data) {
                            if (Object.values(this.dict.data[i][this.head.rely]).indexOf(this.data[this.head.rely]) >= 0) {
                                temp.push(this.dict.data[i]);
                            }
                        }
                    }
                    else temp = this.dict.data;
                    return temp;
                }
            },
            mounted: function() {
                this.setSelect();
            },
            methods: {
                setSelect: function() {
                    if (this.dict.total <= this.head.ajax) $(this.$el).find('select').empty();
                    $(this.$el).find('select').empty().select2({
                        data: this.changeData,
                        theme: 'bootstrap4',
                        placeholder: this.placeholder || this.$root.lang.int['008'],
                        minimumInputLength: parseInt(this.dict.total) > this.head.ajax ? 2 : -1,
                        minimumResultsForSearch: parseInt(Object.keys(this.changeData).length) > 10 ? '' : '-1',
                        ajax: parseInt(this.dict.total) > this.head.ajax ? {
                            url: '',
                            dataType: 'json',
                            delay: 250,
                            data: param => {
                                return {
                                    'FIND': param.term,
                                    '@API': 'yes',
                                    '@COM': this.$root.comp,
                                    '@MET': 'dict_get_dict',
                                    'FILTER': JSON.stringify(this.$root.data.filter),
                                    'DICT': this.head.data
                                };
                            },
                            processResults: (data, param) => {
                                let html = [];
                                for (let i in data.data) {
                                    if (this.head.rely) {
                                        if (Object.values(data.data[i][this.head.rely]).indexOf(this.data[this.head.rely]) >= 0) {
                                            html.push(data.data[i]);
                                        }
                                    }
                                    else html.push(data.data[i]);                          
                                }
                                return { results: html };
                            }
                        } : null
                    }).val(this.data[this.head.data]).trigger('change').on('change', () => {
                        this.data[this.model[1]] = $(this.$el).find('select').val();
                    });
                },
                remove: function() {
                    this.data[this.model[1]] = null;
                    this.setSelect();
                }
            },
            template: `
                <div class="d-flex align-items-center">
                    <div v-show="dict.total > 0 || head.null === true" class="input-group input-group-sm was-validated">
                        <div v-if="prepend" class="input-group-prepend">
                            <div class="input-group-text">{{ prepend }}</div>
                        </div>
                        <select
                            :required="head.need ? true : false"
                            class="is-invalid"
                        >
                            <option
                                v-if="init"
                                :value="init"
                            >{{ $root.data['items_dict'][head.data][init] }}</option>
                            <option v-else value="">{{ this.placeholder || this.$root.lang.int['008'] }}</option>
                        </select>
                    </div>
                    <div v-if="dict.total > 0 && !head.need && data[model[1]]" class="d-ml-1">
                        <button @click="remove()" class="btn btn-sm btn-outline-danger border-0 p-1 ml-1">
                            <i class="fas fa-minus fa-fw"></i>
                        </button>
                    </div>
                    <div v-if="dict.total == 0 && head.null !== true">
                        <div class="text-danger small">{{ $root.lang.int['009'] }}</div>
                        <div class="font-weight-bold">{{ head.null }}</div>
                    </div>
                </div>
            `
        });


    //  компонент - мультиселект
        Vue.component('c-multiselect-2', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) this.data = this.data[this.model[0][i]];
                this.init = this.data[this.model[1]];
            },
            watch: {
                rely: function() {
                    if (this.dict.total > this.head.ajax) $(this.$el).find('select').empty();
                    this.setSelect();
                },
                dict: function() {
                    this.setSelect();
                }
            },
            computed: {
                changeData: function() {
                    let temp = [];
                    if (this.head.rely) {
                        for (let i in this.dict.data) {
                            if (Object.values(this.dict.data[i][this.head.rely]).indexOf(this.data[this.head.rely]) >= 0) {
                                temp.push(this.dict.data[i]);
                            }
                        }
                    }
                    else temp = this.dict.data;
                    return temp;
                }
            },
            mounted: function() {
                this.setSelect();
            },
            methods: {
                selectAll: function() {
                    let temp = [];
                    for (let i in this.$root.dict[this.head.data].data) {
                        temp.push(this.$root.dict[this.head.data].data[i].id);
                    }
                    $(this.$el).find('select').val(temp).trigger('change');
                },
                removeAll: function() {
                    this.data[this.model[1]] = [];
                    $(this.$el).find('select').val([]).trigger('change');
                },
                setSelect: function() {
                    if (this.dict.total <= this.head.ajax) $(this.$el).find('select').empty();
                    $(this.$el).find('select').select2({
                        data: this.changeData,
                        theme: 'bootstrap4',
                        placeholder: this.placeholder || this.$root.lang.int['008'],
                        minimumInputLength: parseInt(this.dict.total) > this.head.ajax ? 2 : -1,
                        minimumResultsForSearch: parseInt(this.dict.total) > 10 ? '' : '-1',
                        ajax: parseInt(this.dict.total) > this.head.ajax ? {
                            url: '',
                            dataType: 'json',
                            delay: 250,
                            data: param => {
                                return {
                                    'FIND': param.term,
                                    '@API': 'yes',
                                    '@COM': this.$root.comp,
                                    '@MET': 'dict_get_dict',
                                    'FILTER': JSON.stringify(this.$root.data.filter),
                                    'DICT': this.head.data
                                };
                            },
                            processResults: (data, param) => {
                                let html = [];
                                for (let i in data.data) {
                                    if (this.head.rely) {
                                        if (Object.values(data.data[i][this.head.rely]).indexOf(this.data[this.head.rely]) >= 0) {
                                            html.push(data.data[i]);
                                        }
                                    }
                                    else html.push(data.data[i]);                          
                                }
                                return { results: html };
                            }
                        } : null
                    }).val(Object.values(this.data[this.head.data])).trigger('change').on('change', () => {
                        this.data[this.model[1]] = $(this.$el).find('select').val();
                    });
                }
            },
            template: `
                <div class="d-flex align-items-center">
                    <div v-show="dict.total > 0" class="input-group was-validated">
                        <select
                            :required="head.need ? true : false"
                            class="is-invalid"
                            multiple
                        >
                            <option
                                v-for="p in init"
                                :value="p"
                            >{{ $root.data['items_dict'][head.data][p] }}</option>
                        </select>
                    </div>
                    <div v-if="dict.total > 0 && dict.data.length" class="d-flex ml-1">
                        <button @click="selectAll()" class="btn btn-sm btn-outline-primary border-0 p-1 ml-1">
                            <i class="fas fa-plus fa-fw"></i>
                        </button>
                        <button @click="removeAll()" class="btn btn-sm btn-outline-danger border-0 p-1 ml-1">
                            <i class="fas fa-minus fa-fw"></i>
                        </button>
                    </div>
                    <div v-if="dict.total == 0">
                        <div class="text-danger small">{{ $root.lang.int['009'] }}</div>
                        <div class="font-weight-bold">{{ head.null }}</div>
                    </div> 
                </div>
            `
        });


    //  компонент - чекбокс
        Vue.component('c-checkbox', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
            },
            template: `
                <div class="custom-control custom-checkbox ml-1">
                    <input
                        v-model="data[model[1]]"
                        :value="data[model[1]]"
                        :id="'id-' + model[0]"
                        type="checkbox"
                        class="custom-control-input"
                    />
                    <label
                        :key="'id-' + model[0]"
                        :for="'id-' + model[0]"
                        class="custom-control-label"
                    ></label>
                </div>
            `
        });


    //  компонент - мультичекбокс
        Vue.component('c-multicheckbox', {
            props: ['lng', 'head', 'model', 'dict', 'rely', 'prepend', 'placeholder'],
            created: function() {
                this.data = this.$root;
                for (let i  in this.model[0]) {
                    this.data = this.data[this.model[0][i]];
                }
                this.$set(this.data, this.model[1], Object.values(this.data[this.model[1]]));
            },
            //watch: {
            //    dict: function() {
            //        this.setData();
            //    }
            //},
            computed: {
                list: function() {
                    let data_sel = [];
                    if (this.head.rely) {
                        for (let i in this.$root.dict[this.head.data].data) {
                            if (Object.values(this.$root.dict[this.head.data].data[i][this.head.rely]).indexOf(this.data[this.head.rely]) >= 0) {
                                data_sel.push(this.$root.dict[this.head.data].data[i]);
                            }
                            else {
                                let pos = this.data[this.model[1]].indexOf(this.$root.dict[this.head.data].data[i].id);
                                if (pos >= 0) this.data[this.model[1]].splice(pos, 1);
                            }
                        }
                    }
                    else data_sel = this.$root.dict[this.head.data].data;
                    return data_sel;
                },
            },
            methods: {
                selectAll: function() {
                    let temp = [];
                    for (let i in this.list) temp.push(this.list[i].id);
                    this.data[this.model[1]] = temp;
                },
                removeAll: function() {
                    this.data[this.model[1]] = [];
                }
            },
            template: `
                <div>
                    <template v-if="list.length > 8 || dict.total > head.ajax">
                        <component
                            :is="'c-multiselect-2'"
                            :lng="lng"
                            :head="head"
                            :model="model"
                            :dict="dict"
                            :rely="rely"
                            :prepend="prepend"
                            :placeholder="placeholder"
                        />
                    </template>
                    <template v-else> 
                        <div v-if="list.length > 0" class="d-flex align-items-center">
                            <div class="mr-2">
                                <div v-for="(l, li) in list">
                                    <div class="custom-control custom-checkbox">
                                        <input
                                            v-model="data[model[1]]"
                                            :value="l.id"
                                            :id="'id-' + model[1] + '-' + l.id"
                                            type="checkbox"
                                            class="custom-control-input"
                                        />
                                        <label
                                            :key="'id-' + model[1] + '-' + l.id"
                                            :for="'id-' + model[1] + '-' + l.id"
                                            class="custom-control-label"
                                        >{{ l.text }}</label>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex">
                                <button @click="selectAll()" class="btn btn-sm btn-outline-primary border-0 p-1 ml-1">
                                    <i class="fas fa-plus fa-fw"></i>
                                </button>
                                <button @click="removeAll()" class="btn btn-sm btn-outline-danger border-0 p-1 ml-1">
                                    <i class="fas fa-minus fa-fw"></i>
                                </button>
                            </div>
                        </div>
                        <div v-else>
                            <div class="text-danger small">{{ $root.lang.int['009'] }}</div>
                            <div class="font-weight-bold">{{ head.null }}</div>
                        </div>
                    </template>
                </div>
            `
        });


    //  компонент - вывод ошибки
        Vue.component('c-error-dynamic', {
            props: ['rounded', 'level', 'error'],
            mounted: function() {
                resize_tr();
            },
            destroyed: function() {
                resize_tr();
            },
            template: `
                <div>
                    <div :class="'error-front-level-' + level" class="error-front d-flex p-2">
                        <div class="error-message alert alert-light shadow text-center py-1 px-4">
                            <div class="d-flex align-items-center p-0">
                                <i class="fas fa-exclamation-triangle fa-2x text-danger my-1 mr-3"></i>
                                <div class="text-left">
                                <b class="text-danger">{{ $root.lang.err['002'].toUpperCase() }}</b><br />
                                <span v-html="error" />
                                </div>
                            </div>
                            <hr class="my-2 mx-n4" />
                            <button
                                @click="$emit('close')"
                                class="btn btn-sm btn-danger w-100"
                            >{{ $root.lang.err['800'] }}</button>
                        </div>
                    </div>
                    <div 
                        :class="{'rounded border': rounded, 'error-back-level-1': level == 1, 'error-back-level-2': level == 2}" 
                        class="error-back table-danger m-0"
                    />
                </div>
            `
        });


});