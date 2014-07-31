<!DOCTYPE html>
<html>
    <head>
        <title>{t}Install{/t} - Centreon : IT Monitoring</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="icon" href="{url_static url='/centreon/img/favicon_centreon.ico'}" type="image/x-icon">
        {foreach from=$cssFileList item='cssFile'}
            {$cssFile|css}
        {/foreach}
    </head>
    
    <body>
        <div class="container">
            <div class="row">
                <div id="installBox" class="col-sm-12 login-box">
                    <div class="flash alert fade in" id="modal-flash-message" style="display: none;">
                        <button type="button" class="close" aria-hidden="true">&times;</button>
                    </div>
                    <div class="modal-header">
                        <h4>
                        {if isset($modalTitle)}
                            {$modalTitle}
                        {/if}
                        </h4>
                    </div>
                        
                    <div class="wizard" id="{$name}">
                        <ul class="steps">
                        {foreach $steps as $step}
                            <li data-target="#{$name}_{$step@index + 1}"{if $step@index == 0} class="active"{/if}><span class="badge badge-info">{$step@index + 1}</span>{$step@key}<span class="chevron"></span></li>
                        {/foreach}
                        </ul>
                    </div>
                        
                    <form role="form" class="form-horizontal" id="wizard_form">
                        <div class="step-content">
                        {foreach $steps as $step}
                            <div class="step-pane{if $step@index == 0} active{/if}" id="{$name}_{$step@index + 1}">
                                {$step['default']}
                            </div>
                        {/foreach}
                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default btn-prev" disabled>{t}Prev{/t}</button>
                            <button class="btn btn-default btn-next" data-last="{t}Finish{/t}" id="wizard_submit">{t}Next{/t}</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
                        
        {foreach from=$jsBottomFileList item='jsFile'}
        {$jsFile|js}
        {/foreach}
        
        <script>
            var modalListener;
            $('#installBox').centreonWizard();
            $(function() {
                $(document).unbind('finished');
                {if isset($validateUrl)}
                $(document).on('finished', function (event) {
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
                      data: $("#wizard_form").serializeArray(),
                      context: document.body
                    })
                    .success(function(data, status, jqxhr) {
                        alertModalClose();
                        if (data.success) {
                        {if isset($formRedirect) && $formRedirect}
                            window.location='{url_for url=$formRedirect}';
                        {else}
                            alertModalMessage("The object has been successfully saved", "alert-success");
                        {/if}
                            $('#modal').modal('hide');
                            if ($('.dataTable').length) {
                                $('.dataTable').dataTable().fnDraw();
                            }
                        } else {
                            alertModalMessage(data.error, "alert-danger");
                        }
                    });
                    return false;
                });
                {/if}
                {$customJs}
            });
        </script>
    </body>
</html>