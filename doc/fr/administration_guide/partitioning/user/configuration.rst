*************
Configuration
*************

Centreon Partitioning utilise des fichiers de configuration XML. Les fichiers des tables Centreon sont fournis.

Exemple avec partitioning-data_bin.xml
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

Explication
===========

Centreon Partitioning permet de créer des partitions journalières. Pour cela, votre table doit avoir un champ dont la valeur est un timestamp (temps en secondes depuis le 1er janvier 1970).

Le sens des attributs/valeurs des fichiers XML:

 - Attributs 'name' et 'schema': Respectivement nom de la table et nom de la base
 - tag 'column': nom du champ ayant pour valeur un timestamp unix
 - tag 'type': seulement la valeur "date" est supportée
 - tag 'duration': seulement la valeur "daily" est supportée (les futures versions supporteront: "weekly", "monthly")
 - tag 'timezone':  la timezone du serveur (la timezone est présente dans '/etc/sysconfig/clock' pour un OS CentOS)
 - tag 'retention': nombre de jours de retention
 - tag 'retentionforward': nombre de partition créées par avance (utile pour la durée de partitionnement)
