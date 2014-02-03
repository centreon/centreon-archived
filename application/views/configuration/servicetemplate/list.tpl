{extends file="../../viewLayout.tpl"}

{block name="title"}Service Template{/block}

{block name="content"}
    {datatable object='Servicetemplate'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Servicetemplate' objectUrl='/configuration/servicetemplate/list'}
{/block}
