/* global jQuery: false */
(function ($) {
  function CentreonGraph(settings, $elem) {
    var self = this;
    var parseInterval;
    var times;
    this.settings = settings;
    this.$elem = $elem;
    this.chart = null;
    this.refreshEvent = null;
    parseInterval = settings.interval.match(/(\d+)([a-z]+)/i);
    this.interval = {
      number: parseInterval[1],
      unit: parseInterval[2]
    };
    
    /* Define tick for timeseries */
    this.timeFormat = d3.time.format.multi([
      [".%L", function(d) { return d.getMilliseconds(); }],
      [":%S", function(d) { return d.getSeconds(); }],
      ["%H:%M", function(d) { return d.getMinutes(); }],
      ["%H", function(d) { return d.getHours(); }],
      ["%m-%d", function(d) { return d.getDay() && d.getDate() !== 1; }],
      ["%m-%d", function(d) { return d.getDate() !== 1; }],
      ["%Y-%m", function(d) { return d.getMonth(); }],
      ["%Y", function() { return true; }]
    ]);
    
    this.loadGraphId();
    
    /* Get start time and end time */
    times = this.getTimes();
    
    this.loadData(times.start, times.end, function (data) {
      self.initGraph(data);
    });
    
    this.setRefresh(this.settings.refresh);
  }

  CentreonGraph.prototype = {
    /**
     * Load graph type and graph id
     *
     * Use in first attribue data-graphType and data-graphId and next
     * the settings graph.type and graph.id
     *
     * Type :
     *   * service
     *   * metaservice
     */
    loadGraphId: function () {
      this.type = this.$elem.data('graphType');
      this.id = this.$elem.data('graphId');
      if (this.type === null || this.type === undefined) {
        this.type = this.settings.graph.type;
      }
      if (this.id === null  || this.type === undefined) {
        this.id = this.settings.graph.id;
      }
      
      if (this.type === null || this.id === null) {
        throw new Error('The graph configuration is missing.');
      }
    },
    initGraph: function (data) {
      var axis = {
        x: {
          type: 'timeseries',
          tick: {
            format: this.timeFormat
          }
        }
      };
      var parsedData = this.buildData(data);
      this.chart = c3.generate({
        bindto: '#' + this.$elem.attr('id'),
        height: this.settings.height,
        data: parsedData.data,
        axis: jQuery.extend({}, axis, parsedData.axis),
        tooltip: {
          format: {
            title: function (x) {
              return moment(x).format('YYYY-MM-DD HH:mm:ss');
            },
            value: function (value) {
              var floatValue = value.toFixed(3);
              if (floatValue == value) {
                return value;
              }
              return value.toFixed(3);
            }
          }
        },
        point: {
          show: false
        }
      });
    },
    loadData: function (start, end, callback) {
      var self = this;
      var url = './api/internal.php?object=centreon_metric&action=metricsDataByService';
      url += '&ids=' + this.id;
      url += '&start=' + start + '&end=' + end;
      $.ajax({
        url: url,
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          if (typeof callback === 'function') {
            return callback(data[0]);
          }
          self.chart.load(
            self.buildData(data[0]).data
          );
        }
      });
    },
    buildData: function (dataRaw) {
      var convertType = {
        line: 'spline',
        area: 'area-spline'
      };
      var i = 0;
      var data = {
        columns: [],
        names: {},
        types: {},
        colors: {}
      };
      var units = {};
      var axis = {};
      var column;
      var name;
      var legend;
      var axesName;
      var unit;
      var times = dataRaw.times;
      times = times.map(function (time) {
        return time * 1000;
      });
      times.unshift('times');
      data.columns.push(times);
      for (i = 0; i < dataRaw.data.length; i++) {
        name = 'data' + (i + 1);
        column = dataRaw.data[i].data;
        column.unshift(name);
        data.columns.push(column);
        legend = dataRaw.data[i].label;
        if (dataRaw.data[i].unit) {
          legend += '(' + dataRaw.data[i].unit + ')';
          if (units.hasOwnProperty(dataRaw.data[i].unit) === false) {
            units[dataRaw.data[i].unit] = name;
          }
        }
        data.names[name] = legend;
        data.types[name] = convertType.hasOwnProperty(dataRaw.data[i].type) !== -1 ? convertType[dataRaw.data[i].type] : dataRaw.data[i].type;
        data.colors[name] = dataRaw.data[i].color;
      }
      
      if (Object.keys(units).length === 2) {
        axesName = 'y';
        for (unit in units) {
          if (units.hasOwnProperty(unit)) {
            for (i = 0; i < units[unit][i]; i++) {
              data.axes[units[unit][i]] = axesName;
            }
          }
          axis[axesName] = {
            label: unit
          };
          axesName = 'y2';
        }
        axis.y2.show = true;
      }
      
      data.x = 'times';
      return {
        data: data,
        axis: axis
      };
    },
    refreshData: function () {
      var times = this.getTimes();
      this.loadData(times.start, times.end);
    },
    getTimes: function () {
      var start;
      var end;
      if (this.settings.period.start === null || this.settings.period.end === null) {
        start = moment();
        end = moment();
        start.subtract(this.interval.number, this.interval.unit);
      } else {
        start = moment(this.settings.period.start);
        end = moment(this.settings.period.end);
      }
        
      return {
        start: start.unix(),
        end: end.unix()
      }
    },
    resize: function () {
      this.chart.resize({
        width: this.$elem.width(),
        height: null
      });
    },
    setInterval: function (interval) {
      var parseInterval = interval.match(/(\d+)([a-z]+)/i);
      this.settings.period = {
        start: null,
        end: null
      };
      this.interval = {
        number: parseInterval[1],
        unit: parseInterval[2]
      };
      this.refreshData();
    },
    setPeriod: function (start, end) {
      this.settings.period = {
        start: start,
        end: end
      };
      this.refreshData();
    },
    setRefresh: function (interval) {
      var self = this;
      this.refresh = interval;
      
      if (this.refresh > 0) {
        this.refreshEvent = setInterval(function () {
          self.refreshData();
        }, self.refresh * 1000);
      } else if (this.refreshEvent !== null) {
        clearInterval(this.refreshEvent);
        this.refreshEvent = null;
      }
    }
  };
  
  $.fn.centreonGraph = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = $.extend({}, $.fn.centreonGraph.defaults, options);
    var methodReturn;
    var $set = this.each(function () {
      var $this = $(this);
      var data = $this.data("centreonGraph");

      if (!data) {
        $this.data("centreonGraph", ( data = new CentreonGraph(settings, $this)));
      }

      if (typeof options === "string") {
        methodReturn = data[options].apply(data, args);
      }

      return (methodReturn === undefined) ? $set : methodReturn;
    });
  };
  
  $.fn.centreonGraph.defaults = {
    refresh: 0,
    height: 230,
    graph: {
      id: null,
      type: null
    },
    interval: '3h',
    period: {
      start: null,
      end: null
    }
  };
})(jQuery);