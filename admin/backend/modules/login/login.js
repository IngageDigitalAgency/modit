$(window).load(function() {
	var img = $('.site-logo > img')[0];
	var ht = img.clientHeight;
	var box = $('.site-logo').height();
	var pd = (box - ht) /2;
	$('.site-logo').css('height',(box-pd).toString().concat('px'));
	$('.site-logo').css('padding-top',pd.toString().concat('px'));
});