{extends file="../../viewLayout.tpl"}

{block name="title"}Host Categories{/block}

{block name="content"}
    {datatable object='Hostcategory'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Hostcategory' objectUrl='/configuration/hostcategory/list'}
{/block}
