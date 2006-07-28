/*
**Change Tab
*/

function init(){

		for (var i = 2; document.getElementById('tab'+i); i++) {
			document.getElementById('tab'+i).style.display='none';
		}
	}
	function montre(id) {
		for (var i = 1; document.getElementById('c'+i); i++) {
				document.getElementById('c'+i).className='b';
		}
		document.getElementById('c'+id).className='a';

		var d = document.getElementById('tab'+id);
		for (var i = 1; document.getElementById('tab'+i); i++) {
			document.getElementById('tab'+i).style.display='none';
		}
	if (d) {
	d.style.display='block';
	}
}		
