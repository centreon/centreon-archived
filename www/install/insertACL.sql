--
-- Contenu de la table `acl_groups`
--

INSERT INTO `acl_groups` (`acl_group_id`, `acl_group_name`, `acl_group_alias`, `acl_group_activate`) VALUES (14, 'Guest View', 'Guest view', '1');

--
-- Contenu de la table `acl_topology`
--

INSERT INTO `acl_topology` (`acl_topo_id`, `acl_topo_name`, `acl_topo_alias`, `acl_topo_activate`) VALUES (4, 'Home page', 'Only access on home page', '1');
INSERT INTO `acl_topology` (`acl_topo_id`, `acl_topo_name`, `acl_topo_alias`, `acl_topo_activate`) VALUES (5, 'Monitoring page', 'Only access on monitoring pages', '1');
INSERT INTO `acl_topology` (`acl_topo_id`, `acl_topo_name`, `acl_topo_alias`, `acl_topo_activate`) VALUES (6, 'Personal option pages ', 'Only access on Option pages', '1');
INSERT INTO `acl_topology` (`acl_topo_id`, `acl_topo_name`, `acl_topo_alias`, `acl_topo_activate`) VALUES (11, 'Configuration pages', 'Only access on all configuration pages', '0');
INSERT INTO `acl_topology` (`acl_topo_id`, `acl_topo_name`, `acl_topo_alias`, `acl_topo_activate`) VALUES (13, 'Graphic pages', 'Only access on graphic pages', '1');

--
-- Contenu de la table `acl_resources`
--

INSERT INTO `acl_resources` (`acl_res_id`, `acl_res_name`, `acl_res_alias`, `acl_res_activate`, `acl_res_comment`, `acl_res_status`, `changed`) VALUES (7, 'Simple view', 'Simple view', '1', NULL, NULL, 0);

--
-- Contenu de la table `acl_group_topology_relations`
--

INSERT INTO `acl_group_topology_relations` VALUES (25,14,4),(26,14,5),(28,14,6),(29,14,13);

--
-- Contenu de la table `acl_resources_hg_relations`
--

INSERT INTO `acl_resources_hg_relations` (`arhge_id`, `hg_hg_id`, `acl_res_id`) VALUES (1, 53, 7);

--
-- Contenu de la table `acl_res_group_relations`
--

INSERT INTO `acl_res_group_relations` (`argr_id`, `acl_res_id`, `acl_group_id`) VALUES (170, 7, 14);

--
-- Contenu de la table `acl_group_contacts_relations`
--

INSERT INTO `acl_group_contacts_relations` (`agcr_id`, `contact_contact_id`, `acl_group_id`) VALUES (20, 17, 14);
INSERT INTO `acl_group_contacts_relations` (`agcr_id`, `contact_contact_id`, `acl_group_id`) VALUES (21, 18, 14);

--
-- Contenu de la table `acl_topology_relations`
--

INSERT INTO `acl_topology_relations` VALUES (192,1,4),(193,185,4),(195,186,4),(196,2,5),(197,21,5),(198,23,5),(199,29,5),(200,30,5),(201,31,5),(202,22,5),(203,32,5),(204,37,5),(205,60,5),(206,61,5),(207,62,5),(208,36,5),(209,57,5),(210,58,5),(211,59,5),(212,33,5),(213,48,5),(214,49,5),(215,50,5),(216,38,5),(217,63,5),(218,64,5),(219,65,5),(220,24,5),(221,39,5),(222,40,5),(223,41,5),(224,34,5),(225,51,5),(226,52,5),(227,53,5),(228,25,5),(229,42,5),(230,43,5),(231,44,5),(232,26,5),(233,45,5),(234,46,5),(235,47,5),(236,35,5),(237,54,5),(238,55,5),(239,56,5),(240,27,5),(241,28,5),(242,17,5),(243,20,5),(244,18,5),(245,19,5),(266,5,6),(267,109,6),(268,128,6),(269,120,6),(270,121,6),(271,122,6),(272,123,6),(273,124,6),(274,125,6),(275,126,6),(276,127,6),(277,4,13),(278,146,13),(279,149,13),(280,148,13);
