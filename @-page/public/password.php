<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  COM: параметры компоненты
        $com_c = [];
        $com_c['type'] = 'password';
        $com_c['error'] = '';
        $com_c['message'] = '';

    //  подключение компоненты авторизации
        include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/auth/auth-@engine.php';

?>

<body class="bg-gradient-primary">
    <div class="container start-hidden">
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5">
                    <div class="card-body p-0">
                        <div class="row m-0 px-3 py-4 p-sm-5">
                            <div class="col-lg-6 d-none d-lg-block bg-password-image"></div>
                            <div class="col-lg-6 pl-lg-3">
                                <div class="pl-lg-5">
                                    <div class="text-center mb-3">
                                        <h1 class="h4 text-gray-900 mb-4">
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-password']['001'] ?>
                                        </h1>
                                        <p>
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-password']['002'] ?>.
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-password']['003'] ?>.
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-password']['004'] ?>
                                        </p>
                                    </div>
                                    <?php if ($com_c['error'] != ''): ?>
                                        <div class="d-flex align-items-center justify-content-center text-danger">
                                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                            <div><?= $com_c['error'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($com_c['message'] != ''): ?>
                                        <div class="d-flex align-items-center justify-content-center text-success">
                                            <i class="fas fa-info-circle fa-2x mr-3"></i>
                                            <div><?= $com_c['message'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <form class="user was-validated mt-4" method="post">
                                        <div class="form-group">
                                            <input
                                                type="password"
                                                name="password"
                                                class="form-control"
                                                required
                                                minlength="8"
                                                maxlength="20"
                                                placeholder="<?= $GLOBALS['@']['LANG']['DATA']['p-password']['005'] ?>"
                                            />
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-password']['006'] ?>
                                        </button>
                                    </form>
                                    <hr />
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a class="small mr-3 text-center" href="/login">
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-password']['007'] ?>
                                        </a>
                                        <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/language.php' ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once $_SERVER['DOCUMENT_ROOT'] . '/@-component/meta/footer.php'; ?>
</body>