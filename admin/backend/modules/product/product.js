
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/product',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
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
	id = moveIt('product_folders',this);
	setTimeout(function() {
		loadTree('product',id);
		makeDroppable();
	},100);
}

$(document).ready(function() {
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('product');
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
		url: '/modit/ajax/getFolderInfo/product',
		data: {p_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
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
		url: '/modit/ajax/showPageProperties/product',
		data: {id : cId},
		method: 'post',
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'));
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
				}
				if (obj.messages && obj.messages.length > 0) showPopupError(obj.messages);
				if (obj.code && obj.code.length > 0) eval(obj.code);
			} catch(err) {
				showError(err.message);
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/product',
		data: {p_id : cId},
		method: 'post',
		dataType: "html",
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
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
			url: '/modit/ajax/deleteContent/product',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					var obj = eval('('+data+')');
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

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/product',
		data: {'p_id':id},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
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
var editArticle = function(p_id) {
	$.ajax({
		url: '/modit/ajax/addContent/product',
		data: {'p_id': p_id },
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
				//if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'))
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) {
						eval(obj.code);
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

var formCheck_fldr = function (frmId,f_url,el) {
	//
	//	convert the <ul> to <select>
	//
	//clearSelect('productDestCoupons');
	//copyOL('toCouponList','productDestCoupons');
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var p_id = $('#productFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function productDrop(obj,evt,el,dest) {
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
					"Copy the product": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/product',
							data: {'src': srcId[0], 'dest': destId[1], 'type':'tree','copy':1},
							success: function(data,textStatus,jqXHR) {
								try {
									var obj = eval('('+data+')');
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
					"Move the product": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/product',
							data: {'src': srcId[1], 'dest': destId[1], 'type':'tree','move':1},
							success: function(data,textStatus,jqXHR) {
								try {
									var obj = eval('('+data+')');
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
			url: '/modit/ajax/moveArticle/product',
			data: {'src': obj, 'dest': evt, 'type':'product'},
			success: function(data,textStatus,jqXHR) {
				try {
					var obj = eval('('+data+')');
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
	$('#contentTree a.active').each(function (idx,el) {
		active = el.id.split("_");
	});
	loadContent(active[1]);
}

function deleteArticle(p_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteArticle/product',
		data: {'j_id':j_id,'p_id':p_id},
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

function newAssemblyDrop(obj) {
	var sel = $('#'.concat(obj))[0];
	var tbl = $('#assemblyTable');
	for(i = 0; i < sel.options.length; i++) {
		if (sel.options[i].selected) {
			var p_id = sel.options[i].value;
			if ($('#assemblyTable tr#subproduct_'.concat(p_id)).length == 0) {
				if ($('#p_id')[0].value == p_id) {
					alert("A product cannot contain itself");
				} else {
					ret_data = $.ajax({
						url: '/modit/ajax/assemblyRow/product',
						data: {'addRow': p_id},
						async: false
					});
					try {
						var obj = eval('('+ret_data.responseText+')');
						if (obj.status == 'true' && obj.html && obj.html.length > 0) {
							//
							//	we can't append a row directly from html
							//	append to a temp table first [erases tbody]
							//	then append that to our table
							//
							//$('#tempStorage').find('tbody')[0].innerHTML = obj.html;
							//tmp = $('#tempStorage').find('tr');
							$('#assemblyTable').append(obj.html);	//find('tbody').append(tmp);
						}
					}
					catch(err) {
						showPopupError(err.message);
					}
				}
			}
		}
	}
}

function clearDraggable(idx,el) {
	el.className = el.className.replace('draggable','');
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/product","middleContent");
		});
		addSortableDroppable(true);
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function initInventorySearch() {
	if ($('#search-tabs').length > 0) {
		initDateFields();
	}
	else
		$(document).ready(function() {
			initInventorySearch();
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

function addInventory(id) {
	var tbl = $('#inventoryTable');
	ret_data = $.ajax({
		url: '/modit/ajax/inventoryRow/product',
		data: {'addRow': id},
		async: false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			//
			//	we can't append a row directly from html
			//	append to a temp table first [erases tbody]
			//	then append that to our table
			//
			$('#tempStorage').find('tbody')[0].innerHTML = obj.html;
			tmp = $('#tempStorage').find('tr');
			$('#inventoryTable').find('tbody').append(tmp);
		}
	}
	catch(err) {
		showPopupError(err.message);
	}
}

function auditTrail(i_id,el) {
	if (i_id > 0) {
		ret_data = $.ajax({
			url: '/modit/ajax/inventoryAudit/product',
			data: {'i_id': i_id},
			async: false
		});
		try {
			obj = eval('('+ret_data.responseText+')');
			if (el == null) {
				if (obj.status == 'true' && obj.html && obj.html.length > 0) {
					$('#inventoryAudit')[0].innerHTML = obj.html;
				}
				if (obj.messages && obj.messages.length > 0)
					showPopupError(obj.messages);
			}
			else {
				if (obj.status == 'true' && obj.html && obj.html.length > 0) {
					$(el)[0].innerHTML = obj.html;
				}
				if (obj.messages && obj.messages.length > 0)
					showError(obj.messages);
			}
		}
		catch(err) {
			showError(err.message);
		}
	}
}

function showOrder(o_id) {
	if (o_id > 0) {
		window.open('/modit/orders/showOrder?o_id='.concat(o_id),'_blank');
	}
}

function loadInventory(i_id,el) {
	if (i_id > 0) {
		ret_data = $.ajax({
			url: '/modit/ajax/showInventory/product',
			data: {'i_id': i_id},
			async: false
		});
		try {
			obj = eval('('+ret_data.responseText+')');
			if (obj.status == 'true' && obj.html && obj.html.length > 0) {
				$(el)[0].innerHTML = obj.html;
			}
			if (obj.messages && obj.messages.length > 0)
				showPopupError(obj.messages);
		}
		catch(err) {
			showPopupError(err.message);
		}
	}
}

function editInventory(i_id,p_id) {
	ret_data = $.ajax({
		url: '/modit/ajax/editInventory/product',
		data: {'i_id': i_id,'product_id':p_id},
		async: false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			showAltPopup(obj.html);
		}
		if (obj.messages && obj.messages.length > 0) showAltPopupError(obj.messages);
		if (obj.code && obj.code.length > 0) eval(obj.code);
	}
	catch(err) {
		showAltPopupError(err.message);
	}
}

function refreshInventory(is_valid) {
	if (is_valid == 1) {
		closeAltPopup();
		formCheck("showInventory","/modit/ajax/showInventory/product","tabs-8");
	}
}

function addPricingRow(p_id) {
	ret_data = $.ajax({
		url: '/modit/ajax/pricingRow/product',
		data: {'p_id': p_id},
		async: false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			$('#tempStorage').find('tbody')[0].innerHTML = obj.html;
			tmp = $('#tempStorage').find('tr');
			$('#pricingTable').find('tbody').append(tmp);
		}
		if (obj.messages && obj.messages.length > 0)
			showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);
	}
}

function myClone() {
	var tr = $(this).closest("tr")[0];
	var tmp = '<table class="draggerClone"><tr>'.concat(tr.innerHTML,'</tr></table>');
	return tmp;
}

function addSortableDroppable(searchForm) {
	if(searchForm == true) {
		$( "#articleList tbody tr" ).draggable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: function(event) {
				//if ($.browser.msie) {
				//	var tr = $(event.target).closest('tr');
				//	var tmp = tr.clone();
				//	tr.children().each(function(idx,el) {
				//		$(tmp.children()[idx]).width(el.clientWidth);
				//	});
				//}
				//else
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
					productDrop(idx[1],curLoc,null,'product');
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
			productDrop(this,event,ui,'tree');
		}
	});
}

function editOptionRow(o_id,p_id) {
	if (p_id <= 0) {
		alert('The Product must be saved first');
		return;
	}
	var result = $.ajax({
		url: '/modit/ajax/editOption/product',
		data: {'o_id':o_id,'p_id':p_id},
		type: 'post',
		async: false
	});
	try {
		obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			showAltPopup(obj.html);
			initTinyMCE();
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	} catch(err) {
		showAltPopupError(err.message);
	}
}

function editPricingRow(o_id,p_id) {
	if (p_id <= 0) {
		alert('The Product must be saved first');
		return;
	}
	var result = $.ajax({
		url: '/modit/ajax/editPricing/product',
		data: {'o_id':o_id,'p_id':p_id},
		type: 'post',
		async: false
	});
	try {
		obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			showAltPopup(obj.html);
			initTinyMCE();
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	} catch(err) {
		showAltPopupError(err.message);
	}
}

function editRecurringRow(o_id,p_id) {
	if (p_id <= 0) {
		alert('The Product must be saved first');
		return;
	}
	var result = $.ajax({
		url: '/modit/ajax/editRecurring/product',
		data: {'o_id':o_id,'p_id':p_id},
		type: 'post',
		async: false
	});
	try {
		obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			showAltPopup(obj.html);
			initTinyMCE();
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	} catch(err) {
		showAltPopupError(err.message);
	}
}

function loadOptions(o_id) {
	var result = $.ajax({
		url: '/modit/ajax/loadOptions/product',
		data: {'o_id':o_id},
		async:false,
		type:'post'
	});
	try {
		var obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			$("#optionsTable").children('tbody')[0].innerHTML = obj.html;
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showAltPopupError(err.message);		
	}
}

function loadPricing(o_id) {
	var result = $.ajax({
		url: '/modit/ajax/loadPricing/product',
		data: {'o_id':o_id},
		async:false,
		type:'post'
	});
	try {
		var obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			$("#pricingTable").children('tbody')[0].innerHTML = obj.html;
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showAltPopupError(err.message);		
	}
}

function loadRecurring(o_id) {
	var result = $.ajax({
		url: '/modit/ajax/loadRecurring/product',
		data: {'o_id':o_id},
		async:false,
		type:'post'
	});
	try {
		var obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			$("#recurringTab table").children('tbody')[0].innerHTML = obj.html;
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showAltPopupError(err.message);		
	}
}

function deletePricingRow(p_id,o_id) {
	var result = $.ajax({
		url: '/modit/ajax/deletePricing/product',
		data: {'p_id':p_id,'o_id':o_id},
		async:false,
		type:'post'
	});
	try {
		var obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			loadPricing(o_id);
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);		
	}
}

function deleteOptionRow(p_id,o_id) {
	var result = $.ajax({
		url: '/modit/ajax/deleteOption/product',
		data: {'p_id':p_id,'o_id':o_id},
		async:false,
		type:'post'
	});
	try {
		var obj = eval('('+result.responseText+')');
		if (obj.status == 'true') {
			loadOptions(o_id);
			if (obj.code) eval(obj.code);
		}
		if (obj.messages) showPopupError(obj.messages);
	}
	catch(err) {
		showPopupError(err.message);		
	}
}

function recurringSortableDroppable() {
	$('#recurringTab tbody').sortable({
		revert: 'invalid',
		cancel: '.edit a, .delete a',
		helper: fixHelper,
		forceHelperSize: true,
		cursor: 'move',
		placeholder: 'placeholder',
		appendTo: $( '#recurringTab'),
		start: function( event, ui) {
			dropped = false;
		},
		update: function( event, ui) {
			if(!dropped) {
				idx = $(ui.item[0]).find("div.id")[0].innerHTML;
				curLoc = $(ui.item[0]).index();
				recurringDrop(idx,curLoc);
			}
			dropped = false;
		}
	});
	$("#recurringTab tbody").disableSelection();
}

function recurringDrop(obj,evt) {
	// obj is the destination element
	// evt the event
	// dest the object type we dragged onto
	clearMessages();
	$.ajax({
		url: '/modit/ajax/moveRecurring/product',
		data: {'src': obj, 'dest': evt, 'type':'product_recurring'},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = eval('('+data+')');
				if (obj.status != 'true') {
					showPopupError(obj.messages);
				}
				else {
					loadRecurring($('input#p_id').val());
				}
			}catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function sortableOptions() {
		$( '#optionsTable tbody').sortable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: fixHelper,
			forceHelperSize: true,
			cursor: 'move',
			placeholder: 'placeholder',
			appendTo: $( '#optionsTable'),
			start: function( event, ui) {
				dropped = false;
			},
			update: function( event, ui) {
				if(!dropped) {
					idx = ui.item[0].id.split("_");
					curLoc = $(ui.item[0]).index();
					$.ajax({
						url: '/modit/ajax/moveOption/product',
						data: {'src': idx[2], 'dest': curLoc},
						success: function(data,textStatus,jqXHR) {
							try {
								var obj = eval('('+data+')');
								if (obj.status != 'true') {
									showPopupError(obj.messages);
								}
							}catch(err) {
								showPopupError(err.message);
							}
						}
					});
				}
				dropped = false;
			}
		});
		$("#optionsTable tbody").disableSelection();
}

function editComment(id) {
	$.ajax({
		url: "/modit/ajax/editComment/product",
		data: {c_id:id},
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				showAltPopup(obj.html);
				addTinyMCE($('#altPopup textarea'));
				eval(obj.code);
			}
			catch(err) {
			}
		}
	});
}

function deleteComment(id) {
	if (confirm("Delete the comment?")) {
		$.ajax({
			url: "/modit/ajax/deleteComment/product",
			data: {c_id: id},
			success: function(obj, status, xhr) {
				try {
					obj = eval("("+obj+")");
					eval(obj.code);
				}
				catch(err) {
				}
			}
		});
	}
}

function loadComments(pg,id) {
	$.ajax({
		url: '/modit/ajax/comments/product',
		data: {p_id:id,pagenum:pg},
		success: function( obj, status, xhr) {
			try {
				obj = eval("("+obj+")");
				$("#tabs-19").html(obj.html);
				eval(obj.code);
			}
			catch(err) {
			}
		}
	});
}