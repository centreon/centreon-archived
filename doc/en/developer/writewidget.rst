=====================
How to write a widget
=====================

Centreon (since version 2.4) offers a custom view system which allows
user to view one or different widgets in the same page : *Home >
Custom views*.

You may have specific needs that are not yet covered by our widget
catalog and this tutorial will explain to you how to write your first
widget for Centreon.

***********************************
Should I make a widget or a module?
***********************************

If you are wondering if you should be making a module or a widget,
then ask yourself if your project is meant to contain many menus or is
it rather a plain page which is going to display little information?

Of course, you could make a widget that would only work with a certain
module.

*******************
Directory structure
*******************

Widgets work pretty much like Modules. They have to be placed in
the following directory::

  # centreon/www/widgets/name-of-your-widget/

Your widget must contain one mandatory file named **configs.xml** at its root.

******************
Configuration file
******************

This is the XML configuration file of our Dummy widget:

.. code-block:: xml

  <configs>
        <title>Dummy</title>
        <author>Centreon</author>
        <email>contact@centreon.com</email>
        <website>http://www.centreon.com</website>
        <description>Dummy widget</description>
        <version>1.0.3</version>
        <keywords>dummy, widget, centreon</keywords>
        <screenshot></screenshot>
        <thumbnail>./widgets/dummy/resources/logoCentreon.png</thumbnail>
        <url>./widgets/dummy/index.php</url>
        <autoRefresh></autoRefresh>
        <preferences>
                <preference label="text preference" name="text preference" defaultValue="default value" type="text"/>
                <preference label="boolean preference" name="boolean preference" defaultValue="1" type="boolean"/>
                <preference label="date" name="date" defaultValue="" type="date"/>
                <preference label="host preference" name="host preference" defaultValue="" type="host"/>
                <preference label="list preference" name="list preference" defaultValue="none" type="list">
                        <option value="all" label="all"/>
                        <option value="none" label="none"/>
                </preference>
                <preference label="range preference" name="range preference" defaultValue="5" type="range" min="0" max="50" step="5"/>
                <preference label="host search" name="host search" defaultValue="notlike _Module_%" type="compare"/>
        </preferences>
  </configs>

Now, let's see what these tags refer to.

Basic tags
==========

\* = Mandatory tag

==============  ================================================================
Tag             nameDescription
==============  ================================================================
title*          Title of your widget

author*         Your name

email           Your email address

website         URL of your project

description*    Short description of your widget

version*        Version of your widget. Increment this number whenever you 
                publish a new version.

keywords        A few key words that describe your widget

screenshot      Screenshot that shows the best side of your widget. Screenshot 
                should be placed within your widget directory.

thumbnail       Logo of your project. Best size is 100px x 25px. Thumbnail 
                shoud be placed within your widget directory.

url*            Path of the main page of your widget

autorefresh     This parameter is not implemented yet
==============  ================================================================

Parameter attributes
====================

\* = *Mandatory parameter*

======================  ========================================================
Tag attributes          Description
======================  ========================================================
label*                  Label of the parameter

name*                   Name of the parameter that will be used for retrieving 
                        its value

defaultValue*           Default Value of the parameter

requirePermission       Value can be "1" or "0". When set to 1, this parameter 
                        will not be shown to unauthorized users.

type*                   Parameter type, must be one of the following: 
                        text,boolean,date,list,range,compare,host,hostgroup,
                        hostTemplate,servicegroup,serviceTemplate

min*                    For range type only. It refers to the minimum value 
                        of the range parameter

max*                    For range type only. It refers to the maximum value 
                        of the range parameter

step*                   For range type only. It refers to the step value of 
                        the range parameter
======================  ========================================================

Parameter type
==============

======================  ========================================================
Type name               Description
======================  ========================================================
text                    Renders a text input element

boolean                 Renders a checkbox

date                    Renders two text input elements. One for the date of 
                        start, the other one for the date of end.

list                    Renders a selectbox. The selectbox will be populated 
                        with the option tags which have to be defined within the
                        preference tag.

range                   Renders a selectbox which will be populated with values
                        depending on the min, max and step definitions.

compare                 Renders a selectbox and a text input. Selectbox will 
                        contain SQL operands such as::

                          > : greater than
                          < : less than
                          >= : greater or equal
                          <= : less or equal
                          = : equal
                          != : not equal
                          LIKE : can be used with the wildcard %%
                          NOT LIKE : can be used with the wildcard %%

host                    Renders a selectbox populated with a list of hosts.

hostgroup               Renders a selectbox populated with a list of hostgroups.

hostTemplate            Renders a selectbox populated with a list of host 
                        templates.

servicegroup            Renders a selectbox populated with a list of 
                        servicegroups.

serviceTemplate         Renders a selectbox populated with a list of service 
                        templates.
======================  ========================================================

The preference window would look like this as a result:

.. image:: /_static/images/extending/pref_dummy_widget.png
   :align: center

****
Code
****

All langages are separated in differents files, one file for each langage. The file "configs.xml" call the php's file and the php's file call html's file etc...

We use Smarty, it's an engine and template'php compiler (http://smarty.net).


To use Smarty you need to :

.. source-code:: php

	require_once $centreon_path . 'GPL_LIB/Smarty/libs/Smarty.class.php';

1.configuration of smarty:

.. source-code:: php

	$path = $centreon_path . "www/widgets/Dummy/src/";
	$template = new Smarty();
	$template = initSmartyTplForPopup($path, $template, "./", $centreon_path);

2.creating php template to be use in html:

.. source-code:: php

 	$template->assign('widgetId', $widgetId);
	$template->assign('autoRefresh', $autoRefresh);
	$template->assign('data', $data);

3.affectation of html's file to execute:

.. source-code:: php

	$template->display('dummy.ihtml');


To call template php's variable in the html look dummy.ihtml

To do request in database: 

initialization of databases's centreon, centreon storage and recovering preferences:

.. sourcecode:: php

	try {
    		global $pearDB;

    		$db_centreon = new CentreonDB("centreon");
    		$db = new CentreonDB("centstorage");
   		$pearDB = $db_centreon;

    		$widgetObj = new CentreonWidget($centreon, $db_centreon);
    		$preferences = $widgetObj->getWidgetPreferences($widgetId);
    		$autoRefresh = 0;
    		if (isset($preferences['refresh_interval'])) {
        		$autoRefresh = $preferences['refresh_interval'];
    		}
	} catch (Exception $e) {
    	echo $e->getMessage() . "<br/>";
    	exit;
	}

then request in database with class' methods.

