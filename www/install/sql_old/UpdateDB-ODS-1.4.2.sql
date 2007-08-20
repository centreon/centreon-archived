-- le 01/08/2007

ALTER TABLE `log` CHANGE `msg_type` `msg_type` ENUM( '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10' ) CHARACTER SET latin1 COLLATE latin1_general_ci NOT NULL;


