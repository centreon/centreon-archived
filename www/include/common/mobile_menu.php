<?php
/*
 * Copyright 2005-2021 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

?>
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
