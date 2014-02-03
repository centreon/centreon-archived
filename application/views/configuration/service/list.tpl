{extends file="../../viewLayout.tpl"}

{block name="title"}Service{/block}

{block name="content"}
    {datatable object='Service'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Service' objectUrl='/configuration/service/list'}
{/block}
