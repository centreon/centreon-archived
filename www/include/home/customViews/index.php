<?php
/**
 * Copyright 2005-2011 MERETHIS
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
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 */

require_once $centreon_path . 'www/class/centreonCustomView.class.php';

try {
    $db = new CentreonDB();
    $viewObj = new CentreonCustomView($centreon, $db);

    /*
	 * Smarty
	 */
    $path = "./include/home/customViews/";
    
    /*
     * Smarty INIT
     */
    $template = new Smarty();
    $template = initSmartyTpl($path, $template, "./");

    $viewId = $viewObj->getCurrentView();
    $views = $viewObj->getCustomViews();

    $rotationTimer = 0;
    if (isset($_SESSION['rotation_timer'])) {
        $rotationTimer = $_SESSION['rotation_timer'];
    }

    $i = 1;
    $indexTab = array(0 => -1);

    foreach ($views as $key => $val) {
    	$indexTab[$key] = $i;
        $i++;
        if (!$viewObj->checkPermission($key)) {
            $views[$key]['icon'] = "locked";
        } else {
            $views[$key]['icon'] = "unlocked";
        }
    }
        
    $template->assign('views', $views);
    $template->assign('empty', $i);
    $template->assign('msg', _("No view available. To create a new view, please click \"Add view\" button."));
    
    $template->display("index.ihtml");
} catch (CentreonCustomViewException $e) {
    echo $e->getMessage() . "<br/>";
}
?>
<script type="text/javascript">
var viewIndex = "<?php echo $indexTab[$viewId];?>";
var rotationTimer = <?php echo $rotationTimer;?>;

jQuery(function() {
	jQuery('.addView').button({ icons : { primary: 'ui-icon-plus'}, label : '<?php echo _("Add view");?>' });
	jQuery('.setDefault').button({ icons : { primary: 'ui-icon-star'}, label : '<?php echo _("Set default");?>' });
	jQuery('.addWidget').button({ icons : { primary: 'ui-icon-plus'}, label : '<?php echo _("Add widget");?>' });
	jQuery('.editView').button({ icons : { primary: 'ui-icon-gear'}, label : '<?php echo _("Edit view");?>'  });
	jQuery('.shareView').button({ icons : { primary: 'ui-icon-folder-open'}, label : '<?php echo _("Share view");?>'  });
	jQuery('.deleteView').button({ icons : { primary: 'ui-icon-trash'}, label : '<?php echo _("Delete view");?>'  }).click(function() { deleteView(); });
	jQuery('.setRotate').button({ icons : { primary: 'ui-icon-play'}, label : '<?php echo _("Rotation");?>' });

	initColorbox(".addView", "./main.php?p=10301&min=1&action=add", "70%", "30%");

	jQuery("#tabs").tabs({
							ajaxOptions: { async: true },
							select: function(event, ui) {
										jQuery('.viewBody').empty();
									},
							selected: -1
						});
	jQuery("#tabs").tabs('select', viewIndex);
	jQuery("#tabs").tabs('rotate', (rotationTimer * 1000));
	jQuery(".ui-tabs-panel").css('overflow', 'auto');
	jQuery(".setDefault").click(function() {
		setDefault();
	});
	jQuery("#toggleActionBar").live('click', function() {
		jQuery("#actionBar, .imgPathWay, .pathWay, hr").toggle();
	});
	jQuery(document).keydown(function(event) {
		jQuery("#tabs").tabs('rotate', 0);
	});

});

/**
 * Initializes Colorbox
 */
function initColorbox(selector, url, w, h)
{
	jQuery(selector).colorbox({
		href: 			url,
		iframe:			true,
		overlayClose:	false,
		width:			w,
		height:			h,
		opacity:		0.7
	});
}

/**
 * Resize widget iframe
 */
function iResize(ifrm, height)
{
	if (height < 150) {
		height = 150;
	}
	jQuery("[name="+ifrm+"]").height(height);
}
</script>