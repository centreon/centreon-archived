<div class="first-content">
    <div class="col-sm-12 col-md-6">
      <a href="{url_for url=$objectAddUrl}" class="btn btn-default">{t}Add{/t}</a>
      <div class="btn-group" id="selected_option">
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Actions{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#">{t}Delete{/t}</a></li>
            <li><a href="#">{t}Massive change{/t}</a></li>
          </ul>
        </div>
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Add to{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#">{t}Hostgroup{/t}</a></li>
            <li><a href="#">{t}Category{/t}</a></li>
          </ul>
        </div>
      </div>
    </div>
</div>
<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered table-hover" width="100%" id="datatable{$object}" >
    <thead>
        <tr class='header'>
        {foreach $datatableParameters.column.firstLevel as $column}
            <th {$column.att} style="text-align:center;">{$column.lab}</th>
        {/foreach}
        </tr>
        
        {if isset($datatableParameters.column.secondLevel)}
            <tr class='header'>
            {foreach $datatableParameters.column.secondLevel as $column}
                <th style="text-align:center;">{$column.lab}</th>
            {/foreach}
            </tr>
        {/if}

        <tr>
        {foreach $datatableParameters.column.search as $column}
            <th rowspan="1" colspan="1" style="text-align:center;" class='header-search'>{$column.lab}</th>
        {/foreach}
        </tr>
    </thead>
    <tbody>
    </tbody>
</table>
