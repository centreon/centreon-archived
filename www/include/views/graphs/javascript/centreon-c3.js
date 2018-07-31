/* global c3 */
(function (window) {
  if (window.c3 === undefined) {
    console.error('c3js library is not loaded');
    return;
  }
  
  /* Additionnal configuration for zoom on select */
  window.c3.chart.internal.fn.additionalConfig = {
    zoom_select: {
      enabled: false,
      onzoom: null
    }
  };
  
  window.c3.chart.internal.fn.beforeInit = function (config) {
    var $$ = this;
    var renderedCallback;
    
    /* Merge configuration */
    if (config.hasOwnProperty('zoom_select')) {
      if (config.zoom_select.hasOwnProperty('enabled')) {
        $$.config.zoom_select.enabled = config.zoom_select.enabled;
      }
      if (config.zoom_select.hasOwnProperty('onzoom')) {
        $$.config.zoom_select.onzoom = config.zoom_select.onzoom;
      }
    }
    
    /* On rendered */
    if ($$.config.zoom_select.enabled) {
      if (typeof $$.config.onrendered === 'function') {
        renderedCallback = $$.config.onrendered
      }
      $$.config.onrendered = function () {
        /* Force scale of brush */
        if ($$.zoom_select_brush) {
          $$.zoom_select_brush.x($$.x);
        }
        /* The saved onrendered */
        if (typeof renderedCallback === 'function') {
          renderedCallback();
        }
      };
    }
  };
  
  window.c3.chart.internal.fn.afterInit = function () {
    var $$ = this;
    var d3 = $$.d3;
    
    if ($$.config.zoom_select.enabled) {
      var brushing = false;

      /* Initialize brush */
      $$.zoom_select_brush = d3.svg.brush()
        .x($$.x)
        .on('brushstart', function () {
          brushing = true;
        })
        .on('brushend', function () {
          if (typeof $$.config.zoom_select.onzoom === 'function') {
            if (!$$.zoom_select_brush.empty()) {
              $$.config.zoom_select.onzoom($$.zoom_select_brush.extent());
              $$.zoom_select_brush.clear();
                $$.svg.selectAll('.' + $$.CLASS.brush)
                .call($$.zoom_select_brush);
            }
          }
          brushing = false;
        });

      /* Attach brush to main chart */
      $$.svg.selectAll('.c3-chart').append('g')
        .attr('class', $$.CLASS.brush)
        .style('display', 'none')
        .call($$.zoom_select_brush)
        .selectAll('rect')
        .attr('height', $$.height);

      /* Attach events for on click activate brush */
      $$.svg.selectAll('.c3-chart').on('mousedown', function () {
        $$.svg.select('.' + $$.CLASS.brush).style('display', 'block');
      });
      $$.svg.selectAll('.c3-chart').on('mouseup', function () {
        if (brushing) {
            $$.svg.select('.' + $$.CLASS.brush).style('display', 'none');
        }
      });
    }
  };
})(window);
