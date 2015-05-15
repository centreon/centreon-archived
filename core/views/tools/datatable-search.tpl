
<div class="SearchWrapper CentreonForm">
    <div class="form-group">
        <div class="input-group">
            <input type="text" name="filters" class="form-control" placeholder="View's name">
            <!--<cite>Rename your view or select an existant one </cite>-->
            <span class="input-group-btn">
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="" id="loadView" data-original-title="Load"><i class="icon-upload"></i></button>
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="" id="saveView" data-original-title="Save"><i class="icon-save"></i></button>
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="" id="deleteView" data-original-title="Delete"><i class="fa icon-delete"></i></button>
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="" id="bookmarkView" data-original-title="Bookmark"><i id="bookmarkStatus" class="icon-favoris"></i></button>
            </span>
        </div>
    </div>
    <div class="centreon-search-block form-group" id="accordion">

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

                </div>

        <div id="collapseOne" class="panel-collapse collapse">

          <div class="CentreonFrom">
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
</div>
