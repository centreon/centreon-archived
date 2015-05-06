<div id="{$element['name']}_controls">
    <div id="{$element['name']}_add" class="clone-trigger">
        <a id="{$element['name']}_add_link" class="addclone" style="padding-right:5px;cursor:pointer;" >
            Add custom macro <i data-action="add" class="fa fa-plus-square"></i>
        </a>
    </div>
</div>

<ul id="{$element['name']}" class="clonable no-deco-list">
    <li id="{$element['name']}_noforms_template">
        <p class="muted">Nothing here, use the "Add" button</p>
    </li>
    <li id="{$element['name']}_clone_template" class="clone_template" style="display:none;">
        <div class="row clone-cell">
            <div class="col-sm-1">
                <label class="label-controller">Name</label>
            </div>
            <div class="col-sm-3">
                <input class="form-control" name="macro_name[#index#]" />
            </div>
            <div class="col-sm-1">
                <label class="label-controller">Value</label>
            </div>
            <div class="col-sm-3">
                <input class="hidden-value form-control" name="macro_value[#index#]" />
            </div>
            <div class="col-sm-1">
                <label class="label-controller">Hidden</label>
            </div>
            <div class="col-sm-1">
                <input class="hidden-value-trigger" type="checkbox" name="macro_hidden[#index#]" />
            </div>
            <div class="col-sm-2">
                <span class="clonehandle" style="cursor:move;"><i class="fa fa-arrows"></i></span>
                &nbsp;
                <span class="remove-trigger" style="cursor:pointer;"><i class="fa fa-times-circle"></i></span>
            </div>
        </div>
        <input type="hidden" name="clone_order_{$element['name']}_#index#" id="clone_order_#index#" />
    </li>

    {assign var=i value=0}
    {foreach from=$currentCustommacro item=macro}
    <li id="{$element['name']}_clone_template" class="cloned_element" style="display:block;">
        <div class="row clone-cell">
            <div class="col-sm-1">
                <label class="label-controller">Name</label>
            </div>
            <div class="col-sm-3">
                <input class="form-control" name="macro_name[{$i}]" value="{$macro['macro_name']}"/>
            </div>
            <div class="col-sm-1">
                <label class="label-controller">Value</label>
            </div>
            <div class="col-sm-3">
                <input class="hidden-value form-control" name="macro_value[{$i}]" value="{$macro['macro_value']}"/>
            </div>
            <div class="col-sm-1">
                <label class="label-controller">Hidden</label>
            </div>
            <div class="col-sm-1">
                <input class="hidden-value-trigger" type="checkbox" name="macro_hidden[{$i}]"
                {if (isset($macro['macro_hidden']) and ($macro['macro_hidden'] > 0))}
                checked=checked
                {/if}
                 />
            </div>
            <div class="col-sm-2">
                <span class="clonehandle" style="cursor:move;"><i class="fa fa-arrows"></i></span>
                &nbsp;
                <span class="remove-trigger" style="cursor:pointer;"><i class="fa fa-times-circle"></i></span>
            </div>
        </div>
        <input type="hidden" name="clone_order_{$element['name']}_{$i}" id="clone_order_{$i}" />
    </li>
    {assign var=i value=$i+1}
    {/foreach}
</ul>

<input id="cloned_element_index" name="cloned_element_index" type="hidden" value="0" />
