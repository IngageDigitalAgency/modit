
function addItem() {
	$.ajax({
		url: '/modit/ajax/showFolderProperties/templates',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			obj = eval('('+data+')');
			if (obj.status == 'true') {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) eval(obj.code);
			}
			if (obj.messages && obj.messages.length > 0)
				showMessages(obj.messages);
		}
	});
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('templates');
	$(window).resize(function() {
		set_iframe();
	});
});

function moveFolder() {
	id = moveIt('template_folders',this);
	setTimeout(function() {loadTree('templates',id)},100);
}

function getInfo(lnk) {
	var id = lnk.split("_");
	$('#contentTree div.active').each(function (idx,el) {
		el.className = el.className.replace('active','');
	});
	$('#contentTree div#'.concat(lnk)).each(function (idx,el) {
		el.className = el.className.concat(' active');
	});
	$.ajax({
		url: '/modit/ajax/getFolderInfo/templates',
		data: {t_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#pageInfo')[0].innerHTML = obj.html;
					
				}
				if (obj.code && obj.code.length > 0) 
					eval(obj.code);
				if (obj.messages && obj.messages.length > 0)
					showError(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
	loadContent(id[1]);
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/templates',
		data: {t_id : cId},
		type: 'post',
		dataType: "html",
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.html != null && obj.html.length > 0) {
					removeTinyMCE($('#middleContent textarea'));
					$("#middleContent")[0].innerHTML = obj.html;
					addTinyMCE($('#middleContent textarea'));
				}
				if (obj.code != null && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0) showError(obj.messages);
			} catch(err) {
				$("#middleContent")[0].innerHTML = err.message;
			}
		}
	});
}

function editArticle(a_id) {
	$.ajax({
		url: '/modit/ajax/addContent/templates',
		data: {'t_id': a_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				removeTinyMCE($('#popup textarea'))
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0) showPopupError(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
	return false;
}

function getPid() {
	pid = 0;
	if (document.location.search.length > 0) {
		parms = document.location.search.replace("?","").split("&");
		for(var i = 0; i < parms.length; i++) {
			var p = parms[i].split("=");
			if (p.length > 1 && p[0] == "t_id")
				pid = p[1];
		}
	}
	return pid;
}

//
//	browser seems to have an issue with function addContent() after it is called 1 time - still a problem
//
var editItem = function(t_id) {
	$.ajax({
		url: '/modit/ajax/addContent/templates',
		data: {'t_id': t_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'))
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) {
						eval(obj.code);
					}
				}
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
	return false;
}

function resetSize(id) {
	var x = $("#".concat(id));
}

var formCheck_add = function (frmId,f_url,el) {
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var t_id = $('#templateFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function loadVersion_dnu(obj) {
	if (obj.selectedIndex > 0) {
		$('#middleContent iframe')[0].src = '/render?t_id='.concat(obj.options[obj.selectedIndex].value);
		var f = $('#editorForm');
		if (f.length > 0) {
			var e = f.find('input[name=edit]');
			formCheck("editorForm","/modit/ajax/setEditMode/templates","editorContent");
		}
		return false;
	}
}

function loadPage(obj) {
	if (obj.selectedIndex > 0) {
		window.open('/modit/menu?p_id='.concat(obj.options[obj.selectedIndex].value),'_blank');
	}
}

function set_iframe() {
	var obj = $('#middleContent iframe')[0];
	//
	//	add some % fat - scrollHeight itself won't fit in the iframe
	//
	if (obj && obj.contentWindow.document.body) {
		obj.style.height = 1.1*obj.contentWindow.document.body.scrollHeight + 'px';
		obj.style.width = ($('#mainContent').outerWidth() - $('#leftContent').outerWidth() - $('#middleContent').css('padding-left').replace('px','') - $('#middleContent').css('padding-right').replace('px','') - 1)+'px';
	}
} 

function copyItem(tId) {
	$.ajax({
		url: '/modit/ajax/copyTemplate/templates',
		data: {'t_id': tId},
		success: function(data,textStatus,jqXHR) {
			obj = eval('('+data+')');
			if (obj.status == 'true') {
				if (obj.url && obj.url.length > 0) {
					document.location = obj.url;
				}
				else {
					removeTinyMCE($('#popup textarea'));
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) eval(obj.code);
				}
			}
			if (obj.messages && obj.messages.length > 0)
				showMessages(obj.messages);
		}
	});
}

function loadActive(t_id) {
	if (t_id == null || t_id == 0) {
		var lst = $('#templateList').find('div.title.active');
		if (count(lst) > 0) {
			t_id = lst[0].id.split('_')[1];
		}
	}
	if (t_id > 0) {
		getInfo('template_'.concat(t_id));
	}
}

function loadTemplateList() {
	$.ajax({
		url: '/modit/ajax/templateList/templates'.concat(document.location.search),
		success: function(data,textStatus,jqXHR) {
			obj = eval('('+data+')');
			if (obj.status == 'true') {
				$('#contentTree')[0].innerHTML = obj.html;
			}
			if (obj.code && obj.code.length > 0)
				eval(obj.code);
		}
	});
}

function deleteArticle(t_id) {
	if (t_id > 0 && confirm("Are you sure you want to delete this record?")) {
		$.ajax({
			url:'/modit/ajax/deleteTemplate/templates',
			data:{'t_id':t_id},
			success: function(data,textStatus,jqXHR) {
				obj = eval('('+data+')');
				if (obj.status == 'true' && (!obj.messages || obj.messages.length == 0)) {
					document.location = '/modit/templates'.concat(document.location.search);	// refresh the page
				}
				showError(obj.messages);
				if (obj.code && obj.code.length > 0)
					eval(obj.code);
			}
		});
	}
}

function setEditMode(mode) {
	var f = $($('#editoriFrame')[0].contentDocument).find("form#editorForm");
	if (mode == 'delete' && !confirm('Are you sure you want to delete this version?'))
		return;
	if (mode == 'overwrite' && !confirm('Are you sure you want to overwrite all pages using this template?'))
		return;
	var e = f.find('input[name=edit]');
	e[0].value=mode;
	f.submit();	// set iframe into edit mode
	var f = $('#editorForm');
	var e = f.find('input[name=edit]');
	e[0].value=mode;
	formCheck("editorForm","/modit/ajax/setEditMode/templates","editorContent");return false;
}

function editContent(cId) {
	$.ajax({
		url: '/modit/ajax/showFolderProperties/templates',
		data: {id : cId},
		method: 'post',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'));
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				var x = 0;
			}
		}
	});
}

function getUrl() {
	return pagingUrl;
}

function editTemplate(id) {
	$.ajax({
		url: '/modit/ajax/showTemplate/templates',
		data: {t_id: id},
		type:'POST',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#middleContent')[0].innerHTML = obj.html;
					
				}
				if (obj.code && obj.code.length > 0) 
					eval(obj.code);
				if (obj.messages && obj.messages.length > 0)
					showError(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
}

function loadVersion(el) {
	$(el).closest('tr').find('a.editLink')[0].onclick = function() {
		editTemplate(el.value);
	};
}

function resetIFrame(id) {
	var c_id = $($('#editoriFrame')[0].contentDocument.getElementById("editorForm")).find('input[name=t_id]').val();
	var retData = $.ajax({
		url: '/modit/ajax/areEditing/templates',
		type: 'post',
		data: {'t_id':'T_'.concat(c_id),'areEditing':1},
		async:false
	});
	var tst = eval('('+retData.responseText+')');
	if (tst.status == 'true') {
		if (!confirm('Unsaved changes exist. Continue? These can be saved later')) {
			$('#versionSelector')[0].value = c_id;
			return;
		}
	}
	$('#editoriFrame')[0].src = '/render?t_id='.concat(id);
	//
	//	need a delay for the iframe to start loading
	//
	$('#editorForm input[name=t_id]')[0].value = id;
	setTimeout(function() { setEditMode('invalid'); }, 1000);
}

