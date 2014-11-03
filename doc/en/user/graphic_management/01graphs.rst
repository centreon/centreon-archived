======
Graphs
======

**********
Definition
**********

Centreon can be used to generate graphs from monitoring information. There are two types of graph:

* Performance graphs serve to view the evolution of services intuitively. E.g.: filling level of a hard disc, network traffic, etc.
* History graphs (or status graphs) serve to view the evolution of the statuses of a service.

Performance graphs always have a time period for the x-axis and a unit as the y-axis (Volts, Octets, etc.). History graphs always have a time period for the x-axis, their y-axes do not vary. Only the color of the graph can be used to view the status of the object:

* Green for OK status 
* Orange for WARNING status
* Red for CRITICAL status
* Grey for UNKNOWN status

Example of performance graphs:

.. image :: /images/user/graphic_management/01perf_graph.png
      :align: center
 
Example of status history graphs:

.. image :: /images/user/graphic_management/01stat_graph.png
      :align: center
 
*************
Visualization
*************

Performance graphs
==================

There are several ways to view performance graphs:

* Viewing the graph in the list of services (**Monitoring ==> Services**) by mouse-over the icon |column-chart|
* Viewing the graph from the page of details of an object by clicking on the icon |column-chart|
* Go into the menu: **Views ==> Graphs** to view multiple graphs

Status graphs
=============

In the same way as for the performance graphs, there are several ways of accessing status history graphs:

* From the detail page of an object (see the chapter covering :ref:`real time monitoring <realtime_monitoring>`)
* From the menu: **Views ==> Graphs**, by first selecting a specific service and then checking the **Display Status** box.

Viewing multiple graphs
=======================

To view all graphs, go into the menu: **Views ==> Graphs**.
 
.. image :: /images/user/graphic_management/01graph_list.png
      :align: center

The left menu can be used to select the hosts and / or the services for which we want to view the graphs.

The grey search bar called **Graph Period** can be used to select the time period over which we want to view the graphs. 
The drop-down list can be used to select predefined time periods. It is possible to choose the time period manually using the fields **From** and **To**, this replaces the predefined selection.

Several actions are possible on the graphs:

* **Split components**: separates multiple curves of a graph into multiple graphs each containing one curve
* **Display Status**: Displays the history graphs linked to performance graphs displayed

To use the data from graphs, it is possible to:

* View the performance graph on one day, one week, one month or one year by clicking on the performance graphs of your choice
* Zoom on the graph by clicking on the icon |zoom|
* Back-up the graph by clicking on the icon |save|
* Download all the data contained in the graph in the .csv format by clicking on the icon |text_binary_csv|

Filters 
-------

It is possible to filter the selection of resources via:

* The quick search bar by searching by **host** or **service**
* By browsing the selection tree (left menu) by host group, and then by host, and then by service displayed by the graph
* By browsing the selection tree (left menu) by service group and then by service displayed by the graph

.. note::
   Hosts not linked to a host group are added to the **Orphanhed Host** container.

.. |column-chart|    image:: /images/column-chart.png
.. |zoom|       image:: /images/zoom.png
.. |save|       image:: /images/save.png
.. |text_binary_csv| image:: /images/text_binary_csv.png

