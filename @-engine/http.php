<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  предварительная обработка URI
        $temp = explode('?', $_SERVER['REQUEST_URI']);
        $temp = explode('/', $temp[0]);
        foreach ($temp as $k => $e) if (trim($e) == '') unset($temp[$k]);
        $temp = implode('/', $temp) . '/';

    //  определение имени скрипта и параметров
        if ($temp == '/') {
            header('Location: /' . array_shift($GLOBALS['@']['PAGE']['ACCESS'][$_SESSION['ROLE']]));
            exit;
        }
        else {
            foreach ($GLOBALS['@']['PAGE']['ACCESS'][$_SESSION['ROLE']] as $k => $e) {
                if (mb_stripos($temp, $e . '/') === 0) {
                    $GLOBALS['@']['PAGE']['CURRENT'] = $k;
                    break;
                }
            }
            $temp = explode('/', str_replace($e, '', $temp));
            foreach ($temp as $k => $e) if (trim($e) != '') $GLOBALS['@']['PAGE']['DATA'][] = $e;
        }

    //  вывод ошибки 404
        if (empty($GLOBALS['@']['PAGE']['CURRENT'])) {
            ob_start();
            header('HTTP/1.1 404 Not Found');
            include_once '404.php';
            exit;
        }
        if (!file_exists($_SERVER['DOCUMENT_ROOT'] . '/@-page/' . $GLOBALS['@']['PAGE']['CURRENT'] . '.php')) {
            ob_start();
            header('HTTP/1.1 404 Not Found');
            include_once '404.php';
            exit;
        }