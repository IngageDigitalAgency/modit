function addItem(itm) {
	//var x = 1;
	//if (select.selectedIndex < 1) return;
	//var itm = select.options[select.selectedIndex].value;
	$.ajax({
		url: '/modit/ajax/showPageProperties/menu',
		data: {'type':itm, 'id': 0},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#popup")[0].innerHTML = obj.html;
					showPopup();
				}
				if (obj.code && obj.code.length > 0)
					eval(obj.code);
				if (obj.messages && obj.messages.length > 0)
					showPopupError(obj.messages);
				initTinyMCE();
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

var myTest = function () {
	var el = this.currentItem.children('div.wrapper')[0];
	try {
		cId = this.currentItem.find('.info')[0].id.split('_');
		var sId = Array();
		var mvMode = '';
		var dest = $('#contentTree li.placeholder')[0];
		//
		//	figure out where we are in the tree
		//
		if (dest.previousElementSibling != null) {
			var s = dest.previousElementSibling.childNodes[0];
			sId = $(s).find(".info")[0].id.split("_");
			mvMode = 'after';
		}
		else if (dest.nextElementSibling != null) {
			var s = dest.nextElementSibling.childNodes[0];
			sId = $(s).find(".info")[0].id.split("_");
			mvMode = 'before';
		}
		else {
			//
			//	new level. get the parent node
			//
			el = dest.parentNode.parentNode.children[0];
			sId = dest.parentNode.parentNode.childNodes[0];	//.id.split("_");
			sId = $(sId).find(".info")[0].id.split("_");
			mvMode = 'append';
		}
		if (sId[1] > 0 && cId[1] > 0 && mvMode != '') {
			$.ajax({
				url: '/modit/ajax/updateTree',
				data: {
					'src': cId[1], 'dest':sId[1], 'type': mvMode, 'table': 'content'
				},
				success: function(data,textStatus,jqXHR) {
					obj = eval('('+data+')');
					if (obj.status != 'true') {
						alert('Update failed - '.concat(obj.error));
					}
					src = findUrlParm(this.url,'src');
					loadTree(src);
				}
			})
		}
	}
	catch(err) {
		showError(err.message);
		loadTree();
	}
}

function moveFolder() {
	id = moveIt('content',this);
	setTimeout(function() {
		loadTree('menu',id);
	},100);
}

function loadTree_dnu(pId) {
	if (pId != null)
		var tree = getContent('showContentTree','menu','','?p_id='.concat(pId));
	else
		var tree = getContent('showContentTree','menu','',document.location.search);	
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
		callback: myTest
	}); 
	$('#contentTree > div > ol a.active').each(function(idx,el) {
		el = $(el).closest("li")[0];	//.parentNode;	//div wrapped now
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

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	//$('#mainNav > div.inner')[0].innerHTML = html;
	loadTree('menu');
	$(window).resize(function() {
		setIframe();
	});
});

function getInfo(lnk) {
	var id = lnk.split("_");
	$('#contentTree a.active').each(function (idx,el) {
		el.className = el.className.replace('active','');
	});
	$('#contentTree a#'.concat(lnk)).each(function (idx,el) {
		el.className = el.className.concat(' active');
	});
	
	$.ajax({
		url: '/modit/ajax/getPageInfo/menu',
		data: {p_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#pageInfo')[0].innerHTML = obj.html;
					
				}
			} catch(err) {
				$('#pageInfo')[0].innerHTML = err.message.concat('<br/>',data);
			}
		}
	});
	loadContent(id[1]);
}

function editContent(cId,mode) {
	$.ajax({
		url: '/modit/ajax/showPageProperties/menu',
		data: {'id' : cId,'fromContent':mode},
		method: 'post',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					showPopup(obj.html);
					initTinyMCE();
				}
				if (obj.code && obj.code.length > 0)
					eval(obj.code);
				if (obj.messages && obj.messages.length > 0)
					showPopupError(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
}

function loadContent(cId,vId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/menu',
		data: {'c_id' : cId, 'p_id': vId},
		method: 'post',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.html != null && obj.html.length > 0) $("#middleContent")[0].innerHTML = obj.html;
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				$("#middleContent")[0].innerHTML = err.message;
			}
		}
	});
}

function loadVersion(selObj) {
	var new_id = selObj.options[selObj.selectedIndex].value;
	var old_id = $('#editorForm input[name=p_id]')[0].value;
	var retData = $.ajax({
		url: '/modit/ajax/areEditing/menu',
		type: 'post',
		data: {'p_id':'P_'.concat(old_id),'areEditing':1},
		async:false
	});
	var tst = eval('('+retData.responseText+')');
	if (tst.status == 'true') {
		if (!confirm('Unsaved changes exist. Continue? These can be saved later')) {
			$('#versionSelector')[0].value = old_id;
			return;
		}
	}

	loadContent(getPid(),new_id);
}

function getPid() {
	pid = 0;
	var el = $('#contentTree a.active');
	if (el.length > 0) {
		tmp = el[0].id.split("_");
		pid = tmp[1];
	}
	else {
		if (document.location.search.length > 0) {
			parms = document.location.search.replace("?","").split("&");
			for(var i = 0; i < parms.length; i++) {
				var p = parms[i].split("=");
				if (p.length > 1 && p[0] == "p_id")
					pid = p[1];
			}
		}
	}
	return pid;
}

function deleteContent(cId) {
	if (confirm("Are you sure you want to delete this item?")) {
		$.ajax({
			url: '/modit/ajax/deleteContent/menu',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					//if (obj.status == "true")document.location = document.location;	// force a refresh
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages && obj.messages.length > 0) showError(obj.messages);
				} catch(err) {
					$("#middleContent")[0].innerHTML = err.message;
				}
			}
		});
	}
}

function loadPage(p_id) {
	document.location = '/modit/menu?p_id='.concat(p_id);
}

function setEditMode(mode) {
	var f = $($('#editoriFrame')[0].contentDocument).find("form#editorForm");
	if (mode == 'delete' && !confirm('Are you sure you want to delete this version?'))
		return;
	var e = f.find('input[name=edit]');
	e[0].value=mode;
	f.submit();	// set iframe into edit mode
	var f = $('#editorForm');
	var e = f.find('input[name=edit]');
	e[0].value=mode;
	formCheck("editorForm","/modit/ajax/setEditMode/menu","editorContent");return false;
}

function pagination(pnum, url, el, obj) {
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function getUrl() {
	return pagingUrl;
}

function initSearch() {
	if ($('#searchForm').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/menu","middleContent");
		});
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function toggleDropdown(obj) {
	var el = $(obj).closest('div.btn-group')[0];
	if (el.className.indexOf('open') >= 0)
		el.className = el.className.replace('open','');
	else
		el.className = el.className.concat(' open');
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