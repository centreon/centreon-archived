{extends file="file:[Core]baseLayout.tpl"}

{block name="title"}{$objectName}{/block}

{block name="content"}

{if !isset($displayActionBar)}
    {$displayActionBar=true}
{/if}

<div class="first-content"></div>

{if isset($objectAddUrl)}
    {datatable module=$moduleName object=$objectName datatableObject=$datatableObject displayActionBar=$displayActionBar objectAddUrl=$objectAddUrl}
{else}
    {datatable module=$moduleName object=$objectName datatableObject=$datatableObject displayActionBar=$displayActionBar}
{/if}

<div class="modal fade" role="dialog" id="modal-console">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" id="modal-console-content"></div>
    </div>
</div>

{/block}

{block name="javascript-bottom" append}
    {if isset($jsUrl)}
        <script>
            var jsUrl = {$jsUrl|json_encode};
        </script>
    {/if}
    {datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}
    <script>
        /* monitoring actions */
        $(function() {
            $( "#selected_option" ).find( "ul" ).append(
                {if isset($actions)}
                    '{foreach from=$actions item=value}
                        <li>{$value.group}</li> \
                        {foreach from=$value.actions key=val item=label}
                            <li><a href="#" id="modalAction{$val}">{$label}</a></li> \
                        {/foreach}
                    {/foreach}'
                {/if}
            );

            var consoleType = 0;
            var serviceObjectRegexp = /service/i;
            if (serviceObjectRegexp.test('{$objectName}')) {
                consoleType = 1;
            }

            $( "#selected_option" ).find('[id^="modalAction"]').on('click', function(e) {
                var selectedItems = [];
                $("tr.selected").each(function(index) {
                    selectedItems[index] = $(this).data('id');
                });
                var regexpExternalCommand = /^modalAction(.*)/;
                var externalCommand = regexpExternalCommand.exec($(this).attr('id'));
                var externalCommandId = externalCommand[1];

                $.ajax({
                    type: 'POST',
                    url: 'externalcommands/' + externalCommandId + '/' + consoleType,
                    data: { ids: selectedItems }
                }).done(function(html) {
                    $("#modal-console-content").html(html);
                    $("#modal-console").modal({
                        show: true
                    });
                });
            });
        });
        </script>
{/block}
