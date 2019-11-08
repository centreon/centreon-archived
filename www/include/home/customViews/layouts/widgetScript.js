'{literal}'
var columnClass = '{/literal}{$columnClass}{literal}';
var viewId = Number('{/literal}{$view_id}{literal}');
var permission = Number('{/literal}{$permission}{literal}');
var ownership = Number('{/literal}{$ownership}{literal}');
var idUser = Number('{/literal}{$userId}{literal}');
var jsonWidgets = JSON.parse('{/literal}{$jsonWidgets}{literal}');

jQuery(function () {
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
                connectWith: '.' + columnClass,
                handle: '.portlet-header',
                cursor: 'move',
                scroll: false,
                stop: function (event, ui) {
                    jQuery('.portlet-content').each(function () {
                        if (jQuery(this).parent().find('.ui-icon-minusthick').length) {
                            jQuery(this).show();
                        }
                    });
                    savePositions();
                },
                start: function () {
                    jQuery('.portlet-content').hide();
                }
            });
            jQuery('.widgetTitle').editable(
                './include/home/customViews/rename.php',
                {
                    id: 'elementId',
                    name: 'newName',
                    event: 'dblclick'
                }
            );
            wrenchSpan = '<span class="ui-icon ui-icon-wrench"></span>';
            trashSpan = '<span class="ui-icon ui-icon-trash"></span>';
        }

        if (!ownership) {
            jQuery('.shareView').button('disable');
        } else {
            jQuery('.shareView').button('enable');
        }
        jQuery('.deleteView').button('enable');

        jQuery(".portlet").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all")
            .find(".portlet-header")
            .addClass("ui-widget-header ui-corner-all")
            .prepend('<span class="ui-icon ui-icon-refresh"></span>')
            .prepend(wrenchSpan)
            .prepend(trashSpan)
            .prepend('<span class="show-hide ui-icon ui-icon-minusthick"></span>')
            .end()
            .find(".portlet-content");

        jQuery(".portlet-header .show-hide").click(function () {
            jQuery(this).toggleClass("ui-icon-minusthick").toggleClass("ui-icon-plusthick");
            jQuery(this).parents(".portlet:first").find(".portlet-content").toggle();
        });

        Object.keys(jsonWidgets).forEach(function (wId) {
            var oWidget = jsonWidgets[wId];
            jQuery("[name=widget_" + viewId + "_" +  oWidget['widget_id'] + "]").attr(
                'src',
                oWidget['url'] +'?widgetId=' + oWidget['widget_id'] +'&customViewId=' + viewId
            );
        })
    }

    jQuery(".ui-icon-wrench").each(function (index, element) {
        var tmp = jQuery(element).parents('.portlet').attr('name'),
            widgetIndex = tmp.split("portlet_"),
            widgetId = widgetIndex[1];

        jQuery(element).on('click', function () {
            var popin = jQuery('<div id="config-popin">');
            var url = './api/internal.php?object=centreon_home_customview&resultFormat=html&action=preferences' +
                '&viewId=' + viewId + '&widgetId=' + widgetId;
            popin.centreonPopin({
                url: url,
                open: true,
                ajaxType: 'GET',
                ajaxDataType: 'html'
            });
        });
    });

    jQuery(".ui-icon-refresh").each(function (index, element) {
        var tmp = jQuery(element).parents('.portlet').attr('name');
        var widgetIndex = tmp.split("portlet_");
        var widgetId = widgetIndex[1];
        jQuery(element).click(function () {
            window.frames["widget_" + viewId + "_" + widgetId].location.reload();
        });
    });

    jQuery("span[class='ui-icon ui-icon-trash']").each(function (index, element) {
        var tmp = jQuery(element).parents('.portlet').attr('name')
        var widgetIndex = tmp.split("portlet_");
        var widgetId = widgetIndex[1];
        deleteWidget(element, viewId, widgetId);
    });
});

/**
 * Delete View
 */
function deleteView() {
    if (confirm(deleteViewMessage)) {
        jQuery.ajax({
            type: "POST",
            dataType: "xml",
            url: "./include/home/customViews/action.php",
            data: {
                action: "deleteView",
                custom_view_id: viewId
            },
            success: function (response) {
                var view = response.getElementsByTagName('custom_view_id');
                if (typeof (view) != 'undefined') {
                    window.top.location = './main.php?p=103';
                } else if (typeof (error) != 'undefined') {
                    var errorMsg = error.item(0).firstChild.data;
                }
            }
        });
    }
}

/**
 * Delete Widget
 */
function deleteWidget(element, viewId, widgetId) {
    jQuery(element).click(function () {
        if (confirm(deleteWdgtMessage)) {
            jQuery.ajax({
                type: "POST",
                dataType: "xml",
                url: "./include/home/customViews/action.php",
                data: {
                    action: "deleteWidget",
                    custom_view_id: viewId,
                    widget_id: widgetId
                },
                success: function (response) {
                    var view = response.getElementsByTagName('custom_view_id');
                    var error = response.getElementsByTagName('error');
                    if (typeof (view) != 'undefined') {
                        var viewId = view.item(0).firstChild.data;
                        jQuery('#tabs').tabs('load', getTabPos(viewId));
                        //window.top.location = './main.php?p=103&currentView='+viewId;
                    } else if (typeof (error) != 'undefined') {
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
function savePositions() {
    var tab = new Array();
    var i = 0;
    jQuery('.' + columnClass).each(function (columnNumber, element) {
        jQuery(element).children('.portlet').each(function (rowNumber, element) {
            if (jQuery(element).attr('name')) {
                var tmp = jQuery(element).attr('name').split("portlet_");
                var order = columnNumber + '_' + rowNumber;
                tab[i] = order + '_' + tmp[1];
                i++;
            }
        });
    });
    jQuery.ajax({
        type: "POST",
        dataType: "xml",
        url: "./include/home/customViews/action.php",
        data: {
            action: "position",
            custom_view_id: viewId,
            positions: tab
        }
    });
}

/**
 * Set default
 */
function setDefault() {
    if (confirm(setDefaultMessage)) {
        jQuery.ajax({
            type: "POST",
            dataType: "xml",
            url: "./include/home/customViews/action.php",
            data: {
                action: "setDefault",
                custom_view_id: viewId
            }
        });
    }
}
'{/literal}'
