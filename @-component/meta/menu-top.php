<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

?>

<nav class="navbar navbar-expand navbar-light bg-white topbar static-top shadow mb-4">

    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>

    <ul class="navbar-nav ml-auto">

        <li class="nav-item dropdown no-arrow">
            <span class="nav-link text-gray-600 text-right font-weight-bold small">
                <?= mb_strtoupper($_SESSION['ROLE']) ?><br />
                <?= isset($_SESSION['MAIL']) ? $_SESSION['MAIL'] : '' ?>
            </span>
        </li>

        <div class="topbar-divider"></div>

        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle text-primary" href="#" data-toggle="modal" data-target="#logoutModal">
                <i class="fas fa-sign-out-alt fa-fw"></i>
                <span class="px-2 d-none d-lg-inline">
                    <?= $GLOBALS['@']['LANG']['DATA']['c-menu-top']['001'] ?>
                </span>
            </a>
        </li>

    </ul>

</nav>