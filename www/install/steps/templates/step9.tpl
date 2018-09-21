<div id="installPub">
    <div class="install-pub-wrapper">
        <div class="pub-header">
            <p class="header-text">
                Thank you for installing
                <b>Centreon</b>
            </p>
            <small>We hope you will enjoy your monitoring experience</small>
        </div>
        <div class="pub-body">
            <div class="left-side">
                <img src="../img/adv-imp.png" alt="'imp" border="0"/>
            </div>
            <div class="right-side">
                <form id='form_step9'>
                    <table cellpadding='0' cellspacing='0' border='0' class='StyleDottedHr' align='center'>
                        <tbody>
                        <tr>
                            <td class='formValue'><input value='1' name='send_statistics' type="checkbox" checked/></td>
                            <td class='formlabel'>
                                <p style="text-align:justify">I agree to participate to the Centreon Customer Experience
                                    Improvement Program whereby anonymous information about the usage of this server
                                    may be sent to Centreon. This information will solely be used to improve the
                                    software user experience. I will be able to opt-out at anytime.
                                    Refer to
                                    <a href="http://ceip.centreon.com/">ceip.centreon.com</a>
                                    for further details.
                                </p>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    {literal}
    function validation() {
        jQuery.ajax({
            type: 'POST',
            url: './steps/process/process_step9.php',
            data: jQuery('input[name="send_statistics"]').serialize(),
        }).success(function (data) {
            var data = JSON.parse(data);
            if (data.result) {
                javascript:self.location = "../main.php";
            }
        });
    }

    {/literal}
</script>