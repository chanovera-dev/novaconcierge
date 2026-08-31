<?php
/**
 * Template part for displaying Property post format as a Slideshow
 *
 * Exact match with stories/template-parts/content-gallery.php structure,
 * populated with property images and real estate specifications.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$post_id             = get_the_ID();
$property_data       = novaconcierge_get_property_data( $post_id );
$gallery_images      = $property_data['gallery'];
$gallery_images_full = $gallery_images;

// Fallback to parent gallery images or post thumbnail
if ( empty( $gallery_images ) ) {
	if ( function_exists( 'stories_get_gallery_images' ) ) {
		$gallery_images      = stories_get_gallery_images( $post_id, 'medium' );
		$gallery_images_full = stories_get_gallery_images( $post_id, 'full' );
	}
	if ( empty( $gallery_images_full ) ) {
		$gallery_images_full = $gallery_images;
	}
}

if ( empty( $gallery_images ) && has_post_thumbnail( $post_id ) ) {
	$featured_med  = get_the_post_thumbnail_url( $post_id, 'medium_large' ) ?: get_the_post_thumbnail_url( $post_id, 'full' );
	$featured_full = get_the_post_thumbnail_url( $post_id, 'full' );
	if ( $featured_med ) {
		$gallery_images      = array( $featured_med );
		$gallery_images_full = array( $featured_full ?: $featured_med );
	}
}

$price             = $property_data['price'];
$currency          = $property_data['currency'];
$operation         = $property_data['operation'];
$location          = $property_data['location'];
$property_type     = $property_data['type'];
$bedrooms          = $property_data['bedrooms'];
$bathrooms         = $property_data['bathrooms'];
$parking           = $property_data['parking'];
$construction_size = $property_data['construction_size'];
$lot_size          = $property_data['lot_size'];
$public_id         = $property_data['public_id'];
$formatted_price   = novaconcierge_format_price( $price, $currency );
$type_label        = novaconcierge_translate_property_type( $property_type );

$operation_label = __( 'En Venta', 'novaconcierge' );
if ( 'rental' === $operation ) {
	$operation_label = __( 'En Renta', 'novaconcierge' );
} elseif ( 'temporary_rental' === $operation ) {
	$operation_label = __( 'Renta Temporal', 'novaconcierge' );
}
?>

<article id="post-<?php the_ID(); ?>" <?php post_class( 'story-card format-gallery-card property-card' ); ?> data-id="<?php echo esc_attr( $post_id ); ?>">
	<?php if ( ! empty( $gallery_images ) ) : ?>
		<div class="stories-slideshow" data-post-id="<?php the_ID(); ?>">
			<!-- Top Actions (Info Toggle & Like Button) -->
			<div class="post-top-actions gallery-top-actions">
				<div class="toggle-info-container inset-shadow-effect">
					<button type="button" class="image-lightbox-trigger gallery-lightbox-trigger"
							data-is-property="1"
							data-lightbox-src="<?php echo esc_url( ! empty( $gallery_images_full[0] ) ? $gallery_images_full[0] : $gallery_images[0] ); ?>"
							data-lightbox-title="<?php echo esc_attr( get_the_title() ); ?>"
							data-lightbox-caption="<?php echo esc_attr( has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '' ); ?>"
							data-lightbox-url="<?php echo esc_url( get_permalink() ); ?>"
							data-gallery-images="<?php echo esc_attr( wp_json_encode( array_values( $gallery_images_full ) ) ); ?>"
							data-current-index="0"
							data-property-price="<?php echo esc_attr( $formatted_price ); ?>"
							data-property-operation="<?php echo esc_attr( $operation_label ); ?>"
							data-property-operation-slug="<?php echo esc_attr( $operation ); ?>"
							data-property-type="<?php echo esc_attr( $type_label ); ?>"
							data-property-location="<?php echo esc_attr( $location ); ?>"
							data-property-id="<?php echo esc_attr( $public_id ); ?>"
							data-property-construction="<?php echo esc_attr( $construction_size > 0 ? novaconcierge_format_numeric( $construction_size ) . ' m² de construcción' : '' ); ?>"
							data-property-lot="<?php echo esc_attr( $lot_size > 0 ? novaconcierge_format_numeric( $lot_size ) . ' m² de terreno' : '' ); ?>"
							data-property-bedrooms="<?php echo esc_attr( $bedrooms > 0 ? $bedrooms . ' recámaras' : '' ); ?>"
							data-property-bathrooms="<?php echo esc_attr( $bathrooms > 0 ? $bathrooms . ' baños' : '' ); ?>"
							data-property-parking="<?php echo esc_attr( $parking > 0 ? $parking . ' estacionamientos' : '' ); ?>"
							aria-label="<?php esc_attr_e( 'Ver galería en pantalla completa', 'novaconcierge' ); ?>"
							title="<?php esc_attr_e( 'Ver galería en lightbox', 'novaconcierge' ); ?>">
						<?php
						if ( function_exists( 'stories_get_svg' ) ) {
							echo stories_get_svg( 'fullscreen', array( 'size' => 15 ) );
						} else {
							echo novaconcierge_get_icon( 'forward' );
						}
						?>
					</button>
					<button type="button" class="toggle-info-btn" aria-label="<?php esc_attr_e( 'Toggle Post Info', 'novaconcierge' ); ?>" title="<?php esc_attr_e( 'Toggle Post Info', 'novaconcierge' ); ?>">
						<?php
						if ( function_exists( 'stories_svg' ) ) {
							stories_svg( 'info', array( 'size' => 18 ) );
						} else {
							echo '<span class="dashicons dashicons-info"></span>';
						}
						?>
					</button>
				</div>
				<?php
				if ( function_exists( 'stories_like_button' ) ) {
					stories_like_button();
				}
				?>
			</div>

			<!-- Floating Badges -->
			<div class="property-floating-badges">
				<span class="property-badge operation-badge <?php echo esc_attr( $operation ); ?>">
					<?php echo novaconcierge_get_property_type_icon( $property_type ); ?>
					<span><?php echo esc_html( $operation_label ); ?></span>
				</span>
				<span class="property-badge price-badge">
					<?php echo esc_html( $formatted_price ); ?>
				</span>
			</div>
			
			<div class="slides-wrapper">
				<?php foreach ( $gallery_images as $index => $image_url ) : 
					$full_url = isset( $gallery_images_full[ $index ] ) ? $gallery_images_full[ $index ] : $image_url;
				?>
					<div class="slide-item <?php echo 0 === $index ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( $index ); ?>" data-full-src="<?php echo esc_url( $full_url ); ?>">
						<img src="<?php echo esc_url( $image_url ); ?>" data-full-src="<?php echo esc_url( $full_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy" decoding="async">
					</div>
				<?php endforeach; ?>
			</div>

			<!-- Information Overlay Card (Toggleable via Info Button) -->
			<div class="info-overlay gallery-info-overlay">
				<header class="entry-header">
					<div class="entry-badge">
						<span class="post-type-badge property-type-tag">
							<?php echo novaconcierge_get_property_type_icon( $property_type ); ?>
							<span><?php echo esc_html( $type_label ); ?></span>
						</span>
					</div>
				</header>

				<div class="entry-body">
					<?php
					if ( is_singular() ) :
						the_title( '<h1 class="entry-title property-card-title">', '</h1>' );
					else :
						the_title( '<h2 class="entry-title property-card-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
					endif;
					?>

					<div class="property-body-highlights">
						<div class="property-highlight-price">
							<span class="price-value"><?php echo esc_html( $formatted_price ); ?></span>
						</div>

						<?php if ( ! empty( $location ) ) : ?>
							<div class="property-highlight-location" title="<?php echo esc_attr( $location ); ?>">
								<?php echo novaconcierge_get_icon( 'location' ); ?>
								<span><?php echo esc_html( $location ); ?></span>
							</div>
						<?php endif; ?>
					</div>

					<div class="entry-summary property-summary">
						<?php the_excerpt(); ?>
					</div>
				</div>

				<footer class="entry-footer">
					<div class="post--tags__wrapper">
						<div class="tags post--tags">
							<?php if ( ! empty( $public_id ) ) : ?>
								<span class="post-tag">
									<?php echo novaconcierge_get_icon( 'id' ); ?>
									<?php echo esc_html( $public_id ); ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $construction_size ) && $construction_size > 0 ) : ?>
								<span class="post-tag">
									<?php echo novaconcierge_get_icon( 'construction' ); ?>
									<?php echo esc_html( novaconcierge_format_numeric( $construction_size ) ); ?> m² <?php esc_html_e( 'de construcción', 'novaconcierge' ); ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $lot_size ) && $lot_size > 0 ) : ?>
								<span class="post-tag">
									<?php echo novaconcierge_get_icon( 'lot' ); ?>
									<?php echo esc_html( novaconcierge_format_numeric( $lot_size ) ); ?> m² <?php esc_html_e( 'de terreno', 'novaconcierge' ); ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $bedrooms ) && $bedrooms > 0 ) : ?>
								<span class="post-tag">
									<?php echo novaconcierge_get_icon( 'bedroom' ); ?>
									<?php echo esc_html( $bedrooms ); ?> <?php esc_html_e( 'recámaras', 'novaconcierge' ); ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $bathrooms ) && $bathrooms > 0 ) : ?>
								<span class="post-tag">
									<?php echo novaconcierge_get_icon( 'bathroom' ); ?>
									<?php echo esc_html( $bathrooms ); ?> <?php esc_html_e( 'baños', 'novaconcierge' ); ?>
								</span>
							<?php endif; ?>

							<?php if ( ! empty( $parking ) && $parking > 0 ) : ?>
								<span class="post-tag">
									<?php echo novaconcierge_get_icon( 'parking' ); ?>
									<?php echo esc_html( $parking ); ?> <?php esc_html_e( 'estacionamientos', 'novaconcierge' ); ?>
								</span>
							<?php endif; ?>
						</div>
					</div>
				</footer>
			</div>

			<!-- Bottom Bar Controls (Z-Index highest, always on top) -->
			<?php
			$total_images = count( $gallery_images );
			if ( $total_images > 1 ) :
				?>
				<div class="slideshow-bottom-bar">
					<div class="inset-shadow-effect slideshow-control-container">
						<button type="button" class="slideshow-control prev-slide" aria-label="<?php esc_attr_e( 'Previous Image', 'novaconcierge' ); ?>"><?php stories_svg( 'arrow-left-circle', array( 'size' => 18 ) ); ?></button>
					</div>

					<?php if ( $total_images <= 5 ) : ?>
						<div class="slideshow-dots">
							<?php foreach ( $gallery_images as $index => $image_url ) : ?>
								<span class="dot-nav <?php echo 0 === $index ? 'is-active' : ''; ?>" data-slide-target="<?php echo esc_attr( $index ); ?>"></span>
							<?php endforeach; ?>
						</div>
					<?php else : ?>
						<div class="inset-shadow-effect slideshow-counter-container">
							<div class="slideshow-counter">
								<span class="current-slide">1</span> / <span class="total-slides"><?php echo esc_html( $total_images ); ?></span>
							</div>
						</div>
					<?php endif; ?>

					<div class="inset-shadow-effect slideshow-control-container">
						<button type="button" class="slideshow-control next-slide" aria-label="<?php esc_attr_e( 'Next Image', 'novaconcierge' ); ?>"><?php stories_svg( 'arrow-right-circle', array( 'size' => 18 ) ); ?></button>
					</div>
				</div>
			<?php endif; ?>
		</div>
	<?php else : ?>
		<!-- Fallback if no media found -->
		<p><?php esc_html_e( 'No se encontraron imágenes para la propiedad.', 'novaconcierge' ); ?></p>
	<?php endif; ?>
	<div class="post__overlay"></div>
</article>
