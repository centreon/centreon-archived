{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{$objectName}{/block}

{block name="content"}
    {datatable module=$moduleName object=$objectName datatableObject=$datatableObject configuration=$displayActionBar}
{/block}

{block name="javascript-bottom" append}
    {datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}
{/block}
