ALTER TABLE `metrics` CHANGE `warn_threshold_mode` `warn_threshold_mode` boolean not null default 0;
ALTER TABLE `metrics` CHANGE `crit_threshold_mode` `crit_threshold_mode` boolean not null default 0;
