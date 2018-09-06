
function check_session() {
 	var xhr2 = null;

    if (window.XMLHttpRequest) {
        xhr2 = new XMLHttpRequest();
    } else if (window.ActiveXObject) {
        xhr2 = new ActiveXObject("Microsoft.XMLHTTP");
    }
    
    if (xhr2 == null) {
    	alert("Le web browser ne supporte pas l'AJAX.");
    }
    xhr2.onreadystatechange = function() { change_status(xhr2); };

    //on appelle le fichier XMLresponse.php
    xhr2.open("GET", "./include/core/autologout/autologoutXMLresponse.php", true);
    xhr2.send(null);
}

function change_status(xhr2) {
	if (xhr2.readyState != 4 && xhr2.readyState != "complete") {
		return(0);
	}
	var docXML= xhr2.responseXML;
	var items_state = docXML.getElementsByTagName("state");
	var items_time = docXML.getElementsByTagName("time");
	var state = items_state.item(0).firstChild.data;
	var currentTime = items_time.item(0).firstChild.data;

	if (state == "ok") {
		if (document.getElementById('date')) {
			document.getElementById('date').innerHTML = currentTime;
		}
	} else if (state == "nok") {
		window.location.replace("./index.php");
	}
	setTimeout("check_session()", <?php echo $tM; ?>);
}