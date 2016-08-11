/* global jQuery: false */
(function ($) {
  function CentreonGraph(settings, $elem) {
    var self = this;
    var parseInterval;
    var times;
    this.settings = settings;
    this.$elem = $elem;
    this.chart = null;
    this.chartSvg = null;
    this.chartData = null;
    this.refreshEvent = null;
    parseInterval = settings.interval.match(/(\d+)([a-z]+)/i);
    this.interval = {
      number: parseInterval[1],
      unit: parseInterval[2]
    };
    
    if ($elem.attr('id') === undefined) {
      $elem.attr('id', function () {
        function s4() {
          return Math.floor((1 + Math.random()) * 0x10000)
            .toString(16)
            .substring(1);
        }
        return 'c' + s4() + s4() + s4() + s4();
      });
    }
    
    /* Define tick for timeseries */
    this.timeFormat = d3.time.format.multi([
      [".%L", function(d) { return d.getMilliseconds(); }],
      [":%S", function(d) { return d.getSeconds(); }],
      ["%H:%M", function(d) { return d.getMinutes(); }],
      ["%H:%M", function(d) { return d.getHours(); }],
      ["%m-%d", function(d) { return d.getDay() && d.getDate() !== 1; }],
      ["%m-%d", function(d) { return d.getDate() !== 1; }],
      ["%Y-%m", function(d) { return d.getMonth(); }],
      ["%Y", function() { return true; }]
    ]);
    
    /* Color for status graph */
    this.colorScale = d3.scale.ordinal().range([
      '#88b917',
      '#ff9a13',
      '#e00b3d',
      '#bcbdc0'
    ]).domain([
      'ok',
      'warning',
      'critical',
      'unknown'
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
    /**
     * Initialize the graph
     *
     * Call the method by graph type
     *
     * @param {Object} data - The graph data
     */
    initGraph: function (data) {
      this.chartData = data;
      if (this.type === 'status') {
        this.initGraphStatus(data);
      } else {
        this.initGraphMetrics(data);
      }
    },
    /**
     * Initialize the status graph
     *
     * @param {Object} data - The graph data
     */
    initGraphStatus: function (data) {
      var self = this;
      
      this.chart = centreonStatusChart.generate({
        tickFormat: {
          format: this.timeFormat
        },
        bindto: '#' + this.$elem.attr('id'),
        data: this.buildStatusData(data),
        margin: {
          left: 0,
          right: 0,
          top: 0,
          bottom: 0
        }
      });
    },
    /**
     * Initialize the metrics graph
     *
     * @param {Object} data - The graph data
     */
    initGraphMetrics: function (data) {
      var self = this;
      var axis = {
        x: {
          type: 'timeseries',
          tick: {
            format: this.timeFormat
          }
        },
        y: {
          tick: {
            format: this.roundTick
          }
        }
      };
      var parsedData = this.buildMetricData(data);
      axis = jQuery.extend({}, axis, parsedData.axis);
      if (axis.hasOwnProperty('y2')) {
        axis.y2.tick = {
          format: this.roundTick
        };
      }
      
      this.chart = c3.generate({
        bindto: '#' + this.$elem.attr('id'),
        size: {
          height: this.settings.height
        },
        data: parsedData.data,
        axis: axis,
        tooltip: {
          format: {
            title: function (x) {
              return moment(x).format('YYYY-MM-DD HH:mm:ss');
            },
            value: function (value, ratio, id) {
              /* Test if the curse is inversed */
              if (self.isInversed(id)) {
                return self.inverseRoundTick(value);
              }
              return self.roundTick(value);
            }
          }
        },
        zoom_select: self.settings.zoom,
        point: {
          show: false
        },
        regions: self.buildRegions(data)
      });
    },
    /**
     * Load data from rest api in ajax
     *
     * @param {Number} start - The start time in unixtimestamp
     * @param {Number} end - The end time in unixtimestamp
     * @param {Function} [callback] - The callback when receive the datas
     */
    loadData: function (start, end, callback) {
      var self = this;
      var action = {
        status: 'statusByService',
        service: 'metricsDataByService'
      };
      var url = './api/internal.php?object=centreon_metric';
      url += '&action=' + action[this.type] ;
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
          if (self.type === 'status') {
            self.chart.load(
              self.buildStatusData(data[0])
            );
          } else {
            self.chart.load(
              self.buildMetricData(data[0]).data
            );
            self.chart.regions(self.buildRegions(data[0]));
          }
        }
      });
    },
    /**
     * Build data for metrics graph
     *
     * @param {Object} dataRaw - The raw data
     * @return {Object} - The converted data 
     */
    buildMetricData: function (dataRaw) {
      var convertType = {
        line: 'spline',
        area: 'area-spline'
      };
      var i = 0;
      var data = {
        columns: [],
        names: {},
        types: {},
        colors: {},
        regions: {}
      };
      var units = {};
      var axis = {};
      var column;
      var name;
      var legend;
      var axesName;
      var unit;
      var times = dataRaw.times;
      var thresholdData;
      var nbPoints;
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
        data.types[name] = convertType.hasOwnProperty(dataRaw.data[i].type) !== -1 ?
          convertType[dataRaw.data[i].type] : dataRaw.data[i].type;
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
      
      /* Prepare threshold */
      if (this.settings.threshold && dataRaw.data.length === 1) {
        nbPoints = dataRaw.data[0].data.length;
        if (dataRaw.data[0].crit) {
          data.colors.crit = '#e00b3d';
          data.types.crit = 'line';
          data.names.crit = 'Threshold critical';
          thresholdData = Array.apply(null, Array(nbPoints))
            .map(function () {
              return dataRaw.data[0].crit;
            });
          thresholdData.unshift('crit');
          data.columns.push(thresholdData);
          data.regions.crit = [{style: 'dashed'}];
        }
        if (dataRaw.data[0].warn) {
          data.colors.warn = '#ff9a13';
          data.types.warn = 'line';
          data.names.warn = 'Threshold warning';
          thresholdData = Array.apply(null, Array(nbPoints))
            .map(function () {
              return dataRaw.data[0].warn;
            });
          thresholdData.unshift('warn');
          data.columns.push(thresholdData);
          data.regions.warn = [{style: 'dashed'}];
        }
      }
      
      /* Add group */
      data.groups = this.buildGroups(dataRaw);
      
      return {
        data: data,
        axis: axis
      };
    },
    /**
     * Build data for status graph
     *
     * @param {Object} dataRaw - The raw data
     * @return {Object} - The converted data 
     */
    buildStatusData: function (dataRaw) {
      var status;
      var data = {};
      var dataStatus = [];
      var statusColor = {
        ok: '#88b917',
        warning: '#ff9a13',
        critical: '#e00b3d',
        unknown: '#bcbdc0'
      };
      
      for (status in dataRaw.data.status) {
        if (dataRaw.data.status.hasOwnProperty(status)) {
          if (dataRaw.data.status[status].length > 0) {
            dataStatus.push({
              label: status,
              color: statusColor[status],
              times: dataRaw.data.status[status].map(function (values) {
                return {
                  starting_time: values['start'] * 1000,
                  ending_time: values['end'] * 1000
                };
              })
            });
          }
        }
      }
      
      data = {
        status: dataStatus,
        comments: dataRaw.data.comments.map(function (values) {
          values['time'] = values['time'] * 1000;
          return values;
        })
      };
      
      return data;
    },
    /**
     * Build regions
     *
     * @param {Object} data - The chart datas
     * @return {Array} - The list of regions
     */
    buildRegions: function (data) {
      var regions = [];
      var i;
      for (i = 0; i < data.acknowledge.length; i++) {
        regions.push({
          start: data.acknowledge['start'] * 1000,
          end: data.acknowledge['end'] * 1000,
          class: 'region-ack'
        });
      }
      for (i = 0; i < data.downtime.length; i++) {
        regions.push({
          start: data.downtime[i]['start'] * 1000,
          end: data.downtime[i]['end'] * 1000,
          class: 'region-downtime'
        });
      }

      return regions;
    },
    /**
     * Build regions
     *
     * @param {Object} data - The chart datas
     * @return {Array} - The list of regions
     */
    buildGroups: function (data) {
      var group = [];
      var i;
      var name;
      
      for (i = 0; i < data.data.length; i++) {
        name = 'data' + (i + 1);
        if (data.data[i].stack) {
          group.push(name);
        }
      }
      
      return [group];
    },
    /**
     * Refresh data of graph
     */
    refreshData: function () {
      var times = this.getTimes();
      this.loadData(times.start, times.end);
    },
    /**
     * Get time start and end in unixtimestamp
     *
     * @return {Object} - The object with date start and end
     */
    getTimes: function () {
      var start;
      var end;
      if (this.settings.period.start === null ||
        this.settings.period.end === null) {
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
    /**
     * Resize the graph
     */
    resize: function () {
      this.chart.resize({
        width: this.$elem.width(),
        height: null
      });
    },
    /**
     * Set an interval string for graph
     *
     * Format : see momentjs
     * 
     * @param {String} interval - A interval string
     */
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
    /**
     * Set a period with start and end time
     *
     * @param {String} start - The start time
     * @param {String} end - The end time
     */
    setPeriod: function (start, end) {
      this.settings.period = {
        start: start,
        end: end
      };
      this.refreshData();
    },
    /**
     * Set auto refresh interval
     *
     * @param {Number} interval - The number of seconds to refresh,
     *                            0 stop the auto refresh
     */
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
    },
    /**
     * Round the value of a point and transform to humanreadable
     *
     * @param {Float} value - The value to transform
     * @return {Float} - The value transformed
     */
    roundTick: function (value) {
      return d3.format('.3s')(value);
    },
    /**
     * Round the value of a point and transform to humanreadable
     * and inverse the value if the curve is inversed
     *
     * @param {Float} value - The value to transform
     * @return {Float} - The value transformed
     */
    inverseRoundTick: function (value) {
      return this.roundTick(value * -1);
    },
    /**
     * Return is the curve is inversed / negative
     *
     * @param {String} id - The curve id
     * @return {Boolean} - If the curve is inversed
     */
    isInversed: function (id) {
      var pos = parseInt(id.replace('data', ''), 10) - 1;
      if (id === 'crit' || id === 'warn') {
        return false;
      }
      return this.chartData.data[pos].negative;
    }
  };
  
  $.fn.centreonGraph = function (options) {
    var args = Array.prototype.slice.call(arguments, 1);
    var settings = jQuery.extend({}, $.fn.centreonGraph.defaults, options);
    var methodReturn;
    var $set = this.each(function () {
      var $this = jQuery(this);
      var data = $this.data("centreonGraph");

      if (!data) {
        $this.data(
          "centreonGraph",
          (data = new CentreonGraph(settings, $this))
        );
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
    zoom: {
      enabled: false,
      onzoom: null 
    },
    graph: {
      id: null,
      type: null
    },
    interval: '3h',
    period: {
      start: null,
      end: null
    },
    threshold: true
  };
})(jQuery);