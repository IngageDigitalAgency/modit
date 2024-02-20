
function addItem() {
	$.ajax({
		url: '/modit/ajax/showPageProperties/stores',
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
				showPopupMessages(obj.messages);
		}
	});
}

function moveFolder() {
	id = moveIt('store_folders',this);
	setTimeout(function() {
		loadTree('stores',id);
		makeDroppable();
	},100);
}

$(document).ready(function() {
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('stores');
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
		url: '/modit/ajax/getFolderInfo/stores',
		data: {p_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#pageInfo')[0].innerHTML = obj.html;
					
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
			} catch(err) {
				var x = 0;
			}
		}
	});
	loadContent(id[1]);
}

function editContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageProperties/stores',
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
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
			} catch(err) {
				var x = 0;
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/stores',
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
				if (obj.messages && obj.messages.length > 0)
					showError(obj.messages);
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
			url: '/modit/ajax/deleteContent/stores',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages && obj.messages.length > 0)
						showError(obj.messages);
					if (obj.status == "true") {
						document.location = document.location
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
		url: '/modit/ajax/showSearchForm/stores',
		data: {'p_id':id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#searchForm")[0].innerHTML = obj.html;
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
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
var editStore = function(s_id) {
	$.ajax({
		url: '/modit/ajax/addContent/stores',
		data: {'s_id': s_id },
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
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
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

var myDrop = function (obj,event,ui) {
	var li = $( "<li></li>" ).text( ui.draggable.text() )
	li.draggable();
	for(var i = 0; i < ui.draggable.length; i++) {
		li[i].id = ui.draggable[i].id;
		li[i].className = ui.draggable[i].className;
	}
	li.appendTo(obj);
	//ui.draggable.remove();
	$( "select.draggable li" ).sortElements(function(a,b) {
		return a.id > b.id ? 1 : -1;
	});
}

var formCheck_add = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	clearSelect('storeDestFolders');
	clearSelect('storeDestCoupons');
	clearSelect('destProductChainsSelect');
	clearSelect('destProductSelect');
	copyOL('toFolderList','storeDestFolders');
	copyOL('toCouponList','storeDestCoupons');
	copyOL('toProductChainsList','destProductChainsSelect');
	copyOL('toProductList','destProductSelect');
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#storeFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

var myHelper = function(event) {
	alert('test');
	return '<div>drag me</div>';
}

function storeDrop(obj,evt,el,dest) {
	// obj is the destination element
	// evt the event
	// el the object being dropped
	// dest the object type we dragged onto
	clearMessages();
	if (dest == 'tree') {
		destId = $(obj).find("a.icon_folder")[0].id.split("_");
		srcId = el.draggable.find("div.id")[0].innerHTML.split("/");
		if (srcId[1] > 0 && destId[1] > 0) {
			$("#dialog-confirm" ).dialog({
				resizable: false,
				height:140,
				modal: true,
				buttons: {
					"Copy the store": function() {
						$.ajax({
							url: '/modit/ajax/moveStore/stores',
							data: {'src': srcId[1], 'dest': destId[1], 'type':'tree','copy':1},
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
					"Move the store": function() {
						$.ajax({
							url: '/modit/ajax/moveStore/stores',
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
			url: '/modit/ajax/moveStore/stores',
			data: {'src': obj, 'dest': evt, 'type':'store'},
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

function loadActiveFolder() {
	$('#contentTree a.active').each(function (idx,el) {
		active = el.id.split("_");
		loadContent(active[1]);
	});
}

function deleteStore(s_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteStore/stores',
		data: {'j_id':j_id,'s_id':s_id},
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

var deleteCouponChain = function(j_id) {
	if (confirm('Delete the coupon from this chain?')) {
		$.ajax({
			url: '/modit/ajax/deleteCouponChain/stores',
			data: {'j_id': j_id },
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					loadContent($('#middleContent [input[name=folder_id]')[0].value);
					if (obj.messages && obj.messages.length > 0)
						showPopupMessages(obj.messages);
				} catch(err) {
					var erm = err.message.concat('<br/>',data);
					showError(err.message);
				}
			}
		});
	}
	return false;
}

function loadAddress(a_id) {
	var o_id = $('#s_id')[0].value;
	var ret_data = $.ajax({
		url: '/modit/ajax/editAddress/stores',
		data: {'a_id':a_id,'o_id':o_id},
		async: false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		showAltPopup(obj.html);
		if (obj.code && obj.code.length > 0) eval(obj.code);
		if (obj.messages)
			showAltPopupMessages(obj.messages);
	} catch(err) {
		showAltPopupMessages(err.message);
	}
}

function checkAddStore(id) {
	if (id == 0 || id == null) {
		$('#addressTab')[0].innerHTML = '<span class="errorMessage">The store must be saved first</span>';
	} else {
		$('#addressSelector').change(function() {
			loadAddress(this);
		});
	}
}

function deleteAddress(s_id,o_id) {
	if (confirm("Are you sure you want to delete this address?")) {
		$.ajax({
			url: '/modit/ajax/deleteAddress/stores',
			data: {'a_id':a_id,'o_id':o_id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.status == 'true')
						loadAddress(null)
					if (obj.messages)
						showError(obj.messages);
				} catch(err) {
					showError(err.message.concat(' [',data,']'));
				}
			}
		});
	}
}

function loadCouponList_dnu(obj) {
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		if ($('#s_id').length == 0) 
			var s_id = $('#p_id')[0].value;
		else
			var s_id = $('#s_id')[0].value;
		$.ajax({
			url: '/modit/ajax/couponList/stores',
			data: {'s_id':s_id,'f_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					$('#couponsTab div.dragger')[0].innerHTML = obj.html;
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages)
						showPopupMessages(obj.messages);
				} catch(err) {
					showPopupMessages(err.message.concat(' [',data,']'));
				}
			}
		});
	}
	else 
		$('#couponsTab div.dragger')[0].innerHTML = '';
}

var storeCouponDrop = function (obj,event,ui) {
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
	//ui.draggable.remove();
	$( "select.draggable li" ).sortElements(function(a,b) {
		return a.id > b.id ? 1 : -1;
	});
}

function initCouponDrag() {
	$( "ul.byStoreCouponList > li" ).draggable({
		helper: "clone"
	});
	$( "ul.byStoreCouponList" ).droppable({
		drop: function( event, ui ) {
			storeCouponDrop(this,event,ui);
		}
	});
}

function loadProductList_dnu(el) {
	idx = el.options[el.selectedIndex].value;
	$.ajax({
		url: '/modit/ajax/productList/stores',
		data: { 'p_id':idx },
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
				showPopupMessages(obj.messages);
			}
			catch(err) {
				showPopupMessages(err.message);
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

function initSearch() {
	if ($('#search-tabs').length > 0) {
		addSortableDroppable(true);
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/stores","middleContent");
		});
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function loadEventList_dnu(el) {
	idx = el.options[el.selectedIndex].value;
	$.ajax({
		url: '/modit/ajax/eventList/stores',
		data: { 'p_id':idx },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$("#sourceEventContainer")[0].innerHTML = obj.html;
					$( "ul.draggable > li" ).draggable({
						helper: "clone"
					});
					$( "ul.draggable" ).droppable({
						drop: function( event, ui ) {
							productDrop(this,event,ui);
						}
					});
				}
				if (obj.messages && obj.messages.length > 0)
					showPopupMessages(obj.messages);
			}
			catch(err) {
				showPopupMessages(err.message);
			}
		}
	});
}

var formCheck_fldr = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	//clearSelect('storeDestCoupons');
	//copyOL('toCouponList','storeDestCoupons');
	//clearSelect('destProductChainsSelect');
	//copyOL('toProductChainsList','destProductChainsSelect');
	//clearSelect('destProductSelect');
	//copyOL('toProductList','destProductSelect');
	formCheck(frmId,f_url,el);
}

function showSearch() {
	getContent('showSearchForm','stores',$('#middleContent')[0]);
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

function saveAddress(frm,frmUrl) {
	var m_data = $('#'.concat(frm)).serialize();
	ret_data = $.ajax({
		url: frmUrl,
		data: m_data,
		type:'POST',
		async: false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		showAltPopup(obj.html);
		if (obj.messages && obj.messages.length > 0) {
			//
			//	edit failed
			//
			showAltPopupError(obj.messages);
			showAltPopup(obj.html);
		}
		if (obj.code && obj.code.length > 0)
			eval(obj.code);
	}
	catch(err) {
		showAltPopupError(err.message);
	}
}

function loadAddresses() {
	var o_id = $('#s_id')[0].value;
	ret_data = $.ajax({
		url: '/modit/ajax/loadAddresses/stores',
		data: {'o_id':o_id},
		async:false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			$('#addressTab')[0].innerHTML = obj.html;
		}
	}
	catch(err) {
		showAltPopupError(err.message);
	}
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
					storeDrop(idx[1],curLoc,null,'news');
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
			storeDrop(this,event,ui,'tree');
		}
	});
}

function resetSearchForm(btn) {
	var frm = $(btn).closest('table');
	frm.find('input').each(function (idx,el) {
		if (el.name != 'submit')
			el.value = '';
	});
	frm.find('select').each(function (idx,el) {
		el.selectedIndex=-1;
	});
}