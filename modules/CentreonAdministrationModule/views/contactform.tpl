{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}Contact Form{/block}

{block name="content"}
<div class="content-container">
    <!-- FORM -->
    <div class="row">
        <form class="form-horizontal" role="form" {$form.attributes}>
            <div>{$form.contact_info_key.html}</div>
            <div>{$form.contact_info_value.html}</div>
            <div>{$form.add_button.html}</div>
            {$form.hidden}
        </form>
    </div>

    <!-- LISTING -->
    <div class="row">
        {foreach $contactInfos as $infoKey => $infoValues}
            <h4 class="page-header" style="padding-top:0px;">{$infoKey|ucwords}</h4>
            <ul>
            {foreach $infoValues as $value}
                {assign var=routeParams value=['id'=>$value.id]}
                <li>{$value.value} <a href="{url_for url='/centreon-administration/contact/info/remove/[i:id]' params=$routeParams}"><i class="fa fa-times-circle"></i></a></li>
            {/foreach}
            </ul>
        {/foreach}
    </div>
</div>
{/block}

{block name="javascript-bottom" append}
    <script>
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
    </script>
{/block}