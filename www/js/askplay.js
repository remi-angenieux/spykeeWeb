$(document).ready( function() {
var queue =$("#askplay").dialog({
			autoOpen: false,
			width: 400,
			draggable:true,
			close: function( event, ui ) {window.location.href='./';},
			buttons: [
				{
					text: "Ok",
					click: function() {
						window.location.href='/play/play';
					}
				},
				{
					text: "Cancel",
					click: function() {
						window.location.href='./';
					}
				}
			]
		});

		queue.dialog("open");
})