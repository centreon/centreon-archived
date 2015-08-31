<?php
/*
 * Copyright 2015 Centreon (http://www.centreon.com/)
 * 
 * Centreon is a full-fledged industry-strength solution that meets 
 * the needs in IT infrastructure and application monitoring for 
 * service performance.
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *    http://www.apache.org/licenses/LICENSE-2.0  
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * For more information : contact@centreon.com
 * 
 */

?>
<script language='javascript' type='text/javascript'>
function mk_pagination(){}
function mk_paginationFF(){}
function set_header_title(){}

var o = '<?php echo $o;?>';
var _cmdId = '<?php echo $cmdId;?>';
var _svcId = '<?php echo $service_id;?>';
var _svcTplId = '<?php echo $serviceTplId;?>';

/**
 *
 */
function transformForm()
{
    var params;
    var proc;
    var addrXML;
    var addrXSL;

    params = '?cmdId=' + _cmdId + '&svcId=' + _svcId + '&svcTplId=' + _svcTplId + '&o=' + o;
    proc = new Transformation();
    addrXML = './include/configuration/configObject/service/xml/argumentsXml.php' + params;
    addrXSL = './include/configuration/configObject/service/xsl/arguments.xsl';
    proc.setXml(addrXML);
    proc.setXslt(addrXSL);
    proc.transform("dynamicDiv");
	trapId = 0;
}

/**
 *
 */
function changeCommand(value)
{
	_cmdId = value;
	_templateId = document.getElementById('svcTemplate').value;
	transformForm();
}

/**
 *
 */
function changeServiceTemplate(value)
{
	_svcTplId = value;
	_cmdId = document.getElementById('checkCommand').value;
	transformForm();
}
</script>