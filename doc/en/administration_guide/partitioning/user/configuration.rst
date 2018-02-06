*************
Configuration
*************

Centreon Partitioning uses XML configuration files. There are already some configuration files for Centreon tables.

Example with partitioning-data_bin.xml
======================================
::

  <?xml version="1.0" encoding="UTF-8"?>
  <centreon-partitioning>
  <table name="data_bin" schema="centreon_storage">
        <activate>1</activate>
        <column>ctime</column>
        <type>date</type>
        <duration>daily</duration>
        <retention>365</retention>
        <retentionforward>10</retentionforward>
        <backup>
            <folder>/var/backups/</folder>
            <format>%Y-%m-%d</format>
        </backup>
        <createstmt>
  CREATE TABLE IF NOT EXISTS `data_bin` (
    `id_metric` int(11) DEFAULT NULL,
    `ctime` int(11) DEFAULT NULL,
    `value` float DEFAULT NULL,
    `status` enum('0','1','2','3','4') DEFAULT NULL,
    KEY `index_metric` (`id_metric`)
  ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
       </createstmt>
  </table>
  </centreon-partitioning>

Explanation
-----------

Centreon Partitioning offers to create daily partitions. For that, your table has to have a unix timestamp column (time in seconds since 1970).
The meaning of XML attributes/values:

 - attributes 'name' and 'schema': table name and database name respectively
 - tag 'column': column name with the unix timestamp
 - tag 'type': only "date" value
 - tag 'duration': only "daily" (futur version could have: "weekly", "monthly")
 - tag 'timezone': your server timezone (you can have the timezone value in file '/etc/sysconfig/clock' for centos)
 - tag 'retention': number of days keeping
 - tag 'retentionforward': number of partition created by advance (useful for range partitioning)
