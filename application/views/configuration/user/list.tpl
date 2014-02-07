{extends file="../../viewLayout.tpl"}

{block name="title"}User{/block}

{block name="content"}
    {datatable object='User' objectAddUrl='/configuration/user/add'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='User' objectUrl='/configuration/user/list'}
{/block}
