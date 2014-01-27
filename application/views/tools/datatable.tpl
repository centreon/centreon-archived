<table cellpadding="0" cellspacing="0" border="0" class="table table-striped table-bordered" width="100%" id="dataTable{$object}">

    <thead>
        <tr>
            {foreach $datatableParameters.header as $headerType=>$header}
                {if $headerType === 'select'}
                    <th>
                        <select class="search_type" id="search_type" name="search_type">
                            {foreach $header as $optName=>$optValue}
                                <option value="{$optValue}">{$optName}</option>
                            {/foreach}
                        </select>
                    </th>
                {else}
                    <th>
                        <input 
                            type="text" 
                            name="search_name" 
                            placeholder="Identifiant" 
                            class="search_init" 
                            size='10' 
                            />
                    </th>
                {/if}
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
            {foreach $datatableParameters.footer as $footerType=>$footer}
                {if $footerType === 'select'}
                    <th>
                        <select class="search_type" id="search_type" name="search_type">
                            {foreach $footer as $optName=>$optValue}
                                <option value="{$optValue}">{$optName}</option>
                            {/foreach}
                        </select>
                    </th>
                {else}
                    <th>
                        <input 
                            type="text" 
                            name="search_name" 
                            placeholder="Identifiant" 
                            class="search_init" 
                            size='10' 
                            />
                    </th>
                {/if}
            {/foreach}
        </tr>
    </tfoot>

</table>