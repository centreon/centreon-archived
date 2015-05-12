<div class="col-lg-12">

  {if (!isset($displaySearchBar) || $displaySearchBar === true)}
    {include file='tools/datatable-search.tpl'}
  {/if}

   <!-- Datatable-->


   <div class="ibox float-e-margins col-lg-12" id="hostListing">
        <div class="ibox-content" id="tableLeft">
            <!-- Add / Actions -->
            <div class="buttonGroup">
                {if (isset($objectAddUrl) && !empty($objectAddUrl))}
                  <div class="configuration-actions">
                    <button class="btnC btnSuccess" id="modalAdd">{t}Add {$objectName}{/t}</button>
                  </div>
                {/if}

                {if (isset($displayActionBar) &&  $displayActionBar)}
                <div id="selected_option" style="display: none;">
                  <button type="button" class="btnC btnDefault dropdown-toggle" data-toggle="dropdown">
                    {t}Actions{/t}
                    <span class="caret"></span>
                  </button>
                  <ul name="action-bar" class="dropdown-menu">
                    {if (isset($isDisableable) && $isDisableable) }
                        <li><a href="#" id="modalEnable">{t}Enable{/t}</a></li>
                        <li><a href="#" id="modalDisable">{t}Disable{/t}</a></li>
                    {/if}
                    {if isset($objectAddUrl)}
                        <li><a href="#" id="modalDelete">{t}Delete{/t}</a></li>
                    {/if}
                    {if (isset($configuration) && ($configuration === true))}
                        <li><a href="#" id="modalDuplicate">{t}Duplicate{/t}</a></li>
                        <li><a href="#" id="modalMassiveChange">{t}Massive change{/t}</a></li>
                    {/if}
                  </ul>
                </div>
                {/if}

                 <div id="addToGroup" style="display: none;">
                      <button type="button" class="btnC btnDefault dropdown-toggle" data-toggle="dropdown">
                        {t}Add to{/t}
                        <span class="caret"></span>
                      </button>
                      <ul class="dropdown-menu">
                          {if isset($datatableParameters.groupname) }<li><a href="#">{$datatableParameters.groupname}</a></li>{/if}
                          {if isset($datatableParameters.hasCategory) }<li><a href="#">{t}Category{/t}</a></li>{/if}
                          {if !isset($datatableParameters.addToHook) }{$datatableParameters.addToHook = array()}{/if}
                          {hook name='displayAppendAddTo' container='[hook]' params=$datatableParameters.addToHook}
                        </ul>
                 </div>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover centreon_table" id="datatable{$object}" ></table>
            </div>
        </div>

   </div>

</div>
<aside id="sideRight" class="sideRightWrapper"></aside>



