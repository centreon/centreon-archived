/**
 * Function loading template steps
 */
function loadTemplateSteps( data, $el, url ) {
  if ($( "#wizard_form" ).length) {
    if ( $el === undefined ) {
      $el = $( "#tmpl_name" );
    }

    /* Remove old additional steps */
    $( "#modal" ).find( ".additional-step" ).remove();

    if ( data !== null && url !== undefined ) {
      $.ajax({
        url: url,
        type: "post",
        data: { name: data.id },
        dataType: "json",
        success: function( data, textStatus, jqXHR ) {
          var nbStep = 2;
          /* Add additional steps */
          $.each( data.steps, function( idx, step ) {
            var stepName = "add_poller";
            nbStep++;
            stepName += nbStep;
            /* Add step in header */
            $( "<li></li>" )
              .data( "target", "#" + stepName )
              .addClass( "additional-step" )
              .html( "<span class=\"badge badge-info\">" + nbStep + "</span>" + step.name + "<span class=\"chevron\"></span>" )
              .appendTo( "ul.steps" );
            /* Add step in wizard body */
            $( "<div></div>" )
              .attr( "id", stepName )
              .addClass( "step-pane additional-step" )
              .html( step.html )
              .appendTo( "div.step-content" );
          });
          /* Reload steps wizard */
          $( "#modal" ).centreonWizard( "reloadSteps" );
        }
      });
    }
  } else if ($( "#poller_form" ).length) {
    if ( $el === undefined ) {
      $el = $( "#tmpl_name" );
    }

    /* Remove old additional fields */
    $( "#poller_form" ).find( ".additional-field" ).remove();

    if ( data !== null && url !== undefined ) {
      $.ajax({
        url: url,
        type: "post",
        data: { name: data.id },
        dataType: "json",
        success: function( data, textStatus, jqXHR ) {
          var nbField = 1;
          /* Add additional fields */
          $.each( data.steps, function( idx, field ) {
            var fieldId = "additionalField" + nbField;
            nbField++;
            $( "<div></div>" )
              .attr( "id", fieldId )
              .addClass( "additional-field" )
              .addClass( "col-md-6" )
              .html( field.html )
              .appendTo( $("#tmpl_name").closest(".panel-body") );
          });
        }
      });
    }
  }
}
