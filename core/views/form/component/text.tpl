<input id="{$element['id']}" type="text" name="{$element['name']}" value="{$element['html']}" class="
{if ((isset($element['label_mandatory'])) and ($element['label_mandatory'] == 1))}
 mandatory-field
{/if}
"
 placeholder="{$element['placeholder']}"
{if ((isset($element['label_mandatory'])) and ($element['label_mandatory'] == 1))}
 required
{/if}
/><cite></cite>
