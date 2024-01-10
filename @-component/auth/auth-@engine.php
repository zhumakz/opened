<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  проверка доступа роли к компоненте
        if (!in_array($_SESSION['ROLE'], ['other'])) {
            ob_end_clean();
            header('Location: ' . $GLOBALS['@']['URL']['GEN']);
            exit;
        }

    //  проверка инициализации параметров компонеты
        if (empty($com_c)) exit;

    //  определение типа вызова
        if ($com_c['type'] == 'login') auth_login_public();
        if ($com_c['type'] == 'logout') auth_logout_public();
        if ($com_c['type'] == 'forgot') auth_forgot_public();
        if ($com_c['type'] == 'password') auth_password_public();

    //  запрос на восстановление пароля
        function auth_forgot_public() {

            //  переменные
                global $com_c;

            //  проверка наличия параметра
                if (!isset($_POST['email'])) return false;
                $_POST['email'] = trim($_POST['email']);
                if ($_POST['email'] == '') {  //  email не указан
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-forgot']['900'];
                    return false;
                }

            //  запрос данных
                $query = "
                    SELECT * 
                    FROM `sys_user` 
                    WHERE `mail` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_POST['email']) . "';
                ";
                $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {  //  ошибка базы данных
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['g-error']['300'];
                    return false;
                }
                if (!($query -> num_rows)) {  //  пользователь не существует
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-forgot']['901'];
                    return false;
                }

            //  обработка данных
                $query = $query -> fetch_all(MYSQLI_ASSOC);
                if ($query[0]['access'] == null) {  //  доступ ограничен
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-forgot']['902'];
                    return false;
                }
                if (!filter_var($query[0]['mail'], FILTER_VALIDATE_EMAIL)) {  //  email некорректный
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-forgot']['903'];
                    return false;
                }

            //  генерация хэша
                $mail = $query[0]['mail'];
                $link = hash('md5', rand(1111111, 9999999) . time() . $query[0]['mail'] . $GLOBALS['@']['HASH']['SALT']);
                $query = "
                    UPDATE `sys_user` 
                    SET `hash_forgot` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($link) . "'
                    WHERE `id` = '" . $query[0]['id'] . "';
                ";
                $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {  //  ошибка базы данных
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['g-error']['300'];
                    return false;
                }

            //  отправка сообщения
                $link = $_SERVER['HTTP_ORIGIN'] . '/password?forgot=' . $link;
                $subject = $GLOBALS['@']['LANG']['DATA']['p-forgot']['904'];
                $body = '<a href="' . $link . '">' . $GLOBALS['@']['LANG']['DATA']['p-forgot']['905'] . '</a>';
                $head = ['X-Mailer: PHP/' . phpversion()];
                if (mail($mail, $subject, $body, implode("\r\n", $head))) {  //  письмо отправлено
                    $com_c['message'] = $GLOBALS['@']['LANG']['DATA']['p-forgot']['906'];
                    return false;
                }
                else {  //  не удалось отправить письмо
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-forgot']['907'];
                    return false;
                }

            //  возврат
                return true;

        }

    //  восстановление пароля
        function auth_password_public() {

            //  переменные
                global $com_c;

            //  проверка наличия параметра
                if (!isset($_GET['forgot']) || mb_strlen($_GET['forgot']) != 32) {
                    ob_end_clean();
                    exit;
                }

            //  проверка соответствия параметра
                $query = "
                    SELECT * 
                    FROM `sys_user` 
                    WHERE `access` = '1' AND
                          `hash_forgot` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_GET['forgot']) . "';
                ";
                $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {  //  ошибка базы данных
                    ob_end_clean();
                    exit;
                }
                if (!($query -> num_rows)) {  //  запрос на восстановление не существует
                    ob_end_clean();
                    exit;
                }

            //  проверка наличия параметра
                if (!isset($_POST['password'])) return false;
                $_POST['password'] = trim($_POST['password']);
                if (mb_strlen($_POST['password']) < 8 || 20 < mb_strlen($_POST['password'])) {  //  некорректная длина
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-password']['900'];
                    return false;
                }

            //  сохранение пароля
                $_POST['password'] = hash('md5', $_POST['password'] . $GLOBALS['@']['HASH']['SALT']);
                $query = "
                    UPDATE `sys_user` 
                    SET `password` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_POST['password']) . "',
                        `hash_forgot` = ''
                    WHERE `hash_forgot` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_GET['forgot']) . "';
                ";
                $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {  //  ошибка базы данных
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['g-error']['300'];
                    return false;
                }
                $com_c['message'] = $GLOBALS['@']['LANG']['DATA']['p-password']['901'];

            //  возврат
                return true;

        }

    //  авторизация
        function auth_login_public() {

            //  переменные
                global $com_c;

            //  проверка наличия параметра
                if (!isset($_POST['email']) && !isset($_POST['password'])) return false;
                $_POST['email'] = trim($_POST['email']);
                $_POST['password'] = trim($_POST['password']);
                if (!mb_strlen($_POST['email']) || !mb_strlen($_POST['password'])) {    //  некорректные данные
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-login']['900'];
                    return false;
                }

            //  запрос данных
                $query = "
                    SELECT `sys_user`.*, `sys_role`.`alias`
                    FROM `sys_user` 
                    LEFT JOIN `sys_role` ON `sys_user`.`id_role` = `sys_role`.`id`
                    WHERE `sys_user`.`mail` = '" . $GLOBALS['@']['MYSQL']['LINK'] -> real_escape_string($_POST['email']) . "';
                ";
                $query = $GLOBALS['@']['MYSQL']['LINK'] -> query($query);
                if ($GLOBALS['@']['MYSQL']['LINK'] -> error) {  //  ошибка базы данных
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['g-error']['300'];
                    return false;
                }

            //  обработка данных
                if (!($query -> num_rows)) {  //  пользователь не существует
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-login']['901'];
                    return false;
                }
                $query = $query -> fetch_all(MYSQLI_ASSOC);
                if ($query[0]['access'] == null) {  //  доступ ограничен
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-login']['902'];
                    return false;
                }
                if (mb_strlen($query[0]['password']) == null) {  //  новый пароль
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-login']['903'];
                    return false;
                }
                $_POST['password'] = hash('md5', $_POST['password'] . $GLOBALS['@']['HASH']['SALT']);
                if ($query[0]['password'] != $_POST['password']) {  //  неверный пароль
                    $com_c['error'] = $GLOBALS['@']['LANG']['DATA']['p-login']['904'];
                    return false;
                }

            //  сохранение cookie (запомнить меня)
                if (isset($_POST['remember']) && $_POST['remember']) {
                    $hash = hash('md5', $query[0]['mail'] . $query[0]['id_role'] . $query[0]['pass'] . $GLOBALS['@']['HASH']['SALT']);
                    $value = $query[0]['id'] . '~' . $hash;
                    setcookie('AUTH', $value, time() + 31536000, '/');
                }

            //  сохранение сессии и перезагрузка
                $_SESSION['ID_USER'] = $query[0]['id'];
                $_SESSION['ROLE'] = $query[0]['alias'];
                $_SESSION['MAIL'] = $query[0]['mail'];
                header('Location: ' . $GLOBALS['@']['URL']['GEN']);
                exit;

        }