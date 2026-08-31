<?php
/**
 * Real Estate Helper Tools and Utilities
 *
 * Provides helper functions for retrieving property metadata, formatting numbers,
 * currency, icons, query variables, and location taxonomies.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrieves essential property data for templates.
 *
 * @param int $post_id Optional. Post ID. Defaults to current post.
 * @return array Associative array with property fields.
 */
function novaconcierge_get_property_data( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$price             = get_post_meta( $post_id, 'eb_price', true ) ?: '';
	$currency          = get_post_meta( $post_id, 'eb_currency', true ) ?: 'MXN';
	$operation         = get_post_meta( $post_id, 'eb_operation', true ) ?: 'sale';
	$location          = get_post_meta( $post_id, 'eb_location', true ) ?: '';
	$property_type     = get_post_meta( $post_id, 'eb_property_type', true ) ?: 'house';
	$bedrooms          = get_post_meta( $post_id, 'eb_bedrooms', true ) ?: 0;
	$bathrooms         = get_post_meta( $post_id, 'eb_bathrooms', true ) ?: 0;
	$parking           = get_post_meta( $post_id, 'eb_parking', true ) ?: 0;
	$construction_size = get_post_meta( $post_id, 'eb_construction_size', true ) ?: 0;
	$lot_size          = get_post_meta( $post_id, 'eb_lot_size', true ) ?: 0;
	$public_id         = get_post_meta( $post_id, 'eb_public_id', true ) ?: '';
	$map_embed         = get_post_meta( $post_id, 'eb_map_embed', true ) ?: '';

	// Gallery images
	$gallery = get_post_meta( $post_id, 'eb_gallery', true );
	if ( ! is_array( $gallery ) ) {
		$gallery = ! empty( $gallery ) ? (array) $gallery : array();
	}

	// Fallback to featured image if gallery is empty
	if ( empty( $gallery ) && has_post_thumbnail( $post_id ) ) {
		$featured_url = get_the_post_thumbnail_url( $post_id, 'full' );
		if ( $featured_url ) {
			$gallery[] = $featured_url;
		}
	}

	return array(
		'price'             => $price,
		'currency'          => $currency,
		'operation'         => $operation,
		'location'          => $location,
		'type'              => $property_type,
		'bedrooms'          => $bedrooms,
		'bathrooms'         => $bathrooms,
		'parking'           => $parking,
		'construction_size' => $construction_size,
		'lot_size'          => $lot_size,
		'public_id'         => $public_id,
		'map_embed'         => $map_embed,
		'gallery'           => $gallery,
	);
}

/**
 * Returns full property metadata list items.
 *
 * @param int $post_id Optional. Post ID. Defaults to current post.
 * @return array Array of metadata items with icon, label and value.
 */
function novaconcierge_get_full_property_metadata( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$data = novaconcierge_get_property_data( $post_id );
	$items = array();

	if ( ! empty( $data['public_id'] ) ) {
		$items[] = array(
			'icon'  => 'id',
			'label' => __( 'Clave / ID', 'novaconcierge' ),
			'value' => esc_html( $data['public_id'] ),
		);
	}

	if ( ! empty( $data['type'] ) ) {
		$items[] = array(
			'icon'  => 'condo',
			'label' => __( 'Tipo', 'novaconcierge' ),
			'value' => esc_html( novaconcierge_translate_property_type( $data['type'] ) ),
		);
	}

	if ( ! empty( $data['bedrooms'] ) && $data['bedrooms'] > 0 ) {
		$items[] = array(
			'icon'  => 'bedroom',
			'label' => __( 'Recámaras', 'novaconcierge' ),
			'value' => sprintf( _n( '%s recámara', '%s recámaras', $data['bedrooms'], 'novaconcierge' ), $data['bedrooms'] ),
		);
	}

	if ( ! empty( $data['bathrooms'] ) && $data['bathrooms'] > 0 ) {
		$items[] = array(
			'icon'  => 'bathroom',
			'label' => __( 'Baños', 'novaconcierge' ),
			'value' => sprintf( '%s %s', $data['bathrooms'], __( 'baños', 'novaconcierge' ) ),
		);
	}

	if ( ! empty( $data['parking'] ) && $data['parking'] > 0 ) {
		$items[] = array(
			'icon'  => 'parking',
			'label' => __( 'Estacionamientos', 'novaconcierge' ),
			'value' => sprintf( _n( '%s estacionamiento', '%s estacionamientos', $data['parking'], 'novaconcierge' ), $data['parking'] ),
		);
	}

	if ( ! empty( $data['construction_size'] ) && $data['construction_size'] > 0 ) {
		$items[] = array(
			'icon'  => 'construction',
			'label' => __( 'Construcción', 'novaconcierge' ),
			'value' => novaconcierge_format_numeric( $data['construction_size'] ) . ' m²',
		);
	}

	if ( ! empty( $data['lot_size'] ) && $data['lot_size'] > 0 ) {
		$items[] = array(
			'icon'  => 'lot',
			'label' => __( 'Terreno', 'novaconcierge' ),
			'value' => novaconcierge_format_numeric( $data['lot_size'] ) . ' m²',
		);
	}

	if ( ! empty( $data['location'] ) ) {
		$items[] = array(
			'icon'  => 'location',
			'label' => __( 'Ubicación', 'novaconcierge' ),
			'value' => esc_html( $data['location'] ),
		);
	}

	return $items;
}

/**
 * Render full property metadata list as HTML <li> elements (matching Avante design).
 *
 * @param int $post_id Optional. Post ID. Defaults to current post.
 */
function novaconcierge_render_full_property_metadata( $post_id = 0 ) {
	if ( ! $post_id ) {
		$post_id = get_the_ID();
	}

	$data = novaconcierge_get_property_data( $post_id );

	// ID
	if ( ! empty( $data['public_id'] ) ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_icon( 'id' ) . '</span> ';
		echo 'ID: ' . esc_html( $data['public_id'] );
		echo '</li>';
	}

	// Location
	if ( ! empty( $data['location'] ) ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_icon( 'location' ) . '</span> ';
		echo esc_html( $data['location'] );
		echo '</li>';
	}

	// Property Type
	if ( ! empty( $data['type'] ) ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_property_type_icon( $data['type'] ) . '</span> ';
		echo 'Tipo: ' . esc_html( novaconcierge_translate_property_type( $data['type'] ) );
		echo '</li>';
	}

	// Operation
	if ( ! empty( $data['operation'] ) ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_icon( 'sale' ) . '</span> ';
		echo ( 'rental' === $data['operation'] ? 'En renta' : ( 'temporary_rental' === $data['operation'] ? 'Renta temporal' : 'En venta' ) );
		echo '</li>';
	}

	// Price
	if ( ! empty( $data['price'] ) ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_icon( 'price' ) . '</span> ';
		echo 'Precio: ' . esc_html( novaconcierge_format_price( $data['price'], $data['currency'] ) );
		echo '</li>';
	}

	// Bedrooms
	if ( ! empty( $data['bedrooms'] ) && $data['bedrooms'] > 0 ) {
		echo '<li class="bedroom">';
		echo '<span>' . novaconcierge_get_icon( 'bedroom' ) . '</span> ';
		echo esc_html( $data['bedrooms'] ) . ' ' . ( $data['bedrooms'] < 2 ? 'recámara' : 'recámaras' );
		echo '</li>';
	}

	// Bathrooms
	if ( ! empty( $data['bathrooms'] ) && $data['bathrooms'] > 0 ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_icon( 'bathroom' ) . '</span> ';
		echo esc_html( $data['bathrooms'] ) . ' ' . ( $data['bathrooms'] < 2 ? 'baño' : 'baños' );
		echo '</li>';
	}

	// Parking
	if ( ! empty( $data['parking'] ) && $data['parking'] > 0 ) {
		echo '<li class="parking">';
		echo '<span>' . novaconcierge_get_icon( 'parking' ) . '</span> ';
		echo esc_html( $data['parking'] ) . ' ' . ( $data['parking'] < 2 ? 'estacionamiento' : 'estacionamientos' );
		echo '</li>';
	}

	// Construction size
	if ( ! empty( $data['construction_size'] ) && $data['construction_size'] > 0 ) {
		echo '<li>';
		echo '<span>' . novaconcierge_get_icon( 'construction' ) . '</span> ';
		echo esc_html( novaconcierge_format_numeric( $data['construction_size'] ) ) . ' m² de construcción';
		echo '</li>';
	}

	// Lot size
	if ( ! empty( $data['lot_size'] ) && $data['lot_size'] > 0 ) {
		echo '<li class="lot">';
		echo '<span>' . novaconcierge_get_icon( 'lot' ) . '</span> ';
		echo esc_html( novaconcierge_format_numeric( $data['lot_size'] ) ) . ' m² de terreno';
		echo '</li>';
	}
}

/**
 * Format numeric price into currency string.
 *
 * @param mixed $price Numeric or string price.
 * @param string $currency Currency code (MXN, USD, EUR).
 * @return string Formatted price string.
 */
function novaconcierge_format_price( $price, $currency = 'MXN' ) {
	if ( empty( $price ) ) {
		return __( 'Precio a consultar', 'novaconcierge' );
	}

	// If already formatted with currency symbol, return cleaned string
	if ( is_string( $price ) && ( strpos( $price, '$' ) !== false || strpos( $price, '€' ) !== false ) ) {
		return $price;
	}

	$numeric = floatval( preg_replace( '/[^\d.]/', '', str_replace( ',', '', $price ) ) );
	if ( $numeric <= 0 ) {
		return __( 'Precio a consultar', 'novaconcierge' );
	}

	$symbol = ( $currency === 'EUR' ) ? '€' : '$';
	return $symbol . number_format( $numeric, 0, '.', ',' ) . ' ' . $currency;
}

/**
 * Format numeric value with thousands separator.
 *
 * @param mixed $num Numeric value.
 * @return string Formatted string.
 */
function novaconcierge_format_numeric( $num ) {
	$numeric = floatval( preg_replace( '/[^\d.]/', '', str_replace( ',', '', $num ) ) );
	return number_format( $numeric, ( floor( $numeric ) == $numeric ? 0 : 1 ), '.', ',' );
}

/**
 * Translates property type key to Spanish readable string.
 *
 * @param string $type Property type slug.
 * @return string Localized property type.
 */
function novaconcierge_translate_property_type( $type ) {
	$translations = array(
		'house'                => __( 'Casa', 'novaconcierge' ),
		'apartment'            => __( 'Departamento', 'novaconcierge' ),
		'condo'                => __( 'Condominio', 'novaconcierge' ),
		'land'                 => __( 'Terreno', 'novaconcierge' ),
		'commercial'           => __( 'Local Comercial', 'novaconcierge' ),
		'office'               => __( 'Oficina', 'novaconcierge' ),
		'warehouse'            => __( 'Bodega', 'novaconcierge' ),
		'industrial_warehouse' => __( 'Nave Industrial', 'novaconcierge' ),
		'building'             => __( 'Edificio', 'novaconcierge' ),
		'villa'                => __( 'Villa', 'novaconcierge' ),
		'penthouse'            => __( 'Penthouse', 'novaconcierge' ),
	);

	return $translations[ strtolower( $type ) ] ?? ucfirst( str_replace( '_', ' ', $type ) );
}

/**
 * Returns available property types for filter forms.
 *
 * @return array Associative array of property type slugs and labels.
 */
function novaconcierge_get_property_types() {
	return array(
		'house'                => __( 'Casa', 'novaconcierge' ),
		'apartment'            => __( 'Departamento', 'novaconcierge' ),
		'land'                 => __( 'Terreno', 'novaconcierge' ),
		'commercial'           => __( 'Local Comercial', 'novaconcierge' ),
		'office'               => __( 'Oficina', 'novaconcierge' ),
		'warehouse'            => __( 'Bodega', 'novaconcierge' ),
		'industrial_warehouse' => __( 'Nave Industrial', 'novaconcierge' ),
		'building'             => __( 'Edificio', 'novaconcierge' ),
	);
}

/**
 * Returns operation types for filter forms.
 *
 * @return array Associative array of operation type slugs and labels.
 */
function novaconcierge_get_operation_types() {
	return array(
		'sale'             => __( 'En Venta', 'novaconcierge' ),
		'rental'           => __( 'En Renta', 'novaconcierge' ),
		'temporary_rental' => __( 'Renta Temporal', 'novaconcierge' ),
	);
}

/**
 * Retrieves unique location states/cities from published properties.
 *
 * @return array Array of location strings grouped or listed.
 */
function novaconcierge_get_property_locations() {
	$cached = get_transient( 'novaconcierge_property_locations' );
	if ( false !== $cached ) {
		return $cached;
	}

	$properties = get_posts( array(
		'post_type'      => 'property',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'fields'         => 'ids',
	) );

	$locations = array();
	if ( ! empty( $properties ) ) {
		foreach ( $properties as $id ) {
			$loc = get_post_meta( $id, 'eb_location', true );
			if ( ! empty( $loc ) ) {
				$parts = array_map( 'trim', explode( ',', $loc ) );
				$count = count( $parts );
				if ( $count >= 2 ) {
					$city  = $parts[ $count - 2 ];
					$state = $parts[ $count - 1 ];
					if ( $state && $city ) {
						$locations[ $state ][ $city ] = $city;
					}
				} else {
					$locations['General'][ $loc ] = $loc;
				}
			}
		}
	}

	// Sort states and cities
	ksort( $locations );
	foreach ( $locations as $state => &$cities ) {
		ksort( $cities );
	}

	set_transient( 'novaconcierge_property_locations', $locations, 12 * HOUR_IN_SECONDS );
	return $locations;
}

/**
 * Retrieves minimum and maximum property prices from database.
 *
 * @return array Array with 'min' and 'max' keys.
 */
function novaconcierge_get_property_price_range() {
	global $wpdb;

	$results = $wpdb->get_row( "
		SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_price, MAX(CAST(meta_value AS UNSIGNED)) as max_price
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'eb_price_num'
		AND p.post_type = 'property'
		AND p.post_status = 'publish'
		AND CAST(meta_value AS UNSIGNED) > 0
	" );

	$min = ( $results && ! empty( $results->min_price ) ) ? intval( $results->min_price ) : 100000;
	$max = ( $results && ! empty( $results->max_price ) ) ? intval( $results->max_price ) : 20000000;

	return array(
		'min' => $min,
		'max' => $max,
	);
}

/**
 * Retrieves minimum and maximum property construction size in m².
 *
 * @return array Array with 'min' and 'max' keys.
 */
function novaconcierge_get_property_construction_range() {
	global $wpdb;

	$results = $wpdb->get_row( "
		SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_val, MAX(CAST(meta_value AS UNSIGNED)) as max_val
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'eb_construction_size'
		AND p.post_type = 'property'
		AND p.post_status = 'publish'
		AND CAST(meta_value AS UNSIGNED) > 0
	" );

	$min = ( $results && ! empty( $results->min_val ) ) ? intval( $results->min_val ) : 50;
	$max = ( $results && ! empty( $results->max_val ) ) ? intval( $results->max_val ) : 1000;

	return array(
		'min' => $min,
		'max' => $max,
	);
}

/**
 * Retrieves minimum and maximum property land size in m².
 *
 * @return array Array with 'min' and 'max' keys.
 */
function novaconcierge_get_property_land_range() {
	global $wpdb;

	$results = $wpdb->get_row( "
		SELECT MIN(CAST(meta_value AS UNSIGNED)) as min_val, MAX(CAST(meta_value AS UNSIGNED)) as max_val
		FROM {$wpdb->postmeta} pm
		INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'eb_lot_size'
		AND p.post_type = 'property'
		AND p.post_status = 'publish'
		AND CAST(meta_value AS UNSIGNED) > 0
	" );

	$min = ( $results && ! empty( $results->min_val ) ) ? intval( $results->min_val ) : 50;
	$max = ( $results && ! empty( $results->max_val ) ) ? intval( $results->max_val ) : 2500;

	return array(
		'min' => $min,
		'max' => $max,
	);
}

/**
 * Returns SVG icons for real estate themes.
 *
 * @param string $type Icon identifier.
 * @return string SVG icon markup.
 */
function novaconcierge_get_icon( $type ) {
	$icons = array(
		'bedroom'          => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M2 19h20M2 14v5M22 14v5M4 14V8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v6M2 14h20"/><path d="M6 11h4M14 11h4"/></svg>',
		'bathroom'         => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 12h16a2 2 0 0 1 2 2v2a4 4 0 0 1-4 4H6a4 4 0 0 1-4-4v-2a2 2 0 0 1 2-2z"/><path d="M6 12V5a2 2 0 0 1 2-2h1a2 2 0 0 1 2 2v1"/><path d="M4 20l-1 2M20 20l1 2"/></svg>',
		'construction'     => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9M15 21V9M3 15h18"/></svg>',
		'lot'              => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6l9-4 9 4v12l-9 4-9-4V6z"/><path d="M3 6l9 4 9-4M12 10v12"/></svg>',
		'parking'          => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><path d="M9 17V7h4.5a3 3 0 0 1 0 6H9"/></svg>',
		'location'         => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21c4-4.5 7-8.5 7-12A7 7 0 0 0 5 9c0 3.5 3 7.5 7 12z"/><circle cx="12" cy="9" r="2.5"/></svg>',
		'id'               => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="3"/><path d="M7 8h10M7 12h6M7 16h4"/></svg>',
		'condo'            => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1M9 11h1M9 15h1M14 7h1M14 11h1M14 15h1"/></svg>',
		'home'             => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
		'store'            => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l2-5h14l2 5M21 9v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V9M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/></svg>',
		'garden'           => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22v-9M12 13a5 5 0 0 0-5-5c0 5 5 5 5 5zM12 13a5 5 0 0 1 5-5c0 5-5 5-5 5z"/></svg>',
		'warehouse'        => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 8.35V20a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V8.35A2 2 0 0 1 3.26 6.5l8-3.2a2 2 0 0 1 1.48 0l8 3.2A2 2 0 0 1 22 8.35z"/><path d="M6 18h12M6 14h12"/></svg>',
		'price'            => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
		'size'             => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 3 21 3 21 9"/><polyline points="9 21 3 21 3 15"/><line x1="21" y1="3" x2="14" y2="10"/><line x1="3" y1="21" x2="10" y2="14"/></svg>',
		'sale'             => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
		'rent'             => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
		'plus'             => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
		'minus'            => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/></svg>',
		'plus-circle'      => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
		'chevron-down'     => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>',
		'close'            => '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
		'backward'         => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 8 8 12 12 16"/><line x1="16" y1="12" x2="8" y2="12"/></svg>',
		'forward'          => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 16 16 12 12 8"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
		'search'           => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>',
		'filter'           => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>',
		'reset'            => '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/></svg>',
	);

	return $icons[ $type ] ?? '';
}

/**
 * Returns the icon key corresponding to a property type.
 *
 * @param string $property_type Slug or key for property type.
 * @return string Icon identifier.
 */
function novaconcierge_get_property_type_icon_key( $property_type ) {
	switch ( $property_type ) {
		case 'apartment':
		case 'building':
		case 'departamento':
		case 'edificio':
		case 'condo':
			return 'condo';

		case 'land':
		case 'terreno':
		case 'lot':
		case 'lote':
			return 'garden';

		case 'commercial':
		case 'local':
		case 'office':
		case 'oficina':
		case 'store':
			return 'store';

		case 'warehouse':
		case 'bodega':
		case 'industrial_warehouse':
			return 'warehouse';

		case 'house':
		case 'casa':
		default:
			return 'home';
	}
}

/**
 * Returns SVG markup for a property type icon.
 *
 * @param string $property_type Slug or key for property type.
 * @return string SVG icon markup.
 */
function novaconcierge_get_property_type_icon( $property_type ) {
	$icon_key = novaconcierge_get_property_type_icon_key( $property_type );
	return novaconcierge_get_icon( $icon_key );
}

/**
 * Renders pagination matching the Stories theme pagination style and options.
 *
 * @param WP_Query|null $query Custom query or null for global wp_query.
 * @param int $paged Current page number.
 */
function novaconcierge_pagination( $query = null, $paged = 1 ) {
	$theme_options    = get_option( 'stories_theme_options', array() );
	$pagination_style = ! empty( $theme_options['pagination_style'] ) ? $theme_options['pagination_style'] : 'default';

	$prev_icon = function_exists( 'stories_get_svg' ) ? stories_get_svg( 'arrow-left-circle', array( 'size' => 18 ) ) : novaconcierge_get_icon( 'backward' );
	$next_icon = function_exists( 'stories_get_svg' ) ? stories_get_svg( 'arrow-right-circle', array( 'size' => 18 ) ) : novaconcierge_get_icon( 'forward' );

	$max_pages = $query ? $query->max_num_pages : ( $GLOBALS['wp_query']->max_num_pages ?? 1 );
	if ( $max_pages <= 1 ) {
		return;
	}

	$links = paginate_links( array(
		'total'              => $max_pages,
		'current'            => $paged,
		'format'             => '?paged=%#%',
		'prev_text'          => $prev_icon . '<span class="nav-prev-text">' . esc_html__( 'Anterior', 'novaconcierge' ) . '</span>',
		'next_text'          => '<span class="nav-next-text">' . esc_html__( 'Siguiente', 'novaconcierge' ) . '</span>' . $next_icon,
		'before_page_number' => '<span class="meta-nav screen-reader-text">' . esc_html__( 'Página', 'novaconcierge' ) . ' </span>',
	) );

	if ( $links ) {
		echo '<nav class="navigation pagination pagination--' . esc_attr( $pagination_style ) . '" aria-label="' . esc_attr__( 'Paginación de propiedades', 'novaconcierge' ) . '">';
		echo '<h2 class="screen-reader-text">' . esc_html__( 'Navegación de propiedades', 'novaconcierge' ) . '</h2>';
		echo '<div class="nav-links">' . $links . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		echo '</nav>';
	}
}

/**
 * Safely renders the property map iframe or URL embed.
 *
 * @param string $map_embed Embed HTML or URL.
 * @param string $title Optional. Map title for accessibility.
 * @return string HTML iframe code.
 */
function novaconcierge_render_property_map( $map_embed, $title = 'Mapa de la propiedad' ) {
	if ( empty( $map_embed ) ) {
		return '';
	}

	$trimmed = trim( $map_embed );

	// If it already contains an <iframe tag
	if ( stripos( $trimmed, '<iframe' ) !== false ) {
		$allowed_html = array(
			'iframe' => array(
				'src'             => true,
				'width'           => true,
				'height'          => true,
				'style'           => true,
				'frameborder'     => true,
				'allowfullscreen' => true,
				'loading'         => true,
				'referrerpolicy'  => true,
				'title'           => true,
			),
		);
		return wp_kses( $trimmed, $allowed_html );
	}

	// If it's a direct URL
	if ( filter_var( $trimmed, FILTER_VALIDATE_URL ) ) {
		return sprintf(
			'<iframe src="%s" width="100%%" height="400" style="border:0; border-radius: 12px;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="%s"></iframe>',
			esc_url( $trimmed ),
			esc_attr( $title )
		);
	}

	return '';
}

/**
 * Backward compatibility wrappers.
 */
if ( ! function_exists( 'avante_get_property_data' ) ) {
	function avante_get_property_data( $post_id = 0 ) {
		return novaconcierge_get_property_data( $post_id );
	}
}

if ( ! function_exists( 'format_price' ) ) {
	function format_price( $price ) {
		return novaconcierge_format_price( $price );
	}
}

if ( ! function_exists( 'format_numeric' ) ) {
	function format_numeric( $num ) {
		return novaconcierge_format_numeric( $num );
	}
}

if ( ! function_exists( 'translate_property_type' ) ) {
	function translate_property_type( $type ) {
		return novaconcierge_translate_property_type( $type );
	}
}

if ( ! function_exists( 'avante_get_icon' ) ) {
	function avante_get_icon( $type ) {
		return novaconcierge_get_icon( $type );
	}
}

if ( ! function_exists( 'avante_render_full_property_metadata' ) ) {
	function avante_render_full_property_metadata( $post_id = 0 ) {
		novaconcierge_render_full_property_metadata( $post_id );
	}
}

if ( ! function_exists( 'get_property_locations' ) ) {
	function get_property_locations() {
		return novaconcierge_get_property_locations();
	}
}

if ( ! function_exists( 'get_property_price_range' ) ) {
	function get_property_price_range() {
		return novaconcierge_get_property_price_range();
	}
}

if ( ! function_exists( 'get_existing_property_types' ) ) {
	function get_existing_property_types() {
		return novaconcierge_get_property_types();
	}
}

if ( ! function_exists( 'get_existing_operation_types' ) ) {
	function get_existing_operation_types() {
		return novaconcierge_get_operation_types();
	}
}

/**
 * Remove read more link from property excerpts on single property pages.
 *
 * @param string $more Current excerpt more string.
 * @return string
 */
function novaconcierge_property_excerpt_more( $more ) {
	if ( is_singular( 'property' ) ) {
		return '';
	}
	return $more;
}
add_filter( 'excerpt_more', 'novaconcierge_property_excerpt_more', 20 );
