{extends file="../../viewLayout.tpl"}

{block name="title"}Service Categories{/block}

{block name="content"}
    {datatable object='Servicecategory'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Servicecategory' objectUrl='/configuration/servicecategory/list'}
{/block}
