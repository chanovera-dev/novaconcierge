<?php
/**
 * Custom Post Type: Property & Native Meta Boxes
 *
 * Registers the 'property' custom post type and provides native WordPress meta boxes
 * for managing property details locally without external plugin dependencies (ACF/SCF).
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the 'property' Custom Post Type.
 */
function novaconcierge_register_property_cpt() {
	$labels = array(
		'name'                  => _x( 'Properties', 'Post Type General Name', 'novaconcierge' ),
		'singular_name'         => _x( 'Property', 'Post Type Singular Name', 'novaconcierge' ),
		'menu_name'             => __( 'Propiedades', 'novaconcierge' ),
		'name_admin_bar'        => __( 'Propiedad', 'novaconcierge' ),
		'archives'              => __( 'Archivo de propiedades', 'novaconcierge' ),
		'attributes'            => __( 'Atributos de propiedades', 'novaconcierge' ),
		'parent_item_colon'     => __( 'Propiedad padre:', 'novaconcierge' ),
		'all_items'             => __( 'Todas las propiedades', 'novaconcierge' ),
		'add_new_item'          => __( 'Agregar nueva propiedad', 'novaconcierge' ),
		'add_new'               => __( 'Agregar propiedad', 'novaconcierge' ),
		'new_item'              => __( 'Nueva propiedad', 'novaconcierge' ),
		'edit_item'             => __( 'Editar propiedad', 'novaconcierge' ),
		'update_item'           => __( 'Actualizar propiedad', 'novaconcierge' ),
		'view_item'             => __( 'Ver propiedad', 'novaconcierge' ),
		'view_items'            => __( 'Ver propiedades', 'novaconcierge' ),
		'search_items'          => __( 'Buscar propiedades', 'novaconcierge' ),
		'not_found'             => __( 'No se encontraron propiedades', 'novaconcierge' ),
		'not_found_in_trash'    => __( 'No se encontraron propiedades en la papelera', 'novaconcierge' ),
		'featured_image'        => __( 'Imagen destacada', 'novaconcierge' ),
		'set_featured_image'    => __( 'Establecer imagen destacada', 'novaconcierge' ),
		'remove_featured_image' => __( 'Eliminar imagen destacada', 'novaconcierge' ),
		'use_featured_image'    => __( 'Usar como imagen destacada', 'novaconcierge' ),
		'insert_into_item'      => __( 'Insertar en propiedad', 'novaconcierge' ),
		'uploaded_to_this_item' => __( 'Subido a esta propiedad', 'novaconcierge' ),
		'items_list'            => __( 'Lista de propiedades', 'novaconcierge' ),
		'items_list_navigation' => __( 'Navegación de lista de propiedades', 'novaconcierge' ),
		'filter_items_list'     => __( 'Filtrar lista de propiedades', 'novaconcierge' ),
	);

	$args = array(
		'label'                 => __( 'Property', 'novaconcierge' ),
		'description'           => __( 'Real estate property listings', 'novaconcierge' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'excerpt', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes' ),
		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'menu_icon'             => 'dashicons-admin-multisite',
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
		'show_in_rest'          => true,
		'rewrite'               => array(
			'slug'       => 'propiedades',
			'with_front' => true,
		),
	);

	register_post_type( 'property', $args );
}
add_action( 'init', 'novaconcierge_register_property_cpt', 0 );

/**
 * Register Native Meta Boxes for Property Custom Post Type.
 */
function novaconcierge_add_property_meta_boxes() {
	add_meta_box(
		'novaconcierge_property_details',
		__( 'Detalles de la Propiedad', 'novaconcierge' ),
		'novaconcierge_render_property_details_meta_box',
		'property',
		'normal',
		'high'
	);

	add_meta_box(
		'novaconcierge_property_gallery',
		__( 'Galería de Imágenes de la Propiedad', 'novaconcierge' ),
		'novaconcierge_render_property_gallery_meta_box',
		'property',
		'normal',
		'high'
	);

	add_meta_box(
		'novaconcierge_property_map',
		__( 'Ubicación y Mapa de la Propiedad', 'novaconcierge' ),
		'novaconcierge_render_property_map_meta_box',
		'property',
		'normal',
		'default'
	);
}
add_action( 'add_meta_boxes', 'novaconcierge_add_property_meta_boxes' );

/**
 * Render Property Details Meta Box.
 *
 * @param WP_Post $post Current post object.
 */
function novaconcierge_render_property_details_meta_box( $post ) {
	wp_nonce_field( 'novaconcierge_save_property_meta', 'novaconcierge_property_meta_nonce' );

	$price             = get_post_meta( $post->ID, 'eb_price', true );
	$currency          = get_post_meta( $post->ID, 'eb_currency', true ) ?: 'MXN';
	$operation         = get_post_meta( $post->ID, 'eb_operation', true ) ?: 'sale';
	$property_type     = get_post_meta( $post->ID, 'eb_property_type', true ) ?: 'house';
	$bedrooms          = get_post_meta( $post->ID, 'eb_bedrooms', true );
	$bathrooms         = get_post_meta( $post->ID, 'eb_bathrooms', true );
	$parking           = get_post_meta( $post->ID, 'eb_parking', true );
	$construction_size = get_post_meta( $post->ID, 'eb_construction_size', true );
	$lot_size          = get_post_meta( $post->ID, 'eb_lot_size', true );
	$public_id         = get_post_meta( $post->ID, 'eb_public_id', true );
	?>
	<style>
		.nc-meta-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-top: 10px; }
		.nc-meta-field label { display: block; font-weight: 600; margin-bottom: 5px; font-size: 13px; }
		.nc-meta-field input, .nc-meta-field select { width: 100%; max-width: 100%; box-sizing: border-box; }
		.nc-meta-field .description { font-size: 11px; color: #666; margin-top: 3px; }
	</style>

	<div class="nc-meta-grid">
		<div class="nc-meta-field">
			<label for="eb_price"><?php esc_html_e( 'Precio', 'novaconcierge' ); ?></label>
			<input type="text" id="eb_price" name="eb_price" value="<?php echo esc_attr( $price ); ?>" placeholder="$3,500,000">
			<span class="description"><?php esc_html_e( 'Precio con formato o valor numérico', 'novaconcierge' ); ?></span>
		</div>

		<div class="nc-meta-field">
			<label for="eb_currency"><?php esc_html_e( 'Moneda', 'novaconcierge' ); ?></label>
			<select id="eb_currency" name="eb_currency">
				<option value="MXN" <?php selected( $currency, 'MXN' ); ?>>MXN ($)</option>
				<option value="USD" <?php selected( $currency, 'USD' ); ?>>USD ($)</option>
				<option value="EUR" <?php selected( $currency, 'EUR' ); ?>>EUR (€)</option>
			</select>
		</div>

		<div class="nc-meta-field">
			<label for="eb_operation"><?php esc_html_e( 'Tipo de Operación', 'novaconcierge' ); ?></label>
			<select id="eb_operation" name="eb_operation">
				<option value="sale" <?php selected( $operation, 'sale' ); ?>><?php esc_html_e( 'En Venta', 'novaconcierge' ); ?></option>
				<option value="rental" <?php selected( $operation, 'rental' ); ?>><?php esc_html_e( 'En Renta', 'novaconcierge' ); ?></option>
				<option value="temporary_rental" <?php selected( $operation, 'temporary_rental' ); ?>><?php esc_html_e( 'Renta Temporal', 'novaconcierge' ); ?></option>
			</select>
		</div>

		<div class="nc-meta-field">
			<label for="eb_property_type"><?php esc_html_e( 'Tipo de Propiedad', 'novaconcierge' ); ?></label>
			<select id="eb_property_type" name="eb_property_type">
				<option value="house" <?php selected( $property_type, 'house' ); ?>><?php esc_html_e( 'Casa', 'novaconcierge' ); ?></option>
				<option value="apartment" <?php selected( $property_type, 'apartment' ); ?>><?php esc_html_e( 'Departamento', 'novaconcierge' ); ?></option>
				<option value="land" <?php selected( $property_type, 'land' ); ?>><?php esc_html_e( 'Terreno', 'novaconcierge' ); ?></option>
				<option value="commercial" <?php selected( $property_type, 'commercial' ); ?>><?php esc_html_e( 'Local Comercial', 'novaconcierge' ); ?></option>
				<option value="office" <?php selected( $property_type, 'office' ); ?>><?php esc_html_e( 'Oficina', 'novaconcierge' ); ?></option>
				<option value="warehouse" <?php selected( $property_type, 'warehouse' ); ?>><?php esc_html_e( 'Bodega', 'novaconcierge' ); ?></option>
				<option value="industrial_warehouse" <?php selected( $property_type, 'industrial_warehouse' ); ?>><?php esc_html_e( 'Nave Industrial', 'novaconcierge' ); ?></option>
				<option value="building" <?php selected( $property_type, 'building' ); ?>><?php esc_html_e( 'Edificio', 'novaconcierge' ); ?></option>
			</select>
		</div>

		<div class="nc-meta-field">
			<label for="eb_bedrooms"><?php esc_html_e( 'Recámaras', 'novaconcierge' ); ?></label>
			<input type="number" id="eb_bedrooms" name="eb_bedrooms" value="<?php echo esc_attr( $bedrooms ); ?>" min="0" step="1">
		</div>

		<div class="nc-meta-field">
			<label for="eb_bathrooms"><?php esc_html_e( 'Baños', 'novaconcierge' ); ?></label>
			<input type="number" id="eb_bathrooms" name="eb_bathrooms" value="<?php echo esc_attr( $bathrooms ); ?>" min="0" step="0.5">
		</div>

		<div class="nc-meta-field">
			<label for="eb_parking"><?php esc_html_e( 'Estacionamientos', 'novaconcierge' ); ?></label>
			<input type="number" id="eb_parking" name="eb_parking" value="<?php echo esc_attr( $parking ); ?>" min="0" step="1">
		</div>

		<div class="nc-meta-field">
			<label for="eb_construction_size"><?php esc_html_e( 'Construcción (m²)', 'novaconcierge' ); ?></label>
			<input type="number" id="eb_construction_size" name="eb_construction_size" value="<?php echo esc_attr( $construction_size ); ?>" min="0" step="1">
		</div>

		<div class="nc-meta-field">
			<label for="eb_lot_size"><?php esc_html_e( 'Terreno (m²)', 'novaconcierge' ); ?></label>
			<input type="number" id="eb_lot_size" name="eb_lot_size" value="<?php echo esc_attr( $lot_size ); ?>" min="0" step="1">
		</div>

		<div class="nc-meta-field">
			<label for="eb_public_id"><?php esc_html_e( 'ID / Clave EasyBroker', 'novaconcierge' ); ?></label>
			<input type="text" id="eb_public_id" name="eb_public_id" value="<?php echo esc_attr( $public_id ); ?>" placeholder="EB-XXXXX">
			<span class="description"><?php esc_html_e( 'ID de EasyBroker o clave interna personalizada', 'novaconcierge' ); ?></span>
		</div>
	</div>
	<?php
}

/**
 * Render Property Gallery Meta Box.
 *
 * @param WP_Post $post Current post object.
 */
function novaconcierge_render_property_gallery_meta_box( $post ) {
	$gallery = get_post_meta( $post->ID, 'eb_gallery', true );
	if ( ! is_array( $gallery ) ) {
		$gallery = ! empty( $gallery ) ? (array) $gallery : array();
	}
	?>
	<div class="nc-gallery-container">
		<p class="description"><?php esc_html_e( 'Selecciona o sube múltiples imágenes para el carrusel y la ficha de la propiedad.', 'novaconcierge' ); ?></p>
		
		<div id="nc-gallery-preview" style="display: flex; flex-wrap: wrap; gap: 10px; margin: 15px 0;">
			<?php foreach ( $gallery as $index => $item ) :
				$img_url = is_array( $item ) ? ( $item['url'] ?? '' ) : $item;
				if ( empty( $img_url ) ) continue;
			?>
				<div class="nc-gallery-item" style="position: relative; width: 110px; height: 110px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; background: #f0f0f1;">
					<img src="<?php echo esc_url( $img_url ); ?>" style="width: 100%; height: 100%; object-fit: cover;" alt="">
					<input type="hidden" name="eb_gallery[]" value="<?php echo esc_url( $img_url ); ?>">
					<button type="button" class="nc-remove-image button-link" style="position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; line-height: 1;">&times;</button>
				</div>
			<?php endforeach; ?>
		</div>

		<button type="button" id="nc-add-gallery-images" class="button button-secondary">
			<span class="dashicons dashicons-format-gallery" style="vertical-align: middle; margin-top: -2px;"></span>
			<?php esc_html_e( 'Gestionar Galería de Imágenes', 'novaconcierge' ); ?>
		</button>
	</div>
	<?php
}

/**
 * Render Property Map & Location Meta Box.
 *
 * @param WP_Post $post Current post object.
 */
function novaconcierge_render_property_map_meta_box( $post ) {
	$location   = get_post_meta( $post->ID, 'eb_location', true );
	$map_embed  = get_post_meta( $post->ID, 'eb_map_embed', true );
	?>
	<div style="margin-bottom: 15px;">
		<label for="eb_location" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php esc_html_e( 'Ubicación / Dirección Texto', 'novaconcierge' ); ?></label>
		<input type="text" id="eb_location" name="eb_location" value="<?php echo esc_attr( $location ); ?>" class="large-text" placeholder="Ej: Polanco, Miguel Hidalgo, Ciudad de México">
		<span class="description"><?php esc_html_e( 'Formato recomendado: Colonia, Ciudad, Estado (utilizado para filtros y listados)', 'novaconcierge' ); ?></span>
	</div>

	<div>
		<label for="eb_map_embed" style="display: block; font-weight: 600; margin-bottom: 5px;"><?php esc_html_e( 'Mapa de Google Maps (Código Embed Iframe o URL)', 'novaconcierge' ); ?></label>
		<textarea id="eb_map_embed" name="eb_map_embed" rows="4" class="large-text" placeholder="<?php esc_attr_e( 'Pega aquí el código <iframe> completo de Google Maps o la URL de embed', 'novaconcierge' ); ?>"><?php echo esc_textarea( $map_embed ); ?></textarea>
		<span class="description"><?php esc_html_e( 'En Google Maps: Compartir -> Insertar un mapa -> Copiar HTML o URL.', 'novaconcierge' ); ?></span>
	</div>
	<?php
}

/**
 * Save Property Meta Box Data.
 *
 * @param int $post_id The ID of the post being saved.
 */
function novaconcierge_save_property_meta( $post_id ) {
	// Verify nonce
	if ( ! isset( $_POST['novaconcierge_property_meta_nonce'] ) || ! wp_verify_nonce( $_POST['novaconcierge_property_meta_nonce'], 'novaconcierge_save_property_meta' ) ) {
		return;
	}

	// Avoid autosave
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// Check permissions
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// Text and numeric fields
	$fields = array(
		'eb_price'             => 'sanitize_text_field',
		'eb_currency'          => 'sanitize_text_field',
		'eb_operation'         => 'sanitize_text_field',
		'eb_property_type'     => 'sanitize_text_field',
		'eb_bedrooms'          => 'intval',
		'eb_bathrooms'         => 'sanitize_text_field',
		'eb_parking'           => 'intval',
		'eb_construction_size' => 'intval',
		'eb_lot_size'          => 'intval',
		'eb_location'          => 'sanitize_text_field',
		'eb_public_id'         => 'sanitize_text_field',
	);

	foreach ( $fields as $field_key => $sanitizer ) {
		if ( isset( $_POST[ $field_key ] ) ) {
			$raw_value = $_POST[ $field_key ];
			$clean_value = call_user_func( $sanitizer, $raw_value );
			update_post_meta( $post_id, $field_key, $clean_value );
		}
	}

	// Compute and store numeric price for DB filtering and ordering
	if ( isset( $_POST['eb_price'] ) ) {
		$raw_price = sanitize_text_field( $_POST['eb_price'] );
		$price_numeric = floatval( preg_replace( '/[^\d.]/', '', str_replace( ',', '', $raw_price ) ) );
		update_post_meta( $post_id, 'eb_price_num', $price_numeric );
	}

	// Save map embed (allow safe iframe tags)
	if ( isset( $_POST['eb_map_embed'] ) ) {
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
		$map_embed_clean = wp_kses( $_POST['eb_map_embed'], $allowed_html );
		update_post_meta( $post_id, 'eb_map_embed', $map_embed_clean );
	}

	// Save Gallery array
	if ( isset( $_POST['eb_gallery'] ) && is_array( $_POST['eb_gallery'] ) ) {
		$clean_gallery = array_map( 'esc_url_raw', $_POST['eb_gallery'] );
		$clean_gallery = array_values( array_filter( $clean_gallery ) );
		update_post_meta( $post_id, 'eb_gallery', $clean_gallery );
	} else {
		update_post_meta( $post_id, 'eb_gallery', array() );
	}
}
add_action( 'save_post_property', 'novaconcierge_save_property_meta' );

/**
 * Allow 'property' post type in Stories post likes.
 *
 * @param array $types Allowed post types.
 * @return array
 */
function novaconcierge_enable_property_likes( $types ) {
	if ( is_array( $types ) && ! in_array( 'property', $types, true ) ) {
		$types[] = 'property';
	}
	return $types;
}
add_filter( 'stories_liked_post_types', 'novaconcierge_enable_property_likes' );
