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
  {$customJs}

  $( "#poller_tmpl" ).on( "change", function() {
    var $this = $( this );
    $.ajax({
      url: "{url_for url='/configuration/poller/templates/form'}",
      type: "post",
      data: { name: $this.val() },
      dataType: "json",
      success: function( data, textStatus, jqXHR ) {
        var nbStep = 2,
            $btnEngine = $this.parents( ".form-group" ).find( ".fa-gear" ),
            $btnBroker = $this.parents( ".form-group" ).find( ".fa-database" );
        /* Set active configuration type */
        if ( data.engine ) {
          $btnEngine.removeClass( "fa-btn-inactive" ).addClass( "fa-btn-active" );
        } else {
          $btnEngine.removeClass( "fa-btn-active" ).addClass( "fa-btn-inactive" );
        }
        if ( data.broker ) {
          $btnBroker.removeClass( "fa-btn-inactive" ).addClass( "fa-btn-active" );
        } else {
          $btnBroker.removeClass( "fa-btn-active" ).addClass( "fa-btn-inactive" );
        }
        /* Remove old additional steps */
        $( "#modal" ).find( ".additional-step" ).remove();
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
  });
});
</script>
