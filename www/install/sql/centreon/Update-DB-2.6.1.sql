ALTER TABLE `cfg_nagios` ADD COLUMN `log_pid` enum('0','1') DEFAULT '1';

-- Ticket #6035 : remove local module into centreon borker.
DELETE FROM cb_module WHERE name = 'local';

INSERT INTO `widget_parameters_field_type` (`ft_typename`, `is_connector`) VALUES
                                           ('ba', 1),
                                           ('bv', 1);
