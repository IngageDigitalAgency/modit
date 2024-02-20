$(document).ready(function() {
	html = getContent('header',null,$('#header > div.inner')[0]);
	html = getContent('mainNav',null,$('#mainNav > div.inner')[0]);
	initTinyMCE();
});


function getDemo(el,lnk) {
	clearMessages();
	$('#contentTree .ui-accordion a').removeClass('active');
	$(el).addClass('active');
	$.ajax({
		url: '/modit/ajax/getDemographics/analytics',
		data: {'type': lnk},
		success: function(data,textStatus,jqXHR) {
			try {
				obj = eval('('+data+')');
				if (obj.status == 'true')
					$('#demographics')[0].innerHTML = obj.html;
				if (obj.code)
					eval(obj.code);
				if (obj.messages)
					showError(obj.messages);
			}
			catch(err) {
				showError(err.message);
			}
		}
	})
}

function initContent(visits,views) {
	if ($('#accordion').length > 0) {
		$('#accordion').accordion({autoHeight:false});

		$('#analytics').css({
			height: '300px',
			width: '900px'
		});

		$.plot($('#analytics'), [{ label: 'Visits', data: visits },{ label: 'Page views', data: views }], {
			lines: { show: true },
			points: { show: true },
			grid: { hoverable: true, backgroundColor: '#fffaff' },
			series: {
				lines: { show: true, lineWidth: 1 },
				shadowSize: 0
			},
			xaxis: { mode: "time" },
			yaxis: { min: 0},
			selection: { mode: "x" }
		});
		
		function showTooltip(x, y, contents) {
			$('<div id="tooltip">' + contents + '</div>').css( {
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				border: '1px solid #fdd',
				padding: '2px',
				'background-color': '#fee',
				opacity: 0.80
			}).appendTo("body").fadeIn(200);
		}
	 
		var previousPoint = null;
		
		$("#analytics").bind("plothover", function (event, pos, item) {
			$("#x").text(pos.x.toFixed(2));
			$("#y").text(pos.y.toFixed(2));
	 
				if (item) {
					if (previousPoint != item.dataIndex) {
						previousPoint = item.dataIndex;
						
						$("#tooltip").remove();
						var x = item.datapoint[0],
							y = item.datapoint[1];
						
						showTooltip(item.pageX, item.pageY,
									item.series.label + " : " + y);
					}
				}
				else {
					$("#tooltip").remove();
					previousPoint = null;            
				}
		});

	}
	else {
		$(document).ready(function() {
			initContent(visits,views);
		});
	}
}

function byMonth(dir) {
	$('#analyticsForm input[name=movedirection]')[0].value = dir;
	formCheck('analyticsForm','/modit/ajax/getAnalytics/analytics','middleContent');
}
