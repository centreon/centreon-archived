{if isset($error)}
	{$error}
{else}
            <h4>{t}Tags{/t}</h4>

            <dl>
            {foreach from=$tags item=d key=k}
                <dt>{$d.text}</dt>
            {/foreach}
            </dl>
{/if}