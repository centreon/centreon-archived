<label class="label-controller" for="{$element['id']}">
    &nbsp;
    <input id="{$element['id']}" type="checkbox" name="{$element['name']}" value=1
    {if (isset($element['html']) && $element['html'] == 1)}
    checked=checked
    {/if}
    />
</label>
&nbsp;&nbsp;
