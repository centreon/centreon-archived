==================
Customizing graphs
==================

***************
Graphs template
***************

Definition
==========

Graph models are models which can be used to shape graph layouts. 
Graph models can be used to configure multiple presentation settings including the y-axis measurement, the width and the height of the graph, or colors, etc.

Configuration
=============

To add a new graph model:

1. Go into the menu: **Views ==> Graphs**
2. In the left menu, click on **Templates**
3. Click on **Add**

.. image:: /images/user/graphic_management/02addgraph_template.png
    :align: center

General informations
--------------------

* The field **Template name** can be used to define a name for the graph model.
* The **Vertical label** field contains the legend for the y-axis (type of datameasured).
* The **Width** and **Height** fields are expressed in pixels and express respectively the width and the height of the model.
* The **Lower limit** field defines the minimum limit of the y-axis.
* The **Upper limit** field defines the maximum limit of the y-axis.
* The **Base** list defines the calculation base for the data during the scaling of the graph y-axis. Use 1024 for measurements like the Bytes (1 KB = 1 024 Bytes) and 1 000 for measurements like the volt (1 kV = 1 000 Volts).

.. note::
    If the box **Size to max** is checked, the graph will automatically be scaled to the scale of the maximum value ordinates shown on the given period.

Legend
------

* The **Grid background color** field defines the background color of the grid, where the data is developed.
* The **Main grid color** field defines the grid, to the main scale.
* The **Secondary grid color** field defines the grid, to the secondary scale.
* The **Outline color** field defines the color of the contours.
* The **Background color** field defines the background color of the graph.
* The **Text Color** field defines the color of the text in the graph.
* The **Arrow color** field defines the color of the x- and y-axis arrows.
* The **Top color** field defines the color of the left and top borders of the image.
* The **Bottom color** field defines the color of the right and bottom borders of the image.
* If the **Split Components** box is checked, the curves are automatically separated on display.
* If the **Scale Graph Values** box is checked, the graph is automatically put to scale by the graph generation motor.
* If the **Default Centreon Graph Template** box is checked, this model becomes the default model for all the graphs for which no model is defined.
* The **Comment** field can be used to comment on the graph model.

Using a graph template
======================

You can add this layout model on edition of the object for:

* A service (or a model of service) by going into the **Service Extended Info** tab in configuration form.
* A command

******
Curves
******

Definition
==========

A curve is the representation of the evolution performance data (metrics produced from the collection of data) visible via performance graphs. A graph may contain multiple curves. It is possible to customise the curves by changing certain settings: curve profile, position of the curves on the graph, legend and additional information (average, total value, etc.).

Configuration
=============

To add a new curve model:

1. Go into the menu: **Views ==> Graphs**
2. In the left menu, click on **Curves**
3. Click on **Add**
 
.. image:: /images/user/graphic_management/02addcurve.png
     :align: center

* The **Template Name** field defines the name of the model.
* The **Hosts/Service Data Source** lists defines the host/service for which this curve will be used. If this information is not filled in, this curve definition will be applied to all services in which this metric appears.
* The **Data Source Name** field can be used to select the metric which will use this definition. The List of known metrics list can be used to choose the already existing metrics used by the services.
* If the **Stack** box is checked, this curve will be stacked on the others (useful to see the proportion of one metric in relation to another).
* If the **Order** box is checked, the Order list can be used to define the order display / stacking of the curve (the smaller the number, the closer it will be to the x-axis).
* If the **Invert** box is checked, the curve is reversed (opposite to the absolute value) in relation to the y-axis (useful for seeing the proportion of the incoming traffic compared to the outgoing traffic).
* The **Thickness** list expresses the thickness of the line of the curve (expressed in pixels).
* The **Line color** field defines the color of the curve.
* The **Area color** field concerns the filling color of the curve if the Filling option is checked, (see below). It contains 3 fields that correspond with the colors of the OK, WARNING and CRITICAL statuses respectively.
* The **Transparency** field defines the level of transparency of the contour color.
* If the **Filling** box is checked, all the curve is filled with the color of the area defined according to the status.

The attributes below concern the information situated under the graph.

* The **Legend** field defines the legend of the curve.
* If the **Display only the legend** box is checked, the curve will be masked but the legend will be visible.
* The **Empty lines after this legend** list can be used to define a certain number of empty lines after the legend.
* If the **Print max value** box is checked, the maximum value reached by the curve will be displayed.
* If the **Print min value** box is checked, the minimum value reached by the curve will be displayed.
* If the **Round the min and max** box is checked, the minimum and maximum values will be rounded.
* If the **Print Average** box is checked, the average of the points of the curve will be displayed.
* If the **Print last value** box is checked, the last value collected from the curve will be displayed.
* If the **Print total value** box is checked, the total value is displayed (sum of all the values of the curve on the selected period).
* The **Comment** field can be used to comment on the curve.

Some examples of curves
=======================

Stacked curves:

.. image:: /images/user/graphic_management/02graphempile.png
    :align: center
 
Reversed curves:

.. image:: /images/user/graphic_management/02graphinverse.png
    :align: center
 
Curves with filling:

.. image:: /images/user/graphic_management/02graphremplissage.png
    :align: center

*************** 
Virtual metrics
***************
 
Definition
==========

The virtual metrics are the display of curves resulting from the processing / aggregation of data from a set of data.
The set of data corresponds to various values of curves on the period covered by the graph. 
The creation of virtual metrics is based on the RPN (Reverse Polish Notation) language.

Two types of sets of data are available:

* CDEF: this command creates a new set of points starting from one or more series of data. The aggregation is performed on each point (data).
* VDEF: the result of each aggregation is a value and a time component. This result can also be used in the miscellaneous graph and printing elements.

CDEF v. VDEF 
------------

The CDEF type works on a set of points (data table). The result of the processing (e.g.: multiplication by 8 to convert bits into Bytes) will be a set of points. The VDEF type enables us to extract the maximum from a set of points.

.. note::
    For more information on the RPN type notation, refer to the `official RRD documentation <http://oss.oetiker.ch/rrdtool/tut/rpntutorial.en.html>`_

Configuration
=============

To add a virtual metric:

1. Go into the menu: **Views ==> Graphs**
2. In the left menu, click on **Metrics** (under **Virtuals**)
3. Click on **Add**
 
.. image:: /images/user/graphic_management/02addvmetric.png
    :align: center

* The field **Metric name** defines the name of the metric.
* The **Host/Service Data Source** list can be used to define the service from which to work the metrics.
* The **DEF Type** field defines the type of data set used to calculate the virtual curve.
* The **RPN (Reverse Polish Notation) Function** field defines the formula to be used to calculate the virtual metric.

.. note:: 
    It is not possible to add together the metrics of different services. However, it is possible to add virtual metrics for the calculation of a new metric.

* The **Metric Unit** field defines the units of the metric.
* The **Warning threshold** field defines the alert threshold to be displayed on the graph.
* The **Critical threshold** field defines the critical threshold to be displayed on the graph.
* If the **Hidden Graph and Legend** box is checked, the curve and the legend are hidden.
* The **Comment** field can be used comment on the metric.

