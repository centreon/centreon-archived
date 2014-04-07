//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/
var _all_used_trees=new Array();dhtmlXTreeObject.prototype._createSelfA2=dhtmlXTreeObject.prototype._createSelf;dhtmlXTreeObject.prototype._createSelf=function(){_all_used_trees[_all_used_trees.length]=this;return this._createSelfA2();};window.onerror=function (a,b,c,d){var d=document.createElement("DIV");d.style.cssText="position:absolute;background-color:white;top:10px;left:10px;z-index:20;width:500px;border: 2px silver outset;";var dh="<div style='width:100%;color:red;font-size:8pt;font-family:Arial;font-weight:bold;'>Javascript Error</div>";dh+="<div style='width:100%;font-size:8pt;font-family:Arial;'>The next error ocured :<br/> <strong>"+a+"</strong> in <strong>"+b+"</strong> at line <strong>"+c+"</strong></div>";dh+="<div style='width:100%;font-size:8pt;font-family:Arial;'>If you think that error can be caused by dhtmlxtree press the 'Generate report' button and send generated report to <a href='email:support@dhtmlx.com'>support@dhtmlx.com</a> </div>";dh+="<input style='font-size:8pt;font-family:Arial;' onclick='dhtmlxtreeReport(this)' type='button' value='Generate report'/><input style='font-size:8pt;font-family:Arial;' type='button' value='Close' onclick='this.parentNode.parentNode.removeChild(this.parentNode);'/>";dh+="<div/>";d.innerHTML=dh;document.body.appendChild(d);return true;};function dhtmlxtreeErrorReport(a,b,c){var str=a+" ["+b+"]";if (a=='LoadXML'){str+="<br/>"+c[0].responseText+"</br>"+c[0].status;};window.onerror(str, "none", "none");};function dhtmlxtreeReport(node){var that=node.parentNode;that.lastChild.innerHTML="<textarea style='width:100%;height:300px;'></textarea>";var rep=that.childNodes[1].innerHTML;for (var a=0;a<_all_used_trees.length;a++){var atree=_all_used_trees[a];rep+="\n\n Tree "+a+"\n";for (b in atree){if (typeof(atree[b])=="function") continue;rep+=b+"="+atree[b]+"\n";};rep+="---------------------\n";if (atree.XMLLoader){try{var z=atree.XMLLoader.getXMLTopNode("tree")
 if (document.all)rep+=z.xml+"\n";else{var xmlSerializer = new XMLSerializer();rep+=xmlSerializer.serializeToString(z)+"\n";}}catch(e){rep+="XML not recognised\n";}};rep+="---------------------\n";for (var i in atree._idpull){var n=atree._idpull[i];if (typeof(n)!='object') continue;rep+="Node: "+n.id;rep+=" Childs: "+n.childsCount;for (var j=0;j<n.childsCount;j++)rep+=" ch"+j+":"+n.childNodes[j].id;rep+="\n";}};that.lastChild.childNodes[0].value=rep;};dhtmlxError.catchError("ALL",dhtmlxtreeErrorReport);


//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/