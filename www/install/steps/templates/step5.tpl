<form id='form_step5'>
    <table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
        <thead>
        <tr>
            <th colspan='2'>{t}Admin information{/t}</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <td class='formlabel'>{t}Login{/t}</td>
            <td class='formvalue'>
                <label>admin</label>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Password{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='password' name='admin_password' value='{$parameters.admin_password}'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Confirm password{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='password' name='confirm_password' value='{$parameters.confirm_password}'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}First name{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='text' name='firstname' value='{$parameters.firstname}'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Last name{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='text' name='lastname' value='{$parameters.lastname}'/>
                <label class='field_msg'></label>
            </td>
        </tr>
        <tr>
            <td class='formlabel'>{t}Email{/t}<span style='color:#e00b3d'> *</span></td>
            <td class='formvalue'>
                <input type='text' name='email' value='{$parameters.email}'/>
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
            url: './steps/process/process_step5.php',
            data: jQuery('#form_step5').serialize(),
            success: (data) => {
                var result = JSON.parse(data);
                if (!result.required.length && result.email && result.password && result.password_security_policy) {
                    loadStep("nextStep");
                } else {
                    result.required.forEach(function (element) {
                        jQuery("input[name=" + element + "]").next().html("Parameter is required");
                    });
                    if (!result.email) {
                        jQuery('input[name="email"]').next().html("Email address is not valid");
                    }
                    if (!result.password) {
                        jQuery('input[name="confirm_password"]').next().html("Password does not match");
                    };
                    if (!result.password_security_policy) {
                        jQuery(
                            'input[name="admin_password"]').next().html("Password must contain at least: " +
                            "1 letter uppercase, 1 letter lowercase, 1 number, 1 special character from '@$!%*?&' " +
                            "and should be at least 12 characters long");
                    };
                }
            }
        });

        return false;
    }

    {/literal}

</script>