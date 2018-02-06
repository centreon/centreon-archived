/*
 * Copyright 2005-2015 Centreon
 * Centreon is developped by : Julien Mathis and Romain Le Merlus under
 * GPL Licence 2.0.
 * 
 * This program is free software; you can redistribute it and/or modify it under 
 * the terms of the GNU General Public License as published by the Free Software 
 * Foundation ; either version 2 of the License.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A 
 * PARTICULAR PURPOSE. See the GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License along with 
 * this program; if not, see <http://www.gnu.org/licenses>.
 * 
 * Linking this program statically or dynamically with other modules is making a 
 * combined work based on this program. Thus, the terms and conditions of the GNU 
 * General Public License cover the whole combination.
 * 
 * As a special exception, the copyright holders of this program give Centreon 
 * permission to link this program with independent modules to produce an executable, 
 * regardless of the license terms of these independent modules, and to copy and 
 * distribute the resulting executable under terms of Centreon choice, provided that 
 * Centreon also meet, for each linked independent module, the terms  and conditions 
 * of the license of that module. An independent module is a module which is not 
 * derived from this program. If you modify this program, you may extend this 
 * exception to your version of the program, but you are not obliged to do so. If you
 * do not wish to do so, delete this exception statement from your version.
 * 
 * For more information : contact@centreon.com
 * 
 */

/*
ModalBox - The pop-up window thingie with AJAX, based on prototype and script.aculo.us.

Copyright Andrey Okonetchnikov (andrej.okonetschnikow@gmail.com), 2006-2007
All rights reserved.
 
VERSION 1.6.0
Last Modified: 12/13/2007
*/

if (!window.Modalbox)
	var Modalbox = new Object();

Modalbox.Methods = {
	overrideAlert: false, // Override standard browser alert message with ModalBox
	focusableElements: new Array,
	currFocused: 0,
	initialized: false,
	active: true,
	options: {
		title: "ModalBox Window", // Title of the ModalBox window
		overlayClose: true, // Close modal box by clicking on overlay
		width: 500, // Default width in px
		height: 90, // Default height in px
		overlayOpacity: .65, // Default overlay opacity
		overlayDuration: .25, // Default overlay fade in/out duration in seconds
		slideDownDuration: .5, // Default Modalbox appear slide down effect in seconds
		slideUpDuration: .5, // Default Modalbox hiding slide up effect in seconds
		resizeDuration: .25, // Default resize duration seconds
		inactiveFade: true, // Fades MB window on inactive state
		transitions: true, // Toggles transition effects. Transitions are enabled by default
		loadingString: "Please wait. Loading...", // Default loading string message
		closeString: "Close window", // Default title attribute for close window link
		closeValue: "&times;", // Default string for close link in the header
		params: {},
		method: 'get', // Default Ajax request method
		autoFocusing: true, // Toggles auto-focusing for form elements. Disable for long text pages.
		aspnet: false // Should be use then using with ASP.NET costrols. Then true Modalbox window will be injected into the first form element.
	},
	_options: new Object,
	
	setOptions: function(options) {
		Object.extend(this.options, options || {});
	},
	
	_init: function(options) {
		// Setting up original options with default options
		Object.extend(this._options, this.options);
		this.setOptions(options);
		
		//Create the overlay
		this.MBoverlay = new Element("div", { id: "MB_overlay", opacity: "0" });
		
		//Create DOm for the window
		this.MBwindow = new Element("div", {id: "MB_window", style: "display: none"}).update(
			this.MBframe = new Element("div", {id: "MB_frame"}).update(
				this.MBheader = new Element("div", {id: "MB_header"}).update(
					this.MBcaption = new Element("div", {id: "MB_caption"})
				)
			)
		);
		this.MBclose = new Element("a", {id: "MB_close", title: this.options.closeString, href: "#"}).update("<span>" + this.options.closeValue + "</span>");
		this.MBheader.insert({'bottom':this.MBclose});
		
		this.MBcontent = new Element("div", {id: "MB_content"}).update(
			this.MBloading = new Element("div", {id: "MB_loading"}).update(this.options.loadingString)
		);
		this.MBframe.insert({'bottom':this.MBcontent});
		
		// Inserting into DOM. If parameter set and form element have been found will inject into it. Otherwise will inject into body as topmost element.
		// Be sure to set padding and marging to null via CSS for both body and (in case of asp.net) form elements. 
		var injectToEl = this.options.aspnet ? $(document.body).down('form') : $(document.body);
		injectToEl.insert({'top':this.MBwindow});
		injectToEl.insert({'top':this.MBoverlay});
		
		// Initial scrolling position of the window. To be used for remove scrolling effect during ModalBox appearing
		this.initScrollX = window.pageXOffset || document.body.scrollLeft || document.documentElement.scrollLeft;
		this.initScrollY = window.pageYOffset || document.body.scrollTop || document.documentElement.scrollTop;
		
		//Adding event observers
		this.hideObserver = this._hide.bindAsEventListener(this);
		this.kbdObserver = this._kbdHandler.bindAsEventListener(this);
		this._initObservers();

		this.initialized = true; // Mark as initialized
	},
	
	show: function(content, options) {
		if(!this.initialized) this._init(options); // Check for is already initialized
		
		this.content = content;
		this.setOptions(options);
		
		if(this.options.title) // Updating title of the MB
			$(this.MBcaption).update(this.options.title);
		else { // If title isn't given, the header will not displayed
			$(this.MBheader).hide();
			$(this.MBcaption).hide();
		}
		
		if(this.MBwindow.style.display == "none") { // First modal box appearing
			this._appear();
			this.event("onShow"); // Passing onShow callback
		}
		else { // If MB already on the screen, update it
			this._update();
			this.event("onUpdate"); // Passing onUpdate callback
		} 
	},
	
	hide: function(options) { // External hide method to use from external HTML and JS
		if(this.initialized) {
			// Reading for options/callbacks except if event given as a pararmeter
			if(options && typeof options.element != 'function') Object.extend(this.options, options); 
			// Passing beforeHide callback
			this.event("beforeHide");
			if(this.options.transitions)
				Effect.SlideUp(this.MBwindow, { duration: this.options.slideUpDuration, transition: Effect.Transitions.sinoidal, afterFinish: this._deinit.bind(this) } );
			else {
				$(this.MBwindow).hide();
				this._deinit();
			}
		} else throw("Modalbox is not initialized.");
	},
	
	_hide: function(event) { // Internal hide method to use with overlay and close link
		event.stop(); // Stop event propaganation for link elements
		/* Then clicked on overlay we'll check the option and in case of overlayClose == false we'll break hiding execution [Fix for #139] */
		if(event.element().id == 'MB_overlay' && !this.options.overlayClose) return false;
		this.hide();
	},
	
	alert: function(message){
		var html = '<div class="MB_alert"><p>' + message + '</p><input type="button" onclick="Modalbox.hide()" value="OK" /></div>';
		Modalbox.show(html, {title: 'Alert: ' + document.title, width: 300});
	},
		
	_appear: function() { // First appearing of MB
		if(Prototype.Browser.IE && !navigator.appVersion.match(/\b7.0\b/)) { // Preparing IE 6 for showing modalbox
			window.scrollTo(0,0);
			this._prepareIE("100%", "hidden"); 
		}
		this._setWidth();
		this._setPosition();
		if(this.options.transitions) {
			$(this.MBoverlay).setStyle({opacity: 0});
			new Effect.Fade(this.MBoverlay, {
					from: 0, 
					to: this.options.overlayOpacity, 
					duration: this.options.overlayDuration, 
					afterFinish: function() {
						new Effect.SlideDown(this.MBwindow, {
							duration: this.options.slideDownDuration, 
							transition: Effect.Transitions.sinoidal, 
							afterFinish: function(){ 
								this._setPosition(); 
								this.loadContent();
							}.bind(this)
						});
					}.bind(this)
			});
		} else {
			$(this.MBoverlay).setStyle({opacity: this.options.overlayOpacity});
			$(this.MBwindow).show();
			this._setPosition(); 
			this.loadContent();
		}
		this._setWidthAndPosition = this._setWidthAndPosition.bindAsEventListener(this);
		Event.observe(window, "resize", this._setWidthAndPosition);
	},
	
	resize: function(byWidth, byHeight, options) { // Change size of MB without loading content
		var wHeight = $(this.MBwindow).getHeight();
		var wWidth = $(this.MBwindow).getWidth();
		var hHeight = $(this.MBheader).getHeight();
		var cHeight = $(this.MBcontent).getHeight();
		var newHeight = ((wHeight - hHeight + byHeight) < cHeight) ? (cHeight + hHeight - wHeight) : byHeight;
		if(options) this.setOptions(options); // Passing callbacks
		if(this.options.transitions) {
			new Effect.ScaleBy(this.MBwindow, byWidth, newHeight, {
					duration: this.options.resizeDuration, 
				  	afterFinish: function() { 
						this.event("_afterResize"); // Passing internal callback
						this.event("afterResize"); // Passing callback
					}.bind(this)
				});
		} else {
			this.MBwindow.setStyle({width: wWidth + byWidth + "px", height: wHeight + newHeight + "px"});
			setTimeout(function() {
				this.event("_afterResize"); // Passing internal callback
				this.event("afterResize"); // Passing callback
			}.bind(this), 1);
			
		}
		
	},
	
	resizeToContent: function(options){
		
		// Resizes the modalbox window to the actual content height.
		// This might be useful to resize modalbox after some content modifications which were changed ccontent height.
		
		var byHeight = this.options.height - this.MBwindow.offsetHeight;
		if(byHeight != 0) {
			if(options) this.setOptions(options); // Passing callbacks
			Modalbox.resize(0, byHeight);
		}
	},
	
	resizeToInclude: function(element, options){
		
		// Resizes the modalbox window to the camulative height of element. Calculations are using CSS properties for margins and border.
		// This method might be useful to resize modalbox before including or updating content.
		
		var el = $(element);
		var elHeight = el.getHeight() + parseInt(el.getStyle('margin-top')) + parseInt(el.getStyle('margin-bottom')) + parseInt(el.getStyle('border-top-width')) + parseInt(el.getStyle('border-bottom-width'));
		if(elHeight > 0) {
			if(options) this.setOptions(options); // Passing callbacks
			Modalbox.resize(0, elHeight);
		}
	},
	
	_update: function() { // Updating MB in case of wizards
		$(this.MBcontent).update("");
		this.MBcontent.appendChild(this.MBloading);
		$(this.MBloading).update(this.options.loadingString);
		this.currentDims = [this.MBwindow.offsetWidth, this.MBwindow.offsetHeight];
		Modalbox.resize((this.options.width - this.currentDims[0]), (this.options.height - this.currentDims[1]), {_afterResize: this._loadAfterResize.bind(this) });
	},
	
	loadContent: function () {
		if(this.event("beforeLoad") != false) { // If callback passed false, skip loading of the content
			if(typeof this.content == 'string') {
				var htmlRegExp = new RegExp(/<\/?[^>]+>/gi);
				if(htmlRegExp.test(this.content)) { // Plain HTML given as a parameter
					this._insertContent(this.content.stripScripts());
					this._putContent(function(){
						this.content.extractScripts().map(function(script) { 
							return eval(script.replace("<!--", "").replace("// -->", ""));
						}.bind(window));
					}.bind(this));
				} else // URL given as a parameter. We'll request it via Ajax
					new Ajax.Request( this.content, { method: this.options.method.toLowerCase(), parameters: this.options.params, 
						onSuccess: function(transport) {
							var response = new String(transport.responseText);
							this._insertContent(transport.responseText.stripScripts());
							this._putContent(function(){
								response.extractScripts().map(function(script) { 
									return eval(script.replace("<!--", "").replace("// -->", ""));
								}.bind(window));
							});
						}.bind(this),
						onException: function(instance, exception){
							Modalbox.hide();
							throw('Modalbox Loading Error: ' + exception);
						}
					});
					
			} else if (typeof this.content == 'object') {// HTML Object is given
				this._insertContent(this.content);
				this._putContent();
			} else {
				Modalbox.hide();
				throw('Modalbox Parameters Error: Please specify correct URL or HTML element (plain HTML or object)');
			}
		}
	},
	
	_insertContent: function(content){
		$(this.MBcontent).hide().update("");
		if(typeof content == 'string') {
			setTimeout(function() { // Hack to disable content flickering in Firefox
				this.MBcontent.update(content);
			}.bind(this), 1);
		} else if (typeof content == 'object') { // HTML Object is given
			var _htmlObj = content.cloneNode(true); // If node already a part of DOM we'll clone it
			// If clonable element has ID attribute defined, modifying it to prevent duplicates
			if(content.id) content.id = "MB_" + content.id;
			/* Add prefix for IDs on all elements inside the DOM node */
			$(content).select('*[id]').each(function(el){ el.id = "MB_" + el.id; });
			this.MBcontent.appendChild(_htmlObj);
			this.MBcontent.down().show(); // Toggle visibility for hidden nodes
			if(Prototype.Browser.IE) // Toggling back visibility for hidden selects in IE
				$$("#MB_content select").invoke('setStyle', {'visibility': ''});
		}
	},
	
	_putContent: function(callback){
		// Prepare and resize modal box for content
		if(this.options.height == this._options.height) {
			setTimeout(function() { // MSIE sometimes doesn't display content correctly
				Modalbox.resize(0, $(this.MBcontent).getHeight() - $(this.MBwindow).getHeight() + $(this.MBheader).getHeight(), {
					afterResize: function(){
						this.MBcontent.show().makePositioned();
						this.focusableElements = this._findFocusableElements();
						this._setFocus(); // Setting focus on first 'focusable' element in content (input, select, textarea, link or button)
						setTimeout(function(){ // MSIE fix
							if(callback != undefined)
								callback(); // Executing internal JS from loaded content
							this.event("afterLoad"); // Passing callback
						}.bind(this),1);
					}.bind(this)
				});
			}.bind(this), 1);
		} else { // Height is defined. Creating a scrollable window
			this._setWidth();
			this.MBcontent.setStyle({overflow: 'auto', height: $(this.MBwindow).getHeight() - $(this.MBheader).getHeight() - 13 + 'px'});
			this.MBcontent.show();
			this.focusableElements = this._findFocusableElements();
			this._setFocus(); // Setting focus on first 'focusable' element in content (input, select, textarea, link or button)
			setTimeout(function(){ // MSIE fix
				if(callback != undefined)
					callback(); // Executing internal JS from loaded content
				this.event("afterLoad"); // Passing callback
			}.bind(this),1);
		}
	},
	
	activate: function(options){
		this.setOptions(options);
		this.active = true;
		$(this.MBclose).observe("click", this.hideObserver);
		if(this.options.overlayClose)
			$(this.MBoverlay).observe("click", this.hideObserver);
		$(this.MBclose).show();
		if(this.options.transitions && this.options.inactiveFade)
			new Effect.Appear(this.MBwindow, {duration: this.options.slideUpDuration});
	},
	
	deactivate: function(options) {
		this.setOptions(options);
		this.active = false;
		$(this.MBclose).stopObserving("click", this.hideObserver);
		if(this.options.overlayClose)
			$(this.MBoverlay).stopObserving("click", this.hideObserver);
		$(this.MBclose).hide();
		if(this.options.transitions && this.options.inactiveFade)
			new Effect.Fade(this.MBwindow, {duration: this.options.slideUpDuration, to: .75});
	},
	
	_initObservers: function(){
		$(this.MBclose).observe("click", this.hideObserver);
		if(this.options.overlayClose)
			$(this.MBoverlay).observe("click", this.hideObserver);
		if(Prototype.Browser.IE)
			Event.observe(document, "keydown", this.kbdObserver);
		else
			Event.observe(document, "keypress", this.kbdObserver);
	},
	
	_removeObservers: function(){
		$(this.MBclose).stopObserving("click", this.hideObserver);
		if(this.options.overlayClose)
			$(this.MBoverlay).stopObserving("click", this.hideObserver);
		if(Prototype.Browser.IE)
			Event.stopObserving(document, "keydown", this.kbdObserver);
		else
			Event.stopObserving(document, "keypress", this.kbdObserver);
	},
	
	_loadAfterResize: function() {
		this._setWidth();
		this._setPosition();
		this.loadContent();
	},
	
	_setFocus: function() { 
		/* Setting focus to the first 'focusable' element which is one with tabindex = 1 or the first in the form loaded. */
		if(this.focusableElements.length > 0 && this.options.autoFocusing == true) {
			var firstEl = this.focusableElements.find(function (el){
				return el.tabIndex == 1;
			}) || this.focusableElements.first();
			this.currFocused = this.focusableElements.toArray().indexOf(firstEl);
			firstEl.focus(); // Focus on first focusable element except close button
		} else if($(this.MBclose).visible())
			$(this.MBclose).focus(); // If no focusable elements exist focus on close button
	},
	
	_findFocusableElements: function(){ // Collect form elements or links from MB content
		this.MBcontent.select('input:not([type~=hidden]), select, textarea, button, a[href]').invoke('addClassName', 'MB_focusable');
		return this.MBcontent.select('.MB_focusable');
	},
	
	_kbdHandler: function(event) {
		var node = event.element();
		switch(event.keyCode) {
			case Event.KEY_TAB:
				event.stop();
				
				/* Switching currFocused to the element which was focused by mouse instead of TAB-key. Fix for #134 */ 
				if(node != this.focusableElements[this.currFocused])
					this.currFocused = this.focusableElements.toArray().indexOf(node);
				
				if(!event.shiftKey) { //Focusing in direct order
					if(this.currFocused == this.focusableElements.length - 1) {
						this.focusableElements.first().focus();
						this.currFocused = 0;
					} else {
						this.currFocused++;
						this.focusableElements[this.currFocused].focus();
					}
				} else { // Shift key is pressed. Focusing in reverse order
					if(this.currFocused == 0) {
						this.focusableElements.last().focus();
						this.currFocused = this.focusableElements.length - 1;
					} else {
						this.currFocused--;
						this.focusableElements[this.currFocused].focus();
					}
				}
				break;			
			case Event.KEY_ESC:
				if(this.active) this._hide(event);
				break;
			case 32:
				this._preventScroll(event);
				break;
			case 0: // For Gecko browsers compatibility
				if(event.which == 32) this._preventScroll(event);
				break;
			case Event.KEY_UP:
			case Event.KEY_DOWN:
			case Event.KEY_PAGEDOWN:
			case Event.KEY_PAGEUP:
			case Event.KEY_HOME:
			case Event.KEY_END:
				// Safari operates in slightly different way. This realization is still buggy in Safari.
				if(Prototype.Browser.WebKit && !["textarea", "select"].include(node.tagName.toLowerCase()))
					event.stop();
				else if( (node.tagName.toLowerCase() == "input" && ["submit", "button"].include(node.type)) || (node.tagName.toLowerCase() == "a") )
					event.stop();
				break;
		}
	},
	
	_preventScroll: function(event) { // Disabling scrolling by "space" key
		if(!["input", "textarea", "select", "button"].include(event.element().tagName.toLowerCase())) 
			event.stop();
	},
	
	_deinit: function()
	{	
		this._removeObservers();
		Event.stopObserving(window, "resize", this._setWidthAndPosition );
		if(this.options.transitions) {
			Effect.toggle(this.MBoverlay, 'appear', {duration: this.options.overlayDuration, afterFinish: this._removeElements.bind(this) });
		} else {
			this.MBoverlay.hide();
			this._removeElements();
		}
		$(this.MBcontent).setStyle({overflow: '', height: ''});
	},
	
	_removeElements: function () {
		$(this.MBoverlay).remove();
		$(this.MBwindow).remove();
		if(Prototype.Browser.IE && !navigator.appVersion.match(/\b7.0\b/)) {
			this._prepareIE("", ""); // If set to auto MSIE will show horizontal scrolling
			window.scrollTo(this.initScrollX, this.initScrollY);
		}
		
		/* Replacing prefixes 'MB_' in IDs for the original content */
		if(typeof this.content == 'object') {
			if(this.content.id && this.content.id.match(/MB_/)) {
				this.content.id = this.content.id.replace(/MB_/, "");
			}
			this.content.select('*[id]').each(function(el){ el.id = el.id.replace(/MB_/, ""); });
		}
		/* Initialized will be set to false */
		this.initialized = false;
		this.event("afterHide"); // Passing afterHide callback
		this.setOptions(this._options); //Settings options object into intial state
	},
	
	_setWidth: function () { //Set size
		$(this.MBwindow).setStyle({width: this.options.width + "px", height: this.options.height + "px"});
	},
	
	_setPosition: function () {
		$(this.MBwindow).setStyle({left: Math.round((Element.getWidth(document.body) - Element.getWidth(this.MBwindow)) / 2 ) + "px"});
	},
	
	_setWidthAndPosition: function () {
		$(this.MBwindow).setStyle({width: this.options.width + "px"});
		this._setPosition();
	},
	
	_getScrollTop: function () { //From: http://www.quirksmode.org/js/doctypes.html
		var theTop;
		if (document.documentElement && document.documentElement.scrollTop)
			theTop = document.documentElement.scrollTop;
		else if (document.body)
			theTop = document.body.scrollTop;
		return theTop;
	},
	_prepareIE: function(height, overflow){
		$$('html, body').invoke('setStyle', {width: height, height: height, overflow: overflow}); // IE requires width and height set to 100% and overflow hidden
		$$("select").invoke('setStyle', {'visibility': overflow}); // Toggle visibility for all selects in the common document
	},
	event: function(eventName) {
		if(this.options[eventName]) {
			var returnValue = this.options[eventName](); // Executing callback
			this.options[eventName] = null; // Removing callback after execution
			if(returnValue != undefined) 
				return returnValue;
			else 
				return true;
		}
		return true;
	}
};

Object.extend(Modalbox, Modalbox.Methods);

if(Modalbox.overrideAlert) window.alert = Modalbox.alert;

Effect.ScaleBy = Class.create();
Object.extend(Object.extend(Effect.ScaleBy.prototype, Effect.Base.prototype), {
  initialize: function(element, byWidth, byHeight, options) {
    this.element = $(element)
    var options = Object.extend({
	  scaleFromTop: true,
      scaleMode: 'box',        // 'box' or 'contents' or {} with provided values
      scaleByWidth: byWidth,
	  scaleByHeight: byHeight
    }, arguments[3] || {});
    this.start(options);
  },
  setup: function() {
    this.elementPositioning = this.element.getStyle('position');
      
    this.originalTop  = this.element.offsetTop;
    this.originalLeft = this.element.offsetLeft;
	
    this.dims = null;
    if(this.options.scaleMode=='box')
      this.dims = [this.element.offsetHeight, this.element.offsetWidth];
	 if(/^content/.test(this.options.scaleMode))
      this.dims = [this.element.scrollHeight, this.element.scrollWidth];
    if(!this.dims)
      this.dims = [this.options.scaleMode.originalHeight,
                   this.options.scaleMode.originalWidth];
	  
	this.deltaY = this.options.scaleByHeight;
	this.deltaX = this.options.scaleByWidth;
  },
  update: function(position) {
    var currentHeight = this.dims[0] + (this.deltaY * position);
	var currentWidth = this.dims[1] + (this.deltaX * position);
	
	currentHeight = (currentHeight > 0) ? currentHeight : 0;
	currentWidth = (currentWidth > 0) ? currentWidth : 0;
	
    this.setDimensions(currentHeight, currentWidth);
  },

  setDimensions: function(height, width) {
    var d = {};
    d.width = width + 'px';
    d.height = height + 'px';
    
	var topd  = Math.round((height - this.dims[0])/2);
	var leftd = Math.round((width  - this.dims[1])/2);
	if(this.elementPositioning == 'absolute' || this.elementPositioning == 'fixed') {
		if(!this.options.scaleFromTop) d.top = this.originalTop-topd + 'px';
		d.left = this.originalLeft-leftd + 'px';
	} else {
		if(!this.options.scaleFromTop) d.top = -topd + 'px';
		d.left = -leftd + 'px';
	}
    this.element.setStyle(d);
  }
});