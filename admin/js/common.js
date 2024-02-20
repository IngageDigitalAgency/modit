var tblHeights = Array();
var fbDiv = null;

function closeMessages() {
	$("#messages > div.warnings")[0].innerHTML = '';
	$("#messages > div.errors")[0].innerHTML = '';
	$("#messages").hide();
}

function getInjector() {
	var html = $('#be_injector')[0].innerHTML;
	$('#be_injector')[0].innerHTML = '';
	return html;
}

function getContent(module,beClass,node,extra) {
	if (extra == null) extra = '';
	if (node == null) node = '';
	var ret_value = null;
	if (beClass != null)
		a_url = '/modit/ajax/'.concat(module,'/',beClass,extra);
	else
		a_url = '/modit/ajax/'.concat(module,extra);
	if (node.length != 0) {
		$.ajax({
			url: a_url,
			async: true,
			success: function (data, textStatus, jqXHR) {
				try {
					obj = JSON.parse(data);
					$(node)[0].innerHTML = obj.html;
					if (obj.code && obj.code.length > 0)
						var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
					if (obj.messages && obj.messages.length > 0)
						showError(obj.messages);
				}
				catch(err) {
					showError(err.message);
				}
			}
		});
	}
	else {
		r = $.ajax({
			url: a_url,
			async: false
		});
		try {			
			ret_value = JSON.parse(responseText);
		}
		catch(err) {
			showError(err.message);
		}
	}
	return ret_value;
}

function showPopup(html) { 
	$("#overlay").css("display","block");
	$("#popupOverlay").css("display","block");
	if (html != null && html.length > 0) {
		$('#popup')[0].innerHTML = html;
		resizeMe();
	}
}

function closePopup() {
	clearPopupErrors();
	$("#popup .mce-tinymce").each(function(idx,el) {
		for(var idx in tinymce.editors) {
			if (tinymce.editors[idx].editorContainer.id == el.id)
				tinymce.editors[idx].remove();
		}
	});
	$('#popupMessages > div.errors')[0].innerHTML = '';
	$("#overlay").css("display","");
	$("#popupOverlay").css("display","");
}

function altClosePopup() {
	clearPopupErrors();
	$("#popup .mce-tinymce").each(function(idx,el) {
		for(var idx in tinymce.editors) {
			if (tinymce.editors[idx].editorContainer.id == el.id)
				tinymce.editors[idx].remove();
		}
	});
	$('#popupMessages > div.errors')[0].innerHTML = '';
	$("#overlay").css("display","");
	$("#popupOverlay").css("display","");
}

function showPopupError(errMsg) {
	showPopupMessages(errMsg);
}

function showAltPopupError(errMsg) {
	showAltPopupMessages(errMsg);
}

function showAlt2PopupError(errMsg) {
	showAltPopupMessages(errMsg);
}

function showAltPopupMessages(errMsg) {
	if (errMsg && errMsg.length > 0) {
		var msg = $('#altPopupMessages > div.errors')[0];
		msg.innerHTML = msg.innerHTML.concat(errMsg);
		$("#altPopupMessages").css('display','block');
		showAltPopup();
	}
}

function showAlt2PopupMessages(errMsg) {
	if (errMsg && errMsg.length > 0) {
		var msg = $('#alt2PopupMessages > div.errors')[0];
		msg.innerHTML = msg.innerHTML.concat(errMsg);
		$("#alt2PopupMessages").css('display','block');
		showAlt2Popup();
	}
}

function showPopupMessages(errMsg) {
	if (errMsg && errMsg.length > 0) {
		var msg = $('#popupMessages > div.errors')[0];
		msg.innerHTML = msg.innerHTML.concat(errMsg);
		$("#popupMessages").css('display','block');
		showPopup();
	}
}

function resizeMe() {
	//console.log('overlay height = '.concat($('#overlay').height()));
	//console.log('document height = '.concat($(document).height()));
	//console.log('popup height = '.concat($('#popupOverlay').height()));
	$('#overlay').css('height','auto');
	$('#overlay').css('height',$(document).height());
}

$(document).ready(function() {
	$(window).resize(function() {resizeMe();});
	$("div.collapser.open").click(function (evt) {
		evt.preventDefault();
		p = this.parentNode;
		var el = $("div#".concat(p.id));
		if (this.className.indexOf('open') >= 0) {
			el.css('overflow','hidden');
			this.className = this.className.replace('open','closed');
			el.animate({
				width:'0px'
			}, 1000);
		} else {
			el.css('overflow','hidden');
			this.className = this.className.replace('closed','open');
			el.css('width','auto');
			el.css('overflow','');
		}
	});
});


function removeTinyMCE(els) {
	els.each(function(idx,el) {
		for(var jdx in tinymce.editors) {
			if (tinymce.editors[jdx].id == el.id)
				tinymce.editors[jdx].remove();
		}
	});
}

function addTinyMCE(els) {
	if ($(els).length > 0)
		initTinyMCE();
}

jQuery.fn.sortElements = (function(){
	var sort = [].sort;
	return function(comparator, getSortable) {
		getSortable = getSortable || function(){return this;};
		var placements = this.map(function(){
			var sortElement = getSortable.call(this),
			parentNode = sortElement.parentNode,
			// Since the element itself will change position, we have
			// to have some way of storing its original position in
			// the DOM. The easiest way is to have a 'flag' node:
			nextSibling = parentNode.insertBefore(
				document.createTextNode(''),
				sortElement.nextSibling
			);
			return function() {
				if (parentNode === this) {
					throw new Error(
						"You can't sort elements if any one is a descendant of another."
					);
				}
				// Insert before flag:
				parentNode.insertBefore(this, nextSibling);
				// Remove flag:
				parentNode.removeChild(nextSibling);
			};
		});
		return sort.call(this, comparator).each(function(i){
			placements[i].call(getSortable.call(this));
		});
	};
 })();
 
function showMessages() {
	$("#messages").show();
}

function clearMessages() {
	$("#messages .message").remove();
	closeMessages();
}

function showError(msg) {
	if (msg.length <= 0) return;
	if (msg.indexOf('<div') >= 0) 
		$("#messages .errors")[0].innerHTML = $("#messages .errors")[0].innerHTML + msg
	else {
		var s = $( "<div class='error message'></div>" ).text( msg );
		s.appendTo($("#messages .errors"));
	}
	showMessages();
}

$(document).ready(function() {
	if ($("#messages .message").length > 0)
		showMessages();
});

function getParent(obj,typ) {
	var el = $(obj).closest(typ);
	if (el.length > 0)
		return el[0];
	else
		return null;
/*
	p = obj;
	while(p.localName != typ && p.parentNode) {
		p = p.parentNode;
	}
	return p;
*/
}

var moveIt = function (folderName,ddObj) {
	var el = ddObj.currentItem.children('div.wrapper')[0]
	for(var i = 0; i < el.childNodes.length; i++) {
		if (el.childNodes[i].className && el.childNodes[i].className.indexOf('info') >= 0)
			cId = el.childNodes[i].id.split("_");
	}
	var sId = Array();
	var mvMode = '';
	var dest = $('#contentTree li.placeholder')[0];
	//
	//	figure out where we are in the tree
	//
	if (dest.previousElementSibling != null) {
		var s = dest.previousElementSibling.childNodes[0].children;
		for(var i = 0; i < s.length; i++) {
			if (s[i].className.indexOf('info') >= 0) {
				sId = s[i].id.split("_");
			}
		}
		mvMode = 'after';
	}
	else if (dest.nextElementSibling != null) {
		var s = dest.nextElementSibling.childNodes[0].children;
		for(var i = 0; i < s.length; i++) {
			if (s[i].className.indexOf('info') >= 0) {
				sId = s[i].id.split("_");
			}
		}
		mvMode = 'before';
	}
	else {
		//
		//	new level. get the parent node
		//
		el = dest.parentNode.parentNode.children[0];
		sId = $(dest.parentNode.parentNode.childNodes[0]).find('.info')[0].id.split("_");
		mvMode = 'append';
	}
	if (sId[1] > 0 && cId[1] > 0 && mvMode != '' && sId[1] != cId[1]) {
		$.ajax({
			url: '/modit/ajax/updateTree',
			data: {
				'src': cId[1], 'dest':sId[1], 'type': mvMode, 'table': folderName
			},
			success: function(data,textStatus,jqXHR) {
				obj = JSON.parse(data);
				if (obj.status != 'true') {
					alert('Update failed - '.concat(obj.error));
					loadTree();
				}
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
		})
	}
	return cId[1];
}

function loadTree(moduleName,pId) {
	if (pId != null)
		var tree = getContent('showContentTree',moduleName,'','?p_id='.concat(pId));
	else
		var tree = getContent('showContentTree',moduleName,'',document.location.search);
	var el = $('#contentTree')[0];
	el.innerHTML = tree.html;
	var tree = $('#contentTree > ol li');
	$('#contentTree > div > ol').nestedSortable({
		disableNesting: 'no-nest',
		forcePlaceholderSize: true,
		handle: 'div.wrapper',
		helper: 'clone',
		items: 'li',
		maxLevels: 0,
		opacity: .6,
		placeholder: 'placeholder',
		revert: 250,
		tabSize: 25,
		tolerance: 'pointer',
		toleranceElement: '> div.wrapper',
		callback: moveFolder
	}); 
	$('#contentTree > div > ol a.active').each(function(idx,el) {
		el = $(el).closest("li")[0];
		while(el.parentNode.localName == 'ol' || el.parentNode.localName == 'li') {
			el = el.parentNode;
			if (el.localName == 'li' && el.children.length > 0 && el.children[0].className == 'wrapper') {
				var div = el.children[0].children[0];
				for(var i = 0; i < div.children.length; i++) {
					if (div.children[i].className.indexOf('toggler') != -1)
						toggle(div.children[i]);
				}
			}
		}
	});
	$('#contentTree > div > ol li.collapsed').each(function(idx,el) {
		if (el.childNodes.length < 2) el.className = el.className.replace('collapsed','');
	});
	$('#contentTree a.info').click(function(evt) {
		evt.preventDefault();
		getInfo(this.id);
	});
	getInfo("li_".concat(getPid()));
}

function formCheck(frmId,f_url,el,callbackFn) {
	if (typeof el == 'object') el = el[0].id;	// just in case we passed in a $() var
	var f_data = '';
	var frm = $("#".concat(frmId))[0];
	try {
		tinyMCE.triggerSave();
		clearMessages();
		$("#".concat(frmId)).ajaxSubmit({
			url: f_url,
			type: frm.method.toUpperCase(),
			async:false,
			success: function(responseText, statusText, xhr, $form) {
				try {					
					obj = JSON.parse(responseText);
					if (obj.status == "true") {
						if (obj.url && obj.url.length > 0)
							document.location = obj.url;
					}
					if (el.length == 0 || el == 'popup') {
						removeTinyMCE($('#popup textarea'));
						showPopup(obj.html);
						addTinyMCE($('#popup textarea'));
						if (obj.messages && obj.messages.length > 0) {
							showPopupError(obj.messages);
						}
					}
					else if (el == 'altPopup') {
						removeTinyMCE($('#altPopup textarea'));
						showAltPopup(obj.html);
						addTinyMCE($('#altPopup textarea'));
						if (obj.messages && obj.messages.length > 0) {
							showAltPopupError(obj.messages);
						}
					}
					else if (el == 'alt2Popup') {
						removeTinyMCE($('#alt2Popup textarea'));
						showAlt2Popup(obj.html);
						addTinyMCE($('#alt2Popup textarea'));
						if (obj.messages && obj.messages.length > 0) {
							showAlt2PopupError(obj.messages);
						}
					}
					else {
						removeTinyMCE($('#'.concat(el,' textarea')));
						$('#'.concat(el))[0].innerHTML = obj.html;
						addTinyMCE($('#'.concat(el,' textarea')));
						if (obj.messages && obj.messages.length > 0) {
							showError(obj.messages);
						}
					}
					if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
					if (callbackFn != null) {
						try {
							obj = eval('('.concat(callbackFn,'(obj))'));
							if (obj && obj.url && obj.url.length > 0)
								document.location = obj.url;
						}
						catch(err) {
						}
					}
				}
				catch(err) {
					if ($('#altOverlay').css('display') == 'block')
						showAltPopupMessages(err.message);
					else if ($('#alt2Overlay').css('display') == 'block')
						showAlt2PopupMessages(err.message);
					else if ($('#popupOverlay').css('display') == 'block')
						showPopupMessages(err.message);
					else
						showError(err.message);
				}
			}
		});
	}
	catch(err) {
		showError(err.message);
	}
	return false;
}

function toggle(el) {
	p = $(el).closest('li')[0];
	if (el.innerHTML == '+') {
		p.className = p.className.replace('collapsed','expanded');
		el.innerHTML = '-';
	}
	else {
		el.innerHTML = '+';
		p.className = p.className.replace('expanded','collapsed');
	}
	if($(el).closest('#mainNav').length >= 1)
		$(el).closest('li').siblings('li.expanded').removeClass('expanded').addClass('collapsed').find('a.toggler').html('+');
}

function sort(fld,frm,url,el) {
	var order = $('#'.concat(frm,' input[name=sortorder]'))[0];
	if ($('#'.concat(frm,' input[name=sortby]'))[0].value == fld) {
		order.value = order.value == 'desc' ? 'asc' : 'desc';
	}
	$('#'.concat(frm,' input[name=sortby]')).val(fld);
	formCheck(frm,url,el);
	return false;
}

function loadProvinces(obj,wrapper,opts) {
	var c_id = obj.options[obj.selectedIndex].value;
	if (opts == null) 
		opts = {'c_id':c_id};
	else
		opts['c_id'] = c_id;
	$.ajax({
		url: '/modit/ajax/loadProvinces',
		data: opts,
		success: function(retData) {
			try{
				obj = JSON.parse(retData);
				if (obj.status == "true")
					wrapper.innerHTML = obj.html;
				if (obj.messages)
					showError(obj.messages);
			}catch(err) {
				showError(err.message);
			}
		}
	});
}

function clearPopupErrors() {
	$('#popupMessages > div.errors')[0].innerHTML = '';
	$("#popupMessages").css('display','');
}

function clearAltPopupErrors() {
	$('#altPopupMessages > div.errors')[0].innerHTML = '';
	$("#altPopupMessages").css('display','');
}

function clearAlt2PopupErrors() {
	$('#alt2PopupMessages > div.errors')[0].innerHTML = '';
	$("#alt2PopupMessages").css('display','');
}

function clearSelect(id) {
	var el = $('#'.concat(id));
	if (el.length > 0) {
		var s = el[0];
		while(s.options && s.options.length > 0) {
			s.options[0].remove();
		}
	}
}

function copyOL(oFrom,sTo) {
	//
	//	populate a <select> from <li>
	//
	$("#".concat(oFrom," > li")).each(function(idx,el) {
		var ids = el.id.split('_');
		var o = $('<option selected="selected"></option>').text(el.innerHTML);
		o.val(ids[ids.length-1]);
		o.appendTo($("#".concat(sTo)));
	});
}

function folderCopyHelper() {
	//
	//	can't use text() as the innerhtml can contain tags
	//
	if ($(this).find('a.info').length > 0) {
		ret = $("<li></li>");
		ret[0].innerHTML = $(this).find('a.info')[0].innerHTML;
	}
	else {
		ret = $("<li></li>");
		ret[0].innerHTML = $(this)[0].innerHTML;
	}
	return ret;
}

var folderDrop = function (obj,event,ui) {
	if ($(ui.draggable[0]).closest('ol.level_1')[0].id == obj.id) {
		//
		//	dragging onto istelf
		//
		return;
	}
	if (obj.className.indexOf('source') >= 0) {
		ui.draggable.remove();
	}
	else {
		var from = ui.draggable.find("a.info")[0];
		from_id = from.id.split('_');
		bDupe = false;
		$(obj).find("li").each(function(idx,el) {
			tmp = el.id.split("_");
			bDupe = bDupe || tmp[1] == from_id[from_id.length-1];
		});
		if (!bDupe) {
			var li = $( "<li></li>" ).text( from.innerHTML );
			const crypto = window.crypto || window.msCrypto;
			var randomarray = new Uint32Array(1);
			var randomnumber = crypto.getRandomValues(randomarray);
			li[0].id = 'folder'.concat(Math.floor(randomnumber*10000),'_',from_id[from_id.length-1]);
			li.draggable();
			li.appendTo(obj);
		}
	}
}

function toggleNav(el) {
	var x = 0;
	var img = $(el).find('img')[0];
	var wrapper_width = $("#mainNav .wrapper").outerWidth();
	if( $('#mainNav').css('left') != '0px') {
		img.src = img.src.replace('right','left');
		$("#mainNav").animate({ 'left' : 0 });
	} else {
		img.src = img.src.replace('left','right');
		$("#mainNav").animate({ 'left' : '-' + wrapper_width + 'px' });
	}
}

function parseUrl(url) {
	var u = url.split("?");
	return u[1].split("&");
}

function findUrlParm(url,parm) {
	var parms = parseUrl(url);
	for(i = 0; i < parms.length; i++) {
		var t = parms[0].split("=");
		if (t[0] == parm)
			return t[1];
	}
	return false;
}

function hideTables(obj) {
	obj.find('table').each(function (idx,el) {
		var tbl = $(el);
		var ct = tbl.find('tr');
		if (ct.length <= 1) {
			tbl.closest('.listing').hide();
		}
	});
	obj.find('div.collapsible').each(function(idx,el) {
		var obj = $(el);
		var w = obj.find(".wrapper");
		$.data(el,'height',w.outerHeight());
		w.css("height","0px");
		obj.hover(
			function() {
				var h = $.data(this,'height');
				$(this).find("div.wrapper").stop().animate({height:h.toString().concat('px')},500);
			},
			function() {
				$(this).find("div.wrapper").stop().animate({height:'0px'},500);
			}
		);
	});
	var ct = 0;
	obj.find('div.collapsible').each(function(idx,el) {
		if ($(el).css("display") != "none") ct++;
	});
	if (ct == 0) $('.collapsible').closest('div.ui-tabs').find('ul.ui-tabs-nav').css("display","none");
}

function showAltPopup(html) {
	if ($("#overlay").css("display") == "none")
		$("#overlay").css("display","block");
	$("#popupOverlay").css("z-index","1");
	$("#altOverlay").css("display","block");
	if (html != null)
		$('#altPopup')[0].innerHTML = html;
	resizeMe();
}

function showAlt2Popup(html) {
	if ($("#overlay").css("display") == "none")
		$("#overlay").css("display","block");
	$("#popupOverlay").css("z-index","1");
	$("#alt2Overlay").css("display","block");
	if (html != null)
		$('#alt2Popup')[0].innerHTML = html;
	resizeMe();
}

function closeAltPopup() {
	clearAltPopupErrors();
	$("#altPopup .mce-tinymce").each(function(idx,el) {
		for(var idx in tinymce.editors) {
			if (tinymce.editors[idx].editorContainer.id == el.id)
				tinymce.editors[idx].remove();
		}
	});
	$("#altOverlay").css("display","");
	$("#popupOverlay").css("z-index","");
	if ($("#popupOverlay").css("display") == "none") {
		$("#overlay").css("display","none");
	}
}

function closeAlt2Popup() {
	clearAlt2PopupErrors();
	$("#altPopup .mce-tinymce").each(function(idx,el) {
		for(var idx in tinymce.editors) {
			if (tinymce.editors[idx].editorContainer.id == el.id)
				tinymce.editors[idx].remove();
		}
	});
	$("#alt2Overlay").css("display","");
	$("#popupOverlay").css("z-index","");
	if ($("#popupOverlay").css("display") == "none") {
		$("#overlay").css("display","none");
	}
}

function initTinyMCE() {
	tinymce.init({
		selector:'textarea.mceAdvanced,textarea.mceSimple',
		content_css:"/css/tinymce.css?ver=1.1",
		image_advtab:true,
		importcss_file_filter: "/css/tinymce.css",
		importcss_append:true,
		moxiemanager_image_settings : { 
			view : 'thumbs', 
			moxiemanager_rootpath:'/'.concat(rootpath,"/images/")
		},
		moxiemanager_file_settings : { 
			view : 'thumbs', 
			moxiemanager_rootpath : '/'.concat(rootpath,'/files/')
		},
		relative_urls: false,
		convert_urls: true,
		remove_script_host:true,
		verify_html: false,
		extended_valid_elements: "i[class]",
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code fullscreen",
			"insertdatetime media table contextmenu paste moxiemanager code importcss"
		],
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image insertimage | code"
	});
}

function toggleForm(id) {
	var el = $('#'.concat(id))[0];
	if (el.className.search('hidden') >= 0)
		el.className = el.className.replace('hidden','show');
	else
		el.className = el.className.replace('show','hidden');
}

// Return a helper with preserved width of cells
var fixHelper = function(e, ui) {
	ui.children().each(function() {
		$(this).width($(this).width());
	});
	return ui;
};

function resetSearchForm(btn) {
	var frm = $(btn).closest('form');
	frm.find('input:not(:hidden)').each(function (idx,el) {
		if (el.name != 'submit')
			el.value = '';
	});
	frm.find('select').each(function (idx,el) {
		el.selectedIndex=-1;
	});
	frm.find('input[name="reset"]').each(function (idx,el) {
		el.value=1;
	});
}

function myAccount() {
	retData = $.ajax({
		url: '/modit/ajax/myAccount',
		async: false
	}).responseText;
	try{
		obj = JSON.parse(retData);
		if (obj.status == "true") showAltPopup(obj.html);
		if (obj.messages) showError(obj.messages);
		if (obj.code ) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
	}catch(err) {
		showError(err.message);
	}
}

function loadProductList(obj,dest,update,rename,beClass) {
	if (beClass == null) beClass='product';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			if (rename) 
				el = '#relatedDestProducts';
			else
				el = '#byProductSource';
			$(el).find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myProductList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			if (rename) {
				obj.html = obj.html.replace('byProductSource','relatedDestProducts');
				obj.code = obj.code.replace('byProductSource','relatedDestProducts');
				obj.html = obj.html.replace('destRelatedProducts[]','relatedDestProducts[]');
			}
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadCouponList(obj,dest,update,beClass) {
	if (beClass == null) beClass='product';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destCoupons').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myCouponList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadBlogList(obj,dest,update,beClass) {
	if (beClass == null) beClass='product';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destBlogs').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myBlogList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadAdvertList(obj,dest,update,beClass) {
	if (beClass == null) beClass='product';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destAds').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myAdvertList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadStoreList(obj,dest,update,beClass) {
	if (beClass == null) beClass='stores';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destStores').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myStoreList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadEventList(obj,dest,update,beClass) {
	if (beClass == null) beClass='calendar';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destEvents').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myEventList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadNewsList(obj,dest,update,beClass) {
	if (beClass == null) beClass='product';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destNews').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myNewsList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function loadImageList(obj,dest,update,beClass) {
	if (beClass == null) beClass='gallery';
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var selOpt = new Array();
		if (update) {
			$('#destGallery').find('option').each(function(idx,el) {
				if (el.selected)
					selOpt[selOpt.length] = el.value;
			});
		}
		retData = $.ajax({
			url: '/modit/ajax/myGalleryList/'.concat(beClass),
			data: {'f_id':id, 'selected[]': selOpt},
			type: 'post',
			async:false
		});
		try {
			obj = JSON.parse(retData.responseText);
			$(dest)[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showPopupMessages(obj.messages);
		} catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function setIframe() {
	var obj = $('#middleContent iframe')[0];
	//
	//	add some % fat - scrollHeight itself won't fit in the iframe
	//
	if (obj) {
		obj.style.height = Math.max(400,obj.contentWindow.document.body.scrollHeight).toString() + 'px';
		//obj.style.width = ($('#page').outerWidth() - $('#leftContent').outerWidth() - $('#rightContent').outerWidth() - 15)+'px';
		obj.style.width = ($('#mainContent').outerWidth() - $('#leftContent').outerWidth() - $('#middleContent').css('padding-left').replace('px','') - $('#middleContent').css('padding-right').replace('px','') - 1)+'px';
	}
} 

function cleanDatePicker() {
	if ($.datepicker != null) {
		var old_fn = $.datepicker._updateDatepicker;
		$.datepicker._updateDatepicker = function(inst) {
			old_fn.call(this, inst);
			var buttonPane = $(this).datepicker("widget").find(".ui-datepicker-buttonpane");
			$("<button type='button' class='ui-datepicker-clean ui-state-default ui-priority-primary ui-corner-all'>Clear</button>").appendTo(buttonPane).click(function(ev) {
				$.datepicker._clearDate(inst.input);
			});
		}
	}
}

$(document).ready(function() {
	cleanDatePicker();
});

function initDateFields() {
	$('.def_field_datepicker').datepicker({
		showButtonPanel:true,
		changeMonth: true,
		changeYear:true,
		beforeShow: function() {
			$('#ui-datepicker-div').maxZIndex(); 
		}
	});
	$('.def_field_datetimepicker').datepicker({
		showButtonPanel:true,
		changeMonth: true,
		changeYear:true
	});
}

$.maxZIndex = $.fn.maxZIndex = function(opt) {
    /// <summary>
    /// Returns the max zOrder in the document (no parameter)
    /// Sets max zOrder by passing a non-zero number
    /// which gets added to the highest zOrder.
    /// </summary>    
    /// <param name="opt" type="object">
    /// inc: increment value, 
    /// group: selector for zIndex elements to find max for
    /// </param>
    /// <returns type="jQuery" />
    var def = { inc: 10, group: "*" };
    $.extend(def, opt);
    var zmax = 0;
    $(def.group).each(function() {
        var cur = parseInt($(this).css('z-index'));
        zmax = cur > zmax ? cur : zmax;
    });
    if (!this.jquery)
        return zmax;

    return this.each(function() {
        zmax += def.inc;
        $(this).css("z-index", zmax);
    });
}

function setImageViewing() {
	$('div.thumbnail-item').each(function(idx,el) {
		if ($(el).find('img').length == 0)
			$(el).remove();
	});
	$('div.thumbnail-item').mouseenter(function(e) {

			// Calculate the position of the image tooltip
			x = e.pageX - $(this).offset().left;
			y = e.pageY - $(this).offset().top;

			// Set the z-index of the current item, 
			// make sure it's greater than the rest of thumbnail items
			// Set the position and display the image tooltip
			$(this).css('z-index','15')
			.children("div.img_tooltip")
			.css({'top': y + 10,'left': x + 20,'display':'block'});
			
		}).mousemove(function(e) {
			
			// Calculate the position of the image tooltip			
			x = e.pageX - $(this).offset().left;
			y = e.pageY - $(this).offset().top;
			
			// This line causes the tooltip will follow the mouse pointer
			$(this).children("div.img_tooltip").css({'top': y + 10,'left': x + 20});
			
		}).mouseleave(function() {
			
			// Reset the z-index and hide the image tooltip 
			$(this).css('z-index','1')
			.children("div.img_tooltip")
			.animate({"opacity": "hide"}, "fast");
		});
}

function setText(obj,bFocus) {
	if (bFocus) {
		if (obj.value == obj.attributes["prompt"].value)
			obj.value = "";
	}
	else {
		if (obj.value.length == 0) {
			obj.value = obj.attributes["prompt"].value;
		}
	}
}

finishFBLogin = function(response) {
	fb_status = response.status;
	$.ajax({
		url: "/modit/ajax/setFBStatus",
		data: {st: fb_status, r: response},
		success:function(obj, status, xhr) {
			try {
				obj = JSON.parse(obj);
				$(fbDiv).remove();
			}
			catch(err) {
			}
		}
	});
}
myFBLogin = function(el) {
	fbDiv = el;
	//FB.login(finishFBLogin,{scope:"user_about_me,manage_pages,publish_actions,publish_pages"});
	FB.login(finishFBLogin,{scope:"user_about_me,manage_pages,publish_pages"});
}

showWait = function() {
	$("#overlay").css("display","block");
	$("#overlay").css("z-index","10000");
	$("#overlay").html('<div class="refresh-container"><i class="refresh-spinner fa fa-spinner fa-spin fa-5x"></i></div>');
}

closeWait = function() {
	$("#overlay").html("");
	$("#overlay").css("display","none");
	$("#overlay").css("z-index","");
}

function evalJsonParse(obj) {	
    return Function('"use strict";return (' + obj + ')')();
}