<?php

    //  инициализация
        $GLOBALS['@']['ENGINE'] = true;
        $GLOBALS['@']['PAGE_CURRENT'] = '900';
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-engine/language.php';

?>

<!DOCTYPE html>
<html class="h-100">

    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/head.php'; ?>

    <body class="h-100">
        <div id="wrapper" class="h-100">
            <div id="content-wrapper" class="d-flex align-items-center justify-content-center">
                <div class="text-center m-5 start-hidden">
                    <div class="error mx-auto fas" data-text="&#xf1c0;"><i class="fas fa-database mb-3"></i></div>
                    <p class="lead text-gray-800 mb-4 font-weight-bold">
                        <?= $GLOBALS['@']['LANG']['DATA']['p-900']['001'] ?>
                    </p>
                    <hr />
                    <div class="d-flex align-items-center justify-content-between">
                        <a class="small mr-5" href="/">
                            <?= $GLOBALS['@']['LANG']['DATA']['p-900']['002'] ?>
                        </a>
                        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/language.php' ?>
                    </div>                    
                </div>
            </div>
        </div>
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/footer.php'; ?>
    </body>

</html>