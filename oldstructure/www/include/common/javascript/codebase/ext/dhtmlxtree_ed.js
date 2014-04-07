//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/




dhtmlXTreeObject.prototype.enableItemEditor=function(mode){this._eItEd=convertStringToBoolean(mode);if (!this._eItEdFlag){this._edn_click_IE=true;this._edn_dblclick=true;this._ie_aFunc=this.aFunc;this._ie_dblclickFuncHandler=this.dblclickFuncHandler;this.setOnDblClickHandler(function (a,b) {if (this._edn_dblclick)this._editItem(a,b);return true;});this.setOnClickHandler(function (a,b) {this._stopEditItem(a,b);if ((this.ed_hist_clcik==a)&&(this._edn_click_IE))
 this._editItem(a,b);this.ed_hist_clcik=a;return true;});this._eItEdFlag=true;}};dhtmlXTreeObject.prototype.setOnEditHandler=function(func){this.attachEvent("onEdit",func);};dhtmlXTreeObject.prototype.setEditStartAction=function(click_IE, dblclick){this._edn_click_IE=convertStringToBoolean(click_IE);this._edn_dblclick=convertStringToBoolean(dblclick);};dhtmlXTreeObject.prototype._stopEdit=function(a){if (this._editCell){this.dADTempOff=this.dADTempOffEd;if (this._editCell.id!=a){var editText=true;editText=this.callEvent("onEdit",[2,this._editCell.id,this,this._editCell.span.childNodes[0].value]);if (editText===true)editText=this._editCell.span.childNodes[0].value;else if (editText===false)editText=this._editCell._oldValue;var changed = (editText!=this._editCell._oldValue);this._editCell.span.innerHTML=editText;this._editCell.label=this._editCell.span.innerHTML;var cSS=this._editCell.i_sel?"selectedTreeRow":"standartTreeRow";this._editCell.span.className=cSS;this._editCell.span.parentNode.className="standartTreeRow";this._editCell.span.style.paddingRight=this._editCell.span.style.paddingLeft='5px';this._editCell.span.onclick=this._editCell.span.ondblclick=function(){};var id=this._editCell.id;if (this.childCalc)this._fixChildCountLabel(this._editCell);this._editCell=null;this.callEvent("onEdit",[3,id,this,changed]);if (this._enblkbrd){this.parentObject.lastChild.focus();this.parentObject.lastChild.focus();}}}};dhtmlXTreeObject.prototype._stopEditItem=function(id,tree){this._stopEdit(id);};dhtmlXTreeObject.prototype.stopEdit=function(){if (this._editCell)this._stopEdit(this._editCell.id+"_non");};dhtmlXTreeObject.prototype.editItem=function(id){this._editItem(id,this);};dhtmlXTreeObject.prototype._editItem=function(id,tree){if (this._eItEd){this._stopEdit();var temp=this._globalIdStorageFind(id);if (!temp)return;editText=this.callEvent("onEdit",[0,id,this,temp.span.innerHTML]);if (editText===true)editText=temp.label;else if (editText===false)return;this.dADTempOffEd=this.dADTempOff;this.dADTempOff=false;this._editCell=temp;temp._oldValue=editText;temp.span.innerHTML="<input type='text' class='intreeeditRow' />";temp.span.style.paddingRight=temp.span.style.paddingLeft='0px';temp.span.onclick = temp.span.ondblclick= function(e){(e||event).cancelBubble = true;};temp.span.childNodes[0].value=editText;temp.span.childNodes[0].onselectstart=function(e){(e||event).cancelBubble=true;return true;};temp.span.childNodes[0].onmousedown=function(e){(e||event).cancelBubble=true;return true;};temp.span.childNodes[0].focus();temp.span.childNodes[0].focus();temp.span.onclick=function (e){(e||event).cancelBubble=true;return false;};temp.span.className="";temp.span.parentNode.className="";var self=this;temp.span.childNodes[0].onkeydown=function(e){if (!e)e=window.event;if (e.keyCode==13){e.cancelBubble=true;self._stopEdit(window.undefined);}else if (e.keyCode==27){self._editCell.span.childNodes[0].value=self._editCell._oldValue;self._stopEdit(window.undefined);};(e||event).cancelBubble=true;};this.callEvent("onEdit",[1,id,this]);}};


//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/