function tableReorder(table) {
	var row = 0;
	var position = 0;
	$('tr', table).each(function () {
		// Only non-header rows get handled
		if ($('th:first', $(this)).length == 0) {
			// Update the sort field with the new counter.
			$('input[id$="Sort"]', $(this)).val(++position);

			// Remove current row class and add the correct one
			$(this).removeClass('altrow').addClass( ++row % 2 == 0 ? '' : 'altrow');
		}
	});
}

function addQuestion() {
	$('#AddQuestion').val('');
	$('#AddQuestionDiv').dialog('open');
	$('#AddQuestion').focus();
	return false;
}

function addQuestionFinish(url, data, index) {
    var type = 'GET';

    var ajax = {
        type: type,
        url: url + '/' + data[1] + '/' + index,
        success: function(row){
			$('#Questions > tbody:first').append(row);
			tableReorder($('#Questions'));
        },
        error: function(message){
            alert(message.responseText);
        }
    };

    $.ajax(ajax);
}

function addAnswer(url, id, index) {
    var type = 'GET';

    var ajax = {
        type: type,
        url: url + '/' + id + '/' + index,
        success: function(row){
			$('#Answers > tbody:first').append(row);
			tableReorder($('#Answers'));
        },
        error: function(message){
            alert(message.responseText);
        }
    };

    $.ajax(ajax);
	return false;
}
