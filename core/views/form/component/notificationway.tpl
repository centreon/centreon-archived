<div id="notification_way_controls">
    <div id="notification_way_add" class="clone-trigger">
        <a id="add_notification_way" class="addclone" style="padding-right:5px;cursor:pointer;" >
            Add notification way <i data-action="add" class="fa fa-plus-square"></i>
        </a>
    </div>
</div>

<ul id="notification_way" class="clonable no-deco-list">
    <li id="notification_way_clone_template" class="clone_template" style="display:none;">
        <div class="row clone-cell">
            <div class="col-sm-4">
                <select class="form-control" name="way_name[#index#]">
                    <option selected></option>
                    {foreach from=$notificationWays item=notificationWay}
                    <option>{$notificationWay}</option>
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-1">
                <label class="label-controller">Value</label>
            </div>
            <div class="col-sm-4">
                <input class="form-control" name="way_value[#index#]" />
            </div>
            <div class="col-sm-1">
                <span class="remove-trigger" style="cursor:pointer;">
                    <i class="fa fa-times-circle"></i>
                </span>
            </div>
        </div>
        <input type="hidden" name="clone_order_notification_way_#index#" id="clone_order_#index#" />
    </li>

    {assign var=i value=0}
    {foreach from=$currentNotificationWays item=wayValue}
    <li id="notification_way_clone_template" class="cloned_element" style="display:block;">
        <div class="row clone-cell">
            <div class="col-sm-4">
                <select class="form-control" name="way_name[{$i}]">
                    {foreach from=$notificationWays item=notificationWay}
                      {if $notificationWay == $wayValue['info_key']}
                      <option selected>{$notificationWay}</option>
                      {else}
                      <option>{$notificationWay}</option>
                      {/if}
                    {/foreach}
                </select>
            </div>
            <div class="col-sm-1">
                <label class="label-controller">Value</label>
            </div>
            <div class="col-sm-4">
                <input class="form-control" name="way_value[{$i}]" value="{$wayValue['info_value']}"/>
            </div>
            <div class="col-sm-1">
                <span class="remove-trigger" style="cursor:pointer;">
                    <i class="fa fa-times-circle"></i>
                </span>
            </div>
        </div>
        <input type="hidden" name="clone_order_notification_way_{$i}" id="clone_order_{$i}" />
    </li>
    {assign var=i value=$i+1}
    {/foreach}
</ul>

<input id="cloned_element_index" name="cloned_element_index" type="hidden" value="{$i}" />
