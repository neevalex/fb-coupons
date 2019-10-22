jQuery( document ).ready(
	function ($) {
		$( ".fb-coupons" ).on(
			"click",
			function () {
				var button = $( this );
				console.log( 'click' );
				$.ajax(
					{
						type : "post",
						dataType : "json",
						url : ajax_object.ajaxurl,
						data : 'action=get_fbcoupons&security=' + ajax_object.ajax_nonce,
						success: function (response) {
							// console.log(response['html']);
							if (response['response'] == 'ok') {
								$( button ).after( response['html'] );
								$( button ).hide();
							}
						}
					}
				);
			}
		);
	}
);
