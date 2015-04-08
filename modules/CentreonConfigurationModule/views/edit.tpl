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
                
                var $myFormGroupLength = $(v).children("div").children(".form-group").length;
                var $hidden = 0;

                $(v).children("div").children(".form-group").each(function(j, w) {
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
            $("#formHeader").parent().parent().append(
                $('<div class="col-xs-12 col-sm-2"></div>').append($("#advanced_mode_switcher"))
            );

            {if isset($inheritanceUrl)}
            $.ajax({
              url: "{$inheritanceUrl}",
              dataType: 'json',
              type: 'get',
              success: function(data, textStatus, jqXHR) {
                if (data.success) {
                  $.each(data.values, function(key, value) {
                     if (value != null) {
                        $('#' + key + '_inheritance').text(value);
                        $('#' + key).removeClass('mandatory-field');
                        $('label[for="' + key + '"]').parent().find('span').remove();
                     }
                  });
                }
              }
            });
            
            /* Function for reload template when adding one */
            $("{$tmplField}").on('change', function(e) {
              $.ajax({
                url: "{$inheritanceTmplUrl}",
                dataType: 'json',
                type: 'post',
                data: { tmpl: e.val },
                success: function(data, textStatus, jqXHR) {
                  if (data.success) {
                    $('span[id$="_inheritance"]').text('');
                    $.each(data.values, function(key, value) {
                       if (value != null) {
                          $('#' + key + '_inheritance').text(value);
                          $('#' + key).removeClass('mandatory-field');
                          $('label[for="' + key + '"]').parent().find('span').remove();
                       }
                    });
                  }
                }
              });
            });
            {/if}
        });
        
  /**
   * Function to save tag for resource 
   * 
   * @param string sName
   */
  function addTagToResource(sName) {

    var iId = '';
    if ( sName !== null && iIdResource !== null) {
        var sResource = $('input[name=object]').val();
        var iIdResource = $('input[name=object_id]').val();
        
      $.ajax({
        url: "{url_for url='/centreon-administration/tag/add'}",
        type: "post",
        data: { 
            resourceName : sResource,
            resourceId   : iIdResource,
            tagName      : sName 
        },
        dataType: "json",
        success: function( data, textStatus, jqXHR ) {
            if (data.success) {
                iId =  data.tagId;
            }
        }
      });
    }
    return iId;
  }
   /**
   * Function to delete tag for resource 
   * 
   * @param integer iId
   */
  function deleteTagToResource(iId) {

    if (iId != "undefined" && iId !== null && iIdResource !== null) {
      var sResource = $('input[name=object]').val();
      var iIdResource = $('input[name=object_id]').val();

      $.ajax({
        url: "{url_for url='/centreon-administration/tag/delete'}",
        type: "post",
        data: { 
            tagId        : iId,
            resourceId   : iIdResource,
            resourceName : sResource,
        },
        dataType: "json",
        success: function( data, textStatus, jqXHR ) {


        }
      });
    }
 
  }
    </script>
{/block}
