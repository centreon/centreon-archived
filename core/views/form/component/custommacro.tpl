<div id="{$element['name']}_controls">
    <div id="{$element['name']}_add" class="clone-trigger">
        <a id="{$element['name']}_add_link" class="addclone" style="padding-right:5px;cursor:pointer;" ><i data-action="add" class="icon-plus"></i> Add custom macro
        </a>
    </div>
    <hr>
</div>

        
<div class="scrollable">        
    <ul class="clonable no-deco-list" >
        <!--<li id="{$element['name']}_noforms_template">
            <p class="muted">Nothing here, use the "Add" button</p>
        </li>-->
        <li class="clone_template" style="display:none;">
            <div class="row clone-cell">

                <div class="col-md-1">
                     <span class="clonehandle" style="cursor:move;"><i class="icon-move ico-18"></i></span>
                </div>

                <div class="col-md-4">
                    <label class="label-controller floatLabel">Name</label>
                    <input class="form-control" type="text" name="macro_name[#index#]" />
                </div>

                <div class="col-md-4">
                    <label class="label-controller floatLabel">Value</label>
                    <input class="hidden-value form-control" type="text" name="macro_value[#index#]" />
                </div>
                <div class="col-md-1">
                <span class="remove-trigger" style="cursor:pointer;"><i class="icon-delete"></i></span>
                </div>
                <div class="col-md-2">
                    <input class="hidden-value-trigger" type="checkbox" name="macro_hidden[#index#]" />
                    <label class="label-controller">Hidden</label>
                </div>

            </div>
            <input type="hidden" name="clone_order_{$element['name']}_#index#" id="clone_order_#index#" />
        </li>

        {assign var=i value=0}
        {foreach from=$currentCustommacro item=macro}
        <li id="{$element['name']}_clone_template" class="cloned_element">
            <div class="row clone-cell">

                <div class="clonehandle col-md-1"><i class="icon-move  ico-18"></i></div>

                <div class="col-md-4">
                    <label class="label-controller floatLabel">Name</label>
                    <input class="form-control" name="macro_name[{$i}]" type="text" value="{$macro['macro_name']}"/>
                </div>

                <div class="col-md-4">
                    <label class="label-controller floatLabel">Value</label>
                    <input class="hidden-value form-control" name="macro_value[{$i}]" type="text" value="{$macro['macro_value']}"/>
                </div>

                <div class="remove-trigger col-md-1" style="cursor:pointer;"><i class="icon-delete ico-18"></i></div>

                <div class="col-md-2">

                    <input class="hidden-value-trigger" type="checkbox" name="macro_hidden[{$i}]"
                    {if (isset($macro['macro_hidden']) and ($macro['macro_hidden'] > 0))}
                    checked=checked
                    {/if}
                     />
                     <label class="label-controller">Hidden</label>

                     <!--<div class="checkbox checkbox-styled">
                        <label>
                            <input type="checkbox" value="">
                            <span>Default checkbox</span>
                        </label>
                      </div>-->

                </div>
            </div>
            <input type="hidden" name="clone_order_{$element['name']}_{$i}" id="clone_order_{$i}" />

        </li>
        {assign var=i value=$i+1}
        {/foreach}

    </ul>
</div>
<input id="cloned_element_index" name="cloned_element_index" type="hidden" value="0" />

<script>
           //console.log({$i});

 </script>