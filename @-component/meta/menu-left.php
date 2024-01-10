<?php

    //  проверка инициализации
        if (empty($GLOBALS['@']['ENGINE'])) exit;

    //  активный пункт меню
        $active = explode('?', $_SERVER['REQUEST_URI'])[0];

?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- ШАПКА -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="/">
        <img src="/img/logo.png" width="40px" />
        <div class="sidebar-brand-text mx-2">NIS</div>
    </a>

        <!-- ОЦЕНИВАНИЕ -->
        <?php if (in_array($_SESSION['ROLE'], ['admin', 'editor', 'expert'])): ?>
            <hr class="sidebar-divider mb-2">
            <li class="nav-item">
                <a class="nav-link mb-2 py-2" href="/rate">
                <i class="fas fa-fw fa-th"></i>
                <span><?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['001'] ?></span></a>
            </li>
        <?php endif; ?>

        <!-- СПРАВОЧНИКИ -->
        <?php if (in_array($_SESSION['ROLE'], ['admin', 'editor'])): ?>
            <hr class="sidebar-divider mb-2">
            <div class="sidebar-heading my-2"><?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['100'] ?></div>
            <li class="nav-item">
                <a class="nav-link mb-2 py-2 collapsed" href="#" data-toggle="collapse" data-target="#collapse1" aria-expanded="true" aria-controls="collapse1">
                    <i class="fas fa-fw fa-graduation-cap"></i>
                    <span class="text-truncate">1. <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['110'] ?></span>
                </a>
                <div id="collapse1" class="collapse" aria-labelledby="heading1" data-parent="#accordionSidebar" style="z-index: 1000">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/dict/education-level">
                            <span class="mr-1">1.1.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['111'] ?>
                        </a>
                        <a class="collapse-item" href="/dict/education-field">
                            <span class="mr-1">1.2.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['112'] ?>
                        </a>
                        <a class="collapse-item" href="/dict/education-class">
                            <span class="mr-1">1.3.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['113'] ?>
                        </a>
                    </div>
                </div>
            </li>
            <?php if (in_array($_SESSION['ROLE'], ['admin'])): ?>
                <li class="nav-item">
                    <a class="nav-link mb-2 py-2 collapsed" href="#" data-toggle="collapse" data-target="#collapse3" aria-expanded="true" aria-controls="collapseTwo">
                        <i class="fas fa-fw fa-award"></i>
                        <span class="text-truncate">2. <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['130'] ?></span>
                    </a>
                    <div id="collapse3" class="collapse" aria-labelledby="heading3" data-parent="#accordionSidebar" style="z-index: 1000">
                        <div class="bg-white py-2 collapse-inner rounded">
                            <a class="collapse-item" href="/dict/criteria-level-1">
                                <span class="mr-1">2.1.</span>
                                <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['132'] ?>
                            </a>
                            <a class="collapse-item" href="/dict/criteria-level-2">
                                <span class="mr-1">2.2.</span>
                                <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['133'] ?>
                            </a>
                            <a class="collapse-item" href="/dict/criteria-level-3">
                                <span class="mr-1">2.3.</span>
                                <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['134'] ?>
                            </a>
                            <hr class="my-2" />
                            <a class="collapse-item" href="/dict/criteria-val">
                                <span class="mr-1">2.4.</span>
                                <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['135'] ?>
                            </a>
                        </div>
                    </div>
                </li>
            <?php endif; ?>
            <li class="nav-item">
                <a class="nav-link mb-2 py-2 collapsed" href="#" data-toggle="collapse" data-target="#collapse2" aria-expanded="true" aria-controls="collapseTwo">
                    <i class="fas fa-fw fa-layer-group"></i>
                    <span class="text-truncate">3. <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['120'] ?></span>
                </a>
                <div id="collapse2" class="collapse" aria-labelledby="heading2" data-parent="#accordionSidebar" style="z-index: 1000">
                    <div class="bg-white py-2 collapse-inner rounded">
                        <a class="collapse-item" href="/dict/discipline-item">
                            <span class="mr-1">3.1.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['120'] ?>
                        </a>
                        <hr class="my-2" />
                        <a class="collapse-item" href="/dict/discipline-section">
                            <span class="mr-1">3.2.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['121'] ?>
                        </a>
                        <a class="collapse-item" href="/dict/discipline-subsection">
                            <span class="mr-1">3.3.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['122'] ?>
                        </a>
                        <a class="collapse-item" href="/dict/discipline-target">
                            <span class="mr-1">3.4.</span>
                            <?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['123'] ?>
                        </a>
                    </div>
                </div>
            </li>
        <?php endif; ?>

        <!-- ПОЛЬЗОВАТЕЛИ -->
        <?php if (in_array($_SESSION['ROLE'], ['admin'])): ?>
            <hr class="sidebar-divider mb-2">
            <li class="nav-item">
                <a class="nav-link mb-2 py-2" href="/user">
                    <i class="fas fa-fw fa-users"></i>
                    <span><?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['200'] ?></span>
                </a>
            </li>
        <?php endif; ?>

        <!-- ГРАФИКИ -->
        <?php if (in_array($_SESSION['ROLE'], ['admin', 'editor', 'expert'])): ?>
            <hr class="sidebar-divider mb-2">
            <li class="nav-item">
                <a class="nav-link mb-2 py-2" href="/view">
                    <i class="fas fa-fw fa-chart-bar"></i>
                    <span><?= $GLOBALS['@']['LANG']['DATA']['c-menu-left']['140'] ?></span>
                </a>
            </li>
        <?php endif; ?>

        <!-- КНОПКА СВОРАЧИВАНИЯ МЕНЮ -->
        <hr class="sidebar-divider mb-2 d-none d-md-block">
        <div class="text-center d-none d-md-inline">
            <button class="rounded-circle border-0 mt-2" id="sidebarToggle"></button>
        </div>

</ul>