{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{$objectName}{/block}

{block name="content"}
    <script>
        var jsUrl = {$jsUrl|json_encode};
    </script>
    {datatable module=$moduleName object=$objectName datatableObject=$datatableObject objectAddUrl=$objectAddUrl configuration=true}
{/block}

{block name="javascript-bottom" append}
    {datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}
    
{/block}
