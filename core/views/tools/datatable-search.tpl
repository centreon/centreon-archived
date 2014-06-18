<div class="panel-group" id="accordion">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                {if true }
                <div class="col-md-8 form-group">
                    <div class=" input-group">
                        <input type="text" name="advsearch" class="form-control">
                        <div class="input-group-btn">
                            <button class="btn btn-default" type="button"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <div class=" input-group">
                        <span class="input-group-btn">
                            <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="loadView" data-original-title="Load"><i class="fa fa-upload"></i></button>
                            <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="saveView" data-original-title="Save"><i class="fa fa-floppy-o"></i></button>
                            <button class="btn btn-default" data-toggle="tooltip" data-placement="bottom" title="" id="deleteView" data-original-title="Delete"><i class="fa fa-trash-o"></i></button>
                        </span>
                        <input type="text" name="filters" class="form-control">
                    </div>
                </div>
                {else}
                {/if}
                <div class="col-md-12">
                    <div class="pull-right">
                        <a data-toggle="collapse" data-parent="#accordion" href="#collapseOne"><i class="fa fa-plus-square-o"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div id="collapseOne" class="panel-collapse collapse">
            <div class="panel-body search-body">
                <div class="row">
                    {foreach $datatableParameters.header.columnSearch as $colName=>$colSearch}
                    <div class="col-md-4">
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
                    </div>
                    {/foreach}
                </div>
            </div>
        </div>
    </div>
</div>
