/**
 * Filter services by host
 * 
 * @param HTMLObject elem
 * @param string serviceMultiSelectName
 */
function hostFilterSelect(elem, serviceMultiSelectName)
{
    var arg = 'host_id='+elem.value;

    if (window.XMLHttpRequest) {
        var xhr = new XMLHttpRequest();
    } else if(window.ActiveXObject){
        try {
            var xhr = new ActiveXObject("Msxml2.XMLHTTP");
        } catch (e) {
            var xhr = new ActiveXObject("Microsoft.XMLHTTP");
        }
    } else {
        var xhr = false;
    }

    xhr.open("POST","./include/configuration/configObject/servicegroup/getServiceXml.php", true);
    xhr.setRequestHeader('Content-Type','application/x-www-form-urlencoded');
    xhr.send(arg);

    xhr.onreadystatechange = function()
    {
        if (xhr && xhr.readyState == 4 && xhr.status == 200 && xhr.responseXML){
            var response = xhr.responseXML.documentElement;
            var _services = response.getElementsByTagName("services");
            var _selbox;

            if (document.getElementById(serviceMultiSelectName+"-f")) {
                _selbox = document.getElementById(serviceMultiSelectName+"-f");
                _selected = document.getElementById(serviceMultiSelectName+"-t");
            } else if (document.getElementById("__"+serviceMultiSelectName)) {
                _selbox = document.getElementById("__"+serviceMultiSelectName);
                _selected = document.getElementById("_"+serviceMultiSelectName);
            }

            while ( _selbox.options.length > 0 ){
                _selbox.options[0] = null;
            }

            if (_services.length === 0) {
                _selbox.setAttribute('disabled', 'disabled');
            } else {
                _selbox.removeAttribute('disabled');
            }

            for (var i = 0 ; i < _services.length ; i++) {
                var _svc 		 = _services[i];
                var _id 		 = _svc.getElementsByTagName("id")[0].firstChild.nodeValue;
                var _description = _svc.getElementsByTagName("description")[0].firstChild.nodeValue;
                var validFlag = true;

                for (var j = 0; j < _selected.length; j++) {
                    if (_id === _selected.options[j].value) {
                        validFlag = false;
                    }
                }

                if (validFlag === true) {
                    new_elem = new Option(_description,_id);
                    _selbox.options[_selbox.length] = new_elem;
                }
            }
        }
    }
}