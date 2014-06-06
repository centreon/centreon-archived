{extends file="file:[Core]viewLayout.tpl"}

{block name="title"}{t}Graphs{/t}{/block}

{block name="content"}
<div class="container">
  <div class="row search">
    <form role="form">
      <div class="form-group col-md-4">
        <input type="text" name="period" class="form-control" placeholder="Period" >
      </div>
      <div class="form-group col-md-4">
        <input type="text" id="service" name="service" class="form-control" >
      </div>
    </form>
  </div>
  <div class="c3" id="graph">
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
var chart;

function updateChart(serviceId, startTime, endTime) {
  $.ajax({
    url: "{url_for url="/graph"}",
    type: "POST",
    data: {
      service_id: serviceId,
      start_time: startTime,
      end_time: endTime
    },
    dataType: "json",
    success: function( data, textStatus, jqXHR ) {
      var firstMetric = false,
          axes = {},
          axis = {},
          metrics = {},
          colors = [],
          columns = [];

      /* Prepare for c3js */
      columns.push(["x"].concat(data["times"]));
      $.each(data.metrics, function(idx, metric) {
        columns.push([metric.legend].concat(metric["data"]));
        if (metric.color != null) {
          colors[metric.legend] = metric.color;
        }
        if (metric.unit in metrics) {
          metrics[metric.unit].push(metric.legend);
        } else {
          metrics[metric.unit] = [metric.legend];
        }
      });
      /* Check for multi axes */
      if (Object.keys(metrics).length == 2) {
        $.each(metrics, function(unit, legends) {
          if (!firstMetric) {
            firstMetric = true;
            axis["y"] = {};
            axis["y"]["label"] = unit;
          } else {
            axis["y2"] = {};
            axis["y2"]["label"] = unit;
          }
          $.each(legends, function(idx, legend) {
            axes[legend] = unit;
          });
        });
        axis["y2"]["show"] = true;
      } else if (Object.keys(metrics).length == 1) {
        $.each(metrics, function(unit, legends) {
          axis["y"] = {};
          axis["y"]["label"] = unit;
        });
      }
      axis["x"] = {
        type: "timeseries"
      };
      var chart = c3.generate({
        bindto: "#graph",
        data: {
          x: 'x',
          columns: columns,
          axes: axes
        },
        axis: axis
      });
    }
  });
}

$(function() {
  var endTime = moment(),
      startTime = moment(endTime).subtract('hours', 2);
  $("#service").select2({
    placeholder: "Select a service",
    allowClear: true,
    ajax: {
      data: function(term, page) { return { q: term }; },
      dataType: "json",
      url: "/centreon-devel/configuration/service/formlist" ,
      results: function(data) { return { results: data, more: false }; }
    }
  });
  $("input[name='period']").daterangepicker({
    timePicker: true,
    timePickerIncrement: 5,
    timePicker12Hour: false,
    format: 'YYYY-MM-DD HH:mm',
    startDate: startTime,
    endDate: endTime,
    ranges: {
      "8 hours": [moment().subtract('hours', 8), moment()],
      "24 hours": [moment().subtract('hours', 24), moment()],
      "1 week": [moment().subtract('days', 7), moment()],
      "1 month": [moment().subtract('months', 1), moment()]
    }
  });
  $("input[name='period']").val(startTime.format('YYYY-MM-DD HH:mm') + " - " + endTime.format('YYYY-MM-DD HH:mm'));

  $("form").on("change", function() {
    var startTime, endTime,
        time = $("input[name='period']").val();
    startTime = moment(time.split(" - ")[0]).format('X');
    endTime = moment(time.split(" - ")[1]).format('X');
    updateChart($("#service").val(), startTime, endTime);
  });

  $("input[name='period']").on("apply.daterangepicker", function() {
    var startTime, endTime,
        time = $("input[name='period']").val();
    if ($("#service").val() === undefined) {
      return;
    }
    startTime = moment(time.split(" - ")[0]).format('X');
    endTime = moment(time.split(" - ")[1]).format('X');
    updateChart($("#service").val(), startTime, endTime);
  });
});
</script>
{/block}
