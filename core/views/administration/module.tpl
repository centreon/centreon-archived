{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{$objectName}{/block}

{block name="content"}
    {datatable module=$moduleName object=$objectName datatableObject=$datatableObject configuration=false}
{/block}

{block name="javascript-bottom" append}
    {datatablejs module=$moduleName object=$objectName objectUrl=$objectListUrl}

<script>
$(function() {
  $( "#datatable{$object}" ).on( "draw.dt", function () {
    $( "input.enabled" ).each( function( idx, el ) {
      var value = $( el ).attr( "value" ),
          readonly = false,
          checked = true;

      if ( value == 2 ) {
        readonly = true;
      } else if ( value == 0 ) {
        checked = false;
      }
      
      $( el ).bootstrapSwitch({
         state: checked,
         readonly: readonly,
         onText: "Enabled",
         offText: "Disabled",
         onSwitchChange: function( e, state ) {
           var $el = $( e.currentTarget );
           if ( state ) {
             $.ajax({
               url: $el.data( "urlenabled" ),
               type: "get",
               dataType: "json",
               success: function( data, textStatus, jqXHR ) {
                 if ( data.success ) {
                   alertMessage( "The module is enabled", "alert-success" );
                 } else {
                   alertMessage( "Error when enabled the module.", "alert-danger" );
                   $el.bootstrapSwitch( "state", false, true);
                 }
               }
             });
           } else {
             $.ajax({
               url: $el.data( "urldisabled" ),
               type: "get",
               dataType: "json",
               success: function( data, textStatus, jqXHR ) {
                 if ( data.success ) {
                   alertMessage( "The module is disabled", "alert-success" );
                 } else {
                   alertMessage( "Error when disabled the module.", "alert-danger" );
                   $el.bootstrapSwitch( "state", false, true);
                 }
               }
             });
           }
         }
      });
    });
  });
});
</script>
{/block}
