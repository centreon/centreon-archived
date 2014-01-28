{extends file="../../viewLayout.tpl"}

{block name="title"}Host{/block}

{block name="content"}
    {datatable object='Host'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Host' objectUrl='/configuration/host/datatable'}
{/block}
