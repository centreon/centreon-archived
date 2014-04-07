/**
 * Resolves host address
 * 
 * callback(err, ipAddress){
 *      // Code...
 * }
 */
function resolveHostNameToAddress(hostName, callback) {
    if(window.XMLHttpRequest) {  // Latest browsers
        var xhr = new XMLHttpRequest();
    } else if(window.ActiveXObject) {  // Internet Explorer
        try {
            var xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            var xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    } else { // XMLHttpRequest not supported
        alert("Ajax requests are disabled");
        return;
    }
    xhr.onreadystatechange = function() {
        if (xhr.readyState == 4) {
            if(xhr.status == 200){
                callback(false, xhr.responseText);
            } else {
                callback(true, undefined);
            }
        }
    }
    xhr.open("GET", "./include/configuration/configObject/host/resolveHostName.php?hostName=" + encodeURIComponent(hostName) + "&sid=" + encodeURIComponent(sid));
    xhr.send(null);
}