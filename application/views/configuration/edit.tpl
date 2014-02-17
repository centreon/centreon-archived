{extends file="../viewLayout.tpl"}

{block name="title"}Command{/block}

{block name="content"}
    <div class="container">
        {$form}
    </div>
{/block}

{block name="javascript-bottom" append}
    <script>
        $("#{$formName}").submit(function (event) {
            $.ajax({
                url: "{url_for url=$validateUrl}",
                type: "POST",
                data: $(this).serialize(),
                context: document.body
            })
            .success(function(data, status, jqxhr) {
                alertClose();
                if (data === "success") {
                    {if isset($formRedirect) && $formRedirect}
                        window.location='{url_for url=$formRedirectRoute}';
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

    </script>
{/block}