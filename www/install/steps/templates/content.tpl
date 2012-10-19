<table cellspacing="0" cellpadding="0" border="0" align="center" class="shell">
    <tr height="83" style="background-image: url('../img/bg_banner.gif');">
        <th width="400" height="83">{$step} - {$title}</th>
        <th width="200" height="83" style="text-align: right; padding: 0px;">
            <a href="http://www.centreon.com" target="_blank"><img src="../img/centreon.gif" alt="Centreon" border="0" style='padding-right:15px;padding-top:10px;'></a>
        </th>
    </tr>
    <tr>
        <td align="left" colspan="2">
            <hr>
            <table width='100%' cellspacing="0" cellpadding="0" border="0" class="stdTable">
                <tr>
                    <td>{$content}</td>
                </tr>
            </table>
        </td>
    </tr>
    <tr style='height:40px;'>
        <td align='right' colspan='2'>
        {if ($step-1 && !$blockPreview)}
        <input class='button' type='button' id='previous' value='Back' onClick='jumpTo({$step-1});'/>
        {/if}
        <input class='button' type='button' id='refresh' value='Refresh' onClick='jumpTo({$step});'/>
        {if !$finish}
        <input class='button' type='button' id='next' value='Next' onClick='if (validation() == true) jumpTo({$step+1});'/>
        {else}
        <input class='button' type='button' id='finish' value='Finish' onClick='javascript:self.location="../main.php"'/>
        {/if}
        </td>
    </tr>
</table>
