{if isset($error)}
	{$error}
{else}
    <h3 class="text-center">
		<span class="label label-{$state|host_color}">{$title}</span>
	</h3>
	<table class="table table-striped table-condensed">
	{foreach from=$data item=d}
		<tr>
			<td>{$d.label}</td>
			<td>{$d.value}</td>
		</tr>
	{/foreach}
	</table>
{/if}
{hook name="displayHostTooltipDetail" container="" params=$params}
