
 
require([
    'jquery', 'imgselect', 'domReady!'
], function($) {

	$(document).ready(function() {
		// Live search
		$("#userCardsSearch").on("keyup", function() {
			var filter = $(this).val().toUpperCase();
			$('#cardsTable tr').not('thead tr').each(function() {
				if ($(this).find('td').text().toUpperCase().indexOf(filter) > -1) {
					$(this).show();
				} 
				else {
					$(this).hide();
				}
			});
		});
	});
});