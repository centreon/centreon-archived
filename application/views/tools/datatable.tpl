
<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" width="100%" id="datatable{$object}">

    <thead>
        <tr>
            {$counter = 0}
            {$counterCol = 0}
            {foreach $datatableParameters.header as $header}
                {foreach $header as $headerType=>$headerData}
                    {if $headerType === 'select'}
                        <th>
                            <select class="search_type c{$counterCol}" id="select_{$counter}" name="select_{$counter++}">
                                {foreach $headerData as $optName=>$optValue}
                                    <option value="{$optValue}">{$optName}</option>
                                {/foreach}
                            </select>
                        </th>
                    {elseif $headerType === 'none'}
                        <th>&nbsp;</th>
                    {else}
                        <th>
                            <input type="text" id="search_{$counter}" name="search_{$counter++}" placeholder="Identifiant" class="search_field c{$counterCol}" size='10' />
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
                    {$footerType}
                    {if $footerType === 'select'}
                        <th>
                            <select class="search_type {$counterCol++} c{$counterCol}" id="select_{$counter}" name="select_{$counter++}">
                                {foreach $footerData as $optName=>$optValue}
                                    <option value="{$optValue}">{$optName}</option>
                                {/foreach}
                            </select>
                        </th>
                    {elseif $footerData === 'none'}
                        <th>&nbsp;</th>
                    {else}
                        <th>
                            <input type="text" id="search_{$counter}" name="search_{$counter++}" placeholder="Identifiant" class="search_field c{$counterCol}" size='10' />
                        </th>
                    {/if}
                {/foreach}
            {/foreach}
        </tr>
    </tfoot>

</table>
