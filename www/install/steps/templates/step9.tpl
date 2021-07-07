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
                            <td class='formValue'>
                                <div class='md-checkbox md-checkbox-inline' style='display:none;'>
                                    <input id='send_statistics' value='1' name='send_statistics' type='checkbox' checked='checked'/>
                                    <label class="empty-label" for='send_statistics'></label>
                                </div>
                            </td>
                            <td class='formlabel'>
                                <p style="text-align:justify">Centreon uses a telemetry system and a Centreon Customer Experience
                                    Improvement Program whereby anonymous information about the usage of this server
                                    may be sent to Centreon. This information will solely be used to improve the
                                    software user experience. You will be able to opt-out at any time about CEIP program
                                    through administration menu.
                                    Refer to
                                    <a target="_blank" style="text-decoration: underline"
                                       href="http://ceip.centreon.com/">ceip.centreon.com</a>
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
            success: (data) => {
                var data = JSON.parse(data);
                if (data.result) {
                    javascript:self.location = "../index.php";
                }
            }
        });
    }

    {/literal}
</script>
