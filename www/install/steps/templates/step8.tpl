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
                {if $module.is_installed}
                    {$module.installed_version}
                {else}
                    {$module.available_version}
                {/if}
                </td>
                <td>
                    <div class="md-checkbox md-checkbox-inline">
                    {if $module.is_installed}
                        <input type="checkbox" id="module_{$module_id}" disabled="disabled"/>
                        <label class="empty-label md-label-green" for="module_{$module_id}"></label>
                    {else}
                        <input type="checkbox" id="module_{$module_id}"/>
                        <label class="empty-label" for="module_{$module_id}"></label>
                    {/if}
                    </div>
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
                {if $widget.is_installed}
                    {$widget.installed_version}
                {else}
                    {$widget.available_version}
                {/if}
                </td>
                <td>
                    <div class="md-checkbox md-checkbox-inline">
                    {if $widget.is_installed}
                        <input type="checkbox" id="widget_{$widget_id}" disabled="disabled"/>
                        <label class="empty-label md-label-green" for="widget_{$widget_id}"></label>
                    {else}
                        <input type="checkbox" id="widget_{$widget_id}" />
                        <label class="empty-label" for="widget_{$widget_id}"></label>
                    {/if}
                    </div>
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
                    if (jQuery(this).prop('checked') && !jQuery(this).prop('disabled')) {
                        moduleIds.push(jQuery(this).attr('id').replace('module_', ''));
                    }
                    jQuery(this).attr('disabled', 'disabled');
                });
            }
            if (widgetBoxes && widgetBoxes.length > 0) {
                widgetBoxes.each(function() {
                    if (jQuery(this).prop('checked') && !jQuery(this).prop('disabled')) {
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
                },
                success: (data) => {
                    var data = JSON.parse(data);
                    const modules = data['modules'] ? Object.values(data['modules']) : [];
                    if (modules && modules.length > 0) {
                        modules.forEach(function(module) {
                            if (module.install) {
                                jQuery('label[for="module_' + module.module + '"]').addClass('md-label-green');
                                jQuery("input[type=checkbox]#module_" + module.module)
                                    .attr('disabled', 'disabled')
                                    .prop('checked', 'checked');
                            }
                        });
                    }
                    const widgets = data['widgets'] ? Object.values(data['widgets']) : [];
                    if (widgets && widgets.length > 0) {
                        widgets.forEach(function(widget) {
                            if (widget.install) {
                                jQuery('label[for="widget_' + widget.widget + '"]').addClass('md-label-green');
                                jQuery("input[type=checkbox]#widget_" + widget.widget)
                                    .attr('disabled', 'disabled')
                                    .prop('checked', 'checked');
                            }
                        });
                    }
                    manageButtons();
                },
                complete: () => {
                    installButton.prop('disabled', false)
                        .addClass('bt_info')
                        .prop('value', 'Install');
                    moduleBoxes = jQuery("input[type=checkbox][id^=module_]");
                    widgetBoxes = jQuery("input[type=checkbox][id^=widget_]");
                    jQuery("input[type=checkbox][id^=module_]:not(:checked)").prop('disabled', false);
                    jQuery("input[type=checkbox][id^=widget_]:not(:checked)").prop('disabled', false);
                }
            });
        });

        function manageButtons() {
            var checkboxes = jQuery("input[type=checkbox]:checked:not(:disabled)");
            if (checkboxes.length) {
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