{extends file="file:[Core]baseLayout.tpl"}

{block name="title"}{$pageTitle}{/block}

{block name="content"}
    <div class="col-md-12">
        <div class="buttonGroup right">
            <button id="advanced_mode_switcher" href="#" class="btnC btnDefault">
                <i class="icon-switch-adv"></i>
            </button>
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

        var tabContentList = {};

        function hideEmptyTab()
        {
            var $tabs = $('.form-tabs-header li');
            $tabs.each(function(index) {
                divId = $(this).children().attr("href").substr(1);
                if (tabContentList[divId] == 0) {
                    if ($(this).hasClass('active')) {
                        $("#formHeader > li:first-child").tab("show");
                        $(".tab-content > .tab-pane").removeClass("active");
                        $($("#formHeader > li:first-child > a").attr("href")).addClass("active");
                    }
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        }


        function hideEmptyBlocks()
        {
            $(".panel-body").each(function(i, v) {
                
                var $myFormGroupLength = $(v).children("div").children(".form-group").length;
                var $hidden = 0;
                myTab = $(v).parent().parent().attr("id");

                $(v).children("div").children(".form-group").each(function(j, w) {
                    if ($(w).css("display") === "none") {
                        $hidden += 1;
                    }
                });
                
                if ($myFormGroupLength === $hidden) {
                    tabContentList[myTab] = 0;
                    $(v).prev().css("display", "none");
                } else {
                    tabContentList[myTab] = 1;
                    $(v).prev().css("display", "block");
                }
            });
        }
        
        $(document).ready(function(e) {
            hideEmptyBlocks();
            hideEmptyTab();
        });
        
        $("#advanced_mode_switcher").on("click", function (event) {
            $(".advanced").toggleClass("advanced-display");
            if ($(".advanced").hasClass('advanced-display')) {
                $(this).html('<i class="icon-switch"></i>');
            } else {
                $(this).html('<i class="icon-switch-adv"></i>');
            }
            hideEmptyBlocks();
            hideEmptyTab();
        });
        
        $("#{$formName}").on("submit", function (event) {
           if ($(this).valid()) {
              $.ajax({
                  url: "{url_for url=$validateUrl}",
                  type: "POST",
                  dataType: 'json',
                  data: $(this).serializeArray(),
                  context: document.body
              })
                .success(function(data, status, jqxhr) {
                    if(!isJson(data)){
                      alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                      return false;
                    }
                    alertClose();
                    if (data.success) {
                      {if isset($formRedirect) && $formRedirect}
                          window.location="{url_for url=$formRedirectRoute}";
                      {else}
                          alertMessage("{t}The object has been successfully saved{/t}", "notif-success", 3);
                      {/if}
                    } else {
                        alertMessage(data.error, "notif-danger");
                    }
              }).error(function(){
                alertModalMessage("an error occured", "alert-danger");
              });
            }
            return false;
        });
        
        $(function () {

            {if isset($inheritanceUrl)}
            $.ajax({
              url: "{$inheritanceUrl}",
              dataType: 'json',
              type: 'get',
              success: function(data, textStatus, jqXHR) {
                if(!isJson(data)){
                  alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                  return false;
                }
                if (data.success) {
                  $.each(data.values, function(key, value) {
                     if (value != null) {
                        $('#' + key + '_inheritance').text(value);
                        $('#' + key).removeClass('mandatory-field');
                        $('label[for="' + key + '"]').parent().find('span').remove();
                     }
                  });
                }
              },
              error : function (){
                  alertModalMessage("an error occured", "alert-danger");
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
                    if(!isJson(data)){
                      alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                      return false;
                    }
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
                },
                error : function (){
                    alertModalMessage("an error occured", "alert-danger");
                }
              });
            });
            {/if}
            {if isset($inheritanceTagsUrl)}
                var sText = '';
                var sText1 = '';

                $.ajax({
                      url: "{$inheritanceTagsUrl}",
                      dataType: 'json',
                      type: 'get',
                      success: function(data, textStatus, jqXHR) {
                        if(!isJson(data)){
                            alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                            return false;
                        }
                        if (data.success) {
                            var i = 0 ;
                            $.each(data.values, function(key, value) {

                                 if (value != null) {
                                    var disabledItem = '<li class="tagGlobalNotDelete">'+value+'</li>';
                                    var disabledItem1 = value;

                                    //$('#s2id_host_tags').children('ul').prepend(disabledItem);
                                    sText =  sText+' '+ disabledItem;
                                    sText1 = sText1+' '+ disabledItem1;
                                 }
                                 i = key+1;
                              });
                                //console.log(i);
                                $('div[id$="tags_inheritance"]').addClass("inheritanceTags");

                             if(i > 4) {

                                    var a = $('<a tabindex="0" data-toggle="popover" data-placement="bottom">Inherited tags <i class="icon-plus ico-16"></i></a>');

                                    $('div[id$="tags_inheritance"]').html(a);
                                    a.append('<div id="popover_content_wrapper" style="display: none"><ul>'+sText+'</ul>');

                               $('[data-toggle="popover"]').popover(
                               {
                                    html : true,
                                        content: function() {
                                          return $('#popover_content_wrapper').html();
                                        }
                               }
                               );
                            } else {
                                $('div[id$="tags_inheritance"]').html('<ul>'+sText+'</ul>');
                            }
                          }
                      },
                      error : function (){
                          alertModalMessage("an error occured", "alert-danger");
                      }
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
            if(!isJson(data)){
               alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
               return false;
            }
            if (data.success) {
                iId =  data.tagId;
            }
        },
        error : function (){
            alertModalMessage("an error occured", "alert-danger");
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
            if(!isJson(data)){
                alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                return false;
            }

        },
        error : function (){
            alertModalMessage("an error occured", "alert-danger");
        }
      });
    }
 
  }
    </script>
{include file="[Core]/form/validators.tpl"}
<script>
    $("#{$formName}").centreonForm({
    rules: (formValidRule["{$formName}"] === undefined ? {} : formValidRule["{$formName}"])
    });
</script>
{/block}
