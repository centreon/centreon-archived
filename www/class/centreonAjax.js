/*
 * Copyright 2005-2009 MERETHIS
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
 */

function mk_pagination(){;}
function mk_paginationFF(){;}
function set_header_title(){;}
document.write("<script type='text/javascript' src='./include/common/javascript/xslt.js'></script>");

function CentreonAjax(xmlFile, xslFile, elementId)
{
	this._xslFile = xslFile;
	this._xmlFile = xmlFile;
	this._target = elementId;
	this._time;
	this._proc;
	this._tObj;
	var _self = this;
	
	this.setTime = function (t)
	{
		this._time = t * 1000;
	}
	
	this.setXslFile = function (xslFile)
	{
		this._xslFile = xslFile;
	}
	
	this.setXmlFile = function (xmlFile)
	{
		this._xmlFile = xmlFile;
	}
	
	this.setTarget = function (elementId)
	{
		this._target = elementId;
	}
	
	this.start = function ()
	{		
		_self._proc = new Transformation();
		_self._proc.setXml(_self._xmlFile);
		_self._proc.setXslt(_self._xslFile);
		_self._proc.transform(_self._target);
		if (_self._time) {
			_self._tObj = setTimeout(function(){_self.start();}, _self._time);
		}
	}
	
	this.stop = function ()
	{
		if (_self._tObj) {
			clearTimeout(_self._tObj);
		}
	}
}