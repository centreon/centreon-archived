{extends file="../viewLayout.tpl"}

{block name="title"}{$pageTitle}{/block}

{block name="content"}
    <div class="content-container">
        <div class="row">
            {if $advanced}
                <a href="{$formModeUrl}" class="btn">{t}Switch to simple mode{/t}</a>
            {else}
                <a href="{$formModeUrl}" class="btn">{t}Switch to advanced mode{/t}</a>
            {/if}
        </div>
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
        $("#{$formName}").on("submit", function (event) {
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
                        alertMessage("The object has been successfully saved", "alert-success");
                    {/if}
                } else {
                    alertMessage("An error occured", "alert-danger");
                }
            });
            return false;
        });
        
        $(function () {
            $('#formHeader a:first').tab('show');
        });
        
        $(".mandatory-field").on("blur", function (event) {
            if ($(this).val().trim() === "") {
                $(this).addClass("has-error has-feedback");
            } else {
                $(this).removeClass("has-error has-feedback");
            }
        });
    </script>
{/block}
