{extends file="../../viewLayout.tpl"}

{block name="title"}HostTemplate{/block}

{block name="content"}
    {datatable object='HostTemplate' objectAddUrl='/configuration/hosttemplate/add'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='HostTemplate' objectUrl='/configuration/hosttemplate/list'}
{/block}
