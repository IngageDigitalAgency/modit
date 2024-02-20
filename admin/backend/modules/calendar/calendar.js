
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/calendar',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'));
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) eval(obj.code);
				}
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function moveFolder() {
	id = moveIt('members_folders',this);
	setTimeout(function() {loadTree('calendar',id)},100);
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('calendar');
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
		url: '/modit/ajax/getFolderInfo/calendar',
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
		url: '/modit/ajax/showPageProperties/calendar',
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
				showError(err.message);
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/calendar',
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
			url: '/modit/ajax/deleteContent/calendar',
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
		url: '/modit/ajax/showSearchForm/calendar',
		data: {'p_id':id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#searchForm")[0].innerHTML = obj.html;
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				$("#searchForm")[0].innerHTML = err.message;
			}
		}
	});
}
//
//	browser seems to have an issue with function addContent() after it is called 1 time - still a problem
//
var editArticle = function(p_id) {
	$.ajax({
		url: '/modit/ajax/addContent/calendar',
		data: {'p_id': p_id },
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
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#calendarFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function calendarDrop(obj,evt,el,dest) {
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
					"Copy the event": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/calendar',
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
									showError(err.message);
								}
							}
						});
						$( this ).dialog( "close" );
					},
					"Move the event": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/calendar',
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
			url: '/modit/ajax/moveArticle/calendar',
			data: {'src': obj, 'dest': evt, 'type':'event'},
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
					showError(err.message);
				}
			}
		});
	}
}

function loadActiveFolder() {
	var a = $('#contentTree a.active');
	if (a.length == 0) {
		getContent('moduleStatus','calendar','#middleContent');
	}
	else {
		active = a[0].id.split("_");
		loadContent(active[1]);
	}
}

function deleteArticle(e_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteArticle/calendar',
		data: {'e_id':e_id,'j_id':j_id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
			if (obj.status == 'true') showPopup(obj.html);
			if (obj.code && obj.code.length > 0) eval(obj.code);
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

function loadCalendarList(obj) {
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var p_id = $('#p_id')[0].value;
		$.ajax({
			url: '/modit/ajax/calendarList/calendar',
			data: {'p_id':p_id,'f_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					$('#assemblyTab div.dragger')[0].innerHTML = obj.html;
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages)
						showPopupMessages(obj.messages);
				} catch(err) {
					showError(err.message);
				}
			}
		});
	}
	else 
		$('#assemblyTab div.dragger')[0].innerHTML = '';
}

function initCalendarDrag() {
	$( "ul.byCalendarList > li" ).draggable({
		helper: "clone"
	});
	$( "table.byCalendarList" ).droppable({
		drop: function( event, ui ) {
			assemblyDrop(this,event,ui);
		}
	});
}

function clearDraggable(idx,el) {
	el.className = el.className.replace('draggable','');
}


function loadCouponList_dnu(obj,p_id) {
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		if (p_id == null)
			p_id = $('#p_id')[0].value;
		$.ajax({
			url: '/modit/ajax/couponList/calendar',
			data: {'p_id':p_id,'f_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					$('#couponsTab div.dragger')[0].innerHTML = obj.html;
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages)
						showPopupMessages(obj.messages);
				} catch(err) {
					showError(err.message);
				}
			}
		});
	}
	else 
		$('#couponsTab div.dragger')[0].innerHTML = '';
}

var eventCouponDrop = function (obj,event,ui) {
	var li = $( "<li></li>" ).text( ui.draggable.text() )
	li.draggable();
	var idList = '';
	for(var i = 0; i < ui.draggable.length; i++) {
		li[i].id = ui.draggable[i].id;
		li[i].className = ui.draggable[i].className;
		idList = idList.concat(',',li[i].id);
	}
	y = 0;
	var bDupe = false;
	for(var j = 0; j < obj.childNodes.length; j++) {
		y += 1;
		bDupe = bDupe || idList.indexOf(obj.childNodes[j].id) >= 0;
	}
	if (!bDupe)
		li.appendTo(obj);
	ui.draggable.remove();
	$( "select.draggable li" ).sortElements(function(a,b) {
		return a.id > b.id ? 1 : -1;
	});
}

function initCouponDrag() {
	$( "ul.byEventCouponList > li" ).draggable({
		helper: "clone"
	});
	$( "ul.byEventCouponList" ).droppable({
		drop: function( event, ui ) {
			eventCouponDrop(this,event,ui);
		}
	});
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/calendar","middleContent");
		});
		addSortableDroppable(true);
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function move(typ) {
	$('#showFolderContent input[name=moveType]')[0].value = typ;
	formCheck("showFolderContent","/modit/ajax/showPageContent/calendar","middleContent");
}

function viewType(typ) {
	$('#showFolderContent input[name=viewType]')[0].value = typ;
	formCheck("showFolderContent","/modit/ajax/showPageContent/calendar","middleContent");
}

function setCalendarHover() {
	$('#monthList td div.day').hover(showEventList,hideEventList);
}

function showEventList(evt) {
	if ($(this).find("div.event").length > 0) {
		$(this).find("div.eventlist").css("display","block");
	}
}

function hideEventList(evt) {
	$(this).find("div.eventlist").css("display","none");
}

function checkAddEvent(id) {
	if (id == 0 || id == null) {
		$('#addressTab')[0].innerHTML = '<span class="errorMessage">The event must be saved first</span>';
	} else {
		$('#addressSelector').change(function() {
			loadAddress(this);
		});
		loadAddress(null);
	}
}

function loadAddress_dnu(obj) {
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var o_id = $('#p_id')[0].value;
		$.ajax({
			url: '/modit/ajax/editAddress/calendar',
			data: {'a_id':id,'o_id':o_id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					//$('#addressTab')[0].innerHTML = obj.html;
					showAltPopup(obj.html);
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages)
						showPopupMessages(obj.messages);
				} catch(err) {
					showError(err.message);
				}
			}
		});
	}
	else 
		$('#addressTab').innerHTML = '';
}

function deleteAddress(a_id,o_id) {
	if (confirm("Are you sure you want to delete this address?")) {
		$.ajax({
			url: '/modit/ajax/deleteAddress/calendar',
			data: {'a_id':a_id,'o_id':o_id},
			success: function(data,textStatus,jqXHR) {
				try {
					var obj = eval('('+data+')');
					if (obj.messages) showPopupError(obj.messages);
					if (obj.code) eval(obj.code);
				} catch(err) {
					showError(err.message);
				}
			}
		});
	}
}

function loadProductList_dnu(el) {
	idx = el.options[el.selectedIndex].value;
	$.ajax({
		url: '/modit/ajax/productList/calendar',
		data: { 'f_id':idx },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#sourceProductContainer")[0].innerHTML = obj.html;
					$( "ul.draggable > li" ).draggable({
						helper: "clone"
					});
					$( "ul.draggable" ).droppable({
						drop: function( event, ui ) {
							productDrop(this,event,ui);
						}
					});
				}
				else showPopupMessages(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

var productDrop = function (obj,event,ui) {
	var li = $( "<li></li>" ).text( ui.draggable.text() )
	li.draggable();
	for(var i = 0; i < ui.draggable.length; i++) {
		li[i].id = ui.draggable[i].id;
		li[i].className = ui.draggable[i].className;
	}
	var bFound = false;
	$(obj).children('li').each(function(idx,el) {
		bFound = bFound || (el.id == li[0].id)
	});
	if (!bFound)
		li.appendTo(obj);
	ui.draggable.remove();
}

var formCheck_fldr = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	clearSelect('eventDestCoupons');
	copyOL('toCouponList','eventDestCoupons');
	formCheck(frmId,f_url,el);
}

function loadStoreList_dnu(el) {
	idx = el.options[el.selectedIndex].value;
	$.ajax({
		url: '/modit/ajax/storeList/calendar',
		data: { 'p_id':idx },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#sourceStoreContainer")[0].innerHTML = obj.html;
					$( "ul.draggable > li" ).draggable({
						helper: "clone"
					});
					$( "ul.draggable" ).droppable({
						drop: function( event, ui ) {
							storeDrop(this,event,ui);
						}
					});
				}
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

var storeDrop = function (obj,event,ui) {
	var li = $( "<li></li>" ).text( ui.draggable.text() )
	li.draggable();
	for(var i = 0; i < ui.draggable.length; i++) {
		li[i].id = ui.draggable[i].id;
		li[i].className = ui.draggable[i].className;
	}
	var bFound = false;
	$(obj).children('li').each(function(idx,el) {
		bFound = bFound || (el.id == li[0].id)
	});
	if (!bFound)
		li.appendTo(obj);
	ui.draggable.remove();
}

function myClone() {
	var tr = $(this).closest("tr")[0];
	var tmp = '<table class="draggerClone"><tr>'.concat(tr.innerHTML,'</tr></table>');
	return tmp;
}

function addSortableDroppable(searchForm) {
	var dropped = false;
	//if(searchForm == true) {
	//	$( "#articleList tbody tr" ).draggable({
	//		revert: 'invalid',
	//		cancel: '.edit a, .delete a',
	//		helper: function(event) {
	//			return $('<div class="drag-row"><table></table></div>').find('table').append($(event.target).closest('tr').clone()).end();
	//		},
	//		forceHelperSize: true,
	//		cursor: 'move',
	//		placeholder: 'placeholder'
	//	});
	//} else {
		//
		//	events are not sortable by drag & drop
		//
		$( '#articleList tbody tr').draggable({
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
		$("#articleList tbody").disableSelection();
	//}

	$('#contentTree ol.ui-sortable li').droppable({
		accept: "#articleList tr",
		hoverClass: "active_drop",
		tolerance:'pointer',
		drop: function( event, ui ) {
			dropped = true;
			calendarDrop(this,event,ui,'tree');
		}
	});

	
}

function loadRecurrence() {
	var frmData = {
		'recurrenceForm':1,
		'recurring_type':$('#recurringType')[0].value,
		'recurring_end_date':$('#recurringEndDate')[0].value,
		'recurring_event':$('#recurringEvent')[0].value
	}
	if ($('#recurringFrequency').length > 0)
		frmData['recurring_frequency'] = $('#recurringFrequency')[0].value;
	if ($('#recurringPosition').length > 0)
		frmData['recurring_position'] = $('#recurringPosition')[0].value;
	var result = $.ajax({
		url: '/modit/ajax/recurringEvent/calendar',
		data: frmData,
		type: 'POST',
		async: false
	});
	try {
		obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			$('#recurringForm')[0].innerHTML = obj.html;
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	} catch(err) {
		showPopupError(err);
	}
}

function checkRecurrence() {
	var recurring = $('#recurringEvent')[0].checked;
	$('#recurringForm input').each(function (idx,el) {
		if (el.name != 'recurring_event')
			el.disabled = recurring ? '':'disabled';
	});
	$('#recurringForm select').each(function (idx,el) {
		if (el.name != 'recurring_event')
			el.disabled = recurring ? '':'disabled';
	});
}

function setByPosition() {
	var mth = $('#recurringByPosition');
	if (mth.length > 0) {
		$('#recurringPosition')[0].disabled = mth[0].checked ? '':'disabled';
		$('#recurrencePattern')[0].disabled = mth[0].checked ? '':'disabled';
	}
}

var jsEditAddress = function(a_id,o_id) {
	if (o_id <= 0) {
		alert('The event must be saved first');
		return;
	}
	var result = $.ajax({
		url: '/modit/ajax/editAddress/calendar',
		data: {'o_id':o_id,'a_id':a_id},
		type: 'post',
		async: false
	});
	try {
		obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			showAltPopup(obj.html);
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	} catch(err) {
		showAltPopupError(err.message);
	}
}

function loadAddresses(o_id) {
	var result = $.ajax({
		url: '/modit/ajax/loadAddresses/calendar',
		data: {'o_id':o_id},
		type: 'post',
		async: false
	});
	try {
		obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			$('#addressListing tbody')[0].innerHTML=obj.html;
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	} catch(err) {
		showPopupError(err.message);
	}
}