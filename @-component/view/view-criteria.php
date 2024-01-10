<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'view-criteria';
    //  COM: локализации
        $com_c['lang'] = [
            'int'   => $GLOBALS['@']['LANG']['DATA']['g-interfase'],
            'err'   => $GLOBALS['@']['LANG']['DATA']['g-error'],
            'suf'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['title'],
            'cur'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'],
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-view-criteria']
        ];
    //  COM: описание полей представления
        $com_c['head'] = [
            'id_level' => [
                'null' => $com_c['lang']['com']['100'],
                'type' => '@-filter',
                'view' => 'col-md-6 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['002'],
                'data' => 'id_level',
                'dict' => false,
                'rely' => false
            ],
            'id_field' => [
                'null' => $com_c['lang']['com']['101'],
                'type' => '@-filter',
                'view' => 'col-md-6 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['003'],
                'data' => 'id_field',
                'dict' => false,
                'rely' => false
            ],
            'id_class' => [
                'null' => $com_c['lang']['com']['004'],
                'type' => '@-filter',
                'view' => 'col-md-6 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['004'],
                'data' => 'id_class',
                'dict' => false,
                'rely' => false
            ],
            'id_item' => [
                'null' => $com_c['lang']['com']['102'],
                'type' => '@-filter',
                'view' => 'col-md-6 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['005'],
                'data' => 'id_item',
                'dict' => false,
                'rely' => false
            ],
            'id_sec' => [
                'name' => $com_c['lang']['com']['006'],
                'null' => $com_c['lang']['com']['006'],
                'type' => '@-filter',
                'view' => 'col-md-6 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['006'],
                'data' => 'id_sec',
                'dict' => false,
                'rely' => false
            ],
            'id_subsec' => [
                'name' => $com_c['lang']['com']['007'],
                'null' => $com_c['lang']['com']['007'],
                'type' => '@-filter',
                'view' => 'col-md-6 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['007'],
                'data' => 'id_subsec',
                'dict' => false,
                'rely' => false
            ],
            'id_target' => [
                'name' => $com_c['lang']['com']['014'],
                'null' => $com_c['lang']['com']['014'],
                'type' => '@-filter',
                'view' => 'col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['014'],
                'data' => 'id_target',
                'dict' => false,
                'rely' => false
            ],
            'id_level_1' => [
                'name' => $com_c['lang']['com']['008'],
                'null' => $com_c['lang']['com']['008'],
                'type' => '@-filter',
                'view' => 'col-md-4 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['008'],
                'data' => 'id_level_1',
                'dict' => false,
                'rely' => false
            ],
            'id_level_2' => [
                'name' => $com_c['lang']['com']['009'],
                'null' => $com_c['lang']['com']['009'],
                'type' => '@-filter',
                'view' => 'col-md-4 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['009'],
                'data' => 'id_level_2',
                'dict' => false,
                'rely' => false
            ],
            'id_level_3' => [
                'name' => $com_c['lang']['com']['010'],
                'null' => $com_c['lang']['com']['010'],
                'type' => '@-filter',
                'view' => 'col-md-4 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['010'],       
                'data' => 'id_level_3',
                'dict' => false,
                'rely' => false
            ],
            'id_val' => [
                'name' => $com_c['lang']['com']['011'],
                'view' => 'w-10 text-center',
                'sort' => false,
                'data' => 'id_val',
                'dict' => false
            ]
        ];
    //  COM: фильтр
        $com_c['filter'] = [
            'id_level'      => null,
            'id_field'      => null,
            'id_class'      => null,
            'id_item'       => null,
            'id_sec'        => null,
            'id_subsec'     => null,
            'id_target'     => null,
            'id_level_1'    => null,
            'id_level_2'    => null,
            'id_level_3'    => null
        ];
    //  COM: структура данных
        $com_c['struct'] = [
            'id_sec'        => '',
            'id_subsec'     => '',
            'id_level_1'    => '',
            'id_level_2'    => '',
            'id_level_3'    => '',
            'id_target'     => '',
            'id_val'        => ''
        ];
    //  COM: источники данных
        $com_s['base'] = "`rate_val`";
        //$com_s['dict'] = [];
    //  COM: сортировка по умолчанию
        $com_c['sort'] = [];
    //  COM: параметры страницы просмотра
        $com_c['page'] = 1;
        $com_c['view'] = 10;
        $com_c['view_select'] = [10,20,50,100];

    //  COM элементы
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-@engine.php';

?>

<script>
    'use strict';
    let VIEW_CRITERIA;
    $(() => { VIEW_CRITERIA = com_view_criteria(JSON.parse('<?= json_encode($com_c, JSON_HEX_TAG | JSON_FORCE_OBJECT) ?>')); });
    $(document.currentScript).remove();
</script>
<div id="<?= $com_c['name'] ?>"></div>