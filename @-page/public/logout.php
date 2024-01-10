<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  деавторизация
        $_SESSION = [];
        setcookie('AUTH', $value, 0, '/');
        ob_end_clean();
        header('Location: ' . $GLOBALS['@']['URL']['GEN']);
        exit;

?>