function tableReorder(table) {
	var row = 0;
	var position = 0;
	jQuery('tr', table).each(function () {
		// Only non-header rows get handled
		if (jQuery('th:first', jQuery(this)).length == 0) {
			// Update the sort field with the new counter.
			jQuery('input[id$="Sort"]', jQuery(this)).val(++position);

			// Remove current row class and add the correct one
			jQuery(this).removeClass('altrow').addClass( ++row % 2 == 0 ? '' : 'altrow');
		}
	});
}

function addQuestion() {
	jQuery('#AddQuestion').val('');
	jQuery('#AddQuestionDiv').dialog('open');
	jQuery('#AddQuestion').focus();
	return false;
}

function addQuestionFinish(url, data, index) {
    var type = 'GET';

    var ajax = {
        type: type,
        url: url + '/question:' + data[1] + '/' + index,
        success: function(row){
			jQuery('#Questions > tbody:first').append(row);
			tableReorder(jQuery('#Questions'));
        },
        error: function(message){
            alert(message.responseText);
        }
    };

    jQuery.ajax(ajax);
}

function addAnswer(url, index) {
    var type = 'GET';

    var ajax = {
        type: type,
        url: url + '/' + index,
        success: function(row){
			jQuery('#Answers > tbody:first').append(row);
			tableReorder(jQuery('#Answers'));
        },
        error: function(message){
            alert(message.responseText);
        }
    };

    jQuery.ajax(ajax);
	return false;
}
