
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/question',
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
				if (obj.messages != null) showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function moveFolder() {
	id = moveIt('question_folders',this);
	setTimeout(function() {
		loadTree('question',id);
		makeDroppable;
	},100);
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('question');
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
		url: '/modit/ajax/getFolderInfo/question',
		data: {'f_id': id[1]},
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
		url: '/modit/ajax/showPageProperties/question',
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
		url: '/modit/ajax/showPageContent/question',
		data: {'f_id' : cId},
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
				if (obj.messages != null) showError(obj.messages);
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
		url: '/modit/ajax/showSearchForm/question',
		data: {'f_id':id},
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
var fnEditArticle = function(p_id) {
	$.ajax({
		url: '/modit/ajax/addContent/question',
		data: {'p_id': p_id },
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				removeTinyMCE($('#popup textarea'))
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) {
					eval(obj.code);
				}
			} catch(err) {
				var erm = err.message.concat('<br/>',data);
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

var getUrl = function () {
	return pagingUrl;
}

function clearDraggable(idx,el) {
	el.className = el.className.replace('draggable','');
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/question","middleContent");
		});
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function initFolderContent(searchForm) {
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

function checkAdd(f_id) {
	if (f_id <= 0) {
		$('#tabs-2').html('<div class="errorMessage">The Order must be saved first</div>');
		$('#tabs-3').html('<div class="errorMessage">The Order must be saved first</div>');
	}
}

function updateLine(l_id,fldName,frmName) {
	$('#lineEditing input[name=tempEdit]')[0].value=1;
	$('#lineEditing input[name=fldName]')[0].value=fldName;
	formCheck(frmName,'/modit/ajax/editQuestion/question','altPopup');
}

function fnSaveResponse(frmName) {
	formCheck(frmName,'/modit/ajax/editResponse/question','altPopup');
}

function resetArticle(f_id) {
	closeAltPopup();
	editArticle(f_id);
}

function loadFromEdit() {
	altClosePopup();
	var closePopup = function() {
		altClosePopup();
	}
	if ($('#showFolderContent').length > 0) {
		formCheck('showFolderContent','/modit/ajax/showPageContent/question','middleContent');
	}
	else
		if ($('#showFolderContent').length > 0)
			formCheck("searchForm","/modit/ajax/showSearchForm/question","middleContent");
		else
			document.location = '/modit/question';
}

function fnRefreshResponses(p_id) {
	$.ajax({
		url: '/modit/ajax/getResponses/question',
		data: {'p_id':p_id},
		success: function(response,stat,xhr) {
			try {
				var obj = eval('('+response+')');
				$('#tabs-5 table.listing tbody')[0].innerHTML = obj.html;
				setImageViewing();
			}
			catch (err) {
			}
		}
	});
}

function fnDeleteArticle(a_id,j_id) {
	$.ajax({
		url: '/modit/ajax/deleteArticle/question',
		data: {'j_id':j_id,'a_id':a_id},
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

function deleteContent(cId) {
	if (confirm("Are you sure you want to delete this item?")) {
		$.ajax({
			url: '/modit/ajax/deleteContent/question',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
					if (obj.code && obj.code.length > 0) eval(obj.code);
					if (obj.messages && obj.messages.length > 0) showError(obj.messages);
				} catch(err) {
					$("#middleContent")[0].innerHTML = err.message;
				}
			}
		});
	}
}

function show() {
	$.ajax({
		url: '/modit/ajax/moduleStatus/question',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				$("#middleContent")[0].innerHTML = obj.html;
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages && obj.messages.length > 0) showError(obj.messages);
			} catch(err) {
				$("#middleContent")[0].innerHTML = err.message;
			}
		}
	});
}

function fnEditResponse(r_id,q_id) {
	if (q_id == 0) {
		alert('The question must be saved first');
		return;
	}
	ret_data = $.ajax({
		url: '/modit/ajax/editResponse/question',
		data: {'r_id': r_id,'q_id':q_id},
		async: false
	});
	try {
		obj = eval('('+ret_data.responseText+')');
		if (obj.status == 'true' && obj.html && obj.html.length > 0) {
			showAltPopup(obj.html);
		}
		if (obj.messages && obj.messages.length > 0)
			showAltPopupError(obj.messages);
		if (obj.code&& obj.code.length > 0)
			eval(obj.code);
	}
	catch(err) {
		showAltPopupError(err.message);
	}
}

function fnDeleteResponse(r_id,q_id) {
	if (confirm('Are You Sure?')) {
		$.ajax({
			'url':'/modit/ajax/removeResponse/question',
			'data':{'q_id':q_id,'r_id':r_id},
			'success':function(data,textStatus,xhr) {
				try {
					var obj = eval('('+data+')');
					if (obj.messages) showPopupError(obj.messages);
					if (obj.code) eval(obj.code);
				}
				catch(err) {
					showPopupError(err.message);
				}
			}
		});
	}
}

function fnSortableResponses() {
	$( '#tabs-5 table.listing tbody').sortable({
		revert: 'invalid',
		cancel: '.edit a, .delete a',
		helper: fixHelper,
		forceHelperSize: true,
		cursor: 'move',
		placeholder: 'placeholder',
		appendTo: $( '#tabs-5 table.listing tbody'),
		start: function( event, ui) {
			dropped = false;
		},
		update: function( event, ui) {
			if(!dropped) {
				idx = $(ui.item[0]).find("div.ids")[0].innerHTML.split("/");
				curLoc = $(ui.item[0]).index();
				$.ajax({
					'url':'/modit/ajax/resortResponses/question',
					'data': {'r_id':idx[0],'dest':curLoc},
					'success':function(data,textStats,xhr) {
						try {
							var obj = eval('('+data+')');
							if (obj.messages) showPopupError(obj.messages);
							if (obj.code) eval(obj.code);
						}
						catch(err) {
							showPopupError(err.message);
						}
					}
				});
			}
			dropped = false;
		}
	});
}
