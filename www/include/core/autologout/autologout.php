
var check_session_interval_id;

function check_session() {
    if (check_session_interval_id) {
        clearInterval(check_session_interval_id);
    }
    
    check_session_callback();

    check_session_interval_id = setInterval(check_session_callback, <?php echo $tM; ?>);
}

function check_session_callback() {
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
		clearInterval(check_session_interval_id);
		return(0);
	}
	var docXML= xhr2.responseXML;

	xhr2.onreadystatechange = null;
	xhr2 = null;

	var items_state = docXML.getElementsByTagName("state");
	var items_time = docXML.getElementsByTagName("time");
	var timezoneItem = docXML.getElementsByTagName("timezone");
	var realTimezone = timezoneItem.item(0).firstChild.data;
	var state = items_state.item(0).firstChild.data;
	var currentTime = items_time.item(0).firstChild.data;

    // storing user's local timezone to the localStorage
    localStorage.setItem('realTimezone', realTimezone);

	if (state == "ok") {
		if (document.getElementById('date')) {
			dateLocale(realTimezone, currentTime);

		}
	} else if (state == "nok") {
		window.location.replace("./index.php");
	}
}

//add the locale timezone and locale to the displayed hours in the header
function dateLocale(timezone,currentTime){
    var userLocale = localStorage.getItem('locale');
    moment.locale(userLocale);
    var userTime = parseInt(currentTime, 10);
    var userDisplay = moment.unix(userTime).tz(timezone).locale(userLocale);
    jQuery("#date").text(userDisplay.format('LLL'));
}


// use of moment() to format dates for each occurence
function formatDateMoment() {

    // get locale and GMT preferences from localStorage
    var userTimezone = localStorage.getItem('realTimezone');
    var userLocale = localStorage.getItem('locale');
    moment.locale(userLocale);

    //creating a collection of occurences
    jQuery(".isTimestamp").each(function(index, element) {
        var myElement = jQuery(element);

        //forcing a numeric timestamp
        var currentDate = parseInt(myElement.text()) * 1000;

        //checking the choosen format
        if (!isNaN(currentDate)) {
            if (myElement.hasClass("isTime")) {
                myElement.text(moment(currentDate).tz(userTimezone).format('LTS'));
            } else if (myElement.hasClass("isDate")) {
                myElement.text(moment(currentDate).tz(userTimezone).format('LL'));
            } else {
                myElement.text(moment(currentDate).tz(userTimezone).format('LL LTS'));
            }
        }
    });
};