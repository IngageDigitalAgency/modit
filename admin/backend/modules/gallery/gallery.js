
function addItem() {
	$.ajax({
		url: '/modit/ajax/showPageProperties/gallery',
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
	id = moveIt('gallery_folders',this);
	setTimeout(function() {
		loadTree('gallery',id);
		makeDroppable;
	},100);
}

$(document).ready(function() {
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('gallery');
	initTinyMCE();
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
		url: '/modit/ajax/getFolderInfo/gallery',
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
		url: '/modit/ajax/showPageProperties/gallery',
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
				if (obj.messages) showPopupError(obj.messages);
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				var x = 0;
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/gallery',
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
				if (obj.messages) showPopupError(obj.messages);
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
			url: '/modit/ajax/deleteContent/gallery',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == "true")
						document.location = document.location;	// force a refresh
					else
						if (obj.messages) showError(obj.messages);
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
			});
		}
	}); 
}

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/gallery',
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
var editImage = function(a_id) {
	$.ajax({
		url: '/modit/ajax/addContent/gallery',
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
					if (obj.messages) showPopupError(obj.messages);
				//}
			} catch(err) {
				var erm = err.message.concat('<br/>',data);
				showError(err.message);
				//$("#popup div.errorMessage")[0].innerHTML = erm;
			}
		}
	});
	return false;
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
	$( "select.draggable li" ).sortElements(function(a,b) {
		return a.id > b.id ? 1 : -1;
	});
}

var formCheck_add = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	$("#".concat(frmId, ' #toList > li')).each(function(idx,el) {
		var ids = el.id.split('_');
		var o = $('<option selected="selected"></option>').text(el.innerHTML);
		o.val(ids[1]);
		o.appendTo($("#".concat(frmId, ' #galleryDestFolders')));
	});
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#galleryFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function imageDrop(obj,evt,el,dest) {
	// obj is the destination element
	// evt the event
	// el the object being dropped
	// dest the object type we dragged onto
	clearMessages();
	if (dest == 'tree') {
		destId = $(obj).find("a.icon_folder")[0].id.split("_");
		srcId = el.draggable.find("div.id")[0].innerHTML.split("/");
		if (srcId[0] > 0 && destId[1] > 0) {
			$("#dialog-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Copy the image": function() {
						$.ajax({
							url: '/modit/ajax/moveImage/gallery',
							data: {'src': srcId[0], 'dest': destId[1], 'type':'tree','copy':1},
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
					"Move the image": function() {
						$.ajax({
							url: '/modit/ajax/moveImage/gallery',
							data: {'src': srcId[1], 'dest': destId[1], 'type':'tree','move':1},
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
		$.ajax({
			url: '/modit/ajax/moveImage/gallery',
			data: {'src': obj, 'dest': evt, 'type':'image'},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status != 'true') {
						showError(obj.messages);
						loadActiveFolder();
					}
				}catch(err) {
					showError(err.message + ' ['.concat(data,']') );
				}
			}
		});
	}
}

function loadActiveFolder() {
	$('#contentTree a.active').each(function (idx,el) {
		active = el.id.split("_");
		loadContent(active[1]);
	});
}

function deleteImage(i_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteImage/gallery',
		data: {'j_id':j_id,'i_id':i_id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') showPopup(obj.html);
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0) showPopupError(obj.messages);
			}
			catch(err) {
			}
		}
	});
	return;
	if (confirm("Are you sure? The actual image will not be removed")) {
		$.ajax({
			url: '/modit/ajax/deleteImage/gallery',
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

function initSearch() {
	if ($('#search-tabs').length > 0) {
		initFolderContent(true);
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

function myClone() {
	return $(this).closest(".droppable")[0].innerHTML;
}


function initFolderContent(searchForm) {
	var preSortList = $('#sortableImages')[0].innerHTML;
	if(searchForm == true) {
		$( "#sortableImages li" ).draggable({
			revert: 'invalid',
			handle: '.header',
			helper: 'clone',
			forceHelperSize: true,
			cursor: 'move',
			placeholder: 'placeholder'
		});
	} else {
		$( '#sortableImages').sortable({
			revert: 'invalid',
			handle: '.header',
			start: function( event, ui) {
				dropped = false;
			},
			update: function( event, ui) {
				if(!dropped) {
					idx = $(ui.item[0]).find("div.id")[0].innerHTML.split("/");
					if (idx[1] == 0) {
						alert("Images are not sortable from search mode");
						$('#sortableImages')[0].innerHTML = preSortList;
						return false;
					}
					curLoc = $(ui.item[0]).index();
					imageDrop(idx[1],curLoc,null,'image');
					preSortList = $('#sortableImages')[0].innerHTML;
				}
				dropped = false;
			}
		});
	}
	makeDroppable();
}

function makeDroppable() {
		$('#contentTree ol.ui-sortable li .wrapper').droppable({
		accept: "ul.ui-sortable li, li.ui-draggable",
		hoverClass: "active_drop",
		tolerance:'pointer',
		drop: function( event, ui ) {
			dropped = true;
			imageDrop(this,event,ui,'tree');
		}
	});
}