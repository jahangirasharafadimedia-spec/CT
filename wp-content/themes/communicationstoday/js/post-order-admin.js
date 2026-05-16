( function ( $ ) {
	function savePostOrder( categoryId, postIds, $status ) {
		if ( ! postIds.length ) {
			return;
		}

		if ( $status && $status.length ) {
			$status.removeClass( 'is-success is-error' ).text( communicationstodayPostOrder.i18n.saving );
		}

		$.post( communicationstodayPostOrder.ajaxUrl, {
			action: 'communicationstoday_save_post_order',
			nonce: communicationstodayPostOrder.nonce,
			category_id: categoryId || 0,
			post_ids: postIds,
		} )
			.done( function ( res ) {
				if ( res && res.success ) {
					if ( $status && $status.length ) {
						$status
							.removeClass( 'is-error' )
							.addClass( 'is-success' )
							.text( res.data.message || communicationstodayPostOrder.i18n.saved );
					}
					postIds.forEach( function ( id, index ) {
						$( '#post-' + id )
							.find( '.ct-posts-order-num' )
							.text( String( index ) );
					} );
				} else if ( $status && $status.length ) {
					$status
						.removeClass( 'is-success' )
						.addClass( 'is-error' )
						.text(
							( res && res.data && res.data.message ) ||
								communicationstodayPostOrder.i18n.error
						);
				}
			} )
			.fail( function () {
				if ( $status && $status.length ) {
					$status
						.removeClass( 'is-success' )
						.addClass( 'is-error' )
						.text( communicationstodayPostOrder.i18n.error );
				}
			} );
	}

	function collectPostIdsFromTable() {
		var postIds = [];
		$( '#the-list tr[id^="post-"]' ).each( function () {
			var match = ( this.id || '' ).match( /^post-(\d+)$/ );
			if ( match ) {
				postIds.push( parseInt( match[1], 10 ) );
			}
		} );
		return postIds;
	}

	function initStoryOrderPage() {
		var $list = $( '#ct-story-order-list' );
		if ( ! $list.length ) {
			return;
		}

		$list.sortable( {
			axis: 'y',
			handle: '.ct-story-order-handle',
			placeholder: 'ct-story-order-item ct-story-order-placeholder',
			forcePlaceholderSize: true,
		} );

		$( '#ct-story-order-save' ).on( 'click', function () {
			var $btn = $( this );
			var $status = $( '.ct-story-order-status' );
			var categoryId = parseInt( $list.data( 'category-id' ), 10 ) || 0;
			var postIds = [];

			$list.find( '.ct-story-order-item' ).each( function () {
				var id = parseInt( $( this ).data( 'post-id' ), 10 );
				if ( id > 0 ) {
					postIds.push( id );
				}
			} );

			if ( ! categoryId || ! postIds.length ) {
				$status
					.removeClass( 'is-success is-error' )
					.addClass( 'is-error' )
					.text( communicationstodayPostOrder.i18n.missing );
				return;
			}

			$btn.prop( 'disabled', true );
			savePostOrder( categoryId, postIds, $status );
			$btn.prop( 'disabled', false );
		} );
	}

	function initPostsListSortable() {
		var $tbody = $( '#the-list' );
		if ( ! $tbody.length || ! $tbody.find( 'tr[id^="post-"]' ).length ) {
			return;
		}

		var categoryId =
			parseInt( communicationstodayPostOrder.categoryId, 10 ) || 0;
		var $status = $( '.ct-posts-order-notice-status' );
		var saveTimer = null;

		$tbody.sortable( {
			items: 'tr[id^="post-"]:not(.inline-edit-row)',
			axis: 'y',
			handle: '.ct-posts-drag-handle',
			placeholder: 'ct-posts-sortable-placeholder',
			forcePlaceholderSize: true,
			tolerance: 'pointer',
			helper: function ( e, ui ) {
				ui.children().each( function () {
					$( this ).width( $( this ).width() );
				} );
				return ui;
			},
			update: function () {
				if ( saveTimer ) {
					clearTimeout( saveTimer );
				}
				saveTimer = setTimeout( function () {
					savePostOrder( categoryId, collectPostIdsFromTable(), $status );
				}, 300 );
			},
		} );
	}

	$( function () {
		if (
			typeof communicationstodayPostOrder === 'undefined' ||
			! communicationstodayPostOrder.context
		) {
			return;
		}

		if ( communicationstodayPostOrder.context === 'posts_list' ) {
			initPostsListSortable();
		} else {
			initStoryOrderPage();
		}
	} );
}( jQuery ) );
