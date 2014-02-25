<div class="first-content">
    <div class="col-sm-12 col-md-6">
      <button class="btn btn-default" id="modalAdd">{t}Add{/t}</button>
      <div class="btn-group" id="selected_option" style="display: none;">
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Actions{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            <li><a href="#" id="modalDelete">{t}Delete{/t}</a></li>
            <li><a href="#">{t}Massive change{/t}</a></li>
          </ul>
        </div>
        {if $datatableParameters.groupname || $datatableParameters.hasCategory}
        <div class="btn-group">
          <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
            {t}Add to{/t}
            <span class="caret"></span>
          </button>
          <ul class="dropdown-menu">
            {if $datatableParameters.groupname }<li><a href="#">{$datatableParameters.groupname}</a></li>{/if}
            {if $datatableParameters.hasCategory}<li><a href="#">{t}Category{/t}</a></li>{/if}
          </ul>
        </div>
        {/if}
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

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="wizard" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
    </div>
  </div>
</div>
