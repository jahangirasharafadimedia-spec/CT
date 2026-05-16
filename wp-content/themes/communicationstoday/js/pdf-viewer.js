/**
 * PDF.js canvas viewer for [ITFPDF] shortcode.
 */
( function () {
	'use strict';

	var i18n = ( window.communicationstodayPdfViewer && window.communicationstodayPdfViewer.i18n ) || {};

	function getPdfLib() {
		if ( typeof pdfjsLib !== 'undefined' ) {
			return pdfjsLib;
		}
		if ( typeof window.pdfjsLib !== 'undefined' ) {
			return window.pdfjsLib;
		}
		return null;
	}

	function showLoading( root, loadingEl, on ) {
		if ( loadingEl ) {
			loadingEl.hidden = ! on;
		}
		root.classList.toggle( 'itf-pdf-viewer--loading', on );
	}

	function showError( root, errorEl, message ) {
		if ( errorEl ) {
			errorEl.textContent = message || '';
			errorEl.hidden = ! message;
		}
		root.classList.toggle( 'itf-pdf-viewer--error', !! message );
	}

	function failAllViewers( message ) {
		var viewers = document.querySelectorAll( '.itf-pdf-viewer[data-pdf-url]' );
		for ( var i = 0; i < viewers.length; i += 1 ) {
			var root = viewers[ i ];
			var loadingEl = root.querySelector( '.itf-pdf-loading' );
			var errorEl = root.querySelector( '.itf-pdf-error' );
			showLoading( root, loadingEl, false );
			showError( root, errorEl, message );
		}
	}

	function initViewer( pdfjsLib, root ) {
		var url = root.getAttribute( 'data-pdf-url' );
		if ( ! url ) {
			return;
		}

		var canvas = root.querySelector( '.itf-pdf-canvas' );
		var pageNumEl = root.querySelector( '.itf-pdf-page-num' );
		var pageCountEl = root.querySelector( '.itf-pdf-page-count' );
		var prevBtn = root.querySelector( '.itf-pdf-prev' );
		var nextBtn = root.querySelector( '.itf-pdf-next' );
		var loadingEl = root.querySelector( '.itf-pdf-loading' );
		var errorEl = root.querySelector( '.itf-pdf-error' );

		if ( ! canvas || ! pageNumEl || ! pageCountEl ) {
			return;
		}

		var ctx = canvas.getContext( '2d' );
		var pdfDoc = null;
		var pageNum = 1;
		var renderTask = null;
		var resizeTimer = null;

		function getScale( viewport ) {
			var card = root.querySelector( '.itf-pdf-card' );
			var maxWidth = card ? card.clientWidth - 50 : root.clientWidth;
			if ( maxWidth < 1 ) {
				maxWidth = viewport.width;
			}
			return Math.min( 2, maxWidth / viewport.width );
		}

		function renderPage( num ) {
			if ( ! pdfDoc ) {
				return;
			}

			showError( root, errorEl, '' );

			pdfDoc.getPage( num ).then( function ( page ) {
				var scale = getScale( page.getViewport( { scale: 1 } ) );
				var viewport = page.getViewport( { scale: scale } );

				canvas.height = viewport.height;
				canvas.width = viewport.width;

				if ( renderTask ) {
					renderTask.cancel();
				}

				renderTask = page.render( {
					canvasContext: ctx,
					viewport: viewport,
				} );

				return renderTask.promise;
			} ).then( function () {
				pageNumEl.textContent = String( num );
				showLoading( root, loadingEl, false );
			} ).catch( function ( err ) {
				if ( err && err.name === 'RenderingCancelledException' ) {
					return;
				}
				showLoading( root, loadingEl, false );
				showError( root, errorEl, i18n.renderError || 'Unable to display this page.' );
			} );
		}

		if ( prevBtn ) {
			prevBtn.addEventListener( 'click', function () {
				if ( pageNum <= 1 ) {
					return;
				}
				pageNum -= 1;
				renderPage( pageNum );
			} );
		}

		if ( nextBtn ) {
			nextBtn.addEventListener( 'click', function () {
				if ( ! pdfDoc || pageNum >= pdfDoc.numPages ) {
					return;
				}
				pageNum += 1;
				renderPage( pageNum );
			} );
		}

		window.addEventListener( 'resize', function () {
			clearTimeout( resizeTimer );
			resizeTimer = setTimeout( function () {
				renderPage( pageNum );
			}, 150 );
		} );

		showLoading( root, loadingEl, true );
		showError( root, errorEl, '' );

		pdfjsLib.getDocument( { url: url } ).promise
			.then( function ( pdf ) {
				pdfDoc = pdf;
				pageCountEl.textContent = String( pdf.numPages );
				renderPage( pageNum );
			} )
			.catch( function () {
				showLoading( root, loadingEl, false );
				showError( root, errorEl, i18n.loadError || 'Unable to load PDF.' );
			} );
	}

	function boot() {
		var pdfjsLib = getPdfLib();
		if ( ! pdfjsLib ) {
			failAllViewers( i18n.libraryError || 'PDF viewer failed to load. Please refresh the page.' );
			return;
		}

		pdfjsLib.GlobalWorkerOptions.workerSrc =
			'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

		var viewers = document.querySelectorAll( '.itf-pdf-viewer[data-pdf-url]' );
		for ( var i = 0; i < viewers.length; i += 1 ) {
			initViewer( pdfjsLib, viewers[ i ] );
		}
	}

	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
