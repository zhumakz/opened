<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['admin', 'editor'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'dict-discipline-subsection';
        $com_c['icon'] = 'fa-layer-group';
    //  COM: доступные операции
        $com_c['oper'] = [
            'add'   => true,
            'edit'  => true,
            'del'   => true
        ];
    //  COM: локализации
        $com_c['lang'] = [
            'int'   => $GLOBALS['@']['LANG']['DATA']['g-interfase'],
            'err'   => $GLOBALS['@']['LANG']['DATA']['g-error'],
            'suf'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['title'],
            'cur'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'],
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-discipline-subsection']
        ];
    //  COM: описание полей представления
        $com_c['head'] = [
            'id' => [
                'name' => 'ID',
                'view' => 'font-weight-bold',
                'sort' => true,
                'data' => 'id'
            ],
            'name_' . $com_c['lang']['cur'] => [
                'name' => $GLOBALS['@']['LANG']['DATA']['g-interfase']['100'],
                'view' => 'w-50',
                'sort' => true,
                'edit' => 'c-input-lng',
                'data' => 'name',
                'lang' => 'name_',
                'need' => true,
                'uniq' => true
            ],
            'id_sec' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-discipline-subsection']['002'],
                'null' => $GLOBALS['@']['LANG']['DATA']['c-discipline-subsection']['002'],
                'view' => 'w-50',
                'edit' => 'c-select-2',
                'data' => 'id_sec',
                'dict' => true,
                'need' => true,
                'uniq' => true,
            ],
            'id_item' => [
                'null' => $GLOBALS['@']['LANG']['DATA']['c-discipline-section']['100'],
                'type' => '@-filter',
                'edit' => 'c-select-2',
                'prep' => $GLOBALS['@']['LANG']['DATA']['c-discipline-subsection']['003'],
                'info' => $GLOBALS['@']['LANG']['DATA']['c-discipline-subsection']['004'],
                'data' => 'id_item',
                'dict' => true,
                'need' => true,
                'uniq' => true
            ],
            '@-action' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['g-interfase']['150'],
                'view' => 'text-center',
                'oper' => ['edit', 'del']
            ]
        ];
    //  COM: фильтр
        $com_c['filter'] = [
            'id_item' => null
        ];
    //  COM: структура данных
        $com_c['struct'] = [
            'id'        => '',
            'name_ru'   => '',
            'name_en'   => '',
            'name_kz'   => '',
            'id_sec'    => '',
            'id_item'   => ''
        ];
    //  COM: источники данных
        $com_s['base'] = "`dict_discipline_subsection`";
        $com_s['dict'] = [
            'id_sec' => [
                'field' => 'name_',
                'table_data' => "`dict_discipline_section`",
                'link' => [
                    'Lb' => "`id_sec`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false,
                'filter' => [
                    'id_item'
                ]
            ],
            'id_item' => [
                'field' => 'name_',
                'table_data' => "`dict_discipline_item`",
                'link' => [
                    'Lb' => "`id_item`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
            ]
        ];
    //  COM: проверка обязательных данным
        $com_c['struct_valid'] = '(v[\"name_en\"] || v[\"name_kz\"] || v[\"name_ru\"]) && v[\"id_sec\"] && v[\"id_item\"]';
        $com_s['struct_valid_php'] = '($param["DATA"]["name_en"] || $param["DATA"]["name_kz"] || $param["DATA"]["name_ru"]) && $param["DATA"]["id_sec"] && $param["DATA"]["id_item"]';
    //  COM: сортировка по умолчанию 
        $com_c['sort'] = ['id' => false];
    //  COM: параметры поиска - поля
        $com_c['search_fields'] = [
            'id' => 'ID',
            'name_' . $com_c['lang']['cur']  => $GLOBALS['@']['LANG']['DATA']['g-interfase']['100'],
            'id_sec' => $GLOBALS['@']['LANG']['DATA']['c-discipline-subsection']['002']
        ];
    //  COM: параметры страницы просмотра
        $com_c['page'] = 1;
        $com_c['view'] = 10;
        $com_c['view_select'] = [10,20,50,100];

    //  подключение компоненты
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/dict/dict-@engine.php';

?>

<script>
    'use strict';
    $(() => com_dict(JSON.parse('<?= json_encode($com_c, JSON_HEX_TAG | JSON_FORCE_OBJECT) ?>')));
    $(document.currentScript).remove();
</script>
<div id="<?= $com_c['name'] ?>"></div>