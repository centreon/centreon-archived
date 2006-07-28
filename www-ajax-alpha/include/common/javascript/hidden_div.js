
	function DivStatus( nom, numero ){
		var divID = nom + numero;
		if ( document.getElementById && document.getElementById( divID ) ){ // Pour les navigateurs récents			
			Pdiv = document.getElementById( divID );
			PcH = true;
	 	} else if ( document.all && document.all[ divID ] ){ // Pour les veilles versions
			Pdiv = document.all[ divID ];
			PcH = true;
		} else if ( document.layers && document.layers[ divID ] ){ // Pour les très veilles versions
			Pdiv = document.layers[ divID ];
			PcH = true;
		} else {
			PcH = false;
		}
		if ( PcH ){
			Pdiv.className = ( Pdiv.className == 'cachediv' ) ? '' : 'cachediv';
		}
	}
		
		