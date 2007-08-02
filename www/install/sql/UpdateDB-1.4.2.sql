-- --------------------------------------------------------

--
-- Structure de la table `service_categories`
--

CREATE TABLE `service_categories` (
`sc_id` INT NULL AUTO_INCREMENT PRIMARY KEY ,
`sc_name` VARCHAR( 255 ) NULL ,
`sc_description` VARCHAR( 255 ) NULL ,
`sc_activated` ENUM( '0', '1') NULL
) ENGINE = innodb COMMENT = 'Services Catégories For best Reporting';


