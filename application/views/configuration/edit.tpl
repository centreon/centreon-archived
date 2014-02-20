{extends file="../viewLayout.tpl"}

{block name="title"}{$pageTitle}{/block}

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

        
        
        {if isset($select2Call)}
            function formatResult(item)
            {
                if(!item.id) {
                  // return `text` for optgroup
                  return item.text;
                }

                if (item.theming) {
                    return item.theming;
                } else {
                    return item.text;
                }
            }

            function formatSelection(item)
            {
                return item.text;
            }
            
            {$select2Call}
        {/if}
    </script>
{/block}