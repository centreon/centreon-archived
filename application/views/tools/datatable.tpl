<table cellpadding="0" 
       cellspacing="0" 
       border="0" 
       class="table table-striped table-bordered" 
       width="100%" 
       id="datatable{$object}"
       >

    <thead>
        <tr>
            {foreach $datatableParameters.header as $header}
                {foreach $header as $headerType=>$headerData}
                    {if $headerType === 'select'}
                        <th>
                            <select class="search_type" id="search_type" name="search_type">
                                {foreach $headerData as $optName=>$optValue}
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
            {foreach $datatableParameters.footer as $footer}
                {foreach $footer as $footerType=>$footerData}
                    {if $footerType === 'select'}
                        <th>
                            <select class="search_type" id="search_type" name="search_type">
                                {foreach $footerData as $optName=>$optValue}
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
            {/foreach}
        </tr>
    </tfoot>

</table>