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
{if $element['label_parent_field'] != '' and $element['label_parent_value'] != ''}
  data-parentfield="{$element['label_parent_field']}"
  data-parentvalue="{$element['label_parent_value']}"
{/if}
/>
