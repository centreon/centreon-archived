<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
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
		$views[$key]['default'] = "";
		if ($viewObj->getDefaultViewId() == $key) {
			$views[$key]['default'] = sprintf(" (%s)", _('default'));
			$views[$key]['default'] = '<span class="ui-icon ui-icon-star" style="float:left;"></span>';
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
