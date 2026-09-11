/**
 * Start planning (plan §6.15): one form, two steps, and the receipt.
 *
 * Without this script both steps show in order and the form posts to the
 * plugin as a plain form. With it: one step at a time, the answers checked
 * before moving on, only the contact field the chosen method needs, a
 * recap of step 1 above step 2, a fresh nonce for a page served from cache,
 * "A cruise" answered inline with the CruiseOomph link, and the analytics
 * events the plan names (inquiry_started, inquiry_step2, inquiry_sent,
 * calendly_booked) plus the generate_lead event every form reports (R11).
 *
 * No dependencies and no build step, like the rest of the theme's scripts.
 */
( function () {
	'use strict';

	/* ---- analytics, guarded: an ad blocker must never break the form ---- */

	function track( name, params ) {
		try {
			if ( typeof window.gtag === 'function' ) {
				window.gtag( 'event', name, params || {} );
			}
			window.dataLayer = window.dataLayer || [];
			window.dataLayer.push( Object.assign( { event: name }, params || {} ) );
		} catch ( e ) {
			// Analytics is never allowed to interrupt an enquiry.
		}
	}

	function markLead( source ) {
		track( 'generate_lead', { lead_source: source } );
		try {
			if ( typeof window.clarity === 'function' ) {
				window.clarity( 'set', 'lead', source );
			}
		} catch ( e ) {
			// As above.
		}
	}

	/* ---- the receipt ------------------------------------------------- */

	var receipt = document.querySelector( '[data-ot-receipt]' );
	if ( receipt ) {
		var reference = receipt.getAttribute( 'data-ot-reference' ) || '';
		var key = 'ot-inquiry-sent-' + reference;
		var seen = false;
		try {
			seen = !! ( reference && window.sessionStorage.getItem( key ) );
			if ( reference ) {
				window.sessionStorage.setItem( key, '1' );
			}
		} catch ( e ) {
			// Private mode: report once per load instead.
		}
		if ( ! seen ) {
			track( 'inquiry_sent', { lead_source: 'start_planning' } );
			markLead( 'start_planning' );
		}

		// When Calendly confirms a booking, swap the widget for a line of
		// text with the exits in view, rather than leaving the visitor on
		// Calendly's own final screen.
		window.addEventListener( 'message', function ( e ) {
			if ( ! e.data || typeof e.data !== 'object' || e.data.event !== 'calendly.event_scheduled' ) {
				return;
			}
			var widget = document.querySelector( '[data-ot-calendly]' );
			var booked = document.querySelector( '[data-ot-booked]' );
			if ( widget ) {
				widget.hidden = true;
			}
			if ( booked ) {
				booked.hidden = false;
				booked.scrollIntoView( { behavior: 'smooth', block: 'center' } );
			}
			track( 'calendly_booked', { lead_source: 'calendly_booking' } );
			markLead( 'calendly_booking' );
		} );
		return;
	}

	/* ---- the form ------------------------------------------------------ */

	var root = document.querySelector( '[data-ot-plan]' );
	var form = root ? root.querySelector( '[data-ot-form]' ) : null;
	if ( ! root || ! form ) {
		return;
	}

	var title = root.querySelector( '.ot-plan-hero__title' );
	var swaps = Array.prototype.slice.call( root.querySelectorAll( '[data-ot-swap]' ) );
	var segs = Array.prototype.slice.call( root.querySelectorAll( '[data-ot-seg]' ) );
	var panels = {
		1: form.querySelector( '[data-ot-panel="1"]' ),
		2: form.querySelector( '[data-ot-panel="2"]' )
	};
	var actions1 = form.querySelector( '[data-ot-actions="1"]' );
	var continueButton = form.querySelector( '[data-ot-continue]' );
	var backButton = form.querySelector( '[data-ot-back-button]' );
	var recapBack = form.querySelector( '[data-ot-back]' );
	var recap = form.querySelector( '[data-ot-recap]' );
	var recapList = form.querySelector( '[data-ot-recap-list]' );
	var cruise = form.querySelector( '[data-ot-cruise]' );
	var contactField = form.querySelector( '[data-ot-contact]' );
	var contactLabel = form.querySelector( '[data-ot-contact-label]' );
	var contactInput = form.querySelector( '[data-ot-contact-input]' );
	var contactHelp = form.querySelector( '[data-ot-contact-help]' );
	var newsletterRow = form.querySelector( '[data-ot-newsletter-row]' );
	var send = form.querySelector( '[data-ot-send]' );
	var status = form.querySelector( '[data-ot-status]' );
	var source = form.querySelector( '[data-ot-source]' );
	var elapsed = form.querySelector( 'input[name="elapsed_ms"]' );
	var nonceField = form.querySelector( 'input[name$="_nonce"]' );
	var nonceApi = root.getAttribute( 'data-ot-nonce-api' );
	var newsletterEndpoint = root.getAttribute( 'data-ot-newsletter' ) || '';
	var sendLabel = send ? send.innerHTML : '';

	var MESSAGES = {
		trip_type: 'Choose the kind of trip, or "Not sure yet".',
		budget: 'Choose a budget range, or "Guide me".',
		name: 'Please tell me your name.',
		contact_method: 'Choose email, a phone call or a text.',
		contact_value: 'Add the detail I should use to reply.',
		consent: 'Please confirm Eric may contact you about this trip.'
	};

	/* Where the visitor came from, for the record (same site only). */
	if ( source && ! source.value && document.referrer ) {
		try {
			var ref = new URL( document.referrer );
			if ( ref.origin === window.location.origin && ref.pathname !== window.location.pathname ) {
				source.value = ref.href;
			}
		} catch ( e ) {
			// Leave it empty.
		}
	}

	function checked( name ) {
		return form.querySelector( 'input[name="' + name + '"]:checked' );
	}

	function labelFor( input ) {
		var text = input && input.parentNode ? input.parentNode.querySelector( '[class$="-text"]' ) : null;
		return text ? text.textContent.trim() : ( input ? input.value : '' );
	}

	function say( text ) {
		if ( status ) {
			status.textContent = text || '';
		}
	}

	/* ---- errors ---------------------------------------------------- */

	function holderFor( field ) {
		var group = form.querySelector( '[data-ot-group="' + field + '"]' );
		if ( group ) {
			return group;
		}
		var control = form.querySelector( '[name="' + field + '"]' );
		return control ? control.closest( '.ot-plan__field, .ot-plan__check' ) : null;
	}

	function clearError( field ) {
		var holder = holderFor( field );
		var existing = form.querySelector( '[data-ot-error="' + field + '"]' );
		if ( existing ) {
			existing.parentNode.removeChild( existing );
		}
		if ( holder ) {
			holder.classList.remove( 'is-invalid' );
		}
		var control = form.querySelector( '[name="' + field + '"]' );
		if ( control && control.type !== 'radio' && control.type !== 'checkbox' ) {
			control.removeAttribute( 'aria-invalid' );
		}
	}

	function showError( field ) {
		clearError( field );
		var holder = holderFor( field );
		if ( ! holder ) {
			return;
		}
		var p = document.createElement( 'p' );
		p.className = 'ot-plan__error';
		p.id = 'ot-plan-error-' + field;
		p.setAttribute( 'data-ot-error', field );
		p.textContent = MESSAGES[ field ] || 'This needs attention.';
		// Keep the error under the controls but above the cruise note.
		if ( cruise && holder.contains( cruise ) ) {
			holder.insertBefore( p, cruise );
		} else {
			holder.appendChild( p );
		}
		holder.classList.add( 'is-invalid' );
		var control = form.querySelector( '[name="' + field + '"]' );
		if ( control && control.type !== 'radio' && control.type !== 'checkbox' ) {
			control.setAttribute( 'aria-invalid', 'true' );
			control.setAttribute( 'aria-describedby', p.id );
		}
	}

	function focusField( field ) {
		var control = checked( field ) || form.querySelector( '[name="' + field + '"]' );
		if ( control ) {
			control.focus();
		}
	}

	function validateStep1() {
		var missing = [];
		[ 'trip_type', 'budget' ].forEach( clearError );
		if ( ! checked( 'trip_type' ) ) {
			missing.push( 'trip_type' );
		}
		if ( ! checked( 'budget' ) ) {
			missing.push( 'budget' );
		}
		missing.forEach( showError );
		if ( missing.length ) {
			focusField( missing[ 0 ] );
			say( 'A few details need attention before continuing.' );
		}
		return ! missing.length;
	}

	function validateStep2() {
		var missing = [];
		var name = form.querySelector( '[name="name"]' );
		var consent = form.querySelector( '[name="consent"]' );
		var method = checked( 'contact_method' );
		[ 'name', 'contact_method', 'contact_value', 'consent' ].forEach( clearError );
		if ( ! name || ! name.value.trim() ) {
			missing.push( 'name' );
		}
		if ( ! method ) {
			missing.push( 'contact_method' );
		}
		var value = contactInput ? contactInput.value.trim() : '';
		if ( ! value ) {
			missing.push( 'contact_value' );
		} else if ( method && method.value === 'email' && ! /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test( value ) ) {
			missing.push( 'contact_value' );
		} else if ( method && method.value !== 'email' && value.replace( /\D+/g, '' ).length < 7 ) {
			missing.push( 'contact_value' );
		}
		if ( ! consent || ! consent.checked ) {
			missing.push( 'consent' );
		}
		missing.forEach( showError );
		if ( missing.length ) {
			focusField( missing[ 0 ] );
			say( 'A few details need attention before sending.' );
		}
		return ! missing.length;
	}

	/* ---- steps ----------------------------------------------------- */

	function fillRecap() {
		if ( ! recap || ! recapList ) {
			return;
		}
		var items = [];
		var type = labelFor( checked( 'trip_type' ) );
		var places = Array.prototype.slice.call( form.querySelectorAll( 'input[name="destinations[]"]:checked' ) ).map( labelFor );
		var when = form.querySelector( '[name="when"]' );
		var who = form.querySelector( '[name="travelers"]' );
		var budget = labelFor( checked( 'budget' ) );
		if ( type ) {
			items.push( type );
		}
		if ( places.length ) {
			items.push( places.join( ', ' ) );
		}
		if ( when && when.value ) {
			items.push( when.options[ when.selectedIndex ].text );
		}
		if ( who && who.value.trim() ) {
			items.push( who.value.trim() );
		}
		if ( budget ) {
			items.push( budget === 'Guide me' ? 'Budget: guide me' : budget );
		}
		recapList.innerHTML = '';
		items.forEach( function ( text ) {
			var li = document.createElement( 'li' );
			li.textContent = text;
			recapList.appendChild( li );
		} );
		recap.hidden = ! items.length;
	}

	function setStep( n ) {
		root.setAttribute( 'data-ot-step', String( n ) );
		panels[ 1 ].hidden = 1 !== n;
		panels[ 2 ].hidden = 2 !== n;
		swaps.forEach( function ( el ) {
			var text = el.getAttribute( 'data-ot-step-' + n );
			if ( text ) {
				el.textContent = text;
			}
		} );
		segs.forEach( function ( seg, i ) {
			seg.classList.toggle( 'is-done', i < n - 1 );
			if ( i === n - 1 ) {
				seg.setAttribute( 'aria-current', 'step' );
			} else {
				seg.removeAttribute( 'aria-current' );
			}
		} );
		if ( 2 === n ) {
			fillRecap();
		}
		if ( backButton ) {
			backButton.hidden = 1 === n;
		}
		say( '' );
	}

	function go( n ) {
		setStep( n );
		window.scrollTo( { top: 0, behavior: 'smooth' } );
		if ( title ) {
			title.focus( { preventScroll: true } );
		}
	}

	// The script is running: one step at a time.
	if ( actions1 ) {
		actions1.hidden = false;
	}
	var hadServerErrors = !! form.querySelector( '[data-ot-error]' );
	var step2Error = form.querySelector( '[data-ot-panel="2"] [data-ot-error]' );
	setStep( hadServerErrors && step2Error && ! form.querySelector( '[data-ot-panel="1"] [data-ot-error]' ) ? 2 : 1 );

	if ( continueButton ) {
		continueButton.addEventListener( 'click', function () {
			if ( validateStep1() ) {
				track( 'inquiry_step2', { trip_type: ( checked( 'trip_type' ) || {} ).value || '' } );
				go( 2 );
			}
		} );
	}
	[ backButton, recapBack ].forEach( function ( b ) {
		if ( b ) {
			b.addEventListener( 'click', function () {
				go( 1 );
			} );
		}
	} );

	/* ---- "A cruise" is answered here, not stored ----------------------- */

	function applyCruise() {
		var type = checked( 'trip_type' );
		var isCruise = !! type && type.value === 'cruise';
		if ( cruise ) {
			cruise.hidden = ! isCruise;
		}
		if ( continueButton ) {
			continueButton.disabled = isCruise;
		}
	}

	/* ---- contact method → one field ------------------------------------ */

	function applyMethod() {
		var method = checked( 'contact_method' );
		var key = method ? method.value : '';
		if ( contactField ) {
			contactField.hidden = ! key;
		}
		if ( contactLabel && key ) {
			contactLabel.textContent = contactLabel.getAttribute( 'data-ot-' + key ) || contactLabel.textContent;
		}
		if ( contactHelp ) {
			contactHelp.textContent = key ? ( contactHelp.getAttribute( 'data-ot-' + key ) || '' ) : '';
		}
		if ( contactInput && key ) {
			contactInput.type = 'email' === key ? 'email' : 'tel';
			contactInput.setAttribute( 'inputmode', 'email' === key ? 'email' : 'tel' );
			contactInput.setAttribute( 'autocomplete', 'email' === key ? 'email' : 'tel' );
		}
		// Trip notes need an address; the tick only makes sense with email.
		if ( newsletterRow ) {
			newsletterRow.hidden = 'email' !== key;
		}
	}

	form.addEventListener( 'change', function ( e ) {
		var t = e.target;
		if ( ! t || ! t.name ) {
			return;
		}
		if ( t.type === 'radio' || t.type === 'checkbox' ) {
			clearError( t.name.replace( /\[\]$/, '' ) );
		}
		if ( t.name === 'trip_type' ) {
			applyCruise();
		}
		if ( t.name === 'contact_method' ) {
			applyMethod();
			if ( contactInput ) {
				contactInput.focus();
			}
		}
	} );
	form.addEventListener( 'input', function ( e ) {
		var t = e.target;
		if ( t && t.name && t.type !== 'radio' && t.type !== 'checkbox' && t.value.trim() ) {
			clearError( t.name );
		}
	} );
	applyCruise();
	applyMethod();

	/* ---- first touch: the clock, the event, a fresh nonce ---------------- */

	var startedAt = null;
	var started = false;
	function begin() {
		if ( started ) {
			return;
		}
		started = true;
		startedAt = Date.now();
		track( 'inquiry_started', { source: source && source.value ? 'internal' : 'direct' } );

		if ( ! nonceField || ! nonceApi || ! window.fetch ) {
			return;
		}
		// A signed-in visitor (Eric checking the form) keeps the nonce the
		// page was rendered with: the REST call carries no wp_rest nonce, so
		// WordPress answers it as logged-out and that nonce would fail here.
		if ( document.body.classList.contains( 'logged-in' ) ) {
			return;
		}
		window.fetch( nonceApi, { headers: { Accept: 'application/json' }, cache: 'no-store' } )
			.then( function ( res ) { return res.ok ? res.json() : null; } )
			.then( function ( body ) {
				if ( body && body.nonce ) {
					nonceField.value = body.nonce;
				}
			} )
			.catch( function () { /* the server-rendered nonce stays. */ } );
	}
	form.addEventListener( 'focusin', begin, { once: true } );
	form.addEventListener( 'change', begin, { once: true } );

	/* ---- send -------------------------------------------------------- */

	form.addEventListener( 'submit', function ( e ) {
		var type = checked( 'trip_type' );
		if ( type && type.value === 'cruise' ) {
			// The server would redirect too; the link is already on the page.
			e.preventDefault();
			go( 1 );
			applyCruise();
			return;
		}
		if ( ! validateStep1() ) {
			e.preventDefault();
			go( 1 );
			return;
		}
		if ( ! validateStep2() ) {
			e.preventDefault();
			return;
		}
		if ( elapsed ) {
			elapsed.value = startedAt === null ? '' : String( Date.now() - startedAt );
		}

		// The optional trip-notes tick: a separate signup, straight to
		// PlainSend from the browser (D30), double opt-in and all. Fire and
		// forget; the enquiry does not wait for it.
		var optIn = form.querySelector( '[name="newsletter_opt_in"]' );
		var method = checked( 'contact_method' );
		if ( optIn && optIn.checked && method && method.value === 'email' && newsletterEndpoint && window.fetch ) {
			try {
				var body = new URLSearchParams();
				body.set( 'email', contactInput.value.trim() );
				body.set( 'elapsed_ms', elapsed ? elapsed.value : '' );
				window.fetch( newsletterEndpoint, {
					method: 'POST',
					headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' },
					body: body.toString(),
					keepalive: true
				} ).catch( function () {} );
			} catch ( err ) {
				// Never in the way of the enquiry.
			}
		}

		if ( send ) {
			send.disabled = true;
			send.innerHTML = '<span class="ot-btn__label">' + ( send.getAttribute( 'data-ot-sending' ) || 'Sending…' ) + '</span>';
		}
		say( 'Sending your request…' );
	} );

	// Re-enable the button when the browser restores the page from history.
	window.addEventListener( 'pageshow', function ( ev ) {
		if ( ev.persisted && send ) {
			send.disabled = false;
			send.innerHTML = sendLabel;
		}
	} );
}() );
