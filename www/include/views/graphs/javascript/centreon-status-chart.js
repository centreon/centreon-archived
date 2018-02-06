/**
 * Generate a status chart
 * 
 * The arguments
 *
 * * [bindto]{String} (#chart): The DOM element where the chart will be append
 * * [tickFormat]{Object}: The object for configure tick format
 *   * [format]{Function} (d3.time.format("%I %p")): The display format for the tick
 * * [colorCycle]{Array} (d3.scale.category20()): The list of default colors
 * * [margin]{Object}: The margin around the chart
 *   * [left]{Number} (30)
 *   * [right]{Number} (30)
 *   * [top]{Number} (30)
 *   * [bottom]{Number} (30)
 * * [itemHeight]{Number} (20): The height of the bar
 * * data{Object}: The data for the chart
 *   * status{Array[Object]}: The list of status
 *     * label{String}: The label of the status
 *     * [color]{String}: The color for the status
 *     * times{Array[Object]}: The list of periods
 *       * starting_time{Number}: The start of the period in timestamp (milliseconds)
 *       * ending_time{Number}: The end of the period in timestamp (milliseconds)
 *   * [comments]{Array[Object]}: The list of commment
 *     * time{Number}: The time of the comment in timestamp (milliseconds)
 *     * comment{String}: The comment string
 *     * author{String}:  The author of the comment
 */

(function (window) {
  var centreonStatusChart = { version: '1.0.0' };
  
  var centreonStatusChartDefault = {
    bindto: '#chart',
    tickFormat: {
      format: window.d3.time.format('%I %p')
    },
    colorCycle: window.d3.scale.category20(),
    margin: {
      left: 30,
      right:30,
      top: 30,
      bottom: 30
    },
    itemHeight: 20,
    tooltipDisplayTime: window.d3.time.format('%Y-%m-%d %H:%M:%S'),
    tooltipAttach: 'bottom',
    data: {}
  };
  
  /**
   * Extend function like jQuery
   */
  function extend() {
    var i;
    var key;
    for (i = 1; i < arguments.length; i++) {
      for (key in arguments[i]) {
        if (arguments[i].hasOwnProperty(key)) {
          arguments[0][key] = arguments[i][key];
        }
      }
    }
    
    return arguments[0];
  }
  
  /**
   * Private constructor
   * 
   * @param {Chart} api - Chart public object
   */
  function ChartInternal(api) {
    var $$ = this;
    $$.d3 = window.d3 ? window.d3 : undefined;
    $$.api = api;
  }
  
  /**
   * Constructor
   * 
   * @param {Object} config - Configuration
   */
  function Chart(config) {
    var $$;
    this.internal = new ChartInternal(this);
    $$ = this.internal;
    $$.loadConfig(config);
    $$.init();
  }
  
  ChartInternal.prototype = {
    /**
     * Load the configuration
     *
     * @param {Object} config - Configuration
     */
    loadConfig: function (config) {
      var $$ = this;
      $$.config = extend({}, centreonStatusChartDefault, config);
    },
    /**
     * Initialize the chart
     */
    init: function () {
      var $$ = this;
      
      /* Append SVG */
      $$.chartContainer = $$.d3.select($$.config.bindto);
      
      if ($$.chartContainer.size() === 0) {
        console.error('Chart container not found.');
        return;
      }
      
      $$.chartContainer
        .style('position', 'relative')
        .classed('c3', true);
      
      $$.initTooltip();
      
      $$.svg = $$.chartContainer.append('svg')
        .style('overflow', 'hidden');
        
      /* Update width */
      $$.setWidth();
      $$.svg
        .style('height', ($$.config.margin.top + $$.config.margin.bottom + $$.config.itemHeight + 20) + 'px')
        .style('width', $$.width + 'px');
      
      $$.setPeriod();
      $$.setScaleFactor();
      
      $$.chartContainer.datum($$.config.data)
        .call(function () { return $$.update(); });
      
      /* Bind resize */
      $$.resizeTimeout = undefined;
      if (window.attachEvent) {
        window.attachEvent(
          'onresize',
          function () {
            $$.onResize();
          }
        );
      } else if (window.addEventListener) {
        window.addEventListener(
          'resize',
          function () {
            $$.onResize();
          },
          false
        );
      }
    },
    initTooltip: function () {
      var $$ = this;
      
      $$.tooltip = $$.chartContainer.style('postion', 'relative')
        .append('div')
        .classed('cc3-tooltip', true)
        .style('position', 'absolute')
        .style('display', 'none');
        
      $$.tooltip
        .append('div')
        .classed('cc3-tooltip-title', true);
        
      $$.tooltip
        .append('div')
        .classed('cc3-tooltip-body', true)
        .append('pre');
    },
    /**
     * Update the chart
     */
    update: function () {
      var $$ = this;
      
      $$.chart = $$.svg.append('g');
      
      /* Prepare xAxis */
      $$.xScale = $$.d3.time.scale()
        .domain([$$.startTime, $$.endTime])
        .range([$$.config.margin.left, $$.width - $$.config.margin.right]);
        
      $$.xAxis = $$.d3.svg.axis()
        .scale($$.xScale)
        .tickFormat($$.config.tickFormat.format);
      
      /* Draw the chart */
      $$.drawData();
      
      /* Add axis to chart */
      $$.gAxis = $$.chart.append('g')
        .attr('class', 'c3-axis c3-axis-x')
        .attr('transform', 'translate(0, ' + $$.getXAxisPosition() + ')')
        .call($$.xAxis);
    },
    /**
     * Prepare the width for the element
     */
    setWidth: function () {
      var $$ = this;
      var widthParent;
      
      try {
        widthParent = $$.chartContainer.node().getBoundingClientRect()['width'];
      } catch (e) {
        widthParent = $$.chartContainer.node().offsetWidth;
      }
      
      $$.width = widthParent;
    },
    /**
     * Prepare period for chart
     */
    setPeriod: function () {
      var $$ = this;
      var data = $$.config.data.status;
      var i;
      var j;
      var times;
      $$.startTime = null;
      $$.endTime = null;
      
      for (i = 0; i < data.length; i++) {
        times = [];
        if (data[i].hasOwnProperty('times')) {
          times = data[i].times;
        }
        
        for (j = 0; j < times.length; j++) {
          if (times[j].hasOwnProperty('starting_time') &&
            ($$.startTime === null || $$.startTime > times[j].starting_time)) {
            $$.startTime = times[j].starting_time;
          }
          if (times[j].hasOwnProperty('ending_time') &&
            ($$.endTime === null || $$.endTime < times[j].ending_time)) {
            $$.endTime = times[j].ending_time;
          }
        }
      }
      
      if ($$.startTime === null) {
        $$.startTime = 0;
      }
      if ($$.endTime === null) {
        $$.endTime = 0;
      }
    },
    /**
     * Define the scale factor
     */
    setScaleFactor: function () {
      var $$ = this;
      
      $$.scaleFactor = (1 / ($$.endTime - $$.startTime)) *
        ($$.width - $$.config.margin.left - $$.config.margin.right);
    },
    /**
     * Get the start position of the x axis
     */
    getXAxisPosition: function () {
      var $$ = this;
      
      return $$.config.margin.top + $$.config.itemHeight;
    },
    /**
     * Draw the periods
     */
    drawData: function () {
      var $$ = this;
      var status = $$.config.data.status;
      var comments = [];
      var i;
      
      if ($$.config.data.hasOwnProperty('comments')) {
        comments = $$.config.data.comments;
      }
      
      for (i = 0; i < status.length; i++) {
        /* Add all period for a type of event */
        if (status[i].hasOwnProperty('times')) {
          $$.chart.selectAll('svg').data(status[i].times)
            .enter()
            .append('rect')
            .attr('x', function (d) { return $$.getXPos(d) })
            .attr('y', $$.config.margin.top + 2)
            .attr('width', function (d) {
              return (d.ending_time - d.starting_time) * $$.scaleFactor;
            })
            .attr('height', $$.config.itemHeight - 2)
            .style('fill', function (d) { return $$.getColor(status[i], i); });
        }
      }
      
      /* Add command line */
      $$.chart.selectAll('svg').data(comments)
        .enter()
        .append('line')
        .attr('x1', function (d) { return $$.getXPosComment(d) })
        .attr('x2', function (d) { return $$.getXPosComment(d) })
        .attr('y1', $$.config.margin.top)
        .attr('y2', $$.config.margin.top + $$.config.itemHeight)
        .style('stroke', '#6F6F6F')
        .on('mousemove', function (d) {
          $$.tooltipCommentShow(d, this);
        })
        .on('mouseout', function () {
          $$.tooltipCommentHide();
        });
    },
    /**
     * Get the start position of a period
     * 
     * @param {Object} d - The period data
     * @return {Float} - The x position
     */
    getXPos: function (d) {
      var $$ = this;

      return $$.config.margin.left + (d.starting_time - $$.startTime) *
        $$.scaleFactor;
    },
    /**
     * Get the position of a comment
     * 
     * @param {Object} d - The comment data
     * @return {Float} - The x position
     */
    getXPosComment: function (d) {
      var $$ = this;
      
      return $$.config.margin.left + (d.time - $$.startTime) * $$.scaleFactor;
    },
    /**
     * Get the color for a period
     * 
     * @param {Object} d - The period data
     * @param {Number} i - The index of the period
     * @return {String} - The color for the period
     */
    getColor: function (d, i) {
      var $$ = this;
      
      if (d.color) {
        return d.color;
      }
      return $$.config.colorCycle(i);
    },
    /**
     * Redraw the chart with new data
     * 
     * @param {Object} data - The new data
     */
    redraw: function (data) {
      var $$ = this;
      
      $$.config.data = data;
      
      /* Update Axis */
      $$.setPeriod();
      $$.xScale
        .domain([$$.startTime, $$.endTime])
        .range([$$.config.margin.left, $$.width - $$.config.margin.right]);
      
      $$.gAxis.transition().duration(300).call($$.xAxis);
      
      /* Update data */
      $$.svg.selectAll('rect')
        .remove();
      $$.setScaleFactor();
        
      $$.drawData();
    },
    /**
     * Display the tooltip
     *
     * @param {Object} data - The data to display
     * @param {Object} element - The element of attach the tooltip
     */
    tooltipCommentShow: function (data, element) {
      var $$ = this;
      
      $$.tooltip.select('.cc3-tooltip-title')
        .text(
          'Comment by ' + data.author + ' at ' +
          $$.config.tooltipDisplayTime(new Date(data.time))
        );
      $$.tooltip.select('.cc3-tooltip-body > pre')
        .text(data.comment);
        
      $$.tooltip.style('display', 'block').style($$.getTooltipPos(element));
    },
    tooltipCommentHide: function() {
      var $$ = this;
      
      $$.tooltip.style('display', 'none');
    },
    /**
     * Get the position of tooltip
     *
     * @param {Object} element - The element to attach the event of the mouse
     * @return {Object} - The absolute position (top, left)
     */
    getTooltipPos: function (element) {
      var $$ = this;
      var pos = $$.d3.mouse(element);
      var sizeTooltip = $$.tooltip.node().getBoundingClientRect();
      
      var top = (pos[1] + 10) + 'px';
      var left = (pos[0] + 20) + 'px';
      if ($$.config.tooltipAttach === 'bottom') {
        top = (pos[1] - sizeTooltip.height) + 'px';
      }
      if (pos[0] + sizeTooltip.width > $$.width) {
        left = (pos[0] - sizeTooltip.width - 10) + 'px';
      }
      
      return {
        left: left,
        top: top
      };
    },
    /**
     * Function on resize action
     */
    onResize: function () {
      var $$ = this;

      if ($$.resizeTimeout !== undefined) {
        window.clearTimeout($$.resizeTimeout);
      }
      $$.resizeTimeout = window.setTimeout(function () {
        $$.setWidth();
        $$.svg.style('width', $$.width + 'px');
        $$.redraw($$.config.data)
      }, 100);
    }
  };
  
  Chart.prototype = {
    /**
     * Load new data
     * 
     * @param {Object} data - The new data
     */
    load: function (data) {
      var $$ = this.internal;
      
      $$.redraw(data);
    },
    /**
     * Resize the chart
     *
     * @param {Object} data - The width and height
     */
    resize: function (data) {
      var $$ = this.internal;
      
      $$.width = data.width;
      $$.svg.style('width', $$.width + 'px');
      
      $$.redraw($$.config.data);
    }
  };
  
  /**
   * Generate a status chart
   */
  centreonStatusChart.generate = function (config) {
    return new Chart(config);
  };
  
  window.centreonStatusChart = centreonStatusChart;
})(window);