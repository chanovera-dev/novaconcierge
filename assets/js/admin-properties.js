/**
 * Nova Concierge - Admin Property Meta Boxes
 *
 * Media library integration for property gallery upload and management without ACF/SCF.
 *
 * @package NovaConcierge
 */

jQuery(document).ready(function ($) {
	let mediaFrame;
	const galleryPreview = $('#nc-gallery-preview');
	const addGalleryBtn = $('#nc-add-gallery-images');

	if (addGalleryBtn.length && galleryPreview.length) {
		addGalleryBtn.on('click', function (e) {
			e.preventDefault();

			if (mediaFrame) {
				mediaFrame.open();
				return;
			}

			mediaFrame = wp.media({
				title: 'Seleccionar Imágenes para la Galería de la Propiedad',
				button: {
					text: 'Añadir a la Galería'
				},
				multiple: true,
				library: {
					type: 'image'
				}
			});

			mediaFrame.on('select', function () {
				const selection = mediaFrame.state().get('selection');

				selection.map(function (attachment) {
					attachment = attachment.toJSON();
					const imgUrl = attachment.url;

					// Avoid exact duplicate addition in preview
					let exists = false;
					galleryPreview.find('input[name="eb_gallery[]"]').each(function () {
						if ($(this).val() === imgUrl) {
							exists = true;
						}
					});

					if (!exists && imgUrl) {
						const itemHtml = `
							<div class="nc-gallery-item" style="position: relative; width: 110px; height: 110px; border-radius: 6px; overflow: hidden; border: 1px solid #ddd; background: #f0f0f1;">
								<img src="${imgUrl}" style="width: 100%; height: 100%; object-fit: cover;" alt="">
								<input type="hidden" name="eb_gallery[]" value="${imgUrl}">
								<button type="button" class="nc-remove-image button-link" style="position: absolute; top: 4px; right: 4px; background: rgba(0,0,0,0.7); color: #fff; border: none; border-radius: 50%; width: 22px; height: 22px; cursor: pointer; display: flex; align-items: center; justify-content: center; font-size: 14px; line-height: 1;">&times;</button>
							</div>
						`;
						galleryPreview.append(itemHtml);
					}
				});
			});

			mediaFrame.open();
		});

		// Remove individual image from gallery
		galleryPreview.on('click', '.nc-remove-image', function (e) {
			e.preventDefault();
			$(this).closest('.nc-gallery-item').remove();
		});
	}
});
