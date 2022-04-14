function CentreonToolTip()
{	
	this._className = 'helpTooltip';
	this._source = '';
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
		jQuery('span.' + _self._className).each(function(index){
			var el = jQuery(this);
            el.empty().append(_self._source);
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