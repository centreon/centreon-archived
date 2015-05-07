{if isset($error)}
	{$error}
{else}
            <h4>{t}Host Details{/t}</h4>

            <dl>
            {foreach from=$checkdata item=d key=k}
                <dt>{$d.label}</dt>
                <dd>{$d.value}</dd>
            {/foreach}
            </dl>
{/if}
