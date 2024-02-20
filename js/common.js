// JavaScript Document

function loadProvinces(c_id,opts) {
	if (opts == null) 
		opts = {'c_id':c_id};
	else
		opts['c_id'] = c_id;
	retStatus = $.ajax({
		url: '/ajax/loadProvinces',
		data: opts,
		type:'post',
		async:false
	});
	try {
		obj = eval('('+retStatus.responseText+')');
		return obj.html;
	} catch(err) {
	}
}

	function nextMonth(yr,mth) {
		mth += 1;
		if (mth > 12) {
			mth = 1;
			yr += 1;
		}
		document.location = '?month='.concat(mth,'&year=',yr);
	}
	
	function prevMonth(yr,mth) {
		mth -= 1;
		if (mth <= 1) {
			mth = 12;
			yr -= 1;
		}
		document.location = '?month='.concat(mth,'&year=',yr);
	}

// map popup
function map_popup(URL) {
	day = new Date();
	id = day.getTime();
	eval("page" + id + " = window.open(URL, '" + id + "', 'toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=1,width=800,height=600,left = 400,top = 192');");
	return false;
}

$.maxZIndex = $.fn.maxZIndex = function(opt) {
    /// <summary>
    /// Returns the max zOrder in the document (no parameter)
    /// Sets max zOrder by passing a non-zero number
    /// which gets added to the highest zOrder.
    /// </summary>    
    /// <param name="opt" type="object">
    /// inc: increment value, 
    /// group: selector for zIndex elements to find max for
    /// </param>
    /// <returns type="jQuery" />
    var def = { inc: 10, group: "*" };
    $.extend(def, opt);
    var zmax = 0;
    $(def.group).each(function() {
        var cur = parseInt($(this).css('z-index'));
        zmax = cur > zmax ? cur : zmax;
    });
    if (!this.jquery)
        return zmax;

    return this.each(function() {
        zmax += def.inc;
        $(this).css("z-index", zmax);
    });
}

function setText(obj,bFocus) {
	if (bFocus) {
		if (obj.value == obj.attributes["prompt"].value)
			obj.value = "";
	}
	else {
		if (obj.value.length == 0) {
			obj.value = obj.attributes["prompt"].value;
		}
	}
}

function formSubmit(obj) {
	var f = $(obj).closest('form');
	$(f).find('input').each(function(idx,el) {
		if (el.attributes.getNamedItem('prompt')!=null) {
			if (el.value == el.attributes.getNamedItem('prompt').value)
				el.value = "";
		}
	});
	f.submit();
}
function frmSubmit(obj) {
	formSubmit(obj);
}

function setImage(e,obj) {
	var img = $(obj.$items[obj.targetPage]).find('div.image')[0].innerHTML;
	var el = $(obj.$window).closest('div.root').children('div.slider_image');
	el[0].innerHTML = img;
	$(obj.$window).closest('div.root').find('div.info')[0].innerHTML = 'Next Slide ('.concat((obj.targetPage % obj.pages)+1,'/',obj.pages,')');
}

function setTopNav() {
	//
	//	can't use the find() directly - the new links get picked up also
	//
	var lnk = $('div.topnav ul.level_1 li')
	lnk.each(function(idx,el) {
		var nav = $(el).find('a');
		nav.each(function(idx,a) {
			$(el).load(a.href.concat(' #contents'));
		});
	});
}

function setAccordion(p,n) {
	if (p.length > 0) {
		$(p).find('li ul').hide();
		$(p).find(n).click(function(){
			$(this).closest('ul.level_0').children('li').removeClass('show');
			$(this).parent().addClass('show');
			$(this).parent().children('ul').show('fast');
			$(this).parent().siblings().children('ul').hide('fast');
			$(this).parent().siblings().removeClass('selected');
			return false;
		});
	}
}

function removeDependants() {
	$(".optCheck").each(function(idx,el) {
		var v = el.innerHTML;
		if (v.length == 0) {
			$(el).closest('.p_optCheck').css('display','none');
		}
	});
}

function initDateFields() {
	$('.def_field_datepicker').datepicker({
		showButtonPanel:true,
		changeMonth: true,
		changeYear:true,
		yearRange:'-5:+0',
		beforeShow: function() {
			$('#ui-datepicker-div').maxZIndex(); 
		}
	});
}

$.maxZIndex = $.fn.maxZIndex = function(opt) {
    /// <summary>
    /// Returns the max zOrder in the document (no parameter)
    /// Sets max zOrder by passing a non-zero number
    /// which gets added to the highest zOrder.
    /// </summary>    
    /// <param name="opt" type="object">
    /// inc: increment value, 
    /// group: selector for zIndex elements to find max for
    /// </param>
    /// <returns type="jQuery" />
    var def = { inc: 10, group: "*" };
    $.extend(def, opt);
    var zmax = 0;
    $(def.group).each(function() {
        var cur = parseInt($(this).css('z-index'));
        zmax = cur > zmax ? cur : zmax;
    });
    if (!this.jquery)
        return zmax;

    return this.each(function() {
        zmax += def.inc;
        $(this).css("z-index", zmax);
    });
}

function balanceItems(selector,els) {
	var ht = 0;
	if (els == null) els = $(selector);
	$(els).each(function(idx,el) {
		if ($(el).outerHeight() > ht)
			ht = $(el).outerHeight();
	});
	$(els).css('height',ht+'px');
}