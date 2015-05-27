<div id="{$element['name']}_controls">
    <div id="{$element['name']}_add" class="clone-trigger">
        <a id="{$element['name']}_add_link" class="addclone" style="padding-right:5px;cursor:pointer;" >
            Add Ldap Server <i data-action="add" class="fa fa-plus-square"></i>
        </a>
    </div>
</div>

        <ul id="{$element['name']}" class="clonable no-deco-list">
        
        <li id="{$element['name']}_clone_template" class="clone_template" style="display:none;">
            <div class="row clone-cell">
                <div class="col-sm-1">
                    <label class="label-controller">Host adresse</label>
                </div>
                <div class="col-sm-2">
                    <input class="form-control" name="auth_server[host_adresse][]" />
                </div>
                <div class="col-sm-1">
                    <label class="label-controller">Port</label>
                </div>
                <div class="col-sm-2">
                    <input class="hidden-value form-control" name="auth_server[port][]" />
                </div>
                <div class="col-sm-1">
                    <label class="label-controller">SSL</label>
                </div>
                <div class="col-sm-1">
                    <input class="hidden-value-trigger" type="checkbox" name="auth_server[ssl][]" />
                </div>
                <div class="col-sm-1">
                    <input class="hidden-value-trigger" type="checkbox" name="auth_server[tls][]" />
                </div>
                <div class="col-sm-2">
                    <span class="clonehandle" style="cursor:move;"><i class="fa fa-arrows"></i></span>
                    &nbsp;
                    <span class="remove-trigger" style="cursor:pointer;"><i class="fa fa-times-circle"></i></span>
                </div>
            </div>
            <input type="hidden" name="clone_order_{$element['name']}_#index#" id="clone_order_#index#" />
        </li>

        
        </ul>