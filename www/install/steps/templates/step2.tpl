<table cellpadding='0' cellspacing='0' border='0' width='100%' class='StyleDottedHr' align='center'>
    <tr>
        <th>{t}Module name{/t}</th>
        <th>{t}File{/t}</th>
        <th>{t}Status{/t}</th>
    </tr>
    {foreach from=$libs.loaded key=name item=value}
    <tr>
        <td>{$name}</td>
        <td>{$value}</td>
        <td><span style="color:#88b917; font-weight:bold;">{t}Loaded{/t}</span></td>
    </tr>
    {/foreach}
    {foreach from=$libs.unloaded key=name item=value}
    <tr>
        <td>{$name}</td>
        <td>{$value}</td>
        <td><span style="color:#e00b3d; font-weight:bold;">{t}Not loaded{/t}</span></td>
    </tr>
    {/foreach}
</table>

<script type="text/javascript">

    {literal}

    function validation() {
        return true;
    }

    {/literal}

</script>