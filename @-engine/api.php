<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  выполнение API запроса
        if (isset($_REQUEST['@API'])) {
            if (!empty($_REQUEST['@COM'])) {
                if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/@-component/' . explode('-', $_REQUEST['@COM'])[0] . '/' . $_REQUEST['@COM'] . '.php')) {
                    //  подключение компоненты
                        include $_SERVER['DOCUMENT_ROOT'] . '/@-component/' . explode('-', $_REQUEST['@COM'])[0] . '/' . $_REQUEST['@COM'] . '.php';
                    //  очистка буфера
                        ob_end_clean();
                    //  выполнение метода
                        if (isset($_REQUEST['@MET']) && function_exists($_REQUEST['@MET'] . '_public')) {
                            echo json_encode(call_user_func($_REQUEST['@MET'] . '_public', $_REQUEST), JSON_FORCE_OBJECT);
                            exit;
                        }
                }
            }
            echo json_encode(['error' => 200], JSON_FORCE_OBJECT);
            exit;
        }