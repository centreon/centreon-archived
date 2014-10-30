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
         onText: "ON",
         offText: "OFF",
         onSwitchChange: function( e, state ) {
           var $el = $( e.currentTarget );
           alertClose();
           if ( state ) {
             $.ajax({
               url: $el.data( "urlenabled" ),
               type: "get",
               size: "small",
               dataType: "json",
               success: function( data, textStatus, jqXHR ) {
                 if ( data.success ) {
                   alertMessage( "{t}The module is enabled{/t}", "alert-success", 3 );
                 } else {
                   alertMessage( "{t}Error when enabled the module.{/t}", "alert-danger" );
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
                   alert
                   alertMessage( "{t}The module is disabled{/t}", "alert-success", 3 );
                 } else {
                   alertMessage( "{t}Error when disabled the module.{/t}", "alert-danger" );
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
