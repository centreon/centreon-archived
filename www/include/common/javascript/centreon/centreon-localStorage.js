var check_session_interval_id;

function check_session(tM) {
    if (check_session_interval_id) {
    clearInterval(check_session_interval_id);
    }

    check_session_callback();
    check_session_interval_id = setInterval(check_session_callback, tM);
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
    xhr2.onreadystatechange = function () {
        change_status(xhr2);
    };

    //on appelle le fichier XMLresponse.php
    xhr2.open("GET", "./include/common/userTimezone.php", true);
    xhr2.send(null);
}

function change_status(xhr2) {
    if (xhr2.readyState != 4 && xhr2.readyState != "complete") {
        clearInterval(check_session_interval_id);
        return (0);
    }
    var docXML = xhr2.responseXML;

    xhr2.onreadystatechange = null;
    xhr2 = null;

    var items_state = docXML.getElementsByTagName("state");
    var items_time = docXML.getElementsByTagName("time");
    var timezoneItem = docXML.getElementsByTagName("timezone");
    var realTimezone = timezoneItem.item(0).firstChild.data;

    // storing user's local timezone to the localStorage
    localStorage.setItem('realTimezone', realTimezone);
}
