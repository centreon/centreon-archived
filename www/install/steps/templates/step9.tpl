<form id='form_step9'>
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
        <thead>
        <tr>
            <th colspan='2'>{t}Help Centreon{/t}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class='formlabel'>{t}Accepter d envoyer les données{/t}</td>

            <td class='formValue'><input value='1' name='send_statistics' type="checkbox" {if $parameters}checked{/if}/></td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">
    {literal}

    function validation() {
        jQuery.ajax({
            type: 'POST',
            url: './steps/process/process_step9.php',
            data: jQuery('input[name="send_statistics"]').serialize(),
        }).success(function () {
                nextStep();
        });

        return false;
    }
    {/literal}
</script>
