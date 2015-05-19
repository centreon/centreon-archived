<label class="label-controller floatLabel
{if ((isset($inputElement['label_mandatory'])) and ($inputElement['label_mandatory'] == 1))}
 required
{/if}
" for="{$inputElement['id']}">
    
{if ((isset($inputElement['label_show_label'])) and ($inputElement['label_show_label'] == 1))}    
    {$inputElement['label']}
{/if} 

</label>
{if ((isset($inputElement['label_mandatory'])) and ($inputElement['label_mandatory'] == 1))}
<span>*</span>
{/if}
