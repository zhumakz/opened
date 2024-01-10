<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка инициализации параметров компонеты
        if (empty($com_c) || empty($com_s)) exit;

    //  параметры полей представления по-умолчанию
        $com_def = [
            'parn' => false,    //  признак родителя                        (false || true)
            'chil' => false,    //  признак потомка                         (false || true)
            'name' => false,    //  наименование                            (строка)
            'null' => false,    //  сообщение для пустых данных             (строка)
            'type' => false,    //  тип данных                              (false || @-filter)
            'view' => false,    //  корректировка стилей                    (false || строка)
            'sort' => false,    //  признак возможности сортировки          (false || true)
            'edit' => false,    //  признак редактирования                  (false || имя компоненты)
            'prep' => false,    //  наименование поля редактирования        (false || строка)
            'info' => false,    //  подсказка для элемента формы            (false || строка)
            'data' => false,    //  имя источника данных                    (строка)
            'dict' => false,    //  признак словаря                         (false || имя словаря)
            'lang' => false,    //  признак мультиязычности                 (false || имя пареметра без суффикса локализации)
            'ajax' => 333,      //  лимит статического словаря              (число)
            'need' => false,    //  признак обязательности значения         (false || true)
            'uniq' => false,    //  признак уникальности значения           (false || true)
            'rely' => false,    //  признак зависомости данных              (false || имя данных)
            'oper' => []        //  массив допустимых действий              (массив)
        ];

    //  проверка параметров
        foreach ($com_def as $def => $value) {
            foreach ($com_c['head'] as $k => $e) {
                if (!isset($e[$def])) $com_c['head'][$k][$def] = $value;
            }
        }

    //  операции по-умолчанию
        $com_oper_def = [
            'add'   => false,
            'edit'  => false,
            'del'   => false
        ];

    //  проверка операция
        foreach ($com_oper_def as $def => $value) {
            if (!isset($com_c['oper'][$def])) $com_c['oper'][$def] = $value;
        }
        if (isset($com_c['head']['@-action'])) {
            if (!empty($com_c['head']['@-action']['oper'])) {
                foreach ($com_c['head']['@-action']['oper'] as $i => $oper) {
                    if (!$com_c['oper'][$oper]) unset($com_c['head']['@-action']['oper'][$i]);
                }
            }
            if (empty($com_c['head']['@-action']['oper'])) unset($com_c['head']['@-action']);
        }

    //  API: получение данных
        function dict_get_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [
                    'page'          => 0,
                    'items'         => [],
                    'items_dict'    => [],
                    'total_all'     => 0,
                    'total_search'  => 0,
                    'search'        => '',
                    'dict'          => []
                ];
                $sql = [
                    'field' => [],
                    'table' => [],
                    'where' => [],
                    'join' => [],
                    'sort' => []
                ];

            //  запрос справочников
                foreach ($com_s['dict'] as $k => $d) {
                    if (!$d) continue;
                    $param['DICT'] = $k;
                    $temp = dict_get_dict_public($param);
                    if (isset($temp['error'])) ['error' => $temp['error']];
                    $return['dict'][$k] = $temp;
                }


            //  параметры фильтрации
                $filter = [$com_s['base'] . ".`id` <> '0'"];
                //  !!! модификация для DICT-RATE-ITEM
                    if ($com_c['name'] == 'dict-rate-item' && in_array($_SESSION['ROLE'], ['expert'])) {
                        $filter[] = "`id_user` = '" . $_SESSION['ID_USER'] . "'";
                    }

                if (!empty($com_c['filter'])) {
                    $param['FILTER'] = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                    foreach ($com_c['filter'] as $f => $e) {
                        if (isset($com_c['struct'][$f])) {
                            if ($param['FILTER'][$f] == null) $filter[] = $com_s['base'] . ".`" . $f . "` IS NULL";
                            else $filter[] = $com_s['base'] . ".`" . $f . "` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($param['FILTER'][$f]))) . "'";
                        }
                    }
                }

            //  сборка фильтра
                $filter = "(" . implode(" AND ", $filter) . ")";

            //  параметры - интервал выборки
                if (!isset($param['VIEW'])) return ['error' => 200];
                $param['VIEW'] = in_array((int) $param['VIEW'], $com_c['view_select']) ? (int) $param['VIEW'] : $com_c['view'];

            //  параметры - начало выборки
                if (!isset($param['PAGE'])) return ['error' => 200];
                $return['page'] = (int) $param['PAGE'] > 0 ? (int) $param['PAGE'] : $com_c['page'];
                $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];

            //  параметры запроса
                $table_link = [];
                foreach ($com_c['struct'] as $s => $i) {
                    if (isset($com_c['head'][$s]) && $com_c['head'][$s]['dict']) {  //  словари
                        $dict =& $com_s['dict'][$com_c['head'][$s]['data']];
                        if (isset($dict['table_link'])) {
                            if (!in_array($dict['table_link'], $table_link)) {
                                $table_link[] = $dict['table_link'];
                                $sql['join'][] = "LEFT JOIN " . $dict['table_link'] . " ON " . $dict['table_link'] . "." . $dict['link']['La'] . " = " . $com_s['base'] . "." . $dict['link']['Ta'];
                            }
                            $sql['join'][] = "LEFT JOIN " . $dict['table_data'] . " ON " . $dict['table_data'] . "." . $dict['link']['Tb'] . " = " . $dict['table_link'] . "." . $dict['link']['Lb'];
                            $sql['field'][] = $dict['table_link'] . "." . $dict['link']['Lb'] . " AS `" . $s . "`";
                        }
                        else {
                            $sql['field'][] = $com_s['base'] . "." . $dict['link']['Lb'] . " AS `" . $s . "`";
                            $sql['join'][] = "LEFT JOIN " . $dict['table_data'] . " ON " . $dict['table_data'] . "." . $dict['link']['Tb'] . " = " . $com_s['base'] . "." . $dict['link']['Lb'];
                        }
                        $sql['sort'][] = $dict['table_data'] . ".`name_" . $com_c['lang']['cur'] . "` ASC";

                        if (isset($dict['field'])) {
                            $f = $dict['field'];
                            if (substr($f, -1) == '_') {
                                $temp = [];
                                foreach($com_c['lang']['suf'] as $suf => $t) {
                                    if ($suf != $com_c['lang']['cur']) {
                                        $temp[] = "WHEN " . $dict['table_data'] . ".`" . $f . $suf . "` <> '' THEN CONCAT('" . mb_strtoupper($suf) . ": ', " . $dict['table_data'] . ".`" . $f . $suf . "`)";
                                    }
                                }
                                $sql['field'][] = "IF (" . $dict['table_data'] . ".`" . $f . $com_c['lang']['cur'] . "` <> '', " . $dict['table_data'] . ".`" . $f . $com_c['lang']['cur'] . "`, CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `" . $com_c['head'][$s]['data'] . "_text`";
                            }
                            else $sql['field'][] = $d['table_data'] . ".`" . $f . "` AS `" . $f . "_" . $com_c['head'][$s]['data'] . "`";
                        }
                    }
                    else {
                        $sql['field'][] = $com_s['base'] . ".`" . $s . "` AS `" . $s . "`";
                    }
                }
                $sql['table'][$com_s['base']] = $com_s['base'];
                $sql['where'][] = $filter;

            //  параметры - поиск
                if (!empty($param['SEARCH'])) $param['SEARCH'] = json_decode($param['SEARCH'], JSON_OBJECT_AS_ARRAY);
                $temp = [];

                if (isset($param['SEARCH']['data']) && $param['SEARCH']['data'] != '') {

                    $return['search'] = $param['SEARCH']['data'];
                    $param['SEARCH']['data'] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($param['SEARCH']['data'])));

                    foreach ($com_c['struct'] as $s => $i) {
                        
                        //  проверка условия наличия поиска
                        if (!isset($com_c['head'][$s])) continue;
                        if (!isset($com_c['search_fields'][$s])) continue;
                        if ($param['SEARCH']['field'] != '' && $param['SEARCH']['field'] != $s) continue;

                        if (isset($com_c['head'][$s]) && $com_c['head'][$s]['dict']) {
                            
                            $dict =& $com_s['dict'][$com_c['head'][$s]['data']];
                            $where = [];
                            foreach ($dict['text'] as $f) {
                                if (substr($f, -1) == '_') $where[] = "UPPER(" . $dict['table_data'] . ".`" . $f . $com_c['lang']['cur'] . "`) LIKE '%" . $param['SEARCH']['data'] . "%'";
                                else $where[] = "UPPER(" . $dict['table_data'] . ".`" . $f . "`) LIKE '%" . $param['SEARCH']['data'] . "%'";
                            }
                            $query = [];
                            if (isset($dict['table_link'])) {
                                $query[] = "SELECT DISTINCT " . $dict['table_link'] . "." . $dict['link']['La'];
                                $query[] = "FROM " . $dict['table_data'] . ", " . $dict['table_link'];
                                $query[] = "WHERE " . $dict['table_data'] . "." . $dict['link']['Tb'] . " = " . $dict['table_link'] . "." . $dict['link']['Lb'] . " AND (" . implode(" OR ", $where) . ")";
                                $temp[] = $com_s['base'] . ".`id` IN (" . implode(' ', $query) . ")";
                            }
                            else {
                                $query[] = "SELECT DISTINCT " . $dict['table_data'] . "." . $dict['link']['Tb'];
                                $query[] = "FROM " . $dict['table_data'] . ", " . $com_s['base'];
                                $query[] = "WHERE " . $dict['table_data'] . "." . $dict['link']['Tb'] . " = " . $com_s['base'] . "." . $dict['link']['Lb'] . " AND (" . implode(" OR ", $where) . ")";
                                $temp[] = $com_s['base'] . "." . $dict['link']['Lb'] . " IN (" . implode(' ', $query) . ")";
                            }
                        }
                        else {
                            $temp[] = "UPPER(" . $com_s['base'] . ".`" . $s . "`) LIKE '%" . $param['SEARCH']['data'] . "%'";
                        }
                    }
                    $param['SEARCH'] = " WHERE " . $filter . " AND (" . implode(" OR ", $temp) . ")";
                    $sql['where'][] = "(" . implode(" OR ", $temp) . ")";
                }
                else $param['SEARCH'] = " WHERE " . $filter;

            //  проверка наличия страницы
                $query = "SELECT COUNT(`id`) AS `TOTAL` FROM " . $com_s['base'] . $param['SEARCH'] . ";";
                //echo $query;
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                $temp = ceil($res[0]['TOTAL'] / $param['VIEW']);
                if ($temp < $return['page']) {
                    if ($temp == 0) $return['page'] = 1;
                    else $return['page'] = $temp;
                    $param['PAGE'] = ($return['page'] - 1) * $param['VIEW'];
                }
                $return['total_search'] = $res[0]['TOTAL'];

            //  сортировка основная
                if (empty($param['SORT'])) $param['SORT'] = $com_c['sort'];
                else $param['SORT'] = json_decode($param['SORT'], JSON_OBJECT_AS_ARRAY);
                $temp = [];
                foreach ($com_c['head'] as $k => $e) {
                    if (isset($param['SORT'][$k]) && $e['sort']) {
                        $temp[] = $com_s['base'] . ".`" . $k . "` " . ($param['SORT'][$k] ? "ASC" : "DESC");
                    }
                }
            //  сортировка второстепенная
                if (count($sql['sort'])) $temp = array_merge($temp, $sql['sort']);
            //  сборка сортировки
                if (count($temp) == 0) $param['SORT'] = '';
                else $param['SORT'] = "ORDER BY " . implode(', ', $temp);

            //  подготовка параметров запроса
                $sql['where'] = count($sql['where']) ? $sql['where'] = " AND " . implode(" AND ", $sql['where']) : '';
                $sql['join'] = count($sql['join']) ? implode(" ", $sql['join']) : '';
                $sql['item'] = "
                    SELECT * 
                    FROM (
                        SELECT DISTINCT " . $com_s['base'] . ".`id` 
                        FROM " . implode(", ", $sql['table']) . "
                        " . $sql['join'] . "
                        WHERE 1 " . $sql['where'] . "
                        " . $param['SORT'] . " 
                        LIMIT " . $param['PAGE'] . ", " . $param['VIEW'] . "
                        ) 
                    temp_table
                ";

            //  запрос данных
                $query = "
                    SELECT " . implode(', ', $sql['field']) . "
                    FROM " . implode(", ", $sql['table']) . "
                    " . $sql['join'] . "
                    WHERE " . $com_s['base'] . ".`id` IN (" . $sql['item'] . ") 
                    " . $param['SORT'] . ";
                ";
                //echo $query;
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                else {
                    $res = $res -> fetch_all(MYSQLI_ASSOC);
                    $temp = [];
                    foreach ($res as $row) {
                        foreach ($com_c['struct'] as $s => $i) {
                            if (!empty($com_c['head'][$s]['dict'])) {

                                if (is_array($i)) {
                                    if (!isset($temp[$row['id']][$s])) $temp[$row['id']][$s] = [];
                                    if ($row[$s] != null) {
                                        $temp[$row['id']][$s][] = $row[$s];
                                        
                                    }
                                }
                                else $temp[$row['id']][$s] = $row[$s];

                                if (isset($row[$s . '_text']) && !empty($row[$s])) {
                                    $return['items_dict'][$s][$row[$s]] = $row[$s . '_text'];
                                }

                            } 
                            else $temp[$row['id']][$s] = $row[$s];
                        }
                    }
                    foreach ($temp as $e) $return['items'][] = $e;
                }

            //  запрос общего количества
                $query = "SELECT COUNT(`id`) AS `TOTAL` FROM " . $com_s['base'] . " WHERE " . $filter . ";";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                $return['total_all'] = $res[0]['TOTAL'];

            //  возврат
                return $return;
        }

    //  API: запрос справочников
        function dict_get_dict_public($param) {
            //  переменные
                global $com_c, $com_s;
                $d = $com_s['dict'][$param['DICT']];
                $return = [
                    'data' => [],
                    'total' => 0
                ];

            //  подготовка параметров запроса
                $field = [];
                $field_sort = '';
                foreach ($d['text'] as $f) {
                    if (substr($f, -1) == '_') {
                        $temp = [];
                        foreach($com_c['lang']['suf'] as $s => $t) {
                            if ($s != $com_c['lang']['cur']) {
                                $temp[] = "WHEN " . $d['table_data'] . ".`" . $f . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', " . $d['table_data'] . ".`" . $f . $s . "`)";
                            }
                        }
                        $f = $f . $com_c['lang']['cur'];
                        $field[] = "IF (" . $d['table_data'] . ".`" . $f . "` <> '', " . $d['table_data'] . ".`" . $f . "`, CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `text`";
                    }
                    else {
                        if (!in_array($f, ['id', 'text'])) $field[] = $d['table_data'] . ".`" . $f . "` AS `text`";
                        else $field[] = $d['table_data'] . ".`" . $f . "`";
                    }
                    if ($field_sort == '') $field_sort = " ORDER BY " . $d['table_data'] . ".`" . $f . "` ASC";
                }
                
                $join = '';
                $is_rely = !empty($d['rely']) && isset($d['rely']['select']);
                if ($is_rely) {
                    $field[] = $d['rely']['select']['field'];
                    $join = " " . implode(" ", $d['rely']['select']['join']);
                }

            //  запрет на служебные записи
                $where = [$d['table_data'] . ".`id` <> '0'"];

            //  прямые условия выборки
                if (!empty($d['where'])) $where[] = implode(" AND ", $d['where']);

            //  фильтрация
                if (!empty($d['filter'])) {
                    $filter = json_decode($param['FILTER'], JSON_OBJECT_AS_ARRAY);
                    foreach ($d['filter'] as $f => $e) {
                        if (empty($filter[$e])) return $return;
                        else $where[] = $d['table_data'] . ".`" . $e . "` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($filter[$e]))) . "'";
                    
                    }
                }

            //  условия динамического запроса
                if (isset($_REQUEST['FIND'])) {
                    $_REQUEST['FIND'] = trim($_REQUEST['FIND']);
                    if ($_REQUEST['FIND'] == '') return $return;
                    $where[] = $d['table_data'] . ".`name_ru` LIKE '%" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string(mb_strtoupper(trim($_REQUEST['FIND']))) . "%'";
                }
                $where = " WHERE (" . implode(" AND ", $where) . ")";

            //  запрос количества
                $query = "SELECT COUNT(DISTINCT " . $d['table_data'] . ".`id`) AS `total` FROM " . $d['table_data'] . $join . $where . ";";
                //echo $query;
                $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $query = $query -> fetch_all(MYSQLI_ASSOC);
                $return['total'] = $query[0]['total'];

            //  справочник работает в режиме AJAX
                if (empty($param['FIND']) && $return['total'] > $com_c['head'][$param['DICT']]['ajax']) return $return;

            //  запрос данных
                $query = "SELECT DISTINCTROW " . implode(', ', $field) . " FROM " . $d['table_data'] . $join . $where . $field_sort . ";";
                //echo $query;
                $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                echo $GLOBALS['@']['MYSQL']['LINK'] -> error;
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                $return['data'] = $query -> fetch_all(MYSQLI_ASSOC);
                if ($is_rely) {
                    $temp = [];
                    $rely = $com_c['head'][$param['DICT']]['rely'];
                    foreach ($return['data'] as $row) {
                        if (isset($temp[$row['id']])) $temp[$row['id']][$rely][] = $row[$rely];
                        else {
                            $temp[$row['id']] = $row;
                            $temp[$row['id']][$rely] = [$row[$rely]];
                        }
                    }
                    $return['data'] = array_values($temp);
                }
            
            //  возврат
                return $return;
        }

    //  API: добавление/редактирование данных
        function dict_save_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $param['DATA'] = json_decode($param['DATA'], JSON_OBJECT_AS_ARRAY);

            //  проверка доступности операции
                if (empty($param['DATA']['id'])) {
                    if (!$com_c['oper']['add']) return ['error' => 200];
                }
                else {
                    if (!$com_c['oper']['edit']) return ['error' => 200];
                }

            //  обработка входящих параметров
                foreach ($com_c['struct'] as $s => $i) {
                    if (!isset($param['DATA'][$s]) && $param['DATA'][$s] != null) return ['error' => 200];
                    if (is_array($param['DATA'][$s])) {
                        foreach ($param['DATA'][$s] as $k => $e) {
                            if (mb_strlen($e) > $GLOBALS['@']['MYSQL']['DATA']) {
                                $param['DATA'][$s][$k] = mb_substr(trim($e), 0, $GLOBALS['@']['MYSQL']['DATA']) . '...';
                                $param['DATA'][$s][$k] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($param['DATA'][$s][$k]);
                            }
                        }
                    }
                    else {
                        if (mb_strlen($param['DATA'][$s]) > $GLOBALS['@']['MYSQL']['DATA']) {
                            $param['DATA'][$s] = mb_substr(trim($param['DATA'][$s]), 0, $GLOBALS['@']['MYSQL']['DATA']) . '...';
                            $param['DATA'][$s] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($param['DATA'][$s]);
                        }
                    }
                }

            //  проверка необходимых полей
                eval('$valid = ' . $com_s['struct_valid_php'] . ';');
                if (!$valid) return ['error' => 200];

            //  старт транзакции
                $GLOBALS['@']['MYSQL']['LINK'] -> begin_transaction();

            //  проверка дубликатоов
                $temp = [];
                foreach ($com_c['head'] as $hi => $h) {
                    if (empty($h['uniq'])) continue;
                    if (!empty($h['lang'])) {
                        $l = [];
                        foreach ($com_c['lang']['suf'] as $si => $s) {
                            if ($param['DATA'][$h['lang'] . $si] != '') {
                                $l[] = $com_s['base'] . ".`" . $h['lang'] . $si . "` = '" . $param['DATA'][$h['lang'] . $si] . "'";
                            }
                        }
                        $temp[] = "(" . implode(" OR ", $l) . ")";
                    }
                    else $temp[] = $com_s['base'] . ".`" . $h['data'] . "` = '" . $param['DATA'][$hi] . "'";    
                }
                if (count($temp)) {
                    if (!empty($param['DATA']['id'])) $temp[] = $com_s['base'] . ".`id` <> " . $param['DATA']['id'];
                    $query = "SELECT " . $com_s['base'] . ".`id` FROM " . $com_s['base'] . " WHERE " . implode(" AND ", $temp). ";";
                    //echo $query;
                    $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                    if ($GLOBALS['@']['MYSQL']['LINK'] -> error) return ['error' => 300];
                    if ($query -> num_rows) return ['error' => 900];
                }

            //  параметры фильтрации
                $filter = ["1"];
                //  !!! модификация для DICT-RATE-ITEM
                if ($com_c['name'] == 'dict-rate-item' && in_array($_SESSION['ROLE'], ['expert'])) {
                    $filter[] = "`id_user` = '" . $_SESSION['ID_USER'] . "'";
                }
                $filter = implode(" AND ", $filter);

            //  сохранение данных
                if (empty($param['DATA']['id'])) { // добавить данные
                    $field = [];
                    $data = [];
                    foreach ($com_c['struct'] as $s => $i) {
                        if (empty($com_c['head'][$s])) $dict = [];
                        else $dict =& $com_s['dict'][$com_c['head'][$s]['data']];
                        if ($s != 'id' && empty($dict['table_link'])) {
                            $field[] = "`" . $s . "`";
                            $data[] = empty($param['DATA'][$s]) ? "NULL" : "'" . $param['DATA'][$s] . "'";
                        }
                    }
                    $query = "INSERT INTO " . $com_s['base'] . " (" . implode(', ', $field) . ") VALUES (" . implode(', ', $data) . ");";
                    //echo $query;
                    $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                    if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {
                        $GLOBALS['@']['MYSQL']['LINK'] -> rollback();
                        if ($GLOBALS['@']['MYSQL']['LINK'] -> errno == 1062) return ['error' => 900];
                        return ['error' => 300];
                    }
                    $insert_id = $GLOBALS['@']['MYSQL']['LINK'] -> insert_id;
                }
                else { // изменение данных
                    $query = [];
                    $where = [];
                    foreach ($com_c['struct'] as $s => $i) {
                        if (empty($com_c['head'][$s])) $dict = [];
                        else $dict =& $com_s['dict'][$com_c['head'][$s]['data']];
                        if (empty($dict['table_link'])) {
                            if ($s == 'id') {
                                $where[] = "`" . $s . "` = '" . $param['DATA'][$s] . "'";
                                $insert_id = $param['DATA'][$s];
                            }
                            else {
                                if (!empty($com_s['struct_no_edit']) && in_array($s, $com_s['struct_no_edit'])) continue;
                                $query[] = "`" . $s . "` = " . (empty($param['DATA'][$s]) ? "NULL" : "'" . $param['DATA'][$s] . "'");
                            }
                        }
                    }
                    $query = "UPDATE " . $com_s['base'] . " SET " . implode(', ', $query) . " WHERE " . implode(', ', $where) . " AND " . $filter . ";";
                    //echo $query;
                    $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                    if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {
                        if ($GLOBALS['@']['MYSQL']['LINK'] -> errno == 1062) return ['error' => 900];
                        return ['error' => $GLOBALS['@']['MYSQL']['LINK'] -> errno];
                    }
                }

            //  подготовка параметров сохранения связей
                $temp = [[$insert_id]];
                $sql_field = [];
                $sql_no_del = [];  
                foreach ($com_c['struct'] as $s => $i) { 
                    if (empty($com_c['head'][$s])) continue;
                    $dict =& $com_s['dict'][$com_c['head'][$s]['data']];
                    if ($s != 'id' && !empty($dict['table_link'])) {
                        //  подготовка данных для сохранения
                            if (count($sql_field) == 0) $sql_field[] = $dict['link']['La'];
                            if (!is_array($param['DATA'][$s])) $param['DATA'][$s] = [$param['DATA'][$s]];
                            if (count($param['DATA'][$s])) {
                                $arr = [];
                                foreach ($temp as $a) {
                                    foreach ($param['DATA'][$s] as $b) $arr[] = array_merge($a, [$b]);
                                }
                                $temp = $arr;
                            }
                            $sql_field[] = $dict['link']['Lb'];
                            $sql_no_del[] = $dict['link']['Lb'] . " NOT IN ('" . implode("', '", $param['DATA'][$s]) . "')";
                    }
                }

            // сохранение связей
                if (count($sql_field) != 0) {
                    //  удаление предыдущей версии связей
                        $query = "DELETE FROM " . $dict['table_link'] . " WHERE " . $dict['link']['La'] . " = '" . $insert_id . "' AND (" . implode(" OR ", $sql_no_del) . ");";
                        //echo $query;
                        $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                        if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {
                            if ($GLOBALS['@']['MYSQL']['LINK'] -> errno == 1451 || $GLOBALS['@']['MYSQL']['LINK'] -> errno == 11111) $return = ['error' => 301];
                            else $return = ['error' => 300];
                            $GLOBALS['@']['MYSQL']['LINK'] -> rollback();
                            return $return;
                        }

                    //  сохранение новой версии связей
                        foreach ($temp as $k => $e) {
                            $arr = [];
                            for ($i = 0; $i < count($e); $i++) $arr[] = $sql_field[$i] . " = '" . $e[$i] . "'";
                            $query = "SELECT COUNT(*) `total` FROM " . $dict['table_link'] . " WHERE " . implode(" AND ", $arr) . ";";
                            $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                            if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {
                                $GLOBALS['@']['MYSQL']['LINK'] -> rollback();
                                return ['error' => 300];
                            }
                            $query = $query -> fetch_all(MYSQLI_ASSOC);
                            if ($query[0]['total'] == 0) {
                                $query = "INSERT INTO " . $dict['table_link'] . " SET " . implode(", ", $arr) . " ON DUPLICATE KEY UPDATE " . array_shift($arr) . ";";
                                $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {
                                    $GLOBALS['@']['MYSQL']['LINK'] -> rollback();
                                    return ['error' => 300];
                                }
                            }
                        }
                }

            //  фиксация транзакции
                $GLOBALS['@']['MYSQL']['LINK'] -> commit();

            //  возврат
                return dict_get_data_public($param);
        }

    //  API: удаление нескольких данных
        function dict_delete_select_data_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [];

            //  проверка доступности операции
                if (!$com_c['oper']['del']) return ['error' => 200];

            //  обработка входящих параметров
                if (empty($param['DATA'])) return false;
                $param['DATA'] = json_decode($param['DATA'], JSON_OBJECT_AS_ARRAY);

            //  выполнение запроса
                foreach ($param['DATA'] as $e) {
                    $temp = dict_delete_data_public(['DATA' => $e], true);
                    if (is_array($temp)) {
                        $return[] = [
                            'id'    => $e,
                            'error' => $temp['error']
                        ];
                    }
                }

            //  возврат
                return array_merge(['delete_select' => $return], dict_get_data_public($param));
        }

    //  API: удаление данных
        function dict_delete_data_public($param, $return_api = false) {
            //  переменные
                global $com_c, $com_s;

            //  проверка доступности операции
                if (!$com_c['oper']['del']) return ['error' => 200];

            //  обработка входящих параметров
                if (empty($param['DATA'])) return false;

            //  старт транзакции
                $GLOBALS['@']['MYSQL']['LINK'] -> begin_transaction();

            //  выполнение запроса
                $query = "DELETE FROM " . $com_s['base'] . " WHERE `id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($param['DATA']) . "';";
                $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {
                    if ($GLOBALS['@']['MYSQL']['LINK'] -> errno == 1451 || $GLOBALS['@']['MYSQL']['LINK'] -> errno == 1001) return ['error' => 301];
                    else return ['error' => 300];
                }

            //  фиксация транзакции
                $GLOBALS['@']['MYSQL']['LINK'] -> commit();

            //  возврат
                if ($return_api) return true;
                else return dict_get_data_public($param);
        }

    //  API: удаление нескольких данных
        function dict_sync_criteria_public($param) {
            //  переменные
                global $com_c, $com_s;
                $return = [];

            //  проверка доступности операции
                if (!$com_c['oper']['sync']) return ['error' => 200];

            //  установка блокировки таблицы
                $GLOBALS['@']['MYSQL']['LINK'] -> query("LOCK TABLES `rave_val` WRITE;");

            //  удаление отсутствующих критериев
                $query = "
                    DELETE FROM `rate_val`
                    WHERE `id` IN (SELECT `rv`.`id`
                        FROM `rate_val` AS `rv`
                        WHERE CONCAT(
                            IF(`rv`.`id_level_1` IS NOT NULL, `rv`.`id_level_1`, '0'), '.',
                            IF(`rv`.`id_level_2` IS NOT NULL, `rv`.`id_level_2`, '0'), '.',
                            IF(`rv`.`id_level_3` IS NOT NULL, `rv`.`id_level_3`, '0')
                        ) NOT IN (
                            SELECT CONCAT(
                                IF(`dcl_1`.`id` IS NOT NULL, `dcl_1`.`id`, '0'), '.',
                                IF(`dcl_2`.`id` IS NOT NULL, `dcl_2`.`id`, '0'), '.',
                                IF(`dcl_3`.`id` IS NOT NULL, `dcl_3`.`id`, '0')
                            )
                            FROM `dict_criteria_level_1` AS `dcl_1`
                            LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id_level_1` = `dcl_1`.`id`
                            LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id_level_2` = `dcl_2`.`id`
                        )
                    );
                ";
                $GLOBALS['@']['MYSQL']['LINK'] -> query($query);

            //  определение новых критериев
                $query = "
                    SELECT 
                        `dcl_1`.`id` AS 'id_level_1', 
                        `dcl_2`.`id` AS 'id_level_2', 
                        `dcl_3`.`id` AS 'id_level_3'

                    FROM `dict_criteria_level_1` AS `dcl_1`
                    LEFT JOIN `dict_criteria_level_2` AS `dcl_2` ON `dcl_2`.`id_level_1` = `dcl_1`.`id`
                    LEFT JOIN `dict_criteria_level_3` AS `dcl_3` ON `dcl_3`.`id_level_2` = `dcl_2`.`id`

                    WHERE CONCAT(
                        IF(`dcl_1`.`id` IS NOT NULL, `dcl_1`.`id`, '0'), '.',
                        IF(`dcl_2`.`id` IS NOT NULL, `dcl_2`.`id`, '0'), '.',
                        IF(`dcl_3`.`id` IS NOT NULL, `dcl_3`.`id`, '0')
                    ) NOT IN (
                        SELECT CONCAT(
                            IF(`rv`.`id_level_1` IS NOT NULL, `rv`.`id_level_1`, '0'), '.',
                            IF(`rv`.`id_level_2` IS NOT NULL, `rv`.`id_level_2`, '0'), '.',
                            IF(`rv`.`id_level_3` IS NOT NULL, `rv`.`id_level_3`, '0')
                        )
                        FROM `rate_val` AS `rv`
                    );
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $criteria = $res -> fetch_all(MYSQLI_ASSOC);

            //  запрос целей
                $query = "
                    SELECT DISTINCTROW `id_item`, `id_class`, `id_sec`, `id_subsec`, `id_target`
                    FROM `rate_val`;
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $target = $res -> fetch_all(MYSQLI_ASSOC);

            //  снятие блокировки таблицы
                $GLOBALS['@']['MYSQL']['LINK'] -> query("UNLOCK TABLES;");

            //  добавление новых критериев в оценку
                $data = [];
                $total = 0;
                foreach ($target as $t) {
                    foreach ($criteria as $c) {
                        if (empty($c['id_level_2'])) $c['id_level_2'] = "NULL";
                        if (empty($c['id_level_3'])) $c['id_level_3'] = "NULL";
                        $data[] = "(" . implode(",", array_merge($t, $c)) . ")";
                    }
                    $total++;

                    if ($total > 100) {
                        $query = "
                            INSERT INTO `rate_val` (`id_item`, `id_class`, `id_sec`, `id_subsec`, `id_target`, `id_level_1`, `id_level_2`, `id_level_3`)
                            VALUES " . implode(", ", $data) . ";
                        ";
                        $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                        $data = [];
                        $total = 0;
                    }
                }
                $query = "
                    INSERT INTO `rate_val` (`id_item`, `id_class`, `id_sec`, `id_subsec`, `id_target`, `id_level_1`, `id_level_2`, `id_level_3`)
                    VALUES " . implode(", ", $data) . ";
                ";
                $GLOBALS['@']['MYSQL']['LINK'] -> query($query);

            //  возврат
                return $return;
        }