<?php
/**
 * Single Property Content Template Part
 *
 * Displays single property details, gallery slideshow matching content-property.php,
 * technical specifications grid, interactive map embed, and related properties carousel.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

while ( have_posts() ) :
	the_post();
	$property_data = novaconcierge_get_property_data();

	$price             = $property_data['price'];
	$currency          = $property_data['currency'];
	$operation         = $property_data['operation'];
	$property_type     = $property_data['type'];
	$location          = $property_data['location'];
	$gallery           = $property_data['gallery'];
	$map_embed         = $property_data['map_embed'];
	$public_id         = $property_data['public_id'];
	$construction_size = $property_data['construction_size'];
	$lot_size          = $property_data['lot_size'];
	$bedrooms          = $property_data['bedrooms'];
	$bathrooms         = $property_data['bathrooms'];
	$parking           = $property_data['parking'];
	$formatted_price   = novaconcierge_format_price( $price, $currency );
	$type_label        = novaconcierge_translate_property_type( $property_type );

	$operation_label = __( 'En venta', 'novaconcierge' );
	if ( 'rental' === $operation ) {
		$operation_label = __( 'En renta', 'novaconcierge' );
	} elseif ( 'temporary_rental' === $operation ) {
		$operation_label = __( 'Renta temporal', 'novaconcierge' );
	}

	$location_js = esc_js( $location );
?>

<main id="main" class="site-main" role="main">
	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php
		if ( function_exists( 'wp_breadcrumbs' ) ) {
			wp_breadcrumbs();
		}
		?>

		<!-- Property Heading Section -->
		<header class="block property--heading">
			<div class="content">
				<div class="property-data--wrapper">
					<?php the_title( '<h1 class="property-title">', '</h1>' ); ?>
					
					<span class="excerpt">
						<?php
						$property_excerpt = get_the_excerpt();
						$property_excerpt = preg_replace( '/\s*&hellip;\s*<a\b[^>]*>.*?<\/a>/i', '', $property_excerpt );
						$property_excerpt = preg_replace( '/<a\b[^>]*class=["\']read-more["\'][^>]*>.*?<\/a>/i', '', $property_excerpt );
						echo wp_kses_post( wpautop( trim( $property_excerpt ) ) );
						?>
					</span>
				</div>

				<div class="is-layout-constrained">
					<div class="post-gallery-wrapper">
						<?php if ( ! empty( $gallery ) ) : ?>
							<div class="stories-slideshow" data-post-id="<?php the_ID(); ?>">
								<!-- Top Actions (Fullscreen Lightbox Trigger & Like Button) -->
								<div class="post-top-actions gallery-top-actions">
									<div class="toggle-info-container inset-shadow-effect">
										<button type="button" class="image-lightbox-trigger gallery-lightbox-trigger"
												data-is-property="1"
												data-lightbox-src="<?php echo esc_url( ! empty( $gallery[0] ) ? $gallery[0] : '' ); ?>"
												data-lightbox-title="<?php echo esc_attr( get_the_title() ); ?>"
												data-lightbox-caption="<?php echo esc_attr( has_excerpt() ? wp_strip_all_tags( get_the_excerpt() ) : '' ); ?>"
												data-lightbox-url="<?php echo esc_url( get_permalink() ); ?>"
												data-gallery-images="<?php echo esc_attr( wp_json_encode( array_values( $gallery ) ) ); ?>"
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

								<!-- Slides Viewport -->
								<div class="slides-wrapper">
									<?php foreach ( $gallery as $index => $image_url ) : ?>
										<div class="slide-item <?php echo 0 === $index ? 'is-active' : ''; ?>" data-slide-index="<?php echo esc_attr( $index ); ?>" data-full-src="<?php echo esc_url( $image_url ); ?>">
											<img src="<?php echo esc_url( $image_url ); ?>" data-full-src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="<?php echo 0 === $index ? 'eager' : 'lazy'; ?>" decoding="async">
										</div>
									<?php endforeach; ?>
								</div>

								<!-- Bottom Bar Controls -->
								<?php
								$total_images = count( $gallery );
								if ( $total_images > 1 ) :
									?>
									<div class="slideshow-bottom-bar">
										<div class="inset-shadow-effect slideshow-control-container">
											<button type="button" class="slideshow-control prev-slide" aria-label="<?php esc_attr_e( 'Anterior', 'novaconcierge' ); ?>">
												<?php
												if ( function_exists( 'stories_svg' ) ) {
													stories_svg( 'arrow-left-circle', array( 'size' => 18 ) );
												} else {
													echo novaconcierge_get_icon( 'backward' );
												}
												?>
											</button>
										</div>

										<?php if ( $total_images <= 5 ) : ?>
											<div class="slideshow-dots">
												<?php foreach ( $gallery as $index => $image_url ) : ?>
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
											<button type="button" class="slideshow-control next-slide" aria-label="<?php esc_attr_e( 'Siguiente', 'novaconcierge' ); ?>">
												<?php
												if ( function_exists( 'stories_svg' ) ) {
													stories_svg( 'arrow-right-circle', array( 'size' => 18 ) );
												} else {
													echo novaconcierge_get_icon( 'forward' );
												}
												?>
											</button>
										</div>
									</div>
								<?php endif; ?>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</header>

		<!-- Property Details Section -->
		<section class="block property--details">
			<div class="content">
				<h2 class="title-section"><?php esc_html_e( 'Detalles de la propiedad', 'novaconcierge' ); ?></h2>
			</div>
			<div class="content details">
				<div class="property--metadata-stage">
					<div class="property--metadata metadata-slideshow-container cert-container">
						<div class="property--metadata-fingerprint" aria-hidden="true">
							<svg viewBox="0 0 600 280" preserveAspectRatio="none" fill="none" stroke="currentColor" stroke-width="0.85" stroke-linecap="round" stroke-linejoin="round">
								<!-- Core Inner Loops & Whorl Center -->
								<path d="M 270,145 C 270,132 277,124 285,124 C 293,124 300,132 300,145 C 300,156 290,168 282,176" />
								<path d="M 262,145 C 262,126 272,116 285,116 C 298,116 308,126 308,145 C 308,162 295,176 278,186" />
								<path d="M 254,145 C 254,120 267,108 285,108 C 303,108 316,120 316,145 C 316,168 300,184 274,196" />
								<path d="M 246,145 C 246,114 262,100 285,100 C 308,100 324,114 324,145 C 324,174 305,192 270,206" />
								<path d="M 238,145 C 238,108 257,92 285,92 C 313,92 332,108 332,145 C 332,180 310,200 266,216" />
								<path d="M 230,145 C 230,102 252,84 285,84 C 318,84 340,102 340,145 C 340,186 315,208 262,226" />
								<path d="M 222,145 C 222,96 247,76 285,76 C 323,76 348,96 348,145 C 348,192 320,216 258,236" />
								<path d="M 214,145 C 214,90 242,68 285,68 C 328,68 356,90 356,145 C 356,198 325,224 254,246" />
								<path d="M 206,145 C 206,84 237,60 285,60 C 333,60 364,84 364,145 C 364,204 330,232 250,256" />
								<path d="M 198,145 C 198,78 232,52 285,52 C 338,52 372,78 372,145 C 372,210 335,240 246,266" />
								<path d="M 190,145 C 190,72 227,44 285,44 C 343,44 380,72 380,145 C 380,216 340,248 242,276" />
								<!-- Intermediate Arch Flows -->
								<path d="M 182,145 C 182,66 222,36 285,36 C 348,36 388,66 388,145 C 388,222 345,256 238,286" />
								<path d="M 174,145 C 174,60 217,28 285,28 C 353,28 396,60 396,145 C 396,228 350,264 234,296" />
								<path d="M 166,145 C 166,54 212,20 285,20 C 358,20 404,54 404,145 C 404,234 355,272 230,306" />
								<path d="M 158,145 C 158,48 207,12 285,12 C 363,12 412,48 412,145 C 412,240 360,280 226,316" />
								<path d="M 150,145 C 150,42 202,4 285,4 C 368,4 420,42 420,145 C 420,246 365,288 222,326" />
								<path d="M 142,145 C 142,36 197,-4 285,-4 C 373,-4 428,36 428,145 C 428,252 370,296 218,336" />
								<path d="M 134,145 C 134,30 192,-12 285,-12 C 378,-12 436,30 436,145 C 436,258 375,304 214,346" />
								<!-- Flanking Outer Biometric Ridges -->
								<path d="M 120,220 C 135,170 142,120 142,60 C 142,-5 200,-25 285,-25 C 370,-25 428,-5 428,60 C 428,120 435,170 450,220" />
								<path d="M 105,230 C 122,170 130,110 130,48 C 130,-18 190,-40 285,-40 C 380,-40 440,-18 440,48 C 440,110 448,170 465,230" />
								<path d="M 90,240 C 110,170 118,100 118,36 C 118,-30 180,-55 285,-55 C 390,-55 452,-30 452,36 C 452,100 460,170 480,240" />
								<path d="M 75,250 C 98,170 106,90 106,24 C 106,-42 170,-70 285,-70 C 400,-70 464,-42 464,24 C 464,90 472,170 495,250" />
								<path d="M 60,260 C 85,170 94,80 94,12 C 94,-54 160,-85 285,-85 C 410,-85 476,-54 476,12 C 476,80 485,170 510,260" />
								<path d="M 45,270 C 72,170 82,70 82,0 C 82,-66 150,-100 285,-100 C 420,-100 488,-66 488,0 C 488,70 498,170 525,270" />
								<path d="M 30,280 C 60,170 70,60 70,-12 C 70,-78 140,-115 285,-115 C 430,-115 500,-78 500,-12 C 500,60 510,170 540,280" />
								<!-- Wide Sweeping Valley Base Lines -->
								<path d="M 0,225 C 100,215 190,230 285,235 C 380,230 470,215 600,225" />
								<path d="M 0,240 C 100,230 190,245 285,250 C 380,245 470,230 600,240" />
								<path d="M 0,255 C 100,245 190,260 285,265 C 380,260 470,245 600,255" />
								<path d="M 0,270 C 100,260 190,275 285,280 C 380,275 470,260 600,270" />
								<!-- Top Crest Ridges -->
								<path d="M 0,95 C 95,55 190,20 285,20 C 380,20 475,55 600,95" />
								<path d="M 0,80 C 95,42 190,8 285,8 C 380,8 475,42 600,80" />
								<path d="M 0,65 C 95,29 190,-4 285,-4 C 380,-4 475,29 600,65" />
								<path d="M 0,50 C 95,16 190,-16 285,-16 C 380,-16 475,16 600,50" />
								<path d="M 0,35 C 95,3 190,-28 285,-28 C 380,-28 475,3 600,35" />
							</svg>
						</div>
						<div class="slideshow--wrapper metadata-slider-track-wrapper">
							<ul class="property--metadata--list slideshow metadata-slider-track">
								<?php novaconcierge_render_full_property_metadata(); ?>
							</ul>
						</div>
						<div class="slideshow-bullets-wrapper metadata-slider-nav">
							<button type="button" class="slideshow-prev meta-slider-btn meta-prev-btn btn-pagination small-pagination" aria-label="<?php esc_attr_e( 'Anterior', 'novaconcierge' ); ?>">
								<?php echo function_exists( 'stories_get_svg' ) ? stories_get_svg( 'arrow-left-circle', array( 'size' => 18 ) ) : novaconcierge_get_icon( 'backward' ); ?>
							</button>
							<div class="slideshow-counter metadata-slider-counter">
								<span class="counter-current">1</span>
								<span class="counter-divider">/</span>
								<span class="counter-total">1</span>
							</div>
							<button type="button" class="slideshow-next meta-slider-btn meta-next-btn btn-pagination small-pagination" aria-label="<?php esc_attr_e( 'Siguiente', 'novaconcierge' ); ?>">
								<?php echo function_exists( 'stories_get_svg' ) ? stories_get_svg( 'arrow-right-circle', array( 'size' => 18 ) ) : novaconcierge_get_icon( 'forward' ); ?>
							</button>
						</div>
					</div>
				</div>
				<div class="is-layout-constrained">
					<?php the_content(); ?>
				</div>
			</div>
		</section>

		<?php
		endwhile;

		// Extract city and state from location string for nearby query
		$city  = '';
		$state = '';

		if ( ! empty( $location ) ) {
			$parts = array_map( 'trim', explode( ',', $location ) );
			$count = count( $parts );
			if ( $count >= 2 ) {
				$city  = $parts[ $count - 2 ];
				$state = $parts[ $count - 1 ];
			}
		}

		// WP_Query for related properties
		$related_meta_query = array( 'relation' => 'OR' );
		if ( ! empty( $city ) ) {
			$related_meta_query[] = array(
				'key'     => 'eb_location',
				'value'   => $city,
				'compare' => 'LIKE',
			);
		}
		if ( ! empty( $state ) ) {
			$related_meta_query[] = array(
				'key'     => 'eb_location',
				'value'   => $state,
				'compare' => 'LIKE',
			);
		}

		$args = array(
			'post_type'      => 'property',
			'post_status'    => 'publish',
			'posts_per_page' => 8,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'no_found_rows'  => true,
			'post__not_in'   => array( get_the_ID() ),
		);

		if ( count( $related_meta_query ) > 1 ) {
			$args['meta_query'] = $related_meta_query;
		}

		$related_query = new WP_Query( $args );

		if ( $related_query->have_posts() ) :
		?>
		<!-- Related Properties Carousel Section -->
		<section class="block posts--body container--related-posts">
			<div class="content related-posts--title">
				<h2 class="title-section"><?php esc_html_e( 'Propiedades cercanas', 'novaconcierge' ); ?></h2>
			</div>
			<div class="content slideshow-wrapper">
				<div class="slideshow-mask-container">
					<div class="related-posts--list slideshow">
						<?php
						while ( $related_query->have_posts() ) :
							$related_query->the_post();
							get_template_part( 'template-parts/content', 'property' );
						endwhile;
						?>
					</div>
				</div>
				<div class="navigation">
					<div class="slideshow-control-container inset-shadow-effect">
						<button id="related-products--backward-button" class="slide-prev btn-pagination small-pagination slideshow-control" aria-label="<?php esc_attr_e( 'Anterior', 'novaconcierge' ); ?>">
							<?php echo function_exists( 'stories_get_svg' ) ? stories_get_svg( 'arrow-left-circle', array( 'size' => 18 ) ) : novaconcierge_get_icon( 'backward' ); ?>
						</button>
					</div>
					<div class="related-bullets"></div>
					<div class="slideshow-control-container inset-shadow-effect">
						<button id="related-products--forward-button" class="slide-next btn-pagination small-pagination slideshow-control" aria-label="<?php esc_attr_e( 'Siguiente', 'novaconcierge' ); ?>">
							<?php echo function_exists( 'stories_get_svg' ) ? stories_get_svg( 'arrow-right-circle', array( 'size' => 18 ) ) : novaconcierge_get_icon( 'forward' ); ?>
						</button>
					</div>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
		endif;
		?>
	</article>
</main>

<script>
document.addEventListener('DOMContentLoaded', () => {
	const mapContainer = document.getElementById('property-map');
	const propertyLocation = "<?php echo esc_js( $location ); ?>";

	// If container already has an iframe (e.g. from manual map_embed), skip dynamic loader
	if (!mapContainer || mapContainer.querySelector('iframe') || !propertyLocation) return;

	const parts = propertyLocation.split(',').map(p => p.trim());
	const city = parts.length >= 2 ? parts[parts.length - 2] : null;
	const state = parts.length >= 1 ? parts[parts.length - 1] : null;

	const renderMap = (lat, lon, label = '') => {
		mapContainer.innerHTML = `
			<iframe
				width="100%"
				height="100%"
				style="border:0; min-height: 220px;"
				loading="lazy"
				allowfullscreen
				src="https://www.google.com/maps?q=${lat},${lon}&hl=es;z=12&output=embed"
				title="Mapa ${label}">
			</iframe>
		`;
	};

	const fetchCoords = async (query) => {
		const response = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}`);
		const data = await response.json();
		return (data && data.length > 0) ? { lat: data[0].lat, lon: data[0].lon } : null;
	};

	(async () => {
		let coords = await fetchCoords(propertyLocation);

		if (!coords && city) {
			coords = await fetchCoords(city);
		}

		if (!coords && state) {
			coords = await fetchCoords(state);
		}

		if (!coords) {
			coords = { lat: 23.6345, lon: -102.5528 }; // Default center of Mexico
		}

		renderMap(coords.lat, coords.lon, propertyLocation);
	})().catch(err => {
		console.error('Error loading map:', err);
		mapContainer.innerHTML = '<p class="map-error">' + <?php echo wp_json_encode( __( 'Ubicación no disponible', 'novaconcierge' ) ); ?> + '</p>';
	});
});
</script>
