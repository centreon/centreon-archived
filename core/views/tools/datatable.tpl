<div class="col-lg-12">


      {if (!isset($displaySearchBar) || $displaySearchBar === true)}
        {include file='tools/datatable-search.tpl'}
      {/if}

   <!-- Datatable-->


   <div class="Listing">

        <div id="tableLeft">
        <div class="centreon-search-block form-group CentreonForm" id="accordion">

                    {$clsOffset=""}
                    {$searchAdv=true}
                    {if true }
                            <div class="inlineGroup">
                            <div class="Elem1"><input type="text" name="advsearch" class="form-control"></div>
                            <div class="Elem2"><button class="btnC btnDefault" type="button" id="btnSearch"><i class="icon-search"></i></button></div>
                            </div>

                    {else}
                    {$searchAdv=false}
                    {$nbMain=0}
                    {foreach $datatableParameters.header.columnSearch as $colName=>$colSearch}
                        {if $colSearch.main == "true" && $nbMain < 2}
                            {$nbMain=$nbMain+1}
                            <div class="col-md-4">
                                <div class="input-group">
                                    <span class="input-group-addon">{$colSearch.title}</span>
                                    {if $colSearch['type'] == 'select'}
                                        <select class="centreon-search form-control" data-column-index="{$colSearch.colIndex}" placeholder="{$colSearch.title}" name="{$colName}" data-searchtag="{$colSearch.searchLabel}">
                                            <option value=""></option>
                                            {foreach $colSearch.additionnalParams as $optionName=>$optionValue}
                                                <option value="{$optionValue}">{$optionName}</option>
                                            {/foreach}
                                        </select>
                                    {else}
                                        <input class="centreon-search form-control" data-column-index="{$colSearch.colIndex}" name="{$colName}" placeholder="{$colSearch.title}" type="text" data-searchtag="{$colSearch.searchLabel}" />
                                    {/if}
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                            {if $nbMain == 0}
                                {$clsOffset="col-md-offset-8 "}
                            {elseif $nbMain == 1}
                                {$clsOffset="col-md-offset-4 "}
                            {/if}
                        {/if}

                             <a data-toggle="collapse" class="search-expand" data-parent="#accordion" href="#collapseOne"><b>+</b></a>



                <div id="collapseOne" class="panel-collapse collapse">

                  <div class="CentreonForm">
                    <div class="panel-body search-body">
                            {foreach $datatableParameters.header.columnSearch as $colName=>$colSearch}
                            {if (!$searchAdv && $colSearch.main != "true") || $searchAdv }

                        <div class="col-md-6">
                            <div class="form-group">
                                    <label class="floatLabel">{$colSearch.title}</label>
                                    {if $colSearch['type'] == 'select'}
                                        <select class="centreon-search form-control" data-column-index="{$colSearch.colIndex}" placeholder="{$colSearch.title}" name="{$colName}" data-searchtag="{$colSearch.searchLabel}">
                                            <option value=""></option>
                                            {foreach $colSearch.additionnalParams as $optionName=>$optionValue}
                                                <option value="{$optionValue}">{$optionName}</option>
                                            {/foreach}
                                        </select>
                                    {else}

                                        <input class="centreon-search form-control" data-column-index="{$colSearch.colIndex}" name="{$colName}" placeholder="{$colSearch.title}" type="text" data-searchtag="{$colSearch.searchLabel}"/>
                                    {/if}
                            </div>
                        </div>
                            {/if}
                            {/foreach}
                    </div>
                </div>
            </div>
            </div>

            <!-- Add / Actions -->
            <div class="buttonGroup btDatatable">
                {if (isset($objectAddUrl) && !empty($objectAddUrl))}
                  <div class="configuration-actions">
                    <button class="btnC btnSuccess" id="modalAdd">{t}Add {$objectDisplayName}{/t}</button>
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

