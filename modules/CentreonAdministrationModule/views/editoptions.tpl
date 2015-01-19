{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{$pageTitle}{/block}

{block name="content"}
    <div class="content-container">
        <div class="row">
            <a id="advanced_mode_switcher" href="#" class="btn btn-primary">{t}Switch to advanced mode{/t}</a>
        </div>
        
        <div class="row">
        
        <div class="bs-callout bs-callout-success" id="formSuccess" style="display: none;">
            The object has been successfully updated'
        </div>
        <div class="bs-callout bs-callout-danger" id="formError" style="display: none;">
            An error occured
        </div>
        
        <form class="form-horizontal" role="form" {$form.attributes}>
            <div class="form-tabs-header">
                <div class="inline-block">
                    <ul class="nav nav-tabs" id="formHeader">

                    {foreach $formComponents as $sectionLabel => $sectionComponents}
                        <li>
                            <a href="#{$sectionLabel|replace:' ':''}" data-toggle="tab">
                                {$sectionLabel}
                            </a>
                        </li>
                    {/foreach}
                        <li>
                            <a href="#engineOptions" data-toggle="tab">{t}Engine Options{/t}</a>
                        </li>
                        
                        {if !isset($formComponents['Broker Options'])}
                        <li>
                            <a href="#brokerOptions" data-toggle="tab">{t}Broker Options{/t}</a>
                        </li>
                        {/if}
                    </ul>
                </div>
            </div>

            <div class="tab-content">
            {foreach $formComponents as $sectionLabel => $sectionComponents}
                <div class="tab-pane" id="{$sectionLabel|replace:' ':''}">
                {foreach $sectionComponents as $blockLabel => $blockComponents}
                    <h4 class="page-header" style="padding-top:0px;">{$blockLabel}</h4>
                    <div class="panel-body">
                    {foreach $blockComponents as $component}
                        {if (isset($form[$component['name']]['html']))}
                            {$form[$component['name']]['html']}
                        {/if}
                    {/foreach}
                    </div>
                {/foreach}
                {if isset($formComponents['Broker Options'])}
                    {hook name='displayBrokerOptions' params=$hookParams}
                {/if}
                </div>
            {/foreach}
                <div class="tab-pane" id="engineOptions">
                    {hook name='displayEngineOptions' params=$hookParams}
                </div>
                {if !isset($formComponents['Broker Options'])}
                <div class="tab-pane" id="brokerOptions">
                    {hook name='displayBrokerOptions' params=$hookParams}
                </div>
                {/if}
            </div>
            {$form.hidden}
            
            <div>{$form.save_form.html}</div>
        
            {$form.hidden}
        </form>
        
    </div>
        
    <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="wizard" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
            </div>
        </div>
    </div>
{/block}

{block name="javascript-bottom" append}
    <script>
        function hideEmptyBlocks()
        {
            $(".panel-body").each(function(i, v) {
                
                var $myFormGroupLength = $(v).children(".form-group").length;
                var $hidden = 0;

                $(v).children(".form-group").each(function(j, w) {
                    if ($(w).css("display") === "none") {
                        $hidden += 1;
                    }
                });
                
                if ($myFormGroupLength === $hidden) {
                    $(v).prev().css("display", "none");
                } else {
                    $(v).prev().css("display", "block");
                }
            });
        }
        
        $(document).ready(function(e) {
            hideEmptyBlocks();
        });
        
        $("#advanced_mode_switcher").on("click", function (event) {
            $(".advanced").toggleClass("advanced-display");
            if ($(".advanced").hasClass('advanced-display')) {
                $(this).text("{t}Switch to simple mode{/t}");
            } else {
                $(this).text("{t}Switch to advanced mode{/t}");
            }
            hideEmptyBlocks();
        });
        
        $("#{$formName}").on("submit", function (event) {
            
            var validateMandatory = true;
            var errorText = "";
            $("input.mandatory-field").each(function(index) {
                if ($(this).val().trim() === "") {
                    validateMandatory = false;
                    $(this).parent().addClass("has-error has-feedback");
                    errorText += $(this).attr("placeholder") + " is required<br/>";
                }
            });
            
            if (!validateMandatory) {
                alertMessage(errorText, "alert-danger");
                return false;
            }
            
            $.ajax({
                url: "{url_for url=$validateUrl}",
                type: "POST",
                dataType: 'json',
                data: $(this).serializeArray(),
                context: document.body
            })
            .success(function(data, status, jqxhr) {
                alertClose();
                if (data.success) {
                    {if isset($formRedirect) && $formRedirect}
                        window.location="{url_for url=$formRedirectRoute}";
                    {else}
                        alertMessage("{t}The object has been successfully saved{/t}", "alert-success", 3);
                    {/if}
                } else {
                    alertMessage(data.error, "alert-danger");
                }
            });
            return false;
        });
        
        $(function () {
            $('#formHeader a:first').tab('show');
            $("#formHeader").parent().after(
                $('<div class="pull-right inline-block"></div>').append($("#advanced_mode_switcher"))
            );
        });
    </script>
{/block}
