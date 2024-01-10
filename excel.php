<?php

    //  инициализация
        ob_start();
        $GLOBALS['@']['ENGINE'] = true;
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/config.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/mysql.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/language.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/session.php';

    //  определение типа и доступа
        $exit = false;
        if (!in_array($_REQUEST['type'], ['rate', 'v-1', 'v-2', 'v-3', 'v-4'])) $exit = true;
        if ($_REQUEST['type'] == 'rate' && !in_array($_SESSION['ROLE'], ['admin', 'editor', 'expert'])) $exit = true;
        if ($exit) {
            ob_start();
            header('HTTP/1.1 404 Not Found');
            include_once '404.php';
            exit;
        }

    //  подготовка локализации
        $suf = $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['title'];
        $cur = $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'];
        $field = [];
        $temp = [];
        foreach($suf as $s => $t) {
            if ($s != $cur) {
                $temp[] = "WHEN `{{table}}`.`name_" . $s . "` <> '' THEN CONCAT('" . mb_strtoupper($s) . ": ', `{{table}}`.`name_" . $s . "`)";
            }
        }
        $field[] = "IF (`{{table}}`.`name_" . $cur . "` <> '', ";
        $field[] = "`{{table}}`.`name_" . $cur . "`, ";
        $field[] = "CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `{{field}}_text`";
        $field = implode("", $field);
    
    //  массив цветов для шапки
        $arr_color = [
            'F0F8FF', 'F0FFF0', 'FFF0F5', 'F0FFFF', 'F8F8FF',
            'F5F5F5', 'FFF5EE', 'F5F5DC', 'FFFFF0', 'FAEBD7'
        ];

    //  подключение библиотеки excel
        define('DS', DIRECTORY_SEPARATOR);
        $path = implode(DS, ['@-library', 'PHPExcel-1.8', 'Classes']);
        require_once __DIR__ . DS . implode(DS, [$path, 'PHPExcel.php']);
        require_once __DIR__ . DS . implode(DS, [$path, 'PHPExcel', 'Writer', 'Excel2007.php']);
        require_once __DIR__ . DS . implode(DS, [$path, 'PHPExcel', 'IOFactory.php']);

    //  инициализация excel
        $xls = new PHPExcel();
        $xls -> setActiveSheetIndex(0);
        $sheet = $xls -> getActiveSheet();
        $sheet -> setTitle('Лист 1');
        $alphabet = range('A', 'Z');
        $set = range('A', 'Z');
        foreach ($set as $e1) foreach ($set as $e2) $alphabet[] = $e1 . $e2;
        foreach ($set as $e1) foreach ($set as $e2) foreach ($set as $e3) $alphabet[] = $e1 . $e2 . $e3;

    //  стиль
        $style_head = [
            'font'  => [
                'size'  => 8
            ],
            'fill'  => [
                'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                'color' => ['rgb' => 'FFFFFF']
            ],
            'borders' => [
                'allborders' => [
                    'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                    'color' => ['rgb' => '000000']
                ]
            ]
        ];

    //  FUN вывод легенды
        function create_legend($col, $row, $data) {
            global $sheet, $alphabet;
            $leg = [
                mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['005']) . ':',
                '−  ... ' . $GLOBALS['@']['LANG']['DATA']['excel']['007']
            ];
            foreach ($data as $v) $leg[] = $v['val'] . ' ... ' . $v['val_text'];
            $sheet -> setCellValueByColumnAndRow(0, $row, implode(chr(10), $leg));
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setWrapText(true);
            $sheet -> getStyle($alphabet[$col] . $row) -> applyFromArray(['font' => ['size' => 8]]);
            $sheet -> getRowDimension($row) -> setRowHeight(12 * count($leg));
        }

    //  FUN вывод шапки - блок А
        function create_head_a($criteria) {
            global $sheet, $alphabet, $arr_color, $style_head;
            $color = 0;
            $c1_col = 4;
            $c1_row = 1;

            //  часть 1
                $sheet -> setCellValueByColumnAndRow(0, 1, mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['001']));
                $sheet -> setCellValueByColumnAndRow(1, 1, mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['002']));
                $sheet -> setCellValueByColumnAndRow(2, 1, '№');
                $sheet -> setCellValueByColumnAndRow(3, 1, mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['003']));
                $sheet -> mergeCells($alphabet[0] . 1 . ':' . $alphabet[0] . 3); // +++
                $sheet -> mergeCells($alphabet[1] . 1 . ':' . $alphabet[1] . 3); // +++
                $sheet -> mergeCells($alphabet[2] . 1 . ':' . $alphabet[2] . 3); // +++
                $sheet -> mergeCells($alphabet[3] . 1 . ':' . $alphabet[3] . 3); // +++
                $sheet -> getStyle($alphabet[0] . '1:' . $alphabet[3] . '3') -> getAlignment() -> setWrapText(true);
                $sheet -> getStyle($alphabet[0] . '1:' . $alphabet[3] . '3') -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet -> getStyle($alphabet[0] . '1:' . $alphabet[3] . '3') -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet -> getStyle($alphabet[0] . '1:' . $alphabet[3] . '3') -> applyFromArray($style_head);
                $sheet -> getColumnDimension('A') -> setWidth(40);
                $sheet -> getColumnDimension('B') -> setWidth(40);
                $sheet -> getColumnDimension('C') -> setWidth(4);
                $sheet -> getColumnDimension('D') -> setWidth(40);

            //  часть 2
                foreach ($criteria as $k1 => $c1) {

                    // стиль
                    $style_head['fill']['color']['rgb'] = $arr_color[$color++];
                    if ($color >= count($arr_color)) $color = 0;

                    // C1
                    $c1_height = 2; // +++
                    $c1_width = 0;  // +++

                    // C2
                    $c2_col = 0;    // +++

                    if (isset($c1['data'])) {

                        // C1
                        $c1_height = 0;

                        // C2
                        $c2_height = 1;
                        $c2_col = $c1_col;
                        $c2_row = 2;

                        foreach ($c1['data'] as $k2 => $c2) {
                            
                            $c2_height = 1;
                            $c2_width = 0;  // +++
                            $c3_col = 0;    // +++

                            if (isset($c2['data'])) {

                                // C1
                                $c1_height = 0;

                                // C2
                                $c2_height = 0;
                                $c2_row = 2;

                                // C3
                                $c3_col = $c2_col;
                                $c3_row = 3;

                                foreach ($c2['data'] as $k3 => $c3) {
                                    $sheet -> getStyle($alphabet[$c3_col] . $c3_row) -> applyFromArray($style_head);
                                    $sheet -> getStyle($alphabet[$c3_col] . $c3_row) -> getAlignment() -> setWrapText(true);
                                    $sheet -> getStyle($alphabet[$c3_col] . $c3_row) -> getAlignment() -> setTextRotation(90);
                                    $sheet -> getStyle($alphabet[$c3_col] . $c3_row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                                    $sheet -> getStyle($alphabet[$c3_col] . $c3_row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                                    $sheet -> setCellValueByColumnAndRow($c3_col, $c3_row, $c3['name']);
                                    //$sheet -> getColumnDimensionByColumn($c3_col) -> setAutoSize(true);
                                    $sheet -> getRowDimension($c3_row) -> setRowHeight(100);
                                    $c1_width++;
                                    $c2_width++;
                                    $c3_col++;
                                }
                                $c1_width--;
                                $c2_width--;
                            }

                            $sheet -> getStyle($alphabet[$c2_col] . $c2_row) -> applyFromArray($style_head);
                            $sheet -> getStyle($alphabet[$c2_col] . $c2_row) -> getAlignment() -> setWrapText(true);
                            $sheet -> getStyle($alphabet[$c2_col] . $c2_row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                            $sheet -> getStyle($alphabet[$c2_col] . $c2_row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                            $sheet -> setCellValueByColumnAndRow($c2_col, $c2_row, $c2['name']);
                            
                            if (!$c2_width) {
                                $sheet -> getStyle($alphabet[$c2_col] . $c2_row) -> getAlignment() -> setTextRotation(90);
                                //$sheet -> getColumnDimensionByColumn($c2_col) -> setAutoSize(true);
                                $sheet -> getRowDimension($c2_row) -> setRowHeight(100);
                            }
                            $sheet -> mergeCells($alphabet[$c2_col] . $c2_row . ':' . $alphabet[$c2_col + $c2_width] . ($c2_row + $c2_height));
                            $sheet -> getStyle($alphabet[$c2_col] . $c2_row . ':' . $alphabet[$c2_col + $c2_width] . ($c2_row + $c2_height)) -> applyFromArray($style_head);
                            $c1_width++;
                            $c2_col = $c3_col ? $c3_col : $c2_col + 1;
                        }
                        $c1_width--;
                    }

                    // C1
                    $sheet -> getStyle($alphabet[$c1_col] . $c1_row) -> applyFromArray($style_head);
                    $sheet -> getStyle($alphabet[$c1_col] . $c1_row) -> getAlignment() -> setWrapText(true);
                    $sheet -> getStyle($alphabet[$c1_col] . $c1_row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet -> getStyle($alphabet[$c1_col] . $c1_row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $sheet -> setCellValueByColumnAndRow($c1_col, $c1_row, $c1['name']);
                    
                    if (!$c1_width) {
                        $sheet -> getStyle($alphabet[$c1_col] . $c1_row) -> getAlignment() -> setTextRotation(90);
                        //$sheet -> getColumnDimensionByColumn($c1_col) -> setAutoSize(true);
                        $sheet -> getRowDimension($c1_row) -> setRowHeight(100);
                    }
                    $sheet -> mergeCells($alphabet[$c1_col] . $c1_row . ':' . $alphabet[$c1_col + $c1_width] . ($c1_row + $c1_height));
                    $sheet -> getStyle($alphabet[$c1_col] . $c1_row . ':' . $alphabet[$c1_col + $c1_width] . ($c1_row + $c1_height)) -> applyFromArray($style_head);
                    $c1_col = $c2_col ? $c2_col : $c1_col + 1;

                }

            return --$c1_col;
        }

    //  FUN вывод данных - блок A
        function create_data_a($target, $criteria, $val, $excel, $s_row, $ss_row, $tar_row) {
            global $sheet, $alphabet;
            $number = 1;

            $style = [
                'fill'  => [
                    'type'  => PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'font' => [
                    'size' => 10,
                    'bold' => true
                ],
            ];

            foreach ($target as $ks => $s) {
                foreach ($s['data'] as $kss => $ss) {
                    foreach ($ss['data'] as $kt => $t) {
                        $sheet -> setCellValueByColumnAndRow(2, $tar_row, strval($number));
                        $sheet -> setCellValueByColumnAndRow(3, $tar_row, $t['name']);

                        $col = 4;
                        foreach ($criteria as $k1 => $c1) {
                            if (!isset($c1['data'])) {
                                $id = @$excel[$s['name']][$ss['name']][$t['name']][$k1];
                                $sheet -> setCellValueByColumnAndRow($col, $tar_row, $id ? $val[$id]['val'] : '-');
                                if ($id) {
                                    $style['fill']['color'] = ['rgb' => $val[$id]['color']];
                                    $sheet -> getStyle($alphabet[$col] . $tar_row) -> applyFromArray($style);
                                }
                                $col++;
                                continue;
                            }
                            foreach ($c1['data'] as $k2 => $c2) {
                                if (!isset($c2['data'])) {
                                    $id = @$excel[$s['name']][$ss['name']][$t['name']][$k1][$k2];
                                    $sheet -> setCellValueByColumnAndRow($col, $tar_row, $id ? $val[$id]['val'] : '-');
                                    if ($id) {
                                        $style['fill']['color'] = ['rgb' => $val[$id]['color']];
                                        $sheet -> getStyle($alphabet[$col] . $tar_row) -> applyFromArray($style);
                                    }
                                    $col++;
                                    continue;
                                }
                                foreach ($c2['data'] as $k3 => $c3) {
                                    $id = @$excel[$s['name']][$ss['name']][$t['name']][$k1][$k2][$k3];
                                    $sheet -> setCellValueByColumnAndRow($col, $tar_row, $id ? $val[$id]['val'] : '-');
                                    if ($id) {
                                        $style['fill']['color'] = ['rgb' => $val[$id]['color']];
                                        $sheet -> getStyle($alphabet[$col] . $tar_row) -> applyFromArray($style);
                                    }
                                    $col++;
                                }
                            }
                        }

                        $tar_row++;
                        $number++;

                    }
                    $sheet -> setCellValueByColumnAndRow(1, $ss_row, $ss['name']);
                    $sheet -> mergeCells($alphabet[1] . $ss_row . ':' . $alphabet[1] . ($tar_row - 1));
                    $ss_row = $tar_row;
                }

                $sheet -> setCellValueByColumnAndRow(0, $s_row, $s['name']);
                $sheet -> mergeCells($alphabet[0] . $s_row . ':' . $alphabet[0] . ($tar_row - 1));
                $s_row = $tar_row;
            }
            $tar_row--;
            $sheet -> getStyle($alphabet[0] . '5:' . $alphabet[$col - 1] . $tar_row) -> getAlignment() -> setWrapText(true);
            $sheet -> getStyle($alphabet[0] . '5:' . $alphabet[$col - 1] . $tar_row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet -> getStyle($alphabet[2] . '5:' . $alphabet[2] . $tar_row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet -> getStyle($alphabet[4] . '5:' . $alphabet[$col - 1] . $tar_row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet -> getStyle($alphabet[0] . '5:' . $alphabet[$col - 1] . $tar_row) -> applyFromArray([
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ]
            ]);
            $sheet -> getStyle($alphabet[0] . '5:' . $alphabet[3] . $tar_row) -> applyFromArray([
                'font' => [
                    'size' => 8
                ]
            ]);

        }

    //  FUN вывод шапки - блок B
        function create_head_b($row, $title, $data) {
            global $sheet, $alphabet, $arr_color, $style_head;

            $col = 0;
            $style_head['font']['bold'] = true;
            $style_head['fill']['color'] = ['rgb' => 'DDDDDD'];
            $sheet -> getColumnDimension($alphabet[$col]) -> setWidth(60);
            $sheet -> mergeCells($alphabet[$col] . $row . ':' . $alphabet[$col] . ($row + 1));
            $sheet -> getStyle($alphabet[$col] . $row . ':' . $alphabet[$col] . ($row + 1)) -> applyFromArray($style_head);
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet -> setCellValueByColumnAndRow($col++, $row, mb_strtoupper($title));
            $sheet -> mergeCells($alphabet[$col] . $row . ':' . $alphabet[$col + count($data)] . $row);
            $sheet -> getStyle($alphabet[$col] . $row . ':' . $alphabet[$col + count($data)] . $row) -> applyFromArray($style_head);
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            
            $sheet -> setCellValueByColumnAndRow($col, $row, mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['006']));
            $row++;
            uasort($data, function ($a, $b) { return ($a['val'] > $b['val']) ? -1 : 1; });
            foreach ($data as $e) {
                $style_head['fill']['color'] = ['rgb' => $e['color']];
                $sheet -> getStyle($alphabet[$col] . $row) -> applyFromArray($style_head);
                $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet -> setCellValueByColumnAndRow($col++, $row, $e['val']);
            }
            $style_head['fill']['color'] = ['rgb' => 'FFFFFF'];
            $sheet -> getStyle($alphabet[$col] . $row) -> applyFromArray($style_head);
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
            $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
            $sheet -> setCellValueByColumnAndRow($col, $row, '−');
            
            return ++$row;
        }

    //  FUN вывод данных - блок B
        function create_data_b($row, $data) {
            global $sheet, $alphabet;

            $style = [
                'borders' => [
                    'allborders' => [
                        'style' => PHPExcel_Style_Border::BORDER_THIN,
                        'color' => ['rgb' => '000000']
                    ]
                ],
                'font' => [
                    'size' => 10
                ],
            ];      
            foreach ($data['items'] as $e) {
                $style['font']['size'] = 8;
                $sheet -> getStyle($alphabet[0] . $row) -> applyFromArray($style);
                $sheet -> getStyle($alphabet[0] . $row) -> getAlignment() -> setWrapText(true);
                $sheet -> setCellValueByColumnAndRow(0, $row, $e['title']);
                $col = 1;
                $style['font']['size'] = 10;
                foreach ($e['data'] as $val) {
                    $sheet -> getStyle($alphabet[$col] . $row) -> applyFromArray($style);
                    $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $sheet -> getStyle($alphabet[$col] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                    $sheet -> setCellValueByColumnAndRow($col++, $row, str_replace('.', ',', $val['value']));
                }
                $row++;
            }
        }

    //  FUN вывод фильтров
        function create_filter($col, $row, $merge, $filter) {
            global $sheet, $alphabet, $field;

            $data = [];

            if (!empty($filter['id_level'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['del', 'name_ru'], $field) . "
                    FROM `dict_education_level` AS `del`
                    WHERE `del`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_level']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['010'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_field'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['def', 'name_ru'], $field) . "
                    FROM `dict_education_field` AS `def`
                    WHERE `def`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_field']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['011'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_class'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['dec', 'name_ru'], $field) . "
                    FROM `dict_education_class` AS `dec`
                    WHERE `dec`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_class']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['012'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_item'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['ddi', 'name_ru'], $field) . "
                    FROM `dict_discipline_item` AS `ddi`
                    WHERE `ddi`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_item']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['013'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_sec'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['dds', 'name_ru'], $field) . "
                    FROM `dict_discipline_section` AS `dds`
                    WHERE `dds`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_sec']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['014'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_subsec'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'name_ru'], $field) . "
                    FROM `dict_discipline_subsection` AS `ddss`
                    WHERE `ddss`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_subsec']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['015'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_target'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['ddt', 'name_ru'], $field) . "
                    FROM `dict_discipline_target` AS `ddt`
                    WHERE `ddt`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_target']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['016'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_level_1'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['dcl1', 'name_ru'], $field) . "
                    FROM `dict_criteria_level_1` AS `dcl1`
                    WHERE `dcl1`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_level_1']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['017'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_level_2'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['dcl2', 'name_ru'], $field) . "
                    FROM `dict_criteria_level_2` AS `dcl2`
                    WHERE `dcl2`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_level_2']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['018'],
                    'data' => $res[0][0]
                ];
            }

            if (!empty($filter['id_level_3'])) {
                $query = "
                    SELECT " . str_replace(['{{table}}', '{{field}}'], ['dcl3', 'name_ru'], $field) . "
                    FROM `dict_criteria_level_3` AS `dcl3`
                    WHERE `dcl3`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($filter['id_level_3']) . "'
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $data[] = [
                    'title' => $GLOBALS['@']['LANG']['DATA']['excel']['019'],
                    'data' => $res[0][0]
                ];
            }

            foreach ($data as $e) {
                $sheet -> mergeCells($alphabet[$col + 1] . $row . ':' . $alphabet[$merge] . $row);
                $sheet -> getStyle($alphabet[$col] . $row . ':' . $alphabet[$col + 1] . $row) -> applyFromArray(['font' => ['size' => 8]]);
                $sheet -> setCellValueByColumnAndRow($col, $row, mb_strtoupper($e['title']));
                $sheet -> setCellValueByColumnAndRow($col + 1, $row, $e['data']);
                $row++;
            }

            return $row;
        }

    //  тип excel
        if ($_REQUEST['type'] == 'rate') {
            //  проверка параметров
                if (empty($_REQUEST['id']) || empty($_REQUEST['class'])) exit;
            //  подготовка параметров
                $_REQUEST['id'] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_REQUEST['id']);
                $_REQUEST['class'] = $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_REQUEST['class']);

            //  формирование имени файла
                $query = "
                    SELECT 
                        " . str_replace(['{{table}}', '{{field}}'], ['ddi', 'name_ru'], $field) . ",
                        " . str_replace(['{{table}}', '{{field}}'], ['dec', 'name_ru'], $field) . "
                    FROM
                        `dict_discipline_item` AS `ddi`,
                        `dict_education_class` AS `dec`
                    WHERE 
                        `ddi`.`id` = '" . $_REQUEST['id'] . "' AND
                        `dec`.`id` = '" . $_REQUEST['class'] . "';
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all();
                $file_name = $res[0][0] . ' ' . $res[0][1] . ' (' . date('d.m.Y') . ')';

            //  запрос целей
                $query = "
                    SELECT DISTINCTROW
                        `dds`.`id` AS `id_sec`, " . str_replace(['{{table}}', '{{field}}'], ['dds', 'id_sec'], $field) . ", 
                        `ddss`.`id` AS `id_subsec`, " . str_replace(['{{table}}', '{{field}}'], ['ddss', 'id_subsec'], $field) . ",
                        `ddt`.`id` AS `id_target`, " . str_replace(['{{table}}', '{{field}}'], ['ddt', 'id_target'], $field) . "
                    FROM
                        `rate_val` AS `rv`,
                        `dict_discipline_section` AS `dds`,
                        `dict_discipline_subsection` AS `ddss`,
                        `dict_discipline_target` AS `ddt`
                    WHERE 
                        `rv`.`id_item` = '" . $_REQUEST['id'] . "' AND
                        `rv`.`id_class` = '" . $_REQUEST['class'] . "' AND
                        `rv`.`id_target` = `ddt`.`id` AND
                        `rv`.`id_subsec` = `ddss`.`id` AND
                        `rv`.`id_sec` = `dds`.`id`
                    ORDER BY
                        `id_sec`, `id_subsec`, `id_target` ASC
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all(MYSQLI_ASSOC);

                $target = [];
                foreach ($res as $row) {
                    $target[$row['id_sec']]['name'] = $row['id_sec_text'];
                    $target[$row['id_sec']]['data'][$row['id_subsec']]['name'] = $row['id_subsec_text'];
                    $target[$row['id_sec']]['data'][$row['id_subsec']]['data'][$row['id_target']]['name'] = $row['id_target_text'];
                }

                if (count($target) == 0) {
                    echo $GLOBALS['@']['LANG']['DATA']['excel']['004'];
                    exit;
                }

            //  запрос критериев
                $query = "
                    SELECT DISTINCTROW
                                `cl_1`.`id` AS `id_level_1`, " . str_replace(['{{table}}', '{{field}}'], ['cl_1', 'id_level_1'], $field) . ", 
                                `cl_2`.`id` AS `id_level_2`, " . str_replace(['{{table}}', '{{field}}'], ['cl_2', 'id_level_2'], $field) . ",
                                `cl_3`.`id` AS `id_level_3`, " . str_replace(['{{table}}', '{{field}}'], ['cl_3', 'id_level_3'], $field) . "
                    FROM        `dict_criteria_level_1` AS `cl_1` 
                    LEFT JOIN   `dict_criteria_level_2` AS `cl_2` ON `cl_1`.`id` = `cl_2`.`id_level_1` 
                    LEFT JOIN   `dict_criteria_level_3` AS `cl_3` ON `cl_2`.`id` = `cl_3`.`id_level_2` 
                    INNER JOIN	`rate_val` AS `rv` ON 
                                `rv`.`id_level_1` = `cl_1`.`id` AND 
                               (`rv`.`id_level_2` = `cl_2`.`id` OR `rv`.`id_level_2` IS NULL) AND 
                               (`rv`.`id_level_3` = `cl_3`.`id` OR `rv`.`id_level_3` IS NULL) AND 
                                `rv`.`id_item` = '" . $_REQUEST['id'] . "' AND
                                `rv`.`id_class` = '" . $_REQUEST['class'] . "';
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                $criteria = [];
                foreach ($res as $row) {
                    $criteria[$row['id_level_1']]['name'] = $row['id_level_1_text'];
                    if (empty($row['id_level_2'])) continue;
                    $criteria[$row['id_level_1']]['data'][$row['id_level_2']]['name'] = $row['id_level_2_text'];
                    if (empty($row['id_level_3'])) continue;
                    $criteria[$row['id_level_1']]['data'][$row['id_level_2']]['data'][$row['id_level_3']]['name'] = $row['id_level_3_text'];
                }

            //  запрос оценок
                $query = "
                    SELECT 
                            `dcv`.`id` AS `id`,
                            `dcv`.`val` AS `val`,
                            `dcv`.`color` AS `color`, 
                            " . str_replace(['{{table}}', '{{field}}'], ['dcv', 'dcv'], $field) . "
                    FROM    `dict_criteria_val` AS `dcv`;
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $res = $res -> fetch_all(MYSQLI_ASSOC);
                $val = [];
                foreach ($res as $row) {
                    $val[$row['id']] = [
                        'val'       => $row['val'],
                        'color'     => $row['color'],
                        'val_text'  => $row['dcv_text']
                    ];
                }

            //  запрос данных
                $query = "
                    SELECT  * 
                    FROM    `rate_val`
                    WHERE   `id_item` = '" . $_REQUEST['id'] . "' AND
                            `id_class` = '" . $_REQUEST['class'] . "';
                ";
                $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                $data = $res -> fetch_all(MYSQLI_ASSOC);

            //  подготовка данных
                $excel = [];
                foreach ($data as $e) {
                    $sec = $target[$e['id_sec']]['name'];
                    $sub = $target[$e['id_sec']]['data'][$e['id_subsec']]['name'];
                    $tar = $target[$e['id_sec']]['data'][$e['id_subsec']]['data'][$e['id_target']]['name'];
                    if (!empty($e['id_level_3'])) $excel[$sec][$sub][$tar][$e['id_level_1']][$e['id_level_2']][$e['id_level_3']] = $e['id_val'];
                    else {
                        if (!empty($e['id_level_2'])) $excel[$sec][$sub][$tar][$e['id_level_1']][$e['id_level_2']] = $e['id_val'];
                        else $excel[$sec][$sub][$tar][$e['id_level_1']] = $e['id_val'];
                    }
                }

            //  генерация шапки
                $width = create_head_a($criteria);

            //  фиксация шапки
                $sheet -> freezePane('E4');

            //  легенда
                $sheet -> mergeCells($alphabet[0] . 4 . ':' . $alphabet[$width] . 4);
                create_legend(0, 4, $val);

            //  вывод дерева целей и значений
                create_data_a($target, $criteria, $val, $excel, 5, 5, 5);
        }

        if ($_REQUEST['type'] == 'v-1') {
            //  проверка параметров
                if (empty($_REQUEST['FILTER'])) exit;
                $filter = json_decode($_REQUEST['FILTER'], true);
                if (empty($filter['id_level']) || empty($filter['id_field']) || empty($filter['id_class']) || empty($filter['id_item'])) exit;

            //  получение данных
                include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-target.php';
                ob_end_clean();
                $_REQUEST['VIEW'] = '*';
                $_REQUEST['PAGE'] = 1;
                $data = view_target_get_data_public($_REQUEST);
                if (isset($data['error'])) exit;

            //  имя файла
                $file_name = array_pop($data['dict']['id_item']['data'])['text'] . ' ' . array_pop($data['dict']['id_class']['data'])['text'] . ' (' . date('d.m.Y') . ')';

            //  подготовка целей
                $target = [];
                foreach ($data['items'] as $row) {
                    $target[$row['id_sec_id']]['name'] = $row['id_sec'];
                    $target[$row['id_sec_id']]['data'][$row['id_subsec_id']]['name'] = $row['id_subsec'];
                    $target[$row['id_sec_id']]['data'][$row['id_subsec_id']]['data'][$row['id_target_id']]['name'] = $row['id_target'];
                }

            //  подготовка критериев
                $criteria = [];
                foreach ($data['items'] as $row) {
                    $criteria[$row['id_level_1_id']]['name'] = $row['id_level_1'];
                    if (empty($row['id_level_2_id'])) continue;
                    $criteria[$row['id_level_1_id']]['data'][$row['id_level_2_id']]['name'] = $row['id_level_2'];
                    if (empty($row['id_level_3_id'])) continue;
                    $criteria[$row['id_level_1_id']]['data'][$row['id_level_2_id']]['data'][$row['id_level_3_id']]['name'] = $row['id_level_3'];
                }

                ksort($criteria);
                foreach ($criteria as $k => $e) {
                    if (isset($e['data'])) {
                        ksort($criteria[$k]['data']);
                        foreach ($e['data'] as $kk => $ee) {
                            if (isset($ee['data'])) {
                                ksort($criteria[$k]['data'][$kk]['data']);
                            } 
                        }
                    } 
                }

            //  подготовка оценок
                $val = [];
                foreach ($data['legend'] as $k => $e) {
                    $val[$k] = [
                        'val'       => $e['val'],
                        'color'     => $e['color'],
                        'val_text'  => $e['val_text']
                    ];
                }

            //  подготовка данных
                $excel = [];
                $excel = [];
                foreach ($data['items'] as $e) {
                    $sec = $e['id_sec'];
                    $sub = $e['id_subsec'];
                    $tar = $e['id_target'];
                    if (!empty($e['id_level_3_id'])) $excel[$sec][$sub][$tar][$e['id_level_1_id']][$e['id_level_2_id']][$e['id_level_3_id']] = $e['id_val_id'];
                    else {
                        if (!empty($e['id_level_2_id'])) $excel[$sec][$sub][$tar][$e['id_level_1_id']][$e['id_level_2_id']] = $e['id_val_id'];
                        else $excel[$sec][$sub][$tar][$e['id_level_1_id']] = $e['id_val_id'];
                    }
                }

            //  генерация шапки
                $width = create_head_a($criteria);

            //  фиксация шапки
                $sheet -> freezePane('E4');

            //  легенда
                $sheet -> mergeCells($alphabet[0] . 4 . ':' . $alphabet[$width] . 4);
                create_legend(0, 4, $val);

            //  вывод дерева целей и значений
                create_data_a($target, $criteria, $val, $excel, 5, 5, 5);
        }

        if ($_REQUEST['type'] == 'v-2') {
            //  получение данных
                include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-target.php';
                ob_end_clean();
                $_REQUEST['VIEW'] = '*';
                $_REQUEST['PAGE'] = 1;
                $data = view_val_get_data_public($_REQUEST);
                if (isset($data['error']) || !count($data['items'][0]['data'])) exit;

            //  имя файла
                $file_name = $GLOBALS['@']['LANG']['DATA']['excel']['100'] . ' (' . date('d.m.Y') . ')';
            
            //  вывод легенды
                $row = 1;
                $sheet -> mergeCells($alphabet[0] . $row . ':' . $alphabet[count($data['legend']) + 1] . $row);
                create_legend(0, $row, $data['legend']);
                $row = $row + 2;

            //  вывод фильтров
                $filter = json_decode($_REQUEST['FILTER'], true);
                $row = create_filter(0, $row, count($data['items'][0]['data']), $filter);
                $title = $GLOBALS['@']['LANG']['DATA']['excel']['010'];
                if (!empty($filter['id_level'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['011'];
                if (!empty($filter['id_field'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['012'];
                if (!empty($filter['id_class'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['013'];
                if (!empty($filter['id_item'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['014'];
                if (!empty($filter['id_sec'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['015'];
                if (!empty($filter['id_subsec'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['016'];
                $row++;   

            //  вывод шапки
                $row = create_head_b($row, $title, $data['legend']);

            //  фиксация шапки
                //$sheet -> freezePane($alphabet[0] . $row);

            //  вывод данных
                create_data_b($row, $data);

        }

        if ($_REQUEST['type'] == 'v-3') {
            //  получение данных
                include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-target.php';
                ob_end_clean();
                $data = view_criteria_get_data_public($_REQUEST);
                if (isset($data['error']) || !count($data['items'])) exit;

            //  имя файла
                $file_name = $GLOBALS['@']['LANG']['DATA']['excel']['101'] . ' (' . date('d.m.Y') . ')';
            
            //  вывод легенды
                $row = 1;
                $sheet -> mergeCells($alphabet[0] . $row . ':' . $alphabet[count($data['legend']) + 1] . $row);
                create_legend(0, $row, $data['legend']);
                $row = $row + 2;

            //  вывод фильтров
                $filter = json_decode($_REQUEST['FILTER'], true);
                $row = create_filter(0, $row, count($data['items'][0]['data']), $filter);
                $title = $GLOBALS['@']['LANG']['DATA']['excel'] ['101'];
                $row++;   

            //  вывод шапки
                $row = create_head_b($row, $title, $data['legend']);

            //  фиксация шапки
                //$sheet -> freezePane($alphabet[0] . $row);

            //  вывод данных 
                foreach ($data['items'] as $k => $e) {
                    $e['title'] = explode('<br />', str_replace(['<b>', '</b>'], ['', ''], $e['title']));
                    $data['items'][$k]['title'] = implode(chr(10), $e['title']);
                }
                create_data_b($row, $data);
        }

        if ($_REQUEST['type'] == 'v-4') {
            //  получение данных
                include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-target.php';
                ob_end_clean();
                $_REQUEST['VIEW'] = '*';
                $_REQUEST['PAGE'] = 1;
                $data = view_total_get_data_public($_REQUEST);
                if (isset($data['error']) || !count($data['items'])) exit;
                $filter = json_decode($_REQUEST['FILTER'], true);

            //  имя файла
                $file_name = $GLOBALS['@']['LANG']['DATA']['excel']['102'] . ' (' . date('d.m.Y') . ')';

            //  вывод легенды
                $row = 1;
                $leg = [mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['008']) . ':'];
                ksort($data['dict']['id_val']['data']);
                foreach ($data['dict']['id_val']['data'] as $e) {
                    if (in_array($e['id'], $filter['id_val'])) $leg[] = $e['text'];
                }
                $sheet -> setCellValueByColumnAndRow(0, $row, implode(chr(10), $leg));
                $sheet -> getStyle($alphabet[0] . $row) -> getAlignment() -> setWrapText(true);
                $sheet -> getStyle($alphabet[0] . $row) -> applyFromArray(['font' => ['size' => 8]]);
                $sheet -> getRowDimension($row) -> setRowHeight(12 * count($leg));
                $sheet -> mergeCells($alphabet[0] . $row . ':' . $alphabet[1] . $row);
                $row = $row + 2;

            //  вывод фильтров
                $row = create_filter(0, $row, count($data['items']), $filter);
                $title = $GLOBALS['@']['LANG']['DATA']['excel']['010'];
                if (!empty($filter['id_level'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['011'];
                if (!empty($filter['id_field'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['012'];
                if (!empty($filter['id_class'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['013'];
                if (!empty($filter['id_item'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['014'];
                if (!empty($filter['id_sec'])) $title = $GLOBALS['@']['LANG']['DATA']['excel']['015'];
                $row++;   

            //  вывод шапки
                $style_head['fill']['color'] = ['rgb' => 'DDDDDD'];
                $sheet -> setCellValueByColumnAndRow(0, $row, implode(chr(10), $leg));
                $sheet -> setCellValueByColumnAndRow(1, $row, implode(chr(10), $leg));
                $sheet -> getColumnDimension($alphabet[0]) -> setWidth(80);
                $sheet -> getColumnDimension($alphabet[1]) -> setWidth(40);
                $sheet -> getStyle($alphabet[0] . $row) -> applyFromArray($style_head);
                $sheet -> getStyle($alphabet[0] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet -> getStyle($alphabet[0] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet -> setCellValueByColumnAndRow(0, $row, mb_strtoupper($title));
                $sheet -> getStyle($alphabet[1] . $row) -> applyFromArray($style_head);
                $sheet -> getStyle($alphabet[1] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $sheet -> getStyle($alphabet[1] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                $sheet -> setCellValueByColumnAndRow(1, $row, mb_strtoupper($GLOBALS['@']['LANG']['DATA']['excel']['008']));
                $row++;

            //  вывод данных
                $style = [
                    'borders' => [
                        'allborders' => [
                            'style' => PHPExcel_Style_Border::BORDER_THIN,
                            'color' => ['rgb' => '000000']
                        ]
                    ],
                    'font' => [
                        'size' => 10
                    ],
                ];      
                foreach ($data['items'] as $e) {
                    
                    $style['font']['size'] = 8;
                    $sheet -> getStyle($alphabet[0] . $row) -> applyFromArray($style);
                    $sheet -> getStyle($alphabet[0] . $row) -> getAlignment() -> setWrapText(true);
                    $sheet -> setCellValueByColumnAndRow(0, $row, $e['title_text']);

                    $style['font']['size'] = 10;
                    $sheet -> getStyle($alphabet[1] . $row) -> applyFromArray($style);
                    $sheet -> setCellValueByColumnAndRow(1, $row, $e['total']);
                    $sheet -> getStyle($alphabet[1] . $row) -> getAlignment() -> setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                    $sheet -> getStyle($alphabet[1] . $row) -> getAlignment() -> setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

                    $row++;
                }
        }

    //  отдача файла на скачиване
        header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename=' . $file_name . '.xlsx');
        $out = new PHPExcel_Writer_Excel2007($xls);
        $out -> save('php://output');
        exit;

    //  вывод
        // echo '<pre>';
        // print_r($excel);
        // echo '<hr />';
        // print_r($target);
        // echo '<hr />';
        // print_r($data);

?>