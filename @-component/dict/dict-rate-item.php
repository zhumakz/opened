<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['admin', 'editor', 'expert'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'dict-rate-item';
        $com_c['icon'] = 'fa-th';
    //  COM: доступные операции
        $com_c['oper'] = [
            'edit'   => true,
            'excel'  => true,
            'rate'   => true
        ];
    //  COM: локализации
        $com_c['lang'] = [
            'int'   => $GLOBALS['@']['LANG']['DATA']['g-interfase'],
            'err'   => $GLOBALS['@']['LANG']['DATA']['g-error'],
            'suf'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['title'],
            'cur'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'],
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-dict-rate-item']
        ];
    //  COM: описание полей представления
        $com_c['head'] = [
            'id_item' => [
                'parn' => ['id_class'],
                'name' => $com_c['lang']['com']['002'],
                'view' => 'w-40',
                'sort' => true,
                'data' => 'id_item',
                'dict' => true,
                'uniq' => true
            ],
            'id_class' => [
                'chil' => true,
                'name' => $com_c['lang']['com']['003'],
                'data' => 'id_class',
                'dict' => true,
                'uniq' => true
            ],
            'id_user' => [
                'parn' => ['comment'],
                'name' => $com_c['lang']['com']['004'],
                'null' => $com_c['lang']['com']['100'],
                'view' => 'w-30',
                'sort' => true,
                'edit' => in_array($_SESSION['ROLE'], ['admin', 'editor']) ? 'c-select-2' : false,
                'info' => $com_c['lang']['com']['050'],
                'data' => 'id_user',
                'dict' => true
            ],
            'comment' => [
                'chil' => true,
                'edit' => 'c-textarea',
                'info' => $com_c['lang']['com']['051'],
                'data' => 'comment'
            ],
            //'done' => [
            //    'parn' => ['id_status'],
            //    'name' => $com_c['lang']['com']['006'],
            //    'view' => 'w-20',
            //    'sort' => true,
            //    'data' => 'id_item',
            //],
            'id_status' => [
                //'chil' => true,
                'name' => $com_c['lang']['com']['006'],
                'view' => 'w-20',
                'sort' => true,
                'edit' => 'c-select-2',
                'need' => true,
                'data' => 'id_status',
                'dict' => true
            ],
            '@-action' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['g-interfase']['150'],
                'view' => 'text-center',
                'oper' => ['edit', 'excel', 'rate']
            ]
        ];
    //  COM: фильтр
        $com_s['filter'] = [];
    //  COM: структура данных
        $com_c['struct'] = [
            'id'        => '',
            'id_item'   => '',
            'id_class'  => '',
            'id_user'   => '',
            //'done'      => '',
            'comment'   => '',
            'id_status' => ''
        ];
        $com_s['struct_no_edit'] = ['id_item', 'id_class', 'done'];
    //  COM: источники данных
        $com_s['base'] = "`rate_item`";
        $com_s['dict'] = [
            'id_item' => [
                'field' => 'name_',
                'table_data' => "`dict_discipline_item`",
                'link' => [
                    'Lb' => "`id_item`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id']
            ],
            'id_class' => [
                'field' => 'name_',
                'table_data' => "`dict_education_class`",
                'link' => [
                    'Lb' => "`id_class`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id']
            ],
            'id_user' => [
                'field' => 'name_',
                'table_data' => "`sys_user`",
                'link' => [
                    'Lb' => "`id_user`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'where' => [
                    "`sys_user`.`id_role` = '3'"
                ]
            ],
            'id_status' => [
                'field' => 'name_',
                'table_data' => "`rate_status`",
                'link' => [
                    'Lb' => "`id_status`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id']
            ]
        ];
    //  COM: фильтр
        $com_c['filter'] = [
        ];
    //  COM: проверка обязательных данным
        $com_c['struct_valid'] = 'v[\"id_status\"]';
        $com_s['struct_valid_php'] = '$param["DATA"]["id_status"]';
    //  COM: сортировка по умолчанию
        $com_c['sort'] = ['id_item' => true];
    //  COM: параметры поиска - поля
        $com_c['search_fields'] = [
            'id_item'   => $com_c['lang']['com']['002'],
            'id_class'  => $com_c['lang']['com']['003'],
            'id_user'   => $com_c['lang']['com']['004'],
            'id_status' => $com_c['lang']['com']['006']
        ];
    //  COM: параметры страницы просмотра
        $com_c['page'] = 1;
        $com_c['view'] = 10;
        $com_c['view_select'] = [10,20,50,100];

    //  модификация для пользователя
        if (in_array($_SESSION['ROLE'], ['expert'])) {
            $com_c['head']['comment'] = [
                'name' => $com_c['lang']['com']['005'],
                'edit' => 'c-textarea',
                'info' => $com_c['lang']['com']['051'],
                'data' => 'comment'
            ];
            unset($com_c['head']['id_user'], $com_s['dict']['id_user'], $com_c['search_fields']['id_user'], $com_c['struct']['id_user']);
        }

    //  подключение компоненты оценивания
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/dict/dict-@engine.php';

?>

<script>
    'use strict';
    $(() => com_dict(JSON.parse('<?= json_encode($com_c, JSON_HEX_TAG | JSON_FORCE_OBJECT) ?>')));
    $(document.currentScript).remove();
</script>
<div id="<?= $com_c['name'] ?>"></div>