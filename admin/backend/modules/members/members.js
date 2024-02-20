
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/members',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			obj = JSON.parse(data);
			if (obj.status == 'true') {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
		}
	});
}

function moveFolder() {
	id = moveIt('members_folders',this);
	setTimeout(function() {
		loadTree('members',id);
		makeDroppable();
	},100);
}

$(document).ready(function() {
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('members');
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
		url: '/modit/ajax/getFolderInfo/members',
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
		url: '/modit/ajax/showPageProperties/members',
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
		url: '/modit/ajax/showPageContent/members',
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
			url: '/modit/ajax/deleteContent/members',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					if (obj.status == "true")
						window.location.reload();	// force a refresh
					else
						showError(obj.messages);
					if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				} catch(err) {
					showError(err.message);
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
		url: '/modit/ajax/showSearchForm/members',
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
var editArticle = function(m_id,j_id) {
	$.ajax({
		url: '/modit/ajax/addContent/members',
		data: {'m_id': m_id, 'j_id': j_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				//if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'))
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) {
						var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
					}
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

function resetSize(id) {
	var x = $("#".concat(id));
}

var myDrop_dnu = function (obj,event,ui) {
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
function addCallback(obj) {

}

var formCheck_add = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	$("#".concat(frmId, ' #toList > li')).each(function(idx,el) {
		var ids = el.id.split('_');
		var o = $('<option selected="selected"></option>').text(el.innerHTML);
		o.val(ids[1]);
		o.appendTo($("#".concat(frmId, ' #membersDestFolders')));
	});
	formCheck(frmId,f_url,el,'addCallback');
}

var paginationDNU = function (pnum, url, el, obj) {
	var p_id = $('#membersFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#membersFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function membersDrop(obj,evt,el,dest) {
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
					"Copy the member": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/members',
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
									showError(err.message);
								}
							}
						});
						$( this ).dialog( "close" );
					},
					"Move the member": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/members',
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
									showError(err.message);
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
			url: '/modit/ajax/moveArticle/members',
			data: {'src': obj, 'dest': evt, 'type':'member'},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					if (obj.messages) showError(obj.messages);
					loadActiveFolder();
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

function deleteArticle(m_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteArticle/members',
		data: {'j_id':j_id,'m_id':m_id},
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

function loadAddress(a_id) {
	var o_id = $('#m_id')[0].value;
	var ret_data = $.ajax({
		url: '/modit/ajax/editAddress/members',
		data: {'a_id':a_id,'o_id':o_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		showAltPopup(obj.html);
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showAltPopupMessages(obj.messages);
	} catch(err) {
		showAltPopupMessages(err.message);
	}
}

function checkAddMember(id) {
	if (id == 0 || id == null) {
		$('#addressTab')[0].innerHTML = '<span class="errorMessage">The member must be saved first</span>';
		$('#imageTab')[0].innerHTML = '<span class="errorMessage">The member must be saved first</span>';
		//$('#profilesTab')[0].innerHTML = '<span class="errorMessage">The member must be saved first</span>';
	} else {
		$('#addressSelector').change(function() {
			loadAddress(this);
		});
		//loadMemberMedia(id,0);
	}
}

function deleteAddress(a_id,o_id) {
	if (confirm("Are you sure you want to delete this address?")) {
		$.ajax({
			url: '/modit/ajax/deleteAddress/members',
			data: {'a_id':a_id,'o_id':o_id,'type':'member'},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					if (obj.status == 'true')
						loadAddresses();
					if (obj.messages) showError(obj.messages);
				} catch(err) {
					showError(err.message.concat(' [',data,']'));
				}
			}
		});
	}
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/members","middleContent");
		});
		addSortableDroppable(true);
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function editProfile_dnu(p_id) {
	ret_data = $.ajax({
		url: '/modit/ajax/editProfile/members',
		data: {'p_id':p_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			showAltPopup(obj.html);
		}
	}
	catch(err) {
		showAltPopupError(err.message);
	}
};

function saveAddress(frm,frmUrl) {
	var m_data = $('#'.concat(frm)).serialize();
	ret_data = $.ajax({
		url: frmUrl,
		data: m_data,
		type:'POST',
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			if (obj.messages && obj.messages.length > 0) {
				//
				//	edit failed
				//
				showAltPopupError(obj.messages);
				showAltPopup(obj.html);
			}
			else {
				closeAltPopup();
				loadAddresses();
			}
		}
		if (obj.code && obj.code.length > 0)
			var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
	}
	catch(err) {
		showAltPopupError(err.message);
	}
}

function loadAddresses() {
	var o_id = $('#m_id')[0].value;
	ret_data = $.ajax({
		url: '/modit/ajax/loadAddresses/members',
		data: {'o_id':o_id},
		async:false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			$('#addressTab')[0].innerHTML = obj.html;
		}
	}
	catch(err) {
		showAltPopupError(err.message);
	}
}	

//
//	editProfile seems to be a browser thing [based on spelling], works 1 time then fails 
//
function editprofile(p_id) {
	ret_data = $.ajax({
		url: '/modit/ajax/editProfile/members',
		data: {'p_id':p_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			showAltPopup(obj.html);
			addTinyMCE($('#altPopup textarea'));
		}
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages && obj.messages.length > 0) showAltPopupError(obj.messages);
	}
	catch(err) {
		alert(err.message);
	}
}

function loadFromEdit() {
	if ($('#showFolderContent').length > 0) {
		formCheck('showFolderContent','/modit/ajax/showPageContent/members','middleContent');
	}
	else {
		formCheck("searchForm","/modit/ajax/showSearchForm/members","middleContent");
	}
}

function addSortableDroppable(searchForm) {
	if(searchForm == true) {
		$( "#articleList tbody tr" ).draggable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: function(event) {
/*
				if ($.browser.msie) {
					var tr = $(event.target).closest('tr');
					var tmp = tr.clone();
					tr.children().each(function(idx,el) {
						$(tmp.children()[idx]).width(el.clientWidth);
					});
				}
				else
*/
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
					membersDrop(idx[1],curLoc,null,'member');
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
			membersDrop(this,event,ui,'tree');
		}
	});
}

function showOrders(o_id) {
	var ret_data = $.ajax({
		url: '/modit/ajax/showOrders/members',
		data: {'o_id':o_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		showAltPopup(obj.html);
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showAltPopupMessages(obj.messages);
	} catch(err) {
		showAltPopupMessages(err.message);
	}
}

function loadGroupMedia(f_id) {
	if (f_id == 0 || f_id == null)
		return;
	var retValue = $.ajax({
		url:'/modit/ajax/loadMedia/members',
		data:{'f_id':f_id,'m_id':0},
		async:false
	});
	try {
		obj = JSON.parse(retValue.responseText);
		$('#tabs-4')[0].innerHTML = obj.html;
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showPopupMessages(obj.messages);
	} catch(err) {
		showPopupMessages(err.message);
	}
}

function loadMemberMedia(m_id,f_id) {
	if (m_id == 0 || m_id == null)
		return;
	var retValue = $.ajax({
		url:'/modit/ajax/loadMedia/members',
		data:{'m_id':m_id,'f_id':0},
		async:false
	});
	try {
		obj = JSON.parse(retValue.responseText);
		$('#tabs-6')[0].innerHTML = obj.html;
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showPopupMessages(obj.messages);
	} catch(err) {
		showPopupMessages(err.message);
	}
}

function fnEditMedia(f_id,m_id,p_id) {
	var retValue = $.ajax({
		url:'/modit/ajax/editMedia/members',
		data:{'m_id':m_id,'f_id':f_id,'p_id':p_id},
		async:false
	});
	try {
		obj = JSON.parse(retValue.responseText);
		showAltPopup(obj.html);
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showAltPopupMessages(obj.messages);
	} catch(err) {
		showAltPopupMessages(err.message);
	}
}

function fnDeleteMedia(p_id,f_id,m_id) {
	if (p_id > 0) {
		if (!confirm('Are you sure?'))
			return false;
		var retStatus = $.ajax({
			url:'/modit/ajax/deleteMedia/members',
			data:{'p_id':p_id,'m_id':m_id,'f_id':f_id},
			async:false
		});
		try {
			var obj = JSON.parse(retStatus.responseText);
			if (obj.status == 'true') {
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			if (obj.messages)
				showPopupMessages(obj.messages);
		}
		catch(err) {
			showPopupMessages(err.message);
		}
	}
}

function checkAddProfile(m_id,f_id) {
	if (f_id > 0) {
		var retValue = $.ajax({
			url:'/modit/ajax/loadMedia/members',
			data:{'m_id':m_id,'f_id':f_id},
			async:false
		});
		try {
			obj = JSON.parse(retValue.responseText);
			$('#tabs-p3')[0].innerHTML = obj.html;
			if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			if (obj.messages)
				showAltPopupMessages(obj.messages);
		} catch(err) {
			showAltPopupMessages(err.message);
		}
	}
}

function loadGroupProducts(id,grp) {
	if (id == 0) return;
	$.ajax({
		url: "/modit/ajax/getProducts/members",
		data: {m:id,g:grp},
		context: {m:id,g:grp},
		success:function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				$("#tabs-5").html(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function loadFedexProducts(id,grp) {
	if (id == 0) return;
	$.ajax({
		url: "/modit/ajax/getFedEx/members",
		data: {m:id,g:grp},
		context: {m:id,g:grp},
		success:function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				$("#tabs-6").html(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function editProduct(id,mbr,grp) {
	$.ajax({
		url: "/modit/ajax/editProduct/members",
		data: {p:id,g:grp,m:mbr,has_grp:1},
		context: {p:id,g:grp,has_grp:1},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showAltPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	});
}

function editMemberOnlyProduct(id,mbr) {
	$.ajax({
		url: "/modit/ajax/editProduct/members",
		data: {p:id,m:mbr,has_grp:0},
		context: {p:id,m:mbr,has_grp:0},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showAltPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	});
}

function editFedEx(id,mbr,grp) {
	$.ajax({
		url: "/modit/ajax/editFedEx/members",
		data: {p:id,g:grp,m:mbr},
		context: {p:id,g:grp},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showAltPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	});
}

function getOverride(obj) {
	el = $(obj);
	f = el.closest("form");
	p = f.find("input[name=product_id]");
	if (p.length == 0)
		p = f.find("select[name=product_id]")
	$.ajax({
		url: '/modit/ajax/getProductOverride/members',
		data: {
			p_id:p.val(),
			fld: obj.name,
			v:el.val(),
			isgroup: f.find("input[name='isgroup']").val(),
			m_id: f.find("input[name='member_id']").val()
		},
		context: {el:obj},
		success: function(obj, status, xhr) {
			try {
				obj = JSON.parse(obj);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				$(this.el).parent().find("span").html(obj.html);
			}
			catch (err) {
			}
		}
	});
}

function getAllOverrides(el) {
	f = $(el).closest("form");
	getOverride(f.find("input[name='minimum_charge']")[0]);
	getOverride(f.find("input[name='inter_downtown']")[0]);
	getOverride(f.find("input[name='zone_surcharge']")[0]);
	getOverride(f.find("input[name='downtown_surcharge']")[0]);
	getOverride(f.find("input[name='km_mincharge']")[0]);
	getOverride(f.find("input[name='km_maxcharge']")[0]);
	getOverride(f.find("input[name='km_charge']")[0]);
	getOverride(f.find("input[name='out_of_zone_rate']")[0]);
}

function getFedExOverrides(el) {
	f = $(el).closest("form");
	getOverride(f.find("input[name='fedex']")[0]);
}

function showPayments(m_id) {
	var ret_data = $.ajax({
		url: '/modit/ajax/showPayments/members',
		data: {'m_id':m_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		showPopup(obj.html);
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showPopupMessages(obj.messages);
	} catch(err) {
		showPopupMessages(err.message);
	}
}

function paymentDetails(p_id,m_id) {
	var ret_data = $.ajax({
		url: '/modit/ajax/paymentDetails/members',
		data: {'m_id':m_id,'p_id':p_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		showAltPopup(obj.html);
		if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		if (obj.messages)
			showAltPopupMessages(obj.messages);
	} catch(err) {
		showAltPopupMessages(err.message);
	}
}

removeProduct = function( id, m_id, is_group, el ) {
	if (confirm("Remove this product?")) {
		$.ajax({
			url: "/modit/ajax/removeProduct/members",
			data: { id: id, m_id: m_id, is_group: is_group },
			context: { el: el},
			success: function( obj, status, xhr ) {
				try {
					h = JSON.parse(obj);
					if (h.status != "false") {
						var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
					}
					else {
						showPopupError(h.messages);
					}
				}
				catch(err) {
					showPopupError(err.message);
				}
			}
		});
	}
}

editKMRate = function(km_id,opt_id) {
	$.ajax({
		url: "/modit/ajax/kmRatesEdit/members",
		data: {"km_id": km_id, "opt_id": opt_id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#kmRates").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

loadKMRates = function( opt_id ) {
	$.ajax({
		url: "/modit/ajax/kmRates/members",
		data: {"opt_id": opt_id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#prodTabs-2").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

deleteKMRate = function(km_id,opt_id) {
	if (confirm("Delete this record?")) {
		$.ajax({
			url: "/modit/ajax/kmRateDelete/members",
			data: {"km_id" : km_id, "opt_id": opt_id},
			context: { opt_id: opt_id },
			success: function( obj, status, xhr ) {
				try {
					loadKMRates(this.opt_id);
				}
				catch(err) {
				}
			}
		});
	}
}

function editContact( c_id) {
		$.ajax({
			url: "/modit/ajax/editContact/members",
			data: {"c_id" : c_id},
			success: function( obj, status, xhr ) {
				try {
					h = JSON.parse(obj);
					showAltPopup(h.html);
					var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
				}
				catch(err) {
					showPopupError(err.message);
				}
			}
		});
}

function saveContact(frm) {
	$(frm).closest("form").ajaxSubmit({
		success: function ( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				showAltPopup(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	})
}

function removeContact( c_id, m_id ) {
	if (confirm("Delete this contact?")) {
		$.ajax({
			url: "/modit/ajax/deleteContact/members",
			data: { c_id: c_id },
			context: { member_id : m_id },
			success: function( obj, status, xhr ) {
				try {
					loadContacts( this.member_id );
				}
				catch(err) {
					
				}
			}
		})
	}
}

function loadContacts( m_id ) {
	$.ajax({
		url: "/modit/ajax/buildContacts/members",
		data: { member_id : m_id },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#tabs-2a").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

editPCRate = function(pc_id,opt_id) {
	$.ajax({
		url: "/modit/ajax/productByPCEdit/members",
		data: {"pc_id": pc_id, "opt_id": opt_id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#pcRates").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

loadPCRates = function( opt_id ) {
	$.ajax({
		url: "/modit/ajax/productByPC/members",
		data: {"opt_id": opt_id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#prodTabs-3").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

deletePCRate = function(pc_id,opt_id) {
	if (confirm("Delete this record?")) {
		$.ajax({
			url: "/modit/ajax/productByPCDelete/members",
			data: {"pc_id" : pc_id, "opt_id": opt_id},
			context: { opt_id: opt_id },
			success: function( obj, status, xhr ) {
				try {
					loadPCRates(this.opt_id);
				}
				catch(err) {
				}
			}
		});
	}
}

editKMOverride = function(km_id,opt_id,j_id) {
	$.ajax({
		url: "/modit/ajax/editKMOverride/members",
		data: {"km_id": km_id, "opt_id": opt_id, "j_id":j_id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#kmOverrides").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
				showAltPopupError(h.messages);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	});
}

recalcOverride = function(el) {
	var f = $(el).closest("form");
	$(f).ajaxSubmit({
		url: "/modit/ajax/editKMOverride/members",
		data: { "temp":1 },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#kmOverrides").html(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
				showAltPopupError(h.messages);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	});
}

reloadKMRates = function( id, j_id ) {
	$.ajax({
		url: "/modit/ajax/kmRateOverride/members",
		data: {opt_id:id, j_id: j_id },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#prodTabs-4").html(h.html);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	})
}

deleteKMOverride = function( id ) {
	$.ajax({
		url: "/modit/ajax/deleteKMOverride/members",
		data: {opt_id:id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				$("#prodTabs-4").html(h.html);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	})
}

initKMOverrides = function( j_id ) {
	$.ajax({
		url: "/modit/ajax/initKMOverrides/members",
		data: { j_id: j_id },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				showAltPopup(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	})
}

groupKMOverrides = function( j_id, opt_id ) {
	$.ajax({
		url: "/modit/ajax/groupKMOverrides/members",
		data: { j_id: j_id, opt_id: opt_id, groupKMOverrides:1 },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				showAltPopup(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	})
}

groupKMDeleteOverride = function( opt_id ) {
	$.ajax({
		url: "/modit/ajax/groupKMDeleteOverride/members",
		data: { opt_id: opt_id, groupKMDeleteOverride:1 },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				showAltPopup(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	})
}