<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['admin', 'editor'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'dict-discipline-item';
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
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']
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
                'view' => 'w-40',
                'sort' => true,
                'edit' => 'c-input-lng',
                'data' => 'name',
                'lang' => 'name_',
                'need' => true,
                'uniq' => true
            ],
            'id_field' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']['002'],
                'null' => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']['002'],
                'view' => 'w-30',
                'edit' => 'c-select-2',
                'data' => 'id_field',
                'dict' => true,
                'need' => true
            ],
            'id_class' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']['003'],
                'null' => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']['100'],
                'view' => 'w-30',
                'edit' => 'c-multicheckbox',
                'data' => 'id_class',
                'dict' => true,
                'need' => true,
                'rely' => 'id_field'
            ],
            '@-action' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['g-interfase']['150'],
                'view' => 'text-center',
                'oper' => ['edit', 'del']
            ]
        ];
    //  COM: фильтр
        $com_c['filter'] = [];
    //  COM: структура данных
        $com_c['struct'] = [
            'id'        => '',
            'name_ru'   => '',
            'name_en'   => '',
            'name_kz'   => '',
            'id_field' => '',
            'id_class' => []
        ];
    //  COM: источники данных
        $com_s['base'] = "`dict_discipline_item`";
        $com_s['dict'] = [
            'id_field' => [
                'field' => 'name_',
                'table_data' => "`dict_education_field`",
                'table_link' => "`rate_item`",
                'link' => [
                    'Ta' => "`id`",
                    'La' => "`id_item`",
                    'Lb' => "`id_field`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
            ],
            'id_class' => [
                'field' => 'name_',
                'table_data' => "`dict_education_class`",
                'table_link' => "`rate_item`",
                'link' => [
                    'Ta' => "`id`",
                    'La' => "`id_item`",
                    'Lb' => "`id_class`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => [
                    'select' => [
                        'field' => "`dict_link_field_level`.`id_field` AS 'id_field'",
                        'join' => [
                            "LEFT JOIN `dict_link_class_level` ON `dict_education_class`.`id` = `dict_link_class_level`.`id_class`",
                            "LEFT JOIN `dict_education_level` ON `dict_link_class_level`.`id_level` = `dict_education_level`.`id`",
                            "LEFT JOIN `dict_link_field_level` ON `dict_education_level`.`id` = `dict_link_field_level`.`id_level`"
                        ]
                    ]
                ]
            ]
        ];
    //  COM: проверка обязательных данным
        $com_c['struct_valid'] = '(v[\"name_en\"] || v[\"name_kz\"] || v[\"name_ru\"]) && v[\"id_field\"] && Object.keys(v[\"id_class\"]).length';
        $com_s['struct_valid_php'] = '($param["DATA"]["name_en"] || $param["DATA"]["name_kz"] || $param["DATA"]["name_ru"]) && $param["DATA"]["id_field"] && count($param["DATA"]["id_class"])';
    //  COM: сортировка по умолчанию
        $com_c['sort'] = ['id' => false];
    //  COM: параметры поиска - поля
        $com_c['search_fields'] = [
            'id' => 'ID',
            'name_' . $com_c['lang']['cur'] => $GLOBALS['@']['LANG']['DATA']['g-interfase']['100'],
            'id_field' => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']['002'],
            'id_class' => $GLOBALS['@']['LANG']['DATA']['c-discipline-item']['003']
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