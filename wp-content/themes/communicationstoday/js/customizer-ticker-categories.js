/**
 * Customizer: sync ticker category checkboxes to theme mod (comma-separated IDs).
 */
( function ( $ ) {
	'use strict';

	function readCheckedIds( control ) {
		var ids = [];
		control.container.find( 'input[type="checkbox"]:checked' ).each( function () {
			ids.push( $( this ).val() );
		} );
		return ids.join( ',' );
	}

	function bindTickerCategoryControl( control ) {
		if ( ! control || ! control.container ) {
			return;
		}

		function syncToSetting() {
			control.setting.set( readCheckedIds( control ) );
		}

		control.container.on( 'change', 'input[type="checkbox"]', syncToSetting );

		// Match visible checkboxes to the setting (needed before Publish).
		syncToSetting();
	}

	wp.customize.bind( 'ready', function () {
		var control = wp.customize.control( 'communicationstoday_ticker_category_ids' );
		if ( control ) {
			bindTickerCategoryControl( control );
		}
	} );
} )( jQuery );
