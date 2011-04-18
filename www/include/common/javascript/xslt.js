/*
 * xslt.js
 *
 * Copyright (c) 2005-2007 Johann Burkard (<mailto:jb@eaio.com>)
 * <http://eaio.com>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the "Software"),
 * to deal in the Software without restriction, including without limitation
 * the rights to use, copy, modify, merge, publish, distribute, sublicense,
 * and/or sell copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
 * NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

var pickRecentProgID = function (idList){
	// found progID flag
    var bFound = false;
    for(var i=0; i < idList.length && !bFound; i++){
        try{
            var oDoc = new ActiveXObject(idList[i]);
            o2Store = idList[i];
            bFound = true;
        }catch (objException){
            // trap; try next progID
        };
    };
    if (!bFound)
		throw ("Aucun ActiveXObject n'est valide sur votre ordinateur, pensez � mettre � jour votre navigateur");
    idList = null;
    return o2Store;
}

// Retourne un nouvel objet XmlHttpRequest
var GetXmlHttpRequest_AXO = null;

var GetXmlHttpRequest=function () {
	if (window.XMLHttpRequest) {
		return new XMLHttpRequest()
	} else if (window.ActiveXObject) {
		if (!GetXmlHttpRequest_AXO) {
			GetXmlHttpRequest_AXO = pickRecentProgID(["Msxml2.XMLHTTP.5.0", "Msxml2.XMLHTTP.4.0", "MSXML2.XMLHTTP.3.0", "MSXML2.XMLHTTP", "Microsoft.XMLHTTP"]);
		}
		return new ActiveXObject(GetXmlHttpRequest_AXO)
	}
	return false;
}



/**
 * Properties of this library.
 *
 * @type String
 * @final
 */
var xslt_js = {
    /**
     * The version of this library
     *
     * @type String
     * @final
     */
    revision: 'xslt.js $Revision: 1.5 $'
};

/**
 * Constructor for client-side XSLT transformations.
 *
 * @author <a href="mailto:jb@eaio.com">Johann Burkard</a>
 * @version $Id$
 * @constructor
 */

function loadXML(url) {

    var xmlDoc;
    /* chargement du fichier XML */
    try {
      // navigateur bas� sur Gecko
      if (document.implementation && document.implementation.createDocument) {
        xmlDoc = document.implementation.createDocument('', '', null);
        xmlDoc.load(url);
      } else if (window.ActiveXObject) {
	    // ActiveX pour Internet Explorer
        try {
          xmlDoc = new ActiveXObject('Msxml2.XMLDOM');
        } catch (e) {
          xmlDoc = new ActiveXObject('Microsoft.XMLDOM');
        }
        xmlDoc.async = false;
        xmlDoc.load(url);
      } else if (window.XMLHttpRequest) {
		// � l'aide de lobjet XMLHTTPRequest
      	xmlDoc = new XMLHttpRequest();
		xmlDoc.overrideMimeType('text/xml');
		xmlDoc.open('GET', url, false);
		xmlDoc.send(null);
		if (this.xmlDoc.readyState == 4) 
        	xmlDoc = xmlDoc.responseXML;
		}
	} catch (e) {
		return e;
	}
	return xmlDoc;
}

function Transformation() {
    var xml;
    var xmlDoc;
    var xslt;
    var xsltDoc;

    var callback = function() {};

    /*
     * Sort of like a fix for Opera who doesn't always get readyStates right.
     */
    var transformed = false;

    /*
     * Returns the URL of the XML document.
     *
     * @return the URL of the XML document
     * @type String
     */
    this.getXml = function() {
        return xml;
    }

    /**
     * Returns the XML document.
     *
     * @return the XML document
     */
    this.getXmlDocument = function() {
        return xmlDoc
    }

    /**
     * Sets the URL of the XML document.
     *
     * @param x the URL of the XML document
     * @return this
     * @type Transformation
     */
    this.setXml = function(x) {
        xml = x;
        return this;
    }

    /**
     * Returns the URL of the XSLT document.
     *
     * @return the URL of the XSLT document
     * @type String
     */
    this.getXslt = function() {
        return xslt;
    }

    /**
     * Returns the XSLT document.
     *
     * @return the XSLT document
     */
    this.getXsltDocument = function() {
        return xsltDoc;
    }

    /**
     * Sets the URL of the XSLT document.
     *
     * @param x the URL of the XML document
     * @return this
     * @type Transformation
     */
    this.setXslt = function(x) {
        xslt = x;
        return this;
    }

    /**
     * Returns the callback function.
     *
     * @return the callback function
     */
    this.getCallback = function() {
        return callback;
    }

    /**
     * Sets the callback function
     *
     * @param c the callback function
     * @return this
     * @type Transformation
     */
    this.setCallback = function(c) {
        callback = c;
        return this;
    }

	/**
	 * Sets the target element to write the transformed content to and asynchronously
	 * starts the transformation process.
	 * <p>
	 * <code>target</code> can be a Node or the ID of an element. 2DO
	 * <p>
	 * This method may only be called after {@link #setXml} and {@link #setXslt} have
	 * been called.
	 * <p>
	 * Note that the target element must exist once this method is called. Calling
	 * this method before <code>onload</code> was fired will most likely
	 * not work.
	 *
	 * @param target the Node or the ID of an element
	 *           if (typeof document.all[xmlID] != u && document.all[xmlID].readyState == c &&
	 *           typeof document.all[xsltID] != u && document.all[xsltID].readyState == c) {
	 *
	 * 
	 */
	
	this.transform = function(target) {
	    var t = this;
	
	    if (!browserSupportsXSLT()) {
	       return;
	    }
		
		if (!document.getElementById("centreonMsg_img")) {
			 _setAlign("centreonMsg", "center");
	         _setTextStyle("centreonMsg", "bold");
	         _setImage("centreonMsg", "./img/misc/ajax-loader.gif");
	         _setText("centreonMsg", " Loading...");
	         _setValign("centreonMsg", "bottom");
		}
		
		var change = function() {
            if (xmlRequest.readyState == 4 && xmlRequest.responseXML && xsltRequest.status == 200 && xsltRequest.readyState == 4 && xsltRequest.statusText == "OK" && xsltRequest.responseText ) {
                    if (transformed) {
                            return;
                    }
                    xsltDoc = xsltRequest.responseXML;
                    xmlDoc = xmlRequest.responseXML;
                    if (window.ActiveXObject) {                    	
                    	document.getElementById(target).innerHTML = xmlDoc.transformNode(xsltDoc);                    	
                    } else {
                    	var resultDoc;
    					var processor = new XSLTProcessor();
    					document.getElementById(target).innerHTML = '';
    					processor.importStylesheet(xsltDoc);
    					resultDoc = processor.transformToFragment(xmlDoc, document);    					
    					document.getElementById(target).appendChild(resultDoc);
                    }
                    callback(t);                	
                    transformed = true;
                    _clear("centreonMsg");
            }
		}
		
		var xmlRequest;
		var xsltRequest;
		
		if (window.ActiveXObject) {
			xmlRequest = new ActiveXObject("Msxml2.XMLHTTP");
			xsltRequest = new ActiveXObject("Msxml2.XMLHTTP");
		} else {
			xmlRequest = GetXmlHttpRequest();
			xsltRequest = GetXmlHttpRequest();
		}
		xmlRequest.open("GET", xml, true);
		xmlRequest.onreadystatechange = change;
		xmlRequest.send(null);		
		if (xsltRequest.readyState != 4) {
            xsltRequest.open("GET", xslt);
            xsltRequest.onreadystatechange = change;
            xsltRequest.send(null);
		}
	}
	
	/**
	 * Generates a random ID.
	 *
	 * @return a random ID
	 */
	function randomID() {
	    var out = 'id' + Math.round(Math.random() * 100000);
	    return out;
	}

}

/**
 * Returns whether the browser supports XSLT.
 *
 * @return the browser supports XSLT
 * @type boolean
 */

function browserSupportsXSLT() {
    var support = false;
    if (window.ActiveXObject) { // IE 6+
        support = true;
    }
    var u = 'undefined';
    if (typeof XMLHttpRequest != u && typeof XSLTProcessor != u) { // Mozilla 0.9.4+, Opera 9+
       var processor = new XSLTProcessor();
       if (typeof processor.transformDocument == 'function') {
           support = typeof XMLSerializer != u;
       } else {
           support = true;
       }
    }
    return support;
}
