<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['admin', 'editor', 'expert'])) exit;

    //  COM: основные параметры
        $com_s = []; // приватные
        $com_c = []; // публичные
        $com_c['name'] = 'dict-rate-val';
        $com_c['icon'] = 'fa-th';
        $com_c['modal'] = true;
    //  COM: доступные операции
        $com_c['oper'] = [
            'edit'   => true
        ];
    //  COM: локализации
        $com_c['lang'] = [
            'int'   => $GLOBALS['@']['LANG']['DATA']['g-interfase'],
            'err'   => $GLOBALS['@']['LANG']['DATA']['g-error'],
            'suf'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['title'],
            'cur'   => $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'],
            'com'   => $GLOBALS['@']['LANG']['DATA']['c-dict-rate-val']
        ];
    //  COM: описание полей представления
        $com_c['head'] = [
            'id_item' => [
                'type' => '@-filter',
                'view' => 'd-none',
                'data' => 'id_item',
                'dict' => true
            ],
            'id_class' => [
                'type' => '@-filter',
                'view' => 'col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['002'],
                'need' => true,
                'data' => 'id_class',
                'dict' => true,
                'rely' => 'id_item'
            ],
            'id_sec' => [
                'name' => $com_c['lang']['com']['007'],
                'chil' => true,
                'data' => 'id_sec',
                'dict' => true
            ],
            'id_subsec' => [
                'name' => $com_c['lang']['com']['008'],
                'chil' => true,
                'data' => 'id_subsec',
                'dict' => true
            ],
            'id_target' => [
                'parn' => ['id_sec', 'id_subsec'],
                'name' => $com_c['lang']['com']['006'],
                'view' => 'w-80',
                'sort' => true,
                'data' => 'id_target',
                'dict' => true
            ],
            'id_level_1' => [
                'null' => true,
                'type' => '@-filter',
                'view' => 'col-lg-4 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['003'],
                'data' => 'id_level_1',
                'dict' => true
            ],
            'id_level_2' => [
                'null' => true,
                'type' => '@-filter',
                'view' => 'col-lg-4 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['004'],
                'data' => 'id_level_2',
                'dict' => true,
                'rely' => 'id_level_1'
            ],
            'id_level_3' => [
                'null' => true,
                'type' => '@-filter',
                'view' => 'col-lg-4 col-12',
                'edit' => 'c-select-2',
                'prep' => $com_c['lang']['com']['005'],
                'data' => 'id_level_3',
                'dict' => true,
                'rely' => 'id_level_2'
            ],
            'id_val' => [
                'name' => $com_c['lang']['com']['009'],
                'view' => 'w-20',
                'sort' => true,
                'edit' => 'c-select-2',
                'need' => true,
                'data' => 'id_val',
                'dict' => true
            ],
            '@-action' => [
                'name' => $GLOBALS['@']['LANG']['DATA']['g-interfase']['150'],
                'view' => 'text-center',
                'oper' => ['edit']
            ]
        ];
    //  COM: фильтр
        $com_c['filter'] = [
            'id_item' => null,
            'id_class' => null,
            'id_level_1' => null,
            'id_level_2' => null,
            'id_level_3' => null
        ];
        if (isset($_REQUEST['FILTER'])) {
            if ($_REQUEST['FILTER']['id_level_1'] == null) $_REQUEST['FILTER']['id_level_2'] = null;
            if ($_REQUEST['FILTER']['id_level_2'] == null) $_REQUEST['FILTER']['id_level_3'] = null;
        }

    //  COM: структура данных
        $com_c['struct'] = [
            'id'            => '',
            'id_item'       => '',
            'id_class'      => '',
            'id_sec'        => '',
            'id_subsec'     => '',
            'id_target'     => '',
            'id_level_1'    => '',
            'id_level_2'    => '',
            'id_level_3'    => '',
            'id_val'        => ''
        ];
        $com_s['struct_no_edit'] = [
            'id_item',
            'id_class',
            'id_sec',
            'id_subsec',
            'id_target',
            'id_level_1',
            'id_level_2',
            'id_level_3'
        ];
    //  COM: источники данных
        $com_s['base'] = "`rate_val`";
        $com_s['dict'] = [
            'id_sec' => [
                'field' => 'name_',
                'table_data' => "`dict_discipline_section`",
                'link' => [
                    'Lb' => "`id_sec`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
            ],
            'id_subsec' => [
                'field' => 'name_',
                'table_data' => "`dict_discipline_subsection`",
                'link' => [
                    'Lb' => "`id_subsec`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
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
            ],
            'id_class' => [
                'field' => 'name_',
                'table_data' => "`dict_education_class`",
                'link' => [
                    'Lb' => "`id_class`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => [
                    'select' => [
                        'field' => "`rate_item`.`id_item` AS 'id_item'",
                        'join' => [
                            "RIGHT JOIN `rate_item` ON `dict_education_class`.`id` = `rate_item`.`id_class`"
                        ]
                    ]
                ]
            ],
            'id_target' => [
                'field' => 'name_',
                'table_data' => "`dict_discipline_target`",
                'link' => [
                    'Lb' => "`id_target`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
            ],
            'id_level_1' => [
                'field' => 'name_',
                'table_data' => "`dict_criteria_level_1`",
                'link' => [
                    'Lb' => "`id_level_1`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => false
            ],
            'id_level_2' => [
                'field' => 'name_',
                'table_data' => "`dict_criteria_level_2`",
                'link' => [
                    'Lb' => "`id_level_2`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => [
                    'select' => [
                        'field' => "`dict_criteria_level_2`.`id_level_1` AS 'id_level_1'",
                        'join' => [
                            "LEFT JOIN `dict_criteria_level_1` ON `dict_criteria_level_1`.`id` = `dict_criteria_level_2`.`id_level_1`"
                        ]
                    ]
                ]
            ],
            'id_level_3' => [
                'field' => 'name_',
                'table_data' => "`dict_criteria_level_3`",
                'link' => [
                    'Lb' => "`id_level_3`",
                    'Tb' => "`id`"
                ],
                'text' => ['name_', 'id'],
                'rely' => [
                    'select' => [
                        'field' => "`dict_criteria_level_3`.`id_level_2` AS 'id_level_2'",
                        'join' => [
                            "LEFT JOIN `dict_criteria_level_2` ON `dict_criteria_level_2`.`id` = `dict_criteria_level_3`.`id_level_2`"
                        ]
                    ]
                ]
            ],
            'id_val' => [
                'field' => 'val',
                'table_data' => "`dict_criteria_val`",
                'link' => [
                    'Lb' => "`id_val`",
                    'Tb' => "`id`"
                ],
                'text' => ['val', 'id'],
                'rely' => false
            ]
        ];
    //  COM: проверка обязательных данным
        $com_c['struct_valid'] = 'v[\"id_val\"]';
        $com_s['struct_valid_php'] = '$param["DATA"]["id_val"]';
    //  COM: сортировка по умолчанию 
        $com_c['sort'] = ['id_target' => true];
    //  COM: параметры поиска - поля
        $com_c['search_fields'] = [
            'id_target' => $com_c['lang']['com']['006'],
            'id_sec'    => $com_c['lang']['com']['007'],
            'id_subsec' => $com_c['lang']['com']['008'],
            'id_val'    => $com_c['lang']['com']['009'],
        ];
    //  COM: параметры страницы просмотра
        $com_c['page'] = 1;
        $com_c['view'] = 10;
        $com_c['view_select'] = [10,20,50,100];

    //  COM элементы
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/dict/dict-@engine.php';

?>

<script>
    'use strict';
    let RATE;
    $(() => { RATE = com_dict(JSON.parse('<?= json_encode($com_c, JSON_HEX_TAG | JSON_FORCE_OBJECT) ?>')) });
    $(document.currentScript).remove();
</script>
<div id="<?= $com_c['name'] ?>"></div>