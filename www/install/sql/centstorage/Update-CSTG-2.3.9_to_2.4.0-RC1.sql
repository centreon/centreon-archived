
ALTER TABLE config ADD reporting_retention int(11) default '365' AFTER archive_retention;

CREATE INDEX internal_id ON `comments` (`internal_id`);

