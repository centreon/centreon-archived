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
    this.forceDisplay = false;
    parseInterval = settings.interval.match(/(\d+)([a-z]+)/i);
    this.interval = {
      number: parseInterval[1],
      unit: parseInterval[2]
    };
    this.ids = {};
    this.toggleAction = 'hide';

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

    // get timezone from localstorage
    // be careful, it seems that it's updated when user logout/login
    this.timezone = localStorage.getItem('realTimezone')
      ? localStorage.getItem('realTimezone')
      : moment.tz.guess();

    this.timeFormat = this.getTimeFormat();

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

    /* Prepare extra legends */
    this.legendDiv = jQuery('<div>').addClass('chart-legends').attr('id', 'chart-legends-' + this.id);
    this.$elem.after(this.legendDiv);

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
      var start = this.$elem.data('graphPeriodStart');
      var end = this.$elem.data('graphPeriodEnd');
      var interval = this.$elem.data('graphInterval');
      this.type = this.$elem.data('graphType');
      this.id = this.$elem.data('graphId');
      if (this.type === null || this.type === undefined) {
        this.type = this.settings.graph.type;
      }
      if (this.id === null  || this.type === undefined) {
        this.id = this.settings.graph.id;
      }
      if (start !== null && start !== undefined) {
        this.settings.period.startTime = start;
      }
      if (end !== null && end !== undefined ) {
        this.settings.period.startTime = end;
      }
      if (interval !== null && interval !== undefined) {
        this.setInterval(interval, false);
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
          padding: {left: 0, right: 0},
          type: 'timeseries',
          tick: {
            fit: false,
            format: this.timeFormat
          }
        },
        y: {
          padding: {bottom: 0, top: 0}
        }
      };
      /* Add Y axis range */
      if ('lower-limit' in data['global']) {
        axis.y.min = numeral(data['global']['lower-limit']).value();
      }
      if ('upper-limit' in data['global']) {
        axis.y.max = numeral(data['global']['upper-limit']).value();
      }

      var parsedData = this.buildMetricData(data);
      axis = jQuery.extend(true, {}, axis, parsedData.axis);
      // Of course no-unit series are 1000 based
      axis.y.tick = {
        format: this.getAxisTickFormat(axis.y.label ? this.getBase() : 1000)
      };
      if (axis.hasOwnProperty('y2')) {
        axis.y2.tick = {
          format: this.getAxisTickFormat(axis.y2.label ? this.getBase() : 1000)
        };
      }

      if (data.metrics.length > 15) {
          datasToAppend = {
            x: parsedData.data.x,
            columns: [],
            names: {},
            types: {},
            colors: {},
            regions: {},
            order: null,
            empty: { label: { text: "Too many metrics, the chart can't be displayed" } }
          }
      } else {
          datasToAppend = parsedData.data;
      }

      this.chart = c3.generate({
        bindto: '#' + this.$elem.attr('id'),
        size: {
          height: this.settings.height
        },
        //padding: this.settings.padding,
        data: datasToAppend,
        axis: axis,
        tooltip: {
          format: {
            title: function (x) {
              return moment(x).tz(self.timezone).format('YYYY-MM-DD HH:mm:ss');
            },
            value: function (value, ratio, id) {
              var fct = self.getAxisTickFormat(self.getBase());
              return fct(value);
            }
          }
        },
        zoom_select: self.settings.zoom,
        point: {
          show: true,
          r: 0,
          focus: {
            expand: {
              r: 4
            }
          }
        },
        regions: self.buildRegions(data),
        legend: {
          show: false
        }
      });

      if (data.metrics.length > 15) {
          jQuery("#display-graph-" + self.id).css('display', 'block');
          jQuery("#display-graph-" + self.id).on('click', function (e){
              self.chart.load(parsedData.data)
              self.chart.regions(self.buildRegions(data));
              jQuery(this).css('display', 'none');
          });
      }

      this.buildLegend(data.metrics);
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
        service: 'metricsDataByService',
        metric: 'metricsDataByMetric',
        poller : 'metricsDataByPoller'
      };
      var url = self.settings.url;
      url += '&action=' + action[this.type];
      url += '&ids=' + this.id;
      url += '&start=' + start + '&end=' + end;
      url += '&type=ng';
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
              self.chart.load(self.buildMetricData(data[0]).data);
              self.chart.regions(self.buildRegions(data[0]));
              self.buildExtraLegend(data[0].metrics);
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
      var i = 0;
      var data = {
        columns: [],
        names: {},
        types: {},
        colors: {},
        regions: {},
        order: null,
        empty: { label: { text: "There's no data" } }
      };

      var units = {};
      var axis = {};
      var column;
      var name;
      var legend;
      var axesName = 'y';
      var unit;
      var times = dataRaw.times;
      var thresholdData;
      var nbPoints;
      times = times.map(function (time) {
        return time * 1000;
      });
      times.unshift('times');

      data.columns.push(times);
      for (i = 0; i < dataRaw.metrics.length; i++) {
        name = 'data' + (i + 1);
        this.ids[dataRaw.metrics[i].legend] = name;
        column = dataRaw.metrics[i].data;
        column.unshift(name);
        data.columns.push(column);
        legend = dataRaw.metrics[i].legend;
        if (dataRaw.metrics[i].unit) {
          axis[axesName] = {
            label: dataRaw.metrics[i].unit
          };
        }
        // these no-unit series also go to their own axis
        if (units.hasOwnProperty(dataRaw.metrics[i].unit) === false) {
          units[dataRaw.metrics[i].unit] = [];
        }
        units[dataRaw.metrics[i].unit].push(name);
        data.names[name] = legend;
        data.types[name] = dataRaw.metrics[i].ds_data.ds_filled == 1 ? 'area' : 'line';
        data.colors[name] = dataRaw.metrics[i].ds_data.ds_color_line;
      }

      if (Object.keys(units).length === 2) {
        data.axes = {};
        for (unit in units) {
          if (units.hasOwnProperty(unit)) {
            for (i = 0; i < units[unit].length; i++) {
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
      if (this.settings.threshold && dataRaw.metrics.length === 1) {
        nbPoints = dataRaw.metrics[0].data.length;
        if (dataRaw.metrics[0].warn) {
          data.colors.warn = '#ff9a13';
          data.types.warn = 'line';
          data.names.warn = 'Warning';
          thresholdData = Array.apply(null, Array(nbPoints))
              .map(function () {
                return dataRaw.metrics[0].warn;
              });
          thresholdData.unshift('warn');
          data.columns.push(thresholdData);
          data.regions.warn = [{style: 'dashed'}];
        }
        if (dataRaw.metrics[0].crit) {
          data.colors.crit = '#e00b3d';
          data.types.crit = 'line';
          data.names.crit = 'Critical';
          thresholdData = Array.apply(null, Array(nbPoints))
            .map(function () {
              return dataRaw.metrics[0].crit;
            });
          thresholdData.unshift('crit');
          data.columns.push(thresholdData);
          data.regions.crit = [{style: 'dashed'}];
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
      if ('acknowledge' in data) {
        for (i = 0; i < data.acknowledge.length; i++) {
          regions.push({
            start: data.acknowledge['start'] * 1000,
            end: data.acknowledge['end'] * 1000,
            class: 'region-ack'
          });
        }
      }
      if ('downtime' in data) {
        for (i = 0; i < data.downtime.length; i++) {
          regions.push({
            start: data.downtime[i]['start'] * 1000,
            end: data.downtime[i]['end'] * 1000,
            class: 'region-downtime'
          });
        }
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

      for (i = 0; i < data.metrics.length; i++) {
        name = 'data' + (i + 1);
        if (data.metrics[i].stack) {
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

      if (this.settings.period.startTime === null ||
        this.settings.period.endTime === null) {

        start = moment().tz(this.timezone);
        end = moment().tz(this.timezone);

        start.subtract(this.interval.number, this.interval.unit);

      } else {
        myStart = this.settings.period.startTime;
        myEnd = this.settings.period.endTime;

        if (typeof(this.settings.period.startTime) === "number") {
          myStart = this.settings.period.startTime * 1000;
        }

        if (typeof(this.settings.period.endTime) === "number") {
          myEnd = this.settings.period.endTime * 1000;
        }

        start = moment.tz(myStart, this.timezone);
        end = moment.tz(myEnd, this.timezone);
      }

      return {
        start: start.unix(),
        end: end.unix()
      }
    },
    /**
     * Define tick for timeseries
     */
    getTimeFormat: function() {
      var self = this;
      var timeFormat;

      if (this.settings.timeFormat !== null) {
        timeFormat = this.settings.timeFormat;
      } else {
        timeFormat = function(date) {
          // convert to moment object to manage timezone
          date = moment(date).tz(self.timezone);

          if (date.millisecond()) {
            return date.format(".SSS");
          }
          if (date.second()) {
            return date.format(":ss");
          }
          if (date.minute()) {
            return date.format("HH:mm");
          }
          if (date.hour()) {
            return date.format("HH:mm");
          }
          if (date.day() && date.date() !== 1) {
            return date.format("MM-DD");
          }
          if (date.date() !== 1) {
            return date.format("MM-DD");
          }
          if (date.month()) {
            return date.format("YYYY-MM");
          }
          return date.format("YYYY");
        }
      }

      return timeFormat;
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
    setInterval: function (interval, refresh) {
      refresh = (refresh !== undefined) ? refresh : true
      var parseInterval = interval.match(/(\d+)([a-z]+)/i);
      this.settings.period = {
        startTime: null,
        endTime: null
      };
      this.interval = {
        number: parseInterval[1],
        unit: parseInterval[2]
      };
      if (refresh) {
        this.refreshData();
      }
    },
    /**
     * Set a period with start and end time
     *
     * @param {String} start - The start time
     * @param {String} end - The end time
     */
    setPeriod: function (start, end) {
      this.settings.period = {
        startTime: start,
        endTime: end
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

      if (this.refreshEvent !== null) {
        clearInterval(this.refreshEvent);
        this.refreshEvent = null;
      }

      if (this.refresh > 0) {
        this.refreshEvent = setInterval(function () {
          self.refreshData();
        }, self.refresh * 1000);
      }
    },
    /**
     * Get function for humanreadable tick
     *
     * @param {Integer} base - The value to transform
     * @return {Function} - The function for round the axes tick
     */
    getAxisTickFormat: function (base) {
      if (base === 1024 || base === '1024') {
        return this.roundTickByte;
      }
      return this.roundTick;
    },
    /**
     * Round the value of a point and transform to humanreadable
     *
     * @param {Float} value - The value to transform
     * @return {String} - The value transformed
     */
    roundTick: function (value) {
      if (value < 0) {
        return '-' + numeral(Math.abs(value)).format('0.0[0]0b').replace(/B/, '');
      }
      return numeral(value).format('0.0[0]0b').replace(/B/, '');
    },
    /**
     * Round the value of a point and transform to humanreadable for bytes
     *
     * @param {Float} value - The value to transform
     * @return {String} - The value transformed
     */
    roundTickByte: function (value) {
      if (value < 0) {
          return '-' + numeral(Math.abs(value)).format('0.0[0]0ib').replace(/i?B/, '');
      }
      return numeral(value).format('0.0[0]0ib').replace(/i?B/, '');
    },
    /**
     * Get base for 1000 or 1024 for a curve
     *
     * @param {String} id - The curve id
     * @return {Integer} - 1000 or 1024
     */
    getBase: function () {
      if (this.chartData.global.base) {
        return this.chartData.global.base;
      }
      return 1000;
    },
    /**
     * Build for display the legends
     *
     * @param {String[]} legends - The list of legends to display
     */
    buildLegend: function (legends) {
      var self = this;
      var legend;
      var legendDiv;
      var legendInfo;
      var legendLabel;
      var legendExtra;
      var curveId;
      var i;
      var j;
      for (i = 0; i < legends.length; i++) {
        legend = legends[i];
        curveId = self.ids[legend.legend];
        var fct = self.getAxisTickFormat(self.getBase());
        legendDiv = jQuery('<div>').addClass('chart-legend')
            .data('curveid', curveId)
            .data('legend', i);
        let legendText = legend.legend;
        if (legend.unit) {
            legendText += ' (' + legend.unit + ')';
        }

        /* Build legend for a curve */
        legendLabel = jQuery('<div>')
            .append(
              /* Color */
              jQuery('<div>')
                .addClass('chart-legend-color')
                .css({
                  'background-color': legend.ds_data.ds_color_line
                })
            )
            .append(
              jQuery('<span>').text(legendText)
            );
        legendLabel.appendTo(legendDiv);

        /* Build legend extra */
        for (j = 0; j < legend.prints.length; j++) {
          legendExtra = jQuery('<div>').addClass('extra')
              .append(
                jQuery('<span>')
                  .text(legend.prints[j])
              )
          legendExtra.appendTo(legendDiv);
        }

        legendDiv
            .on('mouseover', 'div', function (e) {
              var curveId = jQuery(e.currentTarget).parent().data('curveid');
              self.chart.focus(curveId);
            })
            .on('mouseout', 'div', function () { self.chart.revert(); })
            .on('click', function (e) {
              var curveId = jQuery(e.currentTarget).data('curveid');
              jQuery(e.currentTarget).toggleClass('hidden');
              self.chart.toggle(curveId);
            });

        legendDiv.appendTo(this.legendDiv);
      }
      /* Append actions button */
      actionDiv = jQuery('<div>').addClass('chart-legend-action');
      if (this.settings.buttonToggleCurves) {
        toggleCurves = jQuery('<img>').attr('src', './img/icons/rub.png')
          .on('click', function () {
            if (self.toggleAction === 'hide') {
              self.toggleAction = 'show';
              self.legendDiv.find('.chart-legend').addClass('hidden');
              self.chart.hide();
            } else {
              self.toggleAction = 'hide';
              self.legendDiv.find('.chart-legend').removeClass('hidden');
              self.chart.show();
            }
          }).appendTo(actionDiv);
      }
        if (self.settings.extraLegend) {
            expandLegend = jQuery('<img>').attr('src', './img/icons/info2.png')
                .on('click', function () {
                    self.legendDiv.toggleClass('extend');
                }).appendTo(actionDiv);
        }
      actionDiv.appendTo(self.legendDiv);
    },
    /**
     * Build for display the extra legends
     *
     * @param {String[]} legends - The list of legends to display
     */
    buildExtraLegend: function (legends) {
      var self = this;
      var i;
      const orderedLegends = legends.reduce((acc, currentValue) => {
        acc[currentValue.legend] = currentValue;
        return acc;
      }, {});

      jQuery('#chart-legends-' + self.id + ' .chart-legend').each(function (idx, el) {
        const legendName = jQuery(el).find('div:first-child').text();

        if (!orderedLegends.hasOwnProperty(legendName)) {
          return true;
        }

        const legendData = orderedLegends[legendName];
        var fct = self.getAxisTickFormat(self.getBase());
        jQuery(el).find('.extra').remove();

        legendData.prints.forEach((printValue) => {
          legendExtra = jQuery('<div>')
            .addClass('extra')
            .append(
              jQuery('<span>')
                .text(printValue)
            );
          legendExtra.appendTo(el);
        });
      });
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
    });
    return (methodReturn === undefined) ? $set : methodReturn;
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
      startTime: null,
      endTime: null
    },
    timeFormat: null,
    threshold: true,
    extraLegend: true,
    buttonToggleCurves: true,
    url: './api/internal.php?object=centreon_metric'
  };
})(jQuery);
