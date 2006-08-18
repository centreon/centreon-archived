<?
/* Configuration Error */

$lang['requiredFields'] = "<font style='color: red;'>*</font> Champs requis";
$lang['ErrName'] = "Nom obligatoire";
$lang['ErrAlias'] = "Alias obligatoire";
$lang['ErrEmail'] = "Email valide";
$lang['ErrOpt'] = "Option obligatoire";
$lang['ErrTp'] = "P&eacute;riode obligatoire";
$lang['ErrCmd'] = "Commande obligatoire";
$lang['ErrCct'] = "Contact obligatoire";
$lang['ErrCg'] = "ContactGroup obligatoire";
$lang['ErrCmdLine'] = "Ligne de Commande obligatoire";
$lang['ErrCmdType'] = "Type de Commande obligatoire";
$lang['ErrAlreadyExist'] = "Un &eacute;l&eacute;ment portant le m&ecirc;me nom existe d&eacute;ja";
$lang['ErrAddress'] = "Adresse obligatoire";
$lang['ErrRequired'] = "Champ requis";
$lang['ErrSvLeast'] = "HostGroup ou Host requis";
$lang['ErrCctPasswd'] = "Les mots de passe ne correspondent pas";
$lang['ErrGenFileProb'] = "Probl&egrave;me d'acc&egrave;s aux fichiers";
$lang['ErrCycleDef'] = "La d&eacute;finition est redondante";
$lang['ErrSvConflict'] = "Cette description de Service entre en conflit avec une autre existente au sein des relations s&eacute;lectionn&eacute;es";
$lang['ErrNotEnoughtContact'] = "Vous devez conserver au moins un utilisateur pour acc&eacute;der &agrave; l'interface";

/* Configuration Menu */

$lang['quicksearch'] = "Recherche rapide";
$lang['available'] = "Disponible";
$lang['selected'] = "S&eacute;lectionn&eacute;";
$lang['further_infos'] = "Informations compl&eacute;mentaires";
$lang['comment'] = "Commentaires";
$lang['nothing'] = "Vide";
$lang['formObjMatch'] = "El&eacute;ment impact&eacute; : ";
$lang['action'] = "Post Validation";
$lang['actionList'] = "Liste";
$lang['actionForm'] = "Formulaire";
$lang['legend1'] = "(*) Ce service est utilis&eacute; par plusieurs Hosts";
$lang['previous'] = "previous";
$lang['next'] = "next";

/* host */

$lang['h'] = "Host";
$lang['h_conf'] = "Host Configuration";
$lang['h_add'] = "Ajouter un Host";
$lang['h_change'] = "Modifier un Host";
$lang['h_view'] = "Afficher un Host";

$lang['h_extInf'] = "Host Extended Infos";
$lang["h_ExtInf_add"] = "Ajouter une Information Etendue";
$lang["h_ExtInf_change"] = "Modifier une Information Etendue";
$lang["h_ExtInf_view"] = "Voir une Information Etendue";

$lang['h_childs'] = "Services li&eacute;s";
$lang['h_parent'] = "Template parent";

$lang['h_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['h_name'] = "Nom de l'Host";
$lang['h_alias'] = "Alias";
$lang['h_address'] = "Adresse";
$lang['h_snmpCom'] = "Communaut&eacute; SNMP";
$lang['h_snmpVer'] = "Version";
$lang['h_template'] = "Template de Host";
$lang['h_templateText'] = "Utiliser un Template vous dispense des &eacute;l&eacute;ments de configuration obligatoires";
$lang['h_dupSvTplAssocText'] = "Cr&eacute;er les services li&eacute;s au Template";

$lang['h_head_links'] = "Relations";
$lang["h_Links_add"] = "Ajouter des relations";
$lang["h_Links_change"] = "Modifier les relations";
$lang["h_Links_view"] = "Voir les relations";
$lang['h_HostGroupMembers'] = "HostGroups parents";
$lang['h_HostParents'] = "Hosts parents";
$lang['h_HostChilds'] = "Hosts enfants";

$lang['h_head_state'] = "Status de l'Host";
$lang['h_checkCmd'] = "Commande de check";
$lang['h_checkMca'] = "Nombre maximum d'essais";
$lang['h_checkInterval'] = "Ordonnancement r&eacute;gulier";
$lang['h_checksEnabled'] = "Activation des controles de l'Host";
$lang['h_checkPeriod'] = "P&eacute;riode de controle";
$lang['h_activeCE'] = "Controles actifs";
$lang['h_passiveCE'] = "Controles passifs";
$lang['h_eventHandlerE'] = "Activation du gestionnaire d'&eacute;v&egrave;nements";
$lang['h_eventHandler'] = "Commande associ&eacute;e";

$lang['h_head_treat'] = "Traitement des donn&eacute;es";
$lang['h_add_treat'] = "Ajouter le Traitement des donn&eacute;es";
$lang['h_modify_treat'] = "Modifier le Traitement des donn&eacute;es";
$lang['h_view_treat'] = "Afficher le Traitement des donn&eacute;es";

$lang['h_ObsessOH'] = "Commande post check";
$lang['h_checkFreshness'] = "Controle de validit&eacute; des donn&eacute;es";
$lang['h_FreshnessThreshold'] = "Seuil de controle de validit&eacute;";
$lang['h_flapDetect'] = "D&eacute;tection des oscillations";
$lang['h_lowFT'] = "Seuil de d&eacute;tection bas";
$lang['h_highFT'] = "Seuil de d&eacute;tection haut";
$lang['h_processPD'] = "Traitement des donn&eacute;es de performance";
$lang['h_retainSI'] = "M&eacute;morisation des informations li&eacute;es &agrave; l'Host";
$lang['h_retainNI'] = "M&eacute;morisation des informations non li&eacute;es &agrave; l'Host";

$lang['h_head_notif'] = "Notification";
$lang['h_CgMembers'] = "ContactGroups rattach&eacute;s";
$lang['h_notifInt'] = "Interval de notification";
$lang['h_notifTp'] = "P&eacute;riode de notification";
$lang['h_notifOpts'] = "Type de notification";
$lang['h_notifEnabled'] = "Activer la notification";
$lang['h_stalOpts'] = "Etats de suivi pr&eacute;cis";

$lang['h_nagios'] = "Nagios";
$lang['h_notes'] = "Note";
$lang['h_notesUrl'] = "URL";
$lang['h_actionUrl'] = "Action URL";
$lang['h_iconImg'] = "Ic&ocirc;ne";
$lang['h_iconImgAlt'] = "Ic&ocirc;ne alt";
$lang['h_vrmlImg'] = "VRML Image";
$lang['h_nagStatImg'] = "Nagios Statuts Map Image";
$lang['h_nag2dCoords'] = "Nagios 2d Coords";
$lang['h_nag3dCoords'] = "Nagios 3d Coords";

$lang['h_oreon'] = "Oreon";
$lang['h_country'] = "Pays";
$lang['h_city'] = "Ville";
$lang['h_popCityTitle'] = "Choisir une Ville";
$lang['h_popCityAlpha'] = "Classement Alphab&eacute;tique";

/* host group */

$lang['hg'] = "HostGroup";
$lang['hg_name'] = "Nom du HostGroup";
$lang['hg_alias'] = "Alias";
$lang['hg_add'] = "Ajouter un HostGroup";
$lang['hg_change'] = "Modifier un HostGroup";
$lang['hg_view'] = "Afficher un HostGroup";
$lang['hg_CgMembers'] = "ContactGroups rattach&eacute;s";
$lang['hg_HostMembers'] = "Hosts rattach&eacute;s";
$lang['hg_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['hg_links'] = "Relations";
$lang['hg_notif'] = "Notification";

/* Escalation  */

$lang['esc'] = "Escalade";
$lang['esc_name'] = "Nom de l'Escalade";
$lang['esc_add'] = "Ajouter une Escalade";
$lang['esc_change'] = "Modifier une Escalade";
$lang['esc_view'] = "Afficher une Escalade";
$lang['esc_infos'] = "Informations";
$lang['esc_sort2'] = "Escalade d'Hosts";
$lang['esc_sort3'] = "Escalade de Services";
$lang['esc_sort4'] = "Escalade de Hostgroups";
$lang['esc_sort5'] = "Escalade de Meta Services";
$lang["esc_firstNotif"] = "Premi&egrave;re notification";
$lang["esc_lastNotif"] = "Derni&egrave;re notification";
$lang["esc_notifInt"] = "Interval de notification";
$lang["esc_escPeriod"] = "P&eacute;riode d'escalade";
$lang["esc_hOpt"] = "Options d'escalade des Hosts";
$lang["esc_sOpt"] = "Options d'escalade des Services";
$lang["esc_comment"] = "Commentaire";
$lang['esc_appCG'] = "Contact Groups concern&eacute;s";
$lang['esc_sortHosts'] = "Hosts concern&eacute;s";
$lang['esc_sortSv'] = "Services  concern&eacute;es";
$lang['esc_sortHg'] = "HostGroups concern&eacute;s";
$lang['esc_sortMs'] = "Meta Services concern&eacute;s";
$lang['esc_hostServiceMembers'] = "Services par Hosts";

/* Dependencies */

$lang['dep'] = "D&eacute;pendance";
$lang['dep_add'] = "Ajouter une D&eacute;pendance";
$lang['dep_change'] = "Modifier une D&eacute;pendance";
$lang['dep_view'] = "Afficher une D&eacute;pendance";
$lang['dep_infos'] = "Informations";
$lang["dep_inheritsP"] = "Liaison Parente";
$lang["dep_exeFC"] = "Options de d&eacute;pendance d'execution";
$lang["dep_notifFC"] = "Options de d&eacute;pendance de notification";
$lang["dep_comment"] = "Commentaire";
$lang['dep_hPar'] = "Hosts dont nous d&eacute;pendons";
$lang['dep_hChi'] = "Hosts D&eacute;pendants";
$lang['dep_hgPar'] = "HostGroups dont nous d&eacute;pendons";
$lang['dep_hgChi'] = "HostGroups D&eacute;pendants";
$lang['dep_hSvPar'] = "Hosts Services dont nous d&eacute;pendons";
$lang['dep_hSvChi'] = "Hosts Services D&eacute;pendants";
$lang['dep_sgPar'] = "ServiceGroups dont nous d&eacute;pendons";
$lang['dep_sgChi'] = "ServiceGroups D&eacute;pendants";
$lang['dep_msPar'] = "Meta Services dont nous d&eacute;pendons";
$lang['dep_msChi'] = "Meta Services D&eacute;pendants";
$lang['dep_name'] = "Nom";
$lang['dep_description'] = "Description";
$lang['dep_sort2'] = "Host Service D&eacute;pendance";

/* host template model */

$lang['htm'] = "Host Template Model";
$lang['htm_childs'] = "Services Template li&eacute;s";
$lang['htm_parent'] = "Template parent";
$lang['htm_add'] = "Ajouter un Host Template Model";
$lang['htm_change'] = "Modifier un Host Template Model";
$lang['htm_view'] = "Afficher un Host Template Model";
$lang['htm_template'] = "Template de Host Model";
$lang['htm_templateText'] = "Utiliser un Template Model vous permet d'avoir plusieurs niveaux de Template";

/* service */

$lang['sv'] = "Service";
$lang['sv_conf'] = "Service Configuration";
$lang['sv_add'] = "Ajouter un Service";
$lang['sv_change'] = "Modifier un Service";
$lang['sv_view'] = "Afficher un Service";
$lang['sv_parent'] = "Template parent";

$lang['sv_extInf'] = "Service Extended Infos";
$lang["sv_ExtInf_add"] = "Ajouter une Information Etendue";
$lang["sv_ExtInf_change"] = "Modifier une Information Etendue";
$lang["sv_ExtInf_view"] = "Voir une Information Etendue";

$lang['sv_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['sv_hPars'] = "Li&eacute; aux Hosts";
$lang['sv_hgPars'] = "Li&eacute; aux HostGroups";
$lang['sv_description'] = "Description";
$lang['sv_template'] = "Template de Service";
$lang['sv_templateText'] = "Utiliser un Template vous dispense des &eacute;l&eacute;ments de configuration obligatoires";
$lang['sv_traps'] = "Service Trap Relation";

$lang['sv_head_links'] = "Relations";
$lang["sv_Links_add"] = "Ajouter des relations";
$lang["sv_Links_change"] = "Modifier les relations";
$lang["sv_Links_view"] = "Voir les relations";
$lang['sv_ServiceGroupMembers'] = "ServiceGroups parents";

$lang['sv_head_state'] = "Status du Service";
$lang['sv_isVolatile'] = "Service volatil";
$lang['sv_checkCmd'] = "Commande de check";
$lang['sv_checkMca'] = "Nombre maximum d'essais";
$lang['sv_normalCheckInterval'] = "Ordonnancement r&eacute;gulier";
$lang['sv_retryCheckInterval'] = "Ordonnancement non r&eacute;gulier";
$lang['sv_checkPeriod'] = "P&eacute;riode de controle";
$lang['sv_activeCE'] = "Controles actifs";
$lang['sv_passiveCE'] = "Controles passifs";
$lang['sv_eventHandlerE'] = "Activation du gestionnaire d'&eacute;v&egrave;nements";
$lang['sv_eventHandler'] = "Commande associ&eacute;e";
$lang['sv_args'] = "Arguments";

$lang['sv_head_treat'] = "Traitement des donn&eacute;es";
$lang['sv_paraCheck'] = "Controle parall&egrave;le";
$lang['sv_ObsessOS'] = "Commande post check";
$lang['sv_checkFreshness'] = "Controle de validit&eacute; des donn&eacute;es";
$lang['sv_FreshnessThreshold'] = "Seuil de controle de validit&eacute;";
$lang['sv_flapDetect'] = "D&eacute;tection des oscillations";
$lang['sv_lowFT'] = "Seuil de d&eacute;tection bas";
$lang['sv_highFT'] = "Seuil de d&eacute;tection haut";
$lang['sv_processPD'] = "Traitement des donn&eacute;es de performance";
$lang['sv_retainSI'] = "M&eacute;morisation des informations li&eacute;es au Service";
$lang['sv_retainNI'] = "M&eacute;morisation des informations non li&eacute;es au Service";

$lang['sv_head_notif'] = "Notification";
$lang['sv_CgMembers'] = "ContactGroups rattach&eacute;s";
$lang['sv_notifInt'] = "Interval de notification";
$lang['sv_notifTp'] = "P&eacute;riode de notification";
$lang['sv_notifOpts'] = "Type de notification";
$lang['sv_notifEnabled'] = "Activer la notification";
$lang['sv_stalOpts'] = "Etats de suivi pr&eacute;cis";

$lang['sv_oreon'] = "Oreon";
$lang['sv_graphTpl'] = "Graph Mod&egrave;le";

/* Meta Service */

$lang['ms'] = "Meta Service";
$lang['ms_conf'] = "Configuration";
$lang['ms_infos'] = "Informations g&eacute;n&eacute;rales";
$lang["ms_add"] = "Ajouter un Meta Service";
$lang["ms_change"] = "Modifier un Meta Service";
$lang["ms_view"] = "Afficher un Meta Service";
$lang['ms_name'] = "Nom du Meta Service";
$lang['ms_comment'] = "Commentaire";
$lang['ms_levelw'] = "Niveau Warning";
$lang['ms_levelc'] = "Niveau Critical";
$lang['ms_calType'] = "Type de Calcul";
$lang['ms_selSum'] = "Somme";
$lang['ms_selAvr'] = "Moyenne";
$lang['ms_selMin'] = "Minimum";
$lang['ms_selMax'] = "Maximum";
$lang['ms_selMod'] = "Mode de s&eacute;lection";
$lang['ms_selList'] = "Liste de Services";
$lang['ms_regexp'] = "Expression R&eacute;guli&egrave;re";
$lang['ms_exp'] = "Expression";
$lang['ms_metric'] = "Metric";

$lang['ms_head_state'] = "Status du Meta Service";
$lang['ms_checkMca'] = "Nombre maximum d'essais";
$lang['ms_normalCheckInterval'] = "Ordonnancement r&eacute;gulier";
$lang['ms_retryCheckInterval'] = "Ordonnancement non r&eacute;gulier";
$lang['ms_checkPeriod'] = "P&eacute;riode de controle";

$lang['ms_head_notif'] = "Notification";
$lang['ms_CgMembers'] = "ContactGroups rattach&eacute;s";
$lang['ms_notifInt'] = "Interval de notification";
$lang['ms_notifTp'] = "P&eacute;riode de notification";
$lang['ms_notifOpts'] = "Type de notification";
$lang['ms_notifEnabled'] = "Activer la notification";

$lang['mss_add'] = "Ajouter un Service";
$lang['mss_change'] = "Modifier un Service";
$lang['mss_view'] = "Voir un Service";

/* extended service infos */

$lang['esi'] = "Informations &eacute;tendues de Service";
$lang['esi_available'] = "Informations &eacute;tendues disponibles des Services ";
$lang['esi_notes'] = "Note";
$lang['esi_notes_url'] = "Adresse de la note";
$lang['esi_action_url'] = "Action url";
$lang['esi_icon_image'] = "Ic&ocirc;ne";
$lang['esi_icon_image_alt'] = "Ic&ocirc;ne alt";

/* service template model*/

$lang['stm'] = "Service Template Model";
$lang['stm_parent'] = "Template parent";
$lang['stm_add'] = "Ajouter un Service Template Model";
$lang['stm_change'] = "Modifier un Service Template Model";
$lang['stm_view'] = "Afficher un Service Template Model";
$lang['stm_template'] = "Template de Service Model";
$lang['stm_templateText'] = "Utiliser un Template Model vous permet d'avoir plusieurs niveaux de Template";

/* service group*/

$lang['sg'] = "ServiceGroup";
$lang['sg_name'] = "Nom du ServiceGroup";
$lang['sg_alias'] = "Alias";
$lang['sg_add'] = "Ajouter un ServiceGroup";
$lang['sg_change'] = "Modifier un ServiceGroup";
$lang['sg_view'] = "Afficher un ServiceGroup";
$lang['sg_hostServiceMembers'] = "Host Services rattach&eacute;s";
$lang['sg_hostGroupServiceMembers'] = "Host Group Services rattach&eacute;s";
$lang['sg_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['sg_links'] = "Relations";
$lang['sg_notif'] = "Notification";

/* contact */

$lang['cct_add'] = "Ajouter un utilisateur";
$lang['cct_change'] = "Modifier un utilisateur";
$lang['cct_view'] = "Afficher un utilisateur";
$lang['cct_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['cct_notif'] = "Types de notifications";
$lang['cct_name'] = "Nom complet";
$lang['cct_mail'] = "Email";
$lang['cct_mailType'] = "Format du Mail";
$lang['cct_pager'] = "Pager";
$lang['cct_hostNotifOpt'] = "Choix de notifications pour les Hosts";
$lang['cct_hostNotifTp'] = "P&eacute;riode de notification pour les Hosts";
$lang['cct_hostNotifCmd'] = "Commandes de notifications pour les Hosts";
$lang['cct_svNotifOpt'] = "Choix de notifications pour les Services";
$lang['cct_svNotifTp'] = "P&eacute;riode de notification pour les Services";
$lang['cct_svNotifCmd'] = "Commandes de notifications pour les Services";
$lang['cct_cgNotif'] = "Contact Groups parent(s)";
$lang['cct_passwd'] = "Mot de passe";
$lang['cct_passwd2'] = "Confirmation";
$lang['cct_lang'] = "Langue principale";
$lang['cct_oreon'] = "Oreon";
$lang['cct_oreon_text'] = "Acc&egrave;de &agrave; l'interface";
$lang['cct_admin'] = "Administrateur";
$lang["cct_contact_auth_type"] = "Type d'authentification";
$lang["cct_ldap_dn"] = "Ldap DN (Distinguished Name)";

/* contact group */

$lang['cg_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['cg_name'] = "Nom du Contact Group";
$lang['cg_alias'] = "Alias";
$lang['cg_members'] = "Contacts rattach&eacute;s";
$lang['cg_notif'] = "Notification";
$lang["cg_add"] = "Ajouter un Contact Group";
$lang["cg_change"] = "Modifier un Contact Group";
$lang["cg_view"] = "Afficher un Contact Group";

/* time period */

$lang['tp_name'] = "Nom de la plage horaire";
$lang['tp_alias'] = "Alias";
$lang['tp_sunday'] = "Dimanche";
$lang['tp_monday'] = "Lundi";
$lang['tp_tuesday'] = "Mardi";
$lang['tp_wednesday'] = "Mercredi";
$lang['tp_thursday'] = "Jeudi";
$lang['tp_friday'] = "Vendredi";
$lang['tp_saturday'] = "Samedi";
$lang['tp_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['tp_notif'] = "Tranche horaire de notification";
$lang["tp_add"] = "Ajouter une Time Period";
$lang["tp_change"] = "Modifier une Time Period";
$lang["tp_view"] = "Afficher une Time Period";

/* command */

$lang['cmd_type'] = "Type de Commande";
$lang['cmd_infos'] = "Informations";
$lang['cmd_check'] = "Commande de Check";
$lang['cmd_notif'] = "Commande de notification";
$lang['cmd_checkShort'] = "Check";
$lang['cmd_notifShort'] = "Notification";
$lang["cmd_add"] = "Ajouter une Commande";
$lang["cmd_change"] = "Modifier une Commande";
$lang["cmd_view"] = "Afficher une Commande";
$lang['cmd_name'] = "Nom de la Commande";
$lang['cmd_line'] = "Ligne de Commande ";
$lang['cmd_type'] = "Type de Commande";
$lang['cmd_comment'] = "Les d&eacute;finitions de Commande peuvent contenir des macros mais vous devez vous assurez pr&eacute;alablement que ces macros sont valides dans les circonstances ou la macro sera utilis&eacute;e.";
$lang['cmd_help'] = "Aide du Plugin";
$lang['cmd_help_output'] = "Aide";
$lang['cmd_output'] = "Sortie";
$lang['cmd_example'] = "Exemple d'argument";

/* Nagios CFG */

$lang["nagios_add"] = "Ajouter un fichier de configuration de Nagios";
$lang["nagios_change"] = "Modifier un fichier de configuration de Nagios";
$lang["nagios_view"] = "Afficher un fichier de configuration de Nagios";
$lang["nagios_infos"] = "Informations";
$lang["nagios_name"] = "Nom de cette configuration";
$lang["nagios_comment"] = "Commentaires pour ce fichier";

$lang["nag_logFile"] = "Log file";
$lang["nag_objConfFile"] = "Object Configuration File";
$lang["nag_objConfDir"] = "Object Configuration Directory";
$lang["nag_objCacheFile"] = "Object Cache File";
$lang["nag_resFile"] = "Resource File";
$lang["nag_tmpFile"] = "Temp File";
$lang["nag_p1File"] = "P1 File";

$lang["nag_statusFile"] = "Status File";
$lang["nag_asuOpt"] = "Aggregated Status Updates Option";
$lang["nag_asuInt"] = "Aggregated Status Data Update Interval";

$lang["nag_nagUser"] = "Nagios User";
$lang["nag_nagGroup"] = "Nagios Group";

$lang["nag_notifOpt"] = "Notification Option";
$lang["nag_svCheckExeOpt"] = "Service Check Execution Option";
$lang["nag_pasSvCheckAccOpt"] = "Passive Service Check Acceptance Option";
$lang["nag_hostCheckExeOpt"] = "Host Check Execution Option";
$lang["nag_pasHostCheckAccOpt"] = "Passive Host Check Acceptance Option";
$lang["nag_eventHandOpt"] = "Event Handler Option";

$lang["nag_logRotMethod"] = "Log Rotation Method";
$lang["nag_logArchPath"] = "Log Archive Path";

$lang["nag_extCmdCheckOpt"] = "External Command Check Option";
$lang["nag_extCmdCheckInt"] = "External Command Check Interval";
$lang["nag_extCmdFile"] = "External Command File";

$lang["nag_cmtFile"] = "Comment File";
$lang["nag_dtFile"] = "Downtime File";
$lang["nag_lockFile"] = "Lock File";

$lang["nag_stateRetOpt"] = "State Retention Option";
$lang["nag_stateRetFile"] = "State Retention File";
$lang["nag_autStateRetUpdInt"] = "Automatic State Retention Update Interval";
$lang["nag_useRetPgmStateOpt"] = "Use Retained Program State Option";
$lang["nag_useRetSchInfoOpt"] = "Use Retained Scheduling Info Option";

$lang["nag_SysLogOpt"] = "Syslog Logging Option";
$lang["nag_notLogOpt"] = "Notification Logging Option";
$lang["nag_svCheckRtrLogOpt"] = "Service Check Retry Logging Option";
$lang["nag_hostRtrLogOpt"] = "Host Retry Logging Option";
$lang["nag_eventHandLogOpt"] = "Event Handler Logging Option";
$lang["nag_iniStateLogOpt"] = "Initial State Logging Option";
$lang["nag_extCmdLogOpt"] = "External Command Logging Option";
$lang["nag_passSvCheckLogOpt"] = "Passive Service Check Logging Option";
$lang["nag_passCheckLogOpt"] = "Passive Check Logging Option";

$lang["nag_glHostEventHand"] = "Global Host Event Handler";
$lang["nag_glSvEventHand"] = "Global Service Event Handler";

$lang["nag_intCheckSleepTm"] = "Inter-Check Sleep Time";
$lang["nag_intCheckDelMth"] = "Inter-Check Delay Method";
$lang["nag_svIntCheckDelMth"] = "Service Inter-Check Delay Method";
$lang["nag_maxSvCheckSpread"] = "Maximum Service Check Spread";
$lang["nag_svInterFac"] = "Service Interleave Factor";
$lang["nag_maxConcSvChecks"] = "Maximum Concurrent Service Checks";
$lang["nag_svReapFreq"] = "Service Repear Frequency";
$lang["nag_hostIntCheckDelMth"] = "Host Inter-Check Delay Method";
$lang["nag_maxHostCheckSpread"] = "Maximum Host Check Spread";
$lang["nag_tmIntLen"] = "Timing Interval Length";
$lang["nag_autoRescheOpt"] = "Auto-Rescheduling Option";
$lang["nag_autoRescheInt"] = "Auto-Rescheduling Interval";
$lang["nag_autoRescheWnd"] = "Auto-Rescheduling Window";

$lang["nag_aggHostCheckOpt"] = "Aggressive Host Checking Option";

$lang["nag_flapDetOpt"] = "Flap Detection Option";
$lang["nag_lowSvFlapThres"] = "Low Service Flap Threshold";
$lang["nag_highSvFlapThres"] = "High Service Flap Threshold";
$lang["nag_lowHostFlapThres"] = "Low Host Flap Threshold";
$lang["nag_highHostFlapThres"] = "High Host Flap Threshold";

$lang["nag_softSvDepOpt"] = "Soft Service Dependencies Option";

$lang["nag_svCheckTmOut"] = "Service Check Timeout";
$lang["nag_hostCheckTmOut"] = "Host Check Timeout";
$lang["nag_eventHandTmOut"] = "Event Handler Timeout";
$lang["nag_notifTmOut"] = "Notification Timeout";
$lang["nag_obComSvProcTmOut"] = "Obsessive Compulsive Service Processor Timeout";
$lang["nag_obComHostProcTmOut"] = "Obsessive Compulsive Host Processor Timeout";
$lang["nag_perfDataProcCmdTmOut"] = "Performance Data Processor Command Timeout";

$lang["nag_obsOverSvOpt"] = "Obsess Over Services Option";
$lang["nag_obsComSvProcCmd"] = "Obsessive Compulsive Service Processor Command";
$lang["nag_obsOverHostOpt"] = "Obsess Over Hosts Option";
$lang["nag_obsComHostProcCmd"] = "Obsessive Compulsive Host Processor Command";

$lang["nag_perfDataProcOpt"] = "Performance Data Processing Option";
$lang["nag_hostPerfDataProcCmd"] = "Host Performance Data Processing Command";
$lang["nag_svPerfDataProcCmd"] = "Service Performance Data Processing Command";
$lang["nag_hostPerfDataFile"] = "Host Performance Data File";
$lang["nag_svPerfDataFile"] = "Service Performance Data File";
$lang["nag_hostPerfDataFileTmp"] = "Host Performance Data File Template";
$lang["nag_svPerfDataFileTmp"] = "Service Performance Data File Template";
$lang["nag_hostPerfDataFileMode"] = "Host Performance Data File Mode";
$lang["nag_svPerfDataFileMode"] = "Service Performance Data File Mode";
$lang["nag_hostPerfDataFileProcInt"] = "Host Performance Data File Processing Interval";
$lang["nag_svPerfDataFileProcInt"] = "Service Performance Data File Processing Interval";
$lang["nag_hostPerfDataFileProcCmd"] = "Host Performance Data File Processing Command";
$lang["nag_svPerfDataFileProcCmd"] = "Service Performance Data File Processing Command";

$lang["nag_OrpSvCheckOpt"] = "Orphaned Service Check Option";

$lang["nag_svFreshCheckOpt"] = "Service Freshness Checking Option";
$lang["nag_svFreshCheckInt"] = "Service Freshness Check Interval";
$lang["nag_freshCheckInt"] = "Freshness Check Interval";
$lang["nag_hostFreshCheckOpt"] = "Host Freshness Checking Option";
$lang["nag_hostFreshCheckInt"] = "Host Freshness Check Interval";

$lang["nag_dateFormat"] = "Date Format";

$lang["nag_illObjNameChar"] = "Illegal Object Name Characters";
$lang["nag_illMacOutChar"] = "Illegal Macro Output Characters";

$lang["nag_regExpMatchOpt"] = "Regular Expression Matching Option";
$lang["nag_trueRegExpMatchOpt"] = "True Regular Expression Matching Option";

$lang["nag_adminEmail"] = "Administrator Email Address";
$lang["nag_adminPager"] = "Administrator Pager";

/* Resource CFG */
$lang['rs_add'] = "Ajouter une Ressource";
$lang['rs_change'] = "Modifier une Ressource";
$lang['rs_view'] = "Afficher une Ressource";
$lang['rs_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['rs_name'] = "Nom de la Ressource";
$lang['rs_line'] = "Expression de la MACRO";

/* Perfparse CFG */
$lang['pp_add'] = "Ajouter un fichier de configuration Perfparse";
$lang['pp_change'] = "Modifier un fichier de configuration Perfparse";
$lang['pp_view'] = "Afficher un fichier de configuration Perfparse";
$lang['pp_infos'] = "Informations g&eacute;n&eacute;rales";
$lang['pp_name'] = "Nom du fichier Perfparse";
$lang['pp_comment'] = "Commentaires sur ce fichier";
$lang['pp_sMan'] = "Server Management";
$lang['pp_serPort'] = "Server Port";
$lang['pp_pMan'] = "Parser Management";
$lang['pp_perfDLF'] = "Performance Data Log Files ('-' for stdin)";
$lang['pp_serLog'] = "Service Log";
$lang['pp_svLPMP'] = "Service Log Position Mark Path";
$lang['pp_errHandling'] = "Error handling";
$lang['pp_errLog'] = "Error Log File";
$lang['pp_errLogRot'] = "Error Log Rotate";
$lang['pp_errLKND'] = "Error Log Keep N Days";
$lang['pp_dropFile'] = "Drop File";
$lang['pp_dropFileRot'] = "Drop File Rotate";
$lang['pp_dropFKND'] = "Drop File Keep N Days";
$lang['pp_lockFileTxt'] = "Lock file for only one perfparse running at the same time";
$lang['pp_lockFile'] = "Lock File";
$lang['pp_reportOpt'] = "Reporting Options";
$lang['pp_showSB'] = "Show Status Bar";
$lang['pp_doReport'] = "Do_Report";
$lang['pp_cgiMan'] = "CGI Management";
$lang['pp_defUPP'] = "Default user permissions Policy";
$lang['pp_defUPHG'] = "Default user permissions Hostgroups";
$lang['pp_defUPS'] = "Default user permissions Summary";
$lang['pp_outLog'] = "Output Logger";
$lang['pp_outLogFile'] = "Output Log File";
$lang['pp_outLogFileName'] = "Output Log Filename";
$lang['pp_outLogRot'] = "Output Log Rotate";
$lang['pp_outLKND'] = "Output Log Keep N Days";
$lang['pp_SockOutMan'] = "Socket_output managment";
$lang['pp_useStoSockOut'] = "Use Storage Socket Output";
$lang['pp_stoSockOutHName'] = "Storage Socket Output Host Name";
$lang['pp_stoSockOutPort'] = "Storage Socket Output Port";
$lang['pp_dbMan'] = "Database managment";
$lang['pp_useStorMySQL'] = "Use Storage Mysql";
$lang['pp_noRawData'] = "No Raw Data";
$lang['pp_noBinData'] = "No Bin Data";
$lang['pp_dbUser'] = "DB User";
$lang['pp_dbName'] = "DB Name";
$lang['pp_dbPass'] = "DP Pass";
$lang['pp_dbHost'] = "DB_Host";
$lang['pp_dumHN'] = "Dummy Hostname";
$lang['pp_stoModLoad'] = "Storage Modules Load";

/* CGI cfg */
$lang['cgi_name'] = "Nom du fichier CGI";
$lang['cgi_comment'] = "Commentaires sur ce fichier";
$lang['cgi_add'] = "Ajouter un fichier de configuration CGI";
$lang['cgi_change'] = "Modifier un fichier de configuration CGI";
$lang['cgi_view'] = "Afficher un fichier de configuration CGI";
$lang['cgi_infos'] = "Informations g&eacute;n&eacute;rales";
$lang["cgi_mainConfFile"] = "Main Configuration File Location";
$lang["cgi_phyHtmlPath"] = "Physical HTML Path";
$lang["cgi_urlHtmlPath"] = "URL HTML Path";
$lang["cgi_nagCheckCmd"] = "Nagios Process Check Command";
$lang["cgi_authUsage"] = "Authentication Usage";
$lang["cgi_defUserName"] = "Default User Name";
$lang["cgi_authFSysInfo"] = "System/Process Information Access";
$lang["cgi_authFSysCmd"] = "System/Process Command Access";
$lang["cgi_authFConfInf"] = "Configuration Information Access";
$lang["cgi_authFAllHosts"] = "Global Host Information Access";
$lang["cgi_authFAllHostCmds"] = "Global Host Command Access";
$lang["cgi_authFAllSv"] = "Global Service Information Access";
$lang["cgi_authFAllSvCmds"] = "Global Service Command Access";
$lang["cgi_smBckImg"] = "Statusmap CGI Background Image";
$lang["cgi_defSMLayMet"] = "Default Statusmap Layout Method";
$lang["cgi_statCGIIncWld"] = "Statuswrl CGI Include World";
$lang["cgi_defStatWRLLay"] = "Default Statuswrl Layout Method";
$lang["cgi_cgIRefRate"] = "CGI Refresh Rate";
$lang["cgi_hus"] = "Host Unreachable Sound";
$lang["cgi_hdu"] = "Host Down Sound";
$lang["cgi_scs"] = "Service Critical Sound";
$lang["cgi_sws"] = "Service Warning Sound";
$lang["cgi_sus"] = "Service Unknown Sound";
$lang["cgi_pingSyntax"] = "Ping Syntax";

/* Generate File */
$lang["gen_name"] = "G&eacute;n&eacute;ration des fichiers de configuration de Nagios";
$lang["gen_infos"] = "Serveur concern&eacute;";
$lang["gen_host"] = "Serveur Nagios / Oreon";
$lang["gen_opt"] = "Options de g&eacute;n&eacute;ration";
$lang["gen_ok"] = "G&eacute;n&eacute;rer les fichiers";
$lang["gen_level"] = "Int&eacute;raction des &eacute;l&eacute;ments";
$lang["gen_level1"] = "Gestion des d&eacute;pendances";
$lang["gen_level2"] = "Activation courante";
$lang["gen_level3"] = "Aucune";
$lang["gen_comment"] = "Inclure les commentaires";
$lang["gen_xml"] = "G&eacute;n&eacute;rer aussi au format XML";
$lang["gen_result"] = "R&eacute;sultat";
$lang["gen_debug"] = "Lancer le debug de Nagios (-v)";
$lang["gen_move"] = "D&eacute;placer les fichiers";
$lang["gen_restart"] = "Recharger Nagios";
$lang["gen_restart_load"] = "Reload";
$lang["gen_restart_start"] = "Restart";
$lang["gen_restart_extcmd"] = "Commande externe";
$lang["gen_butOK"] = "Generer";
$lang["gen_status"] = "Statuts";
$lang['gen_mvOk'] = " - d&eacute;placement OK";
$lang['gen_mvKo'] = " - d&eacute;placement KO";
$lang['gen_trapd'] = "Traps SNMP";
$lang['gen_genTrap'] = "G&eacute;n&eacute;rer les Traps";
$lang['gen_trapRestart'] = "Red&eacute;marrer snmptrad";

/* Upload File */
$lang["upl_name"] = "Upload de configuration Nagios";
$lang["upl_infos"] = "Serveur concern&eacute;";
$lang["upl_host"] = "Serveur Nagios / Oreon";
$lang["upl_opt"] = "Options d'upload";
$lang["upl_del"] = "Supprimer toute la configuration existante pour le type de fichier choisi";
$lang["upl_over"] = "Mettre &agrave; jour une d&eacute;finition en cas de doublons";
$lang["upl_comment"] = "Inclure les commentaires";
$lang["upl_type"] = "Cat&eacute;gorie du Fichier";
$lang["upl_mis1"] = "Pour l'upload d'une archive, la premi&egrave;re ligne de chaque fichier doit etre sans importance car elle n'est pas prise en compte.<br>Evitez de faire d&eacute;marrer une d&eacute;finition d&egrave;s le d&eacute;but.";
$lang["upl_typeNag"] = "nagios.cfg";
$lang["upl_typeCgi"] = "cgi.cfg";
$lang["upl_typePerfparse"] = "perfparse.cfg";
$lang["upl_typeRes"] = "resource.cfg";
$lang["upl_typeCfg"] = "Template based method file";
$lang["upl_typeManual"] = "Saisie manuel";
$lang["upl_format"] = "Format du Fichier";
$lang["upl_typeName"] = "Fichier";
$lang["upl_typeCmdType"] = "Type de Commande";
$lang["upl_typeCmdCheck"] = "Commande de Check";
$lang["upl_typeCmdNotif"] = "Commande de Notification";
$lang["upl_typeCmdCmt1"] = "Il est conseill&eacute; d'uploader en premier les fichiers/d&eacute;finitions de Commande en pr&eacute;cisant leurs types.";
$lang["upl_typeCmdCmt2"] = "La cat&eacute;gorie dans laquelle placer la Commande ne peut &ecirc;tre d&eacute;fini par sa seule d&eacute;finition.";
$lang["upl_file"] = "Fichier (zip, tar ou cfg)";
$lang["upl_manualDef"] = "D&eacute;finition Manuelle";
$lang["upl_result"] = "R&eacute;sultat";
$lang["upl_debug"] = "Lancer le debug de Nagios (-v)";
$lang["upl_butOK"] = "Loader";
$lang["upl_uplOk"] = "Chargement du fichier OK";
$lang["upl_uplKo"] = "Chargement du fichier KO";
$lang["upl_carrOk"] = "R&eacute;cup&eacute;ration des donn&eacute;es OK";
$lang["upl_carrKo"] = "R&eacute;cup&eacute;ration des donn&eacute;es KO";
$lang["upl_manualDefOk"] = "D&eacute;finition Manuelle OK";
$lang["upl_uplBadType"] = "Extension non prise en charge";
$lang["upl_newEntries"] = "entr&eacute;e(s) enregistr&eacute;e(s)";

/* Purge Policy Template */

$lang["mod_purgePolicy"] = "Mod&egrave;le de purge des donn&eacute;es";
$lang["mod_purgePolicy_add"] = "Ajouter un Template de purge";
$lang["mod_purgePolicy_change"] = "Modifier un Template de purge";
$lang["mod_purgePolicy_view"] = "Voir un Template de purge";

$lang["mod_purgePolicy_infos"] = "Informations g&eacute;n&eacute;rales";
$lang["mod_purgePolicy_name"] = "Nom du Mod&egrave;le";
$lang["mod_purgePolicy_alias"] = "Alias";
$lang["mod_purgePolicy_retain"] = "P&eacute;riode de r&eacute;tention";
$lang["mod_purgePolicy_raw"] = "Suppression des raw";
$lang["mod_purgePolicy_raw2"] = "Seulement les raw qui ne figurent pas dans la periode de retention";
$lang["mod_purgePolicy_bin"] = "Suppression des bin";
$lang["mod_purgePolicy_bin2"] = "Seulement les bin qui ne figurent pas dans la periode de retention";
$lang["mod_purgePolicy_metric"] = "Suppression des d&eacute;finitions de m&eacute;trics";
$lang["mod_purgePolicy_metric2"] = "Non li&eacute; &agrave; la p&eacute;riode, SUPPRESSION des Metric + bin";
$lang["mod_purgePolicy_service"] = "Suppression des d&eacute;finitions de service";
$lang["mod_purgePolicy_service2"] = "Non li&eacute; &agrave; la p&eacute;riode, SUPPRESSION du Service + Metric + bin + raw";
$lang["mod_purgePolicy_host"] = "Suppression des d&eacute;finitions d'host";
$lang["mod_purgePolicy_host2"] = "Non li&eacute; &agrave; la p&eacute;riode, SUPPRESSION de l'Host + Service + Metric + bin + raw";
$lang["mod_purgePolicy_comment"] = "Commentaires";

$lang["mod_purgePolicy_listRaw"] = "Raw";
$lang["mod_purgePolicy_listBin"] = "Bin";
$lang["mod_purgePolicy_listMetric"] = "Metric";
$lang["mod_purgePolicy_listService"] = "Service";
$lang["mod_purgePolicy_listHost"] = "Host";

/* Traps SNMP */

$lang['m_traps_command'] = "Traps SNMP";
$lang['m_traps_name'] = "Nom de la Trap";
$lang['m_traps_oid'] = "OID";
$lang['m_traps_handler'] = "Handler";
$lang['m_traps_args'] = "Arguments";
$lang['m_traps_comments'] = "Commentaires";

$lang['m_traps_add'] = "Ajouter une d&eacute;finition de Trap";
$lang['m_traps_change'] = "Modifier une d&eacute;finition de Trap";
$lang['m_traps_view'] = "Voir une d&eacute;finition de Trap";

/* GANTT Escalation */

$lang['m_gantt'] = "Voir Escalades";
$lang['m_header_gantt'] = "Vue des Escalades";

?>