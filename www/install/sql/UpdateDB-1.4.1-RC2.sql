-- 19/07/2007
DELETE FROM topology WHERE topology_page = '401';
DELETE FROM topology WHERE topology_page = '40101';
DELETE FROM topology WHERE topology_page = '40102';

DROP TABLE 'view_city';
DROP TABLE 'view_country';
DROP TABLE 'view_map';


ALTER TABLE `giv_graphs_template`
  DROP `title`,
  DROP `img_format`,
  DROP `period`,
  DROP `step`,
  DROP `default_tpl2`;
  
  