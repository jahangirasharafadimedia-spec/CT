( function () {
	'use strict';

	function ready( fn ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', fn );
		} else {
			fn();
		}
	}

	ready( function () {
		if ( typeof communicationstodayNewsletter === 'undefined' ) {
			return;
		}

		var form = document.getElementById( 'ct-newsletter-form' );
		var emailInput = document.getElementById( 'ct-newsletter-email' );
		var messageEl = document.getElementById( 'ct-newsletter-message' );
		var submitBtn = form ? form.querySelector( '.newsletter-button' ) : null;

		if ( ! form || ! emailInput ) {
			return;
		}

		function showMessage( text, isError ) {
			if ( ! messageEl ) {
				return;
			}
			messageEl.textContent = text;
			messageEl.hidden = false;
			messageEl.classList.toggle( 'is-error', !! isError );
			messageEl.classList.toggle( 'is-success', ! isError );
		}

		function submitNewsletter() {
			var email = emailInput.value.trim();
			var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

			if ( ! email || ! emailPattern.test( email ) ) {
				showMessage( communicationstodayNewsletter.i18n.invalidEmail, true );
				return;
			}

			if ( submitBtn ) {
				submitBtn.disabled = true;
			}
			showMessage( communicationstodayNewsletter.i18n.sending, false );

			var body = new URLSearchParams();
			body.append( 'action', 'communicationstoday_newsletter_subscribe' );
			body.append( 'nonce', communicationstodayNewsletter.nonce );
			body.append( 'email', email );

			fetch( communicationstodayNewsletter.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
				},
				body: body.toString(),
				credentials: 'same-origin',
			} )
				.then( function ( response ) {
					return response.text().then( function ( text ) {
						try {
							return JSON.parse( text );
						} catch ( err ) {
							return null;
						}
					} );
				} )
				.then( function ( data ) {
					if ( data && data.success && data.data && data.data.message ) {
						showMessage( data.data.message, false );
						form.reset();
						return;
					}
					var msg =
						( data && data.data && data.data.message ) ||
						communicationstodayNewsletter.i18n.error;
					showMessage( msg, true );
				} )
				.catch( function () {
					showMessage( communicationstodayNewsletter.i18n.error, true );
				} )
				.finally( function () {
					if ( submitBtn ) {
						submitBtn.disabled = false;
					}
				} );
		}

		form.addEventListener( 'submit', function ( e ) {
			e.preventDefault();
			submitNewsletter();
		} );

		if ( submitBtn ) {
			submitBtn.addEventListener( 'click', function ( e ) {
				if ( form.checkValidity && ! form.checkValidity() ) {
					return;
				}
				e.preventDefault();
				submitNewsletter();
			} );
		}

		emailInput.addEventListener( 'keydown', function ( e ) {
			if ( e.key === 'Enter' ) {
				e.preventDefault();
				submitNewsletter();
			}
		} );
	} );
} )();
