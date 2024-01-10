<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  COM: параметры компоненты
        $com_c = [];
        $com_c['type'] = 'login';
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
                            <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
                            <div class="col-lg-6 pl-lg-3">
                                <div class="pl-lg-5">
                                    <div class="text-center mb-3">
                                        <h1 class="h4 text-gray-900 mb-4">
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-login']['001'] ?>
                                        </h1>
                                    </div>
                                    <?php if ($com_c['error'] != ''): ?>
                                        <div class="d-flex align-items-center justify-content-center text-danger">
                                            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
                                            <div><?= $com_c['error'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                    <form class="user was-validated mt-4" method="post">
                                        <div class="form-group">
                                            <input
                                                type="email"
                                                name="email"
                                                class="form-control"
                                                required
                                                maxlength="256"
                                                placeholder="<?= $GLOBALS['@']['LANG']['DATA']['p-login']['002'] ?>"
                                                value="<?= isset($_POST['email']) ? $_POST['email'] : '' ?>"
                                            />
                                        </div>
                                        <div class="form-group">
                                            <input
                                                type="password"
                                                name="password"
                                                class="form-control"
                                                required
                                                minlength="8"
                                                maxlength="20"
                                                placeholder="<?= $GLOBALS['@']['LANG']['DATA']['p-login']['003'] ?>"
                                            />
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" name="remember" class="custom-control-input" id="rememberCheck">
                                                <label class="custom-control-label" for="rememberCheck">
                                                    <?= $GLOBALS['@']['LANG']['DATA']['p-login']['004'] ?>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary w-100">
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-login']['005'] ?>
                                        </button>
                                    </form>
                                    <hr />
                                    <div class="d-flex align-items-center justify-content-between">
                                        <a class="small mr-3" href="/forgot">
                                            <?= $GLOBALS['@']['LANG']['DATA']['p-login']['006'] ?>
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