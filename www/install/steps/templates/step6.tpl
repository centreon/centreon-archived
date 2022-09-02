<form id='form_step6'>
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
        <thead>
        <tr>
            <th colspan='2'>{t}Database information{/t}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class='formlabel'>{t}Database Host Address (default: localhost){/t}</td>
            <td class='formvalue'>
                <input type='text' name='address' value='{$parameters.address}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Database Port (default: 3306){/t}</td>
            <td class='formvalue'>
                <input type='text' name='port' value='{$parameters.port}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Root user (default: root){/t}</td>
            <td class='formvalue'>
                <input type='text' name='root_user' value='{$parameters.root_user}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Root password{/t}</td>
            <td class='formvalue'>
                <input type='password' name='root_password' value='{$parameters.root_password}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Configuration database name{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='text' name='db_configuration' value='{$parameters.db_configuration}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Storage database name{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='text' name='db_storage' value='{$parameters.db_storage}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Database user name{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='text' name='db_user' value='{$parameters.db_user}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Database user password{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='password' name='db_password' value='{$parameters.db_password}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Confirm user password{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='password' name='db_password_confirm' value='{$parameters.db_password_confirm}' />
                <label class='field_msg'></label>
            </td>
        </tr>
        </tbody>
    </table>
</form>

<script type="text/javascript">

    {literal}

    function validation() {
        jQuery('.field_msg').empty();

        jQuery.ajax({
            type: 'POST',
            url: './steps/process/process_step6.php',
            data: jQuery('#form_step6').serialize(),
            success: (data) => {
                var result = JSON.parse(data);
                if (!result.required.length && result.password && result.connection == '') {
                    loadStep("nextStep");
                } else {
                    result.required.forEach(function (element) {
                        jQuery("input[name=" + element + "]").next().html("Parameter is required");
                    });
                    if (!result.password) {
                        jQuery('input[name="db_password_confirm"]').next().html("Password does not match");
                    }
                    if (result.connection != '') {
                        jQuery('input[name="address"]').next().html(result.connection);
                    }
                }
            }
        });

        return false;
    }

    {/literal}

</script>