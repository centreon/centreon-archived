<form id='form_step3'>
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
        <thead>
        <tr>
            <th colspan='2'>{t}Monitoring engine information{/t}</th>
        </tr>
        </thead>
        <tbody id='engineParams'>
            {foreach from=$parameters item=parameter}
            <tr>
                <td class='formlabel'>
                    {$parameter.label}
                    {if $parameter.required}
                        <span style='color:#e00b3d'> *</span>
                    {/if}
                </td>
                <td class='formvalue'>
                    <input type='text' name='{$parameter.name}' value='{$parameter.value}' size='30' />
                    <label class='field_msg'></label>
                </td>
            </tr>
            {/foreach}
        </tbody>
    </table>
</form>

<script type="text/javascript">

    {literal}

    function validation() {
        jQuery('.field_msg').empty();

        jQuery.ajax({
            type: 'POST',
            url: './steps/process/process_step3.php',
            data: jQuery('#form_step3').serialize(),
            success: (data) => {
                var errors = JSON.parse(data);
                if (!errors.required.length && !errors.directory_not_found.length && !errors.file_not_found.length) {
                    loadStep("nextStep");
                } else {
                    errors.required.forEach(function(element){
                        jQuery("input[name=" + element + "]").next().html("Parameter is required");
                    });
                    errors.directory_not_found.forEach(function(element){
                        jQuery("input[name=" + element + "]").next().html("Directory not found");
                    });
                    errors.file_not_found.forEach(function(element){
                        jQuery("input[name=" + element + "]").next().html("File not found");
                    });
                }
            }
        });

        return false;
    }

    {/literal}

</script>