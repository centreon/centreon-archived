{extends file="file:[Core]viewLayout.tpl"}
{block name="title"}
	{t}Service monitoring{/t}
{/block}
{block name="content"}
	{datatable module=$moduleName object=$objectName configuration=false}
{/block}
{block name="javascript-bottom" append}
	{datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}
{/block}
