<?php
/**
 * Admin pages for Impression data.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Impression admin menu and submenus.
 */
function communicationstoday_register_impression_admin_menu() {
	add_menu_page(
		__( 'Impression', 'communicationstoday' ),
		__( 'Impression', 'communicationstoday' ),
		'manage_options',
		'communicationstoday-impression-view',
		'communicationstoday_render_impression_view_page',
		'dashicons-chart-bar',
		59
	);

	add_submenu_page(
		'communicationstoday-impression-view',
		__( 'View Impression', 'communicationstoday' ),
		__( 'View Impression', 'communicationstoday' ),
		'manage_options',
		'communicationstoday-impression-view',
		'communicationstoday_render_impression_view_page'
	);

	add_submenu_page(
		'communicationstoday-impression-view',
		__( 'Analyze', 'communicationstoday' ),
		__( 'Analyze', 'communicationstoday' ),
		'manage_options',
		'communicationstoday-impression-analyze',
		'communicationstoday_render_impression_analyze_page'
	);
}
add_action( 'admin_menu', 'communicationstoday_register_impression_admin_menu' );

/**
 * Enqueue media library on Analyze page.
 *
 * @param string $hook_suffix Current admin page hook.
 */
function communicationstoday_impression_admin_assets( $hook_suffix ) {
	if ( 'impression_page_communicationstoday-impression-analyze' !== $hook_suffix ) {
		return;
	}

	wp_enqueue_media();
}
add_action( 'admin_enqueue_scripts', 'communicationstoday_impression_admin_assets' );

/**
 * Get all saved impressions.
 *
 * @return array<int, array<string, mixed>>
 */
function communicationstoday_get_impressions_data() {
	$data = get_option( 'communicationstoday_impressions_data', array() );
	$data = is_array( $data ) ? $data : array();

	$normalized = array();
	$changed    = false;
	foreach ( $data as $row ) {
		if ( ! is_array( $row ) ) {
			continue;
		}
		if ( empty( $row['id'] ) ) {
			$row['id'] = wp_generate_uuid4();
			$changed   = true;
		}
		$normalized[] = $row;
	}

	if ( $changed ) {
		update_option( 'communicationstoday_impressions_data', $normalized );
	}

	return $normalized;
}

/**
 * Save analyze form row.
 */
function communicationstoday_handle_impression_save() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'communicationstoday' ) );
	}

	check_admin_referer( 'communicationstoday_save_impression' );

	$month = isset( $_POST['impression_month'] ) ? sanitize_text_field( wp_unslash( $_POST['impression_month'] ) ) : '';
	$count = isset( $_POST['impression_count'] ) ? sanitize_text_field( wp_unslash( $_POST['impression_count'] ) ) : '';
	$click = isset( $_POST['impression_click'] ) ? sanitize_text_field( wp_unslash( $_POST['impression_click'] ) ) : '';
	$image = isset( $_POST['impression_image_id'] ) ? absint( $_POST['impression_image_id'] ) : 0;
	$edit_id = isset( $_POST['impression_id'] ) ? sanitize_text_field( wp_unslash( $_POST['impression_id'] ) ) : '';
	$redirect_page = isset( $_POST['redirect_page'] ) ? sanitize_key( wp_unslash( $_POST['redirect_page'] ) ) : 'communicationstoday-impression-analyze';

	if ( ! in_array( $redirect_page, array( 'communicationstoday-impression-analyze', 'communicationstoday-impression-view' ), true ) ) {
		$redirect_page = 'communicationstoday-impression-analyze';
	}

	if ( ! preg_match( '/^\d{4}\-\d{2}$/', $month ) ) {
		wp_safe_redirect(
			add_query_arg(
				array(
					'page'    => $redirect_page,
					'updated' => '0',
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	$rows = communicationstoday_get_impressions_data();
	$is_updated = false;

	if ( '' !== $edit_id ) {
		foreach ( $rows as $index => $row ) {
			$current_id = isset( $row['id'] ) ? (string) $row['id'] : '';
			if ( $current_id !== $edit_id ) {
				continue;
			}

			$rows[ $index ]['month']     = $month;
			$rows[ $index ]['image_id']  = $image;
			$rows[ $index ]['image_url'] = $image ? wp_get_attachment_image_url( $image, 'medium' ) : '';
			$rows[ $index ]['count']     = $count;
			$rows[ $index ]['click']     = $click;
			$is_updated                  = true;
			break;
		}
	}

	if ( ! $is_updated ) {
		$rows[] = array(
			'id'         => wp_generate_uuid4(),
			'month'      => $month,
			'image_id'   => $image,
			'image_url'  => $image ? wp_get_attachment_image_url( $image, 'medium' ) : '',
			'count'      => $count,
			'click'      => $click,
			'created_at' => current_time( 'mysql' ),
		);
	}

	update_option( 'communicationstoday_impressions_data', $rows );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'   => $redirect_page,
				'status' => $is_updated ? 'updated' : 'saved',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_communicationstoday_save_impression', 'communicationstoday_handle_impression_save' );

/**
 * Delete one impression entry.
 */
function communicationstoday_handle_impression_delete() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'You are not allowed to do this.', 'communicationstoday' ) );
	}

	check_admin_referer( 'communicationstoday_delete_impression' );

	$delete_id = isset( $_POST['impression_id'] ) ? sanitize_text_field( wp_unslash( $_POST['impression_id'] ) ) : '';
	$redirect_page = isset( $_POST['redirect_page'] ) ? sanitize_key( wp_unslash( $_POST['redirect_page'] ) ) : 'communicationstoday-impression-view';
	if ( ! in_array( $redirect_page, array( 'communicationstoday-impression-analyze', 'communicationstoday-impression-view' ), true ) ) {
		$redirect_page = 'communicationstoday-impression-view';
	}
	$rows      = communicationstoday_get_impressions_data();
	$updated   = array();

	foreach ( $rows as $row ) {
		$current_id = isset( $row['id'] ) ? (string) $row['id'] : '';
		if ( '' !== $delete_id && $current_id === $delete_id ) {
			continue;
		}
		$updated[] = $row;
	}

	update_option( 'communicationstoday_impressions_data', $updated );

	wp_safe_redirect(
		add_query_arg(
			array(
				'page'    => $redirect_page,
				'deleted' => '1',
			),
			admin_url( 'admin.php' )
		)
	);
	exit;
}
add_action( 'admin_post_communicationstoday_delete_impression', 'communicationstoday_handle_impression_delete' );

/**
 * Render Analyze admin page.
 */
function communicationstoday_render_impression_analyze_page() {
	$rows         = communicationstoday_get_impressions_data();
	$current_month = current_time( 'Y-m' );
	$edit_id      = isset( $_GET['edit_id'] ) ? sanitize_text_field( wp_unslash( $_GET['edit_id'] ) ) : '';
	$edit_entry   = null;

	usort(
		$rows,
		static function( $a, $b ) {
			return strcmp( (string) $b['month'], (string) $a['month'] );
		}
	);

	if ( '' !== $edit_id ) {
		foreach ( $rows as $row ) {
			if ( isset( $row['id'] ) && (string) $row['id'] === $edit_id ) {
				$edit_entry = $row;
				break;
			}
		}
	}

	$form_month = $edit_entry && ! empty( $edit_entry['month'] ) ? (string) $edit_entry['month'] : $current_month;
	$form_count = $edit_entry && isset( $edit_entry['count'] ) ? (string) $edit_entry['count'] : '';
	$form_click = $edit_entry && isset( $edit_entry['click'] ) ? (string) $edit_entry['click'] : '';
	$form_image_id = $edit_entry && isset( $edit_entry['image_id'] ) ? (int) $edit_entry['image_id'] : 0;
	$form_image_url = $edit_entry && ! empty( $edit_entry['image_url'] ) ? (string) $edit_entry['image_url'] : '';

	$grouped = array();
	foreach ( $rows as $row ) {
		$month = isset( $row['month'] ) ? (string) $row['month'] : '';
		if ( '' === $month ) {
			continue;
		}
		if ( ! isset( $grouped[ $month ] ) ) {
			$grouped[ $month ] = array();
		}
		$grouped[ $month ][] = $row;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Analyze', 'communicationstoday' ); ?></h1>
		<?php if ( isset( $_GET['status'] ) && 'saved' === $_GET['status'] ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impression entry saved.', 'communicationstoday' ); ?></p></div>
		<?php elseif ( isset( $_GET['status'] ) && 'updated' === $_GET['status'] ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impression entry updated.', 'communicationstoday' ); ?></p></div>
		<?php endif; ?>
		<?php if ( isset( $_GET['deleted'] ) && '1' === $_GET['deleted'] ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impression entry deleted.', 'communicationstoday' ); ?></p></div>
		<?php endif; ?>

		<style>
			.communicationstoday-analyze-layout {
				width: calc(100% - 24px);
				max-width: none;
				display: grid;
				gap: 16px;
			}
			.communicationstoday-analyze-card {
				background: #fff;
				border: 1px solid #d6deea;
				border-radius: 12px;
				padding: 18px;
				box-shadow: 0 4px 16px rgba(16, 24, 40, 0.06);
			}
			.communicationstoday-form-grid {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
				gap: 14px;
			}
			.communicationstoday-form-group label {
				display: block;
				margin-bottom: 6px;
				font-size: 12px;
				font-weight: 600;
				color: #2a3441;
			}
			.communicationstoday-form-group input[type="month"],
			.communicationstoday-form-group input[type="text"] {
				width: 100%;
				height: 42px;
				border-radius: 10px;
				border: 1px solid #c3cede;
				padding: 0 12px;
			}
			.communicationstoday-form-group input:focus {
				outline: none;
				border-color: #2271b1;
				box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.15);
			}
			.communicationstoday-media-row {
				margin-top: 6px;
			}
			.communicationstoday-media-preview {
				width: 180px;
				height: 90px;
				border-radius: 8px;
				border: 1px solid #dcdcde;
				object-fit: contain;
				background: #fff;
				margin-bottom: 8px;
			}
			.communicationstoday-list-section {
				margin-top: 10px;
			}
			.communicationstoday-month-title {
				display: inline-block;
				margin: 0 0 10px;
				padding: 4px 10px;
				border-radius: 999px;
				background: #eef6ff;
				border: 1px solid #c6def6;
				font-size: 12px;
				font-weight: 700;
			}
			.communicationstoday-analyze-table {
				width: 100%;
				border-collapse: separate;
				border-spacing: 0;
				border: 1px solid #dcdcde;
				border-radius: 10px;
				overflow: hidden;
			}
			.communicationstoday-analyze-table th {
				background: #1f2a37;
				color: #fff;
				padding: 10px;
				text-align: left;
			}
			.communicationstoday-analyze-table td {
				padding: 10px;
				border-bottom: 1px solid #eceff3;
				vertical-align: middle;
			}
			.communicationstoday-analyze-image {
				width: 130px;
				height: 68px;
				border: 1px solid #dcdcde;
				border-radius: 8px;
				object-fit: contain;
				background: #fff;
			}
			.communicationstoday-actions {
				display: flex;
				gap: 8px;
				align-items: center;
			}
			.communicationstoday-inline-form {
				margin: 0;
			}
		</style>

		<div class="communicationstoday-analyze-layout">
		<div class="communicationstoday-analyze-card">
		<h2 style="margin-top:0;"><?php echo $edit_entry ? esc_html__( 'Edit Impression Entry', 'communicationstoday' ) : esc_html__( 'Add Impression Entry', 'communicationstoday' ); ?></h2>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php wp_nonce_field( 'communicationstoday_save_impression' ); ?>
			<input type="hidden" name="action" value="communicationstoday_save_impression">
			<input type="hidden" name="redirect_page" value="communicationstoday-impression-analyze">
			<input type="hidden" name="impression_id" value="<?php echo esc_attr( $edit_entry && isset( $edit_entry['id'] ) ? (string) $edit_entry['id'] : '' ); ?>">

			<div class="communicationstoday-form-grid">
				<div class="communicationstoday-form-group">
					<label for="impression_month"><?php esc_html_e( 'Month', 'communicationstoday' ); ?></label>
					<input name="impression_month" type="month" id="impression_month" required value="<?php echo esc_attr( $form_month ); ?>">
				</div>
				<div class="communicationstoday-form-group">
					<label for="impression_count"><?php esc_html_e( 'Count', 'communicationstoday' ); ?></label>
					<input name="impression_count" type="text" id="impression_count" placeholder="<?php esc_attr_e( 'Optional', 'communicationstoday' ); ?>" value="<?php echo esc_attr( $form_count ); ?>">
				</div>
				<div class="communicationstoday-form-group">
					<label for="impression_click"><?php esc_html_e( 'Click', 'communicationstoday' ); ?></label>
					<input name="impression_click" type="text" id="impression_click" placeholder="<?php esc_attr_e( 'Optional', 'communicationstoday' ); ?>" value="<?php echo esc_attr( $form_click ); ?>">
				</div>
				<div class="communicationstoday-form-group">
					<label for="impression_image_id"><?php esc_html_e( 'Image', 'communicationstoday' ); ?></label>
					<input name="impression_image_id" type="hidden" id="impression_image_id" value="<?php echo esc_attr( (string) $form_image_id ); ?>">
					<div class="communicationstoday-media-row">
						<img id="impression_image_preview" class="communicationstoday-media-preview" src="<?php echo esc_url( $form_image_url ); ?>" alt="" style="<?php echo $form_image_url ? '' : 'display:none;'; ?>">
					</div>
					<button type="button" class="button" id="impression_select_image"><?php esc_html_e( 'Select image', 'communicationstoday' ); ?></button>
					<button type="button" class="button" id="impression_remove_image" style="<?php echo $form_image_id ? '' : 'display:none;'; ?>"><?php esc_html_e( 'Remove image', 'communicationstoday' ); ?></button>
				</div>
			</div>
			<div style="margin-top:14px;">
				<?php submit_button( $edit_entry ? __( 'Update Impression', 'communicationstoday' ) : __( 'Save Impression', 'communicationstoday' ), 'primary', 'submit', false ); ?>
				<?php if ( $edit_entry ) : ?>
					<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=communicationstoday-impression-analyze' ) ); ?>"><?php esc_html_e( 'Cancel Edit', 'communicationstoday' ); ?></a>
				<?php endif; ?>
			</div>
		</form>
		</div>

		<div class="communicationstoday-analyze-card communicationstoday-list-section">
			<h2 style="margin-top:0;"><?php esc_html_e( 'Month-wise Entries', 'communicationstoday' ); ?></h2>
			<?php if ( empty( $grouped ) ) : ?>
				<p><?php esc_html_e( 'No entries found.', 'communicationstoday' ); ?></p>
			<?php else : ?>
				<?php foreach ( $grouped as $month => $month_rows ) : ?>
					<?php $formatted_month = DateTime::createFromFormat( 'Y-m', $month ); ?>
					<h3 class="communicationstoday-month-title"><?php echo esc_html( $formatted_month ? $formatted_month->format( 'F Y' ) : $month ); ?></h3>
					<table class="communicationstoday-analyze-table">
						<thead>
							<tr>
								<th><?php esc_html_e( 'Banner', 'communicationstoday' ); ?></th>
								<th><?php esc_html_e( 'Count', 'communicationstoday' ); ?></th>
								<th><?php esc_html_e( 'Click', 'communicationstoday' ); ?></th>
								<th><?php esc_html_e( 'Action', 'communicationstoday' ); ?></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $month_rows as $entry ) : ?>
								<tr>
									<td>
										<?php if ( ! empty( $entry['image_url'] ) ) : ?>
											<img class="communicationstoday-analyze-image" src="<?php echo esc_url( (string) $entry['image_url'] ); ?>" alt="">
										<?php else : ?>
											&mdash;
										<?php endif; ?>
									</td>
									<td><?php echo '' !== (string) $entry['count'] ? esc_html( (string) $entry['count'] ) : '&mdash;'; ?></td>
									<td><?php echo '' !== (string) $entry['click'] ? esc_html( (string) $entry['click'] ) : '&mdash;'; ?></td>
									<td>
										<div class="communicationstoday-actions">
											<a class="button button-secondary" href="<?php echo esc_url( add_query_arg( array( 'page' => 'communicationstoday-impression-analyze', 'edit_id' => (string) $entry['id'] ), admin_url( 'admin.php' ) ) ); ?>"><?php esc_html_e( 'Edit', 'communicationstoday' ); ?></a>
											<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="communicationstoday-inline-form" onsubmit="return confirm('<?php echo esc_js( __( 'Delete this entry?', 'communicationstoday' ) ); ?>');">
												<?php wp_nonce_field( 'communicationstoday_delete_impression' ); ?>
												<input type="hidden" name="action" value="communicationstoday_delete_impression">
												<input type="hidden" name="redirect_page" value="communicationstoday-impression-analyze">
												<input type="hidden" name="impression_id" value="<?php echo esc_attr( isset( $entry['id'] ) ? (string) $entry['id'] : '' ); ?>">
												<button type="submit" class="button button-link-delete"><?php esc_html_e( 'Delete', 'communicationstoday' ); ?></button>
											</form>
										</div>
									</td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
		</div>
	</div>

	<script>
	(function($) {
		let frame;
		const $imageId = $('#impression_image_id');
		const $preview = $('#impression_image_preview');
		const $removeBtn = $('#impression_remove_image');

		function resetImage() {
			$imageId.val('');
			$preview.attr('src', '').hide();
			$removeBtn.hide();
		}

		$('#impression_select_image').on('click', function(e) {
			e.preventDefault();

			if (frame) {
				frame.open();
				return;
			}

			frame = wp.media({
				title: 'Select image',
				button: { text: 'Use this image' },
				multiple: false
			});

			frame.on('select', function() {
				const attachment = frame.state().get('selection').first().toJSON();
				$imageId.val(attachment.id || '');
				$preview.attr('src', attachment.url || '').show();
				$removeBtn.show();
			});

			frame.open();
		});

		$removeBtn.on('click', function(e) {
			e.preventDefault();
			resetImage();
		});
	})(jQuery);
	</script>
	<?php
}

/**
 * Render View Impression page with month-wise grouped rows.
 */
function communicationstoday_render_impression_view_page() {
	$rows = communicationstoday_get_impressions_data();
	$from = isset( $_GET['from_month'] ) ? sanitize_text_field( wp_unslash( $_GET['from_month'] ) ) : '';
	$to   = isset( $_GET['to_month'] ) ? sanitize_text_field( wp_unslash( $_GET['to_month'] ) ) : '';
	$has_filter_query = isset( $_GET['from_month'] ) || isset( $_GET['to_month'] );
	$current_month    = current_time( 'Y-m' );

	if ( ! $has_filter_query ) {
		$from = $current_month;
		$to   = $current_month;
	}

	$is_from_valid = '' === $from || preg_match( '/^\d{4}\-\d{2}$/', $from );
	$is_to_valid   = '' === $to || preg_match( '/^\d{4}\-\d{2}$/', $to );

	if ( $is_from_valid && $is_to_valid && '' !== $from && '' !== $to && $from > $to ) {
		$temp = $from;
		$from = $to;
		$to   = $temp;
	}

	if ( $is_from_valid && $is_to_valid && ( '' !== $from || '' !== $to ) ) {
		$rows = array_filter(
			$rows,
			static function( $row ) use ( $from, $to ) {
				$month = isset( $row['month'] ) ? (string) $row['month'] : '';
				if ( '' === $month ) {
					return false;
				}
				if ( '' !== $from && $month < $from ) {
					return false;
				}
				if ( '' !== $to && $month > $to ) {
					return false;
				}
				return true;
			}
		);
	}

	usort(
		$rows,
		static function( $a, $b ) {
			return strcmp( (string) $b['month'], (string) $a['month'] );
		}
	);

	$total_entries = count( $rows );
	$total_count   = 0;
	foreach ( $rows as $row ) {
		if ( isset( $row['count'] ) && is_numeric( $row['count'] ) ) {
			$total_count += (int) $row['count'];
		}
	}

	$grouped = array();
	foreach ( $rows as $row ) {
		$month = isset( $row['month'] ) ? (string) $row['month'] : '';
		if ( '' === $month ) {
			continue;
		}
		if ( ! isset( $grouped[ $month ] ) ) {
			$grouped[ $month ] = array();
		}
		$grouped[ $month ][] = $row;
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'View Impression', 'communicationstoday' ); ?></h1>
		<?php if ( isset( $_GET['deleted'] ) && '1' === $_GET['deleted'] ) : ?>
			<div class="notice notice-success is-dismissible"><p><?php esc_html_e( 'Impression entry deleted.', 'communicationstoday' ); ?></p></div>
		<?php endif; ?>

		<style>
			.communicationstoday-impression-wrap {
				width: calc(100% - 24px);
				max-width: none;
				margin-top: 12px;
			}
			.communicationstoday-impression-filter {
				display: flex;
				flex-wrap: wrap;
				gap: 12px;
				margin: 16px 0;
				padding: 20px;
				background: linear-gradient(180deg, #ffffff 0%, #fbfcff 100%);
				border: 1px solid #d6deea;
				border-radius: 12px;
				box-shadow: 0 6px 18px rgba(16, 24, 40, 0.06);
			}
			.communicationstoday-impression-filter label {
				display: block;
				font-size: 12px;
				font-weight: 600;
				margin-bottom: 6px;
				color: #2a3441;
			}
			.communicationstoday-impression-filter .field {
				min-width: 220px;
				flex: 1;
				max-width: 280px;
			}
			.communicationstoday-impression-filter .field input[type="month"] {
				width: 100%;
				min-height: 42px;
				border-radius: 10px;
				border: 1px solid #c3cede;
				padding: 0 12px;
				background: #fff;
				color: #111927;
				box-shadow: inset 0 1px 2px rgba(16, 24, 40, 0.04);
				transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
			}
			.communicationstoday-impression-filter .field input[type="month"]:focus {
				border-color: #2271b1;
				box-shadow: 0 0 0 2px rgba(34, 113, 177, 0.15);
				outline: none;
				background: #fafdff;
			}
			.communicationstoday-impression-filter .button {
				min-height: 42px;
				border-radius: 10px;
				padding: 0 14px;
			}
			.communicationstoday-impression-filter .button-primary {
				background: linear-gradient(135deg, #2271b1 0%, #135e96 100%);
				border-color: #135e96;
			}
			.communicationstoday-impression-filter .button-primary:hover {
				background: linear-gradient(135deg, #135e96 0%, #0d4d7d 100%);
				border-color: #0d4d7d;
			}
			.communicationstoday-impression-summary {
				display: grid;
				grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
				gap: 14px;
				margin: 0 0 18px;
			}
			.communicationstoday-summary-card {
				background: linear-gradient(135deg, #ffffff 0%, #f8fbff 100%);
				border: 1px solid #d0dbe8;
				border-radius: 12px;
				padding: 14px 16px;
				min-height: 92px;
				display: flex;
				flex-direction: column;
				justify-content: center;
			}
			.communicationstoday-summary-label {
				font-size: 12px;
				font-weight: 600;
				color: #5b6470;
				margin-bottom: 6px;
				text-transform: uppercase;
				letter-spacing: 0.4px;
			}
			.communicationstoday-summary-value {
				font-size: 24px;
				line-height: 1.2;
				font-weight: 700;
				color: #111927;
			}
			.communicationstoday-month-section {
				margin-top: 20px;
				padding: 16px;
				background: #fff;
				border: 1px solid #dcdcde;
				border-radius: 12px;
				box-shadow: 0 1px 4px rgba(15, 23, 42, 0.08);
			}
			.communicationstoday-month-title {
				display: inline-flex;
				align-items: center;
				gap: 8px;
				margin: 0 0 12px;
				padding: 6px 10px;
				background: #f0f6fc;
				border: 1px solid #c5d9ed;
				border-radius: 999px;
				color: #0a4b78;
				font-size: 14px;
				font-weight: 600;
			}
			.communicationstoday-impression-table {
				border-collapse: separate;
				border-spacing: 0;
				overflow: hidden;
				border-radius: 8px;
				border: 1px solid #dcdcde;
			}
			.communicationstoday-impression-table thead th {
				background: #1f2a37 !important;
				color: #ffffff !important;
				font-weight: 700;
				padding: 12px 10px;
				font-size: 12px;
				letter-spacing: 0.3px;
				text-transform: uppercase;
				opacity: 1 !important;
				text-shadow: 0 1px 0 rgba(0, 0, 0, 0.2);
			}
			.communicationstoday-impression-table thead th a,
			.communicationstoday-impression-table thead th span {
				color: #ffffff !important;
			}
			.communicationstoday-impression-table tbody td {
				padding: 12px 10px;
				vertical-align: middle;
				border-bottom: 1px solid #eceff3;
			}
			.communicationstoday-impression-table tbody tr:nth-child(even) {
				background: #fbfbfc;
			}
			.communicationstoday-impression-table tbody tr:hover {
				background: #f0f6fc;
			}
			.communicationstoday-image-cell {
				display: flex;
				align-items: center;
				gap: 12px;
			}
			.communicationstoday-image-wrap {
				width: 138px;
				height: 72px;
				border-radius: 8px;
				border: 1px solid #dcdcde;
				background: #fff;
				display: flex;
				align-items: center;
				justify-content: center;
				overflow: hidden;
			}
			.communicationstoday-image-cell img {
				width: 100%;
				height: 100%;
				object-fit: contain;
			}
			.communicationstoday-banner-label {
				font-weight: 600;
				color: #1d2327;
				min-width: 110px;
			}
			.communicationstoday-metric-chip {
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 74px;
				height: 34px;
				text-align: center;
				padding: 4px 8px;
				border-radius: 999px;
				background: #eef6ff;
				border: 1px solid #c6def6;
				font-weight: 800;
				color: #0a4b78;
			}
			.communicationstoday-metric-empty {
				color: #8c8f94;
				font-style: italic;
			}
			.communicationstoday-delete-form {
				margin: 0;
			}
			.communicationstoday-delete-form .button-link-delete {
				color: #b42318;
				text-decoration: none;
				border: 1px solid #f2d4d0;
				background: #fff7f6;
				padding: 0 12px;
				height: 30px;
				display: inline-flex;
				align-items: center;
				justify-content: center;
				min-width: 78px;
				border-radius: 999px;
				line-height: 1.2;
			}
			.communicationstoday-delete-form .button-link-delete:hover {
				color: #fff;
				background: #b42318;
				border-color: #b42318;
			}
		</style>

		<div class="communicationstoday-impression-wrap">
		<form method="get" class="communicationstoday-impression-filter">
			<input type="hidden" name="page" value="communicationstoday-impression-view">
			<div class="field">
				<label for="from_month"><?php esc_html_e( 'From Month', 'communicationstoday' ); ?></label>
				<input type="month" id="from_month" name="from_month" value="<?php echo esc_attr( $from ); ?>">
			</div>
			<div class="field">
				<label for="to_month"><?php esc_html_e( 'To Month', 'communicationstoday' ); ?></label>
				<input type="month" id="to_month" name="to_month" value="<?php echo esc_attr( $to ); ?>">
			</div>
			<div class="field" style="display:flex;align-items:flex-end;gap:8px;">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Apply Filter', 'communicationstoday' ); ?></button>
				<a class="button" href="<?php echo esc_url( admin_url( 'admin.php?page=communicationstoday-impression-view' ) ); ?>"><?php esc_html_e( 'Reset', 'communicationstoday' ); ?></a>
			</div>
		</form>

		<div class="communicationstoday-impression-summary">
			<div class="communicationstoday-summary-card">
				<div class="communicationstoday-summary-label"><?php esc_html_e( 'Total Banners', 'communicationstoday' ); ?></div>
				<div class="communicationstoday-summary-value"><?php echo esc_html( (string) $total_entries ); ?></div>
			</div>
			<div class="communicationstoday-summary-card">
				<div class="communicationstoday-summary-label"><?php esc_html_e( 'Total Count', 'communicationstoday' ); ?></div>
				<div class="communicationstoday-summary-value"><?php echo esc_html( number_format_i18n( $total_count ) ); ?></div>
			</div>
		</div>

		<?php if ( empty( $grouped ) ) : ?>
			<div class="communicationstoday-month-section">
				<p><?php esc_html_e( 'No impression data found for this filter range.', 'communicationstoday' ); ?></p>
			</div>
		<?php else : ?>
			<?php foreach ( $grouped as $month => $month_rows ) : ?>
				<?php
				$formatted_month = DateTime::createFromFormat( 'Y-m', $month );
				?>
				<div class="communicationstoday-month-section">
				<h2 class="communicationstoday-month-title">
					<?php echo esc_html( $formatted_month ? $formatted_month->format( 'F Y' ) : $month ); ?>
				</h2>
				<table class="widefat striped communicationstoday-impression-table" style="width:100%;">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Banner', 'communicationstoday' ); ?></th>
							<th><?php esc_html_e( 'Count', 'communicationstoday' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $month_rows as $entry ) : ?>
							<tr>
								<td>
									<?php if ( ! empty( $entry['image_url'] ) ) : ?>
										<div class="communicationstoday-image-cell">
											<div class="communicationstoday-image-wrap">
												<img src="<?php echo esc_url( (string) $entry['image_url'] ); ?>" alt="">
											</div>
											<span class="communicationstoday-banner-label"><?php esc_html_e( 'Banner Image', 'communicationstoday' ); ?></span>
										</div>
									<?php else : ?>
										&mdash;
									<?php endif; ?>
								</td>
								<td>
									<?php if ( '' !== (string) $entry['count'] ) : ?>
										<span class="communicationstoday-metric-chip"><?php echo esc_html( (string) $entry['count'] ); ?></span>
									<?php else : ?>
										<span class="communicationstoday-metric-empty"><?php esc_html_e( 'Not set', 'communicationstoday' ); ?></span>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		</div>
	</div>
	<?php
}
