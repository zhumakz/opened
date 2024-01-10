<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

?>

<body id="page-top">
    <script src="/js/script-elem.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>
    <script src="/js/script-dict.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>
    <div id="wrapper">
        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/menu-left.php'; ?>
        <div id="content-wrapper" class="d-flex flex-column start-hidden">
            <div id="content">
            <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/menu-top.php'; ?>
                <div class="container-fluid">
                    <!--BEGIN-->
                
                    <div class="row pb-4">
                        <div class="col-12">
                            <?php include $_SERVER['DOCUMENT_ROOT'] . '/@-component/dict/dict-user.php'; ?>
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