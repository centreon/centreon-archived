<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus - Christophe Coraboeuf

The Software is provided to you AS IS and WITH ALL FAULTS.
OREON makes no representation and gives no warranty whatsoever,
whether express or implied, and without limitation, with regard to the quality,
safety, contents, performance, merchantability, non-infringement or suitability for
any particular or intended purpose of the Software found on the OREON web site.
In no event will OREON be liable for any direct, indirect, punitive, special,
incidental or consequential damages however they may arise and even if OREON has
been previously advised of the possibility of such damages.

For information : contact@oreon-project.org
*/

/*
Ce fichier contient le contenu texte utilis&eacute; pour l'outil Oreon. Les tables de hash permettent facilement de cr&eacuteer un programme multi-langage.
Les variables pr&eacute;sentes sont parlantes. Il ne devrait pas exister de difficult&eacute;s pour savoir &agrave; quoi elles correspondent.
*/

/* Error Code */

$lang['not_allowed'] = "Vous n'&ecirc;tes pas autoris&eacute; &agrave; acc&eacute;der &agrave; cette page";
$lang['not_dbPPConnect'] = "Probl&egrave;me avec la base Perfparse";
$lang['errCode'][-2] = "La d&eacute;finition n&#146;est pas compl&egrave;te";
$lang['errCode'][-3] = "Cette d&eacute;finition existe d&eacute;ja";
$lang['errCode'][-4] = "Le format de l'email est invalid, xxx@xxx.xx";
$lang['errCode'][-5] = "La d&eacute;finition est circulaire";
$lang['errCode'][-6] = "Vous devez choisir soit un Host, soit un Hostgroup";
$lang['errCode'][-7] = "Le mot de passe est incorrect";
$lang['errCode'][-8] = "La date de d&eacute;but doit &ecirc;tre inf&eacute;rieur &agrave; la date de fin";
$lang['errCode'][-9] = "Des valeurs sont manquantes";
$lang['errCode'][2] = "La d&eacute;finition a &eacute;t&eacute; modifi&eacute;e";
$lang['errCode'][3] = "La d&eacute;finition a &eacute;t&eacute; enregistr&eacute;e";
$lang['errCode'][4] = "Le mot de passe a &eacute;t&eacute; modifi&eacute;";
$lang['errCode'][5] = "L&#146;Host a &eacute;t&eacute; dupliqu&eacute;";

# Menu Level 1

$lang['m_home'] = "Accueil";
$lang['m_configuration'] = "Configuration";
$lang['m_monitoring'] = "Monitoring";
$lang['m_reporting'] = "Reporting";
$lang['m_views'] = "Vues Oreon";
$lang['m_options'] = "Options";
$lang['m_logout'] = "D&eacute;connexion";
$lang['m_help'] = "Aide";

# Menu Level 3

$lang['m_main_menu'] = "Menu Principal";
$lang['m_connected_users'] = "Connect&eacute;s";

# Monitoring menu

$lang['m_host_detail'] = "Hosts D&eacute;tails";
$lang['m_hosts_problems'] = "Hosts Probl&egrave;mes";
$lang['m_hostgroup_detail'] = "HostGroups D&eacute;tails";

$lang['m_service_detail'] = "Services D&eacute;tails";
$lang['m_services_problems'] = "Services Probl&egrave;mes";
$lang['m_servicegroup_detail'] = "ServiceGroups D&eacute;tails";
$lang['m_service_by_service_group'] = "Services par Svc Grp";

$lang['m_status_scheduling'] = "Ordonnancement";
$lang['m_status_summary'] = "Sommaire des Status";
$lang['m_status_resume'] = "R&eacute;sum&eacute; des Status";

$lang['m_status_grid'] = "Grille de Status";
$lang['m_scheduling'] = "Ordonnancement";

$lang['m_tools'] = "Outils";
$lang['m_process_info'] = "Informations sur les Processus";
$lang['m_event_log'] = "Gestionnaire d'&eacute;v&egrave;nements";
$lang['m_downtime'] = "Temps d'arrets";
$lang['m_comments'] = "Commentaires";

$lang['m_alerts'] = "Alertes";

# Log Menu

$lang['m_all_logs'] = "Tous les Logs";
$lang['m_notify_logs'] = "Notifications";
$lang['m_alerts_log'] = "Alertes";
$lang['m_warning_log'] = "Erreurs/Avertissements";

# Reporting menu

$lang['m_report'] = "Rapports";
$lang['m_rtList'] = "Listes des rapports";
$lang['m_rtStat'] = "Statistiques";

$lang['m_rtNotif'] = "Diffusion";
$lang['m_rtMailList'] = "Liste de diffusion";
$lang['m_rtMail'] = "Base Mail";

$lang['m_message'] = "Message";
$lang['m_status_map'] = "Carte de Status des Hosts";
$lang['m_cartography'] = "Cartographie";

$lang['m_dashboard'] = "Dashboard";
$lang['m_dashboardHost'] = "Host";
$lang['m_dashboardService'] = "Service";

# Graph menu

$lang['m_views_loc'] = "Localisation";
$lang['m_views_cty'] = "Pays & Villes";
$lang['m_views_map'] = "Cartes";
$lang['m_views_graphs'] = "Graphs";
$lang['m_views_graphCustom'] = "Graphs Personnalis&eacute;s";
$lang['m_views_graphShow'] = "Graphs Simples";
$lang['m_views_graphPlu'] = "Sondes Graphiques";
$lang['m_views_graphTmp'] = "Graphs Templates";
$lang['m_views_compoTmp'] = "Courbes Templates";
#$lang['m_views_mine'] = "Mes Vues";

# Options menu

$lang['m_opt_conf'] = "Oreon";
$lang['m_general'] = "Options G&eacute;n&eacute;rales";
$lang['m_lang'] = "Langues";
$lang['m_modules'] = "Modules";
$lang['m_plugins'] = "Sondes";
$lang['m_myAccount'] = "Mon Compte";

$lang['m_acl'] = "LCA";
$lang['lca_list'] = "Listes de controles d'acc&egrave;s";

$lang['m_db'] = "Database";
$lang['m_extract_db'] = "Extraction de la Database";

$lang['m_server_status'] = "Syst&egrave;me";

$lang['m_about'] = "A propos...";
$lang['m_web'] = "Site Web";
$lang['m_forum'] = "Forum";
$lang['m_wiki'] = "Wiki";
$lang['m_bug'] = "Bug Track";
$lang['m_donate'] = "Donation";
$lang['m_pro'] = "Professionel";

$lang['m_sessions'] = "Sessions";

# Configuration menu

$lang['m_host'] = "Hosts";
$lang['m_hostgroup'] = "Host Groups";
$lang['m_host_extended_info'] = "Host Informations Etendues";

$lang['m_service'] = "Services";
$lang['m_serviceByHost'] = "Services par Host";
$lang['m_serviceByHostGroup'] = "Services par Host Group";
$lang['m_servicegroup'] = "Service Groups";
$lang['m_service_extended_info'] = "Service Informations Etendues";
$lang['m_meta_service'] = "Meta Services";

$lang['m_notification'] = "Utilisateurs";
$lang['m_contact'] = "Utilisateurs";
$lang['m_contactgroup'] = "Groupes Utilisateurs";
$lang['m_timeperiod'] = "Plage Horaires";

$lang['m_escalation'] = "Escalades";
$lang['m_hostgroupesc'] = "Host Group Escalades";
$lang['m_hostesc'] = "Host Escalades";
$lang['m_serviceesc'] = "Service Escalades";
$lang['m_metaserviceesc'] = "Meta Service Escalades";

$lang['m_dependencies'] = "D&eacute;pendances";
$lang['m_service_dependencies'] = "D&eacute;pendances de Service";
$lang['m_host_dependencies'] = "D&eacute;pendances d'Host";

$lang['m_template'] = "Mod&egrave;les";
$lang['m_host_template_model'] = "Mod&egrave;les d'Host";
$lang['m_service_template_model'] = "Mod&egrave;les de Service";

$lang['m_nagios'] = "Nagios";
$lang['m_nagiosCFG'] = "Nagios CFG";
$lang['m_cgi'] = "CGI CFG";
$lang['m_resource'] = "Resource CFG";
$lang['m_perfparse'] = "Perfparse CFG";
$lang['m_load_nagios'] = "Importer";
$lang['m_gen_nagios'] = "Exporter";

$lang['m_commandNotif'] = "Commandes de Notification";
$lang['m_commandCheck'] = "Commandes de V&eacute;rifcations";
$lang['m_commandMisc'] = "Commandes Diverses";
$lang['m_commands'] = "Commandes";


/* ID Menu */

$lang['m_idCards'] = "Fiches d'identit&eacute;s";
$lang['m_id_serv'] = "Serveurs";
$lang['m_id_network'] = "Equipements Reseaux";
$lang['m_idUpdate'] = "Mise a jour manuelle";
$lang['m_id_manu'] = "Constructeur";

/* Monitoring */

$lang['mon_last_update'] = "Derni&egrave;re mise &agrave; jour :";
$lang['mon_up'] = "UP";
$lang['mon_down'] = "DOWN";
$lang['mon_unreachable'] = "INACCESSIBLE";
$lang['mon_ok'] = "OK";
$lang['mon_critical'] = "CRITIQUE";
$lang['mon_warning'] = "ATTENTION";
$lang['mon_pending'] = "EN SUSPENS";
$lang['mon_unknown'] = "INCONNU";
$lang['mon_status'] = "Status";
$lang['mon_ip'] = "IP";
$lang['mon_last_check'] = "Dernier controle";
$lang['mon_next_check'] = "Prochain controle";
$lang['mon_active_check'] = "Check actif";
$lang['mon_duration'] = "Dur&eacute;e";
$lang['mon_retry'] = "Essai";
$lang['mon_status_information'] = "Informations";
$lang['mon_service_overview_fah'] = "Vue d'ensemble des services pour les Host Groups";
$lang['mon_service_overview_fas'] = "Vue d'ensemble des services pour les Service Groups";
$lang['mon_status_summary_foh'] = "Informations de status de tous les Host Groups";
$lang['mon_status_grid_fah'] = "Grille de status pour tous les Host Groups";
$lang['mon_sv_hg_detail1'] = "D&eacute;tail des services";
$lang['mon_sv_hg_detail2'] = "pour l&#146;Host Group";
$lang['mon_sv_hg_detail3'] = "pour l&#146;Host";
$lang['mon_host_status_total'] = "Total Host Status";
$lang['mon_service_status_total'] = "Total Service Status";
$lang['mon_scheduling'] = "Ordre d'ordonnancement";
$lang['mon_actions'] = "Actions";
$lang['mon_active'] = "ACTIF";
$lang['mon_inactive'] = "INACTIF";
$lang['mon_request_submit_host'] = "Votre demande a bien &eacute;t&eacute; trait&eacute;e. <br><br>Vous allez &ecirc;tre redirig&eacute; vers la page des Hosts.";
$lang['Details'] = "D&eacute;tails";
$lang['mon_checkOutput'] = "check output";
$lang['mon_dataPerform'] = "data perform";

/* Monitoring commands */

$lang['mon_hg_commands'] = "Commandes pour l'Host Group";
$lang['mon_h_commands'] = "Commandes pour l'Host";
$lang['mon_sg_commands'] = "Commandes pour le Service Group";
$lang['mon_s_commands'] = "Commandes pour le Service";
$lang['mon_no_stat_for_host'] = "Pas de stat pour cet Host.<br><br>Pensez &agrave; g&eacute;n&eacute;rer les fichiers de configuration.";
$lang['mon_no_stat_for_service'] = "Aucune stat pour ce service.<br><br> Pensez a gnrer les fichiers de configuration.";
$lang['mon_hg_cmd1'] = "Programmer un arr&ecirc;t pour tous les Hosts de ce Host Group";
$lang['mon_hg_cmd2'] = "Programmer un arr&ecirc;t pour tous les Services de cet Host Group";
$lang['mon_hg_cmd3'] = "Activer les notifications pour tous les Hosts de cet Host Group";
$lang['mon_hg_cmd4'] = "D&eacute;sactiver les notifications pour tous les Hosts de cet Host Group";
$lang['mon_hg_cmd5'] = "Activer les notifications pour tous les Services de cet Host Group";
$lang['mon_hg_cmd6'] = "D&eacute;sactiver les notifications pour tous les Services de cet Host Group";
$lang['mon_hg_cmd7'] = "Activer les checks pour tous les Services de cet Host Group";
$lang['mon_hg_cmd8'] = "D&eacute;sactiver les checks pour les Services de cet Host Group";
$lang['mon_host_state_info'] = "Information sur l&#146;&eacute;tat de l&#146;Host";
$lang['mon_hostgroup_state_info'] = "Information sur l&#146;&eacute;tat de l&#146;Hostgroup";
$lang['mon_host_status'] = "Etat de l&#146;Host";
$lang['mon_status_info'] = "Informations sur l&#146;&eacute;tat";
$lang['mon_last_status_check'] = "Last Status Check";
$lang['mon_status_data_age'] = "Status Data Age";
$lang['mon_current_state_duration'] = "Current State Duration";
$lang['mon_last_host_notif'] = "Last Host Notification";
$lang['mon_current_notif_nbr'] = "Current Notification Number";
$lang['mon_is_host_flapping'] = "Is This Host Flapping ?";
$lang['mon_percent_state_change'] = "Percent State Change";
$lang['mon_is_sched_dt'] = "Est ce qu&#146;un arr&ecirc;t est programm&eacute; ?";
$lang['mon_sch_imm_cfas'] = "Programmer un check imm&eacute;diat pour tous les services";
$lang['mon_sch_dt'] = "Arr&ecirc;t programm&eacute;";
$lang['mon_dis_notif_fas'] = "D&eacute;sactiver la notification pour tous les Services";
$lang['mon_enable_notif_fas'] = "Activer la notification pour tous les Services";
$lang['mon_dis_checks_fas'] = "D&eacute;sactiver les checks pour tous les Services";
$lang['mon_enable_checks_fas'] = "Activer les checks pour tous les Services";
$lang['mon_service_state_info'] = "Information sur l&#146;&eacute;tat du Service";
$lang['mon_service_status'] = "Etat du Service";
$lang['mon_current_attempt'] = "Current Attempt";
$lang['mon_state_type'] = "State Type";
$lang['mon_last_check_type'] = "Dernier type de check";
$lang['mon_last_check_time'] = "Derni&egrave;re heure de check";
$lang['mon_next_sch_active_check'] = "Prochain check actif programm&eacute;";
$lang['mon_last_service_notif'] = "Derni&egrave;re notification de Service";
$lang['mon_is_service_flapping'] = "Is This Service Flapping ?";
$lang['mon_checks_for_service'] = "check de ce service";
$lang['mon_accept_pass_check'] = "les passives checks pour ce Service";
$lang['mon_notif_service'] = "les notifications pour ce Service";
$lang['mon_eh_service'] = "l&#146;event handler pour ce Service";
$lang['mon_fp_service'] = "Flap detection pour ce Service";
$lang['mon_submit_pass_check_service'] = "Submit passive check result for this service";
$lang['mon_sch_dt_service'] = "Programmer un arr&ecirc;t pour ce Service";
$lang['mon_service_check_executed'] = "Les checks de Service ont &eacute;t&eacute; ex&eacute;cut&eacute;s";
$lang['mon_passive_service_check_executed'] = "Les checks passifs de Service ont &eacute;t&eacute; ex&eacute;cut&eacute;s";
$lang['mon_eh_enabled'] = "Event Handlers activ&eacute;s";
$lang['mon_obess_over_services'] = "Obsessing Over Services";
$lang['mon_fp_detection_enabled'] = "Flap Detection Enabled";
$lang['mon_perf_data_process'] = "Performance Data Being Processed";
$lang['mon_process_infos'] = "Informations sur les processus";
$lang['mon_process_start_time'] = "D&eacute;but du programme";
$lang['mon_total_run_time'] = "Temps total de fonctionnement";
$lang['mon_last_ext_command_check'] = "Last External Command Check";
$lang['mon_last_log_file_rotation'] = "Last Log File Rotation";
$lang['mon_nagios_pid'] = "Nagios PID";
$lang['mon_process_cmds'] = "Process Commands";
$lang['mon_stop_nagios_proc'] = "Arr&eacute;ter le processus Nagios";
$lang['mon_start_nagios_proc'] = "D&eacute;marrer le processus Nagios";
$lang['mon_restart_nagios_proc'] = "Red&eacute;marrer le processus Nagios";
$lang['mon_proc_options'] = "Options des processus";
$lang['mon_notif_enabled'] = "Notifications activ&eacute;es";
$lang['mon_notif_disabled'] = "Notifications D&eacute;sactiv&eacute;es";
$lang['mon_service_check_disabled'] = "Check Service D&eacute;sactiv&eacute;";
$lang['mon_service_check_passice_only'] = "Passive Check Uniquement";
$lang['mon_service_view_graph'] = "Visualisation du graphique";
$lang['mon_service_sch_check'] = "Programmer un check imm&eacute;diat pour ce service";

/* comments */

$lang['cmt_service_comment'] = "Commentaires de Services";
$lang['cmt_host_comment'] = "Commentaires d&#146;Hosts";
$lang['cmt_addH'] = "Ajouter une commentaired'Host";
$lang['cmt_addS'] = "Ajouter un commentaire de Service";
$lang['cmt_added'] = "Commentaire ajout&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=307' class='text11b'>ici</a> pour retourner a la page des commentaires ";
$lang['cmt_del'] = "Commentaire supprim&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=307' class='text11b'>ici</a> pour retourner a la page des commentaires ";
$lang['cmt_del_all'] = "Tous les Commentaires ont &eacute;t&eacute; supprim&eacute;s avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=307'>ici</a> pour retourner a la page des commentaires ";
$lang['cmt_host_name'] = "Nom de l&#146;Host";
$lang['cmt_service_descr'] = "Services";
$lang['cmt_entry_time'] = "Date de saisie";
$lang['cmt_author'] = "Auteur";
$lang['cmt_comment'] = "Commentaire";
$lang['cmt_persistent'] = "Persistent";
$lang['cmt_actions'] = "Actions";

/* downtimes */

$lang['dtm_addH'] = "Ajouter un arr&ecirc;t d&#146;Host";
$lang['dtm_addS'] = "Ajouter un arr&ecirc;t de Service";
$lang['dtm_addHG'] = "Ajouter un arr&ecirc;t de Host Group";
$lang['dtm_added'] = "Downtime ajout&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=308' class='text11b'>ici</a> pour retourner a la page des Downtimes ";
$lang['dtm_del'] = "Downtime supprim&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=308' class='text11b'>ici</a> pour retourner a la page des Downtimes ";
$lang['dtm_start_time'] = "Date de d&eacute;but";
$lang['dtm_end_time'] = "Date de fin";
$lang['dtm_fixed'] = "Fix&eacute;";
$lang['dtm_duration'] = "Dur&eacute;e";
$lang['dtm_sch_dt_fht'] = "Programmer un arr&ecirc;t pour les Hosts &eacute;galement";
$lang['dtm_host_downtimes'] = "Arr&ecirc;ts de l&#146;Host";
$lang['dtm_service_downtimes'] = "Arr&ecirc;ts du Service";
$lang['dtm_dt_no_file'] = "Fichier d&#146;arr&ecirc;t introuvable";
$lang['dtm_host_delete'] = "Effacer l&#146;arr&ecirc;t d&#146;Host";

/* cmd externe */

#$lang['cmd_utils'] = 'Utilitaires';
#$lang['cmd_send'] = "Votre commande a &eacute;t&eacute; envoy&eacute;e.";
#$lang['cmd_ping'] = "Ping";
#$lang['cmd_traceroute'] = "Traceroute.";

/* actions & recurrent text */

$lang['home'] = "Accueil ";
$lang['oreon'] = "Oreon";
$lang['add'] = "Ajouter";
$lang['dup'] = "Dupliquer";
$lang['save'] = "Sauvegarder";
$lang['modify'] = "Modifier";
$lang['mchange'] = "Changement Massif";
$lang['delete'] = "Supprimer";
$lang['update'] = "Mettre &agrave; jour";
$lang['ex'] = "Exemple ";
$lang['name'] = "Nom ";
$lang['alias'] = "Alias";
$lang['user'] = "Utilisateur ";
$lang['here'] = "ici";
$lang['this'] = "celui-ci";
$lang['confirm_removing'] = "Validez vous cette suppression ?";
$lang['confirm_duplication'] = "Validez vous cette duplication ?";
$lang['confirm_mchange'] = "Validez vous ce changement massif ?";
$lang['confirm_update'] = "Etes vous s&ucirc;r de vouloir mettre &agrave; jour la traffic map ?";
$lang['file_exist'] = "D&eacute;sol&eacute; le fichier existe d&eacute;j&agrave;.";
$lang['uncomplete_form'] = "Formulaire incomplet ou erron&eacute;";
$lang['none'] = "Aucun";
$lang['already_logged'] = "Vous &ecirc;tes d&eacute;j&agrave; connect&eacute; &agrave; OREON, fermez d&acute;abord l&acute;autre session <br>Si c&acute;est la seule fen&ecirc;tre d&acute;ouverte, cliquez<br><a href='?disconnect=1' class='text11b'>ici</a>";
$lang['usage_stats'] = "Statistiques d'utilisation";
$lang['check'] = "Cocher";
$lang['uncheck'] = "D&eacute;cocher";
$lang['options'] = "Options";
$lang['status'] = "Etat";
$lang['status_options'] = "Etat et Options";
$lang['details'] = "D&eacute;tails";
$lang['back'] = "Retour";
$lang['view'] = "Voir";
$lang['choose'] = "Choisir";
$lang['enable'] = "Activ&eacute;";
$lang['disable'] = "D&eacute;sactiv&eacute;";
$lang['yes'] = "Oui";
$lang['no'] = "Non";
$lang['description'] = "Description";
$lang['page'] = "Page";
$lang['required'] = "<font color='red'>*</font> obligatoire";
$lang['nbr_per_page'] = "Limite";
$lang['reset'] = "Effacer";
$lang['time_sec'] = " secondes ";
$lang['time_min'] = " minutes ";
$lang['time_hours'] = " Heures ";
$lang['time_days'] = " Jours ";
$lang['size'] = "Taille";
$lang['close'] = "Fermer";
$lang['forTheSelectedElements'] = "Pour la s&eacute;lection : ";
$lang['mc_mod'] = "Type de mise &agrave; jour";
$lang['mc_mod_incremental'] = "Incrementiel";
$lang['mc_mod_replacement'] = "Remplacement";

/* db */

$lang['db_extract'] = "Extraire";
$lang['db_execute'] = "Executer";
$lang['db_save'] = "Sauvegarder";
$lang['DB_status'] = "Statistiques de la base de Donn&eacute;es";
$lang['db_lenght'] = "Taille";
$lang['db_nb_entry'] = "Nombre D'entr&eacute;es";

/* Parseenevlog */

$lang['hours'] = "Heures";

/* user Online */

$lang['wi_user'] = "Utilisateurs";
$lang['wi_where'] = "Localisation";
$lang['wi_last_req'] = "Derni&egrave;re Requ&ecirc;te";

/* About */

$lang['developped'] = "D&eacute;velopp&eacute; par";

/* Date and Time Format */

$lang['date_format'] = "d/m/Y";
$lang['time_format'] = "H:i:s";
$lang['header_format'] = "d/m/Y G:i";
$lang['date_time_format'] = "d/m/Y - H:i:s";
$lang['date_time_format_status'] = "d/m/Y H:i:s";
$lang['date_time_format_g_comment'] = "d/m/Y H:i";

/* legend */

$lang['lgd_legend'] = " L&eacute;gende";
$lang['lgd_force'] = " Forcer une v&eacute;rification";
$lang['lgd_graph'] = " Graph";
$lang['lgd_passiv'] = " V&eacute;rification passive activ&eacute;e";
$lang['lgd_desactivated'] = " El&eacute;ment D&eacute;sactiv&eacute;";
$lang['lgd_notification'] = "Notification d&eacute;sactiv&eacute;e";
$lang['lgd_work'] = " Status courant pris en compte";
$lang['lgd_delOne'] = " Supprimer";
$lang['lgd_delAll'] = " Supprimer";
$lang['lgd_duplicate'] = " Dupliquer";
$lang['lgd_view'] = " Voir";
$lang['lgd_play'] = " Play";
$lang['lgd_pause']= " Pause";
$lang['lgd_refresh'] = " Rafraichir";
$lang['lgd_edit'] = " Modifier";
$lang['lgd_signpost'] = " D&eacute;tail";
$lang['lgd_next'] = " Suivant";
$lang['lgd_prev'] = " Pr&eacute;c&eacute;dent";
$lang['lgd_on'] = " Activer";
$lang['lgd_off'] = " D&eacute;sactiver";
$lang['advanced'] = "Options Avancees >>";
$lang['quickFormError'] = "Impossible de valider, un ou plusieurs champs sont erron&eacute;s";
$lang['lgd_more_actions'] = " Plus d'actions...";

?>