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
            {foreach from=$modules item=module}
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
                    <input id="{$module.name}" type="checkbox"/>
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
        nextButton.parent().append('<input class="btc bt_info" type="button" id="installModules" value="Install" style="display: none;"/>');
        var installButton = jQuery("input[type=button]#installModules");
        var moduleBoxes = jQuery("input[type=checkbox]");
        moduleBoxes.each(function() {
            jQuery(this).attr('checked', 'checked');
        });
        manageButtons();

        jQuery('input[type=checkbox]').click(function() {
            manageButtons();
        });

        installButton.on('click', function() {
            installButton.prop('disabled', true);
            var moduleBoxes = jQuery("input[type=checkbox]:checked");
            var moduleNames = [];
            moduleBoxes.each(function() {
                moduleNames.push(jQuery(this).attr('id'));
            });
            jQuery.ajax({
                type: 'POST',
                url: './steps/process/process_step8.php',
                data: {'modules':moduleNames}
            }).success(function(data) {
                var data = JSON.parse(data);
                data.forEach(function(module) {
                    if (module.install) {
                        jQuery("input[type=checkbox]#" + module.module).after('<img src="../img/icons/checked.png" class="ico-16" />');
                        jQuery("input[type=checkbox]#" + module.module).remove();
                    }
                });
                manageButtons();
            }).complete(function() {
                installButton.prop('disabled', false);
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