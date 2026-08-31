<?php
/**
 * Template Name: Propiedades
 *
 * Page template for the main property catalog with left sidebar filters and interactive AJAX filtering.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

get_header();
?>

<main id="main" class="site-main" role="main">

	<?php if ( function_exists( 'wp_breadcrumbs' ) ) : ?>
		<?php wp_breadcrumbs(); ?>
	<?php endif; ?>

	<!-- Property Archive Section with Left Sidebar & Results Grid -->
	<section class="block posts--body properties-catalog-section">
		<div class="content">
			<?php get_template_part( 'templates/filter-bar' ); ?>

			<div class="posts-grid properties--list" id="nc-properties-list-container">
				<?php
				$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
				$query = new WP_Query( array(
					'post_type'      => 'property',
					'post_status'    => 'publish',
					'posts_per_page' => 12,
					'paged'          => $paged,
					'orderby'        => 'date',
					'order'          => 'DESC',
				) );

				if ( $query->have_posts() ) :
					while ( $query->have_posts() ) :
						$query->the_post();
						get_template_part( 'template-parts/content', 'property' );
					endwhile;

					novaconcierge_pagination( $query, $paged );
				else :
				?>
					<div class="no-properties-found" style="grid-column: 1 / -1; text-align: center; padding: 40px 20px; background: rgba(255,255,255,0.6); border-radius: 12px; margin: 20px 0;">
						<p style="font-size: 18px; color: #666;"><?php esc_html_e( 'No se encontraron propiedades disponibles.', 'novaconcierge' ); ?></p>
					</div>
				<?php
				endif;
				wp_reset_postdata();
				?>
			</div>
		</div>
	</section>

</main>

<?php
get_footer();
