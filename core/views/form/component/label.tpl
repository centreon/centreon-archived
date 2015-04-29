<label class="label-controller floatLabel
{if ((isset($inputElement['label_mandatory'])) and ($inputElement['label_mandatory'] == 1))}
 required
{/if}
" for="{$inputElement['id']}">
{$inputElement['label']}
</label>
{if ((isset($inputElement['label_mandatory'])) and ($inputElement['label_mandatory'] == 1))}
<span>*</span>
{/if}
