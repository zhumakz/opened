<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  запрос структуры
        $query = "
            SELECT `ll`.`id_level`, `fl`.`id_field`, `rv`.`id_class`, `rv`.`id_item`, `rv`.`id_sec`, `rv`.`id_subsec`, COUNT(`rv`.`id`) as `total`  
            FROM `rate_val` AS `rv`
            
            RIGHT JOIN `rate_item` AS `ri` ON `rv`.`id_item` = `ri`.`id_item` AND `rv`.`id_class` = `ri`.`id_class` AND `ri`.`id_status` = '3'
            INNER JOIN `dict_link_class_level` AS `ll` ON `ll`.`id_class` = `rv`.`id_class`
            INNER JOIN `dict_link_field_level` AS `fl` ON `fl`.`id_level` = `ll`.`id_level` AND `ri`.`id_field` = `fl`.`id_field`
            
            GROUP BY `ll`.`id_level`, `fl`.`id_field`, `rv`.`id_class`, `rv`.`id_item`, `rv`.`id_sec`, `rv`.`id_subsec`;
        ";
        $res = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
        $data = $res -> fetch_all(MYSQLI_ASSOC);
        $dict = [];
        foreach ($data as $row) {
            $dict['id_level'][$row['id_level']] = $row['id_level'];
            $dict['id_field'][$row['id_field']] = $row['id_field'];
            $dict['id_class'][$row['id_class']] = $row['id_class'];
            $dict['id_item'][$row['id_item']] = $row['id_item'];
            $dict['id_sec'][$row['id_sec']] = $row['id_sec'];
            $dict['id_subsec'][$row['id_subsec']] = $row['id_subsec'];
        }

    //  подготовка параметров локализации
        $temp = [];
        $name = "`name_" . $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'] . "`";
        foreach ($GLOBALS['@']['LANG']['PARAM'] as $k => $e) {
            if ($_COOKIE['LANG'] != $k) {
                $temp[] = "WHEN `name_" . $e['suffix'] . "` <> ''";
                $temp[] = "THEN CONCAT('" . mb_strtoupper($e['suffix']) . ": ', `name_" . $e['suffix'] . "`)";
            }
        }
        $name = "IF (" . $name . " <> '', " . $name . ",  CASE " . implode(' ', $temp) . " ELSE 'NULL' END) AS `name`";

    //  запрос справочников
        $temp = [
            'id_level'  => 'dict_education_level',
            'id_field'  => 'dict_education_field',
            'id_class'  => 'dict_education_class',
            'id_item'   => 'dict_discipline_item',
            'id_sec'    => 'dict_discipline_section',
            'id_subsec' => 'dict_discipline_subsection',
        ];
        foreach ($dict as $k => $e) {
            $res = $GLOBALS['@']['MYSQL']['LINK'] -> query("
                SELECT `id`, " . $name . "
                FROM `" . $temp[$k] . "`
                WHERE `id` IN (" . implode(', ', $e) . ")
                ORDER BY `name`;
            ");
            foreach ($res -> fetch_all(MYSQLI_ASSOC) as $row) $dict[$k][$row['id']] = $row['name'];
        }
        foreach ($dict as $k => $e) asort($dict[$k]);

    //  подготовка данных
        $n = 'name';
        $f = 'filter';
        $c = 'children';
        $d = 'color';
        $color = ['#79BCD9', '#EECD79', '#e079ee'];
        $temp = [$n => '', $c => []];
        foreach ($data as $k => $e) {
            $temp[$c][$e['id_level']][$n] = $dict['id_level'][$e['id_level']];
            $temp[$c][$e['id_level']][$f] = [
                'id_level'  => $e['id_level'],
                'id_field'  => '',
                'id_class'  => '',
                'id_item'   => '',
                'id_sec'    => '',
                'id_subsec' => ''
            ];
            if (!isset($temp[$c][$e['id_level']][$d])) {
                if (count($color)) $temp[$c][$e['id_level']][$d] = array_shift($color);
            }

            $temp[$c][$e['id_level']][$c][$e['id_field']][$n] = $dict['id_field'][$e['id_field']];
            $temp[$c][$e['id_level']][$c][$e['id_field']][$f] = [
                'id_level'  => $e['id_level'],
                'id_field'  => $e['id_field'],
                'id_class'  => '',
                'id_item'   => '',
                'id_sec'    => '',
                'id_subsec' => ''
            ];

            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$n] = $dict['id_class'][$e['id_class']];
            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$f] = [
                'id_level'  => $e['id_level'],
                'id_field'  => $e['id_field'],
                'id_class'  => $e['id_class'],
                'id_item'   => '',
                'id_sec'    => '',
                'id_subsec' => ''
            ];

            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$n] = $dict['id_item'][$e['id_item']];
            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$f] = [
                'id_level'  => $e['id_level'],
                'id_field'  => $e['id_field'],
                'id_class'  => $e['id_class'],
                'id_item'   => $e['id_item'],
                'id_sec'    => '',
                'id_subsec' => ''
            ];

            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$c][$e['id_sec']][$n] = $dict['id_sec'][$e['id_sec']];
            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$c][$e['id_sec']][$f] = [
                'id_level'  => $e['id_level'],
                'id_field'  => $e['id_field'],
                'id_class'  => $e['id_class'],
                'id_item'   => $e['id_item'],
                'id_sec'    => $e['id_sec'],
                'id_subsec' => ''
            ];

            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$c][$e['id_sec']][$c][$e['id_subsec']][$n] = $dict['id_subsec'][$e['id_subsec']];
            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$c][$e['id_sec']][$c][$e['id_subsec']][$f] = [
                'id_level'  => $e['id_level'],
                'id_field'  => $e['id_field'],
                'id_class'  => $e['id_class'],
                'id_item'   => $e['id_item'],
                'id_sec'    => $e['id_sec'],
                'id_subsec' => $e['id_subsec']
            ];

            $temp[$c][$e['id_level']][$c][$e['id_field']][$c][$e['id_class']][$c][$e['id_item']][$c][$e['id_sec']][$c][$e['id_subsec']]['value'] = $e['total'];
        }

        function data_sort($arr) {
            $temp = [];
            foreach ($arr as $k => $e) {
                if ($k != 'children') $temp[$k] = $e;
                else foreach ($e as $ee) $temp[$k][] = data_sort($ee);
            }
            return $temp;
        }

        $data = data_sort($temp);

?>

<body id="page-top">
    <script src="/js/d3.v7.min.js"></script>
    <script src="/js/babel.min.js"></script>
    <script src="/js/script-elem.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>
    <script src="/js/script-view.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>
    <script> let DICT = JSON.parse('<?= json_encode($dict, JSON_UNESCAPED_UNICODE); ?>'); </script>
    <script> let DATA = JSON.parse('<?= json_encode($data, JSON_UNESCAPED_UNICODE); ?>'); </script>
    <script src="/js/script-view-target.js" type="text/babel"></script>
    <script>
        $(() => {
            for (let i = 1; i <= 10; i++) {
                $('#view-' + i).on('hidden.bs.collapse', () => $('#button-view-' + i + ' i').removeClass('fa-chevron-up').addClass('fa-chevron-down'));
                $('#view-' + i).on('show.bs.collapse', () => $('#button-view-' + i + ' i').removeClass('fa-chevron-down').addClass('fa-chevron-up'));
            }
        });

    </script>
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container-fluid start-hidden mt-4">
                    <!--BEGIN-->

                    <div class="pb-4 position-relative">
                        <div class="card shadow">
                            <div class="card-header d-flex align-items-center px-3">
                                <i class="far fa-dot-circle fa-lg mr-3"></i>
                                <h5 class="flex-grow-1 m-0 font-weight-bold">
                                    <?= $GLOBALS['@']['LANG']['DATA']['c-view-target']['200'] ?>
                                </h5>
                                <button id="button-view-1" class="btn btn-sm btn-outline-primary border-0 p-2" data-toggle="collapse" data-target="#view-1">
                                    <i class="fas fa-chevron-up fa-lg"></i>
                                </button>
                            </div>
                            <div id="view-1" class="card-body row pb-2 pt-2 collapse show">
                                <div class="col-12 col-xl-5 p-xl-3 p-5">
                                    <div class="position-relative">
                                    <button class="btn btn-sm btn-outline-grey border-0 p-2 position-absolute" style="left: calc(50% - 25px); top: calc(50% - 25px)" onClick="resetGraf()">
                                        <i class="fas fa-undo-alt fa-3x"></i>
                                    </button>
                                    <div id="graf" class="mx-5" style="font-size: 0.7rem"></div>
                                    </div>
                                </div>
                                <div class="col-12 col-xl-7">
                                    <?php include $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-target.php'; ?>
                                </div>
                            </div>
                            <div id="load-graf-1" class="loader rounded"></div>
                        </div>
                    </div>

                    <div class="pb-4">
                        <div class="card shadow">
                            <div class="card-header d-flex align-items-center px-3">
                                <i class="far fa-chart-bar fa-lg mr-3"></i>
                                <h5 class="flex-grow-1 m-0 font-weight-bold">
                                    <?= $GLOBALS['@']['LANG']['DATA']['c-view-val']['200'] ?>
                                </h5>
                                <button id="button-view-2" class="btn btn-sm btn-outline-primary border-0 p-2" data-toggle="collapse" data-target="#view-2">
                                    <i class="fas fa-chevron-down fa-lg"></i>
                                </button>
                            </div>
                            <div id="view-2" class="card-body pb-2 collapse">
                                <?php include $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-val.php'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pb-4">
                        <div class="card shadow">
                            <div class="card-header d-flex align-items-center px-3">
                                <i class="fas fa-chart-area fa-lg mr-3"></i>
                                <h5 class="flex-grow-1 m-0 font-weight-bold">
                                    <?= $GLOBALS['@']['LANG']['DATA']['c-view-criteria']['200'] ?>
                                </h5>
                                <button id="button-view-3" class="btn btn-sm btn-outline-primary border-0 p-2" data-toggle="collapse" data-target="#view-3">
                                    <i class="fas fa-chevron-down fa-lg"></i>
                                </button>
                            </div>
                            <div id="view-3" class="card-body pb-2 collapse">
                                <?php include $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-criteria.php'; ?>
                            </div>
                        </div>
                    </div>

                    <div class="pb-4">
                        <div class="card shadow">
                            <div class="card-header d-flex align-items-center px-3">
                                <i class="fas fa-list-ul fa-rotate-180 fa-lg mr-3"></i>
                                <h5 class="flex-grow-1 m-0 font-weight-bold">
                                    <?= $GLOBALS['@']['LANG']['DATA']['c-view-total']['200'] ?>
                                </h5>
                                <button id="button-view-4" class="btn btn-sm btn-outline-primary border-0 p-2" data-toggle="collapse" data-target="#view-4">
                                    <i class="fas fa-chevron-down fa-lg"></i>
                                </button>
                            </div>
                            <div id="view-4" class="card-body pb-2 collapse">
                                <?php include $_SERVER['DOCUMENT_ROOT'] . '/@-component/view/view-total.php'; ?>
                            </div>
                        </div>
                    </div>

                    <!--END-->
                </div>
            </div>
            <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/footer.php'; ?>
            <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/footer-private.php'; ?>
        </div>
    </div>
</body>