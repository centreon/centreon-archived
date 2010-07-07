function CentreonToolTip()
{	
	this._className = 'helpTooltip';
	this._source = './img/icones/16x16/question_grey.gif';
	var _self = this;
	
	this.setClass = function(name) {
		this._className = name;
	}
	
	this.setSource = function(source) {
		this._source = source;
	}
	
	this.render = function() {
		$$('img.' + _self._className).each(function(el){
			el.src = _self._source;
			el.setStyle('cursor:pointer');
			el.setAttribute('onclick' , 'TagToTip("help:'+el.getAttribute("name")+'", TITLE, "Help", CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, "#ffff99", BORDERCOLOR, "orange", TITLEFONTCOLOR, "black", TITLEBGCOLOR, "orange", CLOSEBTNCOLORS, ["","black", "white", "red"], WIDTH, -300, SHADOW, true, TEXTALIGN, "justify");');
		});	
	}
}