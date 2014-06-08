/* Gauge based on http://codepen.io/jaketrent/pen/eloGk?editors=001 */
centreonGaugeSimpleNeedle = (function() {
  function centreonGaugeSimpleNeedle( len, radius ) {
    this.len = len;
    this.radius = radius;
  }

  centreonGaugeSimpleNeedle.prototype.drawOn = function( element, percent ) {
    element.append( "circle" )
      .attr( "class", "needle-center" )
      .attr( "cx", 0 )
      .attr( "cy", 0 )
      .attr( "r", this.radius );
    return element.append( "path" )
      .attr( "class", "needle" )
      .attr( "d", this.makeCmd( percent ) );
  };

  centreonGaugeSimpleNeedle.prototype.animateOn = function( element, percent ) {
    var self = this;
    return element.transition()
      .delay( 500 )
      .ease( "elastic" )
      .duration( "3000" )
      .selectAll( ".needle" )
      .tween( "progress", function() {
        return function( percentOfPercent ) {
          var progress = percentOfPercent * percent;
          return d3.select( this ).attr( "d", self.makeCmd( progress ) );
        };
      });
  };

  centreonGaugeSimpleNeedle.prototype.makeCmd = function( percent ) {
    var leftX, leftY, rightX, rightY, topX, topY,
        centerX = 0,
        centerY = 0,
        thetaRadius = centreonGaugeSimple.percentToRad( percent / 2 );
        topX = centerX - this.len * Math.cos( thetaRadius );
        topY = centerY - this.len * Math.sin( thetaRadius );
        leftX = centerX - this.radius * Math.cos( thetaRadius - Math.PI / 2 );
        leftY = centerY - this.radius * Math.sin( thetaRadius - Math.PI / 2 );
        rightX = centerX - this.radius * Math.cos( thetaRadius + Math.PI / 2 );
        rightY = centerY - this.radius * Math.sin( thetaRadius + Math.PI / 2 );
        return "M " + leftX + " " + leftY + " L " + topX + " " + topY + " L " + rightX + " " + rightY;
  };

  return centreonGaugeSimpleNeedle;
})();

( function( window ) {
  var centreonGaugeSimple = {
    version: "1.0.0"
  };

  centreonGaugeSimple.generate = function( config ) {
    var element, elementSvg, sectionPercent, elWidth, elHeight, radius, chart, i,
        arcStartRad, arcEndRad, sectionIndex, numSections,
        totalPercent = 0.75,
        defaultConfig = {
          element: '.gauge-simple',
          percent: 0,
          barWidth: 20,
          sections: [],
          padRadius: 0.05,
          chartInset: 10,
          margin: {
            top: 20,
            right: 20,
            bottom: 30,
            left: 20
          },
          needleRadius: 10,
          needleSize: 60
        };
    config = $.extend( {}, defaultConfig, config );
    numSections = config.sections.length;
    sectionPercent = 1 / config.sections.length / 2;
    element = d3.select( config.element );
    elWidth = element[0][0].offsetWidth - config.margin.left - config.margin.right;
    elHeight = elWidth;
    radius = Math.min( elWidth, elHeight ) / 2;

    elementSvg = element.append( "svg" )
      .attr( "width" , elWidth + config.margin.left + config.margin.right )
      .attr( "height" , elHeight / 2 + config.margin.top + config.margin.bottom );

    chart = elementSvg.append( "g" )
      .attr( "transform", "translate(" + ( ( elWidth + config.margin.left ) / 2 ) + ", " + ( ( elHeight + config.margin.top) / 2 ) + ")" );

    for ( sectionIndex = i = 1; 1 <= numSections ? i <= numSections : i >= numSections; sectionIndex = 1 <= numSections ? ++i : --i ) {
      arcStartRad = centreonGaugeSimple.percentToRad( totalPercent );
      arcEndRad = arcStartRad + centreonGaugeSimple.percentToRad( sectionPercent );
      totalPercent += sectionPercent;
      startPadRad = sectionIndex === 0 ? 0 : config.padRadius / 2;
      endPadRad = sectionIndex === numSections ?  0 : config.padRadius / 2;
      arc = d3.svg.arc()
        .outerRadius( radius - config.chartInset )
        .innerRadius( radius - config.chartInset - config.barWidth)
        .startAngle( arcStartRad + startPadRad )
        .endAngle( arcEndRad - endPadRad );
      chart.append( "path" ).attr( "fill", config.sections[ i - 1 ] ).attr( "d", arc );
    }

    needle = new centreonGaugeSimpleNeedle( config.needleSize, config.needleRadius );
    needle.drawOn( chart, 0 );
    needle.animateOn( chart, config.percent );
  };

  centreonGaugeSimple.percentToRad = function( percent ) {
    return ( percent * 360 ) * Math.PI / 180;
  };


  window.centreonGaugeSimple = centreonGaugeSimple;
} )( window );
