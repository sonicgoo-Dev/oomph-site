/**
 * Escorted tours index: a change to any filter submits the form, so the
 * URL (and the grid) updates without the button. Without this script the
 * form still works: the button submits it. Nothing is fetched; the page
 * reloads with the filters in the query string, which is the point (plan
 * §6.5: filter state lives in the URL).
 */
( function () {
	'use strict';

	var form = document.querySelector( '.ot-filters' );
	if ( ! form ) {
		return;
	}
	form.classList.add( 'is-live' );
	form.addEventListener( 'change', function ( event ) {
		if ( event.target && event.target.matches( 'select' ) ) {
			if ( typeof form.requestSubmit === 'function' ) {
				form.requestSubmit();
			} else {
				form.submit();
			}
		}
	} );
} )();
