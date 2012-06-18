// This is modified from http://www.lukedingle.com/javascript/sortable-table-rows-with-jquery-draggable-rows/

(function($) {
	$.fn.tableSort = function(postprocess, options) {
		var defaults = {
		};
		var settings = $.extend({}, defaults, options);

		// Capture the mouse x and y positions (only Y is needed for this task but there's no harm in getting
		// both axis. 
		// Declare global variables so they can be accessed anywhere.
		// The lastX and lastY variables will be used to keep track of which direction the mouse is heading
		// when moving the TR elements
		var mouseX, mouseY, lastX, lastY = 0;

		// IE Doesn't stop selecting text when mousedown returns false we need to check
		// That onselectstart exists and return false if it does -- we won't check if the browser is IE
		// As thy may very well change this at some point
		var need_select_workaround = typeof $(document).attr('onselectstart') != 'undefined';

		// This function captures the x and y positions anytime the mouse moves in the document.
		$().mousemove(function(e) { mouseX = e.pageX; mouseY = e.pageY; });

		this.each(
			function() {
				// store $(this) table element in a variable to allow access inside other contexts
				var table = $(this);

				// The first order of business is to bind a function to the mousedown event 
				// on all TR elements inside the tbody. I am using the jQuery live() function because my content is loaded through
				// ajax. simply use mousedown() if you do not need to load this on dynamic functions
				$('tbody tr .handle', $(this)).mousedown(function (e) {

					// Store the current location Y axis position of the mouse at the time the 
					// mouse button was pushed down. This will determine which direction to move the table row
					lastY = mouseY;

					// store $(this) tr element in a variable to allow faster access in the functions soon to be declared
					var tr = $(this).closest('tr');

					// This is just for flashiness. It fades the TR element out to an opacity of 0.2 while it is being moved.
					tr.find('td').fadeTo('fast', 0.2);

					// jQuery has a fantastic function called mouseenter() which fires when the mouse enters
					// This code fires a function each time the mouse enters over any TR inside the tbody -- except $(this) one
					$('tr', tr.parent() ).not(tr).bind('mouseenter', function(){
						// Check mouse coordinates to see whether to pop this before or after
						// If mouseY has decreased, we are moving UP the page and insert tr before $(this) tr where 
						// $(this) is the tr that is being hovered over. If mouseY has decreased, we insert after
						if (mouseY > lastY) {
								$(this).after(tr);
						} else {
								$(this).before(tr);
						}
						// Store the current location of the mouse for next time a mouseenter event triggers
						lastY = mouseY;
					});

					// Now, bind a function that runs on the very next mouseup event that occurs on the page
					// This checks for a mouse up *anywhere*, not just on table rows so that the function runs even
					// if the mouse is dragged outside of the table.
					$('body').mouseup(function () {
						// Fade the TR element back to full opacity
						tr.find('td').fadeTo('fast', 1);
						// Remove the mouseenter events from the tbody so that the TR element stops being moved
						$('tr', tr.parent()).unbind('mouseenter');
						// Remove this mouseup function until next time
						$('body').unbind('mouseup');

						// Make text selectable for IE again with
						// The workaround for IE based browers
						if (need_select_workaround)
							$(document).unbind('selectstart');

						// Check if postprocess is function
						if ($.isFunction(postprocess)) {
							postprocess(table);
						}
					});

					// This part if important. Preventing the default action and returning false will
					// Stop any text in the table from being highlighted (this can cause problems when dragging elements)
					e.preventDefault();

					// The workaround for IE based browers
					if (need_select_workaround)
						$(document).bind('selectstart', function () { return false; });
					return false;
			 
				}).css('cursor', 'move');
			}
		);

		// returns the jQuery object to allow for chainability.
		return this;
	}
})(jQuery);
