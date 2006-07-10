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
// JavaScript Document

// retourne un objet xmlHttpRequest.
// m�thode compatible entre tous les navigateurs (IE/Firefox/Opera)
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
  else { // XMLHttpRequest non support� par le navigateur
    alert("Votre navigateur ne supporte pas les objets XMLHTTPRequest...");
  }
  return xhr;
}

var _documentForm=null; // le formulaire contenant notre champ texte
var _inputField=null; // le champ texte lui-m�me
var _submitButton=null; // le bouton submit de notre formulaire

function initAutoComplete(mform,mcity_name,msub){

_documentForm=document.getElementById(mform);
  _inputField=document.getElementById(mcity_name);
  _submitButton=document.getElementById(msub);

  _inputField.autocomplete="off";
  creeAutocompletionDiv();
  _currentInputFieldValue=_inputField.value;
  _oldInputFieldValue=_currentInputFieldValue;
  cacheResults("",new Array())
  document.onkeydown=onKeyDownHandler;
  _inputField.onkeyup=onKeyUpHandler;
  _inputField.onblur=onBlurHandler;
  window.onresize=onResizeHandler;
  // Premier d�clenchement de la fonction dans 200 millisecondes
  setTimeout("mainLoop()",100)
}

var _oldInputFieldValue=""; // valeur pr�c�dente du champ texte
var _currentInputFieldValue=""; // valeur actuelle du champ texte
var _resultCache=new Object(); // m�canisme de cache des requetes

// tourne en permanence pour suggerer suite � un changement du champ texte
function mainLoop(){
  if(_oldInputFieldValue!=_currentInputFieldValue){
    var valeur=escapeURI(_currentInputFieldValue);
    var country=document.Form.elements['country_id'].options[document.Form.elements['country_id'].selectedIndex].value;
    var suggestions=_resultCache[_currentInputFieldValue];
    if(suggestions){ // la r�ponse �tait encore dans le cache
      metsEnPlace(valeur,suggestions)
    }else{
      callSuggestions(valeur, country) // appel distant
    }
    _inputField.focus()
  }
  _oldInputFieldValue=_currentInputFieldValue;
  setTimeout("mainLoop()",100); // la fonction se red�clenchera dans 200 ms
  return true
}

// echappe les caract�re sp�ciaux
function escapeURI(La){
  if(encodeURIComponent) {
    return encodeURIComponent(La);
  }
  if(escape) {
    return escape(La)
  }
}

var _xmlHttp = null; //l'objet xmlHttpRequest utilis� pour contacter le serveur
var _adresseRecherche = "./include/common/javascript/autocompleteOptions.php" //l'adresse � interroger pour trouver les suggestions

function callSuggestions(valeur, country){
  if(_xmlHttp&&_xmlHttp.readyState!=0){
    _xmlHttp.abort()
  }
  _xmlHttp=getXMLHTTP();
  if(_xmlHttp){
    //appel � l'url distante
    _xmlHttp.open("GET",_adresseRecherche+"?debut="+valeur+"&country="+country,true);
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

// Mecanisme de caching des r�ponses
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

//ins�re une r�gle avec son nom
function insereCSS(nom,regle){
  if (document.styleSheets) {
    var I=document.styleSheets[0];
    if(I.addRule){ // m�thode IE
      I.addRule(nom,regle)
    }else if(I.insertRule){ // m�thode DOM
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

// calcule le d�calage � gauche
function calculateOffsetLeft(r){
  return calculateOffset(r,"offsetLeft")
}

// calcule le d�calage vertical
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
  return _inputField.offsetWidth
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
  _completeDiv.style.visibility="hidden";
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
    nouveauDiv.onmousedown=divOnMouseDown;
    nouveauDiv.onmouseover=divOnMouseOver;
    nouveauDiv.onmouseout=divOnMouseOut;
    setStylePourElement(nouveauDiv,"AutoCompleteDiv");
    var nouveauSpan=document.createElement("SPAN");
    nouveauSpan.innerHTML=liste[f]; // le texte de la suggestion
    nouveauDiv.appendChild(nouveauSpan);
    _completeDiv.appendChild(nouveauDiv)
  }
  PressAction();
  if(_completeDivRows>0) {
    _completeDiv.height=16*_completeDivRows+4;
  } else {
    hideCompleteDiv();
  }

}

var _lastKeyCode=null;

// Handler pour le keydown du document
var onKeyDownHandler=function(event){
  // acc�s evenement compatible IE/Firefox
  if(!event&&window.event) {
    event=window.event;
  }
  // on enregistre la touche ayant d�clench� l'evenement
  if(event) {
    _lastKeyCode=event.keyCode;
  }
}

var _eventKeycode = null;

// Handler pour le keyup de lu champ texte
var onKeyUpHandler=function(event){
  // acc�s evenement compatible IE/Firefox
  if(!event&&window.event) {
    event=window.event;
  }
  _eventKeycode=event.keyCode;
  // Dans les cas touches touche haute(38) ou touche basse (40)
  if(_eventKeycode==40||_eventKeycode==38) {
    // on autorise le blur du champ (traitement dans onblur)
    blurThenGetFocus();
  }
  // taille de la selection
  var N=rangeSize(_inputField);
  // taille du texte avant la selection (selection = suggestion d'autocompl�tion)
  var v=beforeRangeSize(_inputField);
  // contenu du champ texte
  var V=_inputField.value;
  if(_eventKeycode!=0){
    if(N>0&&v!=-1) {
      // on recupere uniquement le champ texte tap� par l'utilisateur
      V=V.substring(0,v);
    }
    // 13 = touche entr�e
    if(_eventKeycode==13||_eventKeycode==3){
      var d=_inputField;
      // on mets en place l'ensemble du champ texte en repoussant la selection
      if(_inputField.createTextRange){
        var t=_inputField.createTextRange();
        t.moveStart("character",_inputField.value.length);
        _inputField.select()
      } else if (d.setSelectionRange){
        _inputField.setSelectionRange(_inputField.value.length,_inputField.value.length)
      }
    } else {
      // si on a pas pu agrandir le champ non selectionn�, on le mets en place violemment.
      if(_inputField.value!=V) {
        _inputField.value=V
      }
    }
  }
  // si la touche n'est ni haut, ni bas, on stocke la valeur utilisateur du champ
  if(_eventKeycode!=40&&_eventKeycode!=38) {
    // le champ courant n est pas change si key Up ou key Down
  	_currentInputFieldValue=V;
  }
  if(handleCursorUpDownEnter(_eventKeycode)&&_eventKeycode!=0) {
    // si on a pr�ss� une touche autre que haut/bas/enter
    PressAction();
  }
}

// Change la suggestion selectionn�.
// cette m�thode traite les touches haut, bas et enter
function handleCursorUpDownEnter(eventCode){
  if(eventCode==40){
    highlightNewValue(_highlightedSuggestionIndex+1);
    return false
  }else if(eventCode==38){
    highlightNewValue(_highlightedSuggestionIndex-1);
    return false
  }else if(eventCode==13||eventCode==3){
    return false
  }
  return true
}

var _completeDivRows = 0;
var _completeDivDivList = null;
var _highlightedSuggestionIndex = -1;
var _highlightedSuggestionDiv = null;

// g�re une touche press�e autre que haut/bas/enter
function PressAction(){
  _highlightedSuggestionIndex=-1;
  var suggestionList=_completeDiv.getElementsByTagName("div");
  var suggestionLongueur=suggestionList.length;
  // on stocke les valeurs pr�c�dentes
  // nombre de possibilit�s de compl�tion
  _completeDivRows=suggestionLongueur;
  // possiblit�s de compl�tion
  _completeDivDivList=suggestionList;
  // si le champ est vide, on cache les propositions de compl�tion
  if(_currentInputFieldValue==""||suggestionLongueur==0){
    hideCompleteDiv()
  }else{
    showCompleteDiv()
  }
  var trouve=false;
  // si on a du texte sur lequel travailler
  if(_currentInputFieldValue.length>0){
    var indice;
    // T vaut true si on a dans la liste de suggestions un mot commencant comme l'entr�e utilisateur
    for(indice=0; indice<suggestionLongueur; indice++){
      if(getSuggestion(suggestionList.item(indice)).toUpperCase().indexOf(_currentInputFieldValue.toUpperCase())==0) {
        trouve=true;
        break
      }
    }
  }
  // on d�s�lectionne toutes les suggestions
  for(var i=0; i<suggestionLongueur; i++) {
    setStylePourElement(suggestionList.item(i),"AutoCompleteDiv");
  }
  // si l'entr�e utilisateur (n) est le d�but d'une suggestion (n-1) on s�lectionne cette suggestion avant de continuer
  if(trouve){
    _highlightedSuggestionIndex=indice;
    _highlightedSuggestionDiv=suggestionList.item(_highlightedSuggestionIndex);
  }else{
    _highlightedSuggestionIndex=-1;
    _highlightedSuggestionDiv=null
  }
  var supprSelection=false;
  switch(_eventKeycode){
    // cursor left, cursor right, page up, page down, others??
    case 8:
    case 33:
    case 34:
    case 35:
    case 35:
    case 36:
    case 37:
    case 39:
    case 45:
    case 46:
      // on supprime la suggestion du texte utilisateur
      supprSelection=true;
      break;
    default:
      break
  }
  // si on a une suggestion (n-1) s�lectionn�e
  if(!supprSelection&&_highlightedSuggestionDiv){
    setStylePourElement(_highlightedSuggestionDiv,"AutoCompleteDivAct");
    var z;
    if(trouve) {
      z=getSuggestion(_highlightedSuggestionDiv).substr(0);
    } else {
      z=_currentInputFieldValue;
    }
    if(z!=_inputField.value){
      if(_inputField.value!=_currentInputFieldValue) {
        return;
      }
      // si on peut cr�er des range dans le document
      if(_inputField.createTextRange||_inputField.setSelectionRange) {
        _inputField.value=z;
      }
      // on s�lectionne la fin de la suggestion
      if(_inputField.createTextRange){
        var t=_inputField.createTextRange();
        t.moveStart("character",_currentInputFieldValue.length);
        t.select()
      }else if(_inputField.setSelectionRange){
        _inputField.setSelectionRange(_currentInputFieldValue.length,_inputField.value.length)
      }
    }
  }else{
    // sinon, plus aucune suggestion de s�lectionn�e
    _highlightedSuggestionIndex=-1;
  }
}

var _cursorUpDownPressed = null;

// permet le blur du champ texte apr�s que la touche haut/bas ai �t� press�.
// le focus est r�cup�r� apr�s traitement (via le timeout).
function blurThenGetFocus(){
  _cursorUpDownPressed=true;
  _inputField.blur();
  setTimeout("_inputField.focus();",10);
  return
}

// taille de la selection dans le champ input
function rangeSize(n){
  var N=-1;
  if(n.createTextRange){
    var fa=document.selection.createRange().duplicate();
    N=fa.text.length
  }else if(n.setSelectionRange){
    N=n.selectionEnd-n.selectionStart
  }
  return N
}

// taille du champ input non selectionne
function beforeRangeSize(n){
  var v=0;
  if(n.createTextRange){
    var fa=document.selection.createRange().duplicate();
    fa.moveEnd("textedit",1);
    v=n.value.length-fa.text.length
  }else if(n.setSelectionRange){
    v=n.selectionStart
  }else{
    v=-1
  }
  return v
}

// Place le curseur � la fin du champ
function cursorAfterValue(n){
  if(n.createTextRange){
    var t=n.createTextRange();
    t.moveStart("character",n.value.length);
    t.select()
  } else if(n.setSelectionRange) {
    n.setSelectionRange(n.value.length,n.value.length)
  }
}


// Retourne la valeur de la possibilite (texte) contenu dans une div de possibilite
function getSuggestion(uneDiv){
  if(!uneDiv) {
    return null;
  }
  return trimCR(uneDiv.getElementsByTagName('span')[0].firstChild.data)
}

// supprime les caract�res retour chariot et line feed d'une chaine de caract�res
function trimCR(chaine){
  for(var f=0,nChaine="",zb="\n\r"; f<chaine.length; f++) {
    if (zb.indexOf(chaine.charAt(f))==-1) {
      nChaine+=chaine.charAt(f);
    }
  }
  return nChaine
}

// Cache completement les choix de completion
function hideCompleteDiv(){
  _completeDiv.style.visibility="hidden"
}

// Rends les choix de completion visibles
function showCompleteDiv(){
  _completeDiv.style.visibility="visible";
  setCompleteDivSize()
}

// Change la suggestion en surbrillance
function highlightNewValue(C){
  if(!_completeDivDivList||_completeDivRows<=0) {
    return;
  }
  showCompleteDiv();
  if(C>=_completeDivRows){
    C=_completeDivRows-1
  }
  if(_highlightedSuggestionIndex!=-1&&C!=_highlightedSuggestionIndex){
    setStylePourElement(_highlightedSuggestionDiv,"AutoCompleteDiv");
    _highlightedSuggestionIndex=-1
  }
  if(C<0){
    _highlightedSuggestionIndex=-1;
    _inputField.focus();
    return
  }
  _highlightedSuggestionIndex=C;
  _highlightedSuggestionDiv=_completeDivDivList.item(C);
  setStylePourElement(_highlightedSuggestionDiv,"AutoCompleteDivAct");
  _inputField.value=getSuggestion(_highlightedSuggestionDiv);
}

// Handler de resize de la fenetre
var onResizeHandler=function(event){
  // recalcule la taille des suggestions
  setCompleteDivSize();
}

// Handler de blur sur le champ texte
var onBlurHandler=function(event){
  if(!_cursorUpDownPressed){
    // si le blur n'est pas caus� par la touche haut/bas
    hideCompleteDiv();
    // Si la derni�re touche pr�ss� est tab, on passe au bouton de validation
    if(_lastKeyCode==9){
      _submitButton.focus();
      _lastKeyCode=-1
    }
  }
  _cursorUpDownPressed=false
};

// declenchee quand on clique sur une div contenant une possibilite
var divOnMouseDown=function(){
  _inputField.value=getSuggestion(this);
  _documentForm.submit()
};

// declenchee quand on passe sur une div de possibilite. La div pr�c�dente est passee en style normal
var divOnMouseOver=function(){
  if(_highlightedSuggestionDiv) {
    setStylePourElement(_highlightedSuggestionDiv,"AutoCompleteDiv");
  }
  setStylePourElement(this,"AutoCompleteDivAct")
};

// declenchee quand la sourie quitte une div de possiblite. La div repasse a l'etat normal
var divOnMouseOut = function(){
  setStylePourElement(this,"AutoCompleteDiv");
};
