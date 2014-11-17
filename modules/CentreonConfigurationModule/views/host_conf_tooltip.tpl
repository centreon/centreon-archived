{if isset($error)}
	{$error}
{else}
	<div>
		<div class="pull-left">
		<h3 class="text-center">
			<span class="label label-default">{t}Check parameters{/t}</span>
		</h3>
		<table class="table table-striped table-condensed">
		{foreach from=$checkdata item=d key=k}
			<tr>
				<td>{$d.label}</td>
				<td>{$d.value}</td>
			</tr>
		{/foreach}
		</table>
		</div>
	</div>
{/if}
