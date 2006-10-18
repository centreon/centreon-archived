<?
/**
Oreon is developped with Apache Licence 2.0 :
http://www.apache.org/licenses/LICENSE-2.0.txt
Developped by : Cedrick Facon

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

    var childrenNumbertable = _tableAjax.childNodes.length;
    for (var j = 0; j < childrenNumbertable; j++)
    {
		var elementtable = _tableAjax.childNodes[j];
		if(elementtable && elementtable.className != "ListHeader")
		{
			_tableAjax.removeChild(elementtable);
			j = j -1;
		}
	}
	if(id == 2)
	_tableAjax.innerHTML = '';

	
	if (id == 1){
	}
	else if (id == 2){
			MyLoading('Loading...');
			_f2.status.value = 1;
			get_network(_host_id);
	}
	else if (id == 3){
			MyLoading('Loading...');
			_f3.status.value = 1;
			get_StorageDevice(_host_id);
	}
	else if (id == 4){
			MyLoading('Loading...');
			_f4.status.value = 1;
			get_software(_host_id);
	}	
	else if (id == 5){
			MyLoading('Loading...');
			_f5.status.value = 1;
			get_runningProcessus(_host_id);
	}	
	else if (id == 6){
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
		if(id == 2 || id == 3 || id == 4 || id == 5)
			document.getElementById('mreload'+id).className='c';

		var d = document.getElementById('tab'+id);
		var dreload = document.getElementById('mreload'+id);
		for (var i = 1; document.getElementById('tab'+i); i++) {
			document.getElementById('tab'+i).style.display='none';
			if(i == 2 || i == 3 || i == 4 || i == 5)
				document.getElementById('mreload'+i).style.display='none';
		}
	if (d) {		
	d.style.display='block';
	}
	if (dreload) {
	dreload.style.display='block';
	}

	if (id == 1){
	}
	else if (id == 2){
		_f2=document.getElementById('f2');
		if(_f2.status.value == '0')
		{
			MyLoading('Loading...');
			_f2.status.value = 1;
			get_network(_host_id);
		}
	}	
	else if (id == 3){
		_f3=document.getElementById('f3');
		if(_f3.status.value == '0')
		{
			MyLoading('Loading...');
			_f3.status.value = 1;
			get_StorageDevice(_host_id);
		}
	}
	else if (id == 4){
		_f4=document.getElementById('f4');
		if(_f4.status.value == '0')
		{
			MyLoading('Loading...');
			_f4.status.value = 1;
			get_software(_host_id);
		}
	}	
	else if (id == 5){
		_f5=document.getElementById('f5');
		if(_f5.status.value == '0')
		{
			MyLoading('Loading...');
			_f5.status.value = 1;
			get_runningProcessus(_host_id);
		}
	}	
	else if (id == 6){
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
var _adrrsearchIDCard = "./modules/inventory/IDCard_server/infosServerXML.php" //l'adresse   interroger pour trouver les suggestions
	 
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
function get_runningProcessus(_host_id)
{
	var _tableforajax = document.getElementById('tab5');
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

	getXhrIDCard()
	// On defini ce qu'on va faire quand on aura la reponse
	xhrIDCard.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrIDCard.readyState == 4 && xhrIDCard.status == 200 && xhrIDCard.responseXML)
		{		
			reponseIDCard = xhrIDCard.responseXML.documentElement;

			// ici je recupere les statistiques
			var _runningProcs = reponseIDCard.getElementsByTagName("runningprocessus");


			for (var i = 0 ; i < _runningProcs.length ; i++) {
				var _runningProc = _runningProcs[i];

				var _application = _runningProc.getElementsByTagName("application")[0].firstChild.nodeValue;
				var _mem = _runningProc.getElementsByTagName("mem")[0].firstChild.nodeValue;
				var _path = _runningProc.getElementsByTagName("path")[0].firstChild.nodeValue;



				var _ligne = document.createElement('tr');
	
				var _case_application = document.createElement('td');
				var _case_mem = document.createElement('td');
				var _case_path = document.createElement('td');

				var _text_application = document.createTextNode(_application);
				var _text_mem = document.createTextNode(_mem);
				var _text_path = document.createTextNode(_path);

				_case_application.appendChild(_text_application);
				_case_mem.appendChild(_text_mem);
				_case_path.appendChild(_text_path);

				_ligne.appendChild(_case_application);
				_ligne.appendChild(_case_mem);
				_ligne.appendChild(_case_path);

				var ClassName = "list_one";
				if(i % 2)
					ClassName = "list_two";

				_ligne.className = ClassName;
				_tableAjax.appendChild(_ligne);
		MyIsLoading("running processus is loaded");
			}
		//MyIsLoading("running processus is loaded");
		//setTimeout('MyIsLoading("running processus is loaded")', '800');
		}
	}
	xhrIDCard.open("POST",_adrrsearchIDCard,true);
	xhrIDCard.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrIDCard.send("type=5&host_id="+_host_id);
}


function get_software(_host_id)
{
	var _tableforajax = document.getElementById('tab4');
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

	getXhrIDCard()
	// On defini ce qu'on va faire quand on aura la reponse
	xhrIDCard.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrIDCard.readyState == 4 && xhrIDCard.status == 200 && xhrIDCard.responseXML)
		{		
			reponseIDCard = xhrIDCard.responseXML.documentElement;

			// ici je recupere les statistiques
			var _softwares = reponseIDCard.getElementsByTagName("software");


			for (var i = 0 ; i < _softwares.length ; i++) {
				var _software = _softwares[i];

				var _name = _software.getElementsByTagName("name")[0].firstChild.nodeValue;
				var _ligne = document.createElement('tr');
				var _case_name = document.createElement('td');
				var _text_name = document.createTextNode(_name);
				_case_name.appendChild(_text_name);
				_ligne.appendChild(_case_name);
	
				var ClassName = "list_one";
				if(i % 2)
					ClassName = "list_two";

				_ligne.className = ClassName;
				_tableAjax.appendChild(_ligne);
			}
		MyIsLoading("software is loaded");
//		setTimeout('MyIsLoading("software is loaded")', '800');
		}
	}
	xhrIDCard.open("POST",_adrrsearchIDCard,true);
	xhrIDCard.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrIDCard.send("type=4&host_id="+_host_id);
}


function get_StorageDevice(_host_id)
{
	var _tableforajax = document.getElementById('tab3');
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

	getXhrIDCard()
	// On defini ce qu'on va faire quand on aura la reponse
	xhrIDCard.onreadystatechange = function()
	{	
		// On ne fait quelque chose que si on a tout recu et que le serveur est ok
		if(xhrIDCard.readyState == 4 && xhrIDCard.status == 200 && xhrIDCard.responseXML)
		{		
			reponseIDCard = xhrIDCard.responseXML.documentElement;

			// ici je recupere les statistiques
			var _storageDevices = reponseIDCard.getElementsByTagName("storageDevice");

			for (var i = 0 ; i < _storageDevices.length ; i++) {
				var _storageDevice = _storageDevices[i];

				var _mntPointlabel = _storageDevice.getElementsByTagName("mntPointlabel")[0].firstChild.nodeValue;
				var _Typelabel = _storageDevice.getElementsByTagName("Typelabel")[0].firstChild.nodeValue;
				var _Utilisationlabel =  parseInt(_storageDevice.getElementsByTagName("Utilisationlabel")[0].firstChild.nodeValue);
				var _Freelabel = _storageDevice.getElementsByTagName("Freelabel")[0].firstChild.nodeValue;
				var _Usedlabel = _storageDevice.getElementsByTagName("Usedlabel")[0].firstChild.nodeValue;
				var _Sizelabel = _storageDevice.getElementsByTagName("Sizelabel")[0].firstChild.nodeValue;

				var _ligne = document.createElement('tr');
	
				var _case_mntPointlabel = document.createElement('td');
				var _case_Typelabel = document.createElement('td');
				var _case_Utilisationlabel = document.createElement('td');
				var _case_Freelabel = document.createElement('td');
				var _case_Usedlabel = document.createElement('td');
				var _case_Sizelabel = document.createElement('td');
	
				var _text_mntPointlabel = document.createTextNode(_mntPointlabel);
				var _text_Typelabel = document.createTextNode(_Typelabel);

				var _text_Utilisationlabel = '';

				if(_Utilisationlabel >= 0)
					_text_Utilisationlabel = document.createTextNode('  ' + _Utilisationlabel + '%');
				else
					_text_Utilisationlabel = document.createTextNode('');

				var _text_Freelabel = document.createTextNode(_Freelabel);
				var _text_Usedlabel = document.createTextNode(_Usedlabel);
				var _text_Sizelabel = document.createTextNode(_Sizelabel);
	
				_case_mntPointlabel.appendChild(_text_mntPointlabel);
				_case_Typelabel.appendChild(_text_Typelabel);
				_case_Freelabel.appendChild(_text_Freelabel);
				_case_Usedlabel.appendChild(_text_Usedlabel);
				_case_Sizelabel.appendChild(_text_Sizelabel);
	
	

		var _red = '';
		if(_Utilisationlabel >= 85)
			_red = "red";
	

		var _imgmiddle = document.createElement('img');
		_imgmiddle.src = "./include/options/sysInfos/templates/classic/images/" + _red + "bar_middle.gif";
		_imgmiddle.height='10';

		if(_Utilisationlabel <= 100)
			_imgmiddle.width= _Utilisationlabel;
		else
			_imgmiddle.width= '100';

				_case_Utilisationlabel.appendChild(_imgmiddle);
				_case_Utilisationlabel.appendChild(_text_Utilisationlabel);
		
				_ligne.appendChild(_case_mntPointlabel);
				_ligne.appendChild(_case_Typelabel);
				_ligne.appendChild(_case_Utilisationlabel);
				_ligne.appendChild(_case_Freelabel);
				_ligne.appendChild(_case_Usedlabel);
				_ligne.appendChild(_case_Sizelabel);
	
				var ClassName = "list_one";
				if(i % 2)
					ClassName = "list_two";

				_ligne.className = ClassName;	
	
				_tableAjax.appendChild(_ligne);
			}
		MyIsLoading("storageDevices is loaded");
//		setTimeout('MyIsLoading("storageDevices is loaded")', '800');
		}
	}
	xhrIDCard.open("POST",_adrrsearchIDCard,true);
	xhrIDCard.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrIDCard.send("type=3&host_id="+_host_id);
}

function get_network(_host_id)
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
		MyIsLoading("network is loaded");
//		setTimeout('MyIsLoading("network is loaded")', '800');

		}
	}
	xhrIDCard.open("POST",_adrrsearchIDCard,true);
	xhrIDCard.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
	xhrIDCard.send("type=6&host_id="+_host_id);
}

</SCRIPT>
