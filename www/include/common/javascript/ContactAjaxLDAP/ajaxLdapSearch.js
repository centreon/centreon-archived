/*
 * Centreon is developped with GPL Licence 2.0 :
 * http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 * Developped by : Julien Mathis - Romain Le Merlus 
 * 
 * The Software is provided to you AS IS and WITH ALL FAULTS.
 * Centreon makes no representation and gives no warranty whatsoever,
 * whether express or implied, and without limitation, with regard to the quality,
 * any particular or intended purpose of the Software found on the Centreon web site.
 * In no event will Centreon be liable for any direct, indirect, punitive, special,
 * incidental or consequential damages however they may arise and even if Centreon has
 * been previously advised of the possibility of such damages.
 * 
 * For information : contact@centreon.com
 */

// JavaScript Document

//var xhrM = null;
var _addrSearchM = "include/configuration/configObject/contact/ldapsearch.php" //l'adresse   interroger pour trouver les suggestions

function getXhrM(){
	if(window.XMLHttpRequest) // Firefox et autres
	   var xhrM = new XMLHttpRequest();
	else if(window.ActiveXObject){ // Internet Explorer
	   try {
                var xhrM = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                var xhrM = new ActiveXObject("Microsoft.XMLHTTP");
            }
	} else { // XMLHttpRequest non support� par le navigateur
		alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
		var xhrM = false;
	}
	return xhrM;
}

function LdapSearch(){

	_ldap_search_filter=encodeURIComponent(document.getElementsByName('ldap_search_filter')[0].value);
	_ldap_base_dn=encodeURIComponent(document.getElementsByName('ldap_base_dn')[0].value);
	_ldap_search_timeout=encodeURIComponent(document.getElementsByName('ldap_search_timeout')[0].value);
	_ldap_search_limit=encodeURIComponent(document.getElementsByName('ldap_search_limit')[0].value);

	var xhrM = getXhrM();

	xhrM.open("POST",_addrSearchM ,true);
	xhrM.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrM.send("ldap_search_filter="+_ldap_search_filter+"&ldap_base_dn="+_ldap_base_dn+"&ldap_search_timeout="+_ldap_search_timeout +"&ldap_search_limit="+_ldap_search_limit);

	document.getElementById('ldap_search_result_output').innerHTML = "<img src='./img/icones/16x16/spinner_blue.gif'>" ;

	// On defini ce qu'on va faire quand on aura la reponse
	xhrM.onreadystatechange = function() {
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		document.getElementById('ldap_search_result_output').innerHTML =  xhrM.responseText;
		if (xhrM && xhrM.readyState == 4 && xhrM.status == 200 && xhrM.responseXML ) {
			//alert(xhrM.responseText);
			document.getElementById('ldap_search_result_output').innerHTML = '' ;
	
			reponse = xhrM.responseXML.documentElement;
	
			var _tab = document.createElement('table');
	
			_tab.setAttribute("style", "text-align:center;");
			_tab.setAttribute("class", "ListTable");
			var _tbody = document.createElement('tbody');
	
			var _tr = null;
			var _td0 = null;
			var _td1 = null;
			var _td2 = null;
			var _td3 = null;
			var _td4 = null;
			var _td5 = null;
			var _td6 = null;
			var _dntext = null;
			var _tr = null;

		_tr =  document.createElement('tr');
		_tr.className = "ListHeader";
		_td0 = document.createElement('td');
    	_td1 = document.createElement('td');
    	_td2 = document.createElement('td');
    	_td3 = document.createElement('td');
    	_td4 = document.createElement('td');
    	_td5 = document.createElement('td');
    	_td6 = document.createElement('td');

   		_td0.className = "ListColHeaderPicker";
    	_td1.className = "ListColHeaderCenter";
    	_td2.className = "ListColHeaderCenter";
    	_td3.className = "ListColHeaderCenter";
    	_td4.className = "ListColHeaderCenter";
    	_td5.className = "ListColHeaderCenter";
    	_td6.className = "ListColHeaderCenter";

		var cbx = document.createElement("input");
		cbx.type = "checkbox";
		cbx.id = "checkall";
		cbx.name = "checkall";
		cbx.value = "checkall";
		cbx.setAttribute("onclick","checkUncheckAll(this);");
		cbx.onclick = "checkUncheckAll(this);";

     	_td0.appendChild(cbx);
		_td1.appendChild(document.createTextNode('DN'));
		_td2.appendChild(document.createTextNode('UID'));
		_td3.appendChild(document.createTextNode('Givenname'));
		_td4.appendChild(document.createTextNode('SN'));
		_td5.appendChild(document.createTextNode('CN'));
		_td6.appendChild(document.createTextNode('Email'));

    	_tr.appendChild(_td0);
		_tr.appendChild(_td1);
		_tr.appendChild(_td2);
		_tr.appendChild(_td3);
		_tr.appendChild(_td4);
		_tr.appendChild(_td5);
		_tr.appendChild(_td6);
		_tbody.appendChild(_tr);

		var infos = reponse.getElementsByTagName("user");

			for (var i = 0 ; i < infos.length ; i++) {

				var info = infos[i];
			//	if (info.getAttribute('isvalid') == 1) {
					if (info.getElementsByTagName("dn")[0].getAttribute('isvalid') == 1)
						var _dn = info.getElementsByTagName("dn")[0].firstChild.nodeValue;
					else
						var _dn = "-";

					if 	(info.getElementsByTagName("sn")[0].getAttribute('isvalid') == 1)
						var _sn = info.getElementsByTagName("sn")[0].firstChild.nodeValue;
					else
						var _sn = "-";

					if 	(info.getElementsByTagName("mail")[0].getAttribute('isvalid') == 1)
						var _mail = info.getElementsByTagName("mail")[0].firstChild.nodeValue;
					else
						var _mail = "-";

					if (info.getElementsByTagName("uid")[0].getAttribute('isvalid') == 1)
						var _uid = info.getElementsByTagName("uid")[0].firstChild.nodeValue;
					else
						var _uid = "-";

					if (info.getElementsByTagName("givenname")[0].getAttribute('isvalid') == 1)
						var _givenname = info.getElementsByTagName("givenname")[0].firstChild.nodeValue;
					else
						var _givenname = "-";

					if (info.getElementsByTagName("cn")[0].getAttribute('isvalid') == 1)
						var _cn = info.getElementsByTagName("cn")[0].firstChild.nodeValue;
					else
						var _cn = "-";

	    			_tr =  document.createElement('tr');

	    			var ClassName = "list_one";
					if(i%2)
					{
						ClassName = "list_two";
					}
					_tr.className = ClassName;
	    			_td0 = document.createElement('td');
	    			_td1 = document.createElement('td');
	    			_td2 = document.createElement('td');
	    			_td3 = document.createElement('td');
	    			_td4 = document.createElement('td');
	    			_td5 = document.createElement('td');
	    			_td6 = document.createElement('td');
	    			_td0.className = "ListColHeaderPicker";
	    			_td1.className = "ListColHeaderCenter";
	    			_td2.className = "ListColHeaderCenter";
	    			_td3.className = "ListColHeaderCenter";
	    			_td4.className = "ListColHeaderCenter";
	    			_td5.className = "ListColHeaderCenter";
	    			_td6.className = "ListColHeaderCenter";

					var cbx = document.createElement("input");
	  				cbx.type = "checkbox";
	  				if (info.getAttribute('isvalid') == 0) {
	  					cbx.disabled = "1";
	  				}
	  				cbx.id = "contact_select"+i;
	  				cbx.name = "contact_select[select]["+i+"]";
	  				cbx.value = i;

	  				var h_dn = document.createElement("input");
	  				h_dn.type = "hidden";
	  				h_dn.id = "user_dn"+i;
	  				h_dn.name = "contact_select[dn]["+i+"]";
	  				h_dn.value = _dn;

	  				var h_uid = document.createElement("input");
	  				h_uid.type = "hidden";
	  				h_uid.id = "contact_alias"+i;
	  				h_uid.name = "contact_select[contact_alias]["+i+"]";
	  				h_uid.value = _uid;

	  				var h_givenname = document.createElement("input");
	  				h_givenname.type = "hidden";
	  				h_givenname.id = "user_givenname"+i;
	  				h_givenname.name = "contact_select[givenname]["+i+"]";
	  				h_givenname.value = _givenname;

	  				var h_sn = document.createElement("input");
	  				h_sn.type = "hidden";
	  				h_sn.id = "user_sn"+i;
	  				h_sn.name = "contact_select[sn]["+i+"]";
	  				h_sn.value = _sn;

	  				var h_cn = document.createElement("input");
	  				h_cn.type = "hidden";
	  				h_cn.id = "contact_name"+i;
	  				h_cn.name = "contact_select[contact_name]["+i+"]";
	  				h_cn.value = _cn;

	  				var h_mail = document.createElement("input");
	  				h_mail.type = "hidden";
	  				h_mail.id = "contact_email"+i;
	  				h_mail.name = "contact_select[contact_email]["+i+"]";
	  				h_mail.value = _mail;


					_td0.appendChild(cbx);
					_td1.appendChild(document.createTextNode(_dn));
					_td1.appendChild(h_dn);
					_td2.appendChild(document.createTextNode(_uid));
					_td2.appendChild(h_uid);
					_td3.appendChild(document.createTextNode(_givenname));
					_td3.appendChild(h_givenname);
					_td4.appendChild(document.createTextNode(_sn));
					_td4.appendChild(h_sn);
					_td5.appendChild(document.createTextNode(_cn));
					_td5.appendChild(h_cn);
					_td6.appendChild(document.createTextNode(_mail));
					_td6.appendChild(h_mail);

					_tr.appendChild(_td0);
					_tr.appendChild(_td1);
					_tr.appendChild(_td2);
					_tr.appendChild(_td3);
					_tr.appendChild(_td4);
					_tr.appendChild(_td5);
					_tr.appendChild(_td6);
					_tbody.appendChild(_tr);

				//	document.getElementById('ldap_search_result_output').innerHTML = _dn;
				}
		//	}
			_tab.appendChild(_tbody);
			document.getElementById('ldap_search_result_output').appendChild(_tab);
		}
	}

}