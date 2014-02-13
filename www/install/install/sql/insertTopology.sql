
/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

LOCK TABLES `menus` WRITE;
/*!40000 ALTER TABLE `menus` DISABLE KEYS */;

INSERT INTO `menus` (`menu_id`, `name`, `parent_id`, `url`, `icon_class`, `icon`, `bgcolor`, ̀ is_module`, `menu_order`) VALUES
(1,'Configuration',NULL,NULL,'fa fa-gears',NULL,'#CCC',0,1),
(2,'Commands',1,NULL,NULL,NULL,NULL,0,2),
(3,'Hosts',1,NULL,NULL,NULL,NULL,0,1),
(4,'Hosts',3,'/configuration/host',NULL,NULL,NULL,0,1),
(5,'Host templates',3,'/configuration/hosttemplate',NULL,NULL,NULL,0,2),
(6,'Hostgroups',3,'/configuration/hostgroup',NULL,NULL,NULL,0,2),
(7,'Services',1,NULL,NULL,NULL,NULL,0,2),
(8,'Services',7,'/configuration/service',NULL,NULL,NULL,0,2),
(9,'Commands',2,'/configuration/command',NULL,NULL,NULL,0,1),
(10,'Connectors',2,'/configuration/connector',NULL,NULL,NULL,0,2),
(11,'Service templates',7,'/configuration/servicetemplate',NULL,NULL,NULL,0,2),
(12,'Servicegroups',7,'/configuration/servicegroup',NULL,NULL,NULL,0,3),
(13,'Service categories',7,'/configuration/servicecategory',NULL,NULL,NULL,0,4),
(14,'Host categories',3,'/configuration/hostcategory',NULL,NULL,NULL,0,4),
(15,'User',1,'/configuration/user',NULL,NULL,NULL,0,1);

/*!40000 ALTER TABLE `menus` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

