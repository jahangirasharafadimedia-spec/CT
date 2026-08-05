( function ( $ ) {
	function bindCategoryImage() {
		$( document )
			.off( 'click.cocat', '.communicationstoday-cat-select-image' )
			.on( 'click.cocat', '.communicationstoday-cat-select-image', function ( e ) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.communicationstoday-cat-image-field' );
				var $input = $wrap.find( 'input[name="communicationstoday_category_image_id"]' );
				var $preview = $wrap.find( '.communicationstoday-cat-img-preview' );
				var $remove = $wrap.find( '.communicationstoday-cat-remove-image' );

				if ( typeof wp === 'undefined' || ! wp.media ) {
					return;
				}

				var frame = wp.media( {
					title: communicationstodayCategoryImage.select,
					button: { text: communicationstodayCategoryImage.use },
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
					$remove.show();
				} );

				frame.open();
			} );

		$( document )
			.off( 'click.cocatr', '.communicationstoday-cat-remove-image' )
			.on( 'click.cocatr', '.communicationstoday-cat-remove-image', function ( e ) {
				e.preventDefault();
				var $wrap = $( this ).closest( '.communicationstoday-cat-image-field' );
				$wrap.find( 'input[name="communicationstoday_category_image_id"]' ).val( '' );
				$wrap.find( '.communicationstoday-cat-img-preview' ).attr( 'src', '' ).hide();
				$( this ).hide();
			} );
	}

	$( function () {
		bindCategoryImage();
	} );
} )( jQuery );
