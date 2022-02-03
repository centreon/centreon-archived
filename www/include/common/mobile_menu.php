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
                    <?php if (!empty($subMenu2['children'])) : ?>
                        <li class="nav-item nav-expand">
                            <a class="nav-link nav-expand-link" href="#">
                                <?= $subMenu2['label'] ?>
                            </a>
                            <ul class="nav-items nav-expand-content">
                            <?php foreach ($subMenu2['children'] as $childrens) : ?>
                                <?php foreach ($childrens as $index3 => $subMenu3) : ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="main.php?p=<?= substr($index3, 1) . $subMenu3['options'] ?>">
                                            <?= $subMenu3['label'] ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                            </ul>
                        </li>
                    <?php else : ?>
                        <li class="nav-item">
                            <a class="nav-link" href="main.php?p=<?= substr($index2, 1) ?>">
                                <?= $subMenu2['label'] ?>
                            </a>
                        </li>
                    <?php endif; ?>
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
