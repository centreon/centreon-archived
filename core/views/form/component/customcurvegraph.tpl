<div id="{$element['name']}_controls">
    <div id="{$element['name']}_add" class="clone-trigger">
        <a id="{$element['name']}_add_link" class="addclone" style="padding-right:5px;cursor:pointer;" ><i data-action="add" class="icon-plus"></i> Add metric
        </a>
    </div>
    <hr>
</div>

        
<div class="scrollable">        
    <ul class="clonable no-deco-list" >
        <li class="clone_template" style="display:none;">
            <div class="row clone-cell">

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="label-controller floatLabel">Name</label>
                        <input class="form-control" type="text" name="metric_name[#index#]" />
                    </div>
                </div>

               <div class="col-md-2">
                    <div class="form-group">
                        <input class="color-picker" name="metric_color[#index#]" />
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="checkbox checkbox-styled">
                        <label>
                            <input type="checkbox" name="metric_fill[#index#]" />
                            <span>Fill</span>
                        </label>
                      </div>
                </div>

                <div class="col-md-2">
                    <div class="checkbox checkbox-styled">
                        <label>
                            <input type="checkbox" name="metric_negative[#index#]" />
                            <span>Negative</span>
                        </label>
                      </div>
                </div>

                <div class="col-md-1">
                    <span class="remove-trigger" style="cursor:pointer;"><i class="icon-delete ico-18"></i></span>
                </div>
            </div>

            <input type="hidden" name="clone_order_{$element['name']}_#index#" id="clone_order_#index#" />
        </li>

        {assign var=i value=0}
        {foreach from=$currentMetrics item=metric}
        <li id="{$element['name']}_clone_template" class="cloned_element">
            <div class="row clone-cell">

                <div class="col-md-4">
                    <div class="form-group">
                        <label class="label-controller floatLabel">Name</label>
                        <input class="form-control" name="metric_name[{$i}]" type="text" value="{$metric['metric_name']}"/>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <input class="color-picker" name="metric_color[{$i}]" value="{$metric['color']}" />
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <div class="checkbox checkbox-styled">
                            <label>
                                <input type="checkbox" name="metric_fill[{$i}]"
                                {if (isset($metric['fill']) and ($metric['fill'] > 0))}
                                checked=checked
                                {/if}
                                />
                                <span>Fill</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-2">
                    <div class="form-group">
                        <div class="checkbox checkbox-styled">
                            <label>
                                <input type="checkbox" name="metric_negative[{$i}]"
                                {if (isset($metric['is_negative']) and ($metric['is_negative'] > 0))}
                                checked=checked
                                {/if}
                                />
                                <span>Negative</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="col-md-1">
                    <span class="remove-trigger" style="cursor:pointer;"><i class="icon-delete ico-18"></i></span>
                </div>

            </div>

            <input type="hidden" name="clone_order_{$element['name']}_{$i}" id="clone_order_{$i}" />

        </li>
    {assign var=i value=$i+1}
    {/foreach}

    </ul>
</div>
<input id="cloned_element_index" name="cloned_element_index" type="hidden" value="0" />
