<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  старт сессии
        session_start();

    //  деавторизация (сброс сессии)
        function session_logout() {
            if (isset($_REQUEST['@API'])) { // доступ через API
                if (isset($GLOBALS['@']['PAGE']['PUBLIC']['public' . $_SERVER['REQUEST_URI']])) return false;
                ob_end_clean();
                echo json_encode(['@' => 0], JSON_FORCE_OBJECT);
                exit;
            }
            $_SESSION = [];
            setcookie('AUTH', null, 0, '/');
            header('Location: ' . $GLOBALS['@']['URL']['GEN']);
            exit;
        }

    //  авторизация по cookie
        if (isset($_COOKIE['AUTH'])) {
            setcookie('AUTH', $_COOKIE['AUTH'], time() + 31536000, '/');
            if (empty($_SESSION['ROLE'])) {
                //  подготовка параметров запроса
                    $param = explode('~', $_COOKIE['AUTH']);
                //  запрос данных
                    $query = "
                        SELECT `sys_user`.*, `sys_role`.`alias`
                        FROM `sys_user` 
                        LEFT JOIN `sys_role` ON `sys_user`.`id_role` = `sys_role`.`id`
                        WHERE `sys_user`.`id` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($param[0]) . "' AND
                              `sys_user`.`access` = '1';
                    ";
                    $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                //  ошибка базы банных
                    if ($GLOBALS['@']['MYSQL']['LINK'] -> error) session_logout();
                //  пользователь не найден или пароль изменен
                    if (!($query -> num_rows)) session_logout();
                //  обработка результата запроса
                    $query = $query -> fetch_all(MYSQLI_ASSOC);
                    $hash = hash('md5', $query[0]['mail'] . $query[0]['id_role'] . $query[0]['pass'] . $GLOBALS['@']['HASH']['SALT']);
                //  параметры пользователя изменились
                    if ($hash != $param[1]) session_logout();
                //  авторизация
                    $_SESSION['ID_USER'] = $query[0]['id'];
                    $_SESSION['ROLE'] = $query[0]['alias'];
                    $_SESSION['MAIL'] = $query[0]['mail'];
                    if (!isset($_REQUEST['@API'])) {
                        header('Location: ' . $GLOBALS['@']['URL']['GEN']);
                        exit;
                    }
            }
        }
        
    //  проверка доступа
        if (!empty($_SESSION['ROLE']) && $_SESSION['ROLE'] != 'other') {
            if (empty($_SESSION['MAIL'])) session_logout();
            else {
                //  запрос данных
                    $query = "
                        SELECT `sys_user`.*, `sys_role`.`alias`
                        FROM `sys_user` 
                        LEFT JOIN `sys_role` ON `sys_user`.`id_role` = `sys_role`.`id`
                        WHERE `sys_user`.`mail` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_SESSION['MAIL']) . "' AND
                              `sys_user`.`access` = '1';
                    ";
                    $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                //  ошибка базы банных
                    if ($GLOBALS['@']['MYSQL']['LINK'] -> error) session_logout();
                //  пользователь не найден
                    if (!($query -> num_rows)) session_logout();
                //  обработка результата запроса
                    $query = $query -> fetch_all(MYSQLI_ASSOC);
                //  роль пользователя сменилась
                    if ($_SESSION['ROLE'] != $query[0]['alias']) session_logout();
            }
        }
        else {
            if (isset($_REQUEST['@API'])) session_logout();
            $_SESSION['ROLE'] = 'other';
        }