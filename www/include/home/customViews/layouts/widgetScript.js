'{literal}'
var columnClass = '{/literal}{$columnClass}{literal}';
var viewId =  Number('{/literal}{$view_id}{literal}');
var permission = Number('{/literal}{$permission}{literal}');
var ownership = Number('{/literal}{$ownership}{literal}');
var idUser = Number('{/literal}{$userId}{literal}');
var jsonWidgets = JSON.parse('{/literal}{$jsonWidgets}{literal}');
var deleteWdgtMessage = 'Deleting this widget might impact users with whom you are sharing this view. Are you sure you want to do it?';
var deleteViewMessage = 'Deleting this view might impact other users. Are you sure you want to do it?';
var setDefaultMessage = 'Set this view as your default view?';

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
            jQuery('.widgetTitle').editable('./include/home/customViews/rename.php',
                {
                    id: 'elementId',
                    name: 'newName',
                    event: 'dblclick'
                });
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

        var i = 0;
        Object.keys(jsonWidgets).forEach(function (wId) {
            var oWidget = jsonWidgets[wId];
            createFrame(
                oWidget['url'] + '?widgetId=' + oWidget['widget_id'] + '&customViewId=' + viewId,
                jQuery('#widget_cont_' + oWidget['widget_id']), "widget_" + viewId + "_" + oWidget['widget_id'],
                i
            );
            i++;
        }

        jQuery(".ui-icon-wrench").each(function (index, element) {
            var tmp = jQuery(element).parents('.portlet').attr('name');
            var widgetIndex = tmp.split("portlet_");
            var widgetId = widgetIndex[1];
            initColorbox(jQuery(element), "./main.php?p=10303&min=1&view_id=" + viewId + "&widget_id=" + widgetId, "70%", "70%");
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
    }

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

    function createFrame(url, parent, name, idx) {
        var $frame = jQuery('<iframe/>')
            .attr('src', url)
            .attr('name', name)
            .attr('width', '100%');

        parent.append($frame);
        window.iframes.push($frame);
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
});