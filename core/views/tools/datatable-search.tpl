<div class="panel-group" id="accordion" style="width:85%">
    <div class="panel panel-default">
        <div class="panel-heading">
            <h4 class="panel-title">
                <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne">Search bar</a>
            </h4>
        </div>
        <div id="collapseOne" class="panel-collapse collapse">
            <div class="panel-body">
                {foreach $datatableParameters.header.columnSearch as $colName=>$colSearch}
                    <div class="input-group">
                        <span class="input-group-addon">{$colSearch.title}</span>
                        {if $colSearch['type'] == 'select'}
                            <select class="centreon-search form-control" data-column-index="{$colSearch.colIndex}" placeholder="{$colSearch.title}" name="{$colName}">
                                <option value=""></option>
                                {foreach $colSearch.additionnalParams as $optionName=>$optionValue}
                                    <option value="{$optionValue}">{$optionName}</option>
                                {/foreach}
                            </select>
                        {else}
                            <input class="centreon-search form-control" data-column-index="{$colSearch.colIndex}" name="{$colName}" placeholder="{$colSearch.title}" type="text" />
                        {/if}
                    </div>
                {/foreach}
            </div>
        </div>
    </div>
</div>