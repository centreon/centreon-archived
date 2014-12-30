var charts = [];

function convertColumns(data) {
  var columns = [];
  columns.push(["x"].concat(data["times"]));
  $.each(data.metrics, function(idx, metric) {
    columns.push([metric.legend].concat(metric["data"]));
  });
  return columns;
}

function addChart( graphId, serviceId, startTime, endTime ) {
  $.ajax({
    url: jsUrl.graph,
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
          columns = convertColumns( data );

      /* Prepare for c3js */
      $.each( data.metrics, function( idx, metric ) {
        if ( metric.color != null ) {
          colors[ metric.legend ] = metric.color;
        }
        if ( metric.unit in metrics ) {
          metrics[metric.unit].push(metric.legend);
        } else {
          metrics[ metric.unit ] = [ metric.legend ];
        }
      });
      /* Check for multi axes */
      if ( Object.keys( metrics ).length == 2 ) {
        $.each( metrics, function( unit, legends ) {
          if ( !firstMetric ) {
            firstMetric = true;
            axis[ "y" ] = {};
            axis[ "y" ][ "label" ] = unit;
          } else {
            axis[ "y2" ] = {};
            axis[ "y2" ][ "label" ] = unit;
          }
          $.each( legends, function( idx, legend ) {
            axes[ legend ] = unit;
          });
        });
        axis[ "y2" ][ "show" ] = true;
      } else if ( Object.keys( metrics ).length == 1 ) {
        $.each( metrics, function( unit, legends ) {
          axis[ "y" ] = {};
          axis[ "y" ][ "label" ] = unit;
        });
      }
      axis[ "x" ] = {
        type: "timeseries",
        tick: {
          format: '%Y-%m-%d %H:%M',
          count: 10
        }
      };
      charts.push( c3.generate({
        bindto: "#" + graphId,
        data: {
          x: "x",
          columns: columns,
          axes: axes
        },
        axis: axis,
        point: {
          show: false
        }
      }));
    }
  });
}

function updateChart( startTime, endTime ) {
  $( ".graph" ).each( function( idx, element ) {
    var serviceId = $( element ).data( "serviceId" );
    if ( charts[ idx ] === null ) {
      return;
    }
    $.ajax( {
      url: jsUrl.graph,
      type: "POST",
      data: {
        service_id: serviceId,
        start_time: startTime,
        end_time: endTime
      },
      dataType: "json",
      success: function( data, textStatus, jqXHR ) {
        var columns = convertColumns( data );
        charts[ idx ].load({
          columns: columns
        });
      }
    });
  });
}

function stackCurve( pos, stack ) {
  var groups = {}, finalGroups = [], axes, key, i;
  if ( typeof charts[pos] !== 'undefined' ) {
    if ( stack ) {
      axes = charts[pos].data.axes();
      if ( Object.keys(axes).length > 0 ) {
        for ( key in axes ) {
          if ( axes.hasOwnProperty( key ) ) {
            if ( false === groups.hasOwnProperty( axes[ key ] ) ) {
              groups[ axes[ key ] ] = [];
            }
            groups[ axes[ key ] ].push( key );
          }
        }
      } else {
        groups['y'] = [];
        for ( i = 0; i < charts[ pos ].data().length ; i++ ) {
          groups['y'].push( charts[ pos ].data()[i].id );
        }
      }
      for ( key in groups ) {
        finalGroups.push( groups[ key ] );
      }
      charts[pos].groups( finalGroups );
    } else {
      charts[pos].groups([]);
    }
  }
}
