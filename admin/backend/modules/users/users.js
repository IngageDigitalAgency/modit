
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/users',
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

function moveFolder() {
	id = moveIt('users_folders',this);
	setTimeout(function() {loadTree('users',id)},100);
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	//$('#mainNav > div.inner')[0].innerHTML = html;
	//loadTree('users');
	initTinyMCE();

	$( "#middleContent div.draggable" ).draggable({
		helper: 'clone'
	});

	$( "#middleContent div.droppable" ).droppable({
		drop: function( event, ui ) {
			usersDrop(this,event,ui,'article');
		}
	});

/*
	$('#contentTree ol.ui-sortable li ').droppable({
		drop: function( event, ui ) {
			usersDrop(this,event,ui,'tree');
		}
	});
*/
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
		url: '/modit/ajax/getFolderInfo/users',
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
		url: '/modit/ajax/showPageProperties/users',
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
		url: '/modit/ajax/showPageContent/users',
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
			url: '/modit/ajax/deleteContent/users',
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
			});
		}
	}); 
}

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/users',
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
		url: '/modit/ajax/addContent/users',
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
				if (obj.messages) showPopupError(obj.messages);
			} catch(err) {
				showPopupError(err.message);
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
		o.appendTo($("#".concat(frmId, ' #usersDestFolders')));
	});
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function usersDrop(obj,evt,el,dest) {
	// obj is the destination element
	// evt the event
	// el the object being dropped
	// dest the object type we dragged onto
	clearMessages();
	if (dest == 'tree') {
		div = obj.childNodes[0];	// div.wrapper
		for(var i = 0; i < div.childNodes.length; i++) {
			if (div.childNodes[i].localName == 'a' && div.childNodes[i].className.indexOf('icon_folder') >= 0) {
				destId = div.childNodes[i].id.split("_");
			}
		}
		srcId = el.draggable[0].children[0].innerHTML;
		if (srcId > 0 && destId[1] > 0) {
			$("#dialog-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Copy the article": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/users',
							data: {'src': srcId, 'dest': destId[1], 'type':'tree','copy':1},
							success: function(data,textStatus,jqXHR) {
								try {
									obj = eval('('+data+')');
									if (obj.status != 'true') {
										showError(obj.messages);
									}
									else {
										getInfo('li_'.concat(destId[1]));
									}
								}catch(err) {
									showError(err.message.concat(' [',data,']'));
								}
							}
						});
						$( this ).dialog( "close" );
					},
					"Move the article": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/users',
							data: {'src': srcId, 'dest': destId[1], 'type':'tree','move':1},
							success: function(data,textStatus,jqXHR) {
								try {
									obj = eval('('+data+')');
									if (obj.status != 'true') {
										showError(obj.messages);
									}
									else {
										getInfo('li_'.concat(destId[1]));
									}
								}catch(err) {
									showError(err.message.concat(' [',data,']'));
								}
							}
						});
						$( this ).dialog( "close" );
					},
					Cancel: function() {
						$( this ).dialog( "close" );
					}
				}
			});
		}
	}
	else {
		destId = obj.id.split("_");
		srcId = el.draggable[0].children[0].innerHTML;
		if (srcId > 0 && destId[1] > 0 && srcId != destId[1]) {
			$.ajax({
				url: '/modit/ajax/moveArticle/users',
				data: {'src': srcId, 'dest': destId[1], 'type':'store'},
				success: function(data,textStatus,jqXHR) {
					try {
						obj = eval('('+data+')');
						if (obj.status != 'true') {
							showError(obj.messages);
						}
						else {
							loadActiveFolder()
						}
					}catch(err) {
						showError(err.message + ' ['.concat(data,']') );
					}
				}
			});
		}
	}
}

function loadActiveFolder() {
	$('#contentTree a.active').each(function (idx,el) {
		active = el.id.split("_");
	});
	loadContent(active[1]);
}

function deleteArticle(id) {
	if (confirm("Are you sure?")) {
		$.ajax({
			url: '/modit/ajax/deleteArticle/users',
			data: {'j_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == 'true')
						document.location = '/modit/users';	// refresh the screen
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
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/users","middleContent");
		});
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function deleteRelation(rel_id,p_id) {
	data = $.ajax({
		url: '/modit/ajax/deleteRelation',
		data: {'j_id':rel_id},
		async:false
	});
	try {
		obj = eval('('+data.responseText+')');
		if (obj.status == 'true') {
			loadContent(p_id);
		}
	}
	catch(err) {
		showError(err.message);			
	}
}

function showRole(r_id) {
	data = $.ajax({
		url: '/modit/ajax/showRole/users',
		data: {'r_id':r_id},
		async:false
	});
	try {
		obj = eval('('+data.responseText+')');
		if (obj.status == 'true') {
			$('#tempStorage').find('tbody').append(obj.html);	//[0].innerHTML = obj.html;
			tmp = $('#tempStorage').find('tr');
			$('#allRoles').find('tbody').append(tmp);
		}
		if (obj.messages)
			showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);			
	}
}