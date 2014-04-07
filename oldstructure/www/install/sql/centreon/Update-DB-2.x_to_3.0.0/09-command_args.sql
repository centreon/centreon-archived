CREATE TABLE `host_checkcmd_args_relations` (
	`host_id` int(11) NOT NULL,
	`arg_number` tinyint(3) NOT NULL, 
	`arg_value` varchar(255) DEFAULT NULL,
	KEY `fk_host_checkcmd_args_relations` (`host_id`),
	CONSTRAINT `fk_host_checkcmd_args_relations` FOREIGN KEY (`host_id`) REFERENCES `host` (`host_id`) ON DELETE CASCADE
);

CREATE TABLE `service_checkcmd_args_relations` (
	`service_id` int(11) NOT NULL,
	`arg_number` tinyint(3) NOT NULL, 
	`arg_value` varchar(255) DEFAULT NULL,
	KEY `fk_service_checkcmd_args_relations` (`service_id`),
	CONSTRAINT `fk_service_checkcmd_args_relations` FOREIGN KEY (`service_id`) REFERENCES `service` (`service_id`) ON DELETE CASCADE
);
