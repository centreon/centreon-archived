<div class="form-group formWrapper">
    <div class="centreon-search-block CentreonForm" id="accordion">
        <div class="panel-heading">
            {$clsOffset=""}
            {$searchAdv=true}
            {if true }
            <div class="col-md-8 form-group">
                <div class="inlineGroup">
                    <div class="Elem1">
                        <input type="text" name="advsearch">
                    </div>
                    <div class="Elem2">
                        <button class="btnC btnDefault" type="button" id="btnSearch"><i class="icon-search"></i></button>
                    </div>
                </div>
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
                <div class="{$clsOffset}col-md-4 form-group">
                    <div class="row">
                      <div class="col-xs-11">
                        <div class=" input-group">
                            <input type="text" name="filters" class="form-control">
                            <span class="input-group-btn">
                                <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="loadView" data-original-title="Load"><i class="fa fa-upload"></i></button>
                                <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="saveView" data-original-title="Save"><i class="fa fa-floppy-o"></i></button>
                                <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="deleteView" data-original-title="Delete"><i class="fa fa-trash-o"></i></button>
                                <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="bookmarkView" data-original-title="Bookmark"><i id="bookmarkStatus" class="fa fa-star-o"></i></button>
                            </span>
                        </div>
                      </div>
                      <div class="col-xs-1">
                          <a data-toggle="collapse" class="search-expand" data-parent="#accordion" href="#collapseOne"><i class="fa fa-plus-square-o"></i></a>
                      </div>
                    </div>
                </div>
        </div>
        <div id="collapseOne" class="panel-collapse collapse">
            <div class="panel-body search-body">
                <div class="row">
                    {foreach $datatableParameters.header.columnSearch as $colName=>$colSearch}
                    {if (!$searchAdv && $colSearch.main != "true") || $searchAdv }
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
</div>
