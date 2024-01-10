<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  подключение к базе данных
        $GLOBALS['@']['MYSQL']['LINK'] = new mysqli;
        if (!@$GLOBALS['@']['MYSQL']['LINK'] -> real_connect(
            $GLOBALS['@']['MYSQL']['HOST'], 
            $GLOBALS['@']['MYSQL']['USER'], 
            $GLOBALS['@']['MYSQL']['PASS'], 
            $GLOBALS['@']['MYSQL']['BASE'], 
            $GLOBALS['@']['MYSQL']['PORT'])
        ) {
            header('HTTP/1.1 900 Error BD');
            include_once '900.php';
            exit;
        }
        $GLOBALS['@']['MYSQL']['LINK'] -> query("SET NAMES utf8;");