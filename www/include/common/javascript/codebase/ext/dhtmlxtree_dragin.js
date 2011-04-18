//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/

dhtmlXTreeObject.prototype.makeDraggable=function(obj,func){if (typeof(obj)!="object")
 obj=document.getElementById(obj);dragger=new dhtmlDragAndDropObject();dropper=new dhx_dragSomethingInTree();dragger.addDraggableItem(obj,dropper);obj.dragLanding=null;obj.ondragstart=dropper._preventNsDrag;obj.onselectstart=new Function("return false;");obj.parentObject=new Object;obj.parentObject.img=obj;obj.parentObject.treeNod=dropper;dropper._customDrop=func;};dhtmlXTreeObject.prototype.makeDragable=dhtmlXTreeObject.prototype.makeDraggable;dhtmlXTreeObject.prototype.makeAllDraggable=function(func){var z=document.getElementsByTagName("div");for (var i=0;i<z.length;i++)if (z[i].getAttribute("dragInDhtmlXTree"))
 this.makeDragable(z[i],func);};function dhx_dragSomethingInTree(){this.lWin=window;this._createDragNode=function(node){var dragSpan=document.createElement('div');dragSpan.style.position="absolute";dragSpan.innerHTML=node.innerHTML;dragSpan.className="dragSpanDiv";return dragSpan;};this._preventNsDrag=function(e){(e||window.event).cancelBubble=true;if ((e)&&(e.preventDefault)) {e.preventDefault();return false;};return false;};this._nonTrivialNode=function(tree,item,bitem,source){if (this._customDrop)return this._customDrop(tree,source.img.id,item.id,bitem?bitem.id:null);var image=(source.img.getAttribute("image")||"");var id=source.img.id||"new";var text=(source.img.getAttribute("text")||(_isIE?source.img.innerText:source.img.textContent));tree[bitem?"insertNewNext":"insertNewItem"](bitem?bitem.id:item.id,id,text,"",image,image,image);}};//(c)dhtmlx ltd. www.dhtmlx.com
//v.2.6 build 100722

/*
Copyright DHTMLX LTD. http://www.dhtmlx.com
You allowed to use this component or parts of it under GPL terms
To use it on other terms or get Professional edition of the component please contact us at sales@dhtmlx.com
*/