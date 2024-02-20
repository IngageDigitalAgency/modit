
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/setup',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			obj = eval('('+data+')');
			if (obj.status == 'true') {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) eval(obj.code);
			}
		}
	});
}

function moveFolder() {
	id = moveIt('module_folders',this);
	setTimeout(function() {loadTree('setup',id)},100);
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	initTinyMCE();
});


function getInfo(lnk) {
	clearMessages();
	var id = lnk.split("_");
	$.ajax({
		url: '/modit/ajax/getFolderInfo/setup',
		data: {p_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#pageInfo')[0].innerHTML = obj.html;
					
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				var x = 0;
			}
		}
	});
	loadContent(id[1]);
}

function editContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageProperties/setup',
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

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/setup',
		data: {p_id : cId},
		method: 'post',
		dataType: "html",
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.html != null && obj.html.length > 0) {
					removeTinyMCE($('#middleContent textarea'));
					$("#middleContent")[0].innerHTML = obj.html;
					addTinyMCE($('#middleContent textarea'));
				}
				if (obj.code != null && obj.code.length > 0) {
					eval(obj.code);
				}
			} catch(err) {
				$("#middleContent")[0].innerHTML = err.message;
			}
		}
	});
}

function getPid() {
	pid = 0;
	if (document.location.search.length > 0) {
		parms = document.location.search.replace("?","").split("&");
		for(var i = 0; i < parms.length; i++) {
			var p = parms[i].split("=");
			if (p.length > 1 && p[0] == "p_id")
				pid = p[1];
		}
	}
	return pid;
}

function deleteContent(cId) {
	if (confirm("Are you sure you want to delete this item?")) {
		$.ajax({
			url: '/modit/ajax/deleteContent/setup',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == "true")
						document.location = document.location;	// force a refresh
					else
						showError(obj.messages);
					if (obj.code && obj.code.length > 0) eval(obj.code);
				} catch(err) {
					showError(err.message);
				}
			}
		});
	}
}

function initTinyMCE() {
	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		editor_deselector: "mceNoEditor",
		plugins : "advimage,safari,spellchecker,pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage,|,forecolor,backcolor,|,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",theme_advanced_resizing : true,
		remove_script_host : true,
		relative_urls:false,
		content_css : "/css/tinymce.css",
		template_templates : [
			{ title : "Store Content",
			src : "store-content.html",
			description : "2 column store template"
		}],
		setup : function(ed) {
			ed.onPostRender.add(function(ed, cm) {
				resetSize(ed.id);
				console.debug('After render: ' + ed.id);
			});
		}
	}); 
}

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/setup',
		data: {'p_id':id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#searchForm")[0].innerHTML = obj.html;
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				var erm = err.message.concat('<br/>',data);
				$("#searchForm")[0].innerHTML = erm;
			}
		}
	});
}
//
//	browser seems to have an issue with function addContent() after it is called 1 time - still a problem
//
var editArticle = function(m_id) {
	$.ajax({
		url: '/modit/ajax/addContent/setup',
		data: {'m_id': m_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				removeTinyMCE($('#popup textarea'))
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0)
					eval(obj.code);
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

var myDrop = function (obj,event,ui) {
	var li = $( "<li></li>" ).text( ui.draggable.text() )
	li.draggable();
	for(var i = 0; i < ui.draggable.length; i++) {
		li[i].id = ui.draggable[i].id;
		li[i].className = ui.draggable[i].className;
	}
	li.appendTo(obj);
	ui.draggable.remove();
	$( "#fromList li" ).sortElements(function(a,b) {
		return mySort(a) > mySort(b) ? 1 : -1;
	});
	$( "#toList li" ).sortElements(function(a,b) {
		return mySort(a) > mySort(b) ? 1 : -1;
	});
}

function mySort(obj) {
	var cls = obj.className.split(" ");
	for(var i = 0; i < cls.length; i++) {
		if (cls[i].indexOf('sortorder') >= 0) {
			tmp = cls[i].split('_');
			return tmp[1];
		}
	}
	return 0;
}

var formCheck_add = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	$("#".concat(frmId, ' #toList > li')).each(function(idx,el) {
		var ids = el.id.split('_');
		var o = $('<option selected="selected"></option>').text(el.innerHTML);
		o.val(ids[1]);
		o.appendTo($("#".concat(frmId, ' #moduleDestFolders')));
	});
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	//var p_id = $('#moduleFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}


function deleteArticle(id) {
	if (confirm("Are you sure?")) {
		$.ajax({
			url: '/modit/ajax/deleteArticle/setup',
			data: {'j_id':id},
			type:'post',
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == 'true')
						formCheck("showSearchForm","/modit/ajax/showSearchForm/setup","middleContent");
					else
						if (obj.messages)
							showError(obj.messages);
				} catch(err) {
					showError(err.message.concat(' [',data,']'));
				}
			}
		});
	}
}

var getUrl = function () {
	return pagingUrl;
}

function initSearch() {
	$('#pager').change(function() {
		formCheck("showSearchForm","/modit/ajax/showSearchForm/setup","middleContent");
	});
	if ($('#search-tabs').length > 0) {
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function loadModuleInfo(obj) {
	if (obj != null && obj.selectedIndex >= 0) {
		var module = obj.options[obj.selectedIndex].value;
		$.ajax({
			url: '/render/ajax/getModuleInfo',
			data: {'module':module},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == 'true') {
						$('#function_list')[0].innerHTML = obj.html;
					}
					if (obj.messages && obj.messages.length > 0)
						showPopupError(obj.messages);
				} catch(err) {
					showPopupError(err.message);
				}
			}
		});
		$.ajax({
			url: '/render/ajax/getFileList',
			data: {'module':module},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == 'true') {
						$('#inner_html_list')[0].innerHTML = obj.html;
						$('#outer_html_list')[0].innerHTML = obj.html;
						$('#inner_html_list select').change(function() {
							loadFile(this);
						});
						$('#outer_html_list select').change(function() {
							loadFile(this);
						});
					}
					if (obj.messages && obj.messages.length > 0)
						showPopupError(obj.messages);
				} catch(err) {
					showPopupError(err.message);
				}
			}
		});

	}
}

function loadFile(obj) {
	if (obj) {
		var node = obj.parentNode.id.replace('_list','');
		$('#addContent input[name='.concat(node,']'))[0].value = obj.options[obj.selectedIndex].value;
	}
}

function getConfig(m_config) {
	$.ajax({
		url: '/render/ajax/getConfigInfo',
		data: {'configuration':$(m_config).val()},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				$('#m-configuration')[0].innerHTML = obj.html;
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0) showPopupError(obj.messages);
			} catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function editRelations(r_id,p_id) {
	var retValue = $.ajax({
		url:'/modit/ajax/editRelation/setup',
		data:{'r_id':r_id,'p_id':p_id},
		async:false,
		type:'post'
	});
	try {
		var obj = eval('('+retValue.responseText+')');
		if (obj.status == 'true') {
			showAltPopup(obj.html);
		}
		if (obj.code) eval(obj.code);
		if (obj.messages && obj.messages.length > 0) showAltPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);
	}
}
