<?php
if ( ! function_exists( 'woo_ce_get_export_type_attribute_count' ) ) {
    /**
     * Retrieves the count of exportable attribute types.
     *
     * @return int The count of exportable attribute types.
     */
    function woo_ce_get_export_type_attribute_count() {

        $count      = 0;
        $attributes = ( function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array() );
        $count      = count( $attributes );
        return $count;
    }
}

/**
 * Returns a list of Attribute export columns.
 *
 * @param string $format The format of the export columns. Default is 'full'.
 * @param int    $post_ID The ID of the post. Default is 0.
 * @return array The list of export columns.
 */
function woo_ce_get_attribute_fields( $format = 'full', $post_ID = 0 ) {

    $export_type = 'attribute';

    $fields   = array();
    $fields[] = array(
        'name'  => 'attribute_id',
        'label' => __( 'Attribute ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'attribute_label',
        'label' => __( 'Attribute Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'attribute_name',
        'label' => __( 'Attribute Slug', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'attribute_type',
        'label' => __( 'Attribute Type', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'attribute_public',
        'label' => __( 'Enable Archives', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'attribute_orderby',
        'label' => __( 'Attribute Sorting', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'attribute_count',
        'label' => __( 'Attribute Count', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'taxonomy',
        'label' => __( 'Taxonomy', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'term_id',
        'label' => __( 'Term ID', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'term_name',
        'label' => __( 'Term Name', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'term_slug',
        'label' => __( 'Term Slug', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'term_description',
        'label' => __( 'Term Description', 'woocommerce-exporter' ),
    );
    $fields[] = array(
        'name'  => 'term_count',
        'label' => __( 'Term Count', 'woocommerce-exporter' ),
    );

    /*
    $fields[] = array(
        'name' => '',
        'label' => __( '', 'woocommerce-exporter' )
    );
    */

    // Drop in our content filters here.
    add_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Allow Plugin/Theme authors to add support for additional columns.
    $fields = apply_filters( sprintf( WOO_CE_PREFIX . '_%s_fields', $export_type ), $fields, $export_type );

    // Remove our content filters here to play nice with other Plugins.
    remove_filter( 'sanitize_key', 'woo_ce_filter_sanitize_key' );

    // Check if we're dealing with an Export Template.
    $sorting = false;
    if ( ! empty( $post_ID ) ) {
        $remember = get_post_meta( $post_ID, sprintf( '_%s_fields', $export_type ), true );
        $hidden   = get_post_meta( $post_ID, sprintf( '_%s_hidden', $export_type ), false );
        $sorting  = get_post_meta( $post_ID, sprintf( '_%s_sorting', $export_type ), true );
    } else {
        $remember = woo_ce_get_option( $export_type . '_fields', array() );
        $hidden   = woo_ce_get_option( $export_type . '_hidden', array() );
    }
    if ( ! empty( $remember ) ) {
        $remember = maybe_unserialize( $remember );
        $hidden   = maybe_unserialize( $hidden );
        $size     = count( $fields );
        for ( $i = 0; $i < $size; $i++ ) {
            $fields[ $i ]['disabled'] = ( isset( $fields[ $i ]['disabled'] ) ? $fields[ $i ]['disabled'] : 0 );
            $fields[ $i ]['hidden']   = ( isset( $fields[ $i ]['hidden'] ) ? $fields[ $i ]['hidden'] : 0 );
            $fields[ $i ]['default']  = 1;
            if ( isset( $fields[ $i ]['name'] ) ) {
                // If not found turn off default.
                if ( ! array_key_exists( $fields[ $i ]['name'], $remember ) ) {
                    $fields[ $i ]['default'] = 0;
                }
                // Remove the field from exports if found.
                if ( array_key_exists( $fields[ $i ]['name'], $hidden ) ) {
                    $fields[ $i ]['hidden'] = 1;
                }
            }
        }
    }

    switch ( $format ) {

        case 'summary':
            $output = array();
            $size   = count( $fields );
            for ( $i = 0; $i < $size; $i++ ) {
                if ( isset( $fields[ $i ] ) ) {
                    $output[ $fields[ $i ]['name'] ] = 'on';
                }
            }
            return $output;
            break;

        case 'full':
        default:
            // Load the default sorting.
            if ( empty( $sorting ) ) {
                $sorting = woo_ce_get_option( sprintf( '%s_sorting', $export_type ), array() );
            }
            $size = count( $fields );
            for ( $i = 0; $i < $size; $i++ ) {
                if ( ! isset( $fields[ $i ]['name'] ) ) {
                    unset( $fields[ $i ] );
                    continue;
                }
                $fields[ $i ]['reset'] = $i;
                $fields[ $i ]['order'] = ( isset( $sorting[ $fields[ $i ]['name'] ] ) ? $sorting[ $fields[ $i ]['name'] ] : $i );
            }
            // Check if we are using PHP 5.3 and above.
            if ( version_compare( phpversion(), '5.3' ) >= 0 ) {
                usort( $fields, woo_ce_sort_fields( 'order' ) );
            }
            return $fields;
            break;
    }
}

/**
 * Overrides field labels from the Field Editor if necessary.
 *
 * @param array $fields The array of fields.
 * @return array The modified array of fields with overridden labels.
 */
function woo_ce_override_attribute_field_labels( $fields = array() ) {

    global $export;

    $export_type = 'attribute';

    $labels = false;

    // Check if this is a Quick Export or CRON export.
    if ( isset( $export->export_template ) ) {
        $export_template = $export->export_template;
        if ( ! empty( $export_template ) ) {
            $labels = get_post_meta( $export_template, sprintf( '_%s_labels', $export_type ), true );
        }
    }

    // Check if this is a Scheduled Export.
    $scheduled_export = absint( get_transient( WOO_CE_PREFIX . '_scheduled_export_id' ) );
    if ( $scheduled_export ) {
        $export_fields = get_post_meta( $scheduled_export, '_export_fields', true );
        if ( 'template' == $export_fields ) {
            $export_template = get_post_meta( $scheduled_export, '_export_template', true );
            if ( ! empty( $export_template ) ) {
                $labels = get_post_meta( $export_template, sprintf( '_%s_labels', $export_type ), true );
            }
        }
    }

    // Default to Quick Export labels.
    if ( empty( $labels ) ) {
        $labels = woo_ce_get_option( sprintf( '%s_labels', $export_type ), array() );
    }

    if ( ! empty( $labels ) ) {
        foreach ( $fields as $key => $field ) {
            if ( isset( $labels[ $field['name'] ] ) ) {
                $fields[ $key ]['label'] = $labels[ $field['name'] ];
            }
        }
    }
    return $fields;
}
add_filter( 'woo_ce_attribute_fields', 'woo_ce_override_attribute_field_labels', 11 );

/**
 * Returns the export column header label based on an export column slug.
 *
 * @param string|null $name   The name of the export column slug.
 * @param string      $format The format of the output. Default is 'name'.
 *
 * @return string|array The export column header label or the full export column details.
 */
function woo_ce_get_attribute_field( $name = null, $format = 'name' ) {

    global $export;

    $output = '';
    if ( $name ) {
        $fields = woo_ce_get_attribute_fields();
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'woo_ce_get_attribute_field() > woo_ce_get_attribute_fields(): ' . ( time() - $export->start_time ) ) );
        }
        $size = count( $fields );
        for ( $i = 0; $i < $size; $i++ ) {
            if ( $fields[ $i ]['name'] == $name ) {
                switch ( $format ) {

                    case 'name':
                        $output = $fields[ $i ]['label'];

                        // Allow Plugin/Theme authors to easily override export field labels.
                        $output = apply_filters( 'woo_ce_get_attribute_field_label', $output );
                        break;

                    case 'full':
                        $output = $fields[ $i ];
                        break;
                }
                $i = $size;
            }
        }
    }
    return $output;
}

/**
 * Retrieves the attributes for exporting.
 *
 * This function retrieves the attributes to be exported. It first checks if the attributes are stored in the transient cache. If not, it retrieves the attributes using the `wc_get_attribute_taxonomies()` function. If that fails, it falls back to querying the `wp_woocommerce_attribute_taxonomies` table directly.
 *
 * @param array $args   Optional. Additional arguments for retrieving the attributes. Default is an empty array.
 * @param array $export Optional. The export object. Default is an empty array.
 * @return array An array of attributes to be exported, including additional attribute details and attribute terms.
 */
function woo_ce_get_attributes( $args = array(), $export = array() ) {
    if ( empty( $export ) ) {
        global $export;
    }

    $attributes = get_transient( 'wc_attribute_taxonomies' );
    if ( false === $attributes ) {
        $attributes = ( function_exists( 'wc_get_attribute_taxonomies' ) ? wc_get_attribute_taxonomies() : array() );
        if ( WOO_CE_LOGGING ) {
            woo_ce_error_log( sprintf( 'Debug: %s', 'wc_get_attribute_taxonomies(): ' . ( time() - $export->start_time ) ) );
        }
    }

    // Fallback when wc_get_attribute_taxonomies() fails.
    if ( empty( $attributes ) ) {

        global $wpdb;

        $output = array();
        // Check if there are any records in wp_woocommerce_attribute_taxonomies.
        if ( $wpdb->get_var( "SHOW TABLES LIKE '{$wpdb->prefix}woocommerce_attribute_taxonomies';" ) ) {
            $attributes = $wpdb->get_results( 'SELECT * FROM `' . $wpdb->prefix . 'woocommerce_attribute_taxonomies`' );
            $wpdb->flush();
            if ( WOO_CE_LOGGING ) {
                if ( isset( $export->start_time ) ) {
                    woo_ce_error_log( sprintf( 'Debug: %s', 'attributes_sql: ' . ( time() - $export->start_time ) ) );
                }
            }
        }
    }

    if ( ! empty( $attributes ) && is_wp_error( $attributes ) == false ) {
        $output = array();
        // Add additional Attribute details to the Attributes.
        foreach ( $attributes as $key => $attribute ) {
            $attributes[ $key ]->attribute_type    = woo_ce_format_attribute_type_label( $attribute->attribute_type );
            $attributes[ $key ]->attribute_orderby = woo_ce_format_attribute_sorting_label( $attribute->attribute_orderby );
            $attributes[ $key ]->attribute_public  = woo_ce_format_switch( $attribute->attribute_public );
            $attributes[ $key ]->taxonomy          = __( 'Taxonomy', 'woocommerce-exporter' );

            // Allow Plugin/Theme authors to add support for additional Attribute columns.
            $attributes[ $key ] = apply_filters( 'woo_ce_attribute_item', $attributes[ $key ] );
        }
        foreach ( $attributes as $key => $attribute ) {
            $output[] = $attributes[ $key ];
            // Populate the list of Attribute Terms.
            $terms                               = woo_ce_get_attribute_terms( $attribute->attribute_name );
            $attributes[ $key ]->attribute_count = count( $terms );
            if ( ! empty( $terms ) ) {
                foreach ( $terms as $term ) {
                    $output[] = (object) array_merge( (array) $attribute, (array) $term );
                }
            }
            unset( $terms );
        }
        return $output;
    }
}

/**
 * Retrieves the terms of a specific WooCommerce attribute.
 *
 * @param string $attribute_slug The slug of the attribute.
 * @return array|WP_Error|null An array of terms on success, WP_Error if there was an error, or null if the attribute slug is empty.
 */
function woo_ce_get_attribute_terms( $attribute_slug = '' ) {

    if ( empty( $attribute_slug ) ) {
        return;
    }

    $term_taxonomy = sprintf( 'pa_%s', $attribute_slug );
    $args          = array(
        'taxonomy'   => $term_taxonomy,
        'orderby'    => 'name',
        'order'      => 'ASC',
        'hide_empty' => 0,
    );
    $terms         = get_terms( $args );
    if ( ! empty( $terms ) && is_wp_error( $terms ) == false ) {
        $size = count( $terms );
        for ( $i = 0; $i < $size; $i++ ) {
            $terms[ $i ]->taxonomy         = __( 'Term', 'woocommerce-exporter' );
            $terms[ $i ]->term_name        = $terms[ $i ]->name;
            $terms[ $i ]->term_slug        = $terms[ $i ]->slug;
            $terms[ $i ]->term_description = $terms[ $i ]->description;
            $terms[ $i ]->term_count       = $terms[ $i ]->count;
        }
    }
    return $terms;
}

if ( ! function_exists( 'woo_ce_export_dataset_override_attribute' ) ) {
    /**
     * Overrides the export dataset for attributes.
     *
     * This function exports the attributes dataset based on the specified export type.
     *
     * @param mixed  $output The output dataset to be exported. Default is null.
     * @param string $export_type The export type. Default is null.
     * @return mixed The exported dataset.
     */
    function woo_ce_export_dataset_override_attribute( $output = null, $export_type = null ) {

        global $export;

        $args = array();
        $attributes = woo_ce_get_attributes( $args );
        if ( $$attributes ) {
            $export->total_rows = count( $attributes );
            // XML, RSS and JSON export.
            if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ) ) ) {
                if ( ! empty( $export->fields ) ) {
                    foreach ( $attributes as $attribute ) {
                        if ( in_array( $export->export_format, array( 'xml', 'json' ) ) ) {
                            $child = $output->addChild( apply_filters( 'woo_ce_export_xml_attribute_node', sanitize_key( $export_type ) ) );
                        } elseif ( 'rss' == $export->export_format ) {
                            $child = $output->addChild( 'item' );
                        }
                        if (
                            'json' !== $export->export_format &&
                            apply_filters( 'woo_ce_export_xml_attribute_node_id_attribute', true )
                        ) {
                            $child->addAttribute( 'id', ( isset( $attribute->attribute_id ) ? $attribute->attribute_id : '' ) );
                        }
                        foreach ( array_keys( $export->fields ) as $key => $field ) {
                            if ( isset( $attribute->$field ) ) {
                                if ( ! is_array( $field ) ) {
                                    if ( woo_ce_is_xml_cdata( $attribute->$field ) ) {
                                        $child->addChild( apply_filters( 'woo_ce_export_xml_attribute_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $attribute->$field ) ) );
                                    } else {
                                        $child->addChild( apply_filters( 'woo_ce_export_xml_attribute_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $attribute->$field ) ) );
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                // PHPExcel export.
                $output = $attributes;
            }
            unset( $attributes, $attribute );
        }
        return $output;
    }
}

/**
 * Overrides the attribute export dataset for multisite.
 *
 * This function exports attributes for each site in a multisite network.
 *
 * @param mixed  $output The output data. Default is null.
 * @param string $export_type The export type. Default is null.
 * @return mixed The exported data.
 */
function woo_ce_export_dataset_multisite_override_attribute( $output = null, $export_type = null ) {

    global $export;

    $sites = get_sites();
    if ( ! empty( $sites ) ) {
        foreach ( $sites as $site ) {
            switch_to_blog( $site->blog_id );
            $args = array();
            $attributes = woo_ce_get_attributes( $args );
            if ( $attributes ) {
                $export->total_rows = count( $attributes );
                // XML, RSS and JSON export.
                if ( in_array( $export->export_format, array( 'xml', 'rss', 'json' ) ) ) {
                    if ( ! empty( $export->fields ) ) {
                        foreach ( $attributes as $attribute ) {
                            if ( in_array( $export->export_format, array( 'xml', 'json' ) ) ) {
                                $child = $output->addChild( apply_filters( 'woo_ce_export_xml_attribute_node', sanitize_key( $export_type ) ) );
                            } elseif ( 'rss' == $export->export_format ) {
                                $child = $output->addChild( 'item' );
                            }
                            if (
                                'json' !== $export->export_format &&
                                apply_filters( 'woo_ce_export_xml_attribute_node_id_attribute', true )
                            ) {
                                $child->addAttribute( 'id', ( isset( $attribute->attribute_id ) ? $attribute->attribute_id : '' ) );
                            }
                            foreach ( array_keys( $export->fields ) as $key => $field ) {
                                if ( isset( $attribute->$field ) ) {
                                    if ( ! is_array( $field ) ) {
                                        if ( woo_ce_is_xml_cdata( $attribute->$field ) ) {
                                            $child->addChild( apply_filters( 'woo_ce_export_xml_attribute_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ) )->addCData( esc_html( woo_ce_sanitize_xml_string( $attribute->$field ) ) );
                                        } else {
                                            $child->addChild( apply_filters( 'woo_ce_export_xml_attribute_label', sanitize_key( $export->columns[ $key ] ), $export->columns[ $key ] ), esc_html( woo_ce_sanitize_xml_string( $attribute->$field ) ) );
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ( is_null( $output ) ) { // PHPExcel export.
                    $output = $attributes;
                } else {
                    $output = array_merge( $output, $attributes );
                }
                unset( $attributes, $attribute );
            }
            restore_current_blog();
        }
    }
    return $output;
}

/**
 * Formats the label for an attribute type.
 *
 * @param string $attribute_type The attribute type.
 * @return string The formatted label for the attribute type.
 */
function woo_ce_format_attribute_type_label( $attribute_type = '' ) {

    $output = $attribute_type;
    switch ( $attribute_type ) {

        case 'text':
            $output = __( 'Text', 'woocommerce-exporter' );
            break;

        case 'select':
            $output = __( 'Select', 'woocommerce-exporter' );
            break;
    }
    return $output;
}

/**
 * Formats the attribute sorting label based on the given attribute sorting value.
 *
 * @param string $attribute_sorting The attribute sorting value.
 * @return string The formatted attribute sorting label.
 */
function woo_ce_format_attribute_sorting_label( $attribute_sorting = 'menu_order' ) {

    $output = $attribute_sorting;
    switch ( $attribute_sorting ) {

        case 'menu_order':
            $output = __( 'Custom ordering', 'woocommerce-exporter' );
            break;

        case 'name':
            $output = __( 'Name', 'woocommerce-exporter' );
            break;

        case 'name_num':
            $output = __( 'Name (numeric)', 'woocommerce-exporter' );
            break;

        case 'id':
            $output = __( 'Term ID', 'woocommerce-exporter' );
            break;
    }
    return $output;
}
