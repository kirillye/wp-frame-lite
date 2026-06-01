import '../scss/main.scss';

( function () {
	'use strict';

	const toggle   = document.querySelector( '.menu-toggle' );
	const sidebar  = document.getElementById( 'mobile-sidebar' );
	const closeBtn = document.querySelector( '.nav-close' );

	if ( ! toggle || ! sidebar ) return;

	const overlay = document.createElement( 'div' );
	overlay.className = 'nav-overlay';
	overlay.setAttribute( 'aria-hidden', 'true' );
	document.body.appendChild( overlay );

	function openNav() {
		document.body.classList.add( 'nav-open' );
		sidebar.setAttribute( 'aria-hidden', 'false' );
		toggle.setAttribute( 'aria-expanded', 'true' );
		toggle.setAttribute( 'aria-label', 'Закрыть меню' );
		document.body.style.overflow = 'hidden';
	}

	function closeNav() {
		document.body.classList.remove( 'nav-open' );
		sidebar.setAttribute( 'aria-hidden', 'true' );
		toggle.setAttribute( 'aria-expanded', 'false' );
		toggle.setAttribute( 'aria-label', 'Открыть меню' );
		document.body.style.overflow = '';
	}

	toggle.addEventListener( 'click', () => {
		document.body.classList.contains( 'nav-open' ) ? closeNav() : openNav();
	} );

	if ( closeBtn ) closeBtn.addEventListener( 'click', closeNav );
	overlay.addEventListener( 'click', closeNav );

	document.addEventListener( 'keydown', ( e ) => {
		if ( e.key === 'Escape' ) closeNav();
	} );

	window.addEventListener( 'resize', () => {
		if ( window.innerWidth >= 600 ) closeNav();
	} );

} )();
