{extends file="../../viewLayout.tpl"}

{block name="title"}HostGroup{/block}

{block name="content"}
    {datatable object='Hostgroup' objectAddUrl='/configuration/hostgroup/add'}
{/block}

{block name="javascript-bottom" append}
    {datatablejs object='Hostgroup' objectUrl='/configuration/hostgroup/list'}
{/block}
