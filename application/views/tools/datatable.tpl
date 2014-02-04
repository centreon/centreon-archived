<div class="first-content">
    <div class="col-sm-12 col-md-6">
      <div class="btn-group">
        <a href="" class="btn btn-default">{t}Add{/t}</a>
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
      </div>
      <div class="btn-group" id="selected_option">
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
<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" width="100%" id="datatable{$object}" >
    <thead>
        <tr>
            {$counter = 0}
            {$counterCol = 0}
            {foreach $datatableParameters.header as $header}
                {foreach $header as $headerType=>$headerData}
                    {if $headerType === 'select'}
                        <th>
                            <select class="form-control search_type c{$counterCol}" id="select_{$counter}" name="select_{$counter++}">
                                {foreach $headerData as $optName=>$optValue}
                                    <option value="{$optValue}">{$optName}</option>
                                {/foreach}
                            </select>
                        </th>
                    {elseif $headerData === 'none'}
                        <th>&nbsp;</th>
                    {else}
                        <th>
                            <input type="text" id="search_{$counter}" name="search_{$counter++}" placeholder="Identifiant" class="form-control search_field c{$counterCol}" size='10' />
                        </th>
                    {/if}
                {/foreach}
            {/foreach}
        </tr>
        <tr>
            {foreach $datatableParameters.column as $columnLabel => $columnName}
                <th>{$columnLabel}</th>
            {/foreach}
        </tr>
    </thead>

    <tbody>
    </tbody>

    <tfoot>
        <tr>
            {$counterCol = 0}
            {foreach $datatableParameters.footer as $footer}
                {foreach $footer as $footerType=>$footerData}
                    {if $footerType === 'select'}
                        <th>
                            <select class="form-control search_type {$counterCol++} c{$counterCol}" id="select_{$counter}" name="select_{$counter++}">
                                {foreach $footerData as $optName=>$optValue}
                                    <option value="{$optValue}">{$optName}</option>
                                {/foreach}
                            </select>
                        </th>
                    {elseif $footerData === 'none'}
                        <th>&nbsp;</th>
                    {else}
                        <th>
                            <input type="text" id="search_{$counter}" name="search_{$counter++}" placeholder="Identifiant" class="form-control search_field c{$counterCol}" size='10' />
                        </th>
                    {/if}
                {/foreach}
            {/foreach}
        </tr>
    </tfoot>

</table>
