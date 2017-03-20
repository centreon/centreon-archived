function CheckModule() {
    jQuery.ajax({
        type: 'POST',
        url: 'include/options/oreon/modules/moduleDependenciesValidator.php',
        data:
            {
                mydata: 1,
                mydata2: 2
            },
        success: function(data) {
            displayResults(data);
        }
    });
}

function displayResults(moduleList) {
    Object.keys(moduleList).forEach(function(moduleName) {
        module = moduleList[moduleName];
        myModuleStatusSpan = jQuery('#' + moduleName);
        myModuleStatusSpan.empty();

        if (module["status"] === "critical") {
            modalBoxId = 'criticalBox_' + moduleName;
            jQuery('#' + modalBoxId).remove();
            var criticalModalBox = '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable" ';
            criticalModalBox += 'id="'+ modalBoxId +'"';
            criticalModalBox += 'style="margin-right: auto; margin-left: auto;" title="Module Error">';

            myModuleStatusSpan.append('<a href="#" onclick="jQuery(\'#'+modalBoxId+'\').dialog(\'open\');" ><img id="img_critical_'+ moduleName +'" src="img/icons/cross.png" class="ico-16" /></a>');
            statusStyle = 'ui-tooltip-red ui-tooltip-shadow';

            statusMessage = "";
            solution = "";
            if (module["message"] instanceof Array) {
                for (var j = 0; j < module["message"].length; j++) {
                    statusMessage += module["message"][j]['ErrorMessage'] + '<br />';
                    solution += module["message"][j]['Solution'] + '<br />';
                }
            } else {
                statusMessage = module["message"]['ErrorMessage'];
                solution = module["message"]['Solution']+ '<br /><br />';
            }

            criticalModalBox += solution;
            criticalModalBox += '</div>';
            statusMessage += '<br />Click to check available actions to fix it.';
            tooltipReferer = '#img_critical_'+ moduleName;
            jQuery('body').append(criticalModalBox);
            jQuery('#' + modalBoxId).dialog({ autoOpen: false });
            jQuery('#' + modalBoxId).dialog( "option", "show", { effect: 'drop', direction: "up" } );
            jQuery('#' + modalBoxId).dialog( "option", "hide", { effect: 'drop', direction: "down" } );
            jQuery('#' + modalBoxId).dialog( "option", "modal", true );
        } else if (module["status"] === "warning") {
            myModuleStatusSpan.append('<img id="img_ok_'+ moduleName + '" src="img/icons/warning.png" class="ico-16" />');
            statusMessage = 'The module is fully functional';
            statusStyle = 'ui-tooltip-red ui-tooltip-shadow';
            tooltipReferer = '#img_ok_'+ moduleName;
        } else {
            myModuleStatusSpan.append('<img id="img_ok_'+ moduleName + '" src="img/icons/checked.png" class="ico-16" />');
            statusStyle = 'ui-tooltip-green ui-tooltip-shadow';
            statusMessage = 'The module is fully functional';
            tooltipReferer = '#img_ok_'+ moduleName;
        }

        if ((module["customAction"] !== undefined) && (module["customAction"] !== "")) {
            modalBoxId = 'customActionModalBox_' + moduleName;
            jQuery('#' + modalBoxId).remove();
            jQuery('#customActionIcon_'+ moduleName).remove();
            var customActionModalBox = '<div class="ui-dialog ui-widget ui-widget-content ui-corner-all ui-draggable ui-resizable" ';
            customActionModalBox += 'id="'+ modalBoxId +'"';
            customActionModalBox += 'style="margin-right: auto; margin-left: auto;" title="'+module["customActionName"]+'">';
            customActionModalBox += module["customAction"];
            customActionModalBox += '</div>';
            customActionIcon = '<span id="customActionIcon_'+moduleName+'"><a href="#" onclick="jQuery(\'#'+modalBoxId+'\').dialog(\'open\');" ><img id="custom_action_'+ moduleName + '" src="img/icons/wrench.gif" class="ico-16" /></a></span>';
            jQuery('body').append(customActionModalBox);
            jQuery('#' + modalBoxId).dialog({ autoOpen: false });
            jQuery('#' + modalBoxId).dialog( "option", "show", { effect: 'drop', direction: "up" } );
            jQuery('#' + modalBoxId).dialog( "option", "hide", { effect: 'drop', direction: "down" } );
            jQuery('#' + modalBoxId).dialog( "option", "modal", true );
            jQuery('#action_'+ moduleName).prepend(customActionIcon);
        }

        jQuery(tooltipReferer).qtip({
            content: statusMessage,
            style: {
                classes: statusStyle
            },
            show: {
                event: "mouseover"
            },
            hide: {
                event: "mouseout"
            },
            position: {
                at: "bottom left",
                my: "top right"
            }
        });
        myModuleStatusSpan = null;
    });
}

jQuery(document).ready(function() {
    CheckModule();
});
