/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

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
          if(!isJson(data)){
            alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
            return false;
          }
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
          if(!isJson(data)){
            alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
            return false;
          }
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
