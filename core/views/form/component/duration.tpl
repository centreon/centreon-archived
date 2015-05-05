<div class="input-group">
    <input id="{$element['id']}" type="number" name="{$element['name']}"
    {if isset($element['html'])}
    value={$element['html']}
    {/if}
    class="form-control input-sm 
    {if ((isset($inputElement['label_mandatory'])) and ($inputElement['label_mandatory'] == 1))}
    mandatory-field
    {/if}
    " placeholder="{$element['placeholder']}"
    {if ((isset($inputElement['label_mandatory'])) and ($inputElement['label_mandatory'] == 1))}
    required
    {/if}
    />
    <span class="input-group-btn">
        <button id="{$element['id']}_scale_selector" type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown">
            Seconds <span class="caret"></span>
        </button>
        <ul class="dropdown-menu" role="menu">
            <li class="{$element['name']}_scale_values"><a href="#">Seconds</a></li>
            <li class="{$element['name']}_scale_values"><a href="#">Minutes</a></li>
            <li class="{$element['name']}_scale_values"><a href="#">Hours</a></li>
            <li class="{$element['name']}_scale_values"><a href="#">Years</a></li>
        </ul>
        <input id="{$element['id']}_scale" type="hidden" name="{$element['name']}_scale" 
        {if isset($element['html'])}
        value={$element['html']}
        {/if}
        />
    </span>
</div>
