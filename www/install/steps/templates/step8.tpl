<form id='form_step8'>
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
        <thead>
        <tr>
            <th colspan='1'>{t}Module{/t}</th>
            <th colspan='1'>{t}Author{/t}</th>
            <th colspan='1'>{t}Version{/t}</th>
            <th colspan='1'></th>
        </tr>
        </thead>
        <tbody id='engineParams'>
            {foreach from=$modules item=module key=module_id}
            <tr>
                <td>
                    {$module.rname}
                </td>
                <td>
                    {$module.author}
                </td>
                <td>
                {if !$module.is_installed}
                    {$module.available_version}
                {else}
                    {$module.installed_version}
                {/if}
                </td>
                <td>
                    {if !$module.is_installed}
                    <input id="module_{$module_id}" type="checkbox"/>
                    {else}
                    <img src="../img/icons/checked.png" class="ico-16" />
                    {/if}
                </td>
            </tr>
            {/foreach}
        <tr>
            <th colspan='1'>{t}Widget{/t}</th>
            <th colspan='1'>{t}Author{/t}</th>
            <th colspan='1'>{t}Version{/t}</th>
            <th colspan='1'></th>
        </tr>
        </thead>
        <tbody id='engineParams'>
            {foreach from=$widgets item=widget key=widget_id}
            <tr>
                <td>
                    {$widget.title}
                </td>
                <td>
                    {$widget.author}
                </td>
                <td>
                {if !$widget.is_installed}
                    {$widget.available_version}
                {else}
                    {$widget.installed_version}
                {/if}
                </td>
                <td>
                    {if !$widget.is_installed}
                    <input id="widget_{$widget_id}" type="checkbox"/>
                    {else}
                    <img src="../img/icons/checked.png" class="ico-16" />
                    {/if}
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</form>

<script type="text/javascript">

    {literal}

    jQuery(function() {
        jQuery("input[type=button]#previous").hide();
        var nextButton = jQuery("input[type=button]#next");
        nextButton.parent().append('<input class="btc bt_default bt_info" type="button" id="installModules" value="Install" style="display: none;"/>');
        var installButton = jQuery("input[type=button]#installModules");
        var moduleBoxes = jQuery("input[type=checkbox][id^=module_]");
        var widgetBoxes = jQuery("input[type=checkbox][id^=widget_]");
        jQuery('input[type=checkbox]').each(function() {
            jQuery(this).attr('checked', 'checked');
        });
        manageButtons();

        jQuery('input[type=checkbox]').click(function() {
            manageButtons();
        });

        installButton.on('click', function() {
            installButton.prop('disabled', true)
                .removeClass('bt_info')
                .prop('value', 'Installing...');
            var moduleIds = [];
            var widgetIds = [];
            if (moduleBoxes && moduleBoxes.length > 0) {
                moduleBoxes.each(function() {
                    if (jQuery(this).prop('checked')) {
                        moduleIds.push(jQuery(this).attr('id').replace('module_', ''));
                    }
                    jQuery(this).attr('disabled', 'disabled');
                });
            }
            if (widgetBoxes && widgetBoxes.length > 0) {
                widgetBoxes.each(function() {
                    if (jQuery(this).prop('checked')) {
                        widgetIds.push(jQuery(this).attr('id').replace('widget_', ''));
                    }
                    jQuery(this).attr('disabled', 'disabled');
                });
            }
            jQuery.ajax({
                type: 'POST',
                url: './steps/process/process_step8.php',
                data: {
                    'modules': moduleIds,
                    'widgets': widgetIds
                }
            }).success(function(data) {
                var data = JSON.parse(data);
                if (data['modules'] && data['modules'].length > 0) {
                    data['modules'].forEach(function(module) {
                        if (module.install) {
                            jQuery("input[type=checkbox]#module_" + module.module).after('<img src="../img/icons/checked.png" class="ico-16" />');
                            jQuery("input[type=checkbox]#module_" + module.module).remove();
                        }
                    });
                }
                if (data['widgets'] && data['widgets'].length > 0) {
                    data['widgets'].forEach(function(widget) {
                        if (widget.install) {
                            jQuery("input[type=checkbox]#widget_" + widget.widget).after('<img src="../img/icons/checked.png" class="ico-16" />');
                            jQuery("input[type=checkbox]#widget_" + widget.widget).remove();
                        }
                    });
                }
                jQuery('input[type=checkbox]').each(function() {
                    jQuery(this).attr('disabled', false);
                });
                manageButtons();
            }).complete(function() {
                installButton.prop('disabled', false)
                    .addClass('bt_info')
                    .prop('value', 'Install');
                moduleBoxes = jQuery("input[type=checkbox][id^=module_]");
                widgetBoxes = jQuery("input[type=checkbox][id^=widget_]");
            });
        });

        function manageButtons() {
            var moduleBoxes = jQuery("input[type=checkbox]:checked");
            if (moduleBoxes.length) {
                nextButton.hide();
                installButton.show();
            } else {
                installButton.hide();
                nextButton.show();
            }
        }
    });

    function validation() {
        return true;
    }

    {/literal}

</script>