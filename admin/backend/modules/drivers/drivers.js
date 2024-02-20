var stores = Array(), markers = [], map = null, mc = null, geocoder = null, idx, centered = false, openWindow = null;

function addDriver() {
	clearMessages();
	$.ajax({
		url: '/modit/ajax/editDriver/drivers',
		data: {'id': 0},
		success: function(data,textStatus,jqXHR) {
			obj = eval('('+data+')');
			if (obj.status == 'true') {
				removeTinyMCE($('#popup textarea'));
				showPopup(obj.html);
				addTinyMCE($('#popup textarea'));
				if (obj.code && obj.code.length > 0) eval(obj.code);
			}
		}
	});
}

initDrivers = function() {
	var dropped = false;
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	$.ajax({
		url: "/modit/ajax/showContentTree/drivers",
		success: function(obj,status,xhr) {
			try {
				obj = eval("("+obj+")");
				$("#contentTree").html(obj.html);
				eval(obj.code);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
	initTinyMCE();
}


function showSchedule(id,el) {
	clearMessages();
	$('#contentTree a.active').each(function (idx,el) {
		$(el).removeClass('active');
	});
	$(el).addClass('active');
	$.ajax({
		url: '/modit/ajax/showSchedule/drivers',
		data: {d_id: id},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true') {
					$('#variableContent').html(obj.html);
				}
				if (obj.code && obj.code.length > 0) eval(obj.code);
				if (obj.messages) showError(obj.messages);
			} catch(err) {
				showError(err.message);
			}
		}
	});
}

function editDriver(cId, md) {
	$.ajax({
		url: '/modit/ajax/editDriver/drivers',
		data: {d_id : cId, md: md},
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
				var x = 0;
			}
		}
	});
}

function loadSearchForm(id) {
	$.ajax({
		url: '/modit/ajax/showSearchForm/drivers',
		data: {'p_id':id},
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

function resetSize(id) {
	var x = $("#".concat(id));
}

var pagination = function (pnum, url, el, obj) {
	//var p_id = $('#driversFolderId')[0].innerHTML
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
			formCheck("searchForm","/modit/ajax/showSearchForm/drivers","middleContent");
		});
		initDateFields();
	}
	else
		$(document).ready(function() {
			initSearch();
		});
}

function moveFolder() {}

editPkg = function(id) {
	$.ajax({
		url: "/modit/ajax/editPackage/drivers",
		data: {p_id: id},
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				showPopup(obj.html);
				eval(obj.code);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

zoneEdit = function(z_id) {
	$.ajax({
		url: "/modit/ajax/editZone/drivers",
		data: { z_id: z_id },
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				showPopup(obj.html);
				eval(obj.code);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

reloadZones = function() {
	$.ajax({
		url: "/modit/drivers/zones",
		success: function( obj, status, xhr ) {
			xxx = $(obj);
			$("#variableContent").replaceWith(xxx.find("#variableContent"));
		}
	});
}

getDrivers = function(id) {
	$.ajax({
		url: "/modit/ajax/zoneDrivers/drivers",
		data: { vehicle_id: id },
		context: { v_id: id },
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				$("#driver_id").html(obj.html);
				getZoneFSA( $("input[name=z_id]").val(), this.v_id );
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

getZoneFSA = function( z_id, v_id ) {
	$.ajax({
		url: "/modit/ajax/zoneFSA/drivers",
		data: { z_id: z_id, v_id: v_id },
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				$("#zone_fsa").html(obj.html);
				$("select[name='fsa[]']").chosen();
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}

editPayment = function(p_id) {
	$.ajax({
		url: "/modit/ajax/editPayment/drivers",
		data: { p_id: p_id },
		success: function(obj,status,xhr) {
			try {
				obj = eval("("+obj+")");
				showPopup(obj.html);
				eval(obj.code);
			}
			catch(err) {
				showError("Error: ".concat(err.message," from ",obj));
			}
		}
	});
}

mapIt = function(d_id) {
	$.ajax({
		url: "/modit/ajax/mapIt/drivers",
		data: {d_id:d_id,"dt":$("#showScheduleForm input[name='']").val()},
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				$("#mapWrapper").html(obj.html);
				if (obj.status == "true") {
					eval(obj.code);
				}
			}
			catch(err) {
			}
		}
	});
}

mapDriver = function(addresses,route) {
	mapInitialize();
	var mcOptions = {gridSize: 50, maxZoom: 15};
	addresses[0].marker = "/images/map_markers/green.png";
	addresses[addresses.length-1].marker = "/images/map_markers/yellow.png";
	for(idx = 0; idx < addresses.length; idx++) {
		showAddress(addresses[idx].address,addresses[idx].info,addresses[idx].marker,addresses[idx].latitude,addresses[idx].longitude);
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
			this.close;
		});
	}
}

function showAddress(address, info, image, lat, lng) {
	info = info.replace("<br/><br/>","<br/>");
	if (geocoder) {
		if (lat && lng && (lat != 0 && lng != 0)) {
			var a = "<a class='directions arrow' target='_new' href='http://maps.google.com/maps?f=d&hl=en&ie=UTF8&daddr=".concat(escape("(".concat(lat,",",lng,")"))).concat("'>Directions</a>");
			info += a;
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

getDest = function(dest) {
	if (dest.latitude)
		return new google.maps.LatLng(dest.latitude,dest.longitude);
	else
		return dest.address;
}

showPayments = function(d_id, el) {
	var flds = {};
	var frm = $(el).closest("form");
	$(frm).find("input").each(function(idx,sel) {
		flds[sel.name] = sel.value;
	});
	$(frm).find("select").each(function(idx,sel) {
		flds[sel.name] = sel.value;
	});
	flds["driver_id"] = d_id;
	$.ajax({
		url: '/modit/ajax/showPayments/drivers',
		data: flds,
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				showPopup(obj.html);
				eval(obj.code);
			}
			catch(err) {
			}
		}
	});
}

editCommission = function(id) {
	$.ajax({
		url: "/modit/ajax/editCommission/drivers",
		data: { c_id: id },
		success: function( obj, status, xhr ) {
			try {
				h = eval("("+obj+")");
				showAltPopup(h.html);
				eval(h.code);
			}
			catch(err) {
			}
		}
	});
}

zoneDelete = function( id ) {
	if (confirm("Delete this Driver zone?")) {
		$.ajax({
			url: "/modit/ajax/zoneDelete/drivers",
			data: { z_id:id },
			success: function( obj, status, xhr ) {
				try {
					h = eval("(" + obj + ")");
					eval(h.code);
				}
				catch(err) {
					showError(err.message);
				}
			}
		});
	}
}

sortby = function( fld, el ) {
	var f = $(el).closest("form");
	var c = $(f).find("input[name='sortby']").val();
	$(f).find("input[name='sortby']").val(fld);
	if (c == fld) {
		var d = $(f).find("input[name='sortorder']").val();
		if (d == "asc")
			$(f).find("input[name='sortorder']").val("desc");
		else
			$(f).find("input[name='sortorder']").val("asc");
	}
	$(f).submit();
}

sendPDF = function(el) {
	var f = $(el).closest("form");
	$(f).ajaxSubmit({
		url: "/modit/ajax/sendPDF/drivers",
		context: {frm : f },
		success: function( obj, status, xhr ) {
			try {
				h = eval("(" + obj + ")");
				$(this.frm).find("#results").html(h.html);
			}
			catch(err) {
				showError(err.message);
			}
		}
	});
}