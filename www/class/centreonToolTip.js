function CentreonToolTip()
{	
	this._className = 'helpTooltip';
	this._source = './img/icones/16x16/question_grey.gif';
	this._title = 'Help';
	
	var _self = this;
	
	this.setClass = function(name) {
		this._className = name;
	}
	
	this.setSource = function(source) {
		this._source = source;
	}
	
	this.setTitle = function(title) {
		this._title = title;
	}
	
	this.render = function() {
		jQuery('img.' + _self._className).each(function(index){
			el = jQuery(this);
            el.src = _self._source;
            el.css('cursor', 'pointer');
			el.click(function() {
				TagToTip(
					"help:" + el.attr('name'),
					TITLE, _self._title, CLOSEBTN, true, FIX, [this, 0, 5], BGCOLOR, '#ffff99',
					BORDERCOLOR, 'orange', TITLEFONTCOLOR, 'black', TITLEBGCOLOR, 'orange',
					CLOSEBTNCOLORS, ['','black', 'white', 'red'], WIDTH, -300, SHADOW, true, TEXTALIGN, 'justify'
				);
			});
		});	
	}
}