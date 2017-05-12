<form {$form.attributes}>
  <table class="formTable table">
	<tr class="ListHeader">
		<td class="FormHeader" colspan="2">
			<h3>| {$headerMenu_title2}</h3>
		</td>
	</tr>
	<tr class="list_one">
		<td class="FormRowField">{$headerMenu_rname}</td>
		<td class="FormRowValue">{$module.upgrade_rname}</td>
	</tr>
	<tr class="list_two">
		<td class="FormRowField">{$headerMenu_release_from}</td>
		<td class="FormRowValue">{$module.upgrade_release_from}</td>
	</tr>
	<tr class="list_one">
		<td class="FormRowField">{$headerMenu_release_to}</td>
		<td class="FormRowValue">{$module.upgrade_release_to}</td>
	</tr>
	<tr class="list_two">
		<td class="FormRowField">{$headerMenu_author}</td>
		<td class="FormRowValue">{$module.upgrade_author}</td>
	</tr>
	<tr class="list_one">
		<td class="FormRowField">{$headerMenu_infos}</td>
		<td class="FormRowValue">{$module.upgrade_infos}</td>
	</tr>
	{if $module.upgrade_infosTxt}
	<tr class="list_one">
		<td class="FormRowField" colspan="2">
			{$module.upgrade_infosTxt}
		</td>
	</tr>
	{/if}
	<tr class="list_one">
        {if $module.upgrade_available}
		<td colspan="2" align="center">{$form.upgrade.html}&nbsp{$form.list.html}</td>
		{else}
		<td colspan="2" align="center">{$form.list.html}</td>
        {/if}
	</tr>
 </table>
 {$form.hidden}
</form>
<br />