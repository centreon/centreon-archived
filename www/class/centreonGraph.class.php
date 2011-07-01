<?php
/*
 * Copyright 2005-2011 MERETHIS
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License as published by the Free Software
 * Foundation ; either version 2 of the License.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * this program; if not, see <http://www.gnu.org/licenses>.
 *
 * Linking this program statically or dynamically with other modules is making a
 * combined work based on this program. Thus, the terms and conditions of the GNU
 * General Public License cover the whole combination.
 *
 * As a special exception, the copyright holders of this program give MERETHIS
 * permission to link this program with independent modules to produce an executable,
 * regardless of the license terms of these independent modules, and to copy and
 * distribute the resulting executable under terms of MERETHIS choice, provided that
 * MERETHIS also meet, for each linked independent module, the terms  and conditions
 * of the license of that module. An independent module is a module which is not
 * derived from this program. If you modify this program, you may extend this
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 *
 * For more information : contact@centreon.com
 *
 * SVN : $URL$
 * SVN : $Id$
 *
 */

/*
 * this class need also others classes
 */
require_once $centreon_path."www/class/centreonDuration.class.php";
require_once $centreon_path."www/class/centreonGMT.class.php";
require_once $centreon_path."www/class/centreonACL.class.php";
require_once $centreon_path."www/class/centreonDB.class.php";
require_once $centreon_path."www/class/centreonHost.class.php";
require_once $centreon_path."www/class/centreonService.class.php";
require_once $centreon_path."www/class/centreonSession.class.php";
require_once $centreon_path."www/include/common/common-Func.php";

/*
 * Class for XML/Ajax request
 *
 */
class CentreonGraph	{

	/*
	 * Objects
	 */
	var $DB;
	var $DBC;

	var $XML;
	var $GMT;

	var $hostObj;
	var $serviceObj;

	var $session_id;

	/*
	 * private vars
	 */
	protected $_RRDoptions;
	protected $_arguments;
	protected $_argcount;
	protected $_options;
	protected $_colors;
	protected $_fonts;
	protected $_flag;

	/*
	 * Variables
	 */
	var $debug;
	var $compress;
	var $user_id;
	var $general_opt;
	var $filename;
	var $commandLine;
	var $dbPath;
	var $index;
	var $indexData;
	var $template_id;
	var $templateInformations;
	var $gprintScaleOption;
	var $graphID;
	var $metricsActive;
	var $metricsEnabled;
	var $rmetrics;
	var $vmetrics;
	var $mpointer;
	var $mlist;
	var $vname;
	var $metrics;
	var $longer;
	var $onecurve;
	var $checkcurve;

	/*
	 * Class constructor
	 *
	 * <code>
	 * $obj = new CentreonBGRequest($_GET["session_id"], 1, 1, 0, 1);
	 * </code>
	 *
	 * $session_id 	char 	session id
	 * $dbneeds		bool 	flag for enable ndo connexion
	 * $headType	bool 	send XML header
	 * $debug		bool 	debug flag.
	 */
	public function __construct($session_id, $index = null, $debug, $compress = null)
	{
		if (!isset($debug)) {
			$this->debug = 0;
		}

		(!isset($compress)) ? $this->compress = 1 : $this->compress = $compress;

		if (!isset($session_id)) {
			print "Your might check your session id";
			exit(1);
		} else {
			$this->session_id = htmlentities($session_id, ENT_QUOTES, "UTF-8");
		}

		$this->index = htmlentities($index, ENT_QUOTES, "UTF-8");

		/*
		 * Enable Database Connexions
		 */
		$this->DB 		= new CentreonDB();
		$this->DBC 		= new CentreonDB("centstorage");

		/*
		 * Init Objects
		 */
		$this->hostObj		= new CentreonHost($this->DB);
		$this->serviceObj	= new CentreonService($this->DB);

		/*
		 * User ID / Contact ID
		 */
		$this->user_id = check_session($this->session_id, $this->DB);

		/*
		 * Timezone management
		 */
		$this->GMT = new CentreonGMT($this->DB);
		$this->GMT->getMyGMTFromSession($this->session_id, $this->DB);

		$this->_RRDoptions = array();
		$this->_arguments = array();
		$this->_options = array();
		$this->_colors = array();
		$this->_fonts = array();
		$this->_argcount = 0;
		$this->_flag = 0;

		/*
		 * Set default parameters
		 */
		$this->setRRDOption("width", 500);
		$this->setRRDOption("height", 120);

		$this->_getIndexData();

		$this->filename = $this->indexData["host_name"]. "-".$this->indexData["service_description"];
		$this->filename = str_replace(array("/", "\\"), array("-", "-"), $this->filename);

		$this->templateInformations = array();
		$this->metricsEnabled = array();
		$this->metricsActive = array();
		$this->rmetrics = array();
		$this->vmetrics = array();
		$this->mpointer = array(0,0);
		$this->mlist = array();
		$this->vname = array();
		$this->metrics = array();
		$this->onecurve = false;
		$this->checkcurve = false;

		$DBRESULT = $this->DBC->query("SELECT RRDdatabase_path FROM config LIMIT 1");
		$config = $DBRESULT->fetchRow();
		$this->dbPath = $config["RRDdatabase_path"];
		unset($config);
		$DBRESULT->free();

		$DBRESULT = $this->DB->query("SELECT * FROM options");
		while ($opt = $DBRESULT->fetchRow()) {
			$this->general_opt[$opt['key']] = $opt['value'];
		}
		$DBRESULT->free();
		unset($opt);

		if (isset($index)) {
			$DBRESULT = $this->DB->query("SELECT `metric_id` FROM `ods_view_details` WHERE `index_id` = '".$this->index."' AND `contact_id` = '".$this->user_id."'");
			if ($DBRESULT->numRows()) {
				while ($metric_Active = $DBRESULT->fetchRow()){
					$this->metricsActive[$metric_Active["metric_id"]] = $metric_Active["metric_id"];
				}
				$DBRESULT->free();
				unset($metric_Active);
			} else {
				$DBRESULT->free();
				$DBRESULT2 = $this->DBC->query("SELECT metric_id FROM metrics WHERE index_id = '".$this->index."'  AND `hidden` = '0' ORDER BY `metric_name`");
				while ($milist = $DBRESULT2->fetchRow()){
					$odsm[$milist["metric_id"]] = 1;
				}
				$DBRESULT2 = $this->DB->query("SELECT vmetric_id metric_id FROM virtual_metrics WHERE index_id = '".$this->index."' AND ( `hidden` = '0' OR `hidden` IS NULL ) AND vmetric_activate = '1' ORDER BY 'metric_name'");
				while ($milist = $DBRESULT2->fetchRow()){
					$vmilist = "v".$milist["metric_id"];
					$odsm[$vmilist] = 1;
				}
				foreach ($odsm as $mid => $val)    {
					$DBRESULT = $this->DB->query("INSERT INTO `ods_view_details` (`metric_id`, `contact_id`, `all_user`, `index_id`) VALUES ('".$mid."', '".$this->user_id."', '0', '".$this->index."');");
				}
				$DBRESULT2->free();
				unset($odsm);
			}
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $metrics
	 */
	public function setMetricList($metrics)
	{
		if (is_array($metrics) && count($metrics)) {
			$this->metricsEnabled = array_keys($metrics);
		} else if ($metrics != "") {
			$this->metricsEnabled = array($metrics);
		}
	}

	/**
	 *
	 * Initiate the Graph objects
	 */
	public function init()
	{
		$this->setRRDOption("interlaced");
		$this->setRRDOption("imgformat", "PNG");
		if (isset($this->templateInformations["vertical_label"])) {
			$this->setRRDOption("vertical-label", $this->templateInformations["vertical_label"]);
		}

		if ($this->general_opt["rrdtool_version"] != "1.0")
			$this->setRRDOption("slope-mode");

		if ($this->general_opt["rrdtool_version"] == "1.3") {
	       if (isset($this->general_opt["rrdtool_title_font"]) && isset($this->general_opt["rrdtool_title_fontsize"]))
	          $this->setFont("TITLE:", $this->general_opt["rrdtool_title_fontsize"].":".$this->general_opt["rrdtool_title_font"]);
	       if (isset($this->general_opt["rrdtool_unit_font"]) && isset($this->general_opt["rrdtool_unit_fontsize"]))
	          $this->setFont("UNIT:", $this->general_opt["rrdtool_unit_fontsize"].":".$this->general_opt["rrdtool_unit_font"]);
	       if (isset($this->general_opt["rrdtool_axis_font"]) && isset($this->general_opt["rrdtool_axis_fontsize"]))
	          $this->setFont("AXIS:", $this->general_opt["rrdtool_axis_fontsize"].":".$this->general_opt["rrdtool_axis_font"]);
	       if (isset($this->general_opt["rrdtool_title_font"]) && isset($this->general_opt["rrdtool_title_fontsize"]))
	          $this->setFont("WATERMARK:", $this->general_opt["rrdtool_title_fontsize"].":".$this->general_opt["rrdtool_title_font"]);
	       if (isset($this->general_opt["rrdtool_legend_title"]) && isset($this->general_opt["rrdtool_legend_fontsize"]))
	          $this->setFont("LEGEND:", $this->general_opt["rrdtool_legend_fontsize"].":".$this->general_opt["rrdtool_legend_title"]);
	    }

		if (isset($this->templateInformations["base"]) && $this->templateInformations["base"])
			$this->setRRDOption("base", $this->templateInformations["base"]);
		if (isset($this->templateInformations["width"]) && $this->templateInformations["width"])
			$this->setRRDOption("width", $this->templateInformations["width"]);
		if (isset($this->templateInformations["height"]) && $this->templateInformations["height"])
			$this->setRRDOption("height", $this->templateInformations["height"]);

		/*
		 * Init Graph Template Value
		 */
		if (isset($this->templateInformations["bg_grid_color"]) && $this->templateInformations["bg_grid_color"])
			$this->setColor("CANVAS", $this->templateInformations["bg_grid_color"]);

		if (isset($this->templateInformations["bg_color"]) && $this->templateInformations["bg_color"]) {
			$this->setColor("BACK", $this->templateInformations["bg_color"]);
		} else {
			$this->setColor("BACK", "#F0F0F0");
		}

		if (isset($this->templateInformations["police_color"]) && $this->templateInformations["police_color"])
			$this->setColor("FONT", $this->templateInformations["police_color"]);
		if (isset($this->templateInformations["grid_main_color"]) && $this->templateInformations["grid_main_color"])
			$this->setColor("MGRID", $this->templateInformations["grid_main_color"]);
		if (isset($this->templateInformations["grid_sec_color"]) && $this->templateInformations["grid_sec_color"])
			$this->setColor("GRID", $this->templateInformations["grid_sec_color"]);
		if (isset($this->templateInformations["contour_cub_color"]) && $this->templateInformations["contour_cub_color"])
			$this->setColor("FRAME", $this->templateInformations["contour_cub_color"]);
		if (isset($this->templateInformations["col_arrow"]) && $this->templateInformations["col_arrow"])
			$this->setColor("ARROW", $this->templateInformations["col_arrow"]);
		if (isset($this->templateInformations["col_top"]) && $this->templateInformations["col_top"])
			$this->setColor("SHADEA", $this->templateInformations["col_top"]);
		if (isset($this->templateInformations["col_bot"]) && $this->templateInformations["col_bot"])
			$this->setColor("SHADEB", $this->templateInformations["col_bot"]);

		if (isset($this->templateInformations["lower_limit"]) && $this->templateInformations["lower_limit"] != NULL)
			$this->setRRDOption("lower-limit", $this->templateInformations["lower_limit"]);
		if (isset($this->templateInformations["upper_limit"]) && $this->templateInformations["upper_limit"] != NULL)
			$this->setRRDOption("upper-limit", $this->templateInformations["upper_limit"]);
		if ((isset($this->templateInformations["lower_limit"]) && $this->templateInformations["lower_limit"] != NULL) || (isset($this->templateInformations["upper_limit"]) && $this->templateInformations["upper_limit"] != NULL)) {
			$this->setRRDOption("rigid");
			$this->setRRDOption("alt-autoscale-max");
		}

		$this->gprintScaleOption = "%s";
		if (isset($this->templateInformations["scaled"]) && $this->templateInformations["scaled"] == "0"){
			# Disable y-axis scaling
			$this->setRRDOption("units-exponent", 0);
			# Suppress Scaling in Text Output
			$this->gprintScaleOption = "";
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $elem
	 */
	private static function quote($elem) { return "'".$elem."'"; }

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $elem
	 */
	private static function vquote($elem) { return "'".substr($elem,1,strlen($elem)-1)."'"; }

	/**
	 *
	 * Enter description here ...
	 */
	public function initCurveList() {

		if (isset($this->metricsEnabled) && count($this->metricsEnabled) > 0) {
			/* Zoom or Metric Image */
			$l_rmEnabled = array();
			$l_vmEnabled = array();
			foreach( $this->metricsEnabled as $l_id ) {
				if ( preg_match("/^v/",$l_id) ) {
					$l_vmEnabled[] = $l_id;

				} else {
					$l_rmEnabled[] = $l_id;
				}
			}
			/* Create selector for reals metrics */
			if ( count($l_rmEnabled) ) {
				$l_rselector = "metric_id IN (".implode(",", array_map(array("CentreonGraph", "quote"), $l_rmEnabled)).")";
				$this->_log("initCurveList with selector [real]= ".$l_rselector);
			}
			if ( count($l_vmEnabled) ) {
				$l_vselector = "vmetric_id IN (".implode(",", array_map(array("CentreonGraph", "vquote"), $l_vmEnabled)).")";
				$this->_log("initCurveList with selector [virtual]= ".$l_vselector);
			}

		} else {
			/* Full Image */
			$l_rselector = "index_id = '".$this->index."'";
			$l_vselector = $l_rselector;
			$this->_log("initCurveList with selector= ".$l_rselector);
		}

		/* Manage reals metrics */
		if (isset($l_rselector)) {
			$DBRESULT = $this->DBC->query("SELECT host_id, service_id, metric_id, metric_name, unit_name, replace(format(warn,9),',','') warn, replace(format(crit,9),',','') crit FROM metrics AS m, index_data AS i WHERE index_id = id AND ".$l_rselector." AND m.hidden = '0' ORDER BY m.metric_name");
			while ($rmetric = $DBRESULT->fetchRow()){
				$this->mlist[$rmetric["metric_id"]] = $this->mpointer[0]++;
				$this->rmetrics[] = $rmetric;
	 		}
			$DBRESULT->free();
		}

		/* Manage virtuals metrics */
		if (isset($l_vselector)) {
			$DBRESULT = $this->DB->query("SELECT vmetric_id FROM virtual_metrics WHERE ".$l_vselector." ORDER BY vmetric_name");
			while ($vmetric = $DBRESULT->fetchRow()){
				$this->manageVMetric($vmetric["vmetric_id"], NULL, NULL);
			}
			$DBRESULT->free();
		}

		/* Merge all metrics */
		$mmetrics = array_merge($this->rmetrics, $this->vmetrics);
        $DBRESULT->free();

		foreach ($mmetrics as $key => $metric) {

			/*
			 * Check if RRD database is available.
			 */
			if ($this->CheckDBAvailability($metric["metric_id"])) {

				$this->_log("found metric ".$metric["metric_id"]);

				if ( isset($this->metricsEnabled) && count($this->metricsEnabled) && !in_array($metric["metric_id"], $this->metricsEnabled) ) {
					if ( isset($metric["need"]) ) {
						$metric["need"] = 1; /* Hidden Metric */
					} else {
						$this->_log("metric disabled ".$metric["metric_id"]);
						continue;
					}
				}

				if ( isset($this->metricsActive) && count($this->metricsActive) && !isset($this->metricsActive[$metric["metric_id"]]) ) {
	            	if ( isset($metric["need"]) ) {
	                	$metric["need"] = 1; /* Hidden Metric */
	                } else {
						$this->_log("metric inactive ".$metric["metric_id"]);
	                	continue;
	                }
				}

				if (isset($metric["virtual"])) {
					$this->metrics[$metric["metric_id"]]["virtual"] = $metric["virtual"];
				}
				$this->metrics[$metric["metric_id"]]["metric_id"] = $metric["metric_id"];
	#			$this->metrics[$metric["metric_id"]]["index_id"] = $metric["index_id"];
				$this->metrics[$metric["metric_id"]]["metric"] = str_replace(array("/","\\", "%"), array("slash_", "bslash_", "pct_"), $metric["metric_name"]);
				$this->metrics[$metric["metric_id"]]["unit"] = $metric["unit_name"];

				if (!isset($metric["need"]) || $metric["need"] != 1) {
					/** **********************************
					 * Copy Template values
					 */
					$DBRESULT2 = $this->DB->query("SELECT * FROM giv_components_template WHERE ( host_id = '".$metric["host_id"]."' OR host_id IS NULL ) AND ( service_id = '".$metric["service_id"]."' OR service_id IS NULL ) AND ds_name  = '".$metric["metric_name"]."' ORDER BY host_id DESC");
					$ds_data = $DBRESULT2->fetchRow();
					$DBRESULT2->free();

					if (!$ds_data) {
						$ds = array();

						/** *******************************************
						 * Get Matching Template
						 */
						$DBRESULT3 = $this->DB->query("SELECT * FROM giv_components_template");
						if ($DBRESULT3->numRows()) {
							while ($data = $DBRESULT3->fetchRow()) {
								$DBRESULT4 = $this->DBC->query("SELECT * from metrics WHERE index_id = '".$metric["metric_id"]."' AND metric_name = '".$metric["metric_name"]."' AND metric_name LIKE '".$data["ds_name"]."'");
								if ($DBRESULT4->numRows()) {
									$ds_data = $data;
									$DBRESULT4->free();
									break;
								}
								$DBRESULT4->free();
							}
						}
						$DBRESULT3->free();

						if (!isset($ds_data) && !$ds_data) {
							/** *******************************************
							 * Get default info in default template
							 */
							$DBRESULT3 = $this->DB->query("SELECT ds_min, ds_max, ds_last, ds_average, ds_tickness FROM giv_components_template WHERE default_tpl1 = '1' LIMIT 1");
							if ($DBRESULT3->numRows()) {
								foreach ($DBRESULT3->fetchRow() as $key => $ds_val) {
									$ds[$key] = $ds_val;
								}
							}
							$DBRESULT3->free();

							/** ******************************************
							 * Get random color. Only line will be set
							 */
							$ds["ds_color_line"] = $this->getOVDColor($metric["metric_id"]);
							/* $this->metrics[$metric["metric_id"]]["ds_id"] = $ds; */
							$ds_data = $ds;
						}
					}

					/** **********************************
					 * Fetch Datas
					 */
					foreach ($ds_data as $key => $ds_d) {
						if ($key == "ds_transparency") {
							$transparency = dechex(255-($ds_d*255)/100);
							if (strlen($transparency) == 1) {
								$transparency = "0" . $transparency;
							}
							$this->metrics[$metric["metric_id"]][$key] = $transparency;
							unset($transparency);
						} else {
							$this->metrics[$metric["metric_id"]][$key] = $ds_d;
						}
					}

					if (isset($ds_data["ds_legend"]) && strlen($ds_data["ds_legend"]) > 0 ) {
						$this->metrics[$metric["metric_id"]]["legend"] = $ds_data["ds_legend"];
					} else {
						if (!isset($ds_data["ds_name"]) || !preg_match('/DS/', $ds_data["ds_name"], $matches)){
							$this->metrics[$metric["metric_id"]]["legend"] = str_replace(array("slash_", "bslash_", "pct_"), array("/", "\\", "%"), $metric["metric_name"]);
						} else {
							$this->metrics[$metric["metric_id"]]["legend"] = (isset($ds_data["ds_name"]) ? $ds_data["ds_name"] : "");
						}
					}

					if (strcmp($metric["unit_name"], "")) {
						$this->metrics[$metric["metric_id"]]["legend"] .= " (".$metric["unit_name"].") ";
					}

					$this->metrics[$metric["metric_id"]]["legend_len"] = strlen($this->metrics[$metric["metric_id"]]["legend"]);
					$this->metrics[$metric["metric_id"]]["stack"] = (isset($ds_data["ds_stack"]) && $ds_data["ds_stack"] ? $ds_data["ds_stack"] : 0);
					if ($this->onecurve) {
						if (isset($metric["warn"]) && $metric["warn"] != 0) {
	                        			$this->metrics[$metric["metric_id"]]["warn"] = $metric["warn"];
							if (!isset($ds_data["ds_color_area_warn"]) || empty($ds_data["ds_color_area_warn"]))
								$this->metrics[$metric["metric_id"]]["ds_color_area_warn"] = $this->general_opt["color_warning"];
						}
						if (isset($metric["crit"]) && $metric["crit"] != 0) {
	                        			$this->metrics[$metric["metric_id"]]["crit"] = $metric["crit"];
							if (!isset($ds_data["ds_color_area_crit"]) || empty($ds_data["ds_color_area_crit"]))
	                        			$this->metrics[$metric["metric_id"]]["ds_color_area_crit"] = $this->general_opt["color_critical"];
						}
					}

					if (isset($metric["need"])) {
						$this->metrics[$metric["metric_id"]]["need"] = $metric["need"];
					} else {
						$this->metrics[$metric["metric_id"]]["ds_order"] = (isset($ds_data["ds_order"]) && $ds_data["ds_order"] ? $ds_data["ds_order"] : 0);
					}
				} else {
					/* the metric is need for a CDEF metric, but not display */
					$this->metrics[$metric["metric_id"]]["need"] = $metric["need"];
					$this->metrics[$metric["metric_id"]]["ds_order"] = "0";
				}
				if (isset($metric["def_type"])) {
					$this->metrics[$metric["metric_id"]]["def_type"] = $metric["def_type"];
				}
				if (isset($metric["cdef_order"])) {
					$this->metrics[$metric["metric_id"]]["cdef_order"] = $metric["cdef_order"];
				}
				if (isset($metric["rpn_function"])) {
					$this->metrics[$metric["metric_id"]]["rpn_function"] = $metric["rpn_function"];
				}
				if (isset($metric["ds_hidecurve"])) {
					$this->metrics[$metric["metric_id"]]["ds_hidecurve"] = $metric["ds_hidecurve"];
				}
			}
		}
		$DBRESULT->free();

        /*
         * Sort by ds_order,then legend
         */
		uasort($this->metrics, array("CentreonGraph", "_cmpmultiple"));

		/*
		 * add data definitions for each metric
		 */
		$cpt = 0;
		$lcdef = array();
		$this->longer = 0;
		if (isset($this->metrics)) {
			foreach ($this->metrics as $key => $tm){
				if (!isset($tm["virtual"]) && isset($tm["need"]) && $tm["need"] == 1) {
					$this->addArgument("DEF:v".$cpt."=".$this->dbPath.$key.".rrd:".substr($tm["metric"],0,19).":AVERAGE");
					$this->vname[$tm["metric"]] = "v".$cpt;
					$cpt++;
					continue;
				}
				if (isset($tm["virtual"])) {
					$lcdef[$key] = $tm;
					$this->vname[$tm["metric"]] = "vv".$cpt;
					$cpt++;
				} else {
					$l_CMP = ",GT,";
					if (isset($tm["ds_invert"]) && $tm["ds_invert"]) {
						/* Switching RRD options lower-limit & upper-limit */
						if ($this->onecurve)
							$this->switchRRDLimitOption($this->_RRDoptions["lower-limit"],$this->_RRDoptions["upper-limit"]);
						$this->addArgument("DEF:vi".$cpt."=".$this->dbPath.$key.".rrd:".substr($tm["metric"],0,19).":AVERAGE CDEF:v".$cpt."=vi".$cpt.",-1,*");
						if (isset($tm["warn"]) && $tm["warn"] != 0)
							$this->metrics[$key]["warn"] *= -1;
						if (isset($tm["crit"]) && $tm["crit"] != 0)
							$this->metrics[$key]["crit"] *= -1;
						if ($this->onecurve)
							$l_CMP = ",LT,";
					} else
						$this->addArgument("DEF:v".$cpt."=".$this->dbPath.$key.".rrd:".substr($tm["metric"],0,19).":AVERAGE");
					if ($this->onecurve && isset($tm["warn"]) && $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
						$this->addArgument("CDEF:ok".$cpt."=v".$cpt.",".$this->metrics[$key]["warn"].$l_CMP.$this->metrics[$key]["warn"].",v".$cpt.",IF");
						$this->addArgument("CDEF:oc".$cpt."=v".$cpt.",".$this->metrics[$key]["crit"].$l_CMP."v".$cpt.",".$this->metrics[$key]["crit"].",-,0,IF");
						$this->addArgument("CDEF:ow".$cpt."=v".$cpt.",".$this->metrics[$key]["warn"].$l_CMP."v".$cpt.",".$this->metrics[$key]["warn"].",-,oc".$cpt.",-,0,IF");
					}
					$this->vname[$tm["metric"]] = "v".$cpt;
					$cpt++;
				}
				if ($tm["legend_len"] > $this->longer)
					$this->longer = $tm["legend_len"];
			}
		}
		$deftype = array(0 => "CDEF", 1 => "VDEF");
		uasort($lcdef, array("CentreonGraph", "_cmpcdeforder"));
		foreach ($lcdef as $key => $tm){
			$rpn = $this->subsRPN($tm["rpn_function"],$this->vname);
			$arg = $deftype[$tm["def_type"]].":".$this->vname[$tm["metric"]]."=".$rpn;
			$l_CMP = ",GT,";
			if (isset($tm["ds_invert"]) && $tm["ds_invert"]) {
				$this->addArgument($arg.",-1,*");
				/* Switching RRD options lower-limit & upper-limit */
				if ($this->onecurve)
					$this->switchRRDLimitOption($this->_RRDoptions["lower-limit"],$this->_RRDoptions["upper-limit"]);
				if (isset($tm["warn"]) && $tm["warn"] != 0)
					$this->metrics[$key]["warn"] *= -1;
				if (isset($tm["crit"]) && $tm["crit"] != 0)
					$this->metrics[$key]["crit"] *= -1;
				if ($this->onecurve)
				$l_CMP = ",LT,";
			} else
				$this->addArgument($arg);
			if ($this->onecurve && isset($tm["warn"]) && $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
				$nb=substr($this->vname[$tm["metric"]],2,strlen($this->vname[$tm["metric"]])-2);
				$this->addArgument("CDEF:ok".$nb."=".$this->vname[$tm["metric"]].",".$this->metrics[$key]["warn"].$l_CMP.$this->metrics[$key]["warn"].",".$this->vname[$tm["metric"]].",IF");
				$this->addArgument("CDEF:oc".$nb."=".$this->vname[$tm["metric"]].",".$this->metrics[$key]["crit"].$l_CMP.$this->vname[$tm["metric"]].",".$this->metrics[$key]["crit"].",-,0,IF");
				$this->addArgument("CDEF:ow".$nb."=".$this->vname[$tm["metric"]].",".$this->metrics[$key]["warn"].$l_CMP.$this->vname[$tm["metric"]].",".$this->metrics[$key]["warn"].",-,oc".$nb.",-,0,IF");
			}
		}
	}

	/**
	 *
	 * Create Legend on the graph
	 */
	public function createLegend()
	{
		$cpt = 0;
		$rpn_values = "";
		$rpn_expr = "";
		foreach ($this->metrics as $key => $tm) {
			if ( isset($tm["need"]) && $tm["need"] == 1 )
				continue;
			if (!$this->onecurve && isset($tm["ds_hidecurve"]) && $tm["ds_hidecurve"] == 1) {
				$arg = "COMMENT:\"";
			} else {
				if ((isset($tm["ds_filled"]) && $tm["ds_filled"]) || (isset($tm["ds_stack"]) && $tm["ds_stack"])) {
					if ($this->onecurve && isset($tm["warn"]) && $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
						$nb=$cpt;
						if (isset($tm["virtual"]))
							$nb=substr($this->vname[$tm["metric"]],2,strlen($this->vname[$tm["metric"]])-2);
						$arg = "AREA:ok".$nb.$tm["ds_color_area"];
					} else
						$arg = "AREA:".$this->vname[$tm["metric"]].$tm["ds_color_area"];
					if ( $tm["ds_filled"] ) {
						$arg .= $tm["ds_transparency"];
					} else {
						$arg .= "00";
					}
					if ( $cpt != 0 && $tm["ds_stack"] ) {
						$arg .= "::STACK CDEF:vc".$cpt."=".$rpn_values.$this->vname[$tm["metric"]].$rpn_expr;
					}
					$rpn_values .= $this->vname[$tm["metric"]].",";
					$rpn_expr .= ",+";
					$this->addArgument($arg);
					if ($this->onecurve && isset($tm["warn"]) && $tm["warn"] != 0 && isset($tm["crit"]) && $tm["crit"] != 0) {
						$this->addArgument("AREA:ow".$nb.$tm["ds_color_area_warn"]."CF::STACK");
						$this->addArgument("AREA:oc".$nb.$tm["ds_color_area_crit"]."CF::STACK");
					}
				}

				if (!isset($tm["ds_stack"]) || !$tm["ds_stack"] || $cpt == 0) {
					$arg = "LINE".$tm["ds_tickness"].":".$this->vname[$tm["metric"]];
				} else {
					$arg = "LINE".$tm["ds_tickness"].":vc".$cpt;
				}
				$arg .= $tm["ds_color_line"].":\"";
			}

			if (!$this->checkcurve) {
				$arg .= $tm["legend"];
				for ($i = $tm["legend_len"]; $i != $this->longer + 1; $i++) {
					$arg .= " ";
				}
				// Add 2 more spaces if display only legend is set
				if (!$this->onecurve && isset($tm["ds_hidecurve"]) && $tm["ds_hidecurve"] == 1) {
					$arg .= "  ";
				}
				$arg .= "\"";
				$this->addArgument($arg);

				if ($tm["ds_last"]){
					$arg = "GPRINT:".$this->vname[$tm["metric"]].":LAST:\"Last\:%7.2lf".($this->gprintScaleOption);
					$tm["ds_min"] || $tm["ds_max"] || $tm["ds_average"] ? $arg .= "\"" : $arg .= "\\l\" ";
					$this->addArgument($arg);
				}
				if ($tm["ds_min"]){
					$arg = "GPRINT:".$this->vname[$tm["metric"]].":MIN:\"Min\:%7.2lf".($this->gprintScaleOption);
					$tm["ds_max"] || $tm["ds_average"] ? $arg .= "\"" : $arg .= "\\l\" ";
					$this->addArgument($arg);
				}
				if ($tm["ds_max"]){
					$arg = "GPRINT:".$this->vname[$tm["metric"]].":MAX:\"Max\:%7.2lf".($this->gprintScaleOption);
					$tm["ds_average"] ? $arg .= "\"" : $arg .= "\\l\" ";
					$this->addArgument($arg);
				}
				if ($tm["ds_average"]){
					$this->addArgument("GPRINT:".$this->vname[$tm["metric"]].":AVERAGE:\"Average\:%7.2lf".($this->gprintScaleOption)."\\l\"");
				}
				if ($this->onecurve) {
					if (isset($tm["warn"]) && !empty($tm["warn"]) && $tm["warn"] != 0) {
						$this->addArgument("HRULE:".$tm["warn"].$tm["ds_color_area_warn"].":\"Warning  \: ".$this->humanReadable($tm["warn"], $tm["unit"])."\\l\" ");
					}
					if (isset($tm["crit"]) && !empty($tm["crit"]) && $tm["crit"] != 0) {
						$this->addArgument("HRULE:".$tm["crit"].$tm["ds_color_area_crit"].":\"Critical \: ".$this->humanReadable($tm["crit"], $tm["unit"])."\"");
					}
				}
				if ( !$this->onecurve ) {
					$cline = 0;
					if (!isset($tm["ds_jumpline"])) {
						$tm["ds_jumpline"] = 0;
					}
					while ($cline < $tm["ds_jumpline"]) {
						$this->addArgument("COMMENT:\"\\c\"");
						$cline++;
					}
				}
			}
			if (isset($tm["ds_stack"]) && $tm["ds_stack"]) {
				$cpt++;
			}
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $l_value
	 * @param unknown_type $l_unit
	 */
	private function humanReadable($l_value = null, $l_unit)
	{
		if (empty($l_value)) {
			return;
		}

		$l_px = array( "8" => array("1000" => "Y", "1024" =>"Yi"), "7" => array("1000" => "Z", "1024" =>"Zi"), "6" => array("1000" => "E", "1024" =>"Ei"), "5" => array("1000" => "P", "1024" =>"Pi"), "4" => array("1000" => "T", "1024" =>"Ti"), "3" => array("1000" => "G", "1024" =>"Gi"), "2" => array("1000" => "M", "1024" =>"Mi"), "1" => array("1000" => "k", "1024" =>"Ki"), "0" => "", "-1" => "m", "-2" => "µ", "-3" => "n");
		$l_cpx = 0;
		$l_sign ="";
		// base : default [1000]
		if (isset($this->_RRDoptions["base"])) {
			$l_base = $this->_RRDoptions["base"];
		} else {
			$l_base = 1000;
		}
		// unit
		$l_unit = preg_replace("/^[YZEPTGMkKmµn]/", "", $l_unit);

		if ($l_value<0) {
			$l_sign = "-";
			$l_value *= -1;
		}

		if ($l_value<1) {
			do {
				$l_value *= $l_base;
				$l_cpx--;
			} while ($l_value < 1);
		} else {
			while ($l_value > $l_base) {
				$l_value /= $l_base;
				$l_cpx++;
			}
		}
		if ($l_cpx > 0) {
			$l_upx = $l_px[$l_cpx][$l_base];
		} else {
			$l_upx = $l_px[$l_cpx];
		}

		return $l_sign.sprintf("%.2f",$l_value).$l_upx.$l_unit;
	}

	/**
	 *
	 * Enter description here ...
	 */
	private function _getDefaultGraphTemplate()
	{
		$template_id = $this->_getServiceGraphID();
		if ($template_id != "") {
			$this->template_id = $template_id;
			return;
		} else {
			$command_id = getMyServiceField($this->indexData["service_id"], "command_command_id");
			$DBRESULT = $this->DB->query("SELECT graph_id FROM command WHERE `command_id` = '".$command_id."'");
			if ($DBRESULT->numRows())	{
				$data = $DBRESULT->fetchRow();
				if ($data["graph_id"] != 0) {
					$this->template_id = $data["graph_id"];
					unset($data);
					return;
				}
			}
			$DBRESULT->free();
			unset($command_id);
		}
		$DBRESULT = $this->DB->query("SELECT graph_id FROM giv_graphs_template WHERE default_tpl1 = '1' LIMIT 1");
		if ($DBRESULT->numRows())	{
			$data = $DBRESULT->fetchRow();
			$this->template_id = $data["graph_id"];
			unset($data);
			$DBRESULT->free();
			return;
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $template_id
	 */
	public function setTemplate($template_id = null)
	{
		if (isset($template_id)) {
			$template_id = htmlentities($template_id, ENT_QUOTES, "UTF-8");
		}

		if (!isset($template_id)|| !$template_id){
			if ($this->indexData["host_name"] != "_Module_Meta") {
				/*
				 * graph is based on real host/service
				 */
				$this->_getDefaultGraphTemplate();
			} else {
				/*
				 * Graph is based on a module check point
				 */
				$tab = preg_split("/\_/", $this->indexData["service_description"]);
				$DBRESULT = $this->DB->query("SELECT graph_id FROM meta_service WHERE meta_id = '".$tab[1]."'");
				$tempRes = $DBRESULT->fetchRow();
				$DBRESULT->free();
				$this->template_id = $tempRes["graph_id"];
				unset($tempRes);
				unset($tab);
			}
		} else {
			$this->template_id = htmlentities($_GET["template_id"], ENT_QUOTES, "UTF-8");
		}
		$DBRESULT = $this->DB->query("SELECT * FROM giv_graphs_template WHERE graph_id = '".$this->template_id."' LIMIT 1");
		$this->templateInformations = $DBRESULT->fetchRow();
		$DBRESULT->free();

	}

	/**
	 *
	 * Enter description here ...
	 */
	private function _getServiceGraphID()
	{
		$service_id = $this->indexData["service_id"];

		$tab = array();
		while (1) {
			$DBRESULT = $this->DB->query("SELECT esi.graph_id, service_template_model_stm_id FROM service, extended_service_information esi WHERE service_id = '".$service_id."' AND esi.service_service_id = service_id LIMIT 1");
			$row = $DBRESULT->fetchRow();
			if ($row["graph_id"]) {
				$this->graphID = $row["graph_id"];
				return $this->graphID;
			} elseif ($row["service_template_model_stm_id"]) {
				if (isset($tab[$row['service_template_model_stm_id']])) {
				    break;
				}
			    $service_id = $row["service_template_model_stm_id"];
			    $tab[$service_id] = 1;
			} else {
				break;
			}
		}
		return $this->graphID;
	}

	/**
	 *
	 * Get index Data
	 */
	private function _getIndexData()
	{
		if (isset($this->metricsEnabled))
			$svc_instance = $this->metrics[$this->metricsEnabled[0]]["index_id"];
		else
			$svc_instance = $this->index;

		$this->_log("index_data for ".$svc_instance);
		$DBRESULT = $this->DBC->query("SELECT * FROM index_data WHERE id = '".$svc_instance."' LIMIT 1");
		if (!$DBRESULT->numRows()) {
			$this->indexData = 0;
		} else {
			$this->indexData = $DBRESULT->fetchRow();
			/*
			 * Check Meta Service description
			 */
			if (preg_match("/meta_([0-9]*)/", $this->indexData["service_description"], $matches)){
				$DBRESULT_meta = $this->DB->query("SELECT meta_name FROM meta_service WHERE `meta_id` = '".$matches[1]."'");
				$meta = $DBRESULT_meta->fetchRow();
				$this->indexData["service_description"] = $meta["meta_name"];
				unset($meta);
				$DBRESULT_meta->free();
			}
			$this->indexData["host_name"] = $this->indexData["host_name"];
			$this->indexData["service_description"] = $this->indexData["service_description"];
		}
		$DBRESULT->free();

		if (isset($this->metricsEnabled)) {
			$metrictitle = " metric ".$this->metrics[$this->metricsEnabled]["metric_name"];
		} else {
			$metrictitle = "";
		}

		if ($this->indexData["host_name"] != "_Module_Meta") {
			$this->setRRDOption("title", $this->indexData["service_description"]." "._("graph on")." ".$this->indexData["host_name"].$metrictitle);
		} else {
			$this->setRRDOption("title", _("Graph")." ".$this->indexData["service_description"].$metrictitle);
		}
	}

	/**
	 *
	 * Display Start and end time on graph
	 * @param $arg
	 */
	public function addArgument($arg)
	{
		$this->_arguments[$this->_argcount++] = $arg;
	}

	/**
	 * Geneate image...
	 */
	public function displayError()
	{
		$image 	= imagecreate(250,100);
		$fond 	= imagecolorallocate($image,0xEF,0xF2,0xFB);
		$textcolor = imagecolorallocate($image, 0, 0, 255);
		// imagestring($image, 5, 0, 0, "Session: ".$_GET['session_id']."svc_id: ".$_GET["index"], $textcolor);

		/*
		 * Send Header
		 */
		header("Content-Type: image/gif");

		imagegif($image);
		exit;
	}

	/**
	 *
	 * Enter description here ...
	 * @param $name
	 * @param $value
	 */
	public function setFont($name, $value)
	{
		$this->_fonts[$name] = $value;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function setColor($name, $value)
	{
		$this->_colors[$name] = $value;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $name
	 * @param unknown_type $value
	 */
	public function setRRDOption($name, $value = null)
	{
		if (strpos($value, " ")!==false)
			$value = "'".$value."'";
		$this->_RRDoptions[$name] = $value;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $lower
	 * @param unknown_type $upper
	 */
	private function switchRRDLimitOption($lower = null, $upper = null)
	{
		if (is_null($lower))
			unset($this->_RRDoptions["upper-limit"]);
		else
			$this->_RRDoptions["upper-limit"] = $lower;
		if (is_null($upper))
			unset($this->_RRDoptions["lower-limit"]);
		else
			$this->_RRDoptions["lower-limit"] = $upper;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $flag
	 */
	public function setCommandLineTimeLimit($flag)
	{
		if (isset($flag))
			$this->_flag = $flag;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $name
	 * @param unknown_type $bool
	 */
	public function setOption($name, $bool = true)
	{
		$this->_options[$name] = $bool;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $name
	 */
	public function getOption($name)
	{
		if (isset($this->_options[$name]))
			return $this->_options[$name];
		return false;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $encoding
	 */
	public function setHeaders($encoding)
	{
		header("Content-Type: image/png");
		header("Content-Transfer-Encoding: binary");
		header("Content-Disposition: attachment; filename=\"".$this->filename.".png\";");

		if ($this->compress && $encoding) {
			header('Content-Encoding: '.$encoding);
		}
	}

	/**
	 *
	 * Enter description here ...
	 */
	public function displayImageFlow()
	{
		$commandLine = "";

		/*
		 * Send header
		 */
		global $HTTP_ACCEPT_ENCODING;
		if (headers_sent()){
			$encoding = false;
		} else if (strpos($HTTP_ACCEPT_ENCODING, 'x-gzip') !== false){
			$encoding = 'x-gzip';
		} else if (strpos($HTTP_ACCEPT_ENCODING,'gzip') !== false){
			$encoding = 'gzip';
		} else {
			$encoding = false;
		}

		$this->setHeaders($encoding);

		$commandLine = $this->general_opt["rrdtool_path_bin"]." graph - ";

		if ($this->_flag == 0 && $this->GMT->used() ) {
				$this->setRRDOption("start", $this->GMT->getUTCDate($this->_RRDoptions["start"]) );
				$this->setRRDOption("end",   $this->GMT->getUTCDate($this->_RRDoptions["end"]) );
		}
		if ($this->_RRDoptions["end"] - $this->_RRDoptions["start"] > 2160000
		&& $this->_RRDoptions["end"] - $this->_RRDoptions["start"] < 12960000 ) {
			if($this->_RRDoptions["end"] - $this->_RRDoptions["start"] < 12960000 - (86400*7))
				$this->setRRDOption("x-grid", "DAY:1:DAY:7:DAY:7:0:%d/%m");
			else
				$this->setRRDOption("x-grid", "DAY:1:DAY:7:DAY:14:0:%d/%m");
		}

		foreach ($this->_RRDoptions as $key => $value) {
			$commandLine .= "--".$key;
			if (isset($value))
				$commandLine .= "=".$value;
			$commandLine .= " ";
		}
		foreach ($this->_colors as $key => $value) {
			$commandLine .= "--color ".$key.$value." ";
		}
		foreach ($this->_fonts as $key => $value) {
			$commandLine .= "--font ".$key.$value." ";
		}

		/*
		 * ... order does matter!
		 */
		if ($this->_options["comment_time"] == true) {
			$rrd_time  = addslashes($this->GMT->getDate("Y\/m\/d G:i", $this->_RRDoptions["start"]));
			$rrd_time = str_replace(":", "\:", $rrd_time);
			$rrd_time2 = addslashes($this->GMT->getDate("Y\/m\/d G:i", $this->_RRDoptions["end"])) ;
			$rrd_time2 = str_replace(":", "\:", $rrd_time2);
			$commandLine .= " COMMENT:\" From $rrd_time to $rrd_time2 \\c\" ";
		}
		foreach ($this->_arguments as $arg) {
			$commandLine .= " ".$arg." ";
		}

		$commandLine = preg_replace("/(\\\$|`)/", "", $commandLine);
		if ($this->GMT->used())
			$commandLine = "export TZ='CMT".$this->GMT->getMyGMTForRRD()."' ; ".$commandLine;

		$this->_log($commandLine);
		/*
		 * Send Binary Data
		 */
		if (!$this->checkcurve) {
			$fp = popen($commandLine." 2>&1"  , 'r');
			if (isset($fp) && $fp ) {
				$str ='';
				while (!feof ($fp)) {
		  			$buffer = fgets($fp, 4096);
		 			$str = $str . $buffer ;
				}
				print $str;
			}
		} else {
			return $commandLine;
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $name
	 * @param unknown_type $tab
	 * @param unknown_type $defaultValue
	 */
	public function checkArgument($name, $tab, $defaultValue)
	{
		if (isset($name) && isset($tab)) {
			if (isset($tab[$name]))
				return htmlentities($tab[$name], ENT_QUOTES, "UTF-8");
			else
				return htmlentities($defaultValue, ENT_QUOTES, "UTF-8");
		}
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $l_mid
	 */
	public function getOVDColor($l_mid)
	{
		$DBRESULT = $this->DB->query("SELECT `rnd_color` FROM `ods_view_details` WHERE `index_id` = '".$this->index."' AND `metric_id` = '".$l_mid."' AND `contact_id` = '".$this->user_id."'");
                if ($DBRESULT->numRows()) {
			$l_ovd = $DBRESULT->fetchRow();
			$DBRESULT->free();
			if (isset($l_ovd["rnd_color"]) && !empty($l_ovd["rnd_color"]) && preg_match("/^\#[a-f0-9]{6,6}/i", $l_ovd["rnd_color"])) {
				return $l_ovd["rnd_color"];
			}
			$l_rndcolor = $this->getRandomWebColor();
			// Update ods_view_details
			$DBRESULT = $this->DB->query("UPDATE `ods_view_details` SET `rnd_color` = '".$l_rndcolor."' WHERE `index_id` = '".$this->index."' AND `metric_id` = '".$l_mid."' AND `contact_id` = '".$this->user_id."';");
		} else {
			$l_rndcolor = $this->getRandomWebColor();
		}
		return $l_rndcolor;
	}

	/**
	 *
	 * Enter description here ...
	 */
	public 	function getRandomWebColor()
	{
		$web_safe_colors = array('#000033', '#000066', '#000099', '#0000cc',
			'#0000ff', '#003300', '#003333', '#003366', '#003399', '#0033cc',
			'#0033ff', '#006600', '#006633', '#006666', '#006699', '#0066cc',
			'#0066ff', '#009900', '#009933', '#009966', '#009999', '#0099cc',
			'#0099ff', '#00cc00', '#00cc33', '#00cc66', '#00cc99', '#00cccc',
			'#00ccff', '#00ff00', '#00ff33', '#00ff66', '#00ff99', '#00ffcc',
			'#00ffff', '#330000', '#330033', '#330066', '#330099', '#3300cc',
			'#3300ff', '#333300', '#333333', '#333366', '#333399', '#3333cc',
			'#3333ff', '#336600', '#336633', '#336666', '#336699', '#3366cc',
			'#3366ff', '#339900', '#339933', '#339966', '#339999', '#3399cc',
			'#3399ff', '#33cc00', '#33cc33', '#33cc66', '#33cc99', '#33cccc',
			'#33ccff', '#33ff00', '#33ff33', '#33ff66', '#33ff99', '#33ffcc',
			'#33ffff', '#660000', '#660033', '#660066', '#660099', '#6600cc',
			'#6600ff', '#663300', '#663333', '#663366', '#663399', '#6633cc',
			'#6633ff', '#666600', '#666633', '#666666', '#666699', '#6666cc',
			'#6666ff', '#669900', '#669933', '#669966', '#669999', '#6699cc',
			'#6699ff', '#66cc00', '#66cc33', '#66cc66', '#66cc99', '#66cccc',
			'#66ccff', '#66ff00', '#66ff33', '#66ff66', '#66ff99', '#66ffcc',
			'#66ffff', '#990000', '#990033', '#990066', '#990099', '#9900cc',
			'#9900ff', '#993300', '#993333', '#993366', '#993399', '#9933cc',
			'#9933ff', '#996600', '#996633', '#996666', '#996699', '#9966cc',
			'#9966ff', '#999900', '#999933', '#999966', '#999999', '#9999cc',
			'#9999ff', '#99cc00', '#99cc33', '#99cc66', '#99cc99', '#99cccc',
			'#99ccff', '#99ff00', '#99ff33', '#99ff66', '#99ff99', '#99ffcc',
			'#99ffff', '#cc0000', '#cc0033', '#cc0066', '#cc0099', '#cc00cc',
			'#cc00ff', '#cc3300', '#cc3333', '#cc3366', '#cc3399', '#cc33cc',
			'#cc33ff', '#cc6600', '#cc6633', '#cc6666', '#cc6699', '#cc66cc',
			'#cc66ff', '#cc9900', '#cc9933', '#cc9966', '#cc9999', '#cc99cc',
			'#cc99ff', '#cccc00', '#cccc33', '#cccc66', '#cccc99', '#cccccc',
			'#ccccff', '#ccff00', '#ccff33', '#ccff66', '#ccff99', '#ccffcc',
			'#ccffff', '#ff0000', '#ff0033', '#ff0066', '#ff0099', '#ff00cc',
			'#ff00ff', '#ff3300', '#ff3333', '#ff3366', '#ff3399', '#ff33cc',
			'#ff33ff', '#ff6600', '#ff6633', '#ff6666', '#ff6699', '#ff66cc',
			'#ff66ff', '#ff9900', '#ff9933', '#ff9966', '#ff9999', '#ff99cc',
			'#ff99ff', '#ffcc00', '#ffcc33', '#ffcc66', '#ffcc99', '#ffcccc',
			'#ffccff', '#ffff00', '#ffff33', '#ffff66', '#ffff99', '#ffffcc');
			return $web_safe_colors[rand(0,sizeof($web_safe_colors))];
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	private function _cmpmultiple($a, $b)
	{
		if ($a["ds_order"]<$b["ds_order"])
			return -1;
		else if ($a["ds_order"]>$b["ds_order"])
			return 1;
		return strnatcasecmp($a["legend"], $b["legend"]);
		return 0;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $a
	 * @param unknown_type $b
	 */
	private function _cmpcdeforder($a, $b)
	{
		if ( $a["cdef_order"] == $b["cdef_order"] )
			return 0;
		return ( $a["cdef_order"] < $b["cdef_order"] ) ? -1 : 1;
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $rpn
	 * @param unknown_type $vname
	 * @param unknown_type $suffix
	 */
    private function subsRPN($rpn, $vname, $suffix = null) {
		$l_list = preg_split("/\,/",$rpn);
        $l_rpn = "";
        $l_err = 0;
        foreach ($l_list as $l_m) {
        	if (isset($vname[$l_m])) {
            	if ($suffix == NULL) {
                	$l_rpn .= $vname[$l_m].",";
            	} else if (isset($vname[$l_m.$suffix])) {
                	$l_rpn .= $vname[$l_m.$suffix].",";
        		} else {
                	$l_err = 1;
        		}
            } else {
            	$l_rpn .= $l_m.",";
            }
            if ($l_err == 0) {
            	return substr($l_rpn,0,strlen($l_rpn) - 1);
            } else {
            	return "No_RPN_Found";
            }
        }
	}

	/**
	 *
	 * need : [0]->need/visible [1]->need/hidden
	 * @param unknown_type $v_id
	 * @param unknown_type $v_name
	 * @param unknown_type $index_id
	 */
	private function manageVMetric($v_id, $v_name, $index_id) {
    	/* Recursif function */

        /*
         * Manage Virtual Metrics
         */
		$l_whidden = "";
        if (!$this->checkcurve) {
        	$l_whidden = " AND (hidden = '0' OR hidden IS NULL) AND vmetric_activate = '1'";
        }

        if (is_null($v_id)) {
			$l_where = "vmetric_name = '".$v_name."' AND index_id ='".$index_id."'";
        } else {
        	$l_where = "vmetric_id = '".$v_id."'".$l_whidden;
        }


		$l_pqy = $this->DB->query("SELECT vmetric_id metric_id, index_id, vmetric_name metric_name, unit_name, replace(format(warn,9),',','') warn, replace(format(crit,9),',','') crit, def_type, rpn_function FROM virtual_metrics WHERE ".$l_where." ORDER BY metric_name");
        /*
         * There is only one metric_id
         */
        if ($l_pqy->numRows() == 1) {
        	$l_vmetric = $l_pqy->fetchRow();
        	$l_pqy->free();
        	if (!isset($this->mlist["v".$l_vmetric["metric_id"]])) {
        		if (is_null($v_id)) {
					$l_vmetric["need"] = 1; /* 1 : Need this virtual metric : Hidden */
        		}




                                /* Find Host/Service For this metric_id */
                                $l_poqy = $this->DBC->query("SELECT host_id, service_id FROM index_data WHERE id = '".$l_vmetric["index_id"]."'");
                                $l_indd = $l_poqy->fetchRow();
                                $l_poqy->free();
                                /* Check for real or virtual metric(s) in the RPN function */
                                $l_mlist = preg_split("/\,/", $l_vmetric["rpn_function"]);
                                foreach ( $l_mlist as $l_mnane ) {
                                        /* Check for a real metric */
					$l_poqy = $this->DBC->query("SELECT host_id, service_id, metric_id, metric_name, unit_name, replace(format(warn,9),',','') warn, replace(format(crit,9),',','') crit FROM metrics AS m, index_data as i WHERE index_id = id AND index_id = '".$l_vmetric["index_id"]."' AND metric_name = '".$l_mnane."'");
                                        if ( $l_poqy->numRows() == 1) {
                                                /* Find a real metric in the RPN function */
                                                $l_rmetric = $l_poqy->fetchrow();
                                                $l_poqy->free();
                                                $l_rmetric["need"] = 1; /* 1 : Need this real metric - hidden */
                                                if ( !isset($this->mlist[$l_rmetric["metric_id"]]) ) {
                                                        $this->mlist[$l_rmetric["metric_id"]] = $this->mpointer[0]++;
                                                        $this->rmetrics[] = $l_rmetric;
                                                } else {
                                                        /* We Already Find the real metrics in the array */
                                                        /* Make sure, it's add */
                                                        $l_pointer = $this->mlist[$l_rmetric["metric_id"]];
                                                        if ( !isset($this->rmetrics[$l_pointer]["need"]) )
                                                                $this->rmetrics[$l_pointer]["need"] = 0;
                                                }
                                        } elseif ( $l_poqy->numRows() == 0 ) {
                                                /* key : id or vname and iid */
                                                $l_poqy->free();
                                                $this->manageVMetric(NULL, $l_mnane, $l_vmetric["index_id"]);
                                        } else
                                                $l_poqy->free();
                                }
                                $l_vmetric["metric_id"] = "v".$l_vmetric["metric_id"];
                                $l_vmetric["host_id"] = $l_indd["host_id"];
                                $l_vmetric["service_id"] = $l_indd["service_id"];
                                $l_vmetric["virtual"] = 1;
                                $l_vmetric["warn"] = $l_vmetric["warn"];
                                $l_vmetric["crit"] = $l_vmetric["crit"];
                                $l_vmetric["cdef_order"]=$this->mpointer[1];
                                $this->mlist[$l_vmetric["metric_id"]] = $this->mpointer[1]++;
                                $this->vmetrics[] = $l_vmetric;
                        } else {
                                /* We Already Find the virtual metrics in the array */
                                /* Make sure, it's add */
                                $l_pointer = $this->mlist["v".$l_vmetric["metric_id"]];
                                if ( is_null($v_id) )
                                        if ( !isset($this->vmetrics[$l_pointer]["need"]) || $this->vmetrics[$l_pointer]["need"] != 1 )
                                                $this->vmetrics[$l_pointer]["need"] = 0;
                                else
                                        if ( !isset($this->vmetrics[$l_pointer]["need"]) || $this->vmetrics[$l_pointer]["need"] == 1 )
                                                $this->vmetrics[$l_pointer]["need"] = 0;
                        }
                } else {
                        $l_pqy->free();
                }
        }

    /**
     *
     * Enter description here ...
     * @param unknown_type $message
     */
	private function _log($message) {
		if ($this->general_opt['debug_rrdtool'])
			error_log("[" . date("d/m/Y H:s") ."] RDDTOOL : ".$message." \n", 3, $this->general_opt["debug_path"]."rrdtool.log");
	}

	/**
	 *
	 * Enter description here ...
	 * @param unknown_type $metric_id
	 */
	private function checkDBAvailability($metric_id) {
		if (!file_exists($this->dbPath.$metric_id.".rrd") && !preg_match("/^v/",$metric_id)) {
			return 0;
		}
		return 1;
	}

}

?>
