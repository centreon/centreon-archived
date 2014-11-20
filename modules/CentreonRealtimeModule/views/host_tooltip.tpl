{if isset($error)}
	{$error}
{else}
<div class='centreon_table' style='padding:0;margin:0;'>
    <h4 class="text-center">
	<span class="label label-{$state|host_color}"><i class='fa fa-hdd-o'></i> {$title}</span>
    </h4>
    <table class="table table-striped table-condensed">
	{foreach from=$data item=d}
		<tr>
			<td>{$d.label}</td>
			<td>{$d.value}</td>
		</tr>
	{/foreach}
    </table>
</div>
{/if}
{hook name="displayHostTooltipDetail" container="" params=$params}
