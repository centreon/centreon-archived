<?php
/*
 * Copyright 2005-2014 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 *
 */

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

