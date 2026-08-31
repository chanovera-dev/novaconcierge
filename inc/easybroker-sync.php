<?php
/**
 * EasyBroker API Synchronization Module
 *
 * Synchronizes property listings, metadata, galleries, and details from
 * EasyBroker API into the local WordPress 'property' Custom Post Type.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Safely get a value from an associative array.
 *
 * @param array $array Target array.
 * @param string $key Array key.
 * @param mixed $default Default value if key is not found.
 * @return mixed Found value or default.
 */
function novaconcierge_eb_safe_value( $array, $key, $default = '' ) {
	return ( isset( $array[ $key ] ) && ! empty( $array[ $key ] ) ) ? $array[ $key ] : $default;
}

/**
 * Retrieves configured EasyBroker API Keys.
 *
 * @return array Array of API key strings.
 */
function novaconcierge_get_eb_api_keys() {
	$keys = get_option( 'novaconcierge_eb_api_keys' );
	if ( false === $keys ) {
		// Fallback to legacy options or constants if available
		$legacy = get_option( 'avante_eb_api_keys' );
		if ( ! empty( $legacy ) && is_array( $legacy ) ) {
			return array_values( array_filter( $legacy, 'strlen' ) );
		}
		if ( defined( 'EASYBROKER_API_KEY' ) ) {
			return array( EASYBROKER_API_KEY );
		}
		return array();
	}

	return is_array( $keys ) ? array_values( array_filter( $keys, 'strlen' ) ) : array();
}

/**
 * Normalizes EasyBroker operation type to internal standard slug.
 *
 * @param string $op Raw operation string.
 * @return string Normalized operation slug ('sale', 'rental', 'temporary_rental').
 */
function novaconcierge_normalize_operation( $op ) {
	$op = strtolower( trim( $op ) );
	if ( strpos( $op, 'sale' ) !== false || strpos( $op, 'venta' ) !== false ) {
		return 'sale';
	}
	if ( strpos( $op, 'temporary' ) !== false || strpos( $op, 'temporal' ) !== false ) {
		return 'temporary_rental';
	}
	if ( strpos( $op, 'rent' ) !== false || strpos( $op, 'renta' ) !== false ) {
		return 'rental';
	}
	return 'sale';
}

/**
 * Normalizes EasyBroker property type to internal standard slug.
 *
 * @param string $type Raw property type.
 * @return string Normalized type slug.
 */
function novaconcierge_normalize_property_type( $type ) {
	$type = strtolower( trim( $type ) );

	if ( strpos( $type, 'casa' ) !== false || strpos( $type, 'house' ) !== false ) {
		return 'house';
	}
	if ( strpos( $type, 'departamento' ) !== false || strpos( $type, 'apartment' ) !== false || strpos( $type, 'condo' ) !== false || strpos( $type, 'flat' ) !== false ) {
		return 'apartment';
	}
	if ( strpos( $type, 'terreno' ) !== false || strpos( $type, 'land' ) !== false || strpos( $type, 'lote' ) !== false ) {
		return 'land';
	}
	if ( strpos( $type, 'comercial' ) !== false || strpos( $type, 'commercial' ) !== false || strpos( $type, 'local' ) !== false ) {
		return 'commercial';
	}
	if ( strpos( $type, 'oficina' ) !== false || strpos( $type, 'office' ) !== false ) {
		return 'office';
	}
	if ( strpos( $type, 'bodega' ) !== false || strpos( $type, 'warehouse' ) !== false ) {
		return 'warehouse';
	}
	if ( strpos( $type, 'industrial' ) !== false || strpos( $type, 'nave' ) !== false ) {
		return 'industrial_warehouse';
	}
	if ( strpos( $type, 'edificio' ) !== false || strpos( $type, 'building' ) !== false ) {
		return 'building';
	}

	return 'house';
}

/**
 * Synchronizes property listings from EasyBroker API.
 *
 * @return array Array with status, total imported count, and errors.
 */
function novaconcierge_eb_sync_properties() {
	$api_keys = novaconcierge_get_eb_api_keys();
	if ( empty( $api_keys ) ) {
		return array(
			'success' => false,
			'message' => __( 'No se encontraron API Keys configuradas para EasyBroker.', 'novaconcierge' ),
			'count'   => 0,
		);
	}

	$total_imported = 0;
	$errors         = array();

	foreach ( $api_keys as $api_key ) {
		$page  = 1;
		$limit = 50;

		do {
			$url  = "https://api.easybroker.com/v1/properties?page={$page}&limit={$limit}";
			$args = array(
				'headers' => array( 'X-Authorization' => $api_key ),
				'timeout' => 45,
			);

			$response = wp_remote_get( $url, $args );
			if ( is_wp_error( $response ) ) {
				$errors[] = 'EasyBroker API Error: ' . $response->get_error_message();
				break;
			}

			$body       = json_decode( wp_remote_retrieve_body( $response ), true );
			$properties = $body['content'] ?? array();
			if ( empty( $properties ) ) {
				break;
			}

			foreach ( $properties as $p ) {
				$public_id = sanitize_text_field( novaconcierge_eb_safe_value( $p, 'public_id' ) );
				$title     = sanitize_text_field( novaconcierge_eb_safe_value( $p, 'title', 'Propiedad sin título' ) );

				if ( empty( $public_id ) ) {
					continue;
				}

				// Check if property exists locally by eb_public_id
				$existing = get_posts( array(
					'post_type'      => 'property',
					'meta_key'       => 'eb_public_id',
					'meta_value'     => $public_id,
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'post_status'    => 'any',
				) );

				if ( ! empty( $existing ) ) {
					$post_id = $existing[0];
					wp_update_post( array(
						'ID'         => $post_id,
						'post_title' => $title,
					) );
				} else {
					$post_id = wp_insert_post( array(
						'post_type'   => 'property',
						'post_title'  => $title,
						'post_status' => 'publish',
					) );
					if ( is_wp_error( $post_id ) ) {
						$errors[] = "Error al crear post para {$public_id}";
						continue;
					}
					update_post_meta( $post_id, 'eb_public_id', $public_id );
				}

				// Operation details
				$operations      = $p['operations'] ?? array();
				$operation       = $operations[0] ?? array();
				$formatted_price = novaconcierge_eb_safe_value( $operation, 'formatted_amount', '' );
				$operation_type  = novaconcierge_eb_safe_value( $operation, 'type', 'sale' );
				$currency        = novaconcierge_eb_safe_value( $operation, 'currency', 'MXN' );

				// Numeric price extraction
				$raw_price = 0;
				if ( ! empty( $operation['amount'] ) ) {
					$raw_price = floatval( preg_replace( '/[^\d.]/', '', strval( $operation['amount'] ) ) );
				} elseif ( ! empty( $formatted_price ) ) {
					$raw_price = floatval( preg_replace( '/[^\d.]/', '', str_replace( ',', '', $formatted_price ) ) );
				}

				// Update core metadata
				update_post_meta( $post_id, 'eb_price', $formatted_price );
				update_post_meta( $post_id, 'eb_price_num', $raw_price );
				update_post_meta( $post_id, 'eb_currency', $currency );
				update_post_meta( $post_id, 'eb_operation', novaconcierge_normalize_operation( $operation_type ) );
				update_post_meta( $post_id, 'eb_location', sanitize_text_field( novaconcierge_eb_safe_value( $p, 'location', '' ) ) );
				update_post_meta( $post_id, 'eb_property_type', novaconcierge_normalize_property_type( novaconcierge_eb_safe_value( $p, 'property_type', 'house' ) ) );
				update_post_meta( $post_id, 'eb_bedrooms', intval( novaconcierge_eb_safe_value( $p, 'bedrooms', 0 ) ) );
				update_post_meta( $post_id, 'eb_bathrooms', intval( novaconcierge_eb_safe_value( $p, 'bathrooms', 0 ) ) );
				update_post_meta( $post_id, 'eb_parking', intval( novaconcierge_eb_safe_value( $p, 'parking_spaces', 0 ) ) );
				update_post_meta( $post_id, 'eb_lot_size', intval( novaconcierge_eb_safe_value( $p, 'lot_size', 0 ) ) );
				update_post_meta( $post_id, 'eb_construction_size', intval( novaconcierge_eb_safe_value( $p, 'construction_size', 0 ) ) );

				// Fetch single property details from API for gallery and description
				$detail_url = "https://api.easybroker.com/v1/properties/{$public_id}";
				$detail_res = wp_remote_get( $detail_url, $args );

				if ( ! is_wp_error( $detail_res ) ) {
					$detail_body = json_decode( wp_remote_retrieve_body( $detail_res ), true );

					// Gallery images
					$gallery = array();
					if ( ! empty( $detail_body['property_images'] ) && is_array( $detail_body['property_images'] ) ) {
						foreach ( $detail_body['property_images'] as $img ) {
							if ( ! empty( $img['url'] ) ) {
								$gallery[] = esc_url_raw( $img['url'] );
							}
						}
						update_post_meta( $post_id, 'eb_gallery', $gallery );
					}

					// Update description / content
					$description = $detail_body['description'] ?? '';
					if ( ! empty( $description ) ) {
						wp_update_post( array(
							'ID'           => $post_id,
							'post_content' => wp_kses_post( $description ),
						) );
					}

					// Featured image
					$title_image = novaconcierge_eb_safe_value( $p, 'title_image_full', '' );
					if ( empty( $title_image ) && ! empty( $gallery ) ) {
						$title_image = $gallery[0];
					}

					if ( ! empty( $title_image ) && ! has_post_thumbnail( $post_id ) ) {
						novaconcierge_eb_set_featured_image( $post_id, $title_image );
					}
				}

				$total_imported++;
			}

			$page++;
		} while ( ! empty( $body['pagination']['next_page'] ) );
	}

	// Invalidate location transient
	delete_transient( 'novaconcierge_property_locations' );

	// Record sync timestamp
	update_option( 'novaconcierge_eb_last_sync_time', current_time( 'mysql' ) );

	return array(
		'success' => true,
		'count'   => $total_imported,
		'errors'  => $errors,
	);
}

/**
 * Downloads a remote image and assigns it as the featured post thumbnail.
 *
 * @param int $post_id Target post ID.
 * @param string $image_url Remote image URL.
 * @return int|false Attachment ID or false on failure.
 */
function novaconcierge_eb_set_featured_image( $post_id, $image_url ) {
	if ( empty( $image_url ) || get_post_thumbnail_id( $post_id ) ) {
		return false;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';

	$attach_id = media_sideload_image( $image_url, $post_id, null, 'id' );
	if ( ! is_wp_error( $attach_id ) && $attach_id ) {
		set_post_thumbnail( $post_id, $attach_id );
		return $attach_id;
	}

	return false;
}

/**
 * Schedule daily cron job for automatic synchronization.
 */
function novaconcierge_schedule_eb_cron() {
	if ( ! wp_next_scheduled( 'novaconcierge_eb_daily_sync' ) ) {
		wp_schedule_event( time(), 'daily', 'novaconcierge_eb_daily_sync' );
	}
}
add_action( 'wp', 'novaconcierge_schedule_eb_cron' );

/**
 * Cron action hook.
 */
add_action( 'novaconcierge_eb_daily_sync', 'novaconcierge_eb_sync_properties' );

/**
 * AJAX Handler for Manual Property Synchronization.
 */
function novaconcierge_ajax_sync_properties() {
	check_ajax_referer( 'novaconcierge_eb_sync_nonce', 'security' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( __( 'No tienes permisos suficientes.', 'novaconcierge' ) );
	}

	$result = novaconcierge_eb_sync_properties();

	if ( $result['success'] ) {
		wp_send_json_success( sprintf(
			__( 'Sincronización exitosa: %d propiedades procesadas.', 'novaconcierge' ),
			$result['count']
		) );
	} else {
		wp_send_json_error( $result['message'] );
	}
}
add_action( 'wp_ajax_novaconcierge_sync_properties', 'novaconcierge_ajax_sync_properties' );

/**
 * Backward compatibility alias for Avante sync action.
 */
if ( ! function_exists( 'eb_sync_properties' ) ) {
	function eb_sync_properties() {
		return novaconcierge_eb_sync_properties();
	}
}
