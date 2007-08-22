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


/* merethis modification for centreon */
var _numRows = 0;
var _limit = 10;
var _num = 0;

function removeAllLine(table)
{
	rows = table.getElementsByTagName("tr");
	while(rows && rows.length > 1)
		table.deleteRow(-1);
}

 function getVar (nomVariable)
 {
	 var infos = location.href.substring(location.href.indexOf("?")+1, location.href.length)+"&"
	 if (infos.indexOf("#")!=-1)
	 infos = infos.substring(0,infos.indexOf("#"))+"&"
	 var variable=''
	 {
	 nomVariable = nomVariable + "="
	 var taille = nomVariable.length
	 if (infos.indexOf(nomVariable)!=-1)
	 variable = infos.substring(infos.indexOf(nomVariable)+taille,infos.length).substring(0,infos.substring(infos.indexOf(nomVariable)+taille,infos.length).indexOf("&"))
	 }
	 return variable
 }
 
function mk_img(_src, _alt)
{
	var _img = document.createElement("img");
  	_img.src = _src;
  	_img.alt = _alt;
  	_img.title = _alt;
	return _img;
}

function mk_pagination(resXML){
	var flag = 0;
	var infos = resXML.getElementsByTagName("i");
	var _nr = infos[0].getElementsByTagName("numrows")[0].firstChild.nodeValue;
	var _nl = infos[0].getElementsByTagName("limit")[0].firstChild.nodeValue;
	var _nn = infos[0].getElementsByTagName("num")[0].firstChild.nodeValue;

	if(_numRows != _nr){
		_numRows = _nr;
		flag = 1;
	}
	if(_num != _nn){
		_num = _nn;
		flag = 1;
	}
	if(_limit != _nl){
		_limit = _nl;
		flag = 1;
	}

	if(flag == 1){
		var p = getVar('p');
		var o = getVar('o');
		var search = '' + getVar('search');

		var _numplus = _num + 1;

		var _img_previous = mk_img("./img/icones/16x16/arrow_left_blue.gif", "previous");
		var _img_next = mk_img("./img/icones/16x16/arrow_right_blue.gif", "next");

		var _linkaction_right = document.createElement("a");
	  	_linkaction_right.href = './oreon.php?p='+p+'&o='+o+'&search='+search+'&num='+_numplus+'&limit=' + _limit ;
		_linkaction_right.appendChild(_img_next);

		var _pagination1 = document.getElementById('pagination1');
		var _pagination2 = document.getElementById('pagination2');
		_pagination1.innerHTML ='';
//		_pagination1.appendChild(_linkaction_right);


		var page_max =  Math.round( (_numRows / _limit) + 0.5);

		viewDebugInfo('max='+page_max);

		if (_num > page_max && _numRows)
			_num = page_max;


		var istart = 0;
		for(i = 5, istart = _num; istart && i > 0 && istart > 0; i--)
		istart--;

		for(i2 = 0, iend = _num; ( iend <  (_numRows / _limit -1)) && ( i2 < (5 + i)); i2++)
			iend++;


		for (i = istart; i <= iend; i++){
			var _linkaction_num = document.createElement("a");
	  		_linkaction_num.href = './oreon.php?p='+p+'&o='+o+'&search='+search+'&num='+i+'&limit=' + _limit ;
			_linkaction_num.innerHTML = parseInt(i + 1);

			_linkaction_num.className = "otherPageNumber";
			if(i == _num)
			_linkaction_num.className = "currentPageNumber";

			_pagination1.appendChild(_linkaction_num);
//			_numplus = i +1;
		}


		
	}
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
 * @version $Id: xslt.js,v 1.5 2007/06/23 19:48:30 jburkard Exp $
 * @constructor
 */
function Transformation() {

    var xml;
    
    var xmlDoc;
    
    var xslt;
    
    var xsltDoc;

    var callback = function() {};
    
    /**
     * Sort of like a fix for Opera who doesn't always get readyStates right.
     */
    var transformed = false;
        
    /**
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
     */
    this.transform = function(target) {
        if (!browserSupportsXSLT()) {
           return;
        }
        var t = this;

        if (document.recalc) {
            var xmlID = randomID();
            var xsltID = randomID();
            
            var change = function() {
                var c = 'complete'; // ?loading ?interactive
                var u = 'undefined';
/*
                if (typeof document.all[xmlID] != u && document.all[xmlID].readyState == c &&
                typeof document.all[xsltID] != u && document.all[xsltID].readyState == c) {

*/
                if (typeof document.all[xmlID] != u && document.all[xmlID].readyState != null &&
                typeof document.all[xsltID] != u && document.all[xsltID].readyState != null) {
/*
viewDebugInfo('---->' + document.all[xmlID].readyState);
viewDebugInfo('---->' + document.all[xsltID].readyState);
*/
                    window.setTimeout(function() {
                        xmlDoc = document.all[xmlID].XMLDocument;
                        xsltDoc = document.all[xsltID].XMLDocument;
                        callback(t);

                       document.all[target].innerHTML = document.all[xmlID].transformNode(document.all[xsltID].XMLDocument);
//                        document.all[target].innerHTML = document.all[xmlID].transformNode(document.all[xsltID].XMLDocument);




                    }, 50);
                }
            }
            
            var xm = document.createElement('xml');
            xm.onreadystatechange = change;
            xm.id = xmlID;
            xm.src = xml;
            
            var xs = document.createElement('xml');
            xs.onreadystatechange = change;
            xs.id = xsltID;
            xs.src = xslt;
            
            document.body.insertBefore(xm);
            document.body.insertBefore(xs);
        }
        else {
            var xmlRequest = new XMLHttpRequest();
            var xsltRequest = new XMLHttpRequest();
            var change = function() {
                if (xmlRequest.readyState == 4 && xsltRequest.readyState == 4) {
                    if (transformed) {
                        return;
                    }
                    xmlDoc = xmlRequest.responseXML;
                    xsltDoc = xsltRequest.responseXML;
                    var resultDoc;
                    var processor = new XSLTProcessor();
                                       
                    if (typeof processor.transformToFragment == 'function') {
                        // obsolete Mozilla interface
                        resultDoc = document.implementation.createDocument("", "", null);
                        processor.transformDocument(xmlDoc, xsltDoc, resultDoc, null);
                        var out = new XMLSerializer().serializeToString(resultDoc);
                        callback(t);
                        mk_pagination(xmlDoc);
//                        removeAllLine(document.getElementById(target));
						document.getElementById(target).innerHTML = out;
                    }
                    else {
                        processor.importStylesheet(xsltDoc);
                        resultDoc = processor.transformToFragment(xmlDoc, document);
                        callback(t);
                        mk_pagination(xmlDoc);
//                        removeAllLine(document.getElementById(target));
                        document.getElementById(target).innerHTML = '';
                        document.getElementById(target).appendChild(resultDoc);
                    }
                    transformed = true;
                }
            }
            xmlRequest.onreadystatechange = change;
            xmlRequest.open("GET", xml);
            xmlRequest.send(null);
            
            xsltRequest.onreadystatechange = change;
            xsltRequest.open("GET", xslt);
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
    if (document.recalc) { // IE 5+
        support = true;
    }
    var u = 'undefined';
    if (typeof XMLHttpRequest != u && typeof XSLTProcessor != u) { // Mozilla 0.9.4+, Opera 9+
       var processor = new XSLTProcessor();
       if (typeof processor.transformDocument == 'function') {
           support = typeof XMLSerializer != u;
       }
       else {
           support = true;
       }
    }
    return support;
}
