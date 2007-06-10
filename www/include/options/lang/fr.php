<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/gpl.txt
Developped by : Julien Mathis - Romain Le Merlus

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

# Options Error

$lang['requiredFields'] = "<font style='color: red;'>*</font> Champs requis";
$lang['ErrValidPath'] = "Le r&eacute;pertoire n'est pas valide";
$lang['ErrReadPath'] = "Le r&eacute;pertoire n'est pas en lecture";
$lang['ErrExeBin'] = "Le binaire n'est pas executable";
$lang['ErrWrPath'] = "Le r&eacute;pertoire n'est pas en &eacute;criture";
$lang['ErrWrFile'] = "Le fichier n'est pas en &eacute;criture";

# LCA

$lang['lca_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['lca_add'] = "Ajouter une LCA";
$lang['lca_change'] = "Modifier une LCA";
$lang['lca_view'] = "Afficher une LCA";
$lang['lca_name'] = "D&eacute;finition de la LCA";
$lang['lca_comment'] = "Commentaire";
$lang['lca_type'] = "Type";
$lang['lca_tpMenu'] = "Menu";
$lang['lca_tpRes'] = "Ressources";
$lang['lca_tpBoth'] = "Les deux";
$lang['lca_appCG'] = "Groupes Utilisateurs concern&eacute;s";
$lang['lca_cg'] = "Groupes Utilisateurs";
$lang['lca_sortRes'] = "Ressources";
$lang['lca_appRes'] = "Ressources impact&eacute;es";
$lang['lca_hg'] = "Host Groups";
$lang['lca_hgChilds'] = "Inclure les Hosts du Host Groups";
$lang['lca_sg'] = "Service Groups";
$lang['lca_host'] = "Hosts";
$lang['lca_sortTopo'] = "Topologie";
$lang['lca_appTopo'] = "Pages concern&eacute;es";
$lang['lca_topo'] = "Pages visibles";

# General Options

$lang["genOpt_change"] = "Modifier les Options G&eacute;n&eacute;rales";
$lang["genOpt_nagios"] = "Informations sur Nagios";
$lang["genOpt_oreon"] = "Informations sur Oreon";
$lang["genOpt_snmp"] = "Informations sur SNMP";
$lang["genOpt_various"] = "Informations diverses";
$lang["genOpt_nagPath"] = "R&eacute;pertoire";
$lang["genOpt_nagBin"] = "R&eacute;pertoire + Binaire";
$lang['genOpt_nagScript'] = "Init Script";
$lang["genOpt_nagImg"] = "R&eacute;pertoire Images";
$lang["genOpt_nagPlug"] = "R&eacute;pertoire Sondes";
$lang["genOpt_nagVersion"] = "Version de Nagios";
$lang["genOpt_oPath"] = "R&eacute;pertoire";
$lang["genOpt_webPath"] = "R&eacute;pertoire Web";
$lang["genOpt_oRrdbPath"] = "R&eacute;pertoire des Bases rrd";
$lang["genOpt_oRefresh"] = "Fr&eacute;quence de rafraichissement de l&#146;interface (en secondes)";
$lang["genOpt_oExpire"] = "Expiration des Sessions (en minutes)";
$lang["genOpt_oHCUP"] = "Couleur Host UP";
$lang["genOpt_oHCDW"] = "Couleur Host DOWN";
$lang["genOpt_oHCUN"] = "Couleur Host UNREACHABLE";
$lang["genOpt_oSOK"] = "Couleur Service OK";
$lang["genOpt_oSWN"] = "Couleur Service WARNING";
$lang["genOpt_oSCT"] = "Couleur Service CRITICAL";
$lang["genOpt_oSPD"] = "Couleur Service PENDING";
$lang["genOpt_oSUK"] = "Couleur Service UNKNOWN";
$lang["genOpt_snmpCom"] = "Communaut&eacute; Globale";
$lang["genOpt_snmpVer"] = "Version";
$lang["genOpt_mailer"] = "R&eacute;pertoire + Binaire du Mailer";
$lang["genOpt_rrdtool"] = "R&eacute;pertoire + Binaire de RRDTOOL";
$lang["genOpt_rrdtoolV"] = "Version de RRDTOOL";
$lang["genOpt_perfparse"] = "Utilisation de PerfParse";
$lang["genOpt_colorPicker"] = "Choisissez une couleur";
$lang["genOpt_maxViewMonitoring"] = "Limite par page dans le Monitoring";
$lang["genOpt_maxViewConfiguration"] = "Limite par page (par default)";
$lang["genOpt_AjaxTimeReloadStatistic"] = "Fr&eacute;quence de rafraichissement pour les statistiques (en secondes)";
$lang["genOpt_AjaxTimeReloadMonitoring"] = "Fr&eacute;quence de rafraichissement pour le monitoring (en secondes)";
$lang["genOpt_AjaxFirstTimeReloadStatistic"] = "Premier rafraichissement pour les statistiques (en secondes)";
$lang["genOpt_AjaxFirstTimeReloadMonitoring"] = "Premier rafraichissement pour le monitoring (en secondes)";
$lang["genOpt_snmp_trapd_pathConf"] = "R&eacute;pertoire des fichiers de configuration des traps";
$lang["genOpt_template"] = "Template";
$lang["genOpt_ldap"] = "Informations sur LDAP";
$lang["genOpt_ldap_host"] = "Serveur LDAP";
$lang["genOpt_ldap_port"] = "Port LDAP";
$lang["genOpt_ldap_base_dn"] = "Base DN LDAP";
$lang["genOpt_ldap_login_attrib"] = "LDAP Login Attribut";
$lang["genOpt_ldap_ssl"] = "Activ&eacute; le support SSL pour le LDAP";
$lang["genOpt_ldap_auth_enable"] = "Activ&eacute; l'authentification LDAP";
$lang["genOpt_searchldap"] = "Informations sur la recherche LDAP";
$lang["genOpt_ldap_search_user"] = "Utilisateur pour effectuer la recherche (anonyme si vide)";
$lang["genOpt_ldap_search_user_pwd"] = "Mot de passe";
$lang["genOpt_ldap_search_filter"] = "Filtre de recherche par d&eacute;faut";
$lang["genOpt_ldap_search_timeout"] = "Dur&eacute;e limite de la recherche";
$lang["genOpt_ldap_search_limit"] = "Nombre maximum d'entre&eacute;es retourn&eacute;es";
$lang["genOpt_graph_preferencies"] = "Moteur de Graphs Pr&eacute;f&eacute;r&eacute;";
$lang["genOpt_debug"] = "Debug";
$lang["genOpt_dPath"] = "R&eacute;pertoire des logs";
$lang["genOpt_debug_auth"] = "Debug de l&#146;authentification";
$lang["genOpt_debug_nagios_import"] = "Debug de l&#146;importation des fichiers Nagios";
$lang["genOpt_debug_rrdtool"] = "Debug de RRDTool";
$lang["genOpt_debug_ldap_import"] = "Debug de l&#146;importation des users LDAP ";
$lang["genOpt_debug_inventory"] = "Debug de l&#146;inventaire";
$lang["genOpt_debug_clear"] = "&nbsp;Effacer le fichier de debug";

$lang["genOpt_problem_sort_type"] = "Trier les probl&egrave;mes par ";
$lang["genOpt_problem_duration"] = "Dur&eacute;e";
$lang["genOpt_problem_host"] = "Hosts";
$lang["genOpt_problem_service"] = "Services";
$lang["genOpt_problem_status"] = "Status";
$lang["genOpt_problem_last_check"] = "Last check";
$lang["genOpt_problem_output"] = "Output";
$lang["genOpt_problem_sort_order"] = "Trier les probl&egrave;mes par ordre ";
$lang["genOpt_problem_order_asc"] = "Ascendant";
$lang["genOpt_problem_order_desc"] = "Descendant";

$lang["genOpt_gmt"] = "GMT";

$lang['genOpt_css'] = "CSS";
$lang['genOpt_menu_name'] = "Menu";
$lang['genOpt_file_name'] = "Fichier css";

# Menu

$lang['mod_menu'] = "Modules disponibles";
$lang['mod_menu_modInfos'] = "Informations sur le Module";
$lang['mod_menu_upgradeInfos'] = "Informations sur la mise &agrave; jour";
$lang["mod_menu_module_name"] = "Nom";
$lang["mod_menu_module_rname"] = "Nom r&eacute;el";
$lang["mod_menu_module_release"] = "Version";
$lang["mod_menu_module_release_from"] = "Version de base";
$lang["mod_menu_module_release_to"] = "Version finale";
$lang["mod_menu_module_author"] = "Auteur";
$lang["mod_menu_module_additionnals_infos"] = "Informations compl&eacute;mentaires";
$lang["mod_menu_module_is_installed"] = "Install&eacute;";
$lang["mod_menu_module_is_validUp"] = "Valide pour une mise &agrave; jour";
$lang["mod_menu_module_is_notvalidUp"] = "Invalide pour une mise &agrave; jour";
$lang["mod_menu_module_is_notvalidIn"] = "Installation d&eacute;ja existente";
$lang["mod_menu_module_invalid"] = "ND";
$lang["mod_menu_module_impossible"] = "Impossible";
$lang["mod_menu_listAction"] = "Actions";
$lang["mod_menu_listAction_del"] = "D&eacuteinstaller le module";
$lang["mod_menu_listAction_install"] = "Installer le module";
$lang["mod_menu_listAction_upgrade"] = "Mettre a jour";
$lang["mod_menu_output1"] = "Module install&eacute; et enregistr&eacute;";
$lang["mod_menu_output2"] = "Fichier SQL inclus";
$lang["mod_menu_output3"] = "Fichier PHP inclus";
$lang["mod_menu_output4"] = "Installation du module impossible";

$lang["menu_ODS"] = "OreonDataStorage";
$lang["menu_nagios"] = "Nagios";

# Session

$lang['kick_user'] = "D&eacute;connecter l&#146;utilisateur";
$lang['distant_location'] = "Adresse IP";
$lang['wi_user'] = "Utilisateurs";
$lang['wi_where'] = "Localisation";
$lang['wi_last_req'] = "Derni&egrave;re Requ&ecirc;te";
$lang['kicked_user'] = "Utilisateur d&eacute;connect&eacute;";

# Lang

$lang['lang_title'] = "Gestion des fichiers de Langue";
$lang['lang_user'] = "Langue Utilisateur par d&eacute;faut :";
$lang['lang_gen'] = "Langues Principales disponibles";
$lang['lang_genUse'] = "Langue Principale utilis&eacute;e";

$lang['lang_mod'] = "Module";
$lang['lang_av'] = "Langues disponibles";
$lang['lang_use'] = "Langue utilis&eacute;e";
$lang['lang_none'] = "Aucune";

# My Account

$lang["myAcc_change"] = "Modifier mes param&egrave;tres";

# Taches

$lang["m_task"] = "T&acirc;ches";

# Menu

$lang["menu_ldap"] = "LDAP";
$lang["menu_snmp"] = "SNMP";
$lang["menu_rrdtool"] = "RRDTool";
$lang["menu_debug"] = "Debug";
$lang["menu_colors"] = "Colors";
$lang["menu_nagios"] = "Nagios";
$lang["menu_general"] = "G&ecute;n&ecute;rate";

# Menu

$lang["m_modules"] = "Modules";

# ODS 

$lang['m_log_advanced'] = "Logs Avanc&eacute;s";
$lang['m_log_lite'] = "Event Logs";
$lang['ods_rrd_path'] = "Chemin d'acc&egrave;s aux bases RRD de stockage";
$lang['ods_len_storage_rrd'] = "Taille des Bases RRDTool";
$lang['ods_autodelete_rrd_db'] = "Auto-Suppression des bases RRD";
$lang['ods_sleep_time'] = "Sleep Time";
$lang['ods_purge_interval'] = "Intervalle de v&eacute;rification de purges";
$lang['ods_storage_type'] = "Type de Stockage";
$lang['ods_sleep_time_expl'] = "en secondes  - Doit &ecirc;tre sup&eacute;rieur &agrave; 10";
$lang['ods_purge_interval_expl'] = "en secondes  -  Doit &ecirc;tre sup&eacute;rieur &agrave; 2";
$lang['ods_auto_drop'] = "D&eacute;placer les donn&eacute;es apr&egrave;s lecture";
$lang['ods_drop_file'] = "Fichier de drop des donn&eacute;es";
$lang['ods_perfdata_file'] = "Fichier des donn&eacute;es de performances";
$lang['ods_archive_log'] = "Archiver les logs de Nagios";
$lang['ods_log_retention'] = "Dur&eacute;e de r&eacute;tention des logs";
$lang['ods_log_retention_unit'] = "days";
$lang['ods_fast_parsing'] = "Lecture rapide des Status";
$lang['ods_nagios_log_file'] = "Fichier de log de Nagios &agrave; parser";

$lang['m_patch'] = "Mise &agrave; jour";
$lang['m_checkVersion'] = "V&eacute;rification";
$lang['m_patchOptions'] = "Options";
$lang['patchOption_change'] = "Modifier les options pour la mise &agrave; jour";
$lang['patchOption_check_stable'] = "V&eacute;rifier les stables";
$lang['patchOption_check_security'] = "V&eacute;rifier les patchs de s&eacute;curit&eacute;";
$lang['patchOption_check_patch'] = "V&eacute;rifier les patchs";
$lang['patchOption_check_rc'] = "V&eacute;rifier les Release Candidate";
$lang['patchOption_check_beta'] = "V&eacute;rifier les b&eacute;tas";
$lang['patchOption_path_download'] = "Chemin pour le t&eacute;l&eacute;chargement des patchs";
$lang['checkVersion_msgErr01'] = "Probl&egrave; dans la r&eacute;cup&eacute;ration de la derni&egrave;re version.";
$lang['updateSecu'] = "Mise &agrave; de s&eacute;curit&eacute; disponible";
$lang['update'] = "Mise &agrave; disponible";
$lang['uptodate'] = "Le logiciel est &agrave; jour";
$lang['preUpdate_msgErr01'] = "Probl&egrave;me dans la r&eacute;cup&eacute;ration de la liste des fichiers.";
$lang['preUpdate_msgErr02'] = "Probl&egrave;me dans la r&eacute;cup&eacute;ration du fichier.";
$lang['preUpdate_msgErr03'] = "Aucune version n'est d&eacute;finie.";
$lang['preUpdate_msgErr04'] = "Impossible d'ouvrir le fichier /etc/oreon.conf";
$lang['preUpdate_msgErr05'] = "Probl&egrave;me la derni&egrave; version disponible.";
$lang['preUpdate_msgErr06'] = "Impossible d'ouvrir le patch";
$lang['preUpdate_fileDownloaded'] = "Le fichier %s est t&eacute;l&eacute;charg&eacute;.<br/>";
$lang['preUpdate_installArchive'] = "Pour l'install de la mise à jour %s, décompressez l'archive et suivez le UPGRADE.\n";
$lang['preUpdate_shellPatch'] = "Exécutez le shell %s en root.\n";
$lang['batchPatch_begin'] = "Début de l'éxecution des patchs";
$lang['batchPatch_end'] = "Fin de l'éxecution des patchs";
$lang['batchPatch_ok01'] = "Le patch %s est appliqué.";
$lang['batchPatch_err01'] = "Erreur dans l'application du patch %s.";

?>