/* global jQuery, wooCeAcfwMarketing */
jQuery( function ( $ ) {
    var $button = $( '.woo-ce-acfw-install' );
    var $error = $( '.woo-ce-acfw-install-error' );

    /**
     * Show the install error and let the user try again.
     */
    function showError( message ) {
        $button.removeClass( 'is-busy' ).removeAttr( 'aria-disabled aria-busy' ).text( $button.data( 'label' ) );
        $error.text( message || wooCeAcfwMarketing.i18n.error ).prop( 'hidden', false );
    }

    /**
     * One-click install and activate Advanced Coupons from the Step 1 button.
     */
    $button.on( 'click', function ( event ) {
        event.preventDefault();

        if ( $button.hasClass( 'is-busy' ) ) {
            return;
        }

        $button.data( 'label', $button.text().trim() );
        $button.addClass( 'is-busy' ).attr( { 'aria-disabled': 'true', 'aria-busy': 'true' } ).text( wooCeAcfwMarketing.i18n.installing );
        $error.prop( 'hidden', true ).text( '' );

        $.ajax( {
            url: wooCeAcfwMarketing.ajaxUrl,
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'woo_ce_install_plugin',
                nonce: wooCeAcfwMarketing.nonce,
                plugin_slug: wooCeAcfwMarketing.pluginSlug,
                silent: 1,
            },
        } )
            .done( function ( response ) {
                if ( response && response.success ) {
                    window.location.href = wooCeAcfwMarketing.redirectUrl;
                    return;
                }

                showError( response && 'string' === typeof response.data ? response.data : '' );
            } )
            .fail( function ( jqXHR, textStatus ) {
                // Extra output from another plugin can break the JSON after a good install.
                // This page no longer exists once ACFW is active, so go to the success destination.
                if ( 'parsererror' === textStatus && 200 === jqXHR.status ) {
                    window.location.href = wooCeAcfwMarketing.redirectUrl;
                    return;
                }

                showError();
            } );
    } );
} );
