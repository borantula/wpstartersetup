<?php
/*
 * Copyright 2012, Theia Smart Thumbnails, Liviu Cristian Mirea Ghiban.
 */

class TstMisc {
	const
		META_POSITION_PICKER = 'theiaSmartThumbnails_positionPicker',
		META_POSITION = 'theiaSmartThumbnails_position';

	/**
	 * This variable stores the last post ID used in "get_attached_file" or "wp_get_attachment_metadata".
	 * These functions are always called before "image_resize_dimensions".
	 * Using this, we can get the post's meta data in "image_resize_dimensions".
	 */
	private static $lastPostId = null;

	// Add fields to the Media Upload dialog.
	public static function attachment_fields_to_edit($form_fields, $post) {
		global $wp_version;

		if (!is_array($form_fields)) {
			$form_fields = array();
		}

		// Get saved values.
		$meta = self::getMeta($post->ID);

		// Create HTML.
		$html = '';
		$image = wp_get_attachment_image_src($post->ID, 'medium');
		$imageId = 'theiaSmartThumbnails_picker_image_' . $post->ID;
		$previewId = 'theiaSmartThumbnails_picker_preview_' . $post->ID;
		$inputId = 'theiaSmartThumbnails_picker_input_' . $post->ID;
		$html .= '<p><span class="_targetIcon"></span>' . __('Click on the point of interest (the area you want included in the thumbnail).') . '</p>';
		$html .= '<div id="' . $imageId . '" class="_picker"><img src="' . $image[0] . '"></div>';
		$html .= '<p><span class="_previewIcon"></span><strong>' . __('Preview') . '</strong> (' . __('this is how the thumbnails will look, depending on the size used') . '):</p>';
		$html .= '<div id="' . $previewId . '" class="_preview"></div>';
		$html = '<div class="theiaSmartThumbnails_mediaUpload">' . $html . '</div>';

		// Create JavaScript.
		$size = 150;
		$sizes = array(
			'Square' => array(
				'width' => $size,
				'height' => $size
			),
			'Potrait' => array(
				'width' => $size / 2,
				'height' => $size
			),
			'Landscape' => array(
				'width' => $size,
				'height' => $size / 2
			)
		);

		// The script will initialize the picker. If the elements do not yet exist, it will wait a while until retrying.
		$script = '
			jQuery(document).ready(function() {
				tst.createPickerDelayed({
					attachmentId: "' . $post->ID . '",
					image: "#' . $imageId . '",
					input: "input[name=\'attachments[' . $post->ID . '][' . self::META_POSITION . ']\']",
					preview: "#' . $previewId . '",
					sizes: ' . json_encode($sizes) . ',
					position: {
						x: ' . $meta[0] . ',
						y: ' . $meta[1] . '
					}
				});
			});
		';
		$html .= '<script type="text/javascript">' . $script . '</script>';

		// Add form field
		$form_fields[self::META_POSITION] = array(
			'input'      => 'text',
			'label'      => __('Theia Smart Thumbnail Crop'),
		);
		$form_fields[self::META_POSITION_PICKER] = array(
			'label'      => __('Theia Smart Thumbnail Crop'),
			'input'      => 'html',
			'html'       => $html
		);

		// Return fields
		return $form_fields;
	}

	// Save submitted fields from the Media Upload dialog.
	public static function attachment_fields_to_save($post, $attachment) {
	    if (isset($attachment[self::META_POSITION]) && $attachment[self::META_POSITION]) {
			$previousPosition = self::getMeta($post['ID']);
		    $position = json_decode($attachment[self::META_POSITION]);
	        update_post_meta($post['ID'], self::META_POSITION, $position);

			// Update the thumbnail if the position changed
		    if ($previousPosition[0] != $position[0] || $previousPosition[1] != $position[1]) {
				$oldImagePath = get_attached_file($post['ID']);

			    // Rename file, while keeping the previous version
			    // Generate new filename
				$oldImagePathParts = pathinfo($oldImagePath);
				$filename = $oldImagePathParts['filename'];
				$suffix = time() . rand(100, 999);
			    $newImagePath = '';
				while (true) {
					$filename = preg_replace( '/-e([0-9]+)$/', '', $filename);
					$filename .= "-e{$suffix}";
					$newImageFile = "{$filename}.{$oldImagePathParts['extension']}";
					$newImagePath = "{$oldImagePathParts['dirname']}/$newImageFile";
					if (file_exists($newImagePath))
						$suffix++;
					else
						break;
				}

			    // Copy original image
			    copy($oldImagePath, $newImagePath);

			    // Update filename
				update_attached_file($post['ID'], $newImagePath);

			    // Generate thumbnails
				@set_time_limit(900); // 5 minutes per image should be PLENTY
				$metadata = wp_generate_attachment_metadata($post['ID'], $newImagePath);
				wp_update_attachment_metadata($post['ID'], $metadata);
		    }
	    }
	    return $post;
	}

	public static function get_attached_file($file, $attachment_id) {
		self::$lastPostId = $attachment_id;
		return $file;
	}

	public static function wp_get_attachment_metadata($data, $postId) {
		self::$lastPostId = $postId;
		return $data;
	}

	public static function image_resize_dimensions($something, $orig_w, $orig_h, $dest_w, $dest_h, $crop) {
		if (!$crop || self::$lastPostId === null) {
			return null;
		}
		$meta = self::getMeta(self::$lastPostId);

		$aspect_ratio = $orig_w / $orig_h;
		$new_w = min($dest_w, $orig_w);
		$new_h = min($dest_h, $orig_h);

		if (!$new_w) {
			$new_w = intval($new_h * $aspect_ratio);
		}

		if (!$new_h) {
			$new_h = intval($new_w / $aspect_ratio);
		}

		$size_ratio = max($new_w / $orig_w, $new_h / $orig_h);

		$crop_w = round($new_w / $size_ratio);
		$crop_h = round($new_h / $size_ratio);

		$s_x = floor(($orig_w - $crop_w) * $meta[0]);
		$s_y = floor(($orig_h - $crop_h) * $meta[1]);

		// If the resulting image would be the same size or larger we don't want to resize it
		if ($new_w >= $orig_w && $new_h >= $orig_h)
			return false;

		return array(0, 0, (int)$s_x, (int)$s_y, (int)$new_w, (int)$new_h, (int)$crop_w, (int)$crop_h);
	}

	// Get the saved crop position of an image.
	public static function getMeta($postId) {
		$meta = get_post_meta($postId, self::META_POSITION, true);
		if (!$meta) {
			$meta = array(0.5, 0.5);
		}
		return $meta;
	}

	// Enqueue JavaScript and CSS for the admin interface.
	public static function admin_enqueue_scripts() {
		// Admin JS
		wp_register_script('theiaSmartThumbnails-admin.js', plugins_url('js/tst-admin.js', __FILE__), array('jquery'), TST_VERSION, true);
		wp_enqueue_script('theiaSmartThumbnails-admin.js');

		// Admin CSS
	    wp_register_style('theiaSmartThumbnails-admin', plugins_url('css/admin.css', __FILE__), TST_VERSION);
	    wp_enqueue_style('theiaSmartThumbnails-admin');
	}
}
