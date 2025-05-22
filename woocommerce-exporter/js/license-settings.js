jQuery( 'document' ).ready( function( $ ) {
    $( '#wsed-license-form' ).on( 'submit', function( e ) {
        e.preventDefault();

        const $_this             = $( this );    
        const $btn               = $( this ).find( '#activate_license' );
        const $spinner           = $( this ).find( '.input-button .spinner' );
        const $notification      = $( this ).closest( '.vl-license-settings-container' ).find( '.vl-license-notification' );
        const $status            = $( this ).closest( '#license-tabs' ).find( '.license-status .value' );
        const $activation_notice = $( '.wsed-activate-license-notice' );

        $_this.find( 'input' ).prop( 'disabled', true );

        $btn.prop( 'disabled', true );
        $spinner.css( 'visibility', 'visible' );

        $notification.find('.notice').removeClass( 'notice-success notice-error' );
        $notification.find('.notice').slideUp();

        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'wsed_activate_license',
                license_key: $( this ).find( '#license_key' ).val(),
                activation_email: $( this ).find( '#activation_email' ).val(),
                ajax_nonce: $( this ).find( '#wsed_activate_license_nonce' ).val()
            },
            dataType: 'json'
        }).done( function( response ) {
            if ( response.status === 'success' ) {
                $notification.find('.notice .message').html( response.message );
                $notification.find('.notice').addClass( 'notice-success' );
                $notification.find('.notice').slideDown();

                if ( $activation_notice.length ) {
                    $activation_notice.slideUp();
                }
            } else {
                $notification.find('.notice .message').html( response.message );
                $notification.find('.notice').addClass( 'notice-error' );
                $notification.find('.notice').slideDown();
            }

            if ( response.license_status != null ) {
                $status.removeClass( 'text-color-red text-color-green' );

                const license_status_i18n = {
                    expired: {
                        label: wsed_license_settings_args.i18n.expired + ' <small><i>(' + response.expired_date + ')</i></small>',
                        class: 'text-color-red',
                    },
                    inactive: {
                        label: wsed_license_settings_args.i18n.inactive,
                        class: 'text-color-red',
                    },
                    active: {
                        label: wsed_license_settings_args.i18n.active,
                        class: 'text-color-green',
                    },
                };

                $status.html( license_status_i18n[ response.license_status ][ 'label' ] );
                $status.addClass( license_status_i18n[ response.license_status ][ 'class' ] );
            }

        }).fail( function( jqXHR, textStatus, errorThrown ) {
            console.log( jqXHR );
            console.log( textStatus );
            console.log( errorThrown );
        }).always( function () {
            $_this.find( 'input' ).prop( 'disabled', false );
            $btn.prop( 'disabled', false );
            $spinner.css( 'visibility', 'hidden' );
        });
    });
    
    $( '.vl-license-settings.store-exporter .vl-license-notification .notice-dismiss' ).on( 'click', function( e ) {
        e.preventDefault();

        const $notice = $( this ).closest( '.notice' );
        
        $notice.slideUp();
        $notice.removeClass( 'notice-success notice-error' );
    });

    $( '.wsed-activate-license-notice' ).on( 'click' , '.notice-dismiss' , function() {
        $.post( window.ajaxurl, { action : 'wsed_slmw_dismiss_activate_notice' } );
    });
});