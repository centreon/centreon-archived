{extends file="../../viewLayout.tpl"}

{block name="title"}Service{/block}

{block name="content"}
    {datatable object='Service' objectAddUrl='/configuration/service/add'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Service' objectUrl='/configuration/service/list'}
{/block}
