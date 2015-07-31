{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Graphs{/t}{/block}

{block name="style-head"}
<style>
.delete-graph {
  cursor: pointer;
}
</style>
{/block}

{block name="content"}
<div class="content-container">
  <div class="panel panel-default">
    <div class="panel-heading">
      <div class="row search">
        <form role="form" class="CentreonForm">
          <div class="form-group col-md-3">
            <input type="text" name="period" class="form-control" placeholder="Period" >
          </div>
          <div class="form-group col-md-4">
            <div class="inlineGroup">
              <div class="Elem1">
                <input type="text" id="service" name="service" class="form-control" >
              </div>
              <span class="Elem2">
                <button class="btnC btnDefault" type="button" id="addGraph">{t}Add{/t}</button>
              </span>
            </div>
          </div>
          <div class="form-group col-md-5">
            <div class="input-group">
              <input type="text" id="view" name="view" class="form-control">
              <span class="input-group-btn">
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="{t}Load{/t}" id="loadView"><i class="icon-arrow-down"></i></button>
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="{t}Save{/t}" id="saveView"><i class="icon-save"></i></button>
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="{t}Delete{/t}" id="deleteView"><i class="icon-delete"></i></button>
                <button class="btnC btnDefault" data-toggle="tooltip" data-placement="bottom" title="" id="bookmarkView" data-original-title="Bookmark"><i id="bookmarkStatus" class="icon-fill-fav"></i></button>
              </span>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="pull-right">
      <input name="graphSize" type="checkbox" value="2" >
    </div>
  </div>
  <div id="graphs" class="row"></div>
</div>
<div class="modal fade" id="saveViewModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
        <h4 class="modal-title">{t}Save view{/t}</h4>
      </div>
      <div class="modal-body">
        <form role="role" class="form-horizontal">
          <div class="form-group">
            <div class="col-sm-2" style="text-align: right">
              <label class="label-controller" for="name">{t}Name{/t}</label>
              <span style="color: red">*</span>
            </div>
            <div class="col-sm-9">
              <input type="text" name="name" class="form-control mandatory-field" placeholder="{t}Name{/t}">
            </div>
          </div>
          <div class="form-group">
            <div class="col-sm-2" style="text-align: right">
              <label class="label-controller" for="name">{t}Pricacy{/t}</label>
              <span style="color: red">*</span>
            </div>
            <div class="col-sm-9">
              <label class="label-controller" for="mode1">
                &nbsp;<input type="radio" name="privacy" value="1"> Public
              </label>
              <label class="label-controller" for="mode2">
                &nbsp;<input type="radio" name="privacy" value="0" checked> Private
              </label>
            </div>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">{t}Close{/t}</button>
        <button type="button" class="btn btn-primary" id="saveViewButton">{t}Save{/t}</button>
      </div>
    </div>
  </div>
</div>
<div class="hide">
<form name="forDownload" action="{url_for url='/centreon-performance/download'}" method="post">
<input type="hidden" name="svg">
<input type="hidden" name="graph_type" value="svc">
<input type="hidden" name="graph_id">
</form>
</div>
{/block}

{block name="javascript-bottom" append}
    
{if isset($jsUrl) }    
<script>
    var jsUrl = {$jsUrl|json_encode};
</script>
{/if}
    
<script>

var nbGraph = 0;
{literal}
var graphTmpl = Hogan.compile("<div class=\"col-xs-12 graph {{classCol}}\">" +
  "  <div class=\"panel panel-default\">" +
  "    <div class=\"panel-heading\">" +
  "      <h5>{{graphTitle}}" +
  "        <button class=\"close pull-right delete-graph\">&times;</button>" +
  "        <button class=\"close pull-right stack-graph\">" +
  "          <i class=\"fa fa-area-chart\"></i>" +
  "        </button>" +
  "      </h5>" +
  "    </div>" +
  "    <div class=\"panel-body\">" +
  "      <div class=\"c3\" id=\"{{graphId}}\"></div>" +
  "    </div>" +
  "  </div>" +
  "</div>");
{/literal}


function createGraph(serviceId, title) {
  var graphId, graphEl, startTime, endTime,
      classCol = "col-sm-12",
      time = $("input[name='period']").val();
  
  ++nbGraph;
  graphId = "graph-" + nbGraph;
  
  if ($("input[name='graphSize']").is(":checked")) {
    classCol = "col-sm-6";
  }
  
  /* Add graph */
  graphEl = graphTmpl.render(
    {
      graphId: graphId,
      graphTitle: title,
      classCol: classCol
    }
  );
    
  $(graphEl).data("serviceId", serviceId).appendTo("#graphs");
    
  startTime = moment(time.split(" - ")[0]).format('X');
  endTime = moment(time.split(" - ")[1]).format('X');
  addChart(graphId, serviceId, startTime, endTime);
}

function saveView(viewId, graphs, viewName, viewPrivacy) {
  $.ajax({
    url: "{url_for url="/centreon-performance/view"}",
    method: "POST",
    data: {
      viewId: viewId,
      viewName: viewName,
      viewPrivacy: viewPrivacy,
      graphs: graphs
    },
    dataType: "json",
    success: function(data, textStatus, jqXHR) {
      if(!isJson(data)){
        alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
        return false;
      }
      if (data.success) {
        alertMessage("{t}Graph view saved{/t}", "alert-success", 3);
      } else {
        alertMessage("{t}Error when trying to save graph view{/t}", "alert-critical");
      }
    }
  });
}
  
function switchGraphSize(nbGraphs) {
  var i, $graphs = $("#graphs .graph");
  if (nbGraphs === 1) {
    $graphs.removeClass("col-sm-6").addClass("col-sm-12");
  } else if (nbGraphs === 2) {
    $graphs.removeClass("col-sm-12").addClass("col-sm-6");
  }
  for (i = 0; i < charts.length; i = i + 1) {
    charts[i].resize();
  }
}

$(function() {
  /* Iniialiaze period */
  /*
  var endTime = moment(),
      startTime = moment(endTime).subtract(24, 'hours');
  */
 
var endTime,
 startTime,
 timeMoinsHeight,
 timeMoinsDay,
 timeMoinsWeek,
 timeMoinsMonth,
 graphSize;
      
if (sessionStorage.length > 0 &&  sessionStorage.getItem("sTimezone") != 'undefined' && sessionStorage.getItem("sTimezone") != '') {
    endTime = moment().tz(sessionStorage.getItem("sTimezone"));
    timeMoinsHeight = moment().tz(sessionStorage.getItem("sTimezone")).subtract(8, 'hours');
    timeMoinsDay = moment().tz(sessionStorage.getItem("sTimezone")).subtract(24, 'hours');
    timeMoinsWeek = moment().tz(sessionStorage.getItem("sTimezone")).subtract(7, 'days');
    timeMoinsMonth = moment().tz(sessionStorage.getItem("sTimezone")).subtract(1, 'months');
} else {
    endTime = moment();
    timeMoinsHeight = moment().subtract(8, 'hours');
    timeMoinsDay = moment().subtract(24, 'hours');
    timeMoinsWeek = moment().subtract(7, 'days');
    timeMoinsMonth = moment().subtract(1, 'months');
}

startTime = moment(endTime).subtract(24, 'hours');

  /* Load tooltip */
  $("[data-toggle='tooltip']").tooltip();

  /* Action on save */
  $("#saveViewButton").on("click", function(e) {
    var graphs = [];
    $(".graph").each(function(idx, element) {
      graphs.push({
        "type": "service",
        "id": $(element).data("serviceId")
      });
    });
    saveView("", graphs, $("input[name='name']").val(), $("input[name='privacy']:checked").val());
    $("#saveViewModal").modal("hide");
  });

  /* Action delete a view */
  $("#deleteView").on("click", function(e) {
    var viewId = $("#view").val();
    e.preventDefault();
    if (viewId === "") {
      return;
    }
    
    $.ajax({
      url: "{url_for url="/centreon-performance/view"}/" + viewId,
      dataType: "json",
      method: "DELETE",
      success: function(data, textStatus, jqXHR) {
        if(!isJson(data)){
            alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
            return false;
        }
        if (data.success) {
          alertMessage("{t}The graph view is deleted{/t}", "alert-success", 3);
          // @todo reset select2 field
        } else {
          alertMessage("{t}Error during delete graph view{/t}", "alert-critical");
        }
      }
    });
  });

  /* Action on delete */
  $("#graphs").on("click", ".delete-graph", function(e) {
    var $element = $(e.currentTarget);
    id = $element.parents(".graph").find(".c3").attr("id").replace("graph-", "");
    charts[id - 1] = null;
    $element.parents(".graph").remove();
  });

  function makeStyleObject(rule) {
    var output = {};
    for (var i = 0; i < rule.length; i++) {
      output[rule[i]] = rule[rule[i]];
    }
    return output;
  }

  /* Download graph */
  $('#graphs').on('click', '.download-graph', function(e) {
    var $element = $(e.currentTarget);
    id = $element.parents(".graph").find(".c3").attr("id").replace("graph-", "");
    form = $('form[name="forDownload"]');
    form.find('input[name="graph_id"]').val(id);
    //svg = $($element.parents('.graph').find('.c3').find('svg')[0]).clone();
    svg = $element.parents('.graph').find('.c3').find('svg')[0];
    /* Replace style */
    /*chartStyle = null;
    for (var i = 0; i < document.styleSheets.length; i++) {
      if (document.styleSheets[i].href && document.styleSheets[i].href.indexOf('c3.css') !== -1) {
        if (document.styleSheets[i].rules !== undefined) {
          chartStyle = document.styleSheets[i].rules;
        } else {
          chartStyle = document.styleSheets[i].cssRules;
        }
      }
    }
    if (chartStyle !== null) {
      for (var i = 0; i < chartStyle.length; i++) {
        if (chartStyle[i].type === 1) {
          var styles = makeStyleObject(chartStyle[i].style);
          var elements = svg.find(chartStyle[i].selectorText);
          if (elements.length > 0) {
            elements.css(styles);
          }
        }
      }
    }*/

    /**/
    form.find('input[name="svg"]').val(svg.outerHTML);
    form.submit();
  });

  $('#graphs').on('click', '.stack-graph', function(e) {
    var $element = $(e.currentTarget),
        id = $element.parents(".graph").find(".c3").attr("id").replace("graph-", "");
    if ($element.hasClass('active')) {
      stackCurve(id - 1 , false);
    } else {
      stackCurve(id - 1, true);
    }
    $element.toggleClass('active');
  });

  /* Initialize service selection */
  $("#service").select2({
    placeholder: "Select a service",
    allowClear: true,
    ajax: {
      data: function(term, page) { return { q: term }; },
      dataType: "json",
      url: "{url_for url="/centreon-performance/service/withmetrics"}" ,
      results: function(data) { return { results: data, more: false }; }
    }
  });

  /* Initialize list view */
  $("#view").select2({
    placeholder: "Load a view",
    allowClear: true,
    ajax: {
      data: function(term, page) { return { q: term }; },
      dataType: "json",
      url: "{url_for url="/centreon-performance/view"}" ,
      results: function(data) { return { results: data, more: false }; }
    }
  });

  /* Initialize dateragepicker */
  $("input[name='period']").daterangepicker({
    timePicker: true,
    timePickerIncrement: 5,
    timePicker12Hour: false,
    format: 'YYYY-MM-DD HH:mm',
    startDate: startTime,
    endDate: endTime,
    ranges: {
      "8 hours": [timeMoinsHeight, endTime],
      "24 hours": [timeMoinsDay, endTime],
      "1 week": [timeMoinsWeek, endTime],
      "1 month": [timeMoinsMonth,endTime]
    }
  });
  
  $("input[name='period']").val(startTime.format('YYYY-MM-DD HH:mm') + " - " + endTime.format('YYYY-MM-DD HH:mm'));

  $("#addGraph").on("click", function() {
      var inView = false,
        serviceId = $("#service").val();
    /* Search if the service is already in view */
    $(".graph").each(function(idx, element) {
      if ($(element).data("serviceId") === serviceId) {
        inView = true;
      }
    });
    if (inView) {
      return;
    }
    
    createGraph(serviceId, $("#service").select2("data").text);
  });

  $("input[name='period']").on("apply.daterangepicker", function() {
    var startTime, endTime,
        time = $("input[name='period']").val();
    if ($("#service").val() === undefined) {
      return;
    }
    startTime = moment(time.split(" - ")[0]).format('X');
    endTime = moment(time.split(" - ")[1]).format('X');
    updateChart(startTime, endTime);
  });

  $("#saveView").on("click", function(e) {
    var graphs = [],
        viewName = "",
        viewId = $("#view").val();
    e.preventDefault();
    /* Get all graph in page*/
    $(".graph").each(function(idx, element) {
      graphs.push({
        "type": "service",
        "id": $(element).data("serviceId")
      });
    });
    /* Stop if there are no graph in view */
    if (graphs.length === 0) {
      alertMessage("No graph selected", "alert-warning");
      setTimeout(alertClose, 5000);
      return;
    }

    if (viewId === "") {
      $("input[name='name']").val("");
      $("input[name='privacy'][value='0']").attr("checked", "checked"); // TODO better
      $("#saveViewModal").modal("show"); 
    } else {
      saveView(viewId, graphs, "", 0);
    }
  });

  $("#loadView").on("click", function(e) {
    var viewId = $("#view").val();
    e.preventDefault();
    if (viewId === "") {
      return;
    }
    $.ajax({
      url: "{url_for url="/centreon-performance/view"}/" + viewId,
      dataType: "json",
      method: "GET",
      success: function(data, textStatus, jqXHR) {
        if(!isJson(data)){
            alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
            return false;
        }
        charts = [];
        nbGraph = 0;
        $(".graph").each(function(idx, element) {
          $(element).remove();
        });
        $.each(data.graphs, function(idx, graph) {
          createGraph(graph['id'], graph['title']);
        });
      }
    });
  });
  
  /* Bookmark search action */
    $( "#bookmarkView" ).on( "click", function( e ) {
      alertClose();
      var viewId = $("#view").val().trim();
      viewLabel = $("#view").select2('data').text.trim();
      $.ajax({
        url: "{url_for url='/bookmark'}",
        dataType: "json",
        method: "post",
        data: {
          route: "{url_for url="/centreon-performance/graph"}",
          type: "graph",
          label: viewLabel,
          params: viewId
        },
        success: function( data, textStatus, jqXHR ) {
          if(!isJson(data)){
            alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
            return false;
          }
          if ( data.success ) {
            alertMessage( "{t}Your graph is bookmarked.{/t}", "alert-success", 3 );
            $( "#bookmarkStatus" ).removeClass('fa-star-o');
            $( "#bookmarkStatus" ).addClass('fa-star');
          } else {
            alertMessage( data.error, "alert-danger" );
          }
        }
      });
    });
  
    if (localStorage !== undefined) {
      graphSize = localStorage.getItem("graphSize");
    }
    if (graphSize === undefined) {
      graphSize = 1;
    }
    if (graphSize == 2) {
      $("input[name='graphSize']").prop('checked', true);
    }
  
    $("input[name='graphSize']").bootstrapSwitch({
        onText: '<i class="icon-two-bars"></i>',
        offText: '<i class="icon-full-bar"></i>',
        onSwitchChange: function (event, state) {
          var graphSize;
          if (state) {
            graphSize = 2;
          } else {
            graphSize = 1;
          }
          switchGraphSize(graphSize);
          if (localStorage !== undefined) {
            localStorage.setItem("graphSize", graphSize);
          }
        }
      });
});

$( document ).ready(function() {
    graphId = getUriParametersByName('quick-access-graph');
    if (graphId) {
        $.ajax({
            url: "{url_for url="/centreon-performance/view"}/" + graphId,
            dataType: "json",
            method: "GET",
            success: function(data, textStatus, jqXHR) {
              if(!isJson(data)){
                alertMessage( "{t} An Error Occured {/t}", "alert-danger" );
                return false;
              }
              charts = [];
              nbGraph = 0;
              $(".graph").each(function(idx, element) {
                $(element).remove();
              });
              $.each(data.graphs, function(idx, graph) {
                createGraph(graph['id'], graph['title']);
              });
            }
        });
    }
});

</script>
{/block}
