<?php

    //  вывод ошибок
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  версии файлов
        $GLOBALS['@']['VERSION'] = [
            'CSS' => time(),
            'JS' => time()
        ];

    //  адрес редиректа при потери авторизации
        $GLOBALS['@']['URL']['GEN'] = '/';

    //  параметры базы данных
        $GLOBALS['@']['MYSQL'] = [
            'HOST' => 'localhost',
            'USER' => 'curriculum',
            'PASS' => 'Pass@cep22',
            'BASE' => 'curriculum',
            'PORT' => '3306',
            'LINK' => false,
            'DATA' => 2222 // максимальная длина данных
        ];

    //  параметры локализации
        $GLOBALS['@']['LANG'] = [
            'PARAM' => [
                0 => ['name' => 'English', 'suffix' => 'en', 'title' => ['en' => 'English', 'kz' => 'Kazakh', 'ru' => 'Russian']],
                1 => ['name' => 'Қазақ', 'suffix' => 'kz', 'title' => ['en' => 'Ағылшын', 'kz' => 'Қазақ', 'ru' => 'Орыс']],
                2 => ['name' => 'Русский', 'suffix' => 'ru', 'title' => ['en' => 'Английский', 'kz' => 'Казахский', 'ru' => 'Русский']]
            ],
            'DEFAULT' => 0
        ];

    //  "соль" для хэша
        $GLOBALS['@']['HASH']['SALT'] = 'DK5L87A2PV30TSAR';

    //  публичные страницы
        $GLOBALS['@']['PAGE']['PUBLIC'] = [
            'public/login' => 'login',
            'public/logout' => 'logout',
            'public/forgot' => 'forgot',
            'public/password' => 'password',
            'public/view' => 'view'
        ];

    //  конфигурация доступа к страницам
        $GLOBALS['@']['PAGE']['ACCESS'] = [
            //  'роль' => [
            //      'файл' => 'URI'
            //  ]
            'admin' => [
                'private/rate' => 'rate',
                'private/dict' => 'dict',
                'private/user' => 'user'
            ],
            'editor' => [
                'private/rate' => 'rate',
                'private/dict' => 'dict'
            ],
            'expert' => [
                'private/rate' => 'rate'
            ],
            'other' => []
        ];

    //  назначение публичных страниц всем ролям
        foreach ($GLOBALS['@']['PAGE']['ACCESS'] as $k => $e) {
            $GLOBALS['@']['PAGE']['ACCESS'][$k] = array_merge($e, $GLOBALS['@']['PAGE']['PUBLIC']);
        }