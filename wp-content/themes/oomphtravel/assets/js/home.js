/**
 * OomphTravel homepage. Loaded only on the front page, deferred, after
 * shell.js. One job: the "Where to / How to travel" tab row (plan §6.1.4).
 *
 * Without JavaScript both panels are shown one under the other and the tab
 * buttons are hidden, so nothing is unreachable; this script adds `is-tabbed`
 * to switch the row into a real tablist. Keyboard follows the WAI-ARIA tabs
 * pattern: Left / Right / Home / End move and select, automatic activation.
 */
( function () {
	'use strict';

	var roots = document.querySelectorAll( '[data-ot-tabs]' );
	if ( ! roots.length ) {
		return;
	}

	function setup( root ) {
		var tabs = root.querySelectorAll( '[role="tab"]' );
		var panels = root.querySelectorAll( '[role="tabpanel"]' );
		if ( tabs.length < 2 || tabs.length !== panels.length ) {
			return;
		}

		function select( index, focus ) {
			for ( var i = 0; i < tabs.length; i++ ) {
				var on = i === index;
				tabs[ i ].setAttribute( 'aria-selected', on ? 'true' : 'false' );
				tabs[ i ].setAttribute( 'tabindex', on ? '0' : '-1' );
				panels[ i ].hidden = ! on;
			}
			if ( focus ) {
				tabs[ index ].focus();
			}
		}

		for ( var i = 0; i < tabs.length; i++ ) {
			( function ( index ) {
				tabs[ index ].addEventListener( 'click', function () {
					select( index, false );
				} );
				tabs[ index ].addEventListener( 'keydown', function ( e ) {
					var next = null;
					if ( 'ArrowRight' === e.key ) {
						next = ( index + 1 ) % tabs.length;
					} else if ( 'ArrowLeft' === e.key ) {
						next = ( index - 1 + tabs.length ) % tabs.length;
					} else if ( 'Home' === e.key ) {
						next = 0;
					} else if ( 'End' === e.key ) {
						next = tabs.length - 1;
					}
					if ( null !== next ) {
						e.preventDefault();
						select( next, true );
					}
				} );
			}( i ) );
		}

		root.classList.add( 'is-tabbed' );
		select( 0, false );
	}

	for ( var i = 0; i < roots.length; i++ ) {
		setup( roots[ i ] );
	}
}() );
