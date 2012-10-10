jQuery(document).ready(function($) {
	$('div.date').each(function() {
		$(this).children('select').last().after('<input class="datepicker" type="hidden"/>');
	});
	$('div.datetime').each(function() {
		$(this).children('select').last().after('<input class="datepicker" type="hidden"/>');
	});
	$('.datepicker').datepicker({
		dateFormat: 'yy-mm-dd',
		buttonImage: '/img/calendar.png',
		buttonImageOnly: true,
		duration: '',
		showOn: 'button',
		onSelect: function(sel_date) {
			var newDate = sel_date.split('-');

			$(this).siblings('select').each(function(){
				id = $(this).attr('id');
				if (id.substring(id.length-3, id.length)=='Day' && $(this).val() != newDate[2]){
					$(this).val(newDate[2]);
					$(this).change();
				}
				else if (id.substring(id.length-5, id.length)=='Month' && $(this).val() != newDate[1]){
					$(this).val(newDate[1]);
					$(this).change();
				}
				else if (id.substring(id.length-4, id.length)=='Year' && $(this).val() != newDate[0]){
					$(this).val(newDate[0]);
					$(this).change();
				}
			});
		},
		beforeShow: function() {
			var year = '';
			var month = '';
			var day = '';
			var id = '';
			$(this).siblings('select').each(function() {
				id = $(this).attr('id');
				if (id.substring(id.length-3, id.length)=='Day') day = $(this).val();
				else if (id.substring(id.length-5, id.length)=='Month') month = $(this).val();
				else if (id.substring(id.length-4, id.length)=='Year') year = $(this).val();
			});
			$(this).val(year+'-'+month+'-'+day);
			return {};
		}
	});
});
