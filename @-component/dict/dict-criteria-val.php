<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['admin'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'dict-criteria-val';
        $com_c['icon'] = 'fa-award';
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
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-criteria-val']
        ];
    //  COM: описание полей представления
        $com_c['head'] = [
            'id' => [
                'name' => 'ID',
                'view' => 'font-weight-bold',
                'sort' => true,
                'data' => 'id'
            ],
            'val' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-criteria-val']['002'],
                'view' => 'w-10',
                'sort' => true,
                'edit' => 'c-input',
                'data' => 'val',
                'need' => true,
                'uniq' => true
            ],
            'name_' . $com_c['lang']['cur'] => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-criteria-val']['003'],
                'view' => 'w-70',
                'sort' => true,
                'edit' => 'c-input-lng',
                'data' => 'name',
                'lang' => 'name_'
            ],
            'color' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['c-criteria-val']['004'],
                'view' => 'w-20 text-nowrap',
                'edit' => 'c-input',
                'data' => 'color',
                'need' => true
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
            'val'       => '',
            'name_ru'   => '',
            'name_en'   => '',
            'name_kz'   => '',
            'color'     => ''
        ];
    //  COM: источники данных
        $com_s['base'] = "`dict_criteria_val`";
        $com_s['dict'] = [];
    //  COM: проверка обязательных данным
        $com_c['struct_valid'] = 'v[\"val\"] && v[\"color\"]';
        $com_s['struct_valid_php'] = '$param["DATA"]["val"] && $param["DATA"]["color"]';
    //  COM: сортировка по умолчанию 
        $com_c['sort'] = ['id' => false];
    //  COM: параметры поиска - поля
        $com_c['search_fields'] = [
            'id' => 'ID',
            'val' => $GLOBALS['@']['LANG']['DATA']['c-criteria-val']['002'],
            'name_' . $com_c['lang']['cur']  => $GLOBALS['@']['LANG']['DATA']['c-criteria-val']['003']
            
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