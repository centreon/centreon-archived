<input id="{$element['id']}" type="password" name="{$element['name']}"
 {if isset($element['value'])}
 value="{$element['value']}"
 {/if}
 class="form-control input-sm
 {if ((isset($element['label_mandatory'])) and ($element['label_mandatory'] == 1))}
 mandatory-field
 {/if}
" placeholder="{$element['placeholder']}"
{if ((isset($element['label_mandatory'])) and ($element['label_mandatory'] == 1))}
 required
{/if}
/>
