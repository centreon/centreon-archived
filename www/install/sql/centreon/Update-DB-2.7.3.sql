-- Set the default number of elements for select2
INSERT INTO `options` (`key`, `value`) VALUES ('selectPaginationSize', 60);
INSERT INTO timezone (`timezone_name`, `timezone_offset`, `timezone_dst_offset`) VALUES ('GMT', '-00:00', '-00:00'), ('UTC', '-00:00', '-00:00');

-- Set possibility for non admin user with view rights access to change tab in poller view configuration
INSERT INTO topology_JS(id_t_js, id_page, o, PathName_js, Init) VALUES(NULL, 60903, 'w', './include/common/javascript/changetab.js', 'initChangeTab');

-- Add capability to list pollers in widgets #4165
INSERT INTO widget_parameters_field_type (ft_typename,is_connector) VALUES ('poller', '1');
insert into widget_parameters_field_type (ft_typename,is_connector) VALUES ('hostCategories',1);
insert into widget_parameters_field_type (ft_typename,is_connector) VALUES ('serviceCategories',1);
insert into widget_parameters_field_type (ft_typename,is_connector) VALUES ('metric',1);
