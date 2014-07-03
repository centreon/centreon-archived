<?php
?>
<html>
<head>
	<title>Google Map</title>
	<link href="../../Themes/Centreon-2/jquery-ui/jquery-ui.css" rel="stylesheet" type="text/css"/>
	<script type="text/javascript" src="../../include/common/javascript/jquery/jquery.js"></script>
	<script type="text/javascript" src="../../include/common/javascript/jquery/jquery-ui.js"></script>
    <script src="//maps.google.com/maps?file=api&amp;v=2&amp;sensor=false&amp;key=ABQIAAAAuPsJpk3MBtDpJ4G8cqBnjRRaGTYH6UMl8mADNa0YKuWNNa8VNxQCzVBXTx2DYyXGsTOxpWhvIG7Djw" type="text/javascript"></script>
    <script type="text/javascript">

      function initialize() {
      if (GBrowserIsCompatible()) {
      var map = new GMap2(document.getElementById("map_canvas"));
      map.setCenter(new GLatLng(37.4419, -122.1419), 13);
      
      // Add 10 markers to the map at random locations
      var bounds = map.getBounds();
      var southWest = bounds.getSouthWest();
      var northEast = bounds.getNorthEast();
      var lngSpan = northEast.lng() - southWest.lng();
      var latSpan = northEast.lat() - southWest.lat();
      for (var i = 0; i < 10; i++) {
          var point = new GLatLng(southWest.lat() + latSpan * Math.random(),
          southWest.lng() + lngSpan * Math.random());
          map.addOverlay(new GMarker(point));
      }
      }
      }
                          
   </script>
</head>
<body onload="initialize()" onunload="GUnload()" style="font-family: Arial;border: 0 none;padding:0px;margin:0px">
<div id="map_canvas" style="width: 100%; height: 100%"></div>
</body>
</html>

