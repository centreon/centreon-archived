-- Change version of Centreon
UPDATE `informations` SET `value` = '19.04.0' WHERE CONVERT( `informations`.`key` USING utf8 ) = 'version' AND CONVERT ( `informations`.`value` USING utf8 ) = '18.10.4' LIMIT 1;

-- Remove unused options
DELETE FROM options WHERE options.key IN ('rrdtool_title_font', 'rrdtool_title_fontsize', 'rrdtool_unit_font', 'rrdtool_unit_fontsize', 'rrdtool_axis_font', 'rrdtool_axis_fontsize', 'rrdtool_watermark_font', 'rrdtool_watermark_fontsize', 'rrdtool_legend_font', 'rrdtool_legend_fontsize');
