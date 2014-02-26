{extends file="../viewLayout.tpl"}

{block name="title"}{$pageTitle}{/block}

{block name="content"}
    <div class="container">
        {$form}
    </div>
{/block}

{block name="javascript-bottom" append}
    <script>
        function getSaveData(mySaveData)
        {
            {foreach $customValuesGetter as $customValueName => $customValueGetter}
                mySaveData += '&' + '{$customValueName}' + '=' + {$customValueGetter};
            {/foreach}
            return mySaveData;
        }
        
        $("#{$formName}").submit(function (event) {
            $.ajax({
                url: "{url_for url=$validateUrl}",
                type: "POST",
                data: getSaveData($(this).serialize()),
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
