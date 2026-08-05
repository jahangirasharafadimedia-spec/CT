( function ( $ ) {
	function bindThinkTankMedia() {
		$( document )
			.off( 'click.communicationstodayTT', '.communicationstoday-think-tank-media' )
			.on( 'click.communicationstodayTT', '.communicationstoday-think-tank-media', function ( e ) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.communicationstoday-think-tank-ad-media' );
				var $input = $wrap.find( '.think-tank-attachment-id' );
				var $preview = $wrap.find( '.think-tank-attachment-preview' );
				var $remove = $wrap.find( '.communicationstoday-think-tank-remove' );
				var $url = $wrap.find( '.think-tank-image-url' );

				if ( typeof wp === 'undefined' || ! wp.media ) {
					return;
				}

				var frame = wp.media( {
					title: communicationstodayThinkTank.select,
					button: { text: communicationstodayThinkTank.use },
					multiple: false,
					library: { type: 'image' },
				} );

				frame.on( 'select', function () {
					var att = frame.state().get( 'selection' ).first().toJSON();
					$input.val( att.id );
					var url =
						att.sizes && att.sizes.medium
							? att.sizes.medium.url
							: att.url;
					$preview.attr( 'src', url ).show();
					$url.val( att.url ? att.url : url );
					$remove.show();
				} );

				frame.open();
			} );

		$( document )
			.off( 'click.communicationstodayTTR', '.communicationstoday-think-tank-remove' )
			.on( 'click.communicationstodayTTR', '.communicationstoday-think-tank-remove', function ( e ) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.communicationstoday-think-tank-ad-media' );
				$wrap.find( '.think-tank-attachment-id' ).val( '' );
				$wrap.find( '.think-tank-attachment-preview' ).attr( 'src', '' ).hide();
				$wrap.find( '.think-tank-image-url' ).val( '' );
				$( this ).hide();
			} );
	}

	$( function () {
		bindThinkTankMedia();
	} );

	$( document ).on( 'widget-added widget-updated', function () {
		bindThinkTankMedia();
	} );
} )( jQuery );
