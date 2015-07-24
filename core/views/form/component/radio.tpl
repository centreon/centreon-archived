{if (isset($inputElement['label_general_label'])) } 
    <label class="label-controller floatLabel" for="{$name}">{$inputElement['label_general_label']}</label>
{/if}
{if (isset($inputElement['label_values'])) } 
    <div class="choiceGroup">
        <label class="label-controller" for="{$inputElement['label']}">
        {assign var=i value=1}

        {foreach $inputElement['label_values'] as $key => $choice }
            <label class="label-controller radio-styled" for="{$choice}">
            <input id="{$inputElement['id']}{$i}" type="{$inputElement['label_type']}" name="{$inputElement['name']}" value="{$choice}"
            {if (!empty($inputElement['label_parent_field']) && !empty($inputElement['label_parent_value']))}
                 data-parentfield="{$inputElement['label_parent_field']}"
                 data-parentvalue="{$inputElement['label_parent_value']}"
            {/if}
             /><span></span>{$key}</label>
            {assign var=i value=$i+1}
        {/foreach}
    </div>
{/if}
