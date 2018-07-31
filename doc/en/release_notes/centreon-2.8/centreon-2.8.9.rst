##################
Centreon Web 2.8.9
##################

Bug Fixes
=========

* Fix Incorrect style for "Scheduled downtime" in dashboard - #5240
* Apply new Centreon graphical charter to add and modify pages for metaservice indicator - #5255
* [2.8.6] : Double quote are converted in html entities in fields Args - #5205
* Duplicate host template doesn't work - #5252
* [BUG] "Home > Poller Statistics > Graphs" only works for Central - #4954
* "Recovery notification delay" is not written to centreon-engine's configuration - #5249 - PR #5268
* Severity of 'host category' - #5245
* [2.8.8] Deploy Service action won't work - #5215
* [2.8.8] Issue when adding new connector - #5233
* [2.8.8] Data pagination - #5259
* Cannot modify metaservice indicator - #5254 - PR #5267
* [2.7.11] Migration 2.7.11 to 2.8.x does not work #5265
* 2.7 to 2.8 upgrade error - #5220
* Cannot insert numbers in service description field - #5275
* [2.8.7] - Timezone / Location BUG !! - #5218
* 2.8.8 Service Trap Relation empty - #5223
* [2.7.x/2.8.X] Old school style in popup - #5232
* [BUG] ACL - Servicegroup - #5101 - PR #5222
* [2.8.7] Missing argument 1 for PEAR::isError() - #5214 - PR #5225
* [Reporting > Dashboard > Services] Unable to export CSV - #5170 - PR #5172

Graphs
------

* Graph are not correctly scaled - #5248
* [Chart] scale in charts using CPU template is wrong Kind/Bug Status/Implemented - #5130
* Graph scale values not working - #4815
* [2.8.5] Charts upper limit different from template - #5123
* Remove chart padding - #5288
* Base Graph 1000/1024 Kind/Bug Status/Implemented - #5069
* [2.8.6] non-admin user split chart permission - #5177
* After using split chart, curves are not displayed anymore (period filter not applied) - #5198 - PR #5171
* [GRAPH] Problem with external graph usage (Widgets, Centreon BAM) - #5270
* Incorrect scale and position for rta curve (performance ping graph) - #5202
* Wrong tool tip display on chart with two units when one of the curves is disabled - #5203
* Splited chart png export misnamed doesn't work with HTTPS - #5121 - PR #5171
* [2.8.5] Splited chart png export misnamed - #5120
* [Chart] curves units are displayed on incorrect side - #5113
* Assign good unit and curves to y axis when 2 axis - #5150
* remove curves artifacts - #5153
* Beta 2.8 Curve with an weird shape. - #4644
* The round of the curves - #5143
* The extra legend is option in chart. - #5156
* Add option for display or not the toggle all curves in views charts - #5159
* Use the base from graph template for humanreable ticks - #5149
