{extends file="file:[Core]widgetLayout.tpl"}
{block name="content"}
	{datatable module=$moduleName object=$objectName configuration=false}
{/block}
{block name="javascript-bottom" append}
	{datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}
{/block}
