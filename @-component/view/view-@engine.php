<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка инициализации параметров компонеты
        if (empty($com_c) || empty($com_s)) exit;

    //  параметры по-умолчанию
        $com_def = [
            'parn' => false,    //  признак родителя                        (false || true)
            'chil' => false,    //  признак потомка                         (false || true)
            'name' => false,    //  наименование                            (строка)
            'null' => false,    //  сообщение для пустых данных             (строка)
            'view' => false,    //  корректировка стилей                    (false || строка)
            'sort' => false,    //  признак возможности сортировки          (false || true)
            'edit' => false,    //  признак редактирования                  (false || имя компоненты)
            'prep' => false,    //  наименование поля редактирования        (false || строка)
            'info' => false,    //  подсказка для элемента формы            (false || строка)
            'need' => false,    //  признак обязательности                  (false || true)
            'data' => false,    //  имя источника данных                    (строка)
            'lang' => false,    //  признак мультиязычности                 (false || true)
            'dict' => false,    //  признак словаря                         (false || имя словаря)
            'ajax' => 10000,    //  лимит статического словаря              (число)
            'uniq' => false,    //  признак уникальности                    (false || true)
            'rely' => false     //  признак зависомости данных              (false || имя данных)
        ];

    //  проверка параметров
        foreach ($com_def as $def => $value) {
            foreach ($com_c['head'] as $k => $e) {
                if (!isset($e[$def])) $com_c['head'][$k][$def] = $value;
            }
        }

    //  API: получение данных
        function view_target_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'legend'        => [],
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => [
                        'id_level'  => ['data' => [], 'total' => 1],
                        'id_field'  => ['data' => [], 'total' => 1],
                        'id_item'   => ['data' => [], 'total' => 1],
                        'id_class'  => ['data' => [], 'total' => 1],
                        'id_sec'    => ['data' => [], 'total' => 1],
                        'id_subsec' => ['data' => [], 'total' => 1]
                    ]
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  подготовка фильтров
                $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                foreach ($param['FILTER'] as $k => $e) {
                    $param['FILTER'][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($e)));
                }

            //  проверка фильтров
                $return['dict']['id_level'] = view_get_dict_public('id_level', $param['FILTER']);

                if ($param['FILTER']['id_level'] == null) return $return;
                $return['dict']['id_field'] = view_get_dict_public('id_field', $param['FILTER']);
                if (!count($return['dict']['id_field']['data'])) return $return;
                
                if ($param['FILTER']['id_field'] == null) return $return;
                $return['dict']['id_class'] = view_get_dict_public('id_class', $param['FILTER']);
                if (!count($return['dict']['id_class']['data'])) return $return;

                if ($param['FILTER']['id_class'] == null) return $return;
                $return['dict']['id_item'] = view_get_dict_public('id_item', $param['FILTER']);
                if (!count($return['dict']['id_item']['data'])) return $return;

                if ($param['FILTER']['id_item'] == null) return $return;
                $return['dict']['id_sec'] = view_get_dict_public('id_sec', $param['FILTER']);

                if ($param['FILTER']['id_sec'] != null) $return['dict']['id_subsec'] = view_get_dict_public('id_subsec', $param['FILTER']);

            //  подготовка параметров запроса - разделы и подразделы
                $section = '';
                if ($param['FILTER']['id_sec'] != null) $section .= " AND `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "'";
                if ($param['FILTER']['id_subsec'] != null) $section .= " AND `rv`.`id_subsec` = '" . $param['FILTER']['id_subsec'] . "'";

            //  параметры - интервал выборки
                if (!isset($param['VIEW'])) return ['error' => 200];
                if ($param['VIEW'] === '*') $param['VIEW'] = 1000000000;
                else $param['VIEW'] = in_array((int) $param['VIEW'], $com_c['view_select']) ? (int) $param['VIEW'] : $com_c['view'];

            //  параметры - начало выборки
                if (!isset($param['PAGE'])) return ['error' => 200];
                $return['page'] = (int) $param['PAGE'] > 0 ? (int) $param['PAGE'] : $com_c['page'];
                $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];

            //  параметры поиска
                $search = '';
                $param['SEARCH'] = json_decode($param['SEARCH'], JSON_OBJECT_AS_ARRAY);
                $return['search'] = $param['SEARCH']['data'];
                $param['SEARCH']['data'] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($param['SEARCH']['data'])));
                $arr = [
                    'id_target' => 'dict_discipline_target',
                    'id_sec' => 'dict_discipline_section',
                    'id_subsec' => 'dict_discipline_subsection',
                    'id_level_1' => 'dict_criteria_level_1',
                    'id_level_2' => 'dict_criteria_level_2',
                    'id_level_3' => 'dict_criteria_level_3',
                    'id_val' => 'dict_criteria_val'
                ];
                $temp = [];
                if (!empty($param['SEARCH']['data'])) {
                    foreach ($arr as $k => $e) {
                        if ($param['SEARCH']['field'] == $k || $param['SEARCH']['field'] == '') {
                            $field = "`name_" . $com_c['lang']['cur'] . "`";
                            if ($k == 'id_val') $field = "`val`";
                            $temp[] = "`rv`.`" . $k . "` IN (SELECT DISTINCT `id` FROM `" . $e . "` WHERE UPPER(" . $field . ") LIKE '%" . $param['SEARCH']['data'] . "%')";
                        }
                    }
                    $search = " AND (" . implode(" OR ", $temp) . ")";
                }

            //  проверка наличия страницы
                $query = "
                    SELECT COUNT(*) AS `total`
                    FROM `rate_val` AS `rv`

                    RIGHT JOIN `dict_discipline_item` AS `ddi` ON `ddi`.`id` = `rv`.`id_item`
                    RIGHT JOIN `dict_discipline_target` AS `ddt` ON `ddt`.`id` = `rv`.`id_target`
                    RIGHT JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `rv`.`id_sec` 
                    RIGHT JOIN `dict_discipline_subsection` AS `ddss` ON `ddss`.`id` = `rv`.`id_subsec` 
                    RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1` 
                    LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2` 
                    LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val` 

                    WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' " . $section . $search . "
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                $temp = ceil($res[0]['total'] / $param['VIEW']);
                if ($temp < $return['page']) {
                    if ($temp == 0) $return['page'] = 1;
                    else $return['page'] = $temp;
                    $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];
                }
                $return['total_search'] = $res[0]['total'];


            //  подготовка запроса - локализации
                $field = [];
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field[] = "IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', ";
                $field[] = "`{{table}}`.`name_" . $com_c['lang']['cur'] . "`, ";
                $field[] = "CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`";
                $field = implode("", $field);

            //  запрос данных
                $query = "
                    SELECT 
                        `ddt`.`id` AS `id_target`, " . str_replace(['{{table}}', '{{field}}'], ['ddt', 'id_target'], $field) . ",
                        `dds`.`id` AS `id_sec`, " . str_replace(['{{table}}', '{{field}}'], ['dds', 'id_sec'], $field) . ", 
                        `ddss`.`id` AS `id_subsec`, " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'id_subsec'], $field) . ", 
                        `dcl_1`.`id` AS `id_level_1`, " . str_replace(['{{table}}', '{{field}}'], ['dcl_1', 'id_level_1'], $field) . ", 
                        `dcl_2`.`id` AS `id_level_2`, " . str_replace(['{{table}}', '{{field}}'], ['dcl_2', 'id_level_2'], $field) . ", 
                        `dcl_3`.`id` AS `id_level_3`, " . str_replace(['{{table}}', '{{field}}'], ['dcl_3', 'id_level_3'], $field) . ", 
                        `dcv`.`id` AS `id_val`, `dcv`.`val` AS `id_val_text`
                    FROM `rate_val` AS `rv`

                    RIGHT JOIN `dict_discipline_item` AS `ddi` ON `ddi`.`id` = `rv`.`id_item`
                    RIGHT JOIN `dict_discipline_target` AS `ddt` ON `ddt`.`id` = `rv`.`id_target`
                    RIGHT JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `rv`.`id_sec` 
                    RIGHT JOIN `dict_discipline_subsection` AS `ddss` ON `ddss`.`id` = `rv`.`id_subsec` 
                    RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1` 
                    LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2` 
                    LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val` 

                    WHERE `rv`.`id` IN (
                        SELECT * 
                        FROM (
                            SELECT DISTINCT `rv`.`id` FROM `rate_val` AS `rv`

                            RIGHT JOIN `dict_discipline_item` AS `ddi` ON `ddi`.`id` = `rv`.`id_item`
                            RIGHT JOIN `dict_discipline_target` AS `ddt` ON `ddt`.`id` = `rv`.`id_target`
                            RIGHT JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `rv`.`id_sec` 
                            RIGHT JOIN `dict_discipline_subsection` AS `ddss` ON `ddss`.`id` = `rv`.`id_subsec` 
                            RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1` 
                            LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2` 
                            LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`
                            LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val` 

                            WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' " . $section . $search . "
                            ORDER BY `ddt`.`name_" . $com_c['lang']['cur'] . "` ASC, `dcl_1`.`name_" . $com_c['lang']['cur'] . "` ASC, `dcl_2`.`name_" . $com_c['lang']['cur'] . "` ASC, `dcl_3`.`name_" . $com_c['lang']['cur'] . "`
                            LIMIT " . $param['PAGE'] . ", " . $param['VIEW'] . "
                            ) 
                        temp_table
                    ) " . $search . "
                    ORDER BY `ddt`.`name_" . $com_c['lang']['cur'] . "` ASC, `dcl_1`.`name_" . $com_c['lang']['cur'] . "` ASC, `dcl_2`.`name_" . $com_c['lang']['cur'] . "` ASC, `dcl_3`.`name_" . $com_c['lang']['cur'] . "`
                ";

                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                else {
                    $res = $res -> fetch_all(MYSQLI_ASSOC);
                    foreach ($res as $row) {
                        $temp = [];
                        //print_r($row);
                        foreach ($com_c['struct'] as $k => $e) {
                            if ($row[$k . '_text'] == 'NULL') {
                                $row[$k . '_text'] = '';
                                $row[$k . '_id'] = '';
                            }
                            $temp[$k] = $row[$k . '_text'];
                            $temp[$k . '_id'] = $row[$k];
                        }
                        $return['items'][] = $temp;
                    }
                }

            //  запрос общего количества
                $query = "
                    SELECT COUNT(*) AS `total`
                    FROM `rate_val` AS `rv`
                    RIGHT JOIN `dict_discipline_target` AS `ddt` ON `ddt`.`id` = `rv`.`id_target`
                    RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1` 
                    LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2` 
                    LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`
                    WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' " . $section . "
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                $return['total_all'] = $res[0]['total'];

            //  запрос легенды
                $query = "
                    SELECT
                        `dcv`.`val` AS `val`, `dcv`.`color` AS `color`, " . str_replace(['{{table}}', '{{field}}'], ['dcv', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcv`
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                foreach ($res as $row) $return['legend'][$row['val']] = $row;

            //  возврат
                return $return;
        }
  
    //  API: получение данных
        function view_val_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'legend'        => [],
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => [
                        'id_level'  => ['data' => [], 'total' => 1],
                        'id_field'  => ['data' => [], 'total' => 1],
                        'id_item'   => ['data' => [], 'total' => 1],
                        'id_class'  => ['data' => [], 'total' => 1],
                        'id_sec'    => ['data' => [], 'total' => 1],
                        'id_subsec' => ['data' => [], 'total' => 1],
                        'id_target' => ['data' => [], 'total' => 1]
                    ]
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  подготовка фильтров
                $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                foreach ($param['FILTER'] as $k => $e) {
                    $param['FILTER'][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($e)));
                }

            //  проверка фильтров
                $return['dict']['id_level'] = view_get_dict_public('id_level', $param['FILTER']);
                if ($param['FILTER']['id_level'] != null) {
                    $return['dict']['id_field'] = view_get_dict_public('id_field', $param['FILTER']);
                    if ($param['FILTER']['id_field'] != null) {
                        $return['dict']['id_class'] = view_get_dict_public('id_class', $param['FILTER']);
                        if ($param['FILTER']['id_class'] != null) {
                            $return['dict']['id_item'] = view_get_dict_public('id_item', $param['FILTER']);
                            if ($param['FILTER']['id_item'] != null) {
                                $return['dict']['id_sec'] = view_get_dict_public('id_sec', $param['FILTER']);
                                if ($param['FILTER']['id_sec'] != null) {
                                    $return['dict']['id_subsec'] = view_get_dict_public('id_subsec', $param['FILTER']);
                                    if ($param['FILTER']['id_subsec'] != null) {
                                        $return['dict']['id_target'] = view_get_dict_public('id_target', $param['FILTER']);
                                    }
                                }
                            }
                        }
                    }
                }

            //  подготовка запроса - локализации
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field = "
                    IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', 
                    `{{table}}`.`name_" . $com_c['lang']['cur'] . "`, 
                    CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`
                ";


            //  параметры - интервал выборки
                if (!isset($param['VIEW'])) return ['error' => 200];
                if ($param['VIEW'] === '*') $param['VIEW'] = 1000000000;
                else $param['VIEW'] = in_array((int) $param['VIEW'], $com_c['view_select']) ? (int) $param['VIEW'] : $com_c['view'];

            //  параметры - начало выборки
                if (!isset($param['PAGE'])) return ['error' => 200];
                $return['page'] = (int) $param['PAGE'] > 0 ? (int) $param['PAGE'] : $com_c['page'];
                $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];

            //  запрос оценок
                $select = [];
                $order_by = [];
                $query = "
                    SELECT `dcl`.`id`, `dcl`.`val`, `dcl`.`color`, " . str_replace(['{{table}}', '{{field}}'], ['dcl', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcl`
                    ORDER BY `dcl`.`val` DESC
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                if (count($res) == 0) return $return;
                foreach ($res as $row) {
                    $return['dict_val'][$row['id']] = $row;
                    $select[] = "SUM(IF (`dcv`.`id` = '" . $row['id'] . "', 1, 0)) AS `" . $row['id'] . "`";
                    $order_by[] = "`" . $row['id'] . "` DESC";
                }
                $select[] = "SUM(IF (`dcv`.`id` IS NULL, 1, 0)) AS `-`";

            //  подготовка запроса
                $query_1 = "
                    SELECT
                        " . str_replace(['{{table}}', '{{field}}'], ['del', 'title'], $field) . ",
                        " . implode(", ", $select) . "
                ";
                $query_2 = "
                    FROM `dict_education_level` AS `del`
                    INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `del`.`id`
                    INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                    INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                ";
                $query_3 = "
                    GROUP BY `title_text`
                    ORDER BY " . implode(", ", $order_by) . "
                ";

                if ($param['FILTER']['id_level'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['def', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_education_field` AS `def`
                        INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_field` = `def`.`id` AND `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }
  
                if ($param['FILTER']['id_field'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['dec', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_education_class` AS `dec`
                        INNER JOIN `dict_link_class_level` AS `cl` ON `dec`.`id` = `cl`.`id_class` AND `cl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `cl`.`id_level` AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_class'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['ddi', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_item` AS `ddi`
                        INNER JOIN `rate_item` AS `ri` ON `ddi`.`id` = `ri`.`id_item` AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `dict_link_field_level` AS `fl` ON `ri`.`id_field` = `fl`.`id_field` AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_item'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['dds', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_section` AS `dds`
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_sec'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_subsection` AS `ddss`
                        INNER JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `ddss`.`id_sec` AND `dds`.`id` = '" . $param['FILTER']['id_sec'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec` AND `ddss`.`id` = `rv`.`id_subsec`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_subsec'] != null) {
                    $target = '';
                    if ($param['FILTER']['id_target'] != null) $target = " AND `rv`.`id_target` = '" . $param['FILTER']['id_target'] . "'";
                    $query_1 = "
                        SELECT 
                        " . str_replace(['{{table}}', '{{field}}'], ['ddt', 'title'], $field) . ",
                        " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `rate_val` AS `rv`
                        LEFT JOIN `dict_discipline_target` AS `ddt` ON `rv`.`id_target` = `ddt`.`id`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                              `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND 
                              `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "' AND 
                              `rv`.`id_subsec` = '" . $param['FILTER']['id_subsec'] . "'
                              " . $target . "
                    ";
                }

            //  проверка наличия страницы
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $total = $res -> num_rows;
                $temp = ceil($total / $param['VIEW']);
                if ($temp < $return['page']) {
                    if ($temp == 0) $return['page'] = 1;
                    else $return['page'] = $temp;
                    $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];
                }
                $return['total_all'] = $total;

            //  запрос данных
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3 . " LIMIT " . $param['PAGE'] . ", " . $param['VIEW'] . ";");
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);

            //  подготовка ответа
                $total = [];
                $temp = [];
                foreach ($res as $row) {
                    foreach ($row as $k => $e) {
                        if ($k == 'title_text') continue;
                        $temp[$row['title_text']][$k] = $e;
                        if (!isset($total[$row['title_text']])) $total[$row['title_text']] = 0;
                        $total[$row['title_text']] += $e;
                    }
                }

                foreach ($temp as $k => $e) {
                    $arr = [];
                    foreach ($e as $kk => $ee) $arr[] = ['id_val' => $kk, 'value' => round((($ee * 100) / $total[$k]), 2)];
                    $return['items'][] = ['title' => $k, 'data' => $arr];
                }

            //  запрос легенды
                $query = "
                    SELECT
                        `dcv`.`val` AS `val`, `dcv`.`color` AS `color`, " . str_replace(['{{table}}', '{{field}}'], ['dcv', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcv`
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                foreach ($res as $row) $return['legend'][$row['val']] = $row;

            //  возврат
                return $return;
        }

    //  API: получение данных
        function view_criteria_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'legend'        => [],
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => [
                        'id_level'  => ['data' => [], 'total' => 1],
                        'id_field'  => ['data' => [], 'total' => 1],
                        'id_item'   => ['data' => [], 'total' => 1],
                        'id_class'  => ['data' => [], 'total' => 1],
                        'id_sec'    => ['data' => [], 'total' => 1],
                        'id_subsec' => ['data' => [], 'total' => 1],
                        'id_target' => ['data' => [], 'total' => 1],
                        'id_level_1' => ['data' => [], 'total' => 1],
                        'id_level_2' => ['data' => [], 'total' => 1],
                        'id_level_3' => ['data' => [], 'total' => 1]
                    ]
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  подготовка фильтров
                $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                foreach ($param['FILTER'] as $k => $e) {
                    $param['FILTER'][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($e)));
                }

            //  проверка фильтров
                $return['dict']['id_level'] = view_get_dict_public('id_level', $param['FILTER']);
                if ($param['FILTER']['id_level'] != null) {
                    $return['dict']['id_field'] = view_get_dict_public('id_field', $param['FILTER']);
                    if ($param['FILTER']['id_field'] != null) {
                        $return['dict']['id_class'] = view_get_dict_public('id_class', $param['FILTER']);
                        if ($param['FILTER']['id_class'] != null) {
                            $return['dict']['id_item'] = view_get_dict_public('id_item', $param['FILTER']);
                            if ($param['FILTER']['id_item'] != null) {
                                $return['dict']['id_sec'] = view_get_dict_public('id_sec', $param['FILTER']);
                                if ($param['FILTER']['id_sec'] != null) {
                                    $return['dict']['id_subsec'] = view_get_dict_public('id_subsec', $param['FILTER']);
                                    if ($param['FILTER']['id_subsec'] != null) {
                                        $return['dict']['id_target'] = view_get_dict_public('id_target', $param['FILTER']);
                                    }
                                }
                            }
                        }
                    }
                }

                $return['dict']['id_level_1'] = view_get_dict_public('id_level_1', $param['FILTER']);
                if ($param['FILTER']['id_level_1'] != null) {
                    $return['dict']['id_level_2'] = view_get_dict_public('id_level_2', $param['FILTER']);
                    if ($param['FILTER']['id_level_2'] != null) {
                        $return['dict']['id_level_3'] = view_get_dict_public('id_level_3', $param['FILTER']);
                    }
                }


            //  подготовка запроса - локализации
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field = "
                    IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', 
                    `{{table}}`.`name_" . $com_c['lang']['cur'] . "`, 
                    CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`
                ";

            //  запрос оценок
                $select = [];
                $order_by = [];
                $query = "
                    SELECT `dcl`.`id`, `dcl`.`val`, `dcl`.`color`, " . str_replace(['{{table}}', '{{field}}'], ['dcl', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcl`
                    ORDER BY `dcl`.`val` DESC
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                if (count($res) == 0) return $return;
                foreach ($res as $row) {
                    $return['dict_val'][$row['id']] = $row;
                    $select[] = "SUM(IF (`dcv`.`id` = '" . $row['id'] . "', 1, 0)) AS `" . $row['id'] . "`";
                    $order_by[] = "`" . $row['id'] . "` DESC";
                }
                $select[] = "SUM(IF (`dcv`.`id` IS NULL, 1, 0)) AS `-`";

            //  подготовка запроса
                $id_level_1 = "RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1`";
                $id_level_2 = "LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2`";
                $id_level_3 = "LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`";
                if ($param['FILTER']['id_level_1'] != null) {
                    $id_level_1 .= " AND `dcl_1`.`id` = '" . $param['FILTER']['id_level_1'] . "'";
                    
                    if ($param['FILTER']['id_level_2'] != null) {
                        $id_level_2 = "RIGHT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2`";
                        $id_level_2 .= " AND `dcl_2`.`id` = '" . $param['FILTER']['id_level_2'] . "'";
                        
                        if ($param['FILTER']['id_level_3'] != null) {
                            $id_level_3 = "RIGHT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`";
                            $id_level_3 .= " AND `dcl_3`.`id` = '" . $param['FILTER']['id_level_3'] . "'";
                        }
                    }
                }
                
                $query_1 = "
                    SELECT
                        " . str_replace(['{{table}}', '{{field}}'], ['dcl_1', 'level_1'], $field) . ",
                        " . str_replace(['{{table}}', '{{field}}'], ['dcl_2', 'level_2'], $field) . ",
                        " . str_replace(['{{table}}', '{{field}}'], ['dcl_3', 'level_3'], $field) . ",
                        " . implode(", ", $select) . "
                    FROM `rate_val` AS `rv`
                    " . $id_level_1 . "
                    " . $id_level_2 . "
                    " . $id_level_3 . "
                ";
                $query_2 = "
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_status` = '3'
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                ";
                $query_3 = "
                    GROUP BY `level_1_text`, `level_2_text`, `level_3_text`
                    ORDER BY " . implode(", ", $order_by) . "
                ";

                if ($param['FILTER']['id_level'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                    
                }
                
                if ($param['FILTER']['id_field'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_class'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                    ";
                }

                if ($param['FILTER']['id_item'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                              `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "'
                    ";                 
                }

                if ($param['FILTER']['id_sec'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                              `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND 
                              `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "'
                    ";  
                } 

                if ($param['FILTER']['id_subsec'] != null) {
                    $target = '';
                    if ($param['FILTER']['id_target'] != null) $target = " AND `rv`.`id_target` = '" . $param['FILTER']['id_target'] . "'";
                    $query_2 = "
                    RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                    RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                          `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND 
                          `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "' AND
                          `rv`.`id_subsec` = '" . $param['FILTER']['id_subsec'] . "' 
                          " . $target . "
                    "; 
                }

            //  запрос данных
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);

            //  подготовка ответа
                $total = [];
                $temp = [];
                foreach ($res as $row) {
                    $title = [];
                    foreach ($row as $k => $e) {
                        if ($k == 'level_1_text') { $title = ['➤ <b>' . $row['level_1_text'] . '</b>']; continue; }
                        if ($k == 'level_2_text') { 
                            if ($row['level_2_text'] != 'NULL') $title[] = '➤ ' . $row['level_2_text'];
                            continue; 
                        }
                        if ($k == 'level_3_text') { 
                            if ($row['level_3_text'] != 'NULL') $title[] = '➤ ' . $row['level_3_text']; 
                            continue; 
                        }
                        $t = implode("<br />", $title);
                        $temp[$t][$k] = $e;
                        if (!isset($total[$t])) $total[$t] = 0;
                        $total[$t] += $e;
                    }
                }
                foreach ($temp as $k => $e) {
                    $arr = [];
                    foreach ($e as $kk => $ee) $arr[] = ['id_val' => $kk, 'value' => round((($ee * 100) / $total[$k]), 2)];
                    $return['items'][] = ['title' => $k, 'data' => $arr];
                }

            //  запрос легенды
                $query = "
                    SELECT `dcv`.`val` AS `val`, `dcv`.`color` AS `color`, " . str_replace(['{{table}}', '{{field}}'], ['dcv', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcv`
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                foreach ($res as $row) $return['legend'][$row['val']] = $row;

            //  возврат
                return $return;
        }

    //  API: получение данных
        function view_total_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'legend'        => [],
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => [
                        'id_val'        => ['data' => [], 'total' => 1],
                        'id_level'      => ['data' => [], 'total' => 1],
                        'id_field'      => ['data' => [], 'total' => 1],
                        'id_item'       => ['data' => [], 'total' => 1],
                        'id_class'      => ['data' => [], 'total' => 1],
                        'id_sec'        => ['data' => [], 'total' => 1],
                        'id_subsec'     => ['data' => [], 'total' => 1],
                        'id_level_1'    => ['data' => [], 'total' => 1],
                        'id_level_2'    => ['data' => [], 'total' => 1],
                        'id_level_3'    => ['data' => [], 'total' => 1]
                    ]
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  подготовка фильтров
                $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                foreach ($param['FILTER'] as $k => $e) {
                    if (is_array($e)) {
                        foreach ($e as $kk => $ee) $param['FILTER'][$k][$kk] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($ee)));
                        continue;
                    }
                    $param['FILTER'][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($e)));
                }

            //  проверка фильтров                
                $return['dict']['id_val'] = view_get_dict_public('id_val', $param['FILTER']);
                $return['dict']['id_val']['data'][0] = [
                    'id' => '0',
                    'text' => $com_c['lang']['com']['013']
                ];
                $return['dict']['id_level'] = view_get_dict_public('id_level', $param['FILTER']);
                if ($param['FILTER']['id_level'] != null) {
                    $return['dict']['id_field'] = view_get_dict_public('id_field', $param['FILTER']);
                    if ($param['FILTER']['id_field'] != null) {
                        $return['dict']['id_class'] = view_get_dict_public('id_class', $param['FILTER']);
                        if ($param['FILTER']['id_class'] != null) {
                            $return['dict']['id_item'] = view_get_dict_public('id_item', $param['FILTER']);
                            if ($param['FILTER']['id_item'] != null) {
                                $return['dict']['id_sec'] = view_get_dict_public('id_sec', $param['FILTER']);
                                if ($param['FILTER']['id_sec'] != null) {
                                    $return['dict']['id_subsec'] = view_get_dict_public('id_subsec', $param['FILTER']);
                                }

                            }
                        }
                    }
                }

                $return['dict']['id_level_1'] = view_get_dict_public('id_level_1', $param['FILTER']);
                if ($param['FILTER']['id_level_1'] != null) {
                    $return['dict']['id_level_2'] = view_get_dict_public('id_level_2', $param['FILTER']);
                    if ($param['FILTER']['id_level_2'] != null) {
                        $return['dict']['id_level_3'] = view_get_dict_public('id_level_3', $param['FILTER']);
                    }
                }

            //  подготовка запроса - локализации
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field = "
                    IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', 
                    `{{table}}`.`name_" . $com_c['lang']['cur'] . "`, 
                    CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`
                ";


            //  параметры - интервал выборки
                if (!isset($param['VIEW'])) return ['error' => 200];
                if ($param['VIEW'] === '*') $param['VIEW'] = 1000000000;
                else $param['VIEW'] = in_array((int) $param['VIEW'], $com_c['view_select']) ? (int) $param['VIEW'] : $com_c['view'];

            //  параметры - начало выборки
                if (!isset($param['PAGE'])) return ['error' => 200];
                $return['page'] = (int) $param['PAGE'] > 0 ? (int) $param['PAGE'] : $com_c['page'];
                $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];

            //  запрос оценок
                $select = ['COUNT(DISTINCT `rv`.`id_target`) AS `total`'];
                $order_by = ['`total` DESC', '`title_text` ASC'];

            //  подготовка запроса
                $id_level_1 = "RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1`";
                $id_level_2 = "LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2`";
                $id_level_3 = "LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`";
                if ($param['FILTER']['id_level_1'] != null) {
                    $id_level_1 .= " AND `dcl_1`.`id` = '" . $param['FILTER']['id_level_1'] . "'";
                    
                    if ($param['FILTER']['id_level_2'] != null) {
                        $id_level_2 = "RIGHT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2`";
                        $id_level_2 .= " AND `dcl_2`.`id` = '" . $param['FILTER']['id_level_2'] . "'";
                        
                        if ($param['FILTER']['id_level_3'] != null) {
                            $id_level_3 = "RIGHT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`";
                            $id_level_3 .= " AND `dcl_3`.`id` = '" . $param['FILTER']['id_level_3'] . "'";
                        }
                    }
                }

                if (count($param['FILTER']['id_val']) == 0) $id_val = "WHERE `rv`.`id_val` = 0";
                else {
                    $temp = [];
                    foreach ($param['FILTER']['id_val'] as $e) {
                        if ($e == 0) $temp[] = "`rv`.`id_val` IS NULL";
                        else $temp[] = "`rv`.`id_val` = " . $e;
                    }
                    $id_val = "WHERE " . implode(' OR ', $temp);
                }

                $query_1 = "
                    SELECT
                        " . str_replace(['{{table}}', '{{field}}'], ['del', 'title'], $field) . ",
                        " . implode(", ", $select) . "
                ";
                $query_2 = "
                    FROM `dict_education_level` AS `del`
                    INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `del`.`id`
                    INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                    INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                ";
                $query_3 = "
                    " . $id_level_1 . "
                    " . $id_level_2 . "
                    " . $id_level_3 . "
                    " . $id_val . "
                    GROUP BY `title_text`
                    ORDER BY " . implode(", ", $order_by) . "
                ";

                if ($param['FILTER']['id_level'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['def', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_education_field` AS `def`
                        INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_field` = `def`.`id` AND `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_field'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['dec', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_education_class` AS `dec`
                        INNER JOIN `dict_link_class_level` AS `cl` ON `dec`.`id` = `cl`.`id_class` AND `cl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `cl`.`id_level` AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_class'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['ddi', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_item` AS `ddi`
                        INNER JOIN `rate_item` AS `ri` ON `ddi`.`id` = `ri`.`id_item` AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `dict_link_field_level` AS `fl` ON `ri`.`id_field` = `fl`.`id_field` AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_item'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['dds', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_section` AS `dds`
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_sec'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_subsection` AS `ddss`
                        INNER JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `ddss`.`id_sec` AND `dds`.`id` = '" . $param['FILTER']['id_sec'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec` AND `ddss`.`id` = `rv`.`id_subsec`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_subsec'] != null) {
                    $query_2 = "
                        FROM `dict_discipline_subsection` AS `ddss`
                        INNER JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `ddss`.`id_sec` AND `dds`.`id` = '" . $param['FILTER']['id_sec'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec` AND `ddss`.`id` = `rv`.`id_subsec` AND `rv`.`id_subsec` = '" . $param['FILTER']['id_subsec'] . "'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

            //  проверка наличия страницы
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $total = $res -> num_rows;
                $temp = ceil($total / $param['VIEW']);
                if ($temp < $return['page']) {
                    if ($temp == 0) $return['page'] = 1;
                    else $return['page'] = $temp;
                    $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];
                }
                $return['total_all'] = $total;

            //  запрос данных
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3 . " LIMIT " . $param['PAGE'] . ", " . $param['VIEW'] . ";");
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);

            //  подготовка ответа
                $total = [];
                $temp = [];
                foreach ($res as $row) {
                    if ($row['title_text'] == 'NULL') continue;
                    $return['items'][] = $row;
                }

            //  возврат
                return $return;
        }

    //  API: получение данных
        function view_targets_1_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'legend'        => [],
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => [
                        'id_level'  => ['data' => [], 'total' => 1],
                        'id_field'  => ['data' => [], 'total' => 1],
                        'id_item'   => ['data' => [], 'total' => 1],
                        'id_class'  => ['data' => [], 'total' => 1],
                        'id_sec'    => ['data' => [], 'total' => 1],
                        'id_subsec' => ['data' => [], 'total' => 1],
                        'id_target' => ['data' => [], 'total' => 1],
                        'id_level_1' => ['data' => [], 'total' => 1],
                        'id_level_2' => ['data' => [], 'total' => 1],
                        'id_level_3' => ['data' => [], 'total' => 1]
                    ]
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  подготовка фильтров
                $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                foreach ($param['FILTER'] as $k => $e) {
                    $param['FILTER'][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($e)));
                }

            //  проверка фильтров
                $return['dict']['id_level'] = view_get_dict_public('id_level', $param['FILTER']);
                if ($param['FILTER']['id_level'] != null) {
                    $return['dict']['id_field'] = view_get_dict_public('id_field', $param['FILTER']);
                    if ($param['FILTER']['id_field'] != null) {
                        $return['dict']['id_class'] = view_get_dict_public('id_class', $param['FILTER']);
                        if ($param['FILTER']['id_class'] != null) {
                            $return['dict']['id_item'] = view_get_dict_public('id_item', $param['FILTER']);
                            if ($param['FILTER']['id_item'] != null) {
                                $return['dict']['id_sec'] = view_get_dict_public('id_sec', $param['FILTER']);
                                if ($param['FILTER']['id_sec'] != null) {
                                    $return['dict']['id_subsec'] = view_get_dict_public('id_subsec', $param['FILTER']);
                                    if ($param['FILTER']['id_subsec'] != null) {
                                        $return['dict']['id_target'] = view_get_dict_public('id_target', $param['FILTER']);
                                    }
                                }
                            }
                        }
                    }
                }

                $return['dict']['id_level_1'] = view_get_dict_public('id_level_1', $param['FILTER']);
                if ($param['FILTER']['id_level_1'] != null) {
                    $return['dict']['id_level_2'] = view_get_dict_public('id_level_2', $param['FILTER']);
                    if ($param['FILTER']['id_level_2'] != null) {
                        $return['dict']['id_level_3'] = view_get_dict_public('id_level_3', $param['FILTER']);
                    }
                }

            //  подготовка запроса - локализации
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field = "
                    IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', 
                    `{{table}}`.`name_" . $com_c['lang']['cur'] . "`, 
                    CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`
                ";


            //  параметры - интервал выборки
                if (!isset($param['VIEW'])) return ['error' => 200];
                $param['VIEW'] = in_array((int) $param['VIEW'], $com_c['view_select']) ? (int) $param['VIEW'] : $com_c['view'];

            //  параметры - начало выборки
                if (!isset($param['PAGE'])) return ['error' => 200];
                $return['page'] = (int) $param['PAGE'] > 0 ? (int) $param['PAGE'] : $com_c['page'];
                $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];

            //  запрос оценок
                $select = [];
                $order_by = [];
                $query = "
                    SELECT `dcl`.`id`, `dcl`.`val`, `dcl`.`color`, " . str_replace(['{{table}}', '{{field}}'], ['dcl', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcl`
                    ORDER BY `dcl`.`val` DESC
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                if (count($res) == 0) return $return;
                foreach ($res as $row) {
                    $return['dict_val'][$row['id']] = $row;
                    $select[] = "SUM(IF (`dcv`.`id` = '" . $row['id'] . "', 1, 0)) AS `" . $row['id'] . "`";
                    $order_by[] = "`" . $row['id'] . "` DESC";
                }
                $select[] = "SUM(IF (`dcv`.`id` IS NULL, 1, 0)) AS `-`";

            //  подготовка запроса
                $id_level_1 = "INNER JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1`";
                $id_level_2 = "LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2`";
                $id_level_3 = "LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`";
                if ($param['FILTER']['id_level_1'] != null) {
                    $id_level_1 .= " AND `dcl_1`.`id` = '" . $param['FILTER']['id_level_1'] . "'";
                    
                    if ($param['FILTER']['id_level_2'] != null) {
                        $id_level_2 = "INNER JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2`";
                        $id_level_2 .= " AND `dcl_2`.`id` = '" . $param['FILTER']['id_level_2'] . "'";
                        
                        if ($param['FILTER']['id_level_3'] != null) {
                            $id_level_3 = "INNER JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3`";
                            $id_level_3 .= " AND `dcl_3`.`id` = '" . $param['FILTER']['id_level_3'] . "'";
                        }
                    }
                }

                $query_1 = "
                    SELECT
                        " . str_replace(['{{table}}', '{{field}}'], ['del', 'title'], $field) . ",
                        COUNT(DISTINCT `rv`.`id_target`) AS 'total_target',
                        " . implode(", ", $select) . "
                ";
                $query_2 = "
                    FROM `dict_education_level` AS `del`
                    INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `del`.`id`
                    INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                    INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                    " . $id_level_1 . "
                    " . $id_level_2 . "
                    " . $id_level_3 . "
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                ";
                $query_3 = "
                    GROUP BY `title_text`
                    ORDER BY " . implode(", ", $order_by) . "
                ";

                if ($param['FILTER']['id_level'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['def', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_education_field` AS `def`
                        INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_field` = `def`.`id` AND `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        " . $id_level_1 . "
                        " . $id_level_2 . "
                        " . $id_level_3 . "
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_field'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['dec', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_education_class` AS `dec`
                        INNER JOIN `dict_link_class_level` AS `cl` ON `dec`.`id` = `cl`.`id_class` AND `cl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `cl`.`id_level` AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        " . $id_level_1 . "
                        " . $id_level_2 . "
                        " . $id_level_3 . "
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_class'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['ddi', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_item` AS `ddi`
                        INNER JOIN `rate_item` AS `ri` ON `ddi`.`id` = `ri`.`id_item` AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `dict_link_field_level` AS `fl` ON `ri`.`id_field` = `fl`.`id_field` AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        INNER JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item`
                        " . $id_level_1 . "
                        " . $id_level_2 . "
                        " . $id_level_3 . "
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_item'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['dds', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_section` AS `dds`
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec`
                        " . $id_level_1 . "
                        " . $id_level_2 . "
                        " . $id_level_3 . "
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_sec'] != null) {
                    $query_1 = "
                        SELECT
                            " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'title'], $field) . ",
                            " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `dict_discipline_subsection` AS `ddss`
                        INNER JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `ddss`.`id_sec` AND `dds`.`id` = '" . $param['FILTER']['id_sec'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `dds`.`id_item` = `ri`.`id_item` AND `ri`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND `ri`.`id_field` = '" . $param['FILTER']['id_field'] . "' AND `ri`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND `ri`.`id_status` = '3'
                        INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `dds`.`id` = `rv`.`id_sec` AND `ddss`.`id` = `rv`.`id_subsec`
                        " . $id_level_1 . "
                        " . $id_level_2 . "
                        " . $id_level_3 . "
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    ";
                }

                if ($param['FILTER']['id_subsec'] != null) {
                    $target = '';
                    if ($param['FILTER']['id_target'] != null) $target = " AND `rv`.`id_target` = '" . $param['FILTER']['id_target'] . "'";
                    $query_1 = "
                        SELECT 
                        " . str_replace(['{{table}}', '{{field}}'], ['ddt', 'title'], $field) . ",
                        " . implode(", ", $select) . "
                    ";
                    $query_2 = "
                        FROM `rate_val` AS `rv`
                        " . $id_level_1 . "
                        " . $id_level_2 . "
                        " . $id_level_3 . "
                        LEFT JOIN `dict_discipline_target` AS `ddt` ON `rv`.`id_target` = `ddt`.`id`
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                            `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND 
                            `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "' AND 
                            `rv`.`id_subsec` = '" . $param['FILTER']['id_subsec'] . "'
                            " . $target . "
                    ";
                }

            //  проверка наличия страницы
                //echo $query_1 . " " . $query_2 . " " . $query_3;
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $total = $res -> num_rows;
                $temp = ceil($total / $param['VIEW']);
                if ($temp < $return['page']) {
                    if ($temp == 0) $return['page'] = 1;
                    else $return['page'] = $temp;
                    $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];
                }
                $return['total_all'] = $total;

            //  запрос данных
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3 . " LIMIT " . $param['PAGE'] . ", " . $param['VIEW'] . ";");
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);

            //  подготовка ответа
                $total = [];
                $temp = [];
                $tt = [];
                foreach ($res as $row) {
                    foreach ($row as $k => $e) {
                        if ($k == 'title_text') continue;
                        if ($k == 'total_target') {
                            $tt[$row['title_text']] = $row['total_target'];
                            continue;
                        }
                        $temp[$row['title_text']][$k] = $e;
                        if (!isset($total[$row['title_text']])) $total[$row['title_text']] = 0;
                        $total[$row['title_text']] += $e;
                    }
                }

                foreach ($temp as $k => $e) {
                    $arr = [];
                    foreach ($e as $kk => $ee) $arr[] = ['id_val' => $kk, 'value' => round((($ee * 100) / $total[$k]), 2)];
                    $return['items'][] = ['title' => $k, 'target' => $tt[$k], 'data' => $arr];
                }

            //  запрос легенды
                $query = "
                    SELECT
                        `dcv`.`val` AS `val`, `dcv`.`color` AS `color`, " . str_replace(['{{table}}', '{{field}}'], ['dcv', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcv`
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                foreach ($res as $row) $return['legend'][$row['val']] = $row;

            //  возврат
                return $return;
        }

    //  API: получение данных
        function view_targets_2_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'legend'        => [],
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => [
                        'id_level'  => ['data' => [], 'total' => 1],
                        'id_field'  => ['data' => [], 'total' => 1],
                        'id_item'   => ['data' => [], 'total' => 1],
                        'id_class'  => ['data' => [], 'total' => 1],
                        'id_sec'    => ['data' => [], 'total' => 1],
                        'id_subsec' => ['data' => [], 'total' => 1],
                        'id_target' => ['data' => [], 'total' => 1],
                        'id_level_1' => ['data' => [], 'total' => 1],
                        'id_level_2' => ['data' => [], 'total' => 1],
                        'id_level_3' => ['data' => [], 'total' => 1]
                    ]
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  подготовка фильтров
                $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                foreach ($param['FILTER'] as $k => $e) {
                    $param['FILTER'][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($e)));
                }

            //  проверка фильтров
                $return['dict']['id_level'] = view_get_dict_public('id_level', $param['FILTER']);
                if ($param['FILTER']['id_level'] != null) {
                    $return['dict']['id_field'] = view_get_dict_public('id_field', $param['FILTER']);
                    if ($param['FILTER']['id_field'] != null) {
                        $return['dict']['id_class'] = view_get_dict_public('id_class', $param['FILTER']);
                        if ($param['FILTER']['id_class'] != null) {
                            $return['dict']['id_item'] = view_get_dict_public('id_item', $param['FILTER']);
                            if ($param['FILTER']['id_item'] != null) {
                                $return['dict']['id_sec'] = view_get_dict_public('id_sec', $param['FILTER']);
                                if ($param['FILTER']['id_sec'] != null) {
                                    $return['dict']['id_subsec'] = view_get_dict_public('id_subsec', $param['FILTER']);
                                    if ($param['FILTER']['id_subsec'] != null) {
                                        $return['dict']['id_target'] = view_get_dict_public('id_target', $param['FILTER']);
                                    }
                                }
                            }
                        }
                    }
                }

            //  параметры - интервал выборки
                if (!isset($param['VIEW'])) return ['error' => 200];
                $param['VIEW'] = in_array((int) $param['VIEW'], $com_c['view_select']) ? (int) $param['VIEW'] : $com_c['view'];

            //  параметры - начало выборки
                if (!isset($param['PAGE'])) return ['error' => 200];
                $return['page'] = (int) $param['PAGE'] > 0 ? (int) $param['PAGE'] : $com_c['page'];
                $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];

            //  подготовка запроса - локализации
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field = "
                    IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', 
                    `{{table}}`.`name_" . $com_c['lang']['cur'] . "`, 
                    CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`
                ";

            //  запрос оценок
                $select = ["COUNT(DISTINCT `rv`.`id_target`) as 'total_target'"];
                $order_by = [];
                $query = "
                    SELECT `dcl`.`id`, `dcl`.`val`, `dcl`.`color`, " . str_replace(['{{table}}', '{{field}}'], ['dcl', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcl`
                    ORDER BY `dcl`.`val` DESC
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                if (count($res) == 0) return $return;
                foreach ($res as $row) {
                    $return['dict_val'][$row['id']] = $row;
                    $select[] = "SUM(IF (`dcv`.`id` = '" . $row['id'] . "', 1, 0)) AS `" . $row['id'] . "`";
                    $order_by[] = "`" . $row['id'] . "` DESC";
                }
                $select[] = "SUM(IF (`dcv`.`id` IS NULL, 1, 0)) AS `-`";

            //  параметры поиска
                $param['SEARCH'] = json_decode($param['SEARCH'], JSON_OBJECT_AS_ARRAY);
                $return['search'] = $param['SEARCH']['data'];
                $param['SEARCH']['data'] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($param['SEARCH']['data'])));
                $search = [];
                if (isset($param['SEARCH']['data']) && $param['SEARCH']['data'] != '') {
                    if (isset($param['SEARCH']['field']) && $param['SEARCH']['field'] == '') {
                        foreach ($com_c['search_fields'] as $k => $e) {
                            $search[] = "`" . $k . "`.`name_" . $com_c['lang']['cur'] . "` LIKE '%" . $param['SEARCH']['data'] . "%'";
                        }
                    }
                    else {
                        foreach ($com_c['search_fields'] as $k => $e) {
                            if ($k == $param['SEARCH']['field']) {
                                $search[] = "`" . $k . "`.`name_" . $com_c['lang']['cur'] . "` LIKE '%" . $param['SEARCH']['data'] . "%'";
                                break;
                            }
                        }
                    }
                }

            //  подготовка запроса
                $join = [
                    "LEFT JOIN `dict_discipline_section` AS `dds` ON `dds`.`id` = `rv`.`id_sec`",
                    "LEFT JOIN `dict_discipline_subsection` AS `ddss` ON `ddss`.`id` = `rv`.`id_subsec`",
                    "LEFT JOIN `dict_discipline_target` AS `ddt` ON `ddt`.`id` = `rv`.`id_target`",
                    "LEFT JOIN `dict_discipline_item` AS `ddi` ON `ddi`.`id` = `rv`.`id_item`"
                ];

                $level = [];
                if ($param['FILTER']['id_level_1'] != null) {
                    $level[] = "RIGHT JOIN `dict_criteria_level_1` AS `dcl_1` ON `dcl_1`.`id` = `rv`.`id_level_1` AND `dcl_1`.`id` = '" . $param['FILTER']['id_level_1'] . "'";
                    if ($param['FILTER']['id_level_2'] != null) {
                        $level[] = "RIGHT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id` = `rv`.`id_level_2` AND `dcl_2`.`id` = '" . $param['FILTER']['id_level_2'] . "'";
                        if ($param['FILTER']['id_level_3'] != null) {
                            $level[] = "RIGHT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id` = `rv`.`id_level_3` AND `dcl_3`.`id` = '" . $param['FILTER']['id_level_3'] . "'";
                        }
                    }
                }

                $query_1 = "
                    SELECT
                        " . str_replace(['{{table}}', '{{field}}'], ['dds', 'dds'], $field) . ",
                        " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'ddss'], $field) . ",
                        " . str_replace(['{{table}}', '{{field}}'], ['ddi', 'ddi'], $field) . ",
                        " . implode(", ", $select) . "
                    FROM `rate_val` AS `rv`
                    " . implode(" ", $join) . "
                    " . implode(" ", $level) . "
                ";
                $query_2 = "
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_status` = '3'
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    " . (count($search) != 0 ? " WHERE (" . implode(" OR ", $search) . ")" : "") . "
                ";
                $query_3 = "
                    GROUP BY `ddi_text`, `dds_text`, `ddss_text`
                    ORDER BY " . implode(", ", $order_by) . "
                ";

                if ($param['FILTER']['id_level'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        " . (count($search) != 0 ? "WHERE (" . implode(" OR ", $search) . ")" : "") . "
                    ";
                    
                }
                
                if ($param['FILTER']['id_field'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        " . (count($search) != 0 ? "WHERE (" . implode(" OR ", $search) . ")" : "") . "
                    ";
                }

                if ($param['FILTER']['id_class'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        " . (count($search) != 0 ? " AND (" . implode(" OR ", $search) . ")" : "") . "
                    ";
                }

                if ($param['FILTER']['id_item'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                            `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "'
                            " . (count($search) != 0 ? " AND (" . implode(" OR ", $search) . ")" : "") . "
                    ";                 
                }

                if ($param['FILTER']['id_sec'] != null) {
                    $query_2 = "
                        RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                        RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                        INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                        LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                        WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                            `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND 
                            `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "'
                            " . (count($search) != 0 ? " AND (" . implode(" OR ", $search) . ")" : "") . "
                    ";  
                } 

                if ($param['FILTER']['id_subsec'] != null) {
                    $target = '';
                    if ($param['FILTER']['id_target'] != null) $target = " AND `rv`.`id_target` = '" . $param['FILTER']['id_target'] . "'";
                    $query_2 = "
                    RIGHT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = '" . $param['FILTER']['id_level'] . "' AND `fl`.`id_field` = '" . $param['FILTER']['id_field'] . "'
                    RIGHT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level` AND `cl`.`id_class` = '" . $param['FILTER']['id_class'] . "'
                    INNER JOIN `rate_item` AS `ri` ON `ri`.`id_item` = `rv`.`id_item` AND `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                    LEFT JOIN `dict_criteria_val` AS `dcv` ON `dcv`.`id` = `rv`.`id_val`
                    WHERE `rv`.`id_class` = '" . $param['FILTER']['id_class'] . "' AND 
                        `rv`.`id_item` = '" . $param['FILTER']['id_item'] . "' AND 
                        `rv`.`id_sec` = '" . $param['FILTER']['id_sec'] . "' AND
                        `rv`.`id_subsec` = '" . $param['FILTER']['id_subsec'] . "' 
                        " . $target . "
                        " . (count($search) != 0 ? " AND (" . implode(" OR ", $search) . ")" : "") . "
                    "; 
                }

            //  запрос общего количества
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $return['total_all'] = count($res -> fetch_all(MYSQLI_ASSOC));

            //  проверка наличия страницы
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $total = count($res -> fetch_all(MYSQLI_ASSOC));
                $temp = ceil($total / $param['VIEW']);
                if ($temp < $return['page']) {
                    if ($temp == 0) $return['page'] = 1;
                    else $return['page'] = $temp;
                    $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];
                }
                $return['total_search'] = $total;

            //  запрос данных
                //echo $query_1 . " " . $query_2 . " " . $query_3;
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query_1 . " " . $query_2 . " " . $query_3 . " LIMIT " . $param['PAGE'] . ", " . $param['VIEW']);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);

            //  подготовка ответа
                $name = [];
                $total = [];
                $temp = [];
                $tt = [];
                foreach ($res as $row) {
                    $title = [
                        //$row['ddt_text'], 
                        $row['dds_text'],
                        $row['ddss_text'],
                        $row['ddi_text']
                    ];
                    $t = implode("<br />", $title);
                    foreach ($row as $k => $e) {
                        //if ($k == 'ddt_text') continue;
                        if ($k == 'dds_text') continue;
                        if ($k == 'ddss_text') continue;
                        if ($k == 'ddi_text') continue;
                        if ($k == 'total_target') {
                            $tt[$t] = $row[$k];
                            continue;
                        }
                        $temp[$t][$k] = $e;
                        if (!isset($total[$t])) $total[$t] = 0;
                        $total[$t] += $e;
                         
                    }
                    $name[$t] = $title;
                }
                foreach ($temp as $k => $e) {
                    $arr = [];
                    foreach ($e as $kk => $ee) $arr[] = ['total' => $ee, 'id_val' => $kk, 'value' => round((($ee * 100) / $total[$k]), 2)];
                    $return['items'][] = ['title' => $k, 'name' => $name[$k], 'target' => $tt[$k], 'data' => $arr];
                }

            //  запрос легенды
                $query = "
                    SELECT `dcv`.`val` AS `val`, `dcv`.`color` AS `color`, " . str_replace(['{{table}}', '{{field}}'], ['dcv', 'val'], $field) . "
                    FROM `dict_criteria_val` AS `dcv`
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                foreach ($res as $row) $return['legend'][$row['val']] = $row;

            //  возврат
                return $return;
        }

    //  API: запрос справочников
        function view_get_dict_public($type, $param) {
            //  переменные
                global $com_c, $com_s;
                $return = ['data' => [], 'total' => 1];
    
            //  подготовка запроса - локализации
                $field = [];
                $temp = [];
                foreach($com_c['lang']['suf'] as $s => $t) {
                    if ($s != $com_c['lang']['cur']) {
                        $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
                    }
                }
                $field[] = "IF (`{{table}}`.`name_" . $com_c['lang']['cur'] . "` <> '', ";
                $field[] = "`{{table}}`.`name_" . $com_c['lang']['cur'] . "`, ";
                $field[] = "CASE " . implode(' ', $temp) . " ELSE 'NULL' END)";
                $field = implode("", $field);

            //  подготовка запроса
                switch ($type) {
                    case 'id_level': {
                        $query = "
                            SELECT DISTINCTROW
                                `del`.`id` AS `id`, " . str_replace('{{table}}', 'del', $field) . " AS `text` 
                            FROM `dict_education_level` AS `del`
                            LEFT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `del`.`id`
                            LEFT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_class` = `ri`.`id_class`;
                        ";
                        break;
                    }
                    case 'id_field': {
                        $query = "
                            SELECT DISTINCTROW 
                                `def`.`id` AS `id`, " . str_replace('{{table}}', 'def', $field) . " AS `text` 
                            FROM `dict_education_field` AS `def`
                            LEFT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_field` = `def`.`id` AND `fl`.`id_level` = '" . $param['id_level'] . "' 
                            LEFT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_level` = `fl`.`id_level`
                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_class` = `ri`.`id_class`;
                        ";
                        break;
                    }
                    case 'id_class': {
                        $query = "
                            SELECT DISTINCTROW
                                `dec`.`id` AS `id`, " . str_replace('{{table}}', 'dec', $field) . " AS `text` 
                            FROM `dict_education_class` AS `dec`
                            LEFT JOIN `dict_link_class_level` AS `cl` ON `cl`.`id_class` = `dec`.`id` AND `cl`.`id_level` = '" . $param['id_level'] . "'
                            LEFT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `cl`.`id_level` AND `fl`.`id_field` = '" . $param['id_field'] . "'
                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = `fl`.`id_field` AND `ri`.`id_class` = `cl`.`id_class` AND `ri`.`id_status` = '3'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_class` = `ri`.`id_class`;
                        ";
                        break;
                    }
                    case 'id_item': {
                        $query = "
                            SELECT DISTINCTROW
                                `ddi`.`id` AS `id`, " . str_replace('{{table}}', 'ddi', $field) . " AS `text` 
                            FROM `dict_discipline_item` AS `ddi`
                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = '" . $param['id_field'] . "' AND `ri`.`id_class` = '" . $param['id_class'] . "' AND `ri`.`id_item` = `ddi`.`id` AND `ri`.`id_status` = '3'
                            LEFT JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_field` = `ri`.`id_field` AND `fl`.`id_level` = '" . $param['id_level'] . "'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_class` = `ri`.`id_class` AND `rv`.`id_item` = `ddi`.`id`;
                        ";
                        break;
                    }
                    case 'id_sec': {
                        $query = "
                            SELECT DISTINCTROW
                                `dds`.`id` AS `id`, " . str_replace('{{table}}', 'dds', $field) . " AS `text` 
                            FROM `dict_discipline_section` AS `dds`

                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = '" . $param['id_field'] . "' AND `ri`.`id_item` = '" . $param['id_item'] . "' AND `ri`.`id_class` = '" . $param['id_class'] . "' AND `ri`.`id_status` = '3'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_sec` = `dds`.`id` AND `rv`.`id_item` = `ri`.`id_item`
                        ";
                        break;
                    }
                    case 'id_subsec': {
                            $query = "
                            SELECT DISTINCTROW
                                `ddss`.`id` AS `id`, " . str_replace('{{table}}', 'ddss', $field) . " AS `text` 
                            FROM `dict_discipline_subsection` AS `ddss`

                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = '" . $param['id_field'] . "' AND `ri`.`id_item` = '" . $param['id_item'] . "' AND `ri`.`id_class` = '" . $param['id_class'] . "' AND `ri`.`id_status` = '3'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_subsec` = `ddss`.`id` AND `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_sec` = '" . $param['id_sec'] . "'
                        ";
                        break;
                    }
                    case 'id_target': {
                        $query = "
                            SELECT DISTINCTROW
                                `ddt`.`id` AS `id`, " . str_replace('{{table}}', 'ddt', $field) . " AS `text`
                            FROM `dict_discipline_target` AS `ddt`

                            INNER JOIN `rate_item` AS `ri` ON `ri`.`id_field` = '" . $param['id_field'] . "' AND `ri`.`id_item` = '" . $param['id_item'] . "' AND `ri`.`id_class` = '" . $param['id_class'] . "' AND `ri`.`id_status` = '3'
                            INNER JOIN `rate_val` AS `rv` ON `rv`.`id_target` = `ddt`.`id` AND `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_sec` = '" . $param['id_sec'] . "' AND `rv`.`id_subsec` = '" . $param['id_subsec'] . "'
                        ";
                        break;
                    }
                    case 'id_level_1': {
                        $query = "
                            SELECT DISTINCTROW
                                `dcl_1`.`id` AS `id`, " . str_replace('{{table}}', 'dcl_1', $field) . " AS `text` 
                            FROM `dict_criteria_level_1` AS `dcl_1`
                        ";
                        break;
                    }
                    case 'id_level_2': {
                        $query = "
                            SELECT DISTINCTROW
                                `dcl_2`.`id` AS `id`, " . str_replace('{{table}}', 'dcl_2', $field) . " AS `text`  
                            FROM `dict_criteria_level_2` AS `dcl_2`
                            WHERE `dcl_2`.`id_level_1` = '" . $param['id_level_1'] . "'
                        ";
                        break;
                    }
                    case 'id_level_3': {
                        $query = "
                            SELECT DISTINCTROW
                                `dcl_3`.`id` AS `id`, " . str_replace('{{table}}', 'dcl_3', $field) . " AS `text`  
                            FROM `dict_criteria_level_3` AS `dcl_3`
                            WHERE `dcl_3`.`id_level_2` = '" . $param['id_level_2'] . "'
                        ";
                        break;
                    }
                    case 'id_val': {
                        $query = "
                            SELECT `dcv`.`id` AS `id`, CONCAT( `dcv`.`val`, ' - '," . str_replace('{{table}}', 'dcv', $field) . ") AS `text` 
                            FROM `dict_criteria_val` AS `dcv`
                        ";
                        break;
                    }
                    default: break;
                }
            
            //  запрос данных
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                foreach ($res as $row) {
                    $return['data'][$row['id']] = [
                        'id'    => $row['id'],
                        'text'  => $row['text']
                    ];
                }
                if (isset($return['data'])) $return['total'] = count($return['data']);
                if ($return['total'] == 0) $return['total'] = 1;

            //  возврат
                return $return;

        }