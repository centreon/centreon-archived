{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Edit a Poller{/t}{/block}

{block name="content"}
<div class="content-container">
  <div class="row">
    <form class="form-horizontal" role="form" method="post" id="edit_poller">
    <div class="form-tabs-header">
      <div class="inline-block">
        <ul class="nav nav-tabs" id="formHeader">
          <li class="active">
            <a href="#pollerGeneral" data-toggle="tab">{t}General{/t}</a>
          </li>
          <li>
            <a href="#pollerPaths" data-toggle="tab">{t}Paths{/t}</a>
          </li>
        </ul>
      </div>
    </div>
    <div class="tab-content" id="formContent">
      <div class="tab-pane active" id="pollerGeneral">
        {$form.poller_name.html}
        {$form.ip_address.html}
        {$form.poller_tmpl.html}
      </div>
      <div class="tab-pane" id="pollerPaths">
        {hook name='displayNodePaths'}
      </div>
    </div>
    <div>
      <div class="form-group">
        <div class="col-sm-offset-2 col-sm-9">
          <input id="save_form" type="submit" name="save_form" value="Save" class="btn btn-default">
        </div>
      </div>
    </div>
    {$form.hidden}
    </form>
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
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
  $( "#edit_poller" ).find( ".additional-step" ).remove();

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
          var stepName = "edit_poller";
          nbStep++;
          stepName += nbStep;
          /* Add step in header */
          $( "<li></li>" )
            .addClass( "additional-step" )
            .append(
                $( "<a></a>" )
                    .attr( "data-toggle", "tab" )
                    .attr( "href", "#" + stepName )
                    .html( step.name )
            )
            .appendTo( "#formHeader" );
          /* Add step in wizard body */
          $( "<div></div>" )
            .attr( "id", stepName )
            .addClass( "tab-pane additional-step" )
            .html( step.html )
            .appendTo( "#formContent" );
        });
      }
    });
  }
}

$(function() {
  /* When select a poller template */
  $( "#poller_tmpl" ).on( "change", function() {
    var $this = $( this ),
        data = $this.select2( "data" );
 
    loadTemplateSteps( data, $this );
  });
});
</script>
{/block}
