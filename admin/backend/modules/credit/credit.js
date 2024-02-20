
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/credit',
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

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
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
		url: '/modit/ajax/getFolderInfo/credit',
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
		url: '/modit/ajax/showPageProperties/credit',
		data: {o_id : cId},
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
				showPopupError(err.message);
			}
		}
	});
}

function loadContent(cId) {
	$.ajax({
		url: '/modit/ajax/showPageContent/credit',
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

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/credit',
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
		url: '/modit/ajax/addContent/credit',
		data: {'a_id': a_id,'p_id':p_id },
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
		o.appendTo($("#".concat(frmId, ' #searchDestFolders')));
	});
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

var getUrl = function () {
	return pagingUrl;
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/credit","middleContent");
		});
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function loadFromEdit() {
	if ($('#showFolderContent').length > 0) {
		formCheck('showFolderContent','/modit/ajax/showPageContent/credit','middleContent');
	}
	else {
		formCheck("searchForm","/modit/ajax/showSearchForm/credit","middleContent");
	}
}

function editRecurring(r_id) {
	$.ajax({
		url: '/modit/ajax/editRecurring/credit',
		data: {r_id:r_id},
		success: function( obj, status, xhr ) {
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

function dailyDetails(r_id) {
	$.ajax({
		url: '/modit/ajax/dailyDetails/credit',
		data: {r_id: r_id},
		success: function( obj, status, xhr) {
			try {
				obj = JSON.parse(obj);
				showPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function addSchedule(s_id) {
	$.ajax({
		url: '/modit/ajax/addSchedule/credit',
		data: {'s_id': s_id},
		success: function(obj, status, xhr) {
			try {
				obj = JSON.parse(obj);
				showPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function addExchange(e_id) {
	$.ajax({
		url: '/modit/ajax/addCurrency/credit',
		data: {'e_id': e_id},
		success: function(obj, status, xhr) {
			try {
				obj = JSON.parse(obj);
				showPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function showMember(id) {
	$.ajax({
		url: "/modit/ajax/showOrders/members",
		data: {"o_id":id},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function removeSchedule(s_id) {
		if (confirm("Delete this record?")) {
		$.ajax({
			url: '/modit/ajax/removeSchedule/credit',
			data: {'s_id': s_id,'removeSchedule':1},
			success: function(obj, status, xhr) {
				try {
					obj = JSON.parse(obj);
					showPopup(obj.html);
					var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				}
				catch(err) {
					showPopupError(err.message);
				}
			}
		});
	}
}