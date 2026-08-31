<?php
/**
 * Property Sidebar Filters Template Part
 *
 * Left-aligned filter sidebar on desktop with interactive accordion sections,
 * pill selectors, numeric steppers, range sliders, and minimized collapsible mode on mobile.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$locations          = novaconcierge_get_property_locations();
$price_range        = novaconcierge_get_property_price_range();
$construction_range = novaconcierge_get_property_construction_range();
$land_range         = novaconcierge_get_property_land_range();
$property_types     = novaconcierge_get_property_types();
$operation_types    = novaconcierge_get_operation_types();
?>

<aside class="sidebar properties--filter properties-sidebar" id="nc-properties-sidebar" aria-label="<?php esc_attr_e( 'Filtros de propiedades', 'novaconcierge' ); ?>">
	<form class="property-filter-form" id="nc-property-filter-form" method="post" action="">
		<input type="hidden" name="action" value="novaconcierge_filter_properties">
		<input type="hidden" name="paged" value="1">

		<!-- Sidebar / Mobile Bar Header -->
		<div class="block-filter filter-buttons">
			<div class="filter-header-left">
				<?php echo novaconcierge_get_icon( 'filter' ); ?>
				<h2 class="title-section"><?php esc_html_e( 'Filtros', 'novaconcierge' ); ?></h2>
				<span class="active-filter-badge" id="nc-active-filters-count" style="display:none;">0</span>
			</div>
			<div class="filter-header-right">
				<button type="button" class="btn-toggle-filters-mobile" id="nc-toggle-filters-btn" aria-expanded="false" aria-controls="nc-filter-drawer" aria-label="<?php esc_attr_e( 'Mostrar u ocultar filtros', 'novaconcierge' ); ?>">
					<span><?php esc_html_e( 'Filtros', 'novaconcierge' ); ?></span>
					<?php echo novaconcierge_get_icon( 'chevron-down' ); ?>
				</button>
				<button class="btn-reset-filters" type="button" id="nc-reset-filters-btn" title="<?php esc_attr_e( 'Limpiar todos los filtros', 'novaconcierge' ); ?>">
					<?php esc_html_e( 'Limpiar', 'novaconcierge' ); ?>
				</button>
			</div>
		</div>

		<!-- Filter Drawer / Collapsible Body on Mobile, Static Sidebar on Desktop -->
		<div class="filter-drawer-body" id="nc-filter-drawer">

			<!-- 1. Search Box -->
			<div class="block-filter filter-search-block">
				<div class="filter-search-field">
					<?php echo novaconcierge_get_icon( 'search' ); ?>
					<input type="text" id="filter-search-input" name="search" aria-label="<?php esc_attr_e( 'Palabras clave', 'novaconcierge' ); ?>" placeholder="<?php esc_attr_e( 'Buscar por ciudad, tipo, palabra clave...', 'novaconcierge' ); ?>" autocomplete="off">
				</div>
			</div>

			<!-- 2. Location Select Box -->
			<?php if ( ! empty( $locations ) ) : ?>
				<div class="block-filter filter-location-block">
					<div class="filter-search-field filter-location-field">
						<?php echo novaconcierge_get_icon( 'location' ); ?>
						<div class="filter-select-wrapper">
							<select name="location" id="filter-location" class="filter-location-select">
								<option value=""><?php esc_html_e( 'Todas las ubicaciones', 'novaconcierge' ); ?></option>
								<?php foreach ( $locations as $state => $cities ) : ?>
									<optgroup label="<?php echo esc_attr( $state ); ?>">
										<option value="<?php echo esc_attr( $state ); ?>"><?php echo esc_html( $state ); ?> (<?php esc_html_e( 'Todo el estado', 'novaconcierge' ); ?>)</option>
										<?php foreach ( $cities as $city ) : ?>
											<option value="<?php echo esc_attr( $city ); ?>"><?php echo esc_html( $city ); ?></option>
										<?php endforeach; ?>
									</optgroup>
								<?php endforeach; ?>
							</select>
						</div>
					</div>
				</div>
			<?php endif; ?>

			<!-- 3. Operation Type (Pills) -->
			<?php if ( ! empty( $operation_types ) ) : ?>
				<div class="block-filter filter-operation-block">
					<div class="post--tags__wrapper">
						<fieldset class="menu-flex">
							<legend class="screen-reader-text"><?php esc_html_e( 'Tipo de Operación', 'novaconcierge' ); ?></legend>
							<div class="menu-flex--operation post--tags" data-scroll-state="start">
								<?php foreach ( $operation_types as $op_key => $op_label ) : 
									$icon_key = ( 'rental' === $op_key || 'temporary_rental' === $op_key ) ? 'rent' : 'sale';
								?>
									<div class="filter-property-pill post-tag">
										<input type="checkbox" id="op-<?php echo esc_attr( $op_key ); ?>" name="operation[]" value="<?php echo esc_attr( $op_key ); ?>">
										<label for="op-<?php echo esc_attr( $op_key ); ?>">
											<?php echo novaconcierge_get_icon( $icon_key ); ?>
											<span><?php echo esc_html( $op_label ); ?></span>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</fieldset>
					</div>
				</div>
			<?php endif; ?>

			<!-- 4. Property Types (Chips) -->
			<?php if ( ! empty( $property_types ) ) : ?>
				<div class="block-filter filter-types-block">
					<div class="post--tags__wrapper">
						<fieldset class="menu-flex">
							<legend class="screen-reader-text"><?php esc_html_e( 'Tipo de Propiedad', 'novaconcierge' ); ?></legend>
							<div class="menu-flex--type post--tags" data-scroll-state="start">
								<?php foreach ( $property_types as $type_key => $type_label ) : 
									$type_icon = 'home';
									if ( 'apartment' === $type_key || 'building' === $type_key ) {
										$type_icon = 'construction';
									} elseif ( 'land' === $type_key ) {
										$type_icon = 'garden';
									} elseif ( 'commercial' === $type_key ) {
										$type_icon = 'store';
									} elseif ( 'warehouse' === $type_key || 'industrial_warehouse' === $type_key ) {
										$type_icon = 'warehouse';
									}
								?>
									<div class="filter-property-pill post-tag">
										<input type="checkbox" id="type-<?php echo esc_attr( $type_key ); ?>" name="type[]" value="<?php echo esc_attr( $type_key ); ?>">
										<label for="type-<?php echo esc_attr( $type_key ); ?>">
											<?php echo novaconcierge_get_icon( $type_icon ); ?>
											<span><?php echo esc_html( $type_label ); ?></span>
										</label>
									</div>
								<?php endforeach; ?>
							</div>
						</fieldset>
					</div>
				</div>
			<?php endif; ?>

			<!-- 5. Accordion List -->
			<div class="block-filter filter-accordion-sections">
				<ul class="filter-navigation menu">

					<!-- Rooms & Bathrooms Accordion -->
					<li class="menu-item-has-children filter-accordion-item filter-rooms">
						<button class="btn-filter button-for-submenu" type="button" aria-expanded="false" aria-label="<?php esc_attr_e( 'Abrir filtro de habitaciones', 'novaconcierge' ); ?>">
							<?php echo novaconcierge_get_icon( 'bedroom' ); ?>
							<span><?php esc_html_e( 'Habitaciones y Baños', 'novaconcierge' ); ?></span>
							<?php echo novaconcierge_get_icon( 'chevron-down' ); ?>
						</button>
						<ul class="sub-menu rooms-sub-menu">
							<li class="stepper-item">
								<label for="bedrooms"><?php esc_html_e( 'Recámaras (mín.)', 'novaconcierge' ); ?></label>
								<div class="number-input-wrapper">
									<button type="button" class="btn-decrease" data-target="bedrooms" aria-label="<?php esc_attr_e( 'Disminuir recámaras', 'novaconcierge' ); ?>"><?php echo novaconcierge_get_icon( 'minus' ); ?></button>
									<input type="number" name="bedrooms" id="bedrooms" min="0" placeholder="0">
									<button type="button" class="btn-increase" data-target="bedrooms" aria-label="<?php esc_attr_e( 'Aumentar recámaras', 'novaconcierge' ); ?>"><?php echo novaconcierge_get_icon( 'plus' ); ?></button>
								</div>
							</li>
							<li class="stepper-item">
								<label for="bathrooms"><?php esc_html_e( 'Baños (mín.)', 'novaconcierge' ); ?></label>
								<div class="number-input-wrapper">
									<button type="button" class="btn-decrease" data-target="bathrooms" aria-label="<?php esc_attr_e( 'Disminuir baños', 'novaconcierge' ); ?>"><?php echo novaconcierge_get_icon( 'minus' ); ?></button>
									<input type="number" name="bathrooms" id="bathrooms" min="0" placeholder="0">
									<button type="button" class="btn-increase" data-target="bathrooms" aria-label="<?php esc_attr_e( 'Aumentar baños', 'novaconcierge' ); ?>"><?php echo novaconcierge_get_icon( 'plus' ); ?></button>
								</div>
							</li>
						</ul>
					</li>

					<!-- Price Accordion -->
					<li class="menu-item-has-children filter-accordion-item filter-price">
						<button class="btn-filter button-for-submenu" type="button" aria-expanded="false" aria-label="<?php esc_attr_e( 'Abrir filtro de precio', 'novaconcierge' ); ?>">
							<?php echo novaconcierge_get_icon( 'price' ); ?>
							<span><?php esc_html_e( 'Precio ($)', 'novaconcierge' ); ?></span>
							<?php echo novaconcierge_get_icon( 'chevron-down' ); ?>
						</button>
						<ul class="sub-menu price-sub-menu">
							<div class="price-input-group">
								<div>
									<label for="filter-min-price"><?php esc_html_e( 'Mínimo', 'novaconcierge' ); ?></label>
									<input type="number" id="filter-min-price" name="min_price" placeholder="<?php esc_attr_e( 'Mín $', 'novaconcierge' ); ?>" min="0" step="50000">
								</div>
								<div>
									<label for="filter-max-price"><?php esc_html_e( 'Máximo', 'novaconcierge' ); ?></label>
									<input type="number" id="filter-max-price" name="max_price" placeholder="<?php esc_attr_e( 'Máx $', 'novaconcierge' ); ?>" min="0" step="50000">
								</div>
							</div>
							<div class="price-range-slider-group">
								<label for="price_range"><?php esc_html_e( 'Rango estimado:', 'novaconcierge' ); ?></label>
								<input type="range" id="price_range" min="<?php echo esc_attr( $price_range['min'] ); ?>" max="<?php echo esc_attr( $price_range['max'] ); ?>" step="100000" value="<?php echo esc_attr( $price_range['min'] ); ?>">
								<div class="range-display-badge">
									<span id="price-range-value">$<?php echo esc_html( novaconcierge_format_numeric( $price_range['min'] ) ); ?></span>
								</div>
							</div>
						</ul>
					</li>

					<!-- Size (Construction & Land) Accordion -->
					<li class="menu-item-has-children filter-accordion-item filter-size">
						<button class="btn-filter button-for-submenu" type="button" aria-expanded="false" aria-label="<?php esc_attr_e( 'Abrir filtro de medidas', 'novaconcierge' ); ?>">
							<?php echo novaconcierge_get_icon( 'size' ); ?>
							<span><?php esc_html_e( 'Medidas (m²)', 'novaconcierge' ); ?></span>
							<?php echo novaconcierge_get_icon( 'chevron-down' ); ?>
						</button>
						<ul class="sub-menu size-sub-menu">
							<!-- Construction Size -->
							<div class="size-group-section">
								<label class="size-group-title"><?php esc_html_e( 'Construcción (m²)', 'novaconcierge' ); ?></label>
								<div class="size-inputs-row">
									<input type="number" id="construction_min" name="construction_min" placeholder="<?php esc_attr_e( 'Mín m²', 'novaconcierge' ); ?>" min="0" step="10">
									<span class="size-sep">-</span>
									<input type="number" id="construction_max" name="construction_max" placeholder="<?php esc_attr_e( 'Máx m²', 'novaconcierge' ); ?>" min="0" step="10">
								</div>
							</div>

							<!-- Land Size -->
							<div class="size-group-section">
								<label class="size-group-title"><?php esc_html_e( 'Terreno (m²)', 'novaconcierge' ); ?></label>
								<div class="size-inputs-row">
									<input type="number" id="land_min" name="land_min" placeholder="<?php esc_attr_e( 'Mín m²', 'novaconcierge' ); ?>" min="0" step="10">
									<span class="size-sep">-</span>
									<input type="number" id="land_max" name="land_max" placeholder="<?php esc_attr_e( 'Máx m²', 'novaconcierge' ); ?>" min="0" step="10">
								</div>
							</div>
						</ul>
					</li>

				</ul>
			</div>

		</div>
	</form>
</aside>
