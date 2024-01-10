<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

?>

<script src="/js/vue/vue.js"></script>
<script src="/js/script-private.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>

<!-- select2 -->
<link href="/js/select2/css/select2.css?<?= $GLOBALS['@']['VERSION']['JS'] ?>" rel="stylesheet" />
<link href="/js/select2/css/select2-bootstrap4.css?<?= $GLOBALS['@']['VERSION']['JS'] ?>" rel="stylesheet" />
<script src="/js/select2/js/select2.full.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>
<script src="/js/select2/js/i18n/<?= $GLOBALS['@']['LANG']['PARAM'][$_COOKIE['LANG']]['suffix'] ?>.js?<?= $GLOBALS['@']['VERSION']['JS'] ?>"></script>


<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-sm" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <?= $GLOBALS['@']['LANG']['DATA']['c-footer-private']['001'] ?>
            </div>
            <div class="modal-footer">
                <a class="btn btn-sm btn-primary w-50 mx-3" href="/logout">
                    <i class="fas fa-sign-out-alt fa-fw"></i>
                    <?= $GLOBALS['@']['LANG']['DATA']['c-footer-private']['002'] ?>
                </a>
                <button class="btn btn-sm btn-secondary w-50 mx-3" type="button" data-dismiss="modal">
                    <i class="fas fa-times fa-fw fa-fw"></i>
                    <?= $GLOBALS['@']['LANG']['DATA']['c-footer-private']['003'] ?>
                </button>
            </div>
        </div>
    </div>
</div>


<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>


<footer class="sticky-footer bg-white">
    <div class="container my-auto">
        <div class="copyright d-flex align-items-center justify-content-center">
            <div class="mr-4"><b>CEP &copy; <?= date('Y', time()) ?></b></div>
            <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/language.php' ?>
        </div>
    </div>
</footer>