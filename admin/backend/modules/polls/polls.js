
function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/polls',
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
	id = moveIt('poll_folders',this);
	setTimeout(function() {
		loadTree('polls',id);
		fnMakeDroppable();
	},100);
}

$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	loadTree('polls');
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
		url: '/modit/ajax/getFolderInfo/polls',
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
		url: '/modit/ajax/showPageProperties/polls',
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
		url: '/modit/ajax/showPageContent/polls',
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
		url: '/modit/ajax/showSearchForm/polls',
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
		url: '/modit/ajax/addContent/polls',
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
			formCheck("searchForm","/modit/ajax/showSearchForm/polls","middleContent");
		});
		addSortableDroppable(true);
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function initFolderContent(searchForm) {
	fnMakeDroppable();
	fnShowComparison(0);
}

function checkAdd(f_id) {
	if (f_id <= 0) {
		$('#tabs-2').html('<div class="errorMessage">The Order must be saved first</div>');
		$('#tabs-3').html('<div class="errorMessage">The Order must be saved first</div>');
	}
}

function fnEditQuestion(q_id,p_id) {
	if (p_id == 0) {
		alert('The poll must be saved first');
		return;
	}
	ret_data = $.ajax({
		url: '/modit/ajax/editQuestion/polls',
		data: {'q_id': q_id,'p_id':p_id},
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

function updateLine(l_id,fldName,frmName) {
	$('#lineEditing input[name=tempEdit]')[0].value=1;
	$('#lineEditing input[name=fldName]')[0].value=fldName;
	formCheck(frmName,'/modit/ajax/editQuestion/polls','altPopup');
}

function fnSaveQuestion(frmName) {
	formCheck(frmName,'/modit/ajax/editQuestion/polls','altPopup');
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
		formCheck('showFolderContent','/modit/ajax/showPageContent/polls','middleContent');
	}
	else
		if ($('#showFolderContent').length > 0)
			formCheck("searchForm","/modit/ajax/showSearchForm/polls","middleContent");
		else
			document.location = '/modit/polls';
}

function fnRefreshQuestions(p_id) {
	$.ajax({
		url: '/modit/ajax/getQuestions/polls',
		data: {'p_id':p_id},
		success: function(response,stat,xhr) {
			try {
				var obj = eval('('+response+')');
				$('#tabs-4 table.listing tbody')[0].innerHTML = obj.html;
			}
			catch (err) {
			}
		}
	});
}

function fnDeleteArticle(a_id) {
	if (confirm("Are you sure?")) {
		$.ajax({
			url: '/modit/ajax/deleteArticle/polls',
			data: {'a_id':a_id,'deleteArticle':1},
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
}

function deleteContent(cId) {
	if (confirm("Are you sure you want to delete this folder?")) {
		$.ajax({
			url: '/modit/ajax/deleteContent/polls',
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
		url: '/modit/ajax/moduleStatus/polls',
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

function fnSetContact() {
	var b = $('#tabs-5 input[name=contact_info]')[0].checked;
	$('#tabs-5 input[type=checkbox]').each(function(idx,el) {
		if (el.name != 'contact_info') {
			el.disabled = !b;
		}
	});
}

function addSortableDroppable(searchForm) {
	if(searchForm == true) {
		$( "#pollArticleList tbody tr" ).draggable({
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
		$( '#pollArticleList tbody').sortable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: fixHelper,
			forceHelperSize: true,
			cursor: 'move',
			placeholder: 'placeholder',
			appendTo: $( '#pollArticleList'),
			start: function( event, ui) {
				dropped = false;
			},
			update: function( event, ui) {
				if(!dropped) {
					idx = $(ui.item[0]).find("div.id")[0].innerHTML.split("/");
					curLoc = $(ui.item[0]).index();
					pollDrop(idx[1],curLoc,null,'polls');
				}
				dropped = false;
			}
		});
		$("#pollArticleList tbody").disableSelection();
	}
	fnMakeDroppable();
}

function fnMakeDroppable() {
	$('#comparator table').droppable({
		accept: "tr.ui-draggable, .ui-sortable tr",
		hoverClass: "active_drop",
		tolerance:'pointer',
		drop: function( event, ui ) {
			//
			//	get the parent table. if it's #comparisons this is really a sort not a drag
			//
			var p = $(ui.draggable[0]).closest('table');
			if (p[0].id == 'comparisons') {
				curLoc = $('#comparisons tbody tr.placeholder').index();
				tmp = $('#comparisons tbody tr.ui-sortable-helper').index();
				if (tmp < curLoc) {
					curLoc -= 1;
				}
				else {
					tmp -= 1;
				}
				fnResortComparison(tmp,curLoc,null,'polls');
			}
			else {
				compareDrop(this,event,ui,'table');
			}
			dropped = true;
		}
	});
	$('#contentTree ol.ui-sortable li .wrapper').droppable({
		accept: "#pollArticleList tr",
		hoverClass: "active_drop",
		tolerance:'pointer',
		drop: function( event, ui ) {
			dropped = true;
			pollDrop(this,event,ui,'tree');
		}
	});
}

function compareDrop(obj,evt,el,dest) {
	var ids = $(el.draggable[0]).find('.id')[0].innerHTML.split('/');
	$.ajax({
		'url': '/modit/ajax/addComparison/polls',
		'data': {'p_id':ids[0]},
		'success': function(data, status, xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') $('#comparator tbody').html(obj.html);
				if (obj.messages) showError(obj.messages);
				if (obj.code) eval(obj.code);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function pollDrop(obj,evt,el,dest) {
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
					"Copy the poll": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/polls',
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
					"Move the poll": function() {
						$.ajax({
							url: '/modit/ajax/moveArticle/polls',
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
			url: '/modit/ajax/moveArticle/polls',
			data: {'src': obj, 'dest': evt, 'type':'polls'},
			success: function(data,textStatus,jqXHR) {
				try {
					obj = eval('('+data+')');
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

function fnLoadQuestions(obj,p_id) {
	if (obj.selectedIndex <= 0) return;
	var f_id = obj.value;
	$.ajax({
		'url':'/modit/ajax/loadQuestions/polls',
		'data':{'p_id':p_id,'f_id':f_id},
		'success': function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#tabs-4 .picker .questions')[0].innerHTML = obj.html;
					eval(obj.code);
				}
				else showError(obj.messages);
			}catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnQuestionDragger() {
	$("#questionDragger tbody tr" ).draggable({
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
		},
		forceHelperSize: true,
		cursor: 'move',
		placeholder: 'placeholder'
	});
}
function fnQuestionDropper() {
		$('#tabs-4 table.listing').droppable({
		accept: "tr.ui-draggable",
		hoverClass: "active_drop",
		tolerance:'pointer',
		drop: function( event, ui ) {
			dropped = true;
			var ids = $(ui.draggable[0]).find('div.ids')[0].innerHTML.split('/')
			$.ajax({
				'url': '/modit/ajax/dropQuestion/polls',
				'data': {'q_id':ids[0],'p_id':ids[1]},
				'context': {'q_id':ids[0],'p_id':ids[1]},
				'success':function(data,textStatus,xhr) {
					try {
						var obj = eval('('+data+')');
						if (obj.messages) showPopupError(obj.messages);
						if (obj.code) eval(obj.code);
						p = this;
						if (obj.status == 'true') {
							$('#questionDragger tbody div.ids').each(function(idx,el) {
								var tmp = el.innerHTML.split('/');
								if (tmp[0] == p.q_id) {
									$(el).closest('tr').remove();
								}
							})
						}
					}
					catch(err) {
						showPopupError(err.message);
					}
				}
			});
		}
	});
}

function fnSortableQuestions() {
	$( '#tabs-4 table.listing tbody').sortable({
		revert: 'invalid',
		cancel: '.edit a, .delete a',
		helper: fixHelper,
		forceHelperSize: true,
		cursor: 'move',
		placeholder: 'placeholder',
		appendTo: $( '#tabs-4 table.listing tbody'),
		start: function( event, ui) {
			dropped = false;
		},
		update: function( event, ui) {
			if(!dropped) {
				idx = $(ui.item[0]).find("div.ids")[0].innerHTML.split("/");
				curLoc = $(ui.item[0]).index();
				$.ajax({
					'url':'/modit/ajax/resortQuestions/polls',
					'data': {'q_id':idx[0],'dest':curLoc},
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

function fnDeleteQuestion(q_id,p_id) {
	if (confirm('Are You Sure?')) {
		$.ajax({
			'url':'/modit/ajax/removeQuestion/polls',
			'data':{'q_id':q_id,'p_id':p_id},
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

function fnCopyArticle(p_id) {
	$.ajax({
		'url':'/modit/ajax/copyPoll/polls',
		'data':{'p_id':p_id},
		'success':function(data,textStatus,xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') showPopup(obj.html);
				if (obj.messages) showPopupError(obj.messages);
				if (obj.code) eval(obj.code);
			}
			catch(err) {
				showPopupError(err.message);
			}
		}
	});
}

function fnShowResponses(q_id) {
	$.ajax({
		'url':'/modit/ajax/pollResponse/polls',
		'data':{'q_id':q_id},
		'success':function(data,textStatus,xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') showAltPopup(obj.html);
				if (obj.messages) showAltPopupError(obj.messages);
				if (obj.code) eval(obj.code);
			}
			catch(err) {
				showAltPopupError(err.message);
			}
		}
	});
}

function fnRenderChart(obj,locn) {
	var loc = $('#'.concat(locn));
	loc[0].innerHTML = '';
	$(loc).css('height',$('#questionChart input[name=height]')[0].value.concat('px'))
	$(loc).css('width',$('#questionChart input[name=width]')[0].value.concat('px'))
	switch(obj.value) {
		case 'pie':
			fnPieChart(locn);
			break;
		case 'hollow':
			fnDonutChart(locn);
			break;
		case 'bar':
			fnBarChart(locn);
			break;
		default:
	}
}
var chartData = [];
function fnPieChart(locn) {
	var plot1 = jQuery.jqplot (locn, [chartData], {
		seriesDefaults: {
			// Make this a pie chart.
			renderer: jQuery.jqplot.PieRenderer,
			rendererOptions: {
				// Put data labels on the pie slices.
				// By default, labels show the percentage of the slice.
				showDataLabels: true
			}
		},
		legend: { show:true, location: 'e' }
	});
}

function fnDonutChart(locn) {
	var plot2 = jQuery.jqplot (locn, [chartData], {
		seriesDefaults: {
			renderer: jQuery.jqplot.PieRenderer,
			rendererOptions: {
				// Turn off filling of slices.
				fill: false,
				showDataLabels: true,
				// Add a margin to seperate the slices.
				sliceMargin: 4,
				// stroke the slices with a little thicker line.
				lineWidth: 5
			}
		},
		legend: { show:true, location: 'e' }
	});
}

function fnBarChart(locn) {
	$.jqplot(locn, [chartSeries], {
		seriesDefaults:{
			renderer:$.jqplot.BarRenderer,
			rendererOptions: {fillToZero: true}
		},
		axesDefaults: {
			tickRenderer: $.jqplot.CanvasAxisTickRenderer,
			tickOptions: {
				angle: -30
			}
		},
		series:chartLabels,
		legend: {
			show: false
		},
		axes: {
			xaxis: {
				renderer: $.jqplot.CategoryAxisRenderer,
				ticks: chartTicks
			},
			yaxis: {
				pad: 1.05,
				ticks:chartAxisTicks,
				tickOptions: {formatString: '%d'}
			}
		}
	});
}

function fnSaveChart(locn) {
	//window.open($('#'.concat(locn)).canvasToImage());
	var img = $('#'.concat(locn)).jqplotToImage(50, 0); 
	if (img) {
		open(img.toDataURL("image/png"));
	} 
}

$.fn.canvasToImage =
	function() {
		var canvas = $(this).find('canvas');
		var w = canvas[0].width;
		var h = canvas[0].height;
		var newCanvas = $('<canvas id="tempcanvas" />').attr('width',w).attr('height',h)[0];
		var newContext = newCanvas.getContext("2d");
		$(canvas).each(function() {
			newContext.drawImage(this, 0, 0);
		});
		return newCanvas.toDataURL("image/png"); // Base64 encoded data url string
	};
	
$(function() {

  $.fn.jqplotToImage =
  function(x_offset, y_offset) {
    if ($(this).width() == 0 || $(this).height() == 0) {
      return null;
    }
    var newCanvas = document.createElement("canvas");
    newCanvas.width = $(this).outerWidth() + Number(x_offset);
    newCanvas.height = $(this).outerHeight() + Number(y_offset);

    if (!newCanvas.getContext) return null;

    var newContext = newCanvas.getContext("2d");
    newContext.textAlign = 'left';
    newContext.textBaseline = 'top';

    function _jqpToImage(el, x_offset, y_offset) {
      var tagname = el.tagName.toLowerCase();
      var p = $(el).position();
      var css = getComputedStyle(el);
      var left = x_offset + p.left + parseInt(css.marginLeft) + parseInt(css.borderLeftWidth) + parseInt(css.paddingLeft);
      var top = y_offset + p.top + parseInt(css.marginTop) + parseInt(css.borderTopWidth)+ parseInt(css.paddingTop);

      if ((tagname == 'div' || tagname == 'span' || tagname == 'table' || tagname == 'tbody' || tagname=='tr' || tagname == 'td') && !$(el).hasClass('jqplot-highlighter-tooltip')) {
        $(el).children().each(function() {
          _jqpToImage(this, left, top);
        });
        var text = $(el).childText();

        if (text) {
          var metrics = newContext.measureText(text);
          newContext.font = $(el).getComputedFontStyle();
          newContext.fillText(text, left, top);
          // For debugging.
          //newContext.strokeRect(left, top, $(el).width(), $(el).height());
        }
      }
      else if (tagname == 'canvas') {
        newContext.drawImage(el, left, top);
      }
    }
    $(this).children().each(function() {
      _jqpToImage(this, x_offset, y_offset);
    });
    return newCanvas;
  };

  $.fn.css2 = jQuery.fn.css;
  $.fn.css = function() {
    if (arguments.length) return jQuery.fn.css2.apply(this, arguments);
    return window.getComputedStyle(this[0]);
  };

  // Returns font style as abbreviation for "font" property.
  $.fn.getComputedFontStyle = function() {
    var css = this.css();
    var attr = ['font-style', 'font-weight', 'font-size', 'font-family'];
    var style = [];

    for (var i=0 ; i < attr.length; ++i) {
      var attr = String(css[attr[i]]);

      if (attr && attr != 'normal') {
        style.push(attr);
      }
    }
    return style.join(' ');
  }

  $.fn.childText =
    function() {
      return $(this).contents().filter(function() {
        return this.nodeType == 3; // Node.TEXT_NODE not defined in I7
      }).text();
    };

});

function fnQuestionCSV(q_id) {
	document.location = '/modit/ajax/questionCSV/polls?q_id='.concat(q_id);
}

function fnPollCSV(p_id) {
	document.location = '/modit/ajax/pollCSV/polls?p_id='.concat(p_id);
}

function fnLoadResults(p_id) {
	$.ajax({
		'url':'/modit/ajax/pollResults/polls',
		'data':{'p_id':p_id},
		'success':function(data,textStatus,xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') $('#pollResults')[0].innerHTML = obj.html;
				if (obj.messages) showError(obj.messages);
				if (obj.code) eval(obj.code);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnShowDetails(obj) {
	$(obj).closest('tr').nextUntil('tr.header').removeClass('hidden');
}

function fnShowOther(q_id) {
	$.ajax({
		'url':'/modit/ajax/showOther/polls',
		'data':{'q_id':q_id},
		'success':function(data,textStatus,xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') showPopup(obj.html);
				if (obj.messages) showPopupError(obj.messages);
				if (obj.code) eval(obj.code);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnRemoveComparison(id) {
	$.ajax({
		'url': '/modit/ajax/removeComparison/polls',
		'data': {'p_id':id},
		'success': function(data,status,xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') $('#comparator tbody').html(obj.html);
				if (obj.code) eval(obj.code)
				if (obj.messages) showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnShowComparison(id) {
	$.ajax({
		'url': '/modit/ajax/addComparison/polls',
		'data': {'p_id':id},
		'success': function(data,status,xhr) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') $('#comparator tbody').html(obj.html);
				if (obj.code) eval(obj.code)
				if (obj.messages) showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnComparisonSortable() {
	$('#comparisons tbody').sortable({
		revert: 'invalid',
		cancel: '.edit a, .delete a',
		helper: fixHelper,
		forceHelperSize: true,
		cursor: 'move',
		placeholder: 'placeholder',
		appendTo: $( '#comparator'),
		start: function( event, ui) {
			dropped = false;
		},
		update: function( event, ui) {
			if(!dropped) {
				alert('sorting');
				idx = $(ui.item[0]).find("div.id")[0].innerHTML.split("/");
				curLoc = $(ui.item[0]).index();
				fnResortComparison(idx[1],curLoc,null,'polls');
			}
			dropped = false;
		}
	});
	//$("#comparisons tbody").disableSelection();
}

function fnResortComparison(obj,evt,el,dest) {
	$.ajax({
		url: '/modit/ajax/resortComparison/polls',
		data: {'src': obj, 'dest': evt, 'type':'polls'},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status != 'true') {
					if (obj.messages) showError(obj.messages);
					if (obj.code) eval(obj.code);
				}
			}catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnComparePolls(obj) {
	$.ajax({
		url: '/modit/ajax/comparePolls/polls',
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.messages) showError(obj.messages);
				if (obj.status == 'true') {
					if (obj.code) eval(obj.code);
					$('#pollResults').html(obj.html);
				}
			}catch(err) {
				showError(err.message);
			}
		}
	});
	
}

function fnDetails(p_id,p_state) {
	$.ajax({
		url: '/modit/ajax/responseDetails/polls',
		data: {'p_id':p_id,'p_state':p_state},
		success: function( data, status, xhr ) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#foldertabs').html(obj.html);
					eval(obj.code);
				}
				showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnResponseDetails(r_id) {
	if (r_id == null || r_id <= 0) return;
	$.ajax({
		url: '/modit/ajax/responseDetailsView/polls',
		data: {'r_id':r_id},
		success: function( data, status, xhr ) {
			try {
				var obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#pollResults').html(obj.html);
					eval(obj.code);
				}
				showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function fnResponseCSV(obj,action) {
	var frm = $(obj).closest('form');
	frm.find('input[name=csv]')[0].value=1;
	frm[0].action=action;
	frm[0].onsubmit = null;
	frm[0].submit();
}