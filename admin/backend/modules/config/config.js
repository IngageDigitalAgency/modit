
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/config',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			obj = eval('('+data+')');
			if (obj.status == 'true') {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) eval(obj.code);
			}
			if (obj.messages) showPopupError(obj.messages);
		}
	});
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	$('.popovers').popover();
	initTinyMCE();
});


function getInfo(lnk) {
	clearMessages();
	$('#contentTree a.active').each(function (idx,el) {
		el.className = el.className.replace('active','');
	});
	$('#contentTree a#'.concat(lnk)).each(function (idx,el) {
		el.className = el.className.concat(' active');
	});
	var id = lnk.split("_");
	$.ajax({
		url: '/modit/ajax/getFolderInfo/config',
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
		url: '/modit/ajax/showPageProperties/config',
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
		url: '/modit/ajax/showPageContent/config',
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

function deleteContent(cId) {
	if (confirm("Are you sure you want to delete this item?")) {
		$.ajax({
			url: '/modit/ajax/deleteContent/config',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == "true")
						document.location = document.location;	// force a refresh
					else
						alert(obj.html);
					if (obj.code && obj.code.length > 0) eval(obj.code);
				} catch(err) {
					$("#middleContent")[0].innerHTML = err.message;
				}
			}
		});
	}
}

function initTinyMCE_dnu() {
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
			});
		}
	}); 
}

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/config',
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
var editArticle = function(a_id) {
	$.ajax({
		url: '/modit/ajax/addContent/config',
		data: {'a_id': a_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				//if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'))
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) {
						eval(obj.code);
					}
				//}
				if (obj.messages)
					showPopupError(obj.messages);
			} catch(err) {
				showPopupError(err.message);
				//$("#popup div.errorMessage")[0].innerHTML = erm;
			}
		}
	});
	return false;
}

function resetSize(id) {
	var x = $("#".concat(id));
}


var formCheck_add = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	$("#".concat(frmId, ' #toList > li')).each(function(idx,el) {
		var ids = el.id.split('_');
		var o = $('<option selected="selected"></option>').text(el.innerHTML);
		o.val(ids[1]);
		o.appendTo($("#".concat(frmId, ' #configDestFolders')));
	});
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#configFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function deleteArticle(id) {
	if (confirm("Are you sure?")) {
		$.ajax({
			url: '/modit/ajax/deleteArticle/config',
			data: {'j_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == 'true')
						loadActiveFolder()
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
}

function addPairedRow() {
	data = $.ajax({
		url: '/modit/ajax/addPairedRow/config',
		async:false
	});
	try {
		obj = eval('('+data.responseText+')');
		if (obj.status == 'true') {
			$('#tempStorage').find('tbody').append(obj.html);	//[0].innerHTML = obj.html;
			tmp = $('#tempStorage').find('tr');
			$('#pairedList').find('tbody').append(tmp);
		}
		if (obj.messages)
			showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);			
	}
}

function initContent() {
	if ($("#foldertabs" ).length > 0) {
		$("#foldertabs" ).tabs();
		getContent('taxList','config',$('#gp_taxes')[0]);
		getContent('formList','config',$('#gp_forms')[0]);
	}
	else {
		$(document).ready(function() {
			initContent();
		});
	}
}

function editTax(t_id) {
	var retStatus = $.ajax({
		url: '/modit/ajax/editTax/config',
		data: {'t_id':t_id},
		type:'post',
		async:false
	});
	try {
		obj = eval('('+retStatus.responseText+')');
		if (obj.html) showPopup(obj.html);
		if (obj.code) eval(obj.code);
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);
	}
}

function editForm(f_id) {
	var retStatus = $.ajax({
		url: '/modit/ajax/editForm/config',
		data: {'f_id':f_id},
		type:'post',
		async:false
	});
	try {
		obj = eval('('+retStatus.responseText+')');
		if (obj.html) {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
		}
		if (obj.code) eval(obj.code);
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);
	}
}
