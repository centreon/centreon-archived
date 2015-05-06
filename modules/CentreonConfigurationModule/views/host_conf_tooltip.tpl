{if isset($error)}
	{$error}
{else}
		<div class="">
            <h3>
                {t}Host Details{/t}
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
{/if}
