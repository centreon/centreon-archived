Ext.ux.Mask = function(mask) {
    var config = {
        mask: mask
    };
    Ext.apply(this, config);
};
Ext.extend(Ext.ux.Mask, Object, {
    init: function(c) {
        this.LetrasL = 'abcdefghijklmnopqrstuvwxyz';
        this.LetrasU = Ext.util.Format.uppercase(this.LetrasL);
        this.Letras  = this.LetrasL + this.LetrasU;
        this.Numeros = '0123456789';
        this.Fixos  = '().-:/ '; 
        this.Charset = " !\"#$%&\'()*+,-./0123456789:;<=>?@" + this.LetrasU + "[\]^_/`" + this.LetrasL + "{|}~";
        c.enableKeyEvents = true;
        c.on('keypress', function(field, evt) { return this.press(field, evt); }, this);
    },
    press: function(field, evt) {
        var value = field.getValue();
        var key = evt.getKey();
        var mask = this.mask;
        if(evt){
            var tecla = this.Charset.substr(key - 32, 1);
            if(key < 32 || evt.isNavKeyPress() || key == evt.BACKSPACE){
                return true;
            }
            if(Ext.isGecko || Ext.isGecko2 || Ext.isGecko3)
                if((evt.charCode == 0 && evt.keyCode == 46) || evt.isSpecialKey()) return true; // DELETE (conflict with dot(.))
            var tamanho = value.length;
            if(tamanho >= mask.length){
                field.setValue(value);
                evt.stopEvent();
                return false;
            }
            var pos = mask.substr(tamanho,1); 
            while(this.Fixos.indexOf(pos) != -1){
                value += pos;
                tamanho = value.length;
                if(tamanho >= mask.length){
                    evt.stopEvent();
                    return false;
                }
                pos = mask.substr(tamanho,1);
            }
            switch(pos){
                case '#' : if(this.Numeros.indexOf(tecla) == -1){evt.stopEvent(); return false;} break;
                case 'A' : if(this.LetrasU.indexOf(tecla) == -1){evt.stopEvent(); return false;} break;
                case 'a' : if(this.LetrasL.indexOf(tecla) == -1){evt.stopEvent(); return false;} break;
                case 'Z' : if(this.Letras.indexOf(tecla) == -1) {evt.stopEvent(); return false;} break;
                case '*' : field.setValue(value + tecla); break;
                default : field.setValue(value); break;
            }
        }
        field.setValue(value + tecla);
        evt.stopEvent();
        return false;
    }
});