<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h4>
{if isset($modalTitle)}
  {$modalTitle}
{else}
  {t}Add{/t}
{/if}
</h4>
</div>
<div class="flash alert fade in" id="modal-flash-message" style="display: none;">
  <button type="button" class="close" aria-hidden="true">&times;</button>
</div>
<div class="wizard" id="{$name}">
  <ul class="steps">
    {foreach $steps as $step}
    <li data-target="#{$name}_{$step@index + 1}"{if $step@index == 0} class="active"{/if}><span class="badge badge-info">{$step@index + 1}</span>{$step@key}<span class="chevron"></span></li>
    {/foreach}
  </ul>
</div>
<div class="row-divider"></div>
<form role="form" class="form-horizontal" id="wizard_form">
  <div class="step-content">
   {foreach $steps as $step}
   <div class="step-pane{if $step@index == 0} active{/if}" id="{$name}_{$step@index + 1}">
     {assign var="isfilter" value=0}
     {foreach $step['default'] as $component}
       {if {preg_match pattern="^cmp-(.+)" subject=$formElements[$component['name']]['name']}}
           {assign var="isfilter" value={$formElements[$component['name']]['label']|strip_tags}}
       {else}
           {if $isfilter}
             <div class="form-group">
                <div class="col-sm-3" style="text-align:right;">
                  <label class="label-controller" for="{$formElements[$component['name']]['name']}">
                    {$formElements[$component['name']]['label']}
                  </label>
                </div>
                <div class="col-sm-3">
                  {assign var="cmpname" value="cmp-"|cat:$component['name']} 
                  <select name="{$cmpname}">
                    {foreach from=$cmpOptions key=k item=v}
                      {assign var="selected" value=""}
                      {if $isfilter == $k}
                        {assign var="selected" value="selected"}
                      {/if}
                      <option value="{$k}" {$selected}>{$v}</option>
                    {/foreach}
                  </select>
                </div>
                <div class="col-sm-5">
                    {$formElements[$component['name']]['input']}
                </div>
             </div>
           {else}
             {$formElements[$component['name']]['html']}
           {/if}
           {assign var="isfilter" value=0}
       {/if}
     {/foreach}
   </div>
   {/foreach}
  </div>
  <div class="modal-footer">
    {$formElements.hidden}
    <button class="btn btn-default btn-prev" disabled>{t}Prev{/t}</button>
    <button class="btn btn-default btn-next" data-last="{t}Finish{/t}" id="wizard_submit">{t}Next{/t}</button>
  </div>
</form>
<script>
var modalListener;
$(function() {
  $(document).unbind('finished');
  {if isset($validateUrl)}
  $(document).on('finished', function (event) {
    /*var validateMandatory = true;
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
    }*/

    if ($('wizard_form').valid()) {
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
    }
  });
  {/if}
  {get_custom_js}
});
</script>
