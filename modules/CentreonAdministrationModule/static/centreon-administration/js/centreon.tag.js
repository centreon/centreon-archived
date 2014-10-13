$(function () {
  var tagExpand = false;
  /* Event for add a tag */
  $( document ).on( "click", ".addtag a", function() {
    var tmplTagCmpl,
        $newTag = $( this ).parent().parent(),
        tmplTag = "<div class='tag' data-resourceid='<%resourceid%>' data-resourcetype='<%resourcetype%>' data-tagid='<%tagid%>'>"
          + "<div class='title'><%tagname%></div>"
          + "<div class='remove'><a href='#'>&times;</a></div>"
        + "</div> ";
    tmplTagCmpl = Hogan.compile( tmplTag, { delimiters: "<% %>" } );
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
          } else {
            tag = tmplTagCmpl.render({
              resourceid: $newTag.data( "resourceid" ),
              resourcetype: $newTag.data( "resourcetype" ),
              tagname: $newTag.find( "input" ).val(),
              tagid: data.tagId
            });
            $newTag.parent().prepend( $( tag ) );
            $newTag.find( "input" ).val( "" );
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
        } else {
          $newTag.remove();
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
    }).val( "" );
    $( ".addtag .remove" ).addClass( "noborder" );
    tagExpand = false;
  });
});
