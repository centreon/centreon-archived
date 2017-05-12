 <table class="formTable table">
	<tr class="ListHeader">
		<td class="FormHeader" colspan="2">
			<h3>| {$headerMenu_title}</h3>
		</td>
	</tr>
 	<tr class="list_one">
 		<td class="FormRowField">{$headerMenu_rname}</td>
 		<td class="FormRowValue">{$module_rname}</td>
 	</tr>
 	<tr class="list_two">
 		<td class="FormRowField">{$headerMenu_release}</td>
 		<td class="FormRowValue">{$module_release}</td>
 	</tr>
 	<tr class="list_one">
 		<td class="FormRowField">{$headerMenu_author}</td>
 		<td class="FormRowValue">{$module_author}</td>
 	</tr>
 	<tr class="list_two">
 		<td class="FormRowField">{$headerMenu_infos}</td>
 		<td class="FormRowValue">{$module_infos}</td>
 	</tr>
 	{if $module_infosTxt}
	 	<tr class="list_one">
	 		<td class="FormRowField" colspan="2">
	 			{$module_infosTxt}
	 		</td>
	 	</tr>
 	{/if}
 	{if $form1.install.html || $form1.list.html}
 	<tr class="list_one">
 		<td colspan="2" align="center">
 			{if $output}
 				{$output}<br />
 			{/if}
 			<form {$form1.attributes}>
	 		    <br />&nbsp;{$form1.install.html}&nbsp{$form1.list.html}<br />
	 		    {$form1.hidden}
	 		</form>
  		</td>
 	</tr>
 	{/if}
 	{if $form.list.html}
	 	<tr class="list_one">
	 		<td colspan="2" align="center">
	 			<form {$form.attributes}>
	 			<br />
	 			{$form.list.html}
	 			 </form>
	 		</td>
	 	</tr>
	 {/if}		 		 	
 </table>