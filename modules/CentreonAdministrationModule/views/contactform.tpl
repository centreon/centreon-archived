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
    <div id="infolist" class="row">
        {foreach $contactInfos as $infoKey => $infoValues}
            <div style="padding-top:0px;" class="col-sm-3">{$infoKey|ucwords}</div>
            <div class="col-sm-9">
                <ul id="{$infoKey}Ul" style="border-left: 1px solid black">
                    {foreach $infoValues as $value}
                        {assign var=routeParams value=['id'=>$value.id]}
                        <li style="list-style: none">
                            <div class="col-sm-9">
                                {$value.value}
                            </div>
                            <div class="col-sm-3">
                                <a class="btn btn-danger btn-xs" href="{url_for url='/centreon-administration/contact/info/remove/[i:id]' params=$routeParams}"><i class="fa fa-times"></i></a>
                            </div>
                        </li>
                    {/foreach}
                </ul>
            </div>
            <div class="col-sm-12"><hr></div>
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
                        var refEl = "#" + data.origin + "Ul";
                        if (!$(refEl).length) {
                            $("#infolist").append(
                                "<div style=\"padding-top:0px;\" class=\"col-sm-3\">" + data.origin + "</div>" +
                                "<div class=\"col-sm-9\">" + 
                                "<ul id=\""+ data.origin + "Ul\" style=\"border-left: 1px solid black\"></ul>" +
                                "</div><div class=\"col-sm-12\"><hr></div>"
                            );
                        }
                        $(refEl).append(
                            "<li style=\"list-style: none\">" +
                            "<div class=\"col-sm-9\">" + data.value + "</div>" +
                            "<div class=\"col-sm-3\">" +
                            "<a class=\"btn btn-danger btn-xs\" href=\"" + data.removeurl + "\"><i class=\"fa fa-times\"></i></a>\n\"" +
                            "</div>" + 
                            "</li>"
                        );
                    {/if}
                } else {
                    alertMessage(data.error, "alert-danger");
                }
            });
            return false;
        });
    </script>
{/block}