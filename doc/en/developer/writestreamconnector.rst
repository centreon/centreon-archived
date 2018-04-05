===============================
How to write a Stream Connector
===============================

********
Overview
********

Centreon Stream Connector is a feature introduced in Centreon 3.4.6. It allows 
to export Centreon data (events and metrics) to an external storage or application 
such as ElasticSearch, Splunk, InfluxDB, files, etc.

In a Centreon platform, the component that carries information between the remote 
pollers and the Centreon central server is called Centreon Broker. This broker 
stores received data into the Centreon local storage: MariaDB and RRDtool.

The following diagram explains the transfer of collected data and insertion into 
storages:

.. image:: /_static/images/developer/lua/archi_broker_regular.png
   :align: center
   :scale: 65%

The Stream Connector functionality is a new Centreon Broker output getting data 
from Centreon Broker Master (also known as Centreon Broker SQL) to aggregate and 
forward it to external storage:

.. image:: /_static/images/developer/lua/archi_broker_stream.png
   :align: center
   :scale: 65%

This output loads a LUA script, called a Stream Connector, which job is to handle, 
aggregate and enrich the data before forwarding it to the defined protocol:

.. image:: /_static/images/developer/lua/archi_broker_lua_script.png
   :align: center
   :scale: 65%

Because it is an output of Centreon Broker, the principle of creation of retention 
files in case of interruption of access to external storage is kept. In the same way, 
it is possible to filter in input on the categories of flow to handle.

************
Requirements
************

To use the Centreon Stream connector functionality you need to update your Centreon 
platform to Centreon 3.4.6:

* Centreon Web >= 2.8.18
* Centreon Broker >= 3.0.13
* Lua >= 5.1.x

*************************
Creating a new LUA script
*************************

The complete technical documentation is available `here <https://documentation.centreon.com/docs/centreon-broker/en/latest/exploit/stream_connectors.html>`_.
In this how-to, we will write all data to a log file and export performance data to a TSDB like InfluxDB.

Programming language
====================

Centreon chose the LUA programing language to let you handle, aggregate and 
transfer data. LUA is a programming language that is easy to use. You can find 
more information into `LUA official documentation <https://www.lua.org/docs.html>`_

Data structure
==============

The data structure depends on the category of the event: real-time data, 
performance data, etc. For more information about event categories and available 
data, `please see technical documentation <https://documentation.centreon.com/docs/centreon-broker/en/latest/dev/bbdo.html#event-categories>`_.

Cache structure
===============

Centreon Broker use ID instead of the name of object (hosts, services, metrics, 
groups, …). The cache structures are built when the Centreon Broker receives the 
events from Centreon Engine during the restart process. 

.. warning::
   Each time you restart your Centreon Broker with a Stream Connector output, 
   you need to restart Centreon Engine to rebuild the cache too.

For more information about event categories and available data, `please see technical documentation 
<https://documentation.centreon.com/docs/centreon-broker/en/latest/exploit/stream_connectors.html#the-broker-cache-object>`_.

Storage of LUA scripts
======================

We will store all LUA scripts into the **/usr/share/centreon-broker/lua**.

.. note::
   In a near future, this directory will be known by Lua scripts launched by broker.

Write all information into a file
=================================

Store raw data
**************

We will store the **bbbdo2file.lua** LUA connector into the 
**/usr/share/centreon-broker/lua** directory on the Centreon central server.

Centreon Broker has a log function to write logs into a file. We will use this 
function to write raw data into a dedicated file. `See technical documentation for more information 
<https://documentation.centreon.com/docs/centreon-broker/en/latest/exploit/stream_connectors.html#the-broker-log-object>`_.

The **write()** function is called each time an event is received from a poller 
through the broker. We will call the log function into this function to store 
raw data. `See technical documentation for more information 
<https://documentation.centreon.com/docs/centreon-broker/en/latest/exploit/stream_connectors.html#the-write-function>`_.

The **bbbdo2file.lua** script will contain:

.. code-block:: lua

  function init(conf)
    broker_log:set_parameters(3, "/var/log/centreon-broker/bbdo2file.log")
  end

  function write(d)
    for k,v in pairs(d) do
      broker_log:info(3, k .. " => " .. tostring(v))
    end
    return true
  end

.. note::
   For more information about the initialisation of the log function of Centreon 
   Broker and the parameters, please `see technical documentation <https://documentation.centreon.com/docs/centreon-broker/en/latest/exploit/stream_connectors.html#the-broker-log-object>`_.

Once your file **/usr/share/centreon-broker/lua/bbbdo2file.lua** is ready, add 
execution rights on it::

  # chown centreon-engine:centreon-engine /usr/share/centreon-broker/lua/bbbdo2file.lua

Then configure the new output into Centreon Web interface in 
**Configuration > Pollers > Broker configuration > Central Broker**. In **Output** 
tab select **Generic – Stream connector** and click **Add**:

.. image:: /_static/images/developer/lua/add_stream_connector.png
   :align: center

Define the name of this output and the path to the LUA connector:

.. image:: /_static/images/developer/lua/describe_output.png
   :align: center

Then click **Save** and go to generate the configuration and restart **cbd**.

Once the Centreon Broker will be restart on your Centreon central server, data 
will appear in your **/var/log/centreon-broker/bbdo2file.log** log file::

  mer. 28 mars 2018 14:27:35 CEST: INFO: flap_detection => true
  mer. 28 mars 2018 14:27:35 CEST: INFO: enabled => true
  mer. 28 mars 2018 14:27:35 CEST: INFO: host_id => 102
  mer. 28 mars 2018 14:27:35 CEST: INFO: last_time_ok => 1522240053
  mer. 28 mars 2018 14:27:35 CEST: INFO: state => 0
  mer. 28 mars 2018 14:27:35 CEST: INFO: last_update => 1522240054
  mer. 28 mars 2018 14:27:35 CEST: INFO: last_check => 1522240053
  mer. 28 mars 2018 14:27:35 CEST: INFO: execution_time => 0.005025
  mer. 28 mars 2018 14:27:35 CEST: INFO: acknowledged => false
  mer. 28 mars 2018 14:27:35 CEST: INFO: service_id => 778
  mer. 28 mars 2018 14:27:35 CEST: INFO: active_checks => true
  mer. 28 mars 2018 14:27:35 CEST: INFO: notify => false
  mer. 28 mars 2018 14:27:35 CEST: INFO: max_check_attempts => 3
  mer. 28 mars 2018 14:27:35 CEST: INFO: obsess_over_service => true
  mer. 28 mars 2018 14:27:35 CEST: INFO: check_type => 0
  mer. 28 mars 2018 14:27:35 CEST: INFO: last_hard_state_change => 1522165654
  mer. 28 mars 2018 14:27:35 CEST: INFO: category => 1
  mer. 28 mars 2018 14:27:35 CEST: INFO: perfdata => used=41986296644o;48103633715;54116587930;0;60129542144 size=60129542144o
  mer. 28 mars 2018 14:27:35 CEST: INFO: check_interval => 5
  mer. 28 mars 2018 14:27:35 CEST: INFO: output => Disk /var - used : 39.10 Go - size : 56.00 Go - percent : 69 %
  mer. 28 mars 2018 14:27:35 CEST: INFO: check_command => check-bench-disk
  mer. 28 mars 2018 14:27:35 CEST: INFO: check_period => 24x7
  mer. 28 mars 2018 14:27:35 CEST: INFO: type => 65560
  mer. 28 mars 2018 14:27:35 CEST: INFO: last_hard_state => 0

.. note::
   This log file will grow quickly, do not forget to add a log rotate

Use parameters
**************

The Centreon Broker log function should be use for log only. To write into a file, 
we must use the LUA dedicated function. Moreover, it is possible to use parameters 
to define the name of the log file.

Edit your LUA connector:

.. code-block:: lua

  function init(conf)
    logFile = conf['logFile']
    broker_log:set_parameters(3, "/var/log/centreon-broker/debug.log")
  end
  
  function write(d)
    for k,v in pairs(d) do
      wrintIntoFile(k .. " => " .. tostring(v) .. "\n")
    end
    return true
  end

  function wrintIntoFile(output)
    local file,err = io.open(logFile, 'a')
    if file == nil then
      broker_log:info(3, "Couldn't open file: " .. err)
    else
      file:write(output)
      file:close()
    end
  end

The **init()** function allows to get parameters and define these from Centreon 
web interface. See technical documentation for more information.

Edit also your broker output to declare this parameter:

.. image:: /_static/images/developer/lua/add_parameter.png
   :align: center

Then click **Save** and go to generate the configuration and restart **cbd**.

.. note::
   Don’t forget to restart “centengine” too to create the Centreon Broker cache.

Data still stored into **/var/log/centreon-broker/bbdo2file.log** log file::

  name => error
  category => 3
  interval => 300
  rrd_len => 3456000
  value => 0
  value_type => 0
  type => 196612
  ctime => 1522315660
  index_id => 4880
  element => 4
  state => 0
  category => 3
  interval => 300
  rrd_len => 3456000
  is_for_rebuild => false
  service_id => 1056
  type => 196609
  ctime => 1522315660
  host_id => 145
  element => 1
  is_for_rebuild => false
  metric_id => 11920

Manipulate data
***************

Not all data are necessary. We will select only the NEB category and the events 
regarding hosts and services status to write into a file the following data:

* The type of object (HOST or SERVICE)
* The name of host and ID
* The description of service and ID (for service only)
* The status and output of service

To do this, we need to select not all events received by Centreon Broker but only 
event regarding status of hosts and services objects from NEB category. By 
following `the technical documentation <https://documentation.centreon.com/docs/centreon-broker/en/latest/dev/bbdo.html#event-categories>`_, 
the NEB events type (real time) are linked to category 1. Moreover, the status of 
host is link to element 14 and 24 for the status of services. To filter on these 
events only, we will add a filter function with these properties.

The new LUA script will contain:

.. code-block:: lua

  function init(conf)
    logFile = conf['logFile']
    broker_log:set_parameters(3, "/var/log/centreon-broker/debug.log")
  end
  
  function write(d)
    local output = ""
  
    local host_name = broker_cache:get_hostname(d.host_id)
    if not host_name then
      broker_log:info(3, "Unable to get name of host, please restart centengine")
      host_name = d.host_id
    end
  
    if d.element == 14 then
      output = "HOST:" .. host_name .. ";" .. d.host_id .. ";" .. d.state .. ";" .. d.output
      wrintIntoFile(output)
      broker_log:info(output)
    elseif d.element == 24 then
      local service_description = broker_cache:get_service_description(d.host_id, d.service_id)
      if not service_description then
        broker_log:info(3, "Unable to get description of service, please restart centengine")
        service_description = d.service_id
      end
      output = "SERVICE:" .. host_name .. ";" .. d.host_id .. ";" .. service_description .. ";" .. d.service_id .. ";" .. d.state .. ";" .. d.output
      wrintIntoFile(output)
      broker_log:info(output)
    end
    return true
  end
  
  function wrintIntoFile(output)
    local file,err = io.open(logFile, 'a')
    if file == nil then
      broker_log:info(3, "Couldn't open file: " .. err)
    else
      file:write(output)
      file:close()
    end
  end
  
  function filter(category, element)
    -- Get only host status and services status from NEB category
    if category == 1 and (element == 14 or element == 24) then
      return true
    end
      return false
  end

The **/var/log/centreon-broker/bbdo2file.log** file will now contain::

  HOST:srv-DC-djakarta;215;0;OK - srv-DC-djakarta: rta 0.061ms, lost 0%
  SERVICE:mail-titan-gateway;92;disk-/usr;623;0;Disk /usr - used : 42.98 Go - size : 142.00 Go - percent : 30 %
  SERVICE:mail-sun-master;87;memory-stats;535;0;Memory usage (Total 13.0GB): 0.12GB [buffer:0.00GB] [cache:0.01GB] [pages_tables:0.00GB] [mapped:0.00GB] [active:0.07GB] [inactive:0.00GB] [apps:0.02GB] [unused:12.88GB]
  SERVICE:mail-saturn-frontend;86;traffic-eth1;512;0;Traffic In : 4.73 Mb/s (4.73 %), Out : 4.79 Mb/s (4.79 %) - Total RX Bits In : 396.01 Gb, Out : 393.88 Gb
  SERVICE:mail-saturn-frontend;86;memory-stats;515;0;Memory usage (Total 16.0GB): 8.89GB [buffer:0.43GB] [cache:0.95GB] [pages_tables:0.27GB] [mapped:0.15GB] [active:3.92GB] [inactive:0.29GB] [apps:2.88GB] [unused:7.11GB]
  SERVICE:mail-neptune-frontend;80;traffic-eth1;392;0;Traffic In : 4.82 Mb/s (4.82 %), Out : 6.48 Mb/s (6.48 %) - Total RX Bits In : 398.40 Gb, Out : 396.44 Gb
  HOST:srv-DC-casablanca;207;0;OK - srv-DC-casablanca: rta 2.042ms, lost 0%
  SERVICE:mail-neptune-frontend;80;memory-stats;395;0;Memory usage (Total 9.0GB): 0.54GB [buffer:0.03GB] [cache:0.00GB] [pages_tables:0.01GB] [mapped:0.00GB] [active:0.48GB] [inactive:0.00GB] [apps:0.01GB] [unused:8.46GB]
  SERVICE:mail-mercury-frontend;82;traffic-eth1;432;0;Traffic In : 8.28 Mb/s (8.28 %), Out : 1.23 Mb/s (1.23 %) - Total RX Bits In : 397.71 Gb, Out : 400.34 Gb
  SERVICE:mail-mercury-frontend;82;memory-stats;435;0;Memory usage (Total 12.0GB): 1.58GB [buffer:0.00GB] [cache:0.63GB] [pages_tables:0.00GB] [mapped:0.00GB] [active:0.75GB] [inactive:0.00GB] [apps:0.19GB] [unused:10.42GB]
  SERVICE:mail-mars-frontend;84;traffic-eth1;472;0;Traffic In : 7.24 Mb/s (7.24 %), Out : 3.36 Mb/s (3.36 %) - Total RX Bits In : 399.93 Gb, Out : 395.67 Gb
  SERVICE:mail-mars-frontend;84;memory-stats;475;0;Memory usage (Total 3.0GB): 1.19GB [buffer:0.01GB] [cache:0.59GB] [pages_tables:0.00GB] [mapped:0.00GB] [active:0.15GB] [inactive:0.04GB] [apps:0.39GB] [unused:1.81GB]
  SERVICE:mail-jupiter-frontend;85;traffic-eth1;492;0;Traffic In : 1.41 Mb/s (1.41 %), Out : 9.08 Mb/s (9.08 %) - Total RX Bits In : 388.86 Gb, Out : 394.85 Gb
  SERVICE:mail-jupiter-frontend;85;memory-stats;495;0;Memory usage (Total 12.0GB): 0.57GB [buffer:0.04GB] [cache:0.23GB] [pages_tables:0.02GB] [mapped:0.02GB] [active:0.07GB] [inactive:0.03GB] [apps:0.16GB] [unused:11.43GB]
  SERVICE:mail-io-backend;88;traffic-eth1;547;0;Traffic In : 1.51 Mb/s (1.51 %), Out : 7.12 Mb/s (7.12 %) - Total RX Bits In : 389.61 Gb, Out : 390.54 Gb
  SERVICE:mail-io-backend;88;diskio-system;551;0;Device /dev/sda: avg read 4.78 (MB/s) and write 9.08 (MB/s)

Export performance data to InfluxDB
===================================

`InfluxDB <https://www.influxdata.com/>`_ is a Time Series database. We will use 
this storage to insert performance data collected by the Centreon platform. For 
this example, we will use the predefined `InfluxDB Docker <https://hub.docker.com/_/influxdb/>`_.

To send data to InfluxDB, we need parameters to access to InfluxDB storage:

* **http_server_address**: IP address of the storage
* **http_server_port**: 8086 by default
* **http_server_protocol**: http or https
* **influx_database**: name of database
* **influx_user**: user to access to database if defined
* **influx_password**: password of user to access to database if defined

In order to do not saturate the storage, we will add all events in a queue and 
send data by bulk. We need to define the size of the queue and the maximum 
delay before send events:

* max_buffer_size  
* max_buffer_age

The LUA script will contain:

A function to create the queue
******************************

This function initialises the queue with default parameters or defined by the 
user. Moreover, the queue settings and the events will be store into a 
dedicated structure:

.. code-block:: lua

  local event_queue = {
    __internal_ts_last_flush    = nil,
    http_server_address         = "",
    http_server_port            = 8086,
    http_server_protocol        = "http",
    events                      = {},
    influx_database             = "mydb",
    influx_user                 = "",
    influx_password             = "",
    max_buffer_size             = 5000,
    max_buffer_age              = 5
  }
  
  function event_queue:new(o, conf)
    o = o or {}
    setmetatable(o, self)
    self.__index = self
    for i,v in pairs(conf) do
      if self[i] and i ~= "events" and string.sub(i, 1, 11) ~= "__internal_" then
        broker_log:info(1, "event_queue:new: getting parameter " .. i .. " => " .. v)
        self[i] = v
      else
        broker_log:warning(1, "event_queue:new: ignoring parameter " .. i .. " => " .. v)
      end
    end
    self.__internal_ts_last_flush = os.time()
    broker_log:info(2, "event_queue:new: setting the internal timestamp to " .. self.__internal_ts_last_flush)
    return o
  end

.. note::
   In this function, we use the concept of LUA classes and metatable to facilitate
   development. "o = o or {}" means that an object will be created even if the 
   "event_queue:new" function doesn't receive one.

   Read official documentation to understand `classes <https://www.lua.org/pil/16.1.html>`_ 
   and `metatable <https://www.lua.org/pil/13.html>`_.

.. note::
   We use level 1 of the log function because the settings of storage access is 
   important information.

A function to add event in queue
********************************

The add function allows to aggregate data before to add these into the queue. This 
function replaces host ID by hostname and service ID by the description. This 
function also operates the maximum size of the queue or the timeout:

.. code-block:: lua

  function event_queue:add(e)
    local metric = e.name
    -- time is a reserved word in influxDB so I rename it 
    if metric == "time" then
      metric = "_" .. metric
    end
  
    -- retrieve objects names instead of IDs
    local host_name = broker_cache:get_hostname(e.host_id)
    local service_description = broker_cache:get_service_description(e.host_id, e.service_id)
  
    -- what if we could not get them from cache
    if not host_name then
      broker_log:warning(1, "event_queue:add: host_name for id " .. e.host_id .. " not found. Restarting centengine should fix this.")
      host_name = e.host_id
    end
    if not service_description then
      broker_log:warning(1, "event_queue:add: service_description for id " .. e.host_id .. "." .. e.service_id .. " not found. Restarting centengine should fix this.")
      service_description = e.service_id
    else
      service_description = service_description:gsub(" ", "_")
    end
  
    -- we finally append the event to the events table
    metric = metric:gsub(" ", "_")
    broker_log:info(3, 'event_queue:add: adding  ' .. service_description .. ",host=" .. host_name .. " " .. metric .. "=" .. e.value .. " " .. e.ctime .. '000000000" to event list.')
    self.events[#self.events + 1] = service_description .. ",host=" .. host_name .. " " .. metric .. "=" .. e.value .. " " .. e.ctime .. "000000000\n"
  
    -- then we check whether it is time to send the events to the receiver and flush
    if #self.events >= self.max_buffer_size then
      broker_log:info(2, "event_queue:add: flushing because buffer size reached " .. self.max_buffer_size .. " elements.")
      self:flush()
      return true
    elseif os.time() - self.__internal_ts_last_flush >= self.max_buffer_age then
      broker_log:info(2, "event_queue:add: flushing " .. #self.events .. " elements because buffer age reached " .. (os.time() - self.__internal_ts_last_flush) .. "s and max age is " .. self.max_buffer_age .. "s.")
      self:flush()
      return true
    else
      return false
    end
  end

A function to flush the queue
*****************************

Once the events added in the queue and the maximum size of the queue or the 
timeout is reached, events will be sent to the InfluxDB storage. This function 
will build data from the queue and send these to the storage. If an error 
occurs, it will be write into the associated log file:

.. code-block:: lua

  function event_queue:flush()
    broker_log:info(2, "event_queue:flush: Concatenating all the events as one string")
    --  we concatenate all the events
    local http_post_data = ""
    local http_result_body = {}
    for i, raw_event in ipairs(self.events) do
      http_post_data = http_post_data .. raw_event
    end
    broker_log:info(2, 'event_queue:flush: HTTP POST request "' .. self.http_server_protocol .. "://" .. self.http_server_address .. ":" .. self.http_server_port .. "/write?db=" .. self.influx_database .. '"')
    broker_log:info(3, "event_queue:flush: HTTP POST data are: '" .. http_post_data .. "'")
  
    -- build url
    local influxdb_url = self.http_server_protocol .. "://" .. self.http_server_address .. ":" .. self.http_server_port .. "/write?db=" .. self.influx_database
    -- add authentication if needed
    if string.len(self.influx_user) >= 1 and string.len(self.influx_password) >= 1 then
      influxdb_url = influxdb_url .. "&u=" .. self.influx_user .. "&p="..self.influx_password
    end
  
    local hr_result, hr_code, hr_header, hr_s = http.request{
      url = influxdb_url,
      method = "POST",
      -- sink is where the request result's body will go
      sink = ltn12.sink.table(http_result_body),
      -- request body needs to be formatted as a LTN12 source
      source = ltn12.source.string(http_post_data),
      headers = { 
        -- mandatory for POST request with body
        ["content-length"] = string.len(http_post_data)
      }
    }
    -- Handling the return code
    if hr_code == 204 then
      broker_log:info(2, "event_queue:flush: HTTP POST request successful: return code is " .. hr_code)
    else
      broker_log:error(1, "event_queue:flush: HTTP POST request FAILED: return code is " .. hr_code)
      for i, v in ipairs(http_result_body) do
        broker_log:error(1, "event_queue:flush: HTTP POST request FAILED: message line " .. i .. ' is "' .. v .. '"')
      end
    end
  
    -- now that the data has been sent, we empty the events array
    self.events = {}
    -- and update the timestamp
    self.__internal_ts_last_flush = os.time()
  end

The init() function to get parameters and create the queue
**********************************************************

In this case, the “init()” function will create the queue with parameters 
defined by users of use default parameters if some are missing:

.. code-block:: lua

  function init(conf)
    broker_log:set_parameters(1, "/var/log/centreon-broker/stream-connector-influxdb.log")
    broker_log:info(2, "init: Beginning init() function")
    queue = event_queue:new(nil, conf)
    broker_log:info(2, "init: Ending init() function, Event queue created")
  end

The write() function to insert event in queue
*********************************************

The “write()” function is only used to insert filtered events into the queue:

.. code-block:: lua

  function write(e)
    broker_log:info(3, "write: Beginning write() function")
    queue:add(e)
    broker_log:info(3, "write: Ending write() function\n")
    return true
  end

The filter() function to select only performance data events
************************************************************
To select only performance data, we need to select category 3 (“Storage”) 
and the element type 1 (“metric”):

.. code-block:: lua

  function filter(category, element)
    if category == 3 and element == 1 then 
      return true
    end
    return false
  end

Complete script
***************

The complete script can by download `here <TODO>`_.

Configure Centreon Broker
*************************

Configure the new output into Centreon Web interface in 
**Configuration > Pollers > Broker configuration > Central Broker**. 
In **Output** tab select **Generic – Stream connector** and click **Add**:

.. image:: /_static/images/developer/lua/add_stream_connector.png
   :align: center

Define the name of this output and the path to the LUA connector:

.. image:: /_static/images/developer/lua/broker_influxdb_output.png
   :align: center
   :scale: 65%

Then click **Save** and go to generate the configuration and restart **cbd**.

.. note::
   Don’t forget to restart “centengine” too to create the Centreon Broker cache.

If you install the `Grafana <https://grafana.com/>`_ dashboard, you can visualize the stored data:

.. image:: /_static/images/developer/lua/visualize_data_grafana.png
   :align: center
   :scale: 65%

Discover other Centreon Stream Connectors
*****************************************

Centreon provides a Github repository to host LUA scripts developed by Centreon 
and the community. Please go to the `dedicated Github <http://github.com/centreon/centreon-lua-scripts>`_.

Need help to develop your Stream connector? You want to share your experience with 
the community? Join the `Centreon community Slack channel <https://centreon.github.io/>`_.
