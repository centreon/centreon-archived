{extends file="../../viewLayout.tpl"}

{block name="title"}Connector{/block}

{block name="content"}
    {datatable object='Connector' objectAddUrl='/configuration/connector/add'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Connector' objectUrl='/configuration/connector/list'}
{/block}
