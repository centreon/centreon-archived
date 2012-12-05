ALTER TABLE `metrics` CHANGE `max` `max` FLOAT NULL DEFAULT NULL;
ALTER TABLE `metrics` CHANGE `min` `min` FLOAT NULL DEFAULT NULL;

-- Replace special some chars in metrics name
UPDATE metrics SET metric_name = REPLACE(metric_name, '#S#', '/');
UPDATE metrics SET metric_name = REPLACE(metric_name, '#P#', '%');
UPDATE metrics SET metric_name = REPLACE(metric_name, '#BS#', '\\');
