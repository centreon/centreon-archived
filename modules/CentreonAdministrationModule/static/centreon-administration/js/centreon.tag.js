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

  /* Action for button Add To */
  $( document ).on( "click", "#addToTag", function( e ) {
    var $header = $( "<div></div>" ).addClass( "modal-header" ),
        $body = $( "<div></div>" ).addClass( "modal-body" ),
        $footer = $( "<div></div>" ).addClass( "modal-footer" );
    /* Cleanup the modal */
    $( "#modal" ).find( ".modal-content" ).html( "" );
    $header.html(
      "<button type='button' class='close' data-dismiss='modal'>&times;</button>"
      + "<h4 class='modal-title'>Add to tag</h4>"
      + "<div class='flash alert fade in' id='modal-flash-message' style='display: none;'>"
      + "<button type='button' class='close' aria-hidden='true'>&times;</button>"
      + "</div>"
    );
    $body.html(
      "<form role='form'><div class='form-group'>"
      + "<input type='text' class='form-control' name='tagName'>"
      + "</div></form>"
    );
    $footer.html(
      "<button type='button' class='btn btn-default' data-dismiss='modal'>Close</button>"
      + "<button type='button' class='btn btn-primary' id='saveAddToTag'>Save</button>"
    );
    $( "#modal" ).find( ".modal-content" )
      .append( $header )
      .append( $body )
      .append( $footer );
    $( "#modal" ).modal();

    $( "#saveAddToTag" ).on( "click", function() {
      var listObject = [],
          name = $( "#modal" ).find( "input[name='tagName']" ).val();
      $( ".allBox:checked" ).each( function( idx, value ) {
        listObject.push( $( value ).val() );
      });
      $.ajax({
        url: jsUrl.tag.add,
        data: {
          tagName: name,
          resourceName: $( "#addToTag" ).data( "resourcetype" ),
          resourceId: listObject
        },
        dataType: "json",
        method: "post",
        success: function( data, textStatus, jqXHR ) {
          if ( data.success ) {
            $( "#modal" ).modal( "hide" );
            oTable.api().ajax.reload( null, false );
          } else {
            alertModalMessage( data.errmsg );
          }
        }
      });
    });
  });
});
