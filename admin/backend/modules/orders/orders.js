
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/orders',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'));
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				}
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	$('#contentTree:empty').hide()
	initTinyMCE();
	$( "#middleContent div.draggable" ).draggable({
		helper: 'clone'
	});
	$( "#middleContent div.droppable" ).droppable({
		drop: function( event, ui ) {
			ordersDrop(this,event,ui,'article');
		}
	});
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
		url: '/modit/ajax/getFolderInfo/orders',
		data: {'o_id': id[1]},
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
		url: '/modit/ajax/showPageProperties/orders',
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
				if (obj.messages && obj.messages.length > 0) showPopupError(obj.messages);
				if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			} catch(err) {
				showError(err.message);
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/orders',
		data: {'o_id' : cId},
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
			url: '/modit/ajax/deleteContent/orders',
			data: {'o_id':cId},
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

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/orders',
		data: {'o_id':id},
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
var editArticle = function(o_id) {
	$.ajax({
		url: '/modit/ajax/addContent/orders',
		data: {'o_id': o_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = JSON.parse(data);
				//if (obj.status == 'true') {
					removeTinyMCE($('#popup textarea'))
					showPopup(obj.html);
					addTinyMCE($('#popup textarea'));
					if (obj.code && obj.code.length > 0) {
						//var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);						
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
	$('#addContent input[name=tempEdit]')[0].value=0;
	formCheck(frmId,f_url,el);
}

var formCheck_fldr = function (frmId,f_url,el) {
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function deleteArticle(id) {
	if (confirm("Are you sure?")) {
		$.ajax({
			url: '/modit/ajax/deleteArticle/orders',
			data: {'j_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					if (obj.status == 'true')
						loadFromEdit();
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

function loadOrdersList(obj) {
	if (obj == null || obj.selectedIndex > 0) {
		if (obj == null) {
			var id = 0;
		}
		else {
			var id = obj.options[obj.selectedIndex].value;
		}
		var o_id = $('#o_id')[0].value;
		$.ajax({
			url: '/modit/ajax/ordersList/orders',
			data: {'o_id':o_id,'f_id':id},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = JSON.parse(data);
					$('#assemblyTab div.dragger')[0].innerHTML = obj.html;
					if (obj.code && obj.code.length > 0) var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
					if (obj.messages)
						showPopupMessages(obj.messages);
				} catch(err) {
					showPopupMessages(err.message.concat(' [',data,']'));
				}
			}
		});
	}
	else 
		$('#assemblyTab div.dragger')[0].innerHTML = '';
}

function clearDraggable(idx,el) {
	el.className = el.className.replace('draggable','');
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/orders","middleContent");
		});
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function checkAdd(o_id) {
	if (o_id <= 0) {
		$('#tabs-2').html('<div class="errorMessage">The Order must be saved first</div>');
		$('#tabs-3').html('<div class="errorMessage">The Order must be saved first</div>');
	}
}

function lineEdit(l_id,o_id) {
	ret_data = $.ajax({
		url: '/modit/ajax/editLine/orders',
		data: {'l_id': l_id,'order_id':o_id},
		async: false
	});
	try {
		obj = JSON.parse(ret_data.responseText);
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			showAltPopup(obj.html);
		}
		if (obj.messages && obj.messages.length > 0)
			showAltPopupError(obj.messages);
		if (obj.code&& obj.code.length > 0)
			var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
	}
	catch(err) {
		showAltPopupError(err.message);
	}
}

function updateLine(l_id,fldName,frmName) {
	$('#lineEditing input[name=tempEdit]')[0].value=1;
	$('#lineEditing input[name=fldName]')[0].value=fldName;
	formCheck(frmName,'/modit/ajax/editLine/orders','altPopup');
}

function saveLine(frmName) {
	$('#lineEditing input[name=tempEdit]')[0].value=0;
	$('#lineEditing input[name=fldName]')[0].value='';
	$('#lineEditing input[name=value]')[0].disabled='';
	formCheck(frmName,'/modit/ajax/editLine/orders','altPopup');
}

function resetArticle(o_id) {
	closeAltPopup();
	editArticle(o_id);
}

function updateOrder(o_id,fldName,frmName) {
	$('#addContent input[name=tempEdit]')[0].value=1;
	$('#addContent input[name=fldName]')[0].value=fldName;
	formCheck(frmName,'/modit/ajax/addContent/orders','popup');
}

function loadAddress(a_id,o_id) {
	//var o_id = $('#p_id')[0].value;
	var ret_data = $.ajax({
		url: '/modit/ajax/editAddress/orders',
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

function loadAddresses(o_id) {
	if (o_id == null)
		o_id = $('#o_id')[0].value;
	ret_data = $.ajax({
		url: '/modit/ajax/loadAddresses/orders',
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

function loadFromEdit() {
	altClosePopup();
	var closePopup = function() {
		altClosePopup();
	}
	if ($('#search_form').length > 0)
		formCheck("search_form","/modit/ajax/showSearchForm/orders","middleContent");
}

function recurringResult(id) {
	$.ajax({
		url: '/modit/ajax/showRecurring/orders',
		data:{'r_id':id},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showAltPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function deleteAddress(a_id,o_id) {
	if (confirm("Are you sure you want to delete this address?")) {
		$.ajax({
			url: '/modit/ajax/deleteAddress/orders',
			data: {'a_id': a_id, 'o_id': o_id, 'type':'order'},
			success: function(obj,textStatus,jqXHR) {
				try {
					obj = JSON.parse(obj);
					if (obj.status == "true") {
						if ($("#altOverlay").css("display") != "none") {
							closeAltPopup();
						}
						loadAddresses();
					}
				}
				catch(err) {
					showPopupError(err.message);
				}
			}
		});
	}
}

function calcPct(obj) {
	if ($(obj)[0].name.contains("pickup"))
		$("input[name='delivery[percent_of_delivery]']").val(100-$(obj).val());
	else
		$("input[name='pickup[percent_of_delivery]']").val(100-$(obj).val());
}

function recalcFuel(id) {
	$.ajax({
		url: "/modit/ajax/recalcFuel/orders",
		data: { o_id: id },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

function cloneAddress(el) {
	$.ajax({
		url: "/modit/ajax/cloneAddress/orders",
		data: { "a_id":el.value },
		context: {"el":el},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				t = $(h.html).find("table");
				$(this.el).closest("table").replaceWith(t);
			}
			catch(err) {
				showAltPopupError(obj);
			}
		}
	});
}

function recalcFedex(id) {
	showWait();
	$.ajax({
		url: "/modit/ajax/recalcFedex/orders",
		data: { o_id: id },
		success: function( obj, status, xhr ) {
			$("#overlay").css("z-index","");
			$("#overlay").html("");
			try {
				h = JSON.parse(obj);
				if (h.messages.length > 0) showPopupMessages(h.messages);
				if (h.ecomError.length > 0) showPopupMessages(h.ecomError);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

addImport = function(i_id) {
	$.ajax({
		url: "/modit/ajax/addImport/orders",
		data: { i_id: i_id },
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				showPopup(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	})
	showPopup();
}

getAttachment = function(i_id) {
	document.location = `/modit/ajax/getImportAttachment/orders?i_id=${i_id}`;
}

deleteImport = function(i_id) {
	if (confirm("Delete this import?")) {
		$.ajax({
			url: "/modit/ajax/deleteImport/orders",
			data: { i_id: i_id },
			success: function( obj, status, xhr ) {
				try {
					h = JSON.parse(obj);
					var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); $('body').append(objcode);
				}
				catch(err) {
					showPopupError(err.message);
				}
			}
		});
	}
}