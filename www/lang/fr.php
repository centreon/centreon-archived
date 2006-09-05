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

$lang["m_main_menu"] = "Menu Principal";
$lang["m_connected_users"] = "Connect&eacute;s";

# Monitoring menu

$lang["m_host_detail"] = "Hosts D&eacute;tails";
$lang["m_hosts_problems"] = "Hosts Probl&egrave;mes";
$lang["m_hostgroup_detail"] = "HostGroups D&eacute;tails";

$lang["m_service_detail"] = "Services D&eacute;tails";
$lang["m_services_problems"] = "Services Probl&egrave;mes";
$lang["m_servicegroup_detail"] = "ServiceGroups D&eacute;tails";
$lang["m_service_by_service_group"] = "Services par Svc Grp";

$lang["m_status_scheduling"] = "Ordonnancement";
$lang["m_status_summary"] = "Sommaire des Status";
$lang["m_status_resume"] = "R&eacute;sum&eacute; des Status";

$lang["m_status_grid"] = "Grille de Status";
$lang["m_scheduling"] = "Ordonnancement";

$lang['m_tools'] = "Outils";
$lang["m_process_info"] = "Informations sur les Processus";
$lang["m_event_log"] = "Gestionnaire d'&eacute;v&egrave;nements";
$lang["m_downtime"] = "Temps d'arrets";
$lang["m_comments"] = "Commentaires";

$lang["m_alerts"] = "Alertes";

# Log Menu

$lang["m_all_logs"] = "Tous les Logs";
$lang["m_notify_logs"] = "Notifications";
$lang["m_alerts_log"] = "Alertes";
$lang["m_warning_log"] = "Erreurs/Avertissements";

# Reporting menu

$lang["m_report"] = "Rapports";
$lang["m_rtList"] = "Listes des rapports";
$lang["m_rtStat"] = "Statistiques";

$lang["m_rtNotif"] = "Diffusion";
$lang["m_rtMailList"] = "Liste de diffusion";
$lang["m_rtMail"] = "Base Mail";

$lang["m_message"] = "Message";
$lang["m_status_map"] = "Carte de Status des Hosts";
$lang["m_cartography"] = "Cartographie";

$lang["m_dashboard"] = "Dashboard";
$lang["m_dashboardHost"] = "Host";
$lang["m_dashboardService"] = "Service";

# Graph menu

$lang['m_views_loc'] = "Localisation";
$lang['m_views_cty'] = "Pays & Villes";
$lang['m_views_map'] = "Cartes";
$lang['m_views_graphs'] = "Moteur Graphique";
$lang['m_views_graphCustom'] = "Graphs Personnalis&eacute;s";
$lang['m_views_graphShow'] = "Graphs Simples";
$lang['m_views_graphPlu'] = "Sondes Graphiques";
$lang['m_views_graphTmp'] = "Graphs Templates";
$lang['m_views_compoTmp'] = "Courbes Templates";
$lang['m_views_mine'] = "Mes Vues";

# Options menu

$lang['m_opt_conf'] = "Oreon";
$lang['m_general'] = "Options G&eacute;n&eacute;rales";
$lang['m_lang'] = "Langues";
$lang['m_menu'] = "Menu";
$lang['m_plugins'] = "Sondes";
$lang['m_myAccount'] = "Mon Compte";

$lang['m_acl'] = "LCA";
$lang["lca_list"] = "Listes de controles d'acc&egrave;s";

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

$lang["m_idCards"] = "Fiches d'identit&eacute;s";
$lang["m_id_serv"] = "Serveurs";
$lang["m_id_network"] = "Equipements Reseaux";
$lang["m_idUpdate"] = "Mise a jour manuelle";
$lang["m_id_manu"] = "Constructeur";

/* Plugins */

$lang["plugins1"] = "Sonde effac&eacute;e";
$lang["plugins2"] = "Etes vous sur de vouloir supprimer cette sonde ? ";
$lang["plugins3"] = "Sonde envoy&eacute;e";
$lang["plugins4"] = "Une erreur est survenue durant l'enregistrement de la sonde. Il s&#146;agit peut etre d'un probl&egrave;me de droits";
$lang["plugins5"] = "Une erreur est survenue durant l'enregistrement du fichier &#146;oreon.conf&#146;. Il s&#146;agit peut etre d'un probl&egrave;me de droits";
$lang["plugins6"] = "Fichier g&eacute;n&eacute;r&eacute;";
$lang["plugins_add"] = "Ajout de sondes pour Nagios";
$lang["plugins"] = "Sondes";
$lang["plugins_list"] = "Liste des sondes";
$lang["plugins_pm_conf"] = "Oreon.conf";
$lang["plugins_pm_conf_desc"] = "G&eacute;n&eacute;re le fichier de configuration pour &#146;Oreon.pm&#146; avec les informations contenues dans le menu G&eacute;n&eacute;ral";

/* index100 */

$lang['ind_infos'] = "Dans cette partie vous pouvez configurer toutes les ressources de Nagios.";
$lang['ind_detail'] = "Les ressources sont li&eacute;es entre elles pour la plupart, n&acute;oubliez pas que la suppression d&acute;une ressource peut impacter, <br>et donc supprimer de nombreuses autres.";

/* index */

$lang['ind_first'] = "Vous &ecirc;tes d&eacute;j&agrave; connect&eacute; &agrave; OREON, fermez d&acute;abord l&acute;autre session<br>Si c&acute;est la seule fen&ecirc;tre d&acute;ouverte, cliquez";

/* alt main */

$lang['am_intro'] = "g&egrave;re actuellement :";
$lang['host_health'] = "Etat des ressources";
$lang['service_health'] = "Etat des services";
$lang['network_health'] = "Etat du r&eacute;seau";
$lang['am_hg_vdetail'] = 'Voir le d&eacute;tail par Hostgroup';
$lang['am_sg_vdetail'] = 'Voir le d&eacute;tail par Servicegroup';
$lang['am_hg_detail'] = 'D&eacute;tails des Hostgroup';
$lang['am_sg_detail'] = 'D&eacute;tails des Servicegroup';

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
$lang['mon_status_info'] = "Informations sur l&#146;&eacute;tat";
$lang['mon_last_status_check'] = "Last Status Check";
$lang['mon_status_data_age'] = "Status Data Age";
$lang['mon_current_state_duration'] = "Current State Duration";
$lang['mon_last_host_notif'] = "Last Host Notification";
$lang['mon_current_notif_nbr'] = "Current Notification Number";
$lang['mon_is_host_flapping'] = "Is This Host Flapping ?";
$lang['mon_percent_state_change'] = "Percent State Change";
$lang['mon_is_sched_dt'] = "Est ce qu&#146;un arr&ecirc;t est programm&eacute; ?";
$lang['mon_last_update'] = "Derni&egrave;re mise &agrave; jour";
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
$lang['mon_percent_state_change'] = "Percent State Change";
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
$lang['mon_request_submit_host'] = "Your request has been recorded<br><br>You&#146;re gonna be redirected to the host page.";
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
$lang["cmt_added"] = "Commentaire ajout&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=307' class='text11b'>ici</a> pour retourner a la page des commentaires ";
$lang["cmt_del"] = "Commentaire supprim&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=307' class='text11b'>ici</a> pour retourner a la page des commentaires ";
$lang["cmt_del_all"] = "Tous les Commentaires ont &eacute;t&eacute; supprim&eacute;s avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=307'>ici</a> pour retourner a la page des commentaires ";
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
$lang["dtm_added"] = "Downtime ajout&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=308' class='text11b'>ici</a> pour retourner a la page des Downtimes ";
$lang["dtm_del"] = "Downtime supprim&eacute; avec succ&egrave;s .<br><br> Cliquez <a href='./oreon.php?p=308' class='text11b'>ici</a> pour retourner a la page des Downtimes ";
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

$lang['cmd_utils'] = 'Utilitaires';
$lang["cmd_send"] = "Votre commande a &eacute;t&eacute; envoy&eacute;e.";
$lang["cmd_ping"] = "Ping";
$lang["cmd_traceroute"] = "Traceroute.";

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

/* Load Nagios CFG */

$lang['nfc_generated_by_oreon'] = 'Est ce que les fichiers ont &eacute;t&eacute; g&eacute;n&eacute;r&eacute;s par Oreon ?';
$lang['nfc_targz'] = 'Vous devez charger une archive tar.gz';
$lang['nfc_limit'] = 'Pour charger une configuration de Nagios vous devez :<ul><li>Specifier au moins les fichiers misccommands.cfg et checkcommands.cfg</li><li>Les autres d&eacute;finitions peuvent &ecirc;tre plac&eacute;es dans n&#146;importe quelle fichier .cfg</li><li>Oreon ne g&egrave;re pas les Nagios time-saving tricks</li></ul>';
$lang['nfc_enum'] = "Hosts, services, contacts, commands, escalations, templates....";
$lang['nfc_ncfg'] = "Nagios.cfg";
$lang['nfc_rcfg'] = "Resource.cfg";
$lang['nfc_ncfgFile'] = "Nagios.cfg fichier";
$lang['nfc_rcfgFile'] = "Resource.cfg fichier";
$lang['nfc_fileUploaded'] = "Fichiers upload&eacute;s avec succ&egrave;s";
$lang['nfc_extractComplete'] = "Extraction Compl&egrave;te";
$lang['nfc_unzipComplete'] = "Unzip Complet";
$lang['nfc_unzipUncomplete'] = "Unzip Incomplet";
$lang['nfc_uploadComplete'] = "Upload Compl&egrave;t";

/* profile */

$lang['profile_h_name'] = "Nom";
$lang['profile_h_contact'] = "Contact";
$lang['profile_h_location'] = "Lieu";
$lang['profile_h_uptime'] = "Uptime";
$lang['profile_h_os'] = "Systeme d&#146;exploitation";
$lang['profile_h_interface'] = "Interface";
$lang['profile_h_ram'] = "M&eacute;moire";
$lang['profile_h_disk'] = "Disque";
$lang['profile_h_software'] = "Logiciels";
$lang['profile_h_update'] = "Mises &agrave; jour";
$lang['profile_s_network'] = "Par r&eacute;seau";
$lang['profile_s_os'] = "Par syst&egrave;me d'exploitation";
$lang['profile_s_software'] = "Par logiciel";
$lang['profile_s_update'] = "Par mises &agrave; jour";
$lang['profile_s_submit'] = "rechercher";
$lang['profile_o_system'] = "Syst&egrave;me";
$lang['profile_o_network'] = "R&eacute;seau";
$lang['profile_o_storage'] = "Espace disque";
$lang['profile_o_software'] = "Logicels";
$lang['profile_o_live_update'] = "Mise &agrave; jour des informations";
$lang['profile_h_ip'] = "IP";
$lang['profile_h_speed'] = "Vitesse";
$lang['profile_h_mac'] = "Mac";
$lang['profile_h_status'] = "Statut";
$lang['profile_h_used_space'] = "Espace utilis&eacute;";
$lang['profile_h_size'] = "Taille";
$lang['profile_h_partition'] = "Partition";
$lang['profile_h_list_host'] = "Selectionner le serveur";
$lang['profile_menu_list'] = "Serveurs";
$lang['profile_menu_search'] = "Recherche";
$lang['profile_menu_options'] = "Inventaire";
$lang['profile_search_results'] = "R&eacute;sultat de la recherche pour :";
$lang['profile_title_partition'] = "Partition";
$lang['profile_title_size'] = "Taille";
$lang['profile_title_used_space'] = "Espace Utilis&eacute;";
$lang['profile_title_free_space'] = "Espace Libre";
$lang['profile_error_snmp'] = "La machine cible ne r&eacute;pond pas aux requ&ecirc;tes SNMP";

/* db */

$lang['db_cannot_open'] = "Impossible d&acute;ouvrir le fichier :";
$lang['db_cannot_write'] = "Impossible d&acute;&eacute;crire dans le fichier :";
$lang['db_genesis'] = "G&eacute;n&eacute;rer les fichiers de configuration";
$lang['db_file_state'] = "Etat des fichiers de configurations g&eacute;n&eacute;r&eacute;s par Oreon :";
$lang['db_create_backup'] = "Cr&eacute;er une sauvegarde avant de cr&eacute;er les nouveaux fichiers de configuration";
$lang['db_create'] = "Cr&eacute;er une nouvelle sauvegarde";
$lang['db_generate'] = "G&eacute;n&eacute;rer";
$lang['db_nagiosconf_backup'] = "Sauvegarde des Configurations de Nagios ";
$lang['db_backup'] = "Sauvegarde de toute la base de donn&eacute;es destin&eacute;e &agrave; Oreon";
$lang['db_nagiosconf_backup_on_server'] = "Sauvegader les configurations de nagios sur le serveur (permet par la suite de les remettre en marche).";
$lang['db_backup_spec_users'] = "Sauvegarder uniquement la configuration des utilisateurs ayant acc&egrave;s a l&acute;interface ";
$lang['db_insert_new_database'] = "Ins&eacute;rer une nouvelle base de donn&eacute;es";
$lang['db_reset_old_conf'] = "Remettre en service des configurations d&eacute;j&agrave; en memoire sur le serveur";
$lang['db_extract'] = "Extraire";
$lang['db_execute'] = "Executer";
$lang['db_save'] = "Sauvegarder";
$lang["DB_status"] = "Statistiques de la base de Donn&eacute;es";
$lang["db_lenght"] = "Taille";
$lang["db_nb_entry"] = "Nombre D'entr&eacute;es";

/* user */

$lang['u_list'] = "Liste des utilisateurs";
$lang['u_admin_list'] = "Liste des administrateurs";
$lang['u_sadmin_list'] = "Liste des supers administrateurs";
$lang['u_user'] = "Utilisateur";
$lang['u_administrator'] = "Administrateur";
$lang['u_sadministrator'] = "Super administrateur";
$lang['u_profile'] = "Votre profil";
$lang['u_new_profile'] = "Nouveau profil";
$lang['u_some_profile'] = "Profil de ";
$lang['u_name'] = "Nom ";
$lang['u_lastname'] = "Pr&eacute;nom ";
$lang['u_login'] = "Login ";
$lang['u_passwd'] = "Mot de passe ";
$lang['u_cpasswd'] = "Changer le mot de passe ";
$lang['u_ppasswd'] = "Confirmez le mot de passe ";
$lang['u_email'] = "e-mail ";
$lang['u_lang'] = "Langue ";
$lang['u_status'] = "Etat ";
$lang['u_delete_profile'] = "supprimer cet utilisateur";

/* lang */

$lang['lang_infos'] = "Il y a actuellement ";
$lang['lang_infos2'] = "langues diff&eacute;rentes de mises en service pour l'utilisation d&acute;Oreon.";
$lang['lang_infos3'] = "Si vous d&eacute;sirez en rajouter, c&acute;est tres simple : il suffit d&acute;uploader un fichier dans le formulaire ci-dessous. ";
$lang['lang_detail'] = "Ce fichier doit respecter les m&ecirc;mes champs que ";
$lang['lang_detail2'] = "mais traduits dans la langue d&eacute;sir&eacute;e";

/* bug resolver */

$lang['bug_infos'] = "Sur cette page vous pouvez effacer toutes les relations entre les ressources et le contenu des tables qui pr&eacute;sente s&ucirc;rement probl&egrave;me en cas de bug.";
$lang['bug_action'] = "Si vous souhaitez r&eacute;initialiser la base de donn&eacute;es parce que vous avez obtenu des bugs pendant la phase de test, merci de nous indiquer &agrave; quel endroit le bug a eu lieu. Et cliquez ici";
$lang['bug_kick'] = "R&eacute;initialiser la base";

/* Parseenevlog */

$lang['hours'] = "Heures";

/* Log report */

$lang['add_report'] = "Le report a &eacute;t&eacute; ajout&eacute;";
$lang['change_report'] = "Le report a &eacute;t&eacute; modifi&eacute;";
$lang['add_reportHost'] = "Un Host a &eacute;t&eacute; ajout&eacute;";
$lang['add_reportService'] = "L&#146;ajout de Service a &eacute;t&eacute; fait";
$lang['daily_report'] = "Rapport journalier (diff&eacute;rents formats)";
$lang['report_select_host'] = "Selectionnez un host";
$lang['report_select_service'] = "Un de ses services (facultatif)";
$lang['report_select_period'] = "Selectionnez une p&eacute;riode";
$lang['report_sp'] = "Debut de la p&eacute;riode";
$lang['report_ep'] = "Fin de la p&eacute;riode";
$lang['report_generate_pdf'] = "G&eacute;n&eacute;rer le rapport PDF";
$lang['custom_start_date'] = "date de d&eacute;but";
$lang['custom_end_date'] = "date de fin";
$lang['report_change_host'] = "Modifier l&acute;host";
$lang['custom_report'] = "Rapport personnalis&eacute;";
$lang['report_color_up'] = "Color UP";
$lang['report_color_down'] = "Color DOWN";
$lang['report_color_unreachable'] = "Color UNREACHABLE";
$lang['report_color_ok'] = "Color OK";
$lang['report_color_warning'] = "Color WARNING";
$lang['report_color_critical'] = "Color CRITICAL";
$lang['report_color_unknown'] = "Color UNKNOWN";
$lang['report_kindof_report'] = "Il existe 3 types de rapport";
$lang['report_daily_report'] = "Le rapport actuel de Nagios";
$lang['report_daily_report_explain'] = "Il s&#146;agit d&acute;interpreter le fichier";
$lang['report_daily_report_availability'] = "Disponible ici sous plusieurs formats :";
$lang['report_spec_info'] = "Le rapport d&acute;informations specifiques";
$lang['report_spec_info_explain'] = "Il donne un accs direct  l'information. Utilis&eacute; pour recup&eacute;rer des informations precises comme :";
$lang['report_spec_info_ex1'] = "l&acute;etat d&acute;un host pendant une p&eacute;riode pr&eacute;cise";
$lang['report_spec_info_ex2'] = "l&acute;etat d&acute;un service pendant une p&eacute;riode pr&eacute;cise";
$lang['report_spec_info_ex3'] = "l&acute;etat des services d&acute;un host pendant une p&eacute;riode pr&eacute;cise";
$lang['report_cont_info'] = "Le rapport d&acute;information continue";
$lang['report_cont_info_explain'] = "Utilis&eacute; pour suivre les informations des hosts/services selectionn&eacute;s comme :";
$lang['report_cont_info_ex1'] = "recevoir par mail chaque jour l&acute;etat d'une selection d'hosts/services de la veille";
$lang['report_cont_info_ex2'] = "recevoir par mail chaque semaine l&acute;etat d'une selection d'hosts/services de la semaine pass&eacute;e";
$lang['report_cont_info_ex3'] = "recevoir par mail chaque mois l&acute;etat d'une selection d'hosts/services pendant le mois pass&eacute;";
$lang['report_logs_explain'] = "Ces logs interpretent le fichier status.log de Nagios, ce fichier est g&eacute;n&eacute;r&eacute; &agrave; chaque redemarrage de Nagios";

/* Traffic Map */

$lang['tm_update'] = "La Traffic Map a &eacute;t&eacute; mise &agrave; jour";
$lang['tm_available'] = "Traffic Map disponibles";
$lang['tm_add'] = "Traffic Map ajout&eacute;e";
$lang['tm_modify'] = "Traffic Map modifi&eacute;e";
$lang['tm_delete'] = "Traffic Map supprim&eacute;e";
$lang['tm_addHost'] = "Ajout d&#146;un Host &agrave; la traffic map";
$lang['tm_changeHost'] = "L&#146;Host a &eacute;t&eacute; modifi&eacute;";
$lang['tm_deleteHost'] = "L&#146;Host a &eacute;t&eacute; supprim&eacute;";
$lang['tm_addRelation'] = "Une nouvelle relation a &eacute;t&eacute; ajout&eacute;e";
$lang['tm_changeRelation'] = "La relation a &eacute;t&eacute; modifi&eacute;e";
$lang['tm_deleteRelation'] = "La relation a &eacute;t&eacute; supprim&eacute;e";
$lang['tm_hostServiceAssociated'] = "Hosts avec un service check_traffic associ&eacute;";
$lang['tm_checkTrafficAssociated'] = "Check_traffic associ&eacute;";
$lang['tm_other'] = "Autres ressources (sans check_traffic)";
$lang['tm_networkEquipment'] = "Equipement r&eacute;seau";
$lang['tm_selected'] = "s&eacute;lectionn&eacute;s";
$lang['tm_maxBWIn'] = "Maximum bande passante entrante (Kbps)";
$lang['tm_maxBWOut'] = "Maximum bande passante sortante (Kbps)";
$lang['tm_background'] = "Image de fond";
$lang['tm_relations'] = "Relation(s)";
$lang['tm_hostsAvailables'] = "Host(s) disponible(s)";
$lang['tm_labelsWarning'] = "Veuillez saisir un label sans accents";

/* Graphs */

$lang['graph'] = "Graphique";
$lang['graphs'] = "Graphiques";
$lang['g_title'] = "Graphiques";
$lang['g_available'] = "Graphiques disponibles";
$lang['g_path'] = "Chemin de la base RRD";
$lang['g_imgformat'] = "Format de l&acute;image affich&eacute;e";
$lang['g_verticallabel'] = "Titre vertical";
$lang['g_width'] = "Taille de l&acute;image en largeur";
$lang['g_height'] = "Taille de l&acute;image en hauteur";
$lang['g_lowerlimit'] = "Limite basse";
$lang['g_Couleurs'] = "Couleurs : ";
$lang['g_ColGrilFond'] = "Couleur de fond du graph central";
$lang['g_ColFond'] = "Couleur du fond";
$lang['g_ColPolice'] = "Couleur de la police";
$lang['g_ColGrGril'] = "Couleur de la grille principale";
$lang['g_ColPtGril'] = "Couleur de la grille secondaire";
$lang['g_ColContCub'] = "Couleur du contour de la l&eacute;gende";
$lang['g_ColArrow'] = "Couleur de l&acute;option arrow";
$lang['g_ColImHau'] = "Couleur du cadre haut";
$lang['g_ColImBa'] = "Couleur du cadre bas";
$lang['g_dsname'] = "Nom de la source de donn&eacute;e ";
$lang['g_ColDs'] = "Couleur de la source de donn&eacute;e ";
$lang['g_flamming'] = "Couleur flamming";
$lang['g_Area'] = "Remplissage (no = courbe)";
$lang['g_tickness'] = "Epaisseur de la courbe";
$lang['g_gprintlastds'] = "Affichage de la derniere valeur calcul&eacute;e";
$lang['g_gprintminds'] = "Affichage de la valeur la plus basse";
$lang['g_gprintaverageds'] = "Affichage de la valeur moyenne";
$lang['g_gprintmaxds'] = "Affichage de la valeur la plus haute";
$lang['g_graphorama'] = "GraphsVision";
$lang['g_graphoramaerror'] = "La date de d&eacute;but est superieure ou &eacute;gale  la date de fin";
$lang['g_date_begin'] = "Date de d&eacute;but";
$lang['g_date_end'] = "Date de fin";
$lang['g_hours'] = "Heures";
$lang['g_number_per_line'] = "Nombre par ligne";
$lang['g_height'] = "Hauteur";
$lang['g_width'] = "Largeur";
$lang['g_basic_conf'] = "Configuration de base :";
$lang['g_ds'] = "Source de donn&eacute;es";
$lang['g_lcurrent'] = "Courant";
$lang['g_lday'] = "Derni&egrave;re journ&eacute;e";
$lang['g_lweek'] = "Derni&egrave;re semaine";
$lang['g_lyear'] = "Derni&egrave;re ann&eacute;e";
$lang['g_see'] = "Voir le graphique associ&eacute;";
$lang['g_from'] = "Du ";
$lang['g_to'] = " Au ";
$lang['g_current'] = "Actuel :";
$lang['g_average'] = "Moyen :";
$lang['g_no_graphs'] = "Pas de graphique disponible";
$lang['g_no_access_file'] = "Le fichier %s n&#146;est pas accessible";

/* Graph Models */

$lang['gmod'] =  'Propri&eacute;t&eacute;s de base';
$lang['gmod_ds'] =  'Source de donn&eacute;es';
$lang['gmod_available'] = 'Mod&egrave;les de propri&eacute;t&eacute;s de Graphique disponibles';
$lang['gmod_ds_available'] = 'Mod&egrave;les de DS de Graphique disponibles';
$lang['gmod_use_model'] = 'Utiliser un mod&egrave;le';

/* Colors */
$lang['colors'] =  'Couleurs';
$lang['hexa'] =  'Couleur en hexadecimal';

/* Nagios.cfg */

$lang['nagios_save'] = 'La configuration a &eacute;t&eacute; sauv&eacute;e.<br> Vous devez maintenant d&eacute;placer le fichier et red&eacute;marrer Nagios pour prendre les changements en compte.';

/* Resource.cfg */

$lang['resources_example'] = 'Exemple de ressource';
$lang['resources_add'] = 'Ajouter une nouvelle ressource';
$lang['resources_new'] = 'Une nouvelle ressource a &eacute;t&eacute; ajout&eacute;e';

/* lca */

$lang['lca_user'] = 'Utilisateur :';
$lang['lca_user_access'] = 'a acc&egrave;s &agrave; :';
$lang['lca_profile'] = 'profil';
$lang['lca_user_restriction'] = 'Utilisateurs poss&eacute;dant des restrictions';
$lang['lca_access_comment'] = 'Autoriser l&#146;acc&egrave;s aux commentaires :';
$lang['lca_access_downtime'] = 'Autoriser l&#146;acc&egrave;s aux downtimes :';
$lang['lca_access_watchlog'] = 'Autoriser l&#146;acc&egrave;s &agrave; la lecture des logs :';
$lang['lca_access_trafficMap'] = 'Autoriser l&#146;acc&egrave;s &agrave; la vision des Traffic Maps :';
$lang['lca_access_processInfo'] = 'Autoriser l&#146;acc&egrave;s sur les process info :';
$lang['lca_add_user_access'] = 'Ajouter des restrictions &agrave; un utilisateur';
$lang['lca_apply_restrictions'] = 'Appliquer les restrictions';
$lang['lca_action_on_profile'] = 'Actions' ;

/* History */

$lang['log_detail'] = "D&eacute;tails de logs pour ";

/* Options General */

$lang["opt_gen"] = "Options G&eacute;n&eacute;rales";
$lang["nagios_version"] = "Version de nagios en cours d&#146;utilisation : ";
$lang["oreon_path"] = "R&eacute;pertoire d&#146;installation de Oreon";
$lang["oreon_path_tooltip"] = "O&#249; est install&eacute; Oreon ?";
$lang["nagios_path"] = "R&eacute;pertoire d&#146;installation de Nagios";
$lang["nagios_path_tooltip"] = "O&#249; est le r&eacute;pertoire de Nagios ?";
$lang["refresh_interface"] = "Rafra&icirc;chissement de l&#146;interface";
$lang["refresh_interface_tooltip"] = "Fr&eacute;quence &#224; laquelle l&#146;interface est rafraichie";
$lang["snmp_com"] = "Communaut&eacute; SNMP";
$lang["snmp_com_tooltip"] = "Communaut&eacute; SNMP utilis&eacute;e par d&eacute;faut";
$lang["snmp_version"] = "Version de SNMP";
$lang["snmp_path"] = "R&eacute;pertoire d&#146;installation de SNMP";
$lang["snmp_path_tooltip"] = "O&#249; se trouvent les binaires snmpget et snmpwalk ?";
$lang["cam_color"] = "Couleurs des Camemberts";
$lang["for_hosts"] = "Pour les hosts";
$lang["for_services"] = "Pour les services";
$lang["rrd_path"] = "RRDToolsPath/rrdtool";
$lang["rrd_path_tooltip"] = "O&#249; est install&eacute; rrdtool ?";
$lang["rrd_base_path"] = "Bases RRDTool";
$lang["rrd_base_path_tooltip"] = "O&#249; sont g&eacute;n&eacute;r&eacute;s les fichiers rrd ?";
$lang["mailer"] = "Mailer";
$lang["mailer_tooltip"] = "O&#249; se trouve le binaire mail ?";
$lang["opt_gen_save"] = "Options G&eacute;n&eacute;rales sauv&eacute;es.<br>Vous n&#146;avez pas besoin de reg&eacute;n&eacute;rer.";
$lang["session_expire"] = "Temps d'expiration des sessions";
$lang["session_expire_unlimited"] = "illimit&eacute;";
$lang["binary_path"] = "R&eacute;pertoire du binaire de Nagios";
$lang["binary_path_tooltip"] = "O&#249; se trouve le binaire nagios ?";
$lang["images_logo_path"] = "R&eacute;pertoire des images de Nagios";
$lang["images_logo_path_tooltip"] = "O&#249; se trouve le r&eacute;pertoire des images Nagios  ?";
$lang["plugins_path"] = "R&eacute;pertoire des sondes Nagios";
$lang["plugins_path_tooltip"] = "O&#249; se trouvent les sondes Nagios ?";
$lang["path_error_legend"] = "Code Couleurs des erreurs";
$lang["invalid_path"] = "Le r&eacute;pertoire ou le fichier n&#146;existe pas";
$lang["executable_binary"] = "Le fichier n&#146;est pas ex&eacute;cutable";
$lang["writable_path"] = "Le r&eacute;pertoire ou le fichier n&#146;est pas modifiable";
$lang["readable_path"] = "Le r&eacute;pertoire et son contenu ne sont pas lisibles";
$lang["rrdtool_version"] = "Version de RRDTool";
$lang["nmap_path"] = "Chemin du binaire de Nmap";
$lang["nmap_path_tooltip"] = "O&#249; est install&eacute; nmap ?";

/* Auto Detect */

$lang['ad_title'] = "D&eacute;tection automatique des Hosts";
$lang['ad_title2'] = "D&eacute;tection automatique";
$lang['ad_ser_result'] = "La recherche automatique a d&eacute;couvert les services suivants sur ";
$lang['ad_ser_result2'] = "Cette liste n'est pas une liste exhaustive et ne comprend que <br>les services r&eacute;seaux ayant ouvert un port r&eacute;seau sur l'host.";
$lang['ad_infos1'] = "Pour faire la recherche automatique,<br>veuillez remplir le champs suivant avec :";
$lang['ad_infos2'] = 'soit avec une adresse IP (ex : 192.168.1.45),';
$lang['ad_infos3'] = 'soit une plage IP (ex : 192.168.1.1-254),';
$lang['ad_infos4'] = 'soit une liste d\'IP :';
$lang['ad_infos5'] = '192.168.1.1,24,38';
$lang['ad_infos6'] = '192.168.*.*';
$lang['ad_infos7'] = '192.168.10-34.23-25,29-32';
$lang['ad_ip'] = 'IP';
$lang['ad_res_result'] = 'R&eacute;sultat de la recherche';
$lang['ad_found'] = "trouv&eacute;(s)";
$lang['ad_number'] = "Num&eacute;ro";
$lang['ad_dns'] = "DNS";
$lang['ad_actions'] = "Actions";
$lang['ad_port'] = "Port";
$lang['ad_name'] = "Nom";

/* Export DB */

$lang['edb_file_already_exist'] = "Ce fichier existe deja, veuillez ressaisir un autre nom de sauvegarde";
$lang['edb_file_move'] = "Fichiers d&eacute;plac&eacute;s";
$lang['edb_file_ok'] = "Fichiers g&eacute;n&eacute;r&eacute;s et d&eacute;plac&eacute;s";
$lang['edb_file_nok'] = "Erreur lors de la g&eacute;n&eacute;ration ou le d&eacute;placement des fichiers";
$lang['edb_restart'] = "Red&eacute;marre le serveur";
$lang['edb_save'] = "Cr&eacute;er une sauvegarde";
$lang['edb_nagios_restart'] = "Red&eacute;marre le serveur Nagios";
$lang['edb_nagios_restart_ok'] = "Nagios red&eacute;marr&eacute;";
$lang['edb_restart'] = "Red&eacute;marrer";

/* user Online */

$lang["wi_user"] = "Utilisateurs";
$lang["wi_where"] = "Localisation";
$lang["wi_last_req"] = "Derni&egrave;re Requ&ecirc;te";

/* Reporting */

$lang["pie_unavailable"] = "Pas de camembert accessible pour le moment";

/* Configuration Stats */

$lang['conf_stats_category'] = "Cat&eacute;gorie";

/* Pictures */

$lang["pict_title"] = "Oreon - Images pour les informations &eacute;tendues";
$lang["pict_new_image"] = "Nouvelle image (.png seulement)";

/* About */

$lang["developped"] = "D&eacute;velopp&eacute; par";

/* Live Report */

$lang["lr_available"] = "Hosts Disponibles";
$lang["live_report"] = "Rapport en direct";
$lang["bbreporting"] = "Rapports";
$lang["lr_host"] = "Host :";
$lang["lr_alias"] = "Alias :";
$lang["lr_ip"] = "Adresse IP :";
$lang["lr_view_services"] = "Visualiser les d&eacute;tails de services pour cet host";
$lang["lr_configure_host"] = "Configurer cet host";
$lang["lr_details_host"] = "Visualiser les informations de l'host";


/* Date and Time Format */

$lang["date_format"] = "d/m/Y";
$lang["time_format"] = "H:i:s";
$lang["header_format"] = "d/m/Y G:i";
$lang["date_time_format"] = "d/m/Y - H:i:s";
$lang["date_time_format_status"] = "d/m/Y H:i:s";
$lang["date_time_format_g_comment"] = "d/m/Y H:i";

/* */

$lang["top"] = "Haut";
$lang["event"] = "Ev&eacute;nements";
$lang["date"] = "Date";
$lang["pel_l_details"] = "D&eacute;tail des logs pour le ";
$lang["pel_sort"] = "Filtres";
$lang["pel_alerts_title"] = "Alertes du ";
$lang["pel_notify_title"] = "Notifications du ";

/* perfparse */

$lang["perfparse_installed"] = "Perfparse est install&eacute; ?";
$lang["service_logged"] = "Services loggs";

/* legend */

$lang["lgd_legend"] = " L&eacute;gende";
$lang["lgd_delOne"] = " Supprimer";
$lang["lgd_delAll"] = " Supprimer";
$lang["lgd_duplicate"] = " Dupliquer";
$lang["lgd_view"] = " Voir";
$lang["lgd_edit"] = " Modifier";
$lang["lgd_signpost"] = " D&eacute;tail";
$lang["lgd_next"] = " Suivant";
$lang["lgd_prev"] = " Pr&eacute;c&eacute;dent";
$lang["lgd_on"] = " Activer";
$lang["lgd_off"] = " D&eacute;sactiver";

$lang["advanced"] = "Options Avancees >>";

$lang["quickFormError"] = "Impossible de valider, un ou plusieurs champs sont erron&eacute;s";
?>
