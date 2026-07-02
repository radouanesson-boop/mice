/**
 * DMF admin — media pickers for attachment & gallery fields.
 * Vanilla JS on top of wp.media; no jQuery of our own.
 */
( () => {
	'use strict';

	if ( typeof wp === 'undefined' || ! wp.media ) {
		return;
	}

	document.addEventListener( 'click', ( event ) => {
		const pick = event.target.closest( '.dmf-attachment-pick, .dmf-gallery-pick' );
		const clear = event.target.closest( '.dmf-attachment-clear' );

		if ( clear ) {
			const wrap = clear.closest( '.dmf-attachment' );
			wrap.querySelector( 'input[type="hidden"]' ).value = '';
			wrap.querySelector( '.dmf-attachment-label' ).textContent = '';
			clear.style.display = 'none';
			event.preventDefault();
			return;
		}

		if ( ! pick ) {
			return;
		}

		event.preventDefault();

		const isGallery = pick.classList.contains( 'dmf-gallery-pick' );
		const wrap = pick.closest( isGallery ? '.dmf-gallery' : '.dmf-attachment' );
		const input = wrap.querySelector( 'input[type="hidden"]' );

		const frame = wp.media( {
			multiple: isGallery,
			library: { type: ! isGallery && wrap.dataset.type === 'image' ? 'image' : undefined },
		} );

		frame.on( 'select', () => {
			const selection = frame.state().get( 'selection' );

			if ( isGallery ) {
				const ids = selection.map( ( att ) => att.id );
				input.value = ids.join( ',' );
				wrap.querySelector( '.dmf-gallery-count' ).textContent = `${ ids.length } ✓`;
			} else {
				const att = selection.first();
				input.value = att.id;
				wrap.querySelector( '.dmf-attachment-label' ).textContent =
					att.get( 'filename' ) || att.get( 'title' ) || `#${ att.id }`;
				const clearBtn = wrap.querySelector( '.dmf-attachment-clear' );
				if ( clearBtn ) {
					clearBtn.style.display = '';
				}
			}
		} );

		frame.open();
	} );
} )();
