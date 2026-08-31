<?php
/**
 * EasyBroker Settings and Synchronization Admin Page
 *
 * Admin interface for managing EasyBroker API keys, viewing synchronization status,
 * and triggering manual updates.
 *
 * @package NovaConcierge
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Submenu Page under Properties menu.
 */
function novaconcierge_register_admin_menu_pages() {
	add_submenu_page(
		'edit.php?post_type=property',
		__( 'Configuración de EasyBroker', 'novaconcierge' ),
		__( 'Configuración EasyBroker', 'novaconcierge' ),
		'manage_options',
		'novaconcierge-easybroker-settings',
		'novaconcierge_render_easybroker_settings_page'
	);
}
add_action( 'admin_menu', 'novaconcierge_register_admin_menu_pages' );

/**
 * Render the EasyBroker settings admin page.
 */
function novaconcierge_render_easybroker_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'No tienes permisos suficientes para acceder a esta página.', 'novaconcierge' ) );
	}

	// Save settings form handler
	if ( isset( $_POST['novaconcierge_save_eb_settings'] ) && check_admin_referer( 'novaconcierge_eb_settings_action', 'novaconcierge_eb_settings_nonce' ) ) {
		$submitted_keys = isset( $_POST['eb_api_keys'] ) ? (array) $_POST['eb_api_keys'] : array();
		$trimmed_keys   = array_map( 'trim', $submitted_keys );
		$cleaned_keys   = array_values( array_unique( array_filter( $trimmed_keys, 'strlen' ) ) );

		update_option( 'novaconcierge_eb_api_keys', $cleaned_keys );

		// Legacy backward compatibility sync
		update_option( 'avante_eb_api_keys', $cleaned_keys );

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__( 'Configuración guardada correctamente.', 'novaconcierge' ) . '</p></div>';
	}

	$keys           = novaconcierge_get_eb_api_keys();
	if ( empty( $keys ) ) {
		$keys = array( '' );
	}

	$last_sync      = get_option( 'novaconcierge_eb_last_sync_time' ) ?: get_option( 'eb_last_sync_time' );
	$total_props    = wp_count_posts( 'property' )->publish ?? 0;
	$cron_scheduled = wp_next_scheduled( 'novaconcierge_eb_daily_sync' );
	?>
	<div class="wrap novaconcierge-admin-wrap" style="max-width: 900px; margin-top: 20px;">
		<h1 style="font-size: 24px; font-weight: 700; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
			<span class="dashicons dashicons-admin-multisite" style="font-size: 28px; width: 28px; height: 28px;"></span>
			<?php esc_html_e( 'Configuración y Sincronización de EasyBroker', 'novaconcierge' ); ?>
		</h1>

		<!-- Status Bar -->
		<div style="background: #fff; border: 1px solid #c3c4c7; border-left: 4px solid #2271b1; padding: 15px 20px; border-radius: 4px; margin-bottom: 25px; display: flex; flex-wrap: wrap; gap: 30px;">
			<div>
				<strong style="color: #646970; display: block; font-size: 11px; text-transform: uppercase;"><?php esc_html_e( 'Total de Propiedades', 'novaconcierge' ); ?></strong>
				<span style="font-size: 20px; font-weight: 700; color: #1d2327;"><?php echo esc_html( $total_props ); ?></span>
			</div>
			<div>
				<strong style="color: #646970; display: block; font-size: 11px; text-transform: uppercase;"><?php esc_html_e( 'Última Sincronización', 'novaconcierge' ); ?></strong>
				<span style="font-size: 14px; font-weight: 600; color: #1d2327; line-height: 28px;">
					<?php echo $last_sync ? esc_html( date_i18n( 'd/m/Y H:i:s', strtotime( $last_sync ) ) ) : esc_html__( 'Nunca sincronizado', 'novaconcierge' ); ?>
				</span>
			</div>
			<div>
				<strong style="color: #646970; display: block; font-size: 11px; text-transform: uppercase;"><?php esc_html_e( 'Sincronización Automática', 'novaconcierge' ); ?></strong>
				<span style="font-size: 14px; font-weight: 600; color: #008a20; line-height: 28px;">
					<?php echo $cron_scheduled ? esc_html__( 'Activa (Diaria)', 'novaconcierge' ) : esc_html__( 'Inactiva', 'novaconcierge' ); ?>
				</span>
			</div>
		</div>

		<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
			<!-- API Keys Box -->
			<div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; border-radius: 6px;">
				<h2 style="font-size: 16px; margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
					<?php esc_html_e( 'API Keys de EasyBroker', 'novaconcierge' ); ?>
				</h2>
				<p class="description" style="margin-bottom: 15px;">
					<?php esc_html_e( 'Ingresa tus claves de acceso de EasyBroker (X-Authorization). Puedes añadir varias si administras múltiples cuentas.', 'novaconcierge' ); ?>
				</p>

				<form method="post" action="">
					<?php wp_nonce_field( 'novaconcierge_eb_settings_action', 'novaconcierge_eb_settings_nonce' ); ?>

					<div id="nc-eb-keys-wrapper">
						<?php foreach ( $keys as $key_val ) : ?>
							<div class="nc-eb-key-row" style="display: flex; gap: 8px; align-items: center; margin-bottom: 10px;">
								<input type="text" name="eb_api_keys[]" value="<?php echo esc_attr( $key_val ); ?>" class="regular-text" style="flex: 1;" placeholder="<?php esc_attr_e( 'Pega aquí la API Key', 'novaconcierge' ); ?>">
								<button type="button" class="button nc-remove-key" style="color: #b32d2e; border-color: #b32d2e;">&times;</button>
							</div>
						<?php endforeach; ?>
					</div>

					<button type="button" id="nc-add-eb-key" class="button button-secondary" style="margin-top: 5px; margin-bottom: 20px;">
						<span class="dashicons dashicons-plus-alt2" style="vertical-align: middle; margin-top: -2px;"></span>
						<?php esc_html_e( 'Agregar otra clave', 'novaconcierge' ); ?>
					</button>

					<div>
						<?php submit_button( __( 'Guardar Claves', 'novaconcierge' ), 'primary', 'novaconcierge_save_eb_settings', false ); ?>
					</div>
				</form>
			</div>

			<!-- Manual Sync Box -->
			<div style="background: #fff; border: 1px solid #c3c4c7; padding: 20px; border-radius: 6px;">
				<h2 style="font-size: 16px; margin-top: 0; padding-bottom: 10px; border-bottom: 1px solid #eee;">
					<?php esc_html_e( 'Sincronización Inmediata', 'novaconcierge' ); ?>
				</h2>
				<p class="description" style="margin-bottom: 15px;">
					<?php esc_html_e( 'Descarga y actualiza todas las propiedades de EasyBroker inmediatamente. Las propiedades se guardarán localmente.', 'novaconcierge' ); ?>
				</p>

				<div style="margin: 25px 0;">
					<button type="button" id="nc-start-sync-btn" class="button button-hero button-primary" style="display: inline-flex; align-items: center; gap: 8px;">
						<span class="dashicons dashicons-update" id="nc-sync-spinner" style="font-size: 20px; width: 20px; height: 20px;"></span>
						<span id="nc-sync-btn-text"><?php esc_html_e( 'Sincronizar Ahora', 'novaconcierge' ); ?></span>
					</button>
				</div>

				<div id="nc-sync-feedback" style="display: none; padding: 12px; border-radius: 4px; font-size: 13px;"></div>
			</div>
		</div>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', () => {
		// Add new API key row
		const addBtn = document.getElementById('nc-add-eb-key');
		const keysWrapper = document.getElementById('nc-eb-keys-wrapper');

		if (addBtn && keysWrapper) {
			addBtn.addEventListener('click', () => {
				const row = document.createElement('div');
				row.className = 'nc-eb-key-row';
				row.style.cssText = 'display: flex; gap: 8px; align-items: center; margin-bottom: 10px;';
				row.innerHTML = `
					<input type="text" name="eb_api_keys[]" value="" class="regular-text" style="flex: 1;" placeholder="Pega aquí la API Key">
					<button type="button" class="button nc-remove-key" style="color: #b32d2e; border-color: #b32d2e;">&times;</button>
				`;
				keysWrapper.appendChild(row);
			});

			keysWrapper.addEventListener('click', (e) => {
				if (e.target && e.target.classList.contains('nc-remove-key')) {
					const row = e.target.closest('.nc-eb-key-row');
					if (keysWrapper.querySelectorAll('.nc-eb-key-row').length > 1) {
						row.remove();
					} else {
						row.querySelector('input').value = '';
					}
				}
			});
		}

		// Manual Sync AJAX trigger
		const syncBtn = document.getElementById('nc-start-sync-btn');
		const syncSpinner = document.getElementById('nc-sync-spinner');
		const syncBtnText = document.getElementById('nc-sync-btn-text');
		const syncFeedback = document.getElementById('nc-sync-feedback');

		if (syncBtn) {
			syncBtn.addEventListener('click', async () => {
				if (syncBtn.disabled) return;

				syncBtn.disabled = true;
				syncSpinner.style.animation = 'rotation 1s infinite linear';
				syncBtnText.textContent = 'Sincronizando... por favor espera';
				syncFeedback.style.display = 'block';
				syncFeedback.style.background = '#f0f6fc';
				syncFeedback.style.border = '1px solid #cce5ff';
				syncFeedback.style.color = '#004085';
				syncFeedback.textContent = 'Conectando con EasyBroker e importando propiedades...';

				const formData = new FormData();
				formData.append('action', 'novaconcierge_sync_properties');
				formData.append('security', '<?php echo esc_js( wp_create_nonce( 'novaconcierge_eb_sync_nonce' ) ); ?>');

				try {
					const response = await fetch(ajaxurl, {
						method: 'POST',
						body: formData
					});
					const data = await response.json();

					if (data.success) {
						syncFeedback.style.background = '#d4edda';
						syncFeedback.style.border = '1px solid #c3e6cb';
						syncFeedback.style.color = '#155724';
						syncFeedback.innerHTML = '<strong>¡Éxito!</strong> ' + data.data;
						setTimeout(() => window.location.reload(), 2500);
					} else {
						syncFeedback.style.background = '#f8d7da';
						syncFeedback.style.border = '1px solid #f5c6cb';
						syncFeedback.style.color = '#721c24';
						syncFeedback.innerHTML = '<strong>Error:</strong> ' + (data.data || 'Ocurrió un error al sincronizar.');
					}
				} catch (err) {
					syncFeedback.style.background = '#f8d7da';
					syncFeedback.style.border = '1px solid #f5c6cb';
					syncFeedback.style.color = '#721c24';
					syncFeedback.textContent = 'Error de conexión con el servidor.';
				} finally {
					syncBtn.disabled = false;
					syncSpinner.style.animation = '';
					syncBtnText.textContent = 'Sincronizar Ahora';
				}
			});
		}
	});
	</script>
	<style>
	@keyframes rotation { from { transform: rotate(0deg); } to { transform: rotate(359deg); } }
	</style>
	<?php
}
