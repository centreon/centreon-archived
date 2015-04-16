$(function () {
  var tagExpand = false;

  function saveTag( $newTag ) {
    var tmplTagCmpl,
        tmplTag = "<div class='tag' data-resourceid='<%resourceid%>' data-resourcetype='<%resourcetype%>' data-tagid='<%tagid%>'>"
          + "<div class='tagname'><%tagname%></div>"
          + "<div class='remove'><a href='#'>&times;</a></div>"
          + "</div>";
        tagName = $newTag.find( "input" ).val().trim();
    /* Does not accept empty tag */
    if ( tagName === "" ) {
      return;
    }
    tmplTagCmpl = Hogan.compile( tmplTag, { delimiters: "<% %>" } );
    $.ajax({
      url: jsUrl.tag.add,
      data: {
        resourceId: $newTag.data( "resourceid" ),
        resourceName: $newTag.data( "resourcetype" ),
        tagName: tagName
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
            tagname: tagName,
            tagid: data.tagId
          });
          $newTag.parent().prepend( " " ).prepend( $( tag ) );
          $newTag.find( "input" )
            .animate({
              "width": 0,
              "padding": 0
            })
            .val( "" );
          tagExpand = false;
        }
      }
    });
      
  }

  /* Event for add a tag */
  $( document ).on( "click", ".addtag a", function() {
    var $newTag = $( this ).parent().parent();
    if ( tagExpand ) {
      saveTag( $newTag );
    } else {
      $( this ).parent().removeClass( "noborder" );
      $newTag.find( ".title > input" ).animate({
        width: "100px"
      });
      $newTag.find( "input" ).focus();
      tagExpand = true;
    }
  });

  /* Save the tag when press enter */
  $( document ).on( "keyup", ".addtag input", function( e ) {
    var $newTag,
        key = e.keyCode || e.which;

    if ( key == 13 ) {
      $newTag = $( e.currentTarget ).parent().parent();
      saveTag( $newTag );
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
      width: 0,
      padding: 0
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

    function saveTags() {
      var listObject = [],
          name = $( "#modal" ).find( "input[name='tagName']" ).val();
      $( ".selected" ).each( function( idx, value ) {
        listObject.push( $( value ).data('id') );
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
    }

    $( "#modal" ).find( "form" ).on( "submit", function( e ) {
      e.preventDefault();
      e.stopPropagation();
      saveTags();
    });

    $( "#saveAddToTag" ).on( "click", function() {
      saveTags();
    });
  });
  $( document).on( "click", ".tagname", function( e ) {
      e.preventDefault();
      e.stopPropagation();
      var sSearch = $(this).html();
      var sOldFilter = $("input[name='advsearch']").val();
      var newSearch = "tags:" + sSearch;
      var regexSearch = new RegExp("(^| )" + newSearch + "( |$)", "g");
      if (null === sOldFilter.match(regexSearch)) {
        $("input[name='advsearch']").val($.trim(sOldFilter + " " + newSearch));
      }
      $("#btnSearch").click();
  });
});
