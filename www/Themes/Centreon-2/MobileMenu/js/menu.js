var isMobile = true;

$(document).ready(function() {
	var navExpand = [].slice.call(document.querySelectorAll('.nav-expand'));
	var backLink =
		'<li class="nav-item">\n\t'
		+ '<a class="nav-link nav-back-link" href="#">'
		+ '\n\t\t' + text_back + '\n\t'
		+ '</a>\n</li>';

	navExpand.forEach(function (item) {
		item.querySelector('.nav-expand-content')
			.insertAdjacentHTML('afterbegin', backLink);
		item.querySelector('.nav-link')
			.addEventListener('click', function () {
				return item.classList.add('active');
			});
		item.querySelector('.nav-back-link')
			.addEventListener('click', function () {
				return item.classList.remove('active');
			});
	});

	document.getElementById('ham')
		.addEventListener('click', function () {
			document.body.classList.toggle('nav-is-toggled');
		});
	$('nav.nav-drill').on('click', function(event) {
		event.stopImmediatePropagation();
	});
	$('section.main').css({'padding-top': '50px'});
});