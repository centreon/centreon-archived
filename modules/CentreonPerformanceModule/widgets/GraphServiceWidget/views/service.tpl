{extends file="file:[Core]widgetLayout.tpl"}

{block name="title"}{t}Graphs{/t}{/block}

{block name="content"}
<div class="container" style='padding-top:20px;'>
  <div class="c3" id="graph">
  </div>
</div>
{/block}

{block name="javascript-bottom" append}
<script>
var chart;

function updateChart(serviceId, startTime, endTime) {
  $.ajax({
    url: "{url_for url="/centreon-performance/graph"}",
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
        axis: axis,
        point: {
          show: false
        }
      });
    }
  });
}

$(function() {
  var serviceId = {$serviceId},
      endTime = new Date(),
      startTime = new Date();
  startTime.setHours(endTime.getHours() - 24);
  updateChart(serviceId, Math.floor(startTime.getTime() / 1000), Math.floor(endTime.getTime() / 1000));
});
</script>
{/block}
