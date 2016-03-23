--
-- table `timeperiod`
--

INSERT INTO `timeperiod` VALUES (NULL, '24x7', '24_Hours_A_Day,_7_Days_A_Week', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES (NULL, 'none', 'No Time Is A Good Time', '', '', '', '', '', '', '');
INSERT INTO `timeperiod` VALUES (NULL, 'nonworkhours', 'Non-Work Hours', '00:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-09:00,17:00-24:00', '00:00-24:00');
INSERT INTO `timeperiod` VALUES (NULL, 'workhours', 'Work hours', '', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '09:00-17:00', '');


