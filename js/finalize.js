function cleanDatePicker() {
	if ($.datepicker != null) {
		var old_fn = $.datepicker._updateDatepicker;
		$.datepicker._updateDatepicker = function(inst) {
			old_fn.call(this, inst);
			var buttonPane = $(this).datepicker("widget").find(".ui-datepicker-buttonpane");
			$("<button type='button' class='ui-datepicker-clean ui-state-default ui-priority-primary ui-corner-all'>Clear</button>").appendTo(buttonPane).click(function(ev) {
				$.datepicker._clearDate(inst.input);
			});
		}
	}
}

function initDateFields() {
	if ($.datepicker != null) {
		$('.def_field_datepicker').datepicker({
			showButtonPanel:true,
			changeMonth: true,
			changeYear:true,
			beforeShow: function() {
				$('#ui-datepicker-div').maxZIndex(); 
			}
		});
		$('.def_field_datetimepicker').datepicker({
			showButtonPanel:true,
			changeMonth: true,
			changeYear:true
		});
	}
}

initDateFields();
removeDependants();

step2Check = function() {
	$.ajax({
		url: "/ajax/render",
		data: {t_id:32},
		success: function( obj, status, xhr ) {
			try {
				obj = eval("("+obj+")");
				$("#to-step-2").replaceWith(obj.html);
			}
			catch(err) {
			}
		}
	});
}

togglePackages = function(el) {
	$("table.toggle").toggleClass("hidden");
	$("#my-cart .fa-stack-1x").toggleClass("fa-minus fa-plus");
}

scrollToItem = function(el) {
	$('html, body').animate({
		scrollTop: $(el).offset().top
	}, 1000);
}
