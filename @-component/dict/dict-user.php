<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['admin'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'dict-user';
        $com_c['icon'] = 'fa-users';
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
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-users']
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
                'name' => $GLOBALS['@']['LANG']['DATA']['c-users']['002'],
                'view' => 'w-40',
                'sort' => true,
                'edit' => 'c-input-lng',
                'data' => 'name',
                'lang' => 'name_',
                'need' => true
            ],
            'id_role' => [
                'parn' => ['mail'],
                'name' => $GLOBALS['@']['LANG']['DATA']['c-users']['003'],
                'view' => 'text-nowrap w-40',
                'edit' => 'c-select-2',
                'info' => $GLOBALS['@']['LANG']['DATA']['c-users']['005'],
                'data' => 'id_role',
                'dict' => true,
                'need' => true
            ],
            'mail' => [
                'chil' => true,
                'edit' => 'c-input-mail',
                'info' => $GLOBALS['@']['LANG']['DATA']['c-users']['006'],
                'data' => 'mail',
                'need' => true
            ],
            'access' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-users']['008'],
                'view' => 'text-center w-20',
                'sort' => true,
                'edit' => 'c-checkbox',
                'data' => 'access'
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
            'id_role'   => '',
            'mail'      => '',
            'access'    => true
        ];
    //  COM: источники данных
        $com_s['base'] = "`sys_user`";
        $com_s['dict'] = [
            'id_role' => [
                'field' => 'name_',
                'table_data' => "`sys_role`",
                'link' => [
                    'Lb' => "`id_role`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
            ]
        ];
    //  COM: проверка обязательных данным
        $com_c['struct_valid'] = '(v[\"name_en\"] || v[\"name_kz\"] || v[\"name_ru\"]) && reg_mail.test(v[\"mail\"])';
        $com_s['struct_valid_php'] = '($param["DATA"]["name_en"] || $param["DATA"]["name_kz"] || $param["DATA"]["name_ru"]) && filter_var($param["DATA"]["mail"], FILTER_VALIDATE_EMAIL)';
    //  COM: сортировка по умолчанию
        $com_c['sort'] = ['id' => false];
    //  COM: параметры поиска - поля
        $com_c['search_fields'] = [
            'id' => 'ID',
            'name_' . $com_c['lang']['cur'] => $GLOBALS['@']['LANG']['DATA']['c-users']['002'],
            'id_role' => $GLOBALS['@']['LANG']['DATA']['c-users']['004'],
            'mail' => $GLOBALS['@']['LANG']['DATA']['c-users']['006']
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