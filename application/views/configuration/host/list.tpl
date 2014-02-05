{extends file="../../viewLayout.tpl"}

{block name="title"}Host{/block}

{block name="content"}
    {datatable object='Host' objectAddUrl='/configuration/host/add'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Host' objectUrl='/configuration/host/list'}
{/block}
