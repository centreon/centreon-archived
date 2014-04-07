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
