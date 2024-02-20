<script type='text/javascript' src='/admin/jquery-ui/jquery-ui.js'></script>
<script type='text/javascript' src='/admin/tinymce/tinymce.min.js'></script>

<script type="text/javascript">
//<![CDATA[ 
rootpath = "<!@@SITE_ROOT@@!>";
<!if(array_key_exists('edit',$_REQUEST) && $_REQUEST['edit'] == 'edit')!>
$("<link/>", {
   rel: "stylesheet",
   type: "text/css",
   href: "/admin/css/editing.css"
}).appendTo("head");
<!fi!>
function highlightModule(el,event) {
	var t = $(el);
	var c = t.find('div.moduleHighlight');
	c.css('width',t.width()-2);
	c.css('height',t.height()-2);
	c.css('display','block');
}

function removeHighlight(el,event) {
	var t = $(el);
	var c = t.find('div.moduleHighlight');
	c.css('display','none');
}

$(document).ready(function() {
	$('div.moduleWrapper').bind(
		"mouseenter",
		function(event) {
			highlightModule(this,event);
		}
	);
	$('div.moduleWrapper').bind(
		"mouseleave",
		function(event) {
			removeHighlight(this,event);
		}
	);
	try {
		if (parent.setIframe)
			parent.setIframe();
	}
	catch(err) {
	}
})
function saveEditor(obj,moduleName) {
	tinyMCE.triggerSave();
	var f = $(obj).closest('form');
	myData = f.serialize();
	a_data = $.ajax({
		url: '/render/ajax/changeContent',
		async:false,
		data:myData,
		type:'post'
	});
	tinyMCE.activeEditor.remove();
	rslt = eval('('+a_data.responseText+')');
	//
	//	now we try to get the corresponding page element & refresh the new module in it
	//
	if (rslt.status == 'true') {
		if ($(f).find('input[name=pageContent]')[0].value == 1)
			$('#tinymce_content').find('.editorContent').html(rslt.html);
		else
			$('#module_'.concat(moduleName,' .editorContent')).html(rslt.html);
		$('#module_content div.moduleWrapper').bind(
			"mouseenter",
			function(event) {
				highlightModule(this,event);
			}
		);
		$('#module_content div.moduleWrapper').bind(
			"mouseleave",
			function(event) {
				removeHighlight(this,event);
			}
		);
		if (rslt.code && rslt.code.length > 0) eval(rslt.code);
		closePopup();
	}
}

function loadEditor(p_id,moduleName,modPageId,pageContent,pageType,obj) {
	clearPopupErrors();
	a_data = $.ajax({
		url:'/render/ajax/changeContent',
		async:false,
		data: {'page_id':p_id,'module_name':moduleName,'id':modPageId,'pageContent':pageContent,'page_type':pageType},
		type:'POST'
	})
	try {
		rslt = eval('('+a_data.responseText+')');
		if (pageContent)
			var pos = $('#tinymce_content').offset();
		else
			var pos = $('#module_'.concat(moduleName,'.moduleWrapper')).offset();
		$('#moduleEditingPopupOverlay').css('top',pos.top);
		$('#moduleEditingPopupOverlay').css('left',pos.left);
		showPopup(rslt.html);
		eval(rslt.code);
	} catch(err) {
		alert(err.message);
	}
}

function loadCMSModule(obj,m_id) {
	clearPopupErrors();
	var f = $(obj).closest('form');
	var p = f.parent();
	f.find('input[name=reset]')[0].value = 0;
	myData = f.serialize();
	a_data = $.ajax({
		url:'/render/ajax/changeModule',
		async:false,
		data: myData,
		method:'post'
	})
	try {
		rslt = eval('('+a_data.responseText+')');
		p[0].innerHTML = rslt.html;
		eval(rslt.code);
	} catch(err) {
		showError(err.message);
	}
}

function saveModule(obj,m_id) {
	var f = $(obj).closest('form');
	f.find('input[name=changeModule]')[0].value = 2;
	f.find('input[name=reset]')[0].value = 0;
	myData = f.serialize();
	a_data = $.ajax({
		url: '/render/ajax/changeModule',
		async:false,
		data:myData,
		method:'post'
	});
	try {
		rslt = eval('('+a_data.responseText+')');
		//
		//	now we try to get the corresponding page element & refresh the new module in it
		//
		if (rslt.status == 'true') {
			showMessage('<div class="alert alert-success">Module Saved</div>');
			var module_name = f.find('input[name=module_name]')[0].value;
			var dest = $('#module_'.concat(module_name));
			a_data = $.ajax({
				url:'/render/ajax/renderModule',
				data:myData,
				async:false
			});
			try {
				rslt = eval('('+a_data.responseText+')');
				dest.find('div.moduleContent')[0].innerHTML = rslt.html;
				if (rslt.code && rslt.code.length > 0) eval(rslt.code);
			}
			catch(err) {
				f.find('span.errorMessage')[0].innerHTML = err.message;
			}
		}
		f.find('span.errorMessage')[0].innerHTML = rslt.messages;
	}
	catch(err) {
		f.find('span.errorMessage')[0].innerHTML = err.message;
	}
}

function showMessage(msg) {
	var el = $('#moduleEditingPopupMessages div.errors')[0];
	el.innerHTML = msg;
	$('#moduleEditingPopupMessages').css('display','block');
}

function clearPopupErrors() {
	showMessage('');
	$('#moduleEditingPopupMessages').css('display','none');
}

function resetModule(obj) {
	var p = $('#moduleEditingPopup');
	var f = $(obj).closest('form');
	f.find('input[name=reset]')[0].value = 1;
	myData = f.serialize();
	a_data = $.ajax({
		url: '/render/ajax/changeModule',
		async:false,
		data:myData,
		method:'post'
	});
	try {
		rslt = eval('('+a_data.responseText+')');
		p[0].innerHTML = rslt.html;
		eval(rslt.code);
	}
	catch(err) {
		f.find('span.errorMessage')[0].innerHTML = err.message;
	}
}

function wrapperToggle(obj) {
	var el = $(obj);
	var p = el.closest("div.moduleWrapper");
	var w = p.find("div.innerWrapper");
	if (w.css("display") == "none") {
		w.css("display","block");
		p.css('height','140px');
		p.css('z-index','201');
	}
	else {
		w.css("display","none");
		p.css('height','auto');
		p.css('z-index','200');
	}
}

function loadCMSPopup(pageType,pageId,moduleName,modPageId,obj) {
	clearPopupErrors();
	a_data = $.ajax({
		url:'/render/ajax/changeModule',
		async:false,
		data: {'page_type':pageType,'page_id':pageId,'module_name':moduleName,'changeModule':0,'reset':0,'id':modPageId},
		method:'post'
	})
	try {
		rslt = eval('('+a_data.responseText+')');
		var pos = $(obj).closest('.moduleWrapper').offset();
		if (pos.left > $(document).width() - 352)	// can't use .width() here as it might not be popuplated yet
			pos.left = $(document).width() - 352;
		if (pos.top > $(document).height() - 252)	// can't use .width() here as it might not be popuplated yet
			pos.top = $(document).height() - 252;
		$('#moduleEditingPopupOverlay').css('top',pos.top);
		$('#moduleEditingPopupOverlay').css('left',pos.left);
		$('#moduleEditingOverlay').css('height',$(document).height());
		showPopup(rslt.html);
		eval(rslt.code);
	} catch(err) {
		alert(err.message);
	}
}

function showPopup(html) {
	$("#moduleEditingOverlay").css("display","block");
	$("#moduleEditingPopupOverlay").css("display","block");
	if (html != null && html.length > 0) {
		$('#moduleEditingPopup')[0].innerHTML = html;
	}
}

function closePopup() {
	$("#moduleEditingPopupOverlay .mce-tinymce").each(function(idx,el) {
		for(var idx in tinymce.editors) {
			if (tinymce.editors[idx].editorContainer.id == el.id)
				tinymce.editors[idx].remove();
		}
	});
	$("#moduleEditingOverlay").css("display","none");
	$("#moduleEditingPopupOverlay").css("display","none");
}

function initTinyMCE(e_id) {
	tinymce.init({
		selector:'textarea.mceAdvanced,textarea.mceSimple',
		content_css:"/css/tinymce.css",
		image_advtab:true,
		importcss_file_filter: "/css/tinymce.css",
		importcss_append:true,
		moxiemanager_image_settings : { 
			view : 'thumbs', 
			extensions : 'jpg,png,gif',
			moxiemanager_rootpath:'/'.concat(rootpath,"/images/")
		},
		moxiemanager_file_settings : { 
			view : 'thumbs', 
			extensions : 'doc,docx,pdf,rtf,swf,mp3,mp4',
			moxiemanager_rootpath : '/'.concat(rootpath,'/files/')
		},
		relative_urls: false,
		remove_script_host:true,
		plugins: [
			"advlist autolink lists link image charmap print preview anchor",
			"searchreplace visualblocks code fullscreen",
			"insertdatetime media table contextmenu paste moxiemanager code template importcss"
		],
		templates: [
			{title: 'Bootstrap Mobile Compatible', description: '14 column - 1 column gutter', url: '/files/mobile.html'}
		],
		toolbar: "insertfile undo redo | styleselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image insertimage | code"
	});
}

function fnSetContentState(obj) {
	var frm = $(obj).closest('form');
	var curState = obj.checked;
	var flds = $(frm).find("input").each(function(idx,el) {
		if (!(el == obj || el.attributes['type'].value == 'hidden'))
			el.disabled = obj.checked;
	});
	var flds = $(frm).find("select").each(function(idx,el) {
		if (el.name != "state") {
			el.disabled = obj.checked;
			if (curState) el.selectedIndex = 0;
		}
	});
}

function addDroppable() {
	$( ".moduleWrapper.draggable" ).draggable({
		revert: 'invalid',
		cancel: '.edit a, .delete a',
		forceHelperSize: true,
		cursor: 'move',
		distance: 10,
		placeholder: 'placeholder'
	});
	makeDroppable();
}

function makeDroppable() {
		$('.moduleWrapper.droppable').droppable({
		accept: ".moduleWrapper.draggable",
		hoverClass: "active_drop",
		tolerance:'pointer',
		distance: 10,
		over: function(event,ui) {
			highlightModule(this,event);
		},
		out: function(event,ui) {
			removeHighlight(this,event);
		},
		drop: function( event, ui ) {
			dropped = true;
			if (!moduleDropped(this,event,ui))
				$(ui.draggable[0]).animate({'top':'0px','left':'0px'});
		}
	});
}

function moduleDropped(srcObj,evt,el) {
	// obj is the destination element
	// evt the event
	// el the object being dropped
	var dest = srcObj.id.split('_');
	var src = el.draggable[0].id.split('_');
	var frm = $('#editorForm');
	var status = false;
	var typ = $('#editorForm').find('input[name=t_id]');
	if (typ.length == 0)
		typId = 'P_'.concat($('#editorForm').find('input[name=p_id]')[0].value);
	else
		typId = 'T_'.concat(typ[0].value);
	$.ajax({
		url: '/render/ajax/dragAnddrop',
		data: {'t_id':typId,'from':src[1],'to':dest[1]},
		async:true,
		success: function(data,textStatus,jqXHR) {
			var obj = JSON.parse(data);
			if (obj.status == 'true') {
				if (typ.length == 0) {
					$('#module_'.concat(dest[1],' .moduleContent')).html(
						$('#module_'.concat(src[1],' .moduleContent')).html()
					);
					$(el.draggable[0]).find('.moduleContent').html('&nbsp;');
					$(el.draggable[0]).css('top','0px').css('left','0px');
				}
				else {
					$(srcObj).find('.moduleContent').html(obj.html);
					$(el.draggable[0]).find('.moduleContent').html('&nbsp;').css('top','0px').css('left','0px');
				}
				$(srcObj).closest('.moduleWrapper').removeClass('ui-droppable').removeClass('droppable').addClass('draggable');
				$(el.draggable[0]).closest('.moduleWrapper').removeClass('ui-draggable').removeClass('draggable').addClass('droppable');
				addDroppable();
			}
			var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
		}
	});
	return status;
}

$(document).ready(function() {
	addDroppable();
	if (window.self != window.top)
		if (window.top.setIframe != null) window.top.setIframe();
});
//]]> 
</script> 
