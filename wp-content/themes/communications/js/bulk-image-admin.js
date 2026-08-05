( function ( $ ) {
	var state = {
		jobId: null,
		total: 0,
	};

	function getFilters() {
		return {
			category_id: parseInt( $( '#ct-bulk-images-category' ).val(), 10 ) || 0,
			year: parseInt( $( '#ct-bulk-images-year' ).val(), 10 ) || 0,
			month: parseInt( $( '#ct-bulk-images-month' ).val(), 10 ) || 0,
		};
	}

	function setStatus( text, type ) {
		var $el = $( '#ct-bulk-images-status' );
		$el.removeClass( 'is-error is-success' );
		if ( type ) {
			$el.addClass( 'is-' + type );
		}
		$el.text( text || '' );
	}

	function renderPreview( data ) {
		var $results = $( '#ct-bulk-images-results' );
		var $stats = $( '#ct-bulk-images-stats' );
		var i18n = communicationstodayBulkImages.i18n;

		$results.prop( 'hidden', false );
		$( '#ct-bulk-images-period' ).text(
			data.category_name + ' — ' + data.period_label
		);

		$stats.empty();
		$stats.append(
			'<li><strong>' +
				i18n.totalPosts +
				':</strong> ' +
				( data.total_posts || 0 ) +
				'</li>'
		);
		$stats.append(
			'<li><strong>' +
				i18n.postsImageDelete +
				':</strong> ' +
				( data.posts_image_delete || 0 ) +
				'</li>'
		);
		$stats.append(
			'<li><strong>' +
				i18n.estimatedSize +
				':</strong> ' +
				( data.size_label || '0 B' ) +
				'</li>'
		);

		state.jobId = data.job_id;
		state.total = data.image_count;

		var $delete = $( '#ct-bulk-images-delete' );
		if ( data.image_count > 0 ) {
			$delete.prop( 'disabled', false );
		} else {
			$delete.prop( 'disabled', true );
			setStatus( i18n.noImages, 'error' );
		}

		$( '#ct-bulk-images-progress-wrap' ).prop( 'hidden', true );
		$( '#ct-bulk-images-progress-fill' ).css( 'width', '0%' );
	}

	function runPreview() {
		var filters = getFilters();
		var i18n = communicationstodayBulkImages.i18n;

		if ( ! filters.category_id || ! filters.year ) {
			setStatus( i18n.selectFilters, 'error' );
			return;
		}

		setStatus( i18n.previewing, '' );
		$( '#ct-bulk-images-delete' ).prop( 'disabled', true );
		state.jobId = null;

		$.post( communicationstodayBulkImages.ajaxUrl, {
			action: 'communicationstoday_bulk_images_preview',
			nonce: communicationstodayBulkImages.nonce,
			category_id: filters.category_id,
			year: filters.year,
			month: filters.month,
		} )
			.done( function ( res ) {
				if ( res && res.success && res.data ) {
					renderPreview( res.data );
					setStatus( '', '' );
				} else {
					setStatus(
						( res && res.data && res.data.message ) ||
							i18n.previewError,
						'error'
					);
				}
			} )
			.fail( function () {
				setStatus( i18n.previewError, 'error' );
			} );
	}

	function updateProgress( percent, label ) {
		$( '#ct-bulk-images-progress-wrap' ).prop( 'hidden', false );
		$( '#ct-bulk-images-progress-fill' ).css( 'width', percent + '%' );
		$( '#ct-bulk-images-progress-label' ).text( label );
	}

	function deleteBatch( offset ) {
		var i18n = communicationstodayBulkImages.i18n;

		$.post( communicationstodayBulkImages.ajaxUrl, {
			action: 'communicationstoday_bulk_images_delete',
			nonce: communicationstodayBulkImages.nonce,
			job_id: state.jobId,
			offset: offset,
			batch_size: 20,
		} )
			.done( function ( res ) {
				if ( ! res || ! res.success || ! res.data ) {
					setStatus(
						( res && res.data && res.data.message ) ||
							i18n.deleteError,
						'error'
					);
					$( '#ct-bulk-images-delete' ).prop( 'disabled', false );
					return;
				}

				var data = res.data;
				var label =
					data.offset +
					' / ' +
					data.total +
					' (' +
					data.percent +
					'%)';

				updateProgress( data.percent, label );

				if ( data.done ) {
					setStatus( i18n.deleteDone, 'success' );
					$( '#ct-bulk-images-delete' ).prop( 'disabled', true );
					state.jobId = null;
					runPreview();
					return;
				}

				deleteBatch( data.offset );
			} )
			.fail( function () {
				setStatus( i18n.deleteError, 'error' );
				$( '#ct-bulk-images-delete' ).prop( 'disabled', false );
			} );
	}

	function runDelete() {
		var i18n = communicationstodayBulkImages.i18n;

		if ( ! state.jobId || ! state.total ) {
			setStatus( i18n.noImages, 'error' );
			return;
		}

		if ( ! window.confirm( i18n.confirmDelete ) ) {
			return;
		}

		var typed = window.prompt( i18n.typeConfirm, '' );
		if ( typed !== i18n.confirmWord ) {
			return;
		}

		setStatus( i18n.deleting, '' );
		$( '#ct-bulk-images-delete' ).prop( 'disabled', true );
		updateProgress( 0, '0 / ' + state.total );

		deleteBatch( 0 );
	}

	$( function () {
		$( '#ct-bulk-images-preview' ).on( 'click', runPreview );
		$( '#ct-bulk-images-delete' ).on( 'click', runDelete );
	} );
} )( jQuery );
