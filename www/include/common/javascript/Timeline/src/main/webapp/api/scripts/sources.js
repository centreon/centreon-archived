/*==================================================
 *  Default Event Source
 *==================================================
 */


Timeline.DefaultEventSource = function(eventIndex) {
    this._events = (eventIndex instanceof Object) ? eventIndex : new Timeline.EventIndex();
    this._listeners = [];
};

Timeline.DefaultEventSource.prototype.addListener = function(listener) {
    this._listeners.push(listener);
};

Timeline.DefaultEventSource.prototype.removeListener = function(listener) {
    for (var i = 0; i < this._listeners.length; i++) {
        if (this._listeners[i] == listener) {
            this._listeners.splice(i, 1);
            break;
        }
    }
};

Timeline.DefaultEventSource.prototype.loadXML = function(xml, url) {
    var base = this._getBaseURL(url);
    
    var dateTimeFormat = xml.documentElement.getAttribute("date-time-format");
    var parseDateTimeFunction = this._events.getUnit().getParser(dateTimeFormat);

    var node = xml.documentElement.firstChild;
    var added = false;
    while (node != null) {
        if (node.nodeType == 1) {
            var description = "";
            if (node.firstChild != null && node.firstChild.nodeType == 3 || node.firstChild.nodeType == 4) {
                description = node.firstChild.nodeValue;
            }
            var evt = new Timeline.DefaultEventSource.Event(
                parseDateTimeFunction(node.getAttribute("start")),
                parseDateTimeFunction(node.getAttribute("end")),
                parseDateTimeFunction(node.getAttribute("latestStart")),
                parseDateTimeFunction(node.getAttribute("earliestEnd")),
                node.getAttribute("isDuration") != "true",
                node.getAttribute("title"),
                description,
                this._resolveRelativeURL(node.getAttribute("image"), base),
                this._resolveRelativeURL(node.getAttribute("link"), base),
                this._resolveRelativeURL(node.getAttribute("icon"), base),
                node.getAttribute("color"),
                node.getAttribute("textColor")
            );
            evt._node = node;
            evt.getProperty = function(name) {
                return this._node.getAttribute(name);
            };
            
            this._events.add(evt);
            
            added = true;
        }
        node = node.nextSibling;
    }

    if (added) {
        for (var i = 0; i < this._listeners.length; i++) {
            this._listeners[i].onAddMany();
        }
    }
};


Timeline.DefaultEventSource.prototype.loadJSON = function(data, url) {
    var base = this._getBaseURL(url);
    var added = false;  
    if (data && data.events){
        var dateTimeFormat = ("dateTimeFormat" in data) ? data.dateTimeFormat : null;
        var parseDateTimeFunction = this._events.getUnit().getParser(dateTimeFormat);
       
        for (var i=0; i < data.events.length; i++){
            var event = data.events[i];
            var evt = new Timeline.DefaultEventSource.Event(
                parseDateTimeFunction(event.start),
                parseDateTimeFunction(event.end),
                parseDateTimeFunction(event.latestStart),
                parseDateTimeFunction(event.earliestEnd),
                event.isDuration || false,
                event.title,
                event.description,
                this._resolveRelativeURL(event.image, base),
                this._resolveRelativeURL(event.link, base),
                this._resolveRelativeURL(event.icon, base),
                event.color,
                event.textColor
            );
            evt._obj = event;
            evt.getProperty = function(name) {
                return this._obj[name];
            };

            this._events.add(evt);
            added = true;
        }
    }
   
    if (added) {
        for (var i = 0; i < this._listeners.length; i++) {
            this._listeners[i].onAddMany();
        }
    }
};

Timeline.DefaultEventSource.prototype.add = function(evt) {
    this._events.add(evt);
    for (var i = 0; i < this._listeners.length; i++) {
        this._listeners[i].onAddOne(evt);
    }
};

Timeline.DefaultEventSource.prototype.clear = function() {
    this._events.removeAll();
    for (var i = 0; i < this._listeners.length; i++) {
        this._listeners[i].onClear();
    }
};

Timeline.DefaultEventSource.prototype.getEventIterator = function(startDate, endDate) {
    return this._events.getIterator(startDate, endDate);
};

Timeline.DefaultEventSource.prototype.getAllEventIterator = function() {
    return this._events.getAllIterator();
};

Timeline.DefaultEventSource.prototype.getCount = function() {
    return this._events.getCount();
};

Timeline.DefaultEventSource.prototype.getEarliestDate = function() {
    return this._events.getEarliestDate();
};

Timeline.DefaultEventSource.prototype.getLatestDate = function() {
    return this._events.getLatestDate();
};

Timeline.DefaultEventSource.prototype._getBaseURL = function(url) {
    if (url.indexOf("://") < 0) {
        var url2 = this._getBaseURL(document.location.href);
        if (url.substr(0,1) == "/") {
            url = url2.substr(0, url2.indexOf("/", url2.indexOf("://") + 3)) + url;
        } else {
            url = url2 + url;
        }
    }
    
    var i = url.lastIndexOf("/");
    if (i < 0) {
        return "";
    } else {
        return url.substr(0, i+1);
    }
};

Timeline.DefaultEventSource.prototype._resolveRelativeURL = function(url, base) {
    if (url == null || url == "") {
        return url;
    } else if (url.indexOf("://") > 0) {
        return url;
    } else if (url.substr(0,1) == "/") {
        return base.substr(0, base.indexOf("/", base.indexOf("://") + 3)) + url;
    } else {
        return base + url;
    }
};


Timeline.DefaultEventSource.Event = function(
        start, end, latestStart, earliestEnd, instant, 
        text, description, image, link,
        icon, color, textColor) {
        
    this._id = "e" + Math.floor(Math.random() * 1000000);
    
    this._instant = instant || (end == null);
    
    this._start = start;
    this._end = (end != null) ? end : start;
    
    this._latestStart = (latestStart != null) ? latestStart : (instant ? this._end : this._start);
    this._earliestEnd = (earliestEnd != null) ? earliestEnd : (instant ? this._start : this._end);
    
    this._text = text;
    
    
    // Merethis modification by cedrick facon here
    
//    description = description.replace(/~br~/ig, "<br />");
    description = description.replace(/{/ig, "<");
    description = description.replace(/}/ig, ">");
    
    this._description = description;
    this._image = (image != null && image != "") ? image : null;
    this._link = (link != null && link != "") ? link : null;
    
    this._icon = (icon != null && icon != "") ? icon : null;
    this._color = (color != null && color != "") ? color : null;
    this._textColor = (textColor != null && textColor != "") ? textColor : null;
};

Timeline.DefaultEventSource.Event.prototype = {

    getID:          function() { return this._id; },
    
    isInstant:      function() { return this._instant; },
    isImprecise:    function() { return this._start != this._latestStart || this._end != this._earliestEnd; },
    
    getStart:       function() { return this._start; },
    getEnd:         function() { return this._end; },
    getLatestStart: function() { return this._latestStart; },
    getEarliestEnd: function() { return this._earliestEnd; },
    
    getText:        function() { return this._text; },
    getDescription: function() { return this._description; },
    getImage:       function() { return this._image; },
    getLink:        function() { return this._link; },
    
    getIcon:        function() { return this._icon; },
    getColor:       function() { return this._color; },
    getTextColor:   function() { return this._textColor; },
    
    getProperty:    function(name) { return null; },
    
    fillDescription: function(elmt) {
        elmt.innerHTML = this._description;
    },
    fillTime: function(elmt, labeller) {/* Merethis modification by cedrick Facon here
        if (this._instant) {
            if (this.isImprecise()) {
                elmt.appendChild(elmt.ownerDocument.createTextNode(labeller.labelPrecise(this._start)));
                elmt.appendChild(elmt.ownerDocument.createElement("br"));
                elmt.appendChild(elmt.ownerDocument.createTextNode(labeller.labelPrecise(this._end)));
            } else {
                elmt.appendChild(elmt.ownerDocument.createTextNode(labeller.labelPrecise(this._start)));
            }
        } else {
            if (this.isImprecise()) {
                elmt.appendChild(elmt.ownerDocument.createTextNode(
                    labeller.labelPrecise(this._start) + " ~ " + labeller.labelPrecise(this._latestStart)));
                elmt.appendChild(elmt.ownerDocument.createElement("br"));
                elmt.appendChild(elmt.ownerDocument.createTextNode(
                    labeller.labelPrecise(this._earliestEnd) + " ~ " + labeller.labelPrecise(this._end)));
            } else {
                elmt.appendChild(elmt.ownerDocument.createTextNode(labeller.labelPrecise(this._start)));
                elmt.appendChild(elmt.ownerDocument.createElement("br"));
                elmt.appendChild(elmt.ownerDocument.createTextNode(labeller.labelPrecise(this._end)));
            }
        }*/
    }
};