//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/
function jsonPointer(data,parent){this.d=data;this.dp=parent;};jsonPointer.prototype={text:function(){var afff=function(n){var p=[];for(var i=0;i<n.length;i++)p.push("{"+sfff(n[i])+"}");return p.join(",");};var sfff=function(n){var p=[];for (var a in n)if (typeof(n[a])=="object"){if (a.length)p.push('"'+a+'":['+afff(n[a])+"]");else p.push('"'+a+'":{'+sfff(n[a])+"}");}else p.push('"'+a+'":"'+n[a]+'"');return p.join(",");};return "{"+sfff(this.d)+"}";},
 get:function(name){return this.d[name];},
 exists:function(){return !!this.d },
 content:function(){return this.d.content;},
 each:function(name,f,t){var a=this.d[name];var c=new jsonPointer();if (a)for (var i=0;i<a.length;i++){c.d=a[i];f.apply(t,[c,i]);}},
 get_all:function(){return this.d;},
 sub:function(name){return new jsonPointer(this.d[name],this.d) },
 sub_exists:function(name){return !!this.d[name];},
 each_x:function(name,rule,f,t,i){var a=this.d[name];var c=new jsonPointer(0,this.d);if (a)for (i=i||0;i<a.length;i++)if (a[i][rule]){c.d=a[i];if(f.apply(t,[c,i])==-1) return;}},
 up:function(name){return new jsonPointer(this.dp,this.d);},
 set:function(name,val){this.d[name]=val;},
 clone:function(name){return new jsonPointer(this.d,this.dp);},
 through:function(name,rule,v,f,t){var a=this.d[name];if (a.length)for (var i=0;i<a.length;i++){if (a[i][rule]!=null && a[i][rule]!="" && (!v || a[i][rule]==v )) {var c=new jsonPointer(a[i],this.d);f.apply(t,[c,i]);};var w=this.d;this.d=a[i];if (this.sub_exists(name)) this.through(name,rule,v,f,t);this.d=w;}}};dhtmlXTreeObject.prototype.loadJSArrayFile=function(file,afterCall){if (!this.parsCount)this.callEvent("onXLS",[this,this._ld_id]);this._ld_id=null;this.xmlstate=1;var that=this;this.XMLLoader=new dtmlXMLLoaderObject(function(){eval("var z="+arguments[4].xmlDoc.responseText);that.loadJSArray(z);},this,true,this.no_cashe);if (afterCall)this.XMLLoader.waitCall=afterCall;this.XMLLoader.loadXML(file);};dhtmlXTreeObject.prototype.loadCSV=function(file,afterCall){if (!this.parsCount)this.callEvent("onXLS",[this,this._ld_id]);this._ld_id=null;this.xmlstate=1;var that=this;this.XMLLoader=new dtmlXMLLoaderObject(function(){that.loadCSVString(arguments[4].xmlDoc.responseText);},this,true,this.no_cashe);if (afterCall)this.XMLLoader.waitCall=afterCall;this.XMLLoader.loadXML(file);};dhtmlXTreeObject.prototype.loadJSArray=function(ar,afterCall){var z=[];for (var i=0;i<ar.length;i++){if (!z[ar[i][1]])z[ar[i][1]]=[];z[ar[i][1]].push({id:ar[i][0],text:ar[i][2]});};var top={id: this.rootId};var f=function(top,f){if (z[top.id]){top.item=z[top.id];for (var j=0;j<top.item.length;j++)f(top.item[j],f);}};f(top,f);this.loadJSONObject(top,afterCall);};dhtmlXTreeObject.prototype.loadCSVString=function(csv,afterCall){var z=[];var ar=csv.split("\n");for (var i=0;i<ar.length;i++){var t=ar[i].split(",");if (!z[t[1]])z[t[1]]=[];z[t[1]].push({id:t[0],text:t[2]});};var top={id: this.rootId};var f=function(top,f){if (z[top.id]){top.item=z[top.id];for (var j=0;j<top.item.length;j++)f(top.item[j],f);}};f(top,f);this.loadJSONObject(top,afterCall);};dhtmlXTreeObject.prototype.loadJSONObject=function(json,afterCall){if (!this.parsCount)this.callEvent("onXLS",[this,null]);this.xmlstate=1;var p=new jsonPointer(json);this._parse(p);this._p=p;if (afterCall)afterCall();};dhtmlXTreeObject.prototype.loadJSON=function(file,afterCall){if (!this.parsCount)this.callEvent("onXLS",[this,this._ld_id]);this._ld_id=null;this.xmlstate=1;var that=this;this.XMLLoader=new dtmlXMLLoaderObject(function(){try {eval("var t="+arguments[4].xmlDoc.responseText);}catch(e){dhtmlxError.throwError("LoadXML", "Incorrect JSON", [
 (arguments[4].xmlDoc),
 this
 ]);return;};var p=new jsonPointer(t);that._parse(p);that._p=p;},this,true,this.no_cashe);if (afterCall)this.XMLLoader.waitCall=afterCall;this.XMLLoader.loadXML(file);};dhtmlXTreeObject.prototype.serializeTreeToJSON=function(){var out=['{"id":"'+this.rootId+'", "item":['];var p=[];for (var i=0;i<this.htmlNode.childsCount;i++)p.push(this._serializeItemJSON(this.htmlNode.childNodes[i]));out.push(p.join(","));out.push("]}");return out.join("");};dhtmlXTreeObject.prototype._serializeItemJSON=function(itemNode){var out=[];if (itemNode.unParsed)return (itemNode.unParsed.text());if (this._selected.length)var lid=this._selected[0].id;else lid="";var text=itemNode.span.innerHTML;if (this._xescapeEntities)for (var i=0;i<this._serEnts.length;i++)text=text.replace(this._serEnts[i][2],this._serEnts[i][1]);if (!this._xfullXML)out.push('{"id":"'+itemNode.id+'", '+(this._getOpenState(itemNode)==1?' "open":"1", ':'')+(lid==itemNode.id?' "select":"1",':'')+' "text":"'+text+'"'+( ((this.XMLsource)&&(itemNode.XMLload==0))?', "child":"1" ':''));else
 out.push('{"id":"'+itemNode.id+'", '+(this._getOpenState(itemNode)==1?' "open":"1", ':'')+(lid==itemNode.id?' "select":"1",':'')+' "text":"'+text+'", "im0":"'+itemNode.images[0]+'", "im1":"'+itemNode.images[1]+'", "im2":"'+itemNode.images[2]+'" '+(itemNode.acolor?(', "aCol":"'+itemNode.acolor+'" '):'')+(itemNode.scolor?(', "sCol":"'+itemNode.scolor+'" '):'')+(itemNode.checkstate==1?', "checked":"1" ':(itemNode.checkstate==2?', "checked":"-1"':''))+(itemNode.closeable?', "closeable":"1" ':'')+( ((this.XMLsource)&&(itemNode.XMLload==0))?', "child":"1" ':''));if ((this._xuserData)&&(itemNode._userdatalist))
 {out.push(', "userdata":[');var names=itemNode._userdatalist.split(",");var p=[];for (var i=0;i<names.length;i++)p.push('{"name":"'+names[i]+'" , "content":"'+itemNode.userData["t_"+names[i]]+'" }');out.push(p.join(","));out.push("]");};if (itemNode.childsCount){out.push(', "item":[');var p=[];for (var i=0;i<itemNode.childsCount;i++)p.push(this._serializeItemJSON(itemNode.childNodes[i]));out.push(p.join(","));out.push("]\n");};out.push("}\n")
 return out.join("");};//(c)dhtmlx ltd. www.dhtmlx.com
//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/