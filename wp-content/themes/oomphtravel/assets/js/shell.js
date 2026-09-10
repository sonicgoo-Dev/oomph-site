/**
 * OomphTravel shell. Forked from CruiseOomph's shell.js (D41) with the
 * `ot-` prefix; every piece is optional and returns early when its markup is
 * absent. No dependencies, deferred.
 *
 *  1. Header: `is-scrolled` once the page has moved (Header / Desktop,
 *     State=Scrolled) and the Destinations / Ways to travel dropdowns for
 *     click and touch — hover and focus are handled in CSS.
 *  2. Mobile drawer: open / close with focus management, Escape, scroll lock.
 *  3. Deferred pictures: put back the src/srcset that oomphtravel_defer_image()
 *     parked in data-ot-src / data-ot-srcset.
 *  4. Ticker: the Pause / Play control (WCAG 2.2.2 — hover pause alone is not
 *     reachable from a keyboard).
 */

/* 1. Header state + dropdowns. */
( function () {
	'use strict';

	var header = document.querySelector( '[data-ot-header]' );
	if ( header ) {
		var ticking = false;
		var update = function () {
			header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
			ticking = false;
		};
		window.addEventListener( 'scroll', function () {
			if ( ! ticking ) {
				window.requestAnimationFrame( update );
				ticking = true;
			}
		}, { passive: true } );
		update();
	}

	var toggles = document.querySelectorAll( '[data-ot-dropdown-toggle]' );
	if ( ! toggles.length ) {
		return;
	}

	function closeAll( except ) {
		for ( var i = 0; i < toggles.length; i++ ) {
			if ( toggles[ i ] !== except ) {
				toggles[ i ].setAttribute( 'aria-expanded', 'false' );
				toggles[ i ].parentNode.classList.remove( 'is-open' );
			}
		}
	}

	for ( var i = 0; i < toggles.length; i++ ) {
		toggles[ i ].addEventListener( 'click', function ( e ) {
			var open = this.getAttribute( 'aria-expanded' ) === 'true';
			closeAll( this );
			this.setAttribute( 'aria-expanded', open ? 'false' : 'true' );
			this.parentNode.classList.toggle( 'is-open', ! open );
			e.stopPropagation();
		} );
	}

	document.addEventListener( 'click', function () { closeAll( null ); } );
	document.addEventListener( 'keydown', function ( e ) {
		if ( e.key === 'Escape' ) {
			closeAll( null );
		}
	} );
	// Leaving a dropdown by keyboard closes it, so the next item is not hidden under it.
	document.addEventListener( 'focusin', function ( e ) {
		for ( var j = 0; j < toggles.length; j++ ) {
			var item = toggles[ j ].parentNode;
			if ( item.classList.contains( 'is-open' ) && ! item.contains( e.target ) ) {
				toggles[ j ].setAttribute( 'aria-expanded', 'false' );
				item.classList.remove( 'is-open' );
			}
		}
	} );
} )();

/* 2. Mobile drawer (Header / Mobile → full-screen navy drawer). */
( function () {
	'use strict';

	var nav = document.querySelector( '[data-ot-menu]' );
	var openBtn = document.querySelector( '[data-ot-menu-open]' );
	if ( ! nav || ! openBtn ) {
		return;
	}
	var closeBtn = nav.querySelector( '[data-ot-menu-close]' );
	var lastFocus = null;

	function focusables() {
		return nav.querySelectorAll( 'a[href], button:not([disabled])' );
	}

	function open() {
		lastFocus = document.activeElement;
		nav.classList.add( 'is-open' );
		document.documentElement.classList.add( 'ot-menu-open' );
		openBtn.setAttribute( 'aria-expanded', 'true' );
		var first = closeBtn || focusables()[ 0 ];
		if ( first ) {
			first.focus();
		}
	}

	function close() {
		nav.classList.remove( 'is-open' );
		document.documentElement.classList.remove( 'ot-menu-open' );
		openBtn.setAttribute( 'aria-expanded', 'false' );
		if ( lastFocus && typeof lastFocus.focus === 'function' ) {
			lastFocus.focus();
		}
	}

	openBtn.addEventListener( 'click', open );
	if ( closeBtn ) {
		closeBtn.addEventListener( 'click', close );
	}

	document.addEventListener( 'keydown', function ( e ) {
		if ( ! nav.classList.contains( 'is-open' ) ) {
			return;
		}
		if ( e.key === 'Escape' ) {
			e.preventDefault();
			close();
			return;
		}
		if ( e.key === 'Tab' ) {
			var items = focusables();
			if ( ! items.length ) {
				return;
			}
			var first = items[ 0 ];
			var last = items[ items.length - 1 ];
			if ( e.shiftKey && document.activeElement === first ) {
				e.preventDefault();
				last.focus();
			} else if ( ! e.shiftKey && document.activeElement === last ) {
				e.preventDefault();
				first.focus();
			}
		}
	} );

	// Close if the viewport grows past the mobile breakpoint while open.
	var mq = window.matchMedia( '(min-width: 1024px)' );
	var onChange = function ( ev ) {
		if ( ev.matches && nav.classList.contains( 'is-open' ) ) {
			close();
		}
	};
	if ( mq.addEventListener ) {
		mq.addEventListener( 'change', onChange );
	} else if ( mq.addListener ) {
		mq.addListener( onChange );
	}
} )();

/* 3. Deferred pictures (see oomphtravel_defer_image()). */
( function () {
	'use strict';

	var done = false;

	function reveal() {
		if ( done ) {
			return;
		}
		done = true;
		var imgs = document.querySelectorAll( 'img[data-ot-src]' );
		for ( var i = 0; i < imgs.length; i++ ) {
			var img = imgs[ i ];
			var srcset = img.getAttribute( 'data-ot-srcset' );
			if ( srcset ) {
				img.setAttribute( 'srcset', srcset );
				img.removeAttribute( 'data-ot-srcset' );
			}
			img.setAttribute( 'src', img.getAttribute( 'data-ot-src' ) );
			img.removeAttribute( 'data-ot-src' );
		}
	}

	if ( document.readyState === 'complete' ) {
		reveal();
	} else {
		window.addEventListener( 'load', reveal );
		window.setTimeout( reveal, 5000 );
	}
} )();

/* 4. Place-name ticker: Pause / Play. */
( function () {
	'use strict';

	var track = document.querySelector( '[data-ot-ticker]' );
	var toggle = document.querySelector( '[data-ot-ticker-toggle]' );
	if ( ! track || ! toggle ) {
		return;
	}

	toggle.addEventListener( 'click', function () {
		var paused = track.classList.toggle( 'is-paused' );
		toggle.setAttribute( 'aria-pressed', paused ? 'true' : 'false' );
		toggle.textContent = paused ? toggle.getAttribute( 'data-label-play' ) : toggle.getAttribute( 'data-label-pause' );
	} );
} )();
