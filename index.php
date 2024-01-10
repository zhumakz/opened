<?php

    //  инициализация
        ob_start();
        $GLOBALS['@']['ENGINE'] = true;
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/config.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/mysql.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/language.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/session.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/api.php';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/http.php';

?>

<!DOCTYPE html>
<html lang="<?= $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'] ?>">

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/head.php'; ?>

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-page/' . $GLOBALS['@']['PAGE']['CURRENT'] . '.php'; ?>

</html>