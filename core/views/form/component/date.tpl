<input id="{$element['id']}" type="text" name="{$element['name']}" value="{$element['html']}" class="date
{if ((isset($element['label_mandatory'])) and ($element['label_mandatory'] == 1))}
 mandatory-field
{/if}
"
 placeholder="{$element['placeholder']}"
{if ((isset($element['label_mandatory'])) and ($element['label_mandatory'] == 1))}
 required
{/if}
{if $element['label_parent_field'] != '' and $element['label_parent_value'] != ''}
  data-parentfield="{$element['label_parent_field']}"
  data-parentvalue="{$element['label_parent_value']}"
{/if}
{if isset($element['label_readonly']) and  $element['label_readonly'] == '1'}
  readonly
{/if}
/><cite></cite>
