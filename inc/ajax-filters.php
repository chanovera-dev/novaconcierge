<?php
/**
 * AJAX Property Filtering and Pagination Handler
 *
 * Receives filter parameters via POST and returns the rendered property cards and pagination HTML.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle AJAX Property Filter Requests.
 */
function novaconcierge_ajax_filter_properties() {
	global $wpdb;

	$paged = isset( $_POST['paged'] ) ? intval( $_POST['paged'] ) : 1;

	$args = array(
		'post_type'      => 'property',
		'post_status'    => 'publish',
		'posts_per_page' => 12,
		'paged'          => $paged,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	// Keyword search
	$search_term = ! empty( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	if ( ! empty( $search_term ) ) {
		$args['s'] = $search_term;
	}

	$meta_query = array( 'relation' => 'AND' );

	// Operation filter (sale, rental, temporary_rental)
	if ( ! empty( $_POST['operation'] ) ) {
		$ops = (array) $_POST['operation'];
		$meta_query[] = array(
			'key'     => 'eb_operation',
			'value'   => array_map( 'sanitize_text_field', $ops ),
			'compare' => 'IN',
		);
	}

	// Property type filter
	if ( ! empty( $_POST['type'] ) ) {
		$types = (array) $_POST['type'];
		$expanded_types = $types;

		// Expand for backward compatibility
		foreach ( $types as $t ) {
			if ( 'house' === $t ) {
				$expanded_types[] = 'Casa';
			} elseif ( 'apartment' === $t ) {
				$expanded_types[] = 'Departamento';
				$expanded_types[] = 'Condominio';
			} elseif ( 'land' === $t ) {
				$expanded_types[] = 'Terreno';
			} elseif ( 'commercial' === $t ) {
				$expanded_types[] = 'Local Comercial';
			} elseif ( 'office' === $t ) {
				$expanded_types[] = 'Oficina';
			} elseif ( 'warehouse' === $t ) {
				$expanded_types[] = 'Bodega';
			} elseif ( 'industrial_warehouse' === $t ) {
				$expanded_types[] = 'Nave Industrial';
			}
		}

		$meta_query[] = array(
			'key'     => 'eb_property_type',
			'value'   => array_unique( $expanded_types ),
			'compare' => 'IN',
		);
	}

	// Bedrooms filter
	if ( ! empty( $_POST['bedrooms'] ) ) {
		$bedrooms = intval( $_POST['bedrooms'] );
		if ( $bedrooms > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_bedrooms',
				'value'   => $bedrooms,
				'type'    => 'NUMERIC',
				'compare' => '>=',
			);
		}
	}

	// Bathrooms filter
	if ( ! empty( $_POST['bathrooms'] ) ) {
		$bathrooms = floatval( $_POST['bathrooms'] );
		if ( $bathrooms > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_bathrooms',
				'value'   => $bathrooms,
				'type'    => 'DECIMAL',
				'compare' => '>=',
			);
		}
	}

	// Price range filter (supports both min_price/max_price and price_min/price_max)
	$min_price = ! empty( $_POST['min_price'] ) ? floatval( $_POST['min_price'] ) : ( ! empty( $_POST['price_min'] ) ? floatval( $_POST['price_min'] ) : 0 );
	$max_price = ! empty( $_POST['max_price'] ) ? floatval( $_POST['max_price'] ) : ( ! empty( $_POST['price_max'] ) ? floatval( $_POST['price_max'] ) : 0 );

	if ( $min_price > 0 || $max_price > 0 ) {
		if ( $min_price > 0 && $max_price > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_price_num',
				'value'   => array( $min_price, $max_price ),
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
			);
		} elseif ( $min_price > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_price_num',
				'value'   => $min_price,
				'type'    => 'NUMERIC',
				'compare' => '>=',
			);
		} elseif ( $max_price > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_price_num',
				'value'   => $max_price,
				'type'    => 'NUMERIC',
				'compare' => '<=',
			);
		}
	}

	// Construction size filter (m²)
	$min_construction = ! empty( $_POST['construction_min'] ) ? floatval( $_POST['construction_min'] ) : ( ! empty( $_POST['min_construction'] ) ? floatval( $_POST['min_construction'] ) : 0 );
	$max_construction = ! empty( $_POST['construction_max'] ) ? floatval( $_POST['construction_max'] ) : ( ! empty( $_POST['max_construction'] ) ? floatval( $_POST['max_construction'] ) : 0 );

	if ( $min_construction > 0 || $max_construction > 0 ) {
		if ( $min_construction > 0 && $max_construction > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_construction_size',
				'value'   => array( $min_construction, $max_construction ),
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
			);
		} elseif ( $min_construction > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_construction_size',
				'value'   => $min_construction,
				'type'    => 'NUMERIC',
				'compare' => '>=',
			);
		} elseif ( $max_construction > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_construction_size',
				'value'   => $max_construction,
				'type'    => 'NUMERIC',
				'compare' => '<=',
			);
		}
	}

	// Land / Lot size filter (m²)
	$min_land = ! empty( $_POST['land_min'] ) ? floatval( $_POST['land_min'] ) : ( ! empty( $_POST['min_land'] ) ? floatval( $_POST['min_land'] ) : 0 );
	$max_land = ! empty( $_POST['land_max'] ) ? floatval( $_POST['land_max'] ) : ( ! empty( $_POST['max_land'] ) ? floatval( $_POST['max_land'] ) : 0 );

	if ( $min_land > 0 || $max_land > 0 ) {
		if ( $min_land > 0 && $max_land > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_lot_size',
				'value'   => array( $min_land, $max_land ),
				'type'    => 'NUMERIC',
				'compare' => 'BETWEEN',
			);
		} elseif ( $min_land > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_lot_size',
				'value'   => $min_land,
				'type'    => 'NUMERIC',
				'compare' => '>=',
			);
		} elseif ( $max_land > 0 ) {
			$meta_query[] = array(
				'key'     => 'eb_lot_size',
				'value'   => $max_land,
				'type'    => 'NUMERIC',
				'compare' => '<=',
			);
		}
	}

	// Location filter (supports location, state, city)
	if ( ! empty( $_POST['location'] ) || ! empty( $_POST['state'] ) || ! empty( $_POST['city'] ) ) {
		$locs = array();
		if ( ! empty( $_POST['location'] ) ) {
			$locs = array_merge( $locs, (array) $_POST['location'] );
		}
		if ( ! empty( $_POST['state'] ) ) {
			$locs = array_merge( $locs, (array) $_POST['state'] );
		}
		if ( ! empty( $_POST['city'] ) ) {
			$locs = array_merge( $locs, (array) $_POST['city'] );
		}

		$location_clauses = array( 'relation' => 'OR' );
		foreach ( array_unique( $locs ) as $loc ) {
			if ( ! empty( $loc ) ) {
				$location_clauses[] = array(
					'key'     => 'eb_location',
					'value'   => sanitize_text_field( $loc ),
					'compare' => 'LIKE',
				);
			}
		}
		if ( count( $location_clauses ) > 1 ) {
			$meta_query[] = $location_clauses;
		}
	}

	if ( count( $meta_query ) > 1 ) {
		$args['meta_query'] = $meta_query;
	}

	// Query execution
	$query = new WP_Query( $args );

	if ( $query->have_posts() ) {
		while ( $query->have_posts() ) {
			$query->the_post();
			get_template_part( 'template-parts/content', 'property' );
		}

		// Stories compatible pagination markup
		novaconcierge_pagination( $query, $paged );
	} else {
		echo '<div class="no-properties-found" style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.6); border-radius: 12px; margin: 20px 0;">';
		echo '<p style="font-size: 18px; color: #666; margin-bottom: 10px;">' . esc_html__( 'No se encontraron propiedades con los criterios seleccionados.', 'novaconcierge' ) . '</p>';
		echo '<p style="font-size: 14px; color: #888;">' . esc_html__( 'Prueba ajustando los filtros o realiza una búsqueda diferente.', 'novaconcierge' ) . '</p>';
		echo '</div>';
	}

	wp_reset_postdata();
	wp_die();
}

add_action( 'wp_ajax_novaconcierge_filter_properties', 'novaconcierge_ajax_filter_properties' );
add_action( 'wp_ajax_nopriv_novaconcierge_filter_properties', 'novaconcierge_ajax_filter_properties' );

// Alias for Avante script compatibility
add_action( 'wp_ajax_filter_properties', 'novaconcierge_ajax_filter_properties' );
add_action( 'wp_ajax_nopriv_filter_properties', 'novaconcierge_ajax_filter_properties' );
