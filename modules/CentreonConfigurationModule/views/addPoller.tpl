<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
<h4>Add</h4>
</div>
<div class="flash alert fade in" id="modal-flash-message" style="display: none;">
<button type="button" class="close" aria-hidden="true">&times;</button>
</div>

<div class="wizard" id="add_poller">
  <ul class="steps">
    <li class="active" data-target="#add_poller1">
      <span class="badge badge-info">1</span>
      {t}General{/t}
      <span class="chevron"></span>
    </li>
    <li data-target="#add_poller2">
      <span class="badge badge-info">2</span>
      {t}Paths{/t}
      <span class="chevron"></span>
    </li>
  </ul>
</div>
<div class="row-divider"></div>
<form role="form" class="form-horizontal" id="wizard_form">
<div class="step-content">
  <div class="step-pane active" id="add_poller1">
    {$form.poller_name.html}
    {$form.ip_address.html}
    {$form.poller_tmpl.html}
  </div>

  <div class="step-pane" id="add_poller2">
    {hook name='displayNodePaths'}
  </div>
</div>

<div class="modal-footer">
  {$form.hidden}
  <button class="btn btn-default btn-prev" disabled>{t}Prev{/t}</button>
  <button class="btn btn-default btn-next" data-last="{t}Finish{/t}" id="wizard_submit">{t}Next{/t}</button>
</div>
</form>

<script>
$(function() {
  {get_custom_js}

  /**
   * Function loading template steps
   */
  function loadTemplateSteps( data, $el ) {
    var $btnEngine, $btnBroker;
    if ( $el === undefined ) {
      $el = $( "#poller_tmpl" );
    }
    $btnEngine = $el.parents( ".form-group" ).find( ".fa-gear" );
    $btnBroker = $el.parents( ".form-group" ).find( ".fa-database" );

    /* Remove old additional steps */
    $( "#modal" ).find( ".additional-step" ).remove();

    /* Reset buttons */
    $btnEngine.removeClass( "fa-btn-active" ).addClass( "fa-btn-inactive" );
    $btnBroker.removeClass( "fa-btn-active" ).addClass( "fa-btn-inactive" );

    if ( data !== null ) {
      $.ajax({
        url: "{url_for url='/configuration/poller/templates/form'}",
        type: "post",
        data: { name: data.id },
        dataType: "json",
        success: function( data, textStatus, jqXHR ) {
          var nbStep = 2;
          /* Set active configuration type */
          if ( data.engine ) {
            $btnEngine.removeClass( "fa-btn-inactive" ).addClass( "fa-btn-active" );
          }
          if ( data.broker ) {
            $btnBroker.removeClass( "fa-btn-inactive" ).addClass( "fa-btn-active" );
          }
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
  }

  /* When select a poller template */
  $( "#poller_tmpl" ).on( "change", function() {
    var $this = $( this ),
        data = $this.select2( "data" );
 
    loadTemplateSteps( data, $this );
  });

  $( document ).unbind( "finished" );
  $( document ).on( "finished", function( event ) {
    var errorMsg = "",
        validMandatory = true,
        $form = $( event.target ).find( "form" );
    /* Validate mandatory fields */
    $form.find( "input.mandatory-field" ).each( function( idx ) {
      if ( $( this ).val().trim() === "" ) {
        validMandatory = false;
        $( this ).parent().addClass( "has-error has-feedback" );
        errorMsg += "<p>" + $( this ).attr( "placeholder" ) + " is required</p>";
      }
    });

    if ( !validMandatory ) {
      alertModalMessage( errorMsg, "alert-danger" );
      return false;
    }

    $.ajax({
      url: "{url_for url='/configuration/poller/add'}",
      data: $( "#wizard_form" ).serializeArray(),
      dataType: "json",
      type: "post",
      success: function( data, textStatus, jqXHR ) {
        alertModalClose();
        if ( data.success ) {
          // @todo
        } else {
          alertModalMessage( data.error, "alert-danger" );
        }
      }
    });
  });
});
</script>
