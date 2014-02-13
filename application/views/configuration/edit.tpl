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
                if (data === "success") {
                    $("#formSuccess").css("display", "block");
                    $("#formError").css("display", "none");
                } else {
                    $("#formError").css("display", "block");
                    $("#formSuccess").css("display", "none");
                }
            });
            return false;
        });
        
        $(function () {
            $('#formHeader a:first').tab('show')
        });

    </script>
{/block}