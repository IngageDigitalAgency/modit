$(document).ready(function() {
	$("a.nav-toggler").click(function() {
		$(this).parent().find(".menu").toggle();
	});
});