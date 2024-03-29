
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/lookups',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			obj = JSON.parse(data);
			if (obj.status == 'true') {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			if (obj.messages) showPopupError(obj.messages);
		}
	});
}

function moveFolder() {
	id = moveIt('code_folders',this);
	setTimeout(function() {
		loadTree('lookups',id);
		makeDroppable();
	},100);
}

$(document).ready(function() {
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('lookups');
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
		url: '/modit/ajax/getFolderInfo/lookups',
		data: {p_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				if (obj.status == 'true') {
					$('#pageInfo')[0].innerHTML = obj.html;
					
				}
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			} catch(err) {
				var x = 0;
			}
		}
	});
	loadContent(id[1]);
}

function editContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageProperties/lookups',
		data: {id : cId},
		method: 'post',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'));
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
				}
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			} catch(err) {
				var x = 0;
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/lookups',
		data: {p_id : cId},
		method: 'post',
		dataType: "html",
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				if (obj.html != null && obj.html.length > 0) {
					removeTinyMCE($('#middleContent textarea'));
					$("#middleContent")[0].innerHTML = obj.html;
					addTinyMCE($('#middleContent textarea'));
				}
				if (obj.code != null && obj.code.length > 0) {
					var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				}
			} catch(err) {
				$("#middleContent")[0].innerHTML = err.message;
			}
		}
	});
}

function addSortableDroppable(searchForm) {
	if(searchForm == true) {
		$( "#articleList tbody tr" ).draggable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: function(event) {
				if ($.browser.msie) {
					var tr = $(event.target).closest('tr');
					var tmp = tr.clone();
					tr.children().each(function(idx,el) {
						$(tmp.children()[idx]).width(el.clientWidth);
					});
				}
				else
					var tmp = $('<div class="drag-row"><table></table></div>').find('table').append($(event.target).closest('tr').clone()).end();
				return tmp;
				//return $('<div class="drag-row"><table></table></div>').find('table').append($(event.target).closest('tr').clone()).end();
			},
			forceHelperSize: true,
			cursor: 'move',
			placeholder: 'placeholder'
		});
	} else {
		$( '#articleList tbody').sortable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: fixHelper,
			forceHelperSize: true,
			cursor: 'move',
			placeholder: 'placeholder',
			appendTo: $( '#articleList'),
			start: function( event, ui) {
				dropped = false;
			},
			update: function( event, ui) {
				if(!dropped) {
					idx = $(ui.item[0]).find("div.id")[0].innerHTML.split("/");
					curLoc = $(ui.item[0]).index();
					lookupsDrop(idx[1],curLoc,null,'lookups');
				}
				dropped = false;
			}
		});
		$("#articleList tbody").disableSelection();
	}
	makeDroppable();
}

function makeDroppable() {
		$('#contentTree ol.ui-sortable li .wrapper').droppable({
		accept: "#articleList tr",
		hoverClass: "active_drop",
		tolerance:'pointer',
		drop: function( event, ui ) {
			dropped = true;
			lookupsDrop(this,event,ui,'tree');
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
	clearMessages();
	if (confirm("Are you sure you want to delete this item?")) {
		$.ajax({
			url: '/modit/ajax/deleteContent/lookups',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					if (obj.status == "true") {
						const crypto = window.crypto || window.msCrypto;
						var randomarray = new Uint32Array(1);
						var randomnumber = crypto.getRandomValues(randomarray);
						document.location = '/modit/lookups'.concat('?rd=',randomnumber);	// force a refresh
					}
					else {
						if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
						if (obj.messages) showError(obj.messages);
					}
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
		editor_selector:'mceAdvanced',
		plugins : "advimage,safari,spellchecker,pagebreak,style,layer,table,save,advhr,advlink,emotions,iespell,inlinepopups,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,template,imagemanager,filemanager",
		// Theme options
		theme_advanced_buttons1 : "save,newdocument,|,bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "cut,copy,paste,pastetext,pasteword,|,search,replace,|,bullist,numlist,|,outdent,indent,blockquote,|,undo,redo,|,link,unlink,anchor,image,cleanup,help,code,|,insertdate,inserttime,preview",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,sub,sup,|,charmap,emotions,iespell,media,advhr,|,print,|,ltr,rtl",
		theme_advanced_buttons4 : "insertlayer,moveforward,movebackward,absolute,|,styleprops,spellchecker,|,cite,abbr,acronym,del,ins,attribs,|,visualchars,nonbreaking,template,blockquote,pagebreak,|,insertfile,insertimage,|,forecolor,backcolor,|,fullscreen",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,
		remove_script_host : true,
		relative_urls:false,
		content_css : "/css/tinymce.css",
		template_templates : [
			{ title : "Store Content",
			src : "store-content.html",
			description : "2 column store template"
		}]
	}); 

	tinyMCE.init({
		// General options
		mode : "textareas",
		theme : "advanced",
		editor_deselector: "mceNoEditor",
		editor_selector:'mceSimple',
		content_css : "/css/tinymce.css",
		theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,styleselect,formatselect,fontselect,fontsizeselect",
		theme_advanced_buttons2 : "undo,redo,|,link,unlink,help,code",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true
	}); 


}

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/lookups',
		data: {'p_id':id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				if (obj.status == 'true') {
					$("#searchForm")[0].innerHTML = obj.html;
				}
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
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
var editArticle = function(a_id,p_id) {
	if (p_id == null) {
		var f = $('form input[name=p_id]');
		if (f.length > 0) p_id = f[0].value;
	}
	$.ajax({
		url: '/modit/ajax/addContent/lookups',
		data: {'a_id': a_id,'p_id':p_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
					removeTinyMCE($('#popup textarea'))
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) {
						var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
					}
				if (obj.messages)
					showPopupError(obj.messages);
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
		o.appendTo($("#".concat(frmId, ' #lookupsDestFolders')));
	});
	formCheck(frmId,f_url,el);
}

var paginationDNU = function (pnum, url, el, obj) {
	var p_id = $('#lookupsFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#lookupsFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function lookupsDrop(obj,evt,el,dest) {
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
					"Copy the article": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/lookups',
							data: {'src': srcId[0], 'dest': destId[1], 'type':'tree','copy':1},
							success: function(data,textStatus,jqXHR) {
								try {
									obj = JSON.parse(data);
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
							url: '/modit/ajax/moveArticle/lookups',
							data: {'src': srcId[1], 'dest': destId[1], 'type':'tree','move':1},
							success: function(data,textStatus,jqXHR) {
								try {
									obj = JSON.parse(data);
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
			url: '/modit/ajax/moveArticle/lookups',
			data: {'src': obj, 'dest': evt, 'type':'lookups'},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					if (obj.status != 'true') {
						showError(obj.messages);
						loadActiveFolder()
					}
				}catch(err) {
					showError(err.message);
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

function deleteArticle(a_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteArticle/lookups',
		data: {'j_id':j_id,'a_id':a_id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				if (obj.status == 'true') showPopup(obj.html);
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				if (obj.messages && obj.messages.length > 0) showPopupError(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
}

var getUrl = function () {
	return pagingUrl;
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/lookups","middleContent");
		});
		addSortableDroppable(true);
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
		obj = JSON.parse(data.responseText);
		if (obj.status == 'true') {
			loadContent(p_id);
		}
	}
	catch(err) {
		showError(err.message);
	}
}

function myClone() {
	var tr = $(this).closest("tr")[0];
	var tmp = '<table class="draggerClone"><tr>'.concat(tr.innerHTML,'</tr></table>');
	return tmp;
}

function reSort(src,dest) {
	src = src.split("/");
	dest = dest.split("/");
	data = $.ajax({
		url: '/modit/ajax/moveArticle/lookups',
		data: {'src':src[1],'dest':dest[1],'type':'lookups'},
		async:false
	});
	try {
		obj = JSON.parse(data.responseText);
		if (obj.status == 'true') {
			formCheck("showFolderContent","/modit/ajax/showPageContent/lookups","middleContent");
		}
		if (obj.messages)
			showError(obj.messages);
		if (obj.code && obj.code.length > 0)
			var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
	}
	catch(err) {
		showError(err.message);
	}
}

function loadFromEdit() {
	if ($('#showFolderContent').length > 0) {
		formCheck('showFolderContent','/modit/ajax/showPageContent/lookups','middleContent');
	}
	else {
		formCheck("searchForm","/modit/ajax/showSearchForm/lookups","middleContent");
	}
}