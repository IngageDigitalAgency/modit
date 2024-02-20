var stores = Array(), markers = [], map = null, mc = null, geocoder = null, idx, centered = false, openWindow = null;

function addItem() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/showPageProperties/dispatch',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = JSON.parse(data);
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
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	getDrivers();
	initTinyMCE();
	getStatus();
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
		url: '/modit/ajax/getFolderInfo/dispatch',
		data: {p_id: id[1]},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = JSON.parse(data);
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
		url: '/modit/ajax/showPageProperties/dispatch',
		data: {id : cId},
		method: 'post',
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = JSON.parse(data);
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
		url: '/modit/ajax/showPageContent/dispatch',
		data: {p_id : cId},
		method: 'post',
		dataType: "html",
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = JSON.parse(data);
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
			url: '/modit/ajax/deleteContent/dispatch',
			data: {'p_id':cId},
			success: function(data,textStatus,jqXHR) {
				try {
					var obj = JSON.parse(data);
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
		url: '/modit/ajax/showSearchForm/dispatch',
		data: {'p_id':id},
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = JSON.parse(data);
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
var editArticle = function(p_id) {
	$.ajax({
		url: '/modit/ajax/addContent/dispatch',
		data: {'p_id': p_id },
		success: function(data,textStatus,jqXHR) {
			try {
				var obj = JSON.parse(data);
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
	//clearSelect('dispatchDestCoupons');
	//copyOL('toCouponList','dispatchDestCoupons');
	formCheck(frmId,f_url,el);
}

var pagination = function (pnum, url, el, obj) {
	//var p_id = $('#dispatchFolderId')[0].innerHTML
	var frm = getParent(obj,'form');
	$('#'.concat(frm.id,' input[name=pagenum]')).val(pnum);
	formCheck(frm.id,url,el);
}

function dispatchDrop(obj,evt,el,dest) {
	// obj is the destination element
	// evt the event
	// el the object being dropped
	// dest the object type we dragged onto
	clearMessages();
	if (dest == 'tree') {
		destId = $(obj).find("a")[0].id.split("_");
		srcId = el.draggable.find("div.id")[0].innerHTML.split("/");
		if (srcId[0] > 0 && destId[1] > 0) {

			$.ajax({
				url: '/modit/ajax/moveArticle/dispatch',
				data: {'src': srcId[0], 'dest': destId[1], 'type':'tree','move':1},
				success: function(data,textStatus,jqXHR) {
					try {
						var obj = JSON.parse(data);
						if (obj.status != 'true') {
							showError(obj.messages);
						}
						var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
					}catch(err) {
						showError(err.message.concat(' [',data,']'));
					}
				}
			});
		}
	}
	else {
		$.ajax({
			url: '/modit/ajax/moveArticle/dispatch',
			data: {'src': obj, 'dest': evt, 'type':'dispatch'},
			success: function(data,textStatus,jqXHR) {
				try {
					var obj = JSON.parse(data);
					if (obj.status != 'true') {
						showError(obj.messages);
					}
					var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
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
		url: '/modit/ajax/deleteArticle/dispatch',
		data: {'j_id':j_id,'p_id':p_id},
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

function clearDraggable(idx,el) {
	el.className = el.className.replace('draggable','');
}

function initSearch() {
	if ($('#search-tabs').length > 0) {
		$('#pager').change(function() {
			formCheck("searchForm","/modit/ajax/showSearchForm/dispatch","middleContent");
		});
		addSortableDroppable(true);
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function showOrder(o_id) {
	if (o_id > 0) {
		window.open('/modit/orders/showOrder?o_id='.concat(o_id),'_blank');
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
					dispatchDrop(idx[1],curLoc,null,'dispatch');
				}
				dropped = false;
			}
		});
		$("#articleList tbody").disableSelection();
	}
	makeDroppable();
}

function makeDroppable() {
	if ($("td.dragBoth").length > 0) {
		$('#contentTree ol li .wrapper').droppable({
			accept: "td.dragBoth",
			hoverClass: "active_drop",
			tolerance:'pointer',
			drop: function( event, ui ) {
				dropped = true;
				dispatchSameDay(this,event,ui);
			}
		});
	}
	else
		$('#contentTree ol li .wrapper').droppable({
			accept: "#articleList tr, #schedule tr",
			hoverClass: "active_drop",
			tolerance:'pointer',
			drop: function( event, ui ) {
				dropped = true;
				dispatchDrop(this,event,ui,'tree');
			}
		});
}

function getDrivers() {
	$.ajax({
		url: "/modit/ajax/getDrivers/dispatch",
		success: function( obj, status, xhr ) {
			try {
				obj = JSON.parse(obj);
				$("#contentTree").html(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

function reloadContent() {
	if ($("#searchForm").length > 0)
		formCheck("searchForm","/modit/ajax/showSearchForm/dispatch","middleContent");
}

function getStatus() {
	return;
	/*Sonarcloud report - All code should be reachable
	setTimeout(getStatus,60000);
	$.ajax({
		url: "/modit/ajax/getStatus/dispatch",
		success: function( obj, status, xhr ) {
			try {
				obj = JSON.parse(obj);
				$("#status").html(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});*/
}

function loadDriver(id) {
	$.ajax({
		url: "/modit/ajax/getDriver/dispatch",
		data: {d_id: id},
		success: function( obj, status, xhr ) {
			try {
				obj = JSON.parse(obj);
				$("#variableContent").html(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
			}
		}
	});
}

function sortablePackages() {
	$( '#schedule tbody').sortable({
		revert: 'invalid',
		cancel: '.edit a, .delete a',
		helper: fixHelper,
		forceHelperSize: true,
		cursor: 'move',
		placeholder: 'placeholder',
		appendTo: $( '#schedule tbody'),
		start: function( event, ui) {
			dropped = false;
		},
		update: function( event, ui) {
			if(!dropped) {
				idx = $(ui.item[0]).find("div.id").html();
				curLoc = $(ui.item[0]).index();

				//
				//	because we can have a filter applied, cannot use just the position any more
				//	position after the id of the element we drop after
				//
				tr = $(ui.item[0]).parent().find("tr");
				if (curLoc > 0) {
					ndx = $(tr[curLoc-1]).find("div.id").html();
				}
				else {
					ndx = -1;
				}
				//dispatchDrop(idx,ndx,null,'dispatch');
				$("#getDriverForm").find("input[name='resort']").val(1)
				$("#getDriverForm").find("input[name='resortId']").val(idx)
				$("#getDriverForm").find("input[name='resortTo']").val(ndx)
				formCheck("getDriverForm","/modit/ajax/getDriver/dispatch","variableContent");
/*
				$("#getDriverForm").ajaxSubmit({
					success: function( obj, status, xhr ) {
						try {
							h = JSON.parse(obj);
							$("#getDriverForm").html(h.html);
							var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); 
						}
						catch(err) {
						}
					}
				});
*/
			}
			dropped = false;
		}
	});
	$("#articleList tbody").disableSelection();
}

function mapIt(id) {
	$("#optimizeForm")[0].action = "/modit/ajax/mapIt/dispatch";
	$("#optimizeForm").ajaxSubmit({
		success: function( obj, status, xhr ) {
			try {
				obj = JSON.parse(obj);
				$("#map-wrapper").html(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

//
//	For driver route mapping
//
function openMarker(marker, info, event) {
	var w = new google.maps.InfoWindow();
	if (openWindow) openWindow.close();
	openWindow = w;
	w.setContent(info);
	w.setPosition(event.latLng);
	w.open(map);
}

function mapInitialize() {
		var myOptions = {
			zoom: 12,
			center: new google.maps.LatLng(43.7, -79.38),
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map"), myOptions);
		geocoder = new google.maps.Geocoder();
}
function setPt(point, info, image) {
	if (point) {
		var marker = new google.maps.Marker({
			position: point,
			animation: google.maps.Animation.DROP,
			map: map,
			icon: image
		});
		markers.push(marker);
		google.maps.event.addListener(marker,'mouseover',function (event) {
			openMarker(this, info, event);
		});
		google.maps.event.addListener(marker,'mouseout',function (event) {
			this.close();
		});
	}
}

function showAddress(address, info, image, lat, lng) {
	info = info.replace("<br/><br/>","<br/>");
	if (geocoder) {
		if (lat && lng && (lat != 0 && lng != 0)) {
			var e1 = `<a target="_d" href="/modit/dispatch/sameDays?order_id=184676&sameDays=1"><em class="fa fa-truck"></em></a>`;
			var e2 = `<a target = "_o" href="/modit/orders/showOrder?o_id=184676"><em class="fa fa-info-circle"></em></a>`;
			var a = "<a class='directions arrow' target='_new' href='http://maps.google.com/maps?f=d&hl=en&ie=UTF8&daddr=".concat(escape("(".concat(lat,",",lng,")"))).concat("'>Directions</a>");
			info += e1.concat(" ",e2);
			setPt(new google.maps.LatLng(lat,lng), info, image);
		}
		else {
			geocoder.geocode( { 'address': address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					setPt(results[0].geometry.location, info, image);
				}
			});
		}
	}
}

function showInfo(address) {
	info = address.info.replace("<br/><br/>","<br/>");
	if (geocoder) {
		if (address.latitude && address.longitude && (address.latitude != 0 && address.longitude != 0)) {
			if (address.sameDay == 1)
				var e1 = `<a target="_new" href="/modit/dispatch/sameDays?order_id=${address.order_id}&sameDays=1"><em class="fa fa-truck"></em></a>`;
			else
				var e1 = `<a target="_new" href="/modit/dispatch?order_id=${address.order_id}&overnights=1"><em class="fa fa-truck"></em></a>`;
			var e2 = `<a target = "_new" href="/modit/orders/showOrder?o_id=${address.order_id}"><em class="fa fa-info-circle"></em></a>`;
			//var a = "<a class='directions arrow' target='_new' href='http://maps.google.com/maps?f=d&hl=en&ie=UTF8&daddr=".concat(escape("(".concat(lat,",",lng,")"))).concat("'>Directions</a>");
			info += "".concat(address.order_id," ",e1," ",e2);
			setPt(new google.maps.LatLng(address.latitude,address.longitude), info, address.marker);
		}
		else {
			geocoder.geocode( { 'address': address.address}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					setPt(results[0].geometry.location, info, address.marker);
				}
			});
		}
	}
}

getDest = function(dest) {
	if (dest.latitude)
		return new google.maps.LatLng(dest.latitude,dest.longitude);
	else
		return dest.address;
}

function editPkg(id) {
	$.ajax({
		url: "/modit/ajax/editPackage/dispatch",
		data: {p_id:id},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

sameDayDraggable = function() {
		$( "#articleList tbody td.dragBoth" ).draggable({
			revert: 'invalid',
			cancel: '.edit a, .delete a',
			helper: function(event) {
				r1 = $(event.target).closest('tr');
				r2 = $(r1).next();
				var tmp = $('<div class="drag-row"><table></table></div>').find('table').append(r1.clone()).append(r2.clone()).end();
				return tmp;
			},
			forceHelperSize: true,
			cursor: 'move',
			placeholder: 'placeholder',
			start: function( event, ui) {
				dropped = false;
			},
			update: function( event, ui) {
				if(!dropped) {
					idx = $(ui.item[0]).find("div.id")[0].innerHTML.split("/");
					//curLoc = $(ui.item[0]).index();
					//dispatchDrop(idx[1],curLoc,null,'dispatch');
				}
				dropped = false;
			}

		});
}

dispatchSameDay = function(obj,ev,ui) {
	id = ui.draggable[0].innerHTML;
	driver = $(obj).find("a")[0].id.split("_");
	showWait();
	$.ajax({
		url: "/modit/ajax/assignSameday/dispatch",
		method: 'POST',
		data: { o_id: id, d_id: driver[1], assignSameday : 1 },
		success: function(obj,status,xhr) {
			closeWait();
			try {
				obj = JSON.parse(obj);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
				showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

exportRoute = function(obj) {
	ids = Array();
	$("#schedule div.id").each(function(idx,el) {
		ids.push($(el).html());
	});
	$.ajax({
		url: "/modit/dispatch/exportRoute",
		data: {r_id: ids, d_id: $("#getDriverForm input[name=driver_id]").val()},
		success: function( obj, status, xhr) {
			try {
				obj = JSON.parse(obj);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

function move(typ) {
	$('#calendar input[name=moveType]').val(typ);
	$("#calendar").submit();
}

editDate = function(dt,id) {
	$.ajax({
		url: "/modit/ajax/editDate/dispatch",
		data: {dt:dt,id:id},
		success: function(obj,status,xhr) {
			try {
				obj = JSON.parse(obj);
				showPopup(obj.html);
				var objcode = "<script type='text/javascript'>"+obj.code+"</script>"; $('body').append(objcode);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}
var xxx = Array();

mapDriver = function(addresses,route) {
	mapInitialize();
	var mcOptions = {gridSize: 50, maxZoom: 15};
	addresses[0].marker = "/images/map_markers/green.png";
	addresses[addresses.length-1].marker = "/images/map_markers/yellow.png";
	for(idx = 0; idx < addresses.length; idx++) {
		if (addresses[idx].daysToDeliver < 0) addresses[idx].marker = '/images/map_markers/red.png';
		if (addresses[idx].daysToDeliver == 0) addresses[idx].marker = '/images/map_markers/green.png';
		if (addresses[idx].daysToDeliver > 0) addresses[idx].marker = '/images/map_markers/yellow.png';
		//showAddress(addresses[idx].address,addresses[idx].info,addresses[idx].marker,addresses[idx].latitude,addresses[idx].longitude);
		showInfo(addresses[idx]);
	}
	var polyline = Array();
	for(var leg in route) {
		polyline.push(new google.maps.LatLng(route[leg].lat,route[leg].lng));
	}
	var plot = new google.maps.Polyline({
		path: polyline,
		geodesic: true,
		strokeColor:"#ff0000",
		strokeWeight:2
	});
	plot.setMap(map);
}

optimizeRoute = function(driver_id) {
	$("#optimizeForm").ajaxSubmit({
		success: function( obj, status, xhr ) {
			try {
				obj = JSON.parse(obj);
				if (obj.status == "true") {
					showError("<div class='alert alert-success'>The Route has been optimized and reloaded</div>");
					loadDriver(driver_id);
					mapIt(driver_id);
				}
				else {
					showError(obj.messages);
					alert("Optimize Failed");
				}
			}
			catch(err) {
			}
		}
	});
}

planRoute = function(driver_id) {
	$("#optimizeForm input[name='optimize']").val(0);
	$("#optimizeForm").ajaxSubmit({
		success: function( obj, status, xhr ) {
			try {
				obj = JSON.parse(obj);
				if (obj.status == "true") {
					showError("<div class='alert alert-success'>The Route has been calculated and reloaded</div>");
					loadDriver(driver_id);
					mapIt(driver_id);
				}
				else {
					showError(obj.messages);
					alert("Optimize Failed");
				}
			}
			catch(err) {
			}
		}
	});
}

editAck = function(a_id) {
	$.ajax({
		url: "/modit/ajax/editAck/dispatch",
		data: {a_id:a_id},
		success: function( obj, status, xhr ) {
			try {
				h = JSON.parse(obj);
				showPopup(h.html);
				var objcode = "<script type='text/javascript'>"+h.code+"<\/script>"; $('body').append(objcode); 
			}
			catch(err) {
			}
		}
	});
}
