--
-- Drop useless ID columns.
--
ALTER TABLE `acl_group_actions_relations` DROP COLUMN `agar_id`;
ALTER TABLE `acl_group_contactgroups_relations` DROP COLUMN `agcgr_id`;
ALTER TABLE `acl_group_contacts_relations` DROP COLUMN `agcr_id`;
ALTER TABLE `acl_group_topology_relations` DROP COLUMN `agt_id`;
ALTER TABLE `acl_res_group_relations` DROP COLUMN `argr_id`;
ALTER TABLE `acl_resources_hc_relations` DROP COLUMN `arhcr_id`;
ALTER TABLE `acl_resources_hg_relations` DROP COLUMN `arhge_id`;
ALTER TABLE `acl_resources_host_relations` DROP COLUMN `arhr_id`;
ALTER TABLE `acl_resources_hostex_relations` DROP COLUMN `arhe_id`;
ALTER TABLE `acl_resources_meta_relations` DROP COLUMN `armse_id`;
ALTER TABLE `acl_resources_poller_relations` DROP COLUMN `arpr_id`;
ALTER TABLE `acl_resources_sc_relations` DROP COLUMN `arscr_id`;
ALTER TABLE `acl_resources_service_relations` DROP COLUMN `arsr_id`;
ALTER TABLE `acl_resources_sg_relations` DROP COLUMN `asgr`;
ALTER TABLE `acl_topology_relations` DROP COLUMN `agt_id`;
ALTER TABLE `command_categories_relation` DROP COLUMN `cmd_cat_id`;
ALTER TABLE `contact_host_relation` DROP COLUMN `chr_id`;
ALTER TABLE `contact_hostcommands_relation` DROP COLUMN `chr_id`;
ALTER TABLE `contact_service_relation` DROP COLUMN `csr_id`;
ALTER TABLE `contact_servicecommands_relation` DROP COLUMN `csc_id`;
ALTER TABLE `contactgroup_contact_relation` DROP COLUMN `cgr_id`;
ALTER TABLE `contactgroup_host_relation` DROP COLUMN `cghr_id`;
ALTER TABLE `contactgroup_hostgroup_relation` DROP COLUMN `cghgr_id`;
ALTER TABLE `contactgroup_service_relation` DROP COLUMN `cgsr_id`;
ALTER TABLE `contactgroup_servicegroup_relation` DROP COLUMN `cgsgr_id`;
ALTER TABLE `dependency_hostChild_relation` DROP COLUMN `dhcr_id`;
ALTER TABLE `dependency_hostParent_relation` DROP COLUMN `dhpr_id`;
ALTER TABLE `dependency_hostgroupChild_relation` DROP COLUMN `dhgcr_id`;
ALTER TABLE `dependency_hostgroupParent_relation` DROP COLUMN `dhgpr_id`;
ALTER TABLE `dependency_metaserviceChild_relation` DROP COLUMN `dmscr_id`;
ALTER TABLE `dependency_metaserviceParent_relation` DROP COLUMN `dmspr_id`;
ALTER TABLE `dependency_serviceChild_relation` DROP COLUMN `dscr_id`;
ALTER TABLE `dependency_serviceParent_relation` DROP COLUMN `dspr_id`;
ALTER TABLE `dependency_servicegroupChild_relation` DROP COLUMN `dsgcr_id`;
ALTER TABLE `dependency_servicegroupParent_relation` DROP COLUMN `dsgpr_id`;
ALTER TABLE `escalation_contactgroup_relation` DROP COLUMN `ecgr_id`;
ALTER TABLE `escalation_host_relation` DROP COLUMN `ehr_id`;
ALTER TABLE `escalation_hostgroup_relation` DROP COLUMN `ehgr_id`;
ALTER TABLE `escalation_meta_service_relation` DROP COLUMN `emsr_id`;
ALTER TABLE `escalation_service_relation` DROP COLUMN `esr_id`;
ALTER TABLE `escalation_servicegroup_relation` DROP COLUMN `esgr_id`;
ALTER TABLE `host_hostparent_relation` DROP COLUMN `hhr_id`;
ALTER TABLE `hostcategories_relation` DROP COLUMN `hcr_id`;
ALTER TABLE `hostgroup_hg_relation` DROP COLUMN `hgr_id`;
ALTER TABLE `meta_contactgroup_relation` DROP COLUMN `mcr_id`;
ALTER TABLE `traps_service_relation` DROP COLUMN `tsr_id`;

--
-- Alter existing tables to conform with strict mode.
--
ALTER TABLE `acl_groups` MODIFY COLUMN `acl_group_changed` int(11) NOT NULL DEFAULT 1;
ALTER TABLE `widget_models` MODIFY COLUMN `description` TEXT NOT NULL;
ALTER TABLE `auth_ressource` ALTER `ar_type` SET DEFAULT 'ldap';

--
-- Remove modules *_files flags.
--
ALTER TABLE `modules_informations` DROP COLUMN `lang_files`;
ALTER TABLE `modules_informations` DROP COLUMN `sql_files`;
ALTER TABLE `modules_informations` DROP COLUMN `php_files`;

--
-- Change IP field from varchar(16) to varchar(255)
--
ALTER TABLE `remote_servers` MODIFY COLUMN `ip` VARCHAR(255) NOT NULL;

--
-- Improve chart performance
--
TRUNCATE TABLE ods_view_details;
ALTER TABLE ods_view_details MODIFY metric_id int(11);

-- Add trap filter
ALTER TABLE `traps` MODIFY COLUMN `traps_exec_interval_type` ENUM('0','1','2','3') NULL DEFAULT '0';
