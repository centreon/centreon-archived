 <div id="_controls">
    <div id="_add" class="clone-trigger">
        <a id="_add_link" class="addclone" style="padding-right:5px;cursor:pointer;" >

            <i data-action="add" class="icon-plus"></i>Add Ldap Server
        </a>
    </div>
</div>           
            
            
<div class="scrollable">
    <ul class="clonable no-deco-list" >

        <li class="clone_template" style="display:none;">
            <div class="row clone-cell">

                <div class="clonehandle col-md-1"><i class="icon-move"></i></div>


                <div class="form-group col-md-4">
                    <label class="label-controller floatLabel">Host adresse</label>
                    <input class="form-control" name="auth_server[#index#][server_address]" type="text" />
                </div>



                <div class="form-group col-md-4">
                    <label class="label-controller floatLabel">Port</label>
                    <input class="hidden-value form-control" name="auth_server[#index#][server_port]" type="text" />
                </div>


                <div class="remove-trigger col-md-1" style="cursor:pointer;"><i class="icon-delete"></i></div>

                <div class="form-group col-md-1">
                    <input class="hidden-value-trigger" type="checkbox" name="auth_server[#index#][use_ssl]" />
                    <label class="label-controller">SSL</label>
                </div>


                <div class="form-group col-md-1">
                    <input class="hidden-value-trigger" type="checkbox" name="auth_server[#index#][use_tls]" />
                    <label class="label-controller">TLS</label>
                </div>   
            </div>
            <input type="hidden" name="clone_order_{$element['name']}_#index#" id="clone_order_#index#" />
        </li>


        {assign var=i value=0}
        {foreach from=$authServers item=authServer}
        <li class="cloned_element">
            <div class="row clone-cell">

                <div class="clonehandle col-md-1"><i class="icon-move"></i></div>

                <div class="form-group col-md-4">
                    <label class="label-controller floatLabel">Host adresse</label>
                    <input class="form-control" name="auth_server[{$i}][server_address]" type="text" value="{$authServer['server_address']}"/>
                </div>

                <div class="form-group col-md-4">
                    <label class="label-controller floatLabel">Port</label>
                    <input class="hidden-value form-control" name="auth_server[{$i}][server_port]" type="text" value="{$authServer['server_port']}"/>
                </div>

                <div class="remove-trigger col-md-1" style="cursor:pointer;"><i class="icon-delete"></i></div>

                <div class="form-group col-md-1">

                    <input class="hidden-value-trigger" type="checkbox" name="auth_server[{$i}][use_ssl]"
                    {if (isset($authServer['use_ssl']) and ($authServer['use_ssl'] > 0))}
                    checked=checked
                    {/if}
                     />
                     <label class="label-controller">SSL</label>
                </div>

                <div class="form-group col-md-1">

                    <input class="hidden-value-trigger" type="checkbox" name="auth_server[{$i}][use_tls]"
                    {if (isset($authServer['use_tls']) and ($authServer['use_tls'] > 0))}
                    checked=checked
                    {/if}
                     />
                     <label class="label-controller">TLS</label>
                </div>     



            </div>
            <input type="hidden" name="clone_order_{$element['name']}_{$i}" id="clone_order_{$i}" />

        </li>
    {assign var=i value=$i+1}
    {/foreach}






    </ul>
    
    
    </div>