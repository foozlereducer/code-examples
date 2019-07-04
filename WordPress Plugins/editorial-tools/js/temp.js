$(function() {
		
		$('a.toggler').click(function(e) {
				var i = $(this).attr('id'),
							c = $( '.' + i ).css('display');
				e.stopPropagation();
				e.preventDefault();
				if ( c == 'none' ) {
					$( '.' + i ).show();
				}
				else {
					$( '.' + i ).hide();
				}
				
		});
		
})
