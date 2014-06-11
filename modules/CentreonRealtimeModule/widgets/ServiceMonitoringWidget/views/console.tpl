{extends file="file:[Core]widgetLayout.tpl"}
{block name="content"}
	{datatable module=$moduleName datatableObject=$datatableObject object=$objectName configuration=false}
{/block}
{block name="javascript-bottom" append}
	{datatablejs module=$moduleName datatableObject=$datatableObject object=$objectName objectUrl=$objectListUrl}
{/block}
