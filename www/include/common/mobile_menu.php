<header class="nav-top">
    <span class="hamburger material-icons" id="ham">menu</span>
    <div class="logo"></div>
</header>
<nav class="nav-drill">
    <ul class="nav-items nav-level-1">
        <?php foreach ($treeMenu as $index => $subMenu) : ?>
        <li class="nav-item <?php if (!empty($subMenu['children'])) : ?>nav-expand<?php endif; ?>">
            <a class="nav-link <?php if (!empty($subMenu['children'])) : ?>nav-expand-link<?php endif; ?>" href="#">
                <?= $subMenu['label'] ?>
            </a>
            <?php if (!empty($subMenu['children'])) : ?>
                <ul class="nav-items nav-expand-content">
                <?php foreach ($subMenu['children'] as $index2 => $subMenu2) : ?>
                    <li class="nav-item">
                        <a class="nav-link" href="main.php?p=<?= substr($index2, 1) ?>">
                            <?= $subMenu2['label'] ?>
                        </a>
                    </li>
                <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
        <li class="nav-item">
            <a class="nav-link" href="index.php?disconnect=1">
                <?= gettext('Logout') ?>
            </a>
        </li>
    </ul>
</nav>
