/* Brazilian initialisation for the jQuery UI date picker plugin. */
/* Written by Leonildo Costa Silva (leocsilva@gmail.com). */
( function( factory ) {
	if ( typeof define === "function" && define.amd ) {

		// AMD. Register as an anonymous module.
		define( [ "../widgets/datepicker" ], factory );
	} else {

		// Browser globals
		factory( jQuery.datepicker );
	}
}( function( datepicker ) {

  datepicker.regional.pt = {
          closeText: 'Fechar',
          prevText: '&#x3c;Anterior',
          nextText: 'Pr&oacute;ximo&#x3e;',
          currentText: 'Hoje',
          monthNames: ['Janeiro','Fevereiro','Mar&ccedil;o','Abril','Maio','Junho',
          'Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'],
          monthNamesShort: ['Jan','Fev','Mar','Abr','Mai','Jun',
          'Jul','Ago','Set','Out','Nov','Dez'],
          dayNames: ['Domingo','Segunda-feira','Ter&ccedil;a-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sabado'],
          dayNamesShort: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
          dayNamesMin: ['Dom','Seg','Ter','Qua','Qui','Sex','Sab'],
          weekHeader: 'Sm',
          dateFormat: 'dd/mm/yy',
          firstDay: 0,
          isRTL: false,
          showMonthAfterYear: false,
          yearSuffix: ''};
  datepicker.setDefaults( datepicker.regional.pt );
  return datepicker.regional.pt;

}));
