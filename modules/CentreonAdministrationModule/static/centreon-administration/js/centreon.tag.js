$(function () {
  var tagExpand = false;
  /* Event for add a tag */
  $( document ).on( "click", ".addtag a", function() {
    var $newTag = $( this ).parent().parent();
    if ( tagExpand ) {
      $.ajax({
        url: jsUrl.tag.add,
        data: {
          resourceId: $newTag.data( "resourceid" ),
          resourceName: $newTag.data( "resourcetype" ),
          tagName: $newTag.find( "input" ).val()
        },
        dataType: "json",
        method: "post",
        success: function( data, textStatus, jqXHR ) {
          if ( ! data.success ) {
            alertMessage( "Error during save the tag." );
          }
        }
      });
    } else {
      $( this ).parent().removeClass( "noborder" );
      $newTag.find( ".title > input" ).animate({
        width: "100px"
      });
      $newTag.find( "input" ).focus();
      tagExpand = true;
    }
  });

  /* Event for delete a tag */
  $( document ).on( "click", ".tag:not(.addtag) .remove a", function() {
    var $newTag = $( this ).parent().parent();
    $.ajax({
      url: jsUrl.tag.del,
      data: {
          resourceId: $newTag.data( "resourceid" ),
          resourceName: $newTag.data( "resourcetype" ),
          tagId: $newTag.data( "tagid" )
      },
      dataType: "json",
      method: "post",
      success: function( data, textStatus, jqXHR ) {
        if ( ! data.success ) {
          alertMessage( "Error during delete the tag." );
        }
      }
    });
  });

  /* Close the input for add a tag */
  $( document ).on( "click", function( e ) {
    var $el = $( e.target );
    if ( !tagExpand ||  $el.hasClass( ".addtag" ) || $el.parents( ".addtag" ).length > 0 ) {
      return;
    }
    $( ".addtag input" ).animate({
      width: "0"
    });
    $( ".addtag .remove" ).addClass( "noborder" );
    tagExpand = false;
  });
});
