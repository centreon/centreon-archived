<table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
    <tr class="install-header">
        <th class="logo-wrapper">
            <a href="http://www.centreon.com" target="_blank"><img src="../img/centreon.png" alt="Centreon" border="0"/></a>
        </th>
        <th class="step-wrapper">
            <h3><span>{$step}</span> {$title}</h3>
        </th>

    </tr>
    <tr class="install-body">
        <td align="left" colspan="2">
            <table width='100%' cellspacing="0" cellpadding="0" border="0" class="stdTable">
                <tr>
                    <td>{$content}</td>
                </tr>
            </table>
        </td>
    </tr>

    <tr style='height:40px;'>
        <td>
            {if $finish}
                <p class="link-group">
                    <a href="https://documentation.centreon.com" target="_blank">Documentation</a> |
                    <a href="https://github.com/centreon/centreon" target="_blank">Github </a> |
                    <a href="http://centreon.github.io/" target="_blank">Community Slack</a> |
                    <a href="http://support.centreon.com" target="_blank">Support</a>
                    <b><a href=" https://www.centreon.com" target="_blank">www.centreon.com</a></b>
                </p>
            {/if}
        </td>

        <td align='right'>
            {if ($step-1 && !$blockPreview)}
                <input class='btc bt_back_install' type='button' id='previous' value='Back' onClick='jumpTo({$step-1});'/>
            {/if}
            <input class='btc bt_default' type='button' id='refresh' value='Refresh' onClick='jumpTo({$step});'/>
            {if (!isset($valid) || $valid)}
                {if !$finish}
                    <input class='btc bt_info' type='button' id='next' value='Next'
                        onClick='if (validation() == true) jumpTo({$step+1});'/>
                {else}
                    <input class='btc bt_success' type='button' id='finish' value='Finish'
                        onClick='validation();' />
                {/if}
            {/if}
        </td>
    </tr>
</table>