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

require_once "@CENTREON_ETC@/centreon.conf.php";
require_once $centreon_path . 'www/class/centreon.class.php';
require_once $centreon_path . 'www/class/centreonSession.class.php';
require_once $centreon_path . 'www/class/centreonCustomView.class.php';
require_once $centreon_path . 'www/class/centreonWidget.class.php';
require_once $centreon_path . 'www/class/centreonDB.class.php';

session_start();

try {
    require_once $centreon_path ."GPL_LIB/Smarty/libs/Smarty.class.php";

    if (!isset($_SESSION['centreon'])) {
        throw new Exception('No session found');
    }
    $centreon = $_SESSION['centreon'];
    $db = new CentreonDB();
    $locale = $centreon->user->get_lang();
	putenv("LANG=$locale");
	setlocale(LC_ALL, $locale);
	bindtextdomain("messages",  $centreon_path . "www/locale/");;
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
    
    if (CentreonSession::checkSession(session_id(), $db) == 0) {
        throw new Exception('Invalid session');
    }
    $viewObj = new CentreonCustomView($centreon, $db);
    $widgetObj = new CentreonWidget($centreon, $db);

    /**
	 * Smarty
	 */
    $path = $centreon_path . "www/include/home/customViews/layouts/";
    $template = new Smarty();
    $template = initSmartyTplForPopup($path, $template, "./", $centreon_path);

    $columnClass = "";
    $viewId = $viewObj->getCurrentView();
    $permission = $viewObj->checkPermission($viewId);
    $ownership = $viewObj->checkOwnership($viewId);
    $widgets = array();
    $columnClass = "column_1";
    $widgetNumber = 0;
    if ($viewId) {
        $columnClass = $viewObj->getLayout($viewId);
        $widgets = $widgetObj->getWidgetsFromViewId($viewId);
        foreach ($widgets as $widgetId => $val) {
            if (isset($widgets[$widgetId]['widget_order']) && $widgets[$widgetId]['widget_order']) {
                $tmp = explode("_", $widgets[$widgetId]['widget_order']);
                $widgets[$widgetId]['column'] = $tmp[0];
            } else {
                $widgets[$widgetId]['column'] = 0;
            }
            $widgetNumber++;
        }
        $template->assign("columnClass", $columnClass);
        $template->assign("widgets", $widgets);
    }
    $template->assign("widgetNumber", $widgetNumber);
    $template->assign("view_id", $viewId);
    $template->assign("error_msg", _("No widget configured in this view. Please add a new widget with the \"Add widget\" button."));
    
    $template->display($columnClass.".ihtml");
} catch (CentreonWidgetException $e) {
    echo $e->getMessage() . "<br/>";
} catch (CentreonCustomViewException $e) {
    echo $e->getMessage() . "<br/>";
} catch (Exception $e) {
    echo $e->getMessage() . "<br/>";
}
?>
<script type="text/javascript">
var columnClass = "<?php echo $columnClass;?>";
var viewId = "<?php echo $viewId;?>";
var deleteWdgtMessage = "<?php echo _("Deleting this widget might impact users with whom you are sharing this view. Are you sure you want to do it?");?>";
var deleteViewMessage = "<?php echo _("Deleting this view might impact other users. Are you sure you want to do it?");?>";
var setDefaultMessage = "<?php echo _("Set this view as your default view?");?>";
var permission = <?php echo ($permission === true) ? 1 : 0; ?>;
var ownership = <?php echo ($ownership === true) ? 1 : 0; ?>;
var wrenchSpan = '<span class="ui-icon ui-icon-wrench"></span>';
var trashSpan = '<span class="ui-icon ui-icon-trash"></span>';

jQuery(function() {
	if (columnClass) {
    	if (!permission) {
    		jQuery('.addWidget').button('disable');
    		jQuery('.editView').button('disable');
            wrenchSpan = '<span></span>';
    		trashSpan = '<span></span>';
    	} else {
    		jQuery('.shareView').button('enable');
    		jQuery('.addWidget').button('enable');
    		jQuery('.editView').button('enable');
    		jQuery('.widgetBody').sortable({
        		connectWith: '.'+columnClass,
        		handle: '.portlet-header',
        		cursor: 'move',
        		scroll: false,
        		stop: function(event, ui) {
    				jQuery('.portlet-content').each(function() {
						if (jQuery(this).parent().find('.ui-icon-minusthick').length) {
    						jQuery(this).show();
						}
					});
					savePositions();
        		},
    			start: function() {
					jQuery('.portlet-content').hide();
            	}
        	});
        	jQuery('.widgetTitle').editable('./include/home/customViews/rename.php',
                							{
												id			: 'elementId',
												name		: 'newName',
												event		: 'dblclick'
                							});
    	}

    	if (!ownership) {
    		jQuery('.shareView').button('disable');
    		jQuery('.deleteView').button('disable');
		} else {
			jQuery('.shareView').button('enable');
        	jQuery('.deleteView').button('enable');
		}

    	jQuery(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
    		.find(".portlet-header")
    			.addClass("ui-widget-header ui-corner-all")
    			.prepend('<span class="ui-icon ui-icon-refresh"></span>')
    			.prepend(wrenchSpan)
    			.prepend(trashSpan)
    			.prepend('<span class="show-hide ui-icon ui-icon-minusthick"></span>')
    			.end()
    		.find(".portlet-content");

    	jQuery(".portlet-header .show-hide").click(function() {
    		jQuery(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
    		jQuery(this).parents(".portlet:first").find(".portlet-content").toggle();
    	});

    	<?php foreach ($widgets as $widgetId => $widget) { ?>
			jQuery("[name=widget_" + viewId +  "_<?php echo $widgetId;?>]").attr('src', '<?php echo $widget['url']; ?>?widgetId='+<?php echo $widgetId;?>);
    	<?php } ?>
	}

	initColorbox(".editView", "./main.php?p=10301&min=1&action=edit&view_id="+viewId, "70%", "25%");
	initColorbox(".shareView", "./main.php?p=10302&min=1&view_id="+viewId, "70%", "70%");
	initColorbox(".addWidget", "./main.php?p=10304&min=1&action=addWidget&view_id="+viewId, "70%", "70%");
	initColorbox(".setRotate", "./main.php?p=10305&min=1&view_id="+viewId, "30%", "20%");

	jQuery(".ui-icon-wrench").each(function(index, element) {
										var tmp = jQuery(element).parents('.portlet').attr('name')
										var widgetIndex = tmp.split("portlet_");
										var widgetId = widgetIndex[1];
										initColorbox(jQuery(element), "./main.php?p=10303&min=1&view_id="+viewId+"&widget_id="+widgetId, "70%", "70%");
								   });

	jQuery(".ui-icon-refresh").each(function(index, element) {
		var tmp = jQuery(element).parents('.portlet').attr('name')
		var widgetIndex = tmp.split("portlet_");
		var widgetId = widgetIndex[1];
		jQuery(element).click(function() {
			window.frames["widget_" + viewId +  "_" + widgetId].location.reload();
		});
   	});

	jQuery("span[class='ui-icon ui-icon-trash']").each(function(index, element) {
                                		var tmp = jQuery(element).parents('.portlet').attr('name')
                                		var widgetIndex = tmp.split("portlet_");
                                		var widgetId = widgetIndex[1];
                                		deleteWidget(element, viewId, widgetId);
    								   });
});

/**
 * Delete View
 */
function deleteView()
{
	if (confirm(deleteViewMessage)) {
			jQuery.ajax({
				type	:	"POST",
				dataType:	"xml",
				url 	:	"./include/home/customViews/action.php",
				data	:   {
								action			:	"deleteView",
								custom_view_id  :	viewId
							},
				success :	function(response) {
								var view = response.getElementsByTagName('custom_view_id');
								if (typeof(view) != 'undefined') {
									window.top.location = './main.php?p=103';
								} else if (typeof(error) != 'undefined') {
									var errorMsg = error.item(0).firstChild.data;
								}
							}
			});
	}
}

/**
 * Delete Widget
 */
function deleteWidget(element, viewId, widgetId)
{
	jQuery(element).click(function() {
		if (confirm(deleteWdgtMessage)) {
			jQuery.ajax({
				type	:	"POST",
				dataType:	"xml",
				url 	:	"./include/home/customViews/action.php",
				data	:   {
								action			:	"deleteWidget",
								custom_view_id  :	viewId,
								widget_id		:	widgetId
							},
				success :	function(response) {
								var view = response.getElementsByTagName('custom_view_id');
								var error = response.getElementsByTagName('error');
								if (typeof(view) != 'undefined') {
									var viewId = view.item(0).firstChild.data;
									window.top.location = './main.php?p=103&currentView='+viewId;
								} else if (typeof(error) != 'undefined') {
									var errorMsg = error.item(0).firstChild.data;
								}
							}
			});
		}
	});
}

/**
 * Save widget positions
 */
function savePositions()
{
	var tab = new Array();
	var i = 0;
	jQuery('.'+columnClass).each(function(columnNumber, element) {
			jQuery(element).children('.portlet').each(function(rowNumber, element) {
				if (jQuery(element).attr('name')) {
    				var tmp = jQuery(element).attr('name').split("portlet_");
    				var order = columnNumber + '_' + rowNumber;
    				tab[i] = order + '_' +tmp[1];
    				i++;
				}
			});
	});
	jQuery.ajax({
		type	:	"POST",
		dataType:	"xml",
		url 	:	"./include/home/customViews/action.php",
		data	:   {
						action			:	"position",
						custom_view_id  :	viewId,
						positions		:	tab
					}
	});
}

/**
 * Set default
 */
function setDefault()
{
	if (confirm(setDefaultMessage)) {
    	jQuery.ajax({
    		type	:	"POST",
    		dataType:	"xml",
    		url 	:	"./include/home/customViews/action.php",
    		data	:   {
    						action			:	"setDefault",
    						custom_view_id  :	viewId
    					}
    	});
	}
}
</script>
