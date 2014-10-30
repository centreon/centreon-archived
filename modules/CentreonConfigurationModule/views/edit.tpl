{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{$pageTitle}{/block}

{block name="content"}
    <div class="content-container">
        <a id="advanced_mode_switcher" href="#" class="btn btn-primary">
            <i class="fa fa-gears"></i> {t}Switch to Advanced mode{/t}
        </a>
        {$form}
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
                $(this).html('<i class="fa fa-gear"></i> {t}Switch to Simple mode{/t}');
            } else {
                $(this).html('<i class="fa fa-gears"></i> {t}Switch to Advanced mode{/t}');
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
