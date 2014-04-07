{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{$objectName}{/block}

{block name="content"}
    {datatable object=$objectName objectAddUrl=$objectAddUrl configuration=true}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object=$objectName objectUrl=$objectListUrl}
{/block}
