//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/
function dhtmlXTreeFromHTML(obj){if (typeof(obj)!="object")
 obj=document.getElementById(obj);var n=obj;var id=n.id;var cont="";for (var j=0;j<obj.childNodes.length;j++)if (obj.childNodes[j].nodeType=="1"){if (obj.childNodes[j].tagName=="XMP"){var cHead=obj.childNodes[j];for (var m=0;m<cHead.childNodes.length;m++)cont+=cHead.childNodes[m].data;}else if (obj.childNodes[j].tagName.toLowerCase()=="ul")
 cont=dhx_li2trees(obj.childNodes[j],new Array(),0);break;};obj.innerHTML="";var t=new dhtmlXTreeObject(obj,"100%","100%",0);var z_all=new Array();for ( b in t )z_all[b.toLowerCase()]=b;var atr=obj.attributes;for (var a=0;a<atr.length;a++)if ((atr[a].name.indexOf("set")==0)||(atr[a].name.indexOf("enable")==0)){var an=atr[a].name;if (!t[an])an=z_all[atr[a].name];t[an].apply(t,atr[a].value.split(","));};if (typeof(cont)=="object"){t.XMLloadingWarning=1;for (var i=0;i<cont.length;i++){var n=t.insertNewItem(cont[i][0],cont[i][3],cont[i][1]);if (cont[i][2])t._setCheck(n,cont[i][2]);};t.XMLloadingWarning=0;t.lastLoadedXMLId=0;t._redrawFrom(t);}else
 t.loadXMLString("<tree id='0'>"+cont+"</tree>");window[id]=t;var oninit = obj.getAttribute("oninit");if (oninit)eval(oninit);return t;};function dhx_init_trees(){var z=document.getElementsByTagName("div");for (var i=0;i<z.length;i++)if (z[i].className=="dhtmlxTree")dhtmlXTreeFromHTML(z[i])
};function dhx_li2trees(tag,data,ind){for (var i=0;i<tag.childNodes.length;i++){var z=tag.childNodes[i];if ((z.nodeType==1)&&(z.tagName.toLowerCase()=="li")){var c="";var ul=null;var check=z.getAttribute("checked");for (var j=0;j<z.childNodes.length;j++){var zc=z.childNodes[j];if (zc.nodeType==3)c+=zc.data;else if (zc.tagName.toLowerCase()!="ul") c+=dhx_outer_html(zc);else ul=zc;};data[data.length]=[ind,c,check,(z.id||(data.length+1))];if (ul)data=dhx_li2trees(ul,data,(z.id||data.length));}};return data;};function dhx_outer_html(node){if (node.outerHTML)return node.outerHTML;var temp=document.createElement("DIV");temp.appendChild(node.cloneNode(true));temp=temp.innerHTML;return temp;};if (window.addEventListener)window.addEventListener("load",dhx_init_trees,false);else if (window.attachEvent)window.attachEvent("onload",dhx_init_trees);




//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/