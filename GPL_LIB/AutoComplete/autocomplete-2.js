// retourne un objet xmlHttpRequest.
// méthode compatible entre tous les navigateurs (IE/Firefox/Opera)
function getXMLHTTP(){
  var xhr=null;
  if(window.XMLHttpRequest) // Firefox et autres
  xhr = new XMLHttpRequest();
  else if(window.ActiveXObject){ // Internet Explorer
    try {
      xhr = new ActiveXObject("Msxml2.XMLHTTP");
    } catch (e) {
      try {
        xhr = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e1) {
        xhr = null;
      }
    }
  }
  else { // XMLHttpRequest non supporté par le navigateur
    alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
  }
  return xhr;
}

var _documentForm=null; // le formulaire contenant notre champ texte
var _inputField=null; // le champ texte lui-même
var _submitButton=null; // le bouton submit de notre formulaire

function initAutoComplete(form,field,submit){
  _documentForm=form;
  _inputField=field;
  _submitButton=submit;
  _inputField.autocomplete="off";
  creeAutocompletionDiv();
  _currentInputFieldValue=_inputField.value;
  _oldInputFieldValue=_currentInputFieldValue;
  cacheResults("",new Array())
  // Premier déclenchement de la fonction dans 200 millisecondes
  setTimeout("mainLoop()",200)
}

var _oldInputFieldValue=""; // valeur précédente du champ texte
var _currentInputFieldValue=""; // valeur actuelle du champ texte
var _resultCache=new Object(); // mécanisme de cache des requetes

// tourne en permanence pour suggerer suite à un changement du champ texte
function mainLoop(){
  _currentInputFieldValue = _inputField.value;
  if(_oldInputFieldValue!=_currentInputFieldValue){
    var valeur=escapeURI(_currentInputFieldValue);
    var suggestions=_resultCache[_currentInputFieldValue];
    if(suggestions){ // la réponse était encore dans le cache
      metsEnPlace(valeur,suggestions)
    }else{
      callSuggestions(valeur) // appel distant
    }
    _inputField.focus()
  }
  _oldInputFieldValue=_currentInputFieldValue;
  setTimeout("mainLoop()",200); // la fonction se redéclenchera dans 200 ms
  return true
}

// echappe les caractère spéciaux
function escapeURI(La){
  if(encodeURIComponent) {
    return encodeURIComponent(La);
  }
  if(escape) {
    return escape(La)
  }
}

var _xmlHttp = null; //l'objet xmlHttpRequest utilisé pour contacter le serveur
var _adresseRecherche = "options.php" //l'adresse à interroger pour trouver les suggestions

function callSuggestions(valeur){
  if(_xmlHttp&&_xmlHttp.readyState!=0){
    _xmlHttp.abort()
  }
  _xmlHttp=getXMLHTTP();
  if(_xmlHttp){
    //appel à l'url distante
    _xmlHttp.open("GET",_adresseRecherche+"?debut="+valeur,true);
    _xmlHttp.onreadystatechange=function() {
      if(_xmlHttp.readyState==4&&_xmlHttp.responseXML) {
        var liste = traiteXmlSuggestions(_xmlHttp.responseXML)
        cacheResults(valeur,liste)
        metsEnPlace(valeur,liste)
      }
    };
    // envoi de la requete
    _xmlHttp.send(null)
  }
}

// Mecanisme de caching des réponses
function cacheResults(debut,suggestions){
  _resultCache[debut]=suggestions
}

// Transformation XML en tableau
function traiteXmlSuggestions(xmlDoc) {
  var options = xmlDoc.getElementsByTagName('option');
  var optionsListe = new Array();
  for (var i=0; i < options.length; ++i) {
    optionsListe.push(options[i].firstChild.data);
  }
  return optionsListe;
}

//insère une règle avec son nom
function insereCSS(nom,regle){
  if (document.styleSheets) {
    var I=document.styleSheets[0];
    if(I.addRule){ // méthode IE
      I.addRule(nom,regle)
    }else if(I.insertRule){ // méthode DOM
      I.insertRule(nom+" { "+regle+" }",I.cssRules.length)
    }
  }
}

function initStyle(){
  var AutoCompleteDivListeStyle="font-size: 13px; font-family: arial,sans-serif; word-wrap:break-word; ";
  var AutoCompleteDivStyle="display: block; padding-left: 3; padding-right: 3; height: 16px; overflow: hidden; background-color: white;";
  var AutoCompleteDivActStyle="background-color: #3366cc; color: white ! important; ";
  insereCSS(".AutoCompleteDivListeStyle",AutoCompleteDivListeStyle);
  insereCSS(".AutoCompleteDiv",AutoCompleteDivStyle);
  insereCSS(".AutoCompleteDivAct",AutoCompleteDivActStyle);
}

function setStylePourElement(c,name){
  c.className=name;
}

// calcule le décalage à gauche
function calculateOffsetLeft(r){
  return calculateOffset(r,"offsetLeft")
}

// calcule le décalage vertical
function calculateOffsetTop(r){
  return calculateOffset(r,"offsetTop")
}

function calculateOffset(r,attr){
  var kb=0;
  while(r){
    kb+=r[attr];
    r=r.offsetParent
  }
  return kb
}

// calcule la largeur du champ
function calculateWidth(){
  return _inputField.offsetWidth-2*1
}

function setCompleteDivSize(){
  if(_completeDiv){
    _completeDiv.style.left=calculateOffsetLeft(_inputField)+"px";
    _completeDiv.style.top=calculateOffsetTop(_inputField)+_inputField.offsetHeight-1+"px";
    _completeDiv.style.width=calculateWidth()+"px"
  }
}

function creeAutocompletionDiv() {
  initStyle();
  _completeDiv=document.createElement("DIV");
  _completeDiv.id="completeDiv";
  var borderLeftRight=1;
  var borderTopBottom=1;
  _completeDiv.style.borderRight="black "+borderLeftRight+"px solid";
  _completeDiv.style.borderLeft="black "+borderLeftRight+"px solid";
  _completeDiv.style.borderTop="black "+borderTopBottom+"px solid";
  _completeDiv.style.borderBottom="black "+borderTopBottom+"px solid";
  _completeDiv.style.zIndex="1";
  _completeDiv.style.paddingRight="0";
  _completeDiv.style.paddingLeft="0";
  _completeDiv.style.paddingTop="0";
  _completeDiv.style.paddingBottom="0";
  setCompleteDivSize();
  _completeDiv.style.visibility="visible";
  _completeDiv.style.position="absolute";
  _completeDiv.style.backgroundColor="white";
  document.body.appendChild(_completeDiv);
  setStylePourElement(_completeDiv,"AutoCompleteDivListeStyle");
}

function metsEnPlace(valeur, liste){
  while(_completeDiv.childNodes.length>0) {
    _completeDiv.removeChild(_completeDiv.childNodes[0]);
  }
  // mise en place des suggestions
  for(var f=0; f<liste.length; ++f){
    var nouveauDiv=document.createElement("DIV");
    setStylePourElement(nouveauDiv,"AutoCompleteDiv");
    var nouveauSpan=document.createElement("SPAN");
    nouveauSpan.innerHTML=liste[f]; // le texte de la suggestion
    nouveauDiv.appendChild(nouveauSpan);
    _completeDiv.appendChild(nouveauDiv)
  }
  if(_completeDivRows>0) {
    _completeDiv.height=16*_completeDivRows+4;
  } else {
    hideCompleteDiv();
  }
}