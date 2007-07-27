<?
/**
Oreon is developped with GPL Licence 2.0 :
http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
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

?>
<SCRIPT LANGUAGE="JavaScript">

var _current_id = '1';

function mreload(id) {
	_f3=document.getElementById('f3');
	_f2=document.getElementById('f2');
	
	_current_id = id;
	var _tableforajax = document.getElementById('tab' + id);
	var _tableAjax = null;
    var childrenNumber = _tableforajax.childNodes.length;

    for (var i = 0; i < childrenNumber; i++)
    {
		var element = _tableforajax.childNodes[i];
      	var elementName = element.nodeName.toLowerCase();
		if (elementName == 'table')
		{
		    var childrenNumbertable = element.childNodes.length;
		    for (var j = 0; j < childrenNumbertable; j++) 
		    {
				var elementtable = element.childNodes[j];
		  		var elementNametable = elementtable.nodeName.toLowerCase();
				if (elementNametable == 'tbody')
				{
					_tableAjax = elementtable;
				}
			}
		}
	}
	
	_tableAjax.innerHTML = "";
	
	if (id == 1){
	}
	else if (id == 2){
			MyLoading('Loading...');
			_f2.status.value = 1;
			_f3.status.value = 1;
			get_network_vlan(_host_id);
	}
	else if (id == 3){
			MyLoading('Loading...');
			_f2.status.value = 1;
			_f3.status.value = 1;
			get_network_vlan(_host_id);
	}
	else
	{
		;
	}
}

function montre(id) {
	_current_id = id;
	_host_id=<?=$host_id?>;

		for (var i = 1; document.getElementById('c'+i); i++) {
				document.getElementById('c'+i).className='b';
		}
		document.getElementById('c'+id).className='a';
		if(id == 2 || id == 3 )
			document.getElementById('mreload'+id).className='c';
			
		var d = document.getElementById('tab'+id);

		if(id == 2 || id == 3)
			var dreload = document.getElementById('mreload'+id);

		for (var i = 1; document.getElementById('tab'+i); i++) {
			document.getElementById('tab'+i).style.display='none';
			if(i == 2 || i == 3)
				document.getElementById('mreload'+i).style.display='none';
		}
	if (d) {
	d.style.display='block';
	}
	if (dreload && (id == 2 || id == 3)) {
	dreload.style.display='block';
	}
	_f3=document.getElementById('f3');
	_f2=document.getElementById('f2');

	if (id == 1){
	}
	else if (id == 2){
		if(_f2.status.value == '0')
		{
			MyLoading('Loading...');
			_f2.status.value = 1;
			_f3.status.value = 1;
			get_network_vlan(_host_id);
		}
	}	
	else if (id == 3){
		if(_f3.status.value == '0')
		{
			MyLoading('Loading...');
			_f2.status.value = 1;
			_f3.status.value = 1;
			get_network_vlan(_host_id);
		}
	}
	else
	{
		;
	}
}

function MyHiddenDiv() {
	_divmsg = document.getElementById('msg');
	_divmsg.className = "cachediv";

}

function MyIsLoading(_txt) {
	_img = document.getElementById('isrefresh' + _current_id);
	_img.className = "cachediv";
	_img = document.getElementById('refresh' + _current_id);
	_img.className = "ok";

	_divmsg = document.getElementById('msg');
	_divmsg.innerHTML = '';
	_divmsg.className = "msg_isloading";
	var _text = document.createTextNode(_txt);
	_divmsg.appendChild(_text);
	setTimeout('MyHiddenDiv()', '2000');
}

function MyLoading(_txt) {
	_img = document.getElementById('refresh' + _current_id);
	_img.className = "cachediv";

	_img = document.getElementById('isrefresh' + _current_id);
	_img.className = "ok";
	_divmsg = document.getElementById('msg');
	_divmsg.innerHTML = '';
	
	var _text = document.createTextNode(_txt);
	_divmsg.className = "msg_loading";
	_divmsg.appendChild(_text);
}

var xhrIDCard = null; 
var _adrrsearchIDCard = "./include/inventory/IDCard_network/infosNetworkXML.php" //l'adresse   interroger pour trouver les suggestions
	 
function getXhrIDCard(){
	if(window.XMLHttpRequest) // Firefox et autres
	   xhrIDCard = new XMLHttpRequest(); 
	else if(window.ActiveXObject){ // Internet Explorer 
	   try {
                xhrIDCard = new ActiveXObject("Msxml2.XMLHTTP");
            } catch (e) {
                xhrIDCard = new ActiveXObject("Microsoft.XMLHTTP");
            }
	}
	else { // XMLHttpRequest non supportÃ¯Â¿Âœ par le navigateur 
	   alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest..."); 
	   xhrIDCard = false; 
	} 
}

function get_network_vlan(_host_id)
{
	var _tableforajax = document.getElementById('tab2');
	var _tableAjax = null;
    var childrenNumber = _tableforajax.childNodes.length;


    for (var i = 0; i < childrenNumber; i++) 
    {
		var element = _tableforajax.childNodes[i];
      	var elementName = element.nodeName.toLowerCase();
		if (elementName == 'table')
		{
		    var childrenNumbertable = element.childNodes.length;
		    for (var j = 0; j < childrenNumbertable; j++) 
		    {
				var elementtable = element.childNodes[j];
		  		var elementNametable = elementtable.nodeName.toLowerCase();
		  		
				if (elementNametable == 'tbody')
				{
					_tableAjax = elementtable;
				}
			}
		}
	}
	_tableAjax.innerHTML = "";
	
	var _tableforajax3 = document.getElementById('tab3');
	var _tableAjax3 = null;
    var childrenNumber3 = _tableforajax3.childNodes.length;


    for (var i = 0; i < childrenNumber3; i++) 
    {
		var element3 = _tableforajax3.childNodes[i];
      	var elementName3 = element3.nodeName.toLowerCase();
		if (elementName3 == 'table')
		{
		    var childrenNumbertable3 = element3.childNodes.length;
		    for (var j = 0; j < childrenNumbertable3; j++) 
		    {
				var elementtable3 = element3.childNodes[j];
		  		var elementNametable3 = elementtable3.nodeName.toLowerCase();
		  		
				if (elementNametable3 == 'tbody')
				{
					_tableAjax3 = elementtable3;
				}
			}
		}
	}
	_tableAjax3.innerHTML = "";

	getXhrIDCard()
	// On defini ce qu'on va faire quand on aura la reponse
	xhrIDCard.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrIDCard.readyState == 4 && xhrIDCard.status == 200 && xhrIDCard.responseXML)
		{
			reponseIDCard = xhrIDCard.responseXML.documentElement;

			var _networks = reponseIDCard.getElementsByTagName("network");

			for (var i = 0 ; i < _networks.length ; i++) {
				var _network = _networks[i];

				var _interfaceName = _network.getElementsByTagName("interfaceName")[0].firstChild.nodeValue;
				var _Status = _network.getElementsByTagName("Status")[0].firstChild.nodeValue;
				var _PhysAddress = _network.getElementsByTagName("PhysAddress")[0].firstChild.nodeValue;
				var _Type = _network.getElementsByTagName("Type")[0].firstChild.nodeValue;
				var _Trafic = _network.getElementsByTagName("Trafic")[0].firstChild.nodeValue;
				var _ipAdress = _network.getElementsByTagName("ipAddress")[0].firstChild.nodeValue;
				var _Speed = _network.getElementsByTagName("Speed")[0].firstChild.nodeValue;
				var _errorPaquet = _network.getElementsByTagName("errorPaquet")[0].firstChild.nodeValue;

				var _interfaceName_label = '<?=$lang["s_status"]?>';
				var _Status_label = '<?=$lang["s_status"]?>';
				var _PhysAddress_label = '<?=$lang["s_PhysAddress"]?>';
				var _Type_label = '<?=$lang["s_Type"]?>';
				var _Trafic_label = '<?=$lang["s_traffic"]?>';
				var _ipAdress_label = '<?=$lang["s_ipadress"]?>';
				var _Speed_label = '<?=$lang["s_speed"]?>';
				var _errorPaquet_label = '<?=$lang["s_pkt_error"]?>';

				var _classname = _network.getElementsByTagName("class")[0].firstChild.nodeValue;

				var _ligne_title = document.createElement('tr');
				
				var _case_interfaceName = document.createElement('td');
				var _text_interfaceName = document.createTextNode(_interfaceName_label + ': ' + _interfaceName);
				_case_interfaceName.appendChild(_text_interfaceName);
				
				_case_interfaceName.colSpan = 2;

				var _case_Status = document.createElement('td');
				var _text_Status = document.createTextNode(_Status_label + ': ' + _Status);
				_case_Status.appendChild(_text_Status);
				_case_Status.colSpan = 2;
				
				_ligne_title.appendChild(_case_interfaceName);
				_ligne_title.appendChild(_case_Status);
				_ligne_title.className = "ListHeader";

				var _ligne_1 = document.createElement('tr');
				var _case_PhysAddress_label = document.createElement('td');
				var _case_PhysAddress = document.createElement('td');
				var _case_ipAdress_label = document.createElement('td');
				var _case_ipAdress = document.createElement('td');
				var _text_PhysAddress_label = document.createTextNode(_PhysAddress_label);
				var _text_PhysAddress = document.createTextNode(_PhysAddress);
				var _text_ipAdress_label = document.createTextNode(_ipAdress_label);
				var _text_ipAdress = document.createTextNode(_ipAdress);
				_case_PhysAddress_label.appendChild(_text_PhysAddress_label);
				_case_PhysAddress.appendChild(_text_PhysAddress);
				_case_ipAdress_label.appendChild(_text_ipAdress_label);	
				_case_ipAdress.appendChild(_text_ipAdress);	
				_ligne_1.appendChild(_case_PhysAddress_label);
				_ligne_1.appendChild(_case_PhysAddress);
				_ligne_1.appendChild(_case_ipAdress_label);
				_ligne_1.appendChild(_case_ipAdress);
				_ligne_1.className = _classname;

				var _ligne_2 = document.createElement('tr');
				var _case_Type_label = document.createElement('td');
				var _case_Type = document.createElement('td');
				var _case_Speed_label = document.createElement('td');
				var _case_Speed = document.createElement('td');
				var _text_Type_label = document.createTextNode(_Type_label);	
				var _text_Type = document.createTextNode(_Type);	
				var _text_Speed_label = document.createTextNode(_Speed_label);	
				var _text_Speed = document.createTextNode(_Speed);	

				_case_Type_label.appendChild(_text_Type_label);	
				_case_Type.appendChild(_text_Type);	
				_case_Speed_label.appendChild(_text_Speed_label);	
				_case_Speed.appendChild(_text_Speed);	
				_ligne_2.appendChild(_case_Type_label);
				_ligne_2.appendChild(_case_Type);
				_ligne_2.appendChild(_case_Speed_label);
				_ligne_2.appendChild(_case_Speed);
				_ligne_2.className = _classname;


				var _ligne_3 = document.createElement('tr');
				var _case_Trafic = document.createElement('td');
				var _case_errorPaquet = document.createElement('td');
				var _text_Trafic = document.createTextNode(_Trafic_label + ': ' + _Trafic);	
				var _text_errorPaquet = document.createTextNode(_errorPaquet_label + ': ' + _errorPaquet);	
				_case_Trafic.appendChild(_text_Trafic);
				_case_errorPaquet.appendChild(_text_errorPaquet);

				_case_Trafic.colSpan = 2;
				_case_errorPaquet.colSpan = 2;

				_ligne_3.appendChild(_case_Trafic);
				_ligne_3.appendChild(_case_errorPaquet);
				_ligne_3.className = _classname;

				_tableAjax.appendChild(_ligne_title);
				_tableAjax.appendChild(_ligne_1);
				_tableAjax.appendChild(_ligne_2);
				_tableAjax.appendChild(_ligne_3);
			}

			var _vlans = reponseIDCard.getElementsByTagName("vlan");

			for (var i = 0 ; i < _vlans.length ; i++) {
				var _vlan = _vlans[i];

				var _interfaceName = _vlan.getElementsByTagName("interfaceName")[0].firstChild.nodeValue;
				var _Status = _vlan.getElementsByTagName("Status")[0].firstChild.nodeValue;
				var _PhysAddress = _vlan.getElementsByTagName("PhysAddress")[0].firstChild.nodeValue;
				var _Type = _vlan.getElementsByTagName("Type")[0].firstChild.nodeValue;
				var _Trafic = _vlan.getElementsByTagName("Trafic")[0].firstChild.nodeValue;
				var _ipAdress = _vlan.getElementsByTagName("ipAddress")[0].firstChild.nodeValue;
				var _Speed = _vlan.getElementsByTagName("Speed")[0].firstChild.nodeValue;
				var _errorPaquet = _vlan.getElementsByTagName("errorPaquet")[0].firstChild.nodeValue;

				var _interfaceName_label = '<?=$lang["s_status"]?>';
				var _Status_label = '<?=$lang["s_status"]?>';
				var _PhysAddress_label = '<?=$lang["s_PhysAddress"]?>';
				var _Type_label = '<?=$lang["s_Type"]?>';
				var _Trafic_label = '<?=$lang["s_traffic"]?>';
				var _ipAdress_label = '<?=$lang["s_ipadress"]?>';
				var _Speed_label = '<?=$lang["s_speed"]?>';
				var _errorPaquet_label = '<?=$lang["s_pkt_error"]?>';


				var _classname = _vlan.getElementsByTagName("class")[0].firstChild.nodeValue;

				var _ligne_title = document.createElement('tr');
				
				var _case_interfaceName = document.createElement('td');
				var _text_interfaceName = document.createTextNode(_interfaceName_label + ': ' + _interfaceName);
				_case_interfaceName.appendChild(_text_interfaceName);
				
				_case_interfaceName.colSpan = 2;

				var _case_Status = document.createElement('td');
				var _text_Status = document.createTextNode(_Status_label + ': ' + _Status);
				_case_Status.appendChild(_text_Status);
				_case_Status.colSpan = 2;
				
				_ligne_title.appendChild(_case_interfaceName);
				_ligne_title.appendChild(_case_Status);
				_ligne_title.className = "ListHeader";

				var _ligne_1 = document.createElement('tr');
				var _case_PhysAddress_label = document.createElement('td');
				var _case_PhysAddress = document.createElement('td');
				var _case_ipAdress_label = document.createElement('td');
				var _case_ipAdress = document.createElement('td');
				var _text_PhysAddress_label = document.createTextNode(_PhysAddress_label);
				var _text_PhysAddress = document.createTextNode(_PhysAddress);
				var _text_ipAdress_label = document.createTextNode(_ipAdress_label);
				var _text_ipAdress = document.createTextNode(_ipAdress);
				_case_PhysAddress_label.appendChild(_text_PhysAddress_label);	
				_case_PhysAddress.appendChild(_text_PhysAddress);	
				_case_ipAdress_label.appendChild(_text_ipAdress_label);	
				_case_ipAdress.appendChild(_text_ipAdress);	
				_ligne_1.appendChild(_case_PhysAddress_label);
				_ligne_1.appendChild(_case_PhysAddress);
				_ligne_1.appendChild(_case_ipAdress_label);
				_ligne_1.appendChild(_case_ipAdress);
				_ligne_1.className = _classname;

				var _ligne_2 = document.createElement('tr');
				var _case_Type_label = document.createElement('td');
				var _case_Type = document.createElement('td');
				var _case_Speed_label = document.createElement('td');
				var _case_Speed = document.createElement('td');
				var _text_Type_label = document.createTextNode(_Type_label);	
				var _text_Type = document.createTextNode(_Type);	
				var _text_Speed_label = document.createTextNode(_Speed_label);	
				var _text_Speed = document.createTextNode(_Speed);	

				_case_Type_label.appendChild(_text_Type_label);	
				_case_Type.appendChild(_text_Type);	
				_case_Speed_label.appendChild(_text_Speed_label);	
				_case_Speed.appendChild(_text_Speed);	
				_ligne_2.appendChild(_case_Type_label);
				_ligne_2.appendChild(_case_Type);
				_ligne_2.appendChild(_case_Speed_label);
				_ligne_2.appendChild(_case_Speed);
				_ligne_2.className = _classname;


				var _ligne_3 = document.createElement('tr');
				var _case_Trafic = document.createElement('td');
				var _case_errorPaquet = document.createElement('td');
				var _text_Trafic = document.createTextNode(_Trafic_label + ': ' + _Trafic);	
				var _text_errorPaquet = document.createTextNode(_errorPaquet_label + ': ' + _errorPaquet);	
				_case_Trafic.appendChild(_text_Trafic);
				_case_errorPaquet.appendChild(_text_errorPaquet);

				_case_Trafic.colSpan = 2;
				_case_errorPaquet.colSpan = 2;

				_ligne_3.appendChild(_case_Trafic);
				_ligne_3.appendChild(_case_errorPaquet);
				_ligne_3.className = _classname;

				_tableAjax3.appendChild(_ligne_title);
				_tableAjax3.appendChild(_ligne_1);
				_tableAjax3.appendChild(_ligne_2);
				_tableAjax3.appendChild(_ligne_3);
			}
		MyIsLoading("network is loaded");
		}
	}
	xhrIDCard.open("POST",_adrrsearchIDCard,true);
	xhrIDCard.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrIDCard.send("type=" + _current_id + "&host_id="+_host_id);
}
</SCRIPT>
