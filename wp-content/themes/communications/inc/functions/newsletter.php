<?php
/**
 * Weekly newsletter: subscribe, activation email, admin notification on activate.
 *
 * @package Communicationstoday
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'COMMUNICATIONSTODAY_NEWSLETTER_DB_VERSION', '1.0' );

/**
 * Subscriber table name.
 *
 * @return string
 */
function communicationstoday_newsletter_table_name() {
	global $wpdb;
	return $wpdb->prefix . 'ct_newsletter_subscribers';
}

/**
 * Create / upgrade subscribers table.
 *
 * @return void
 */
function communicationstoday_newsletter_install_table() {
	global $wpdb;

	$table           = communicationstoday_newsletter_table_name();
	$charset_collate = $wpdb->get_charset_collate();
	$sql             = "CREATE TABLE {$table} (
		id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
		email varchar(190) NOT NULL,
		token varchar(64) NOT NULL,
		status varchar(20) NOT NULL DEFAULT 'pending',
		subscribed_at datetime NOT NULL,
		activated_at datetime DEFAULT NULL,
		PRIMARY KEY  (id),
		UNIQUE KEY email (email),
		KEY token (token),
		KEY status (status)
	) {$charset_collate};";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta( $sql );

	update_option( 'communicationstoday_newsletter_db_version', COMMUNICATIONSTODAY_NEWSLETTER_DB_VERSION );
}

/**
 * @return void
 */
function communicationstoday_newsletter_maybe_install_table() {
	if ( COMMUNICATIONSTODAY_NEWSLETTER_DB_VERSION !== get_option( 'communicationstoday_newsletter_db_version' ) ) {
		communicationstoday_newsletter_install_table();
	}
}
add_action( 'after_switch_theme', 'communicationstoday_newsletter_install_table' );
add_action( 'init', 'communicationstoday_newsletter_maybe_install_table', 5 );

/**
 * From address for newsletter emails.
 *
 * @return string
 */
function communicationstoday_newsletter_from_email() {
	return apply_filters(
		'communicationstoday_newsletter_from_email',
		'ct@adi-media.com'
	);
}

/**
 * From name for newsletter emails.
 *
 * @return string
 */
function communicationstoday_newsletter_from_name() {
	return apply_filters(
		'communicationstoday_newsletter_from_name',
		'Communications Today'
	);
}

/**
 * Admin notification recipient after activation.
 *
 * @return string
 */
function communicationstoday_newsletter_notify_email() {
	return apply_filters(
		'communicationstoday_newsletter_notify_email',
		'jahangirasharafadimedia@gmail.com'
	);
}

/**
 * Display name from email local part.
 *
 * @param string $email Email.
 * @return string
 */
function communicationstoday_newsletter_display_name_from_email( $email ) {
	$email = sanitize_email( $email );
	$local = strstr( $email, '@', true );
	if ( ! is_string( $local ) || '' === $local ) {
		return __( 'Subscriber', 'communicationstoday' );
	}
	$local = str_replace( array( '.', '_', '-' ), ' ', $local );
	return ucwords( trim( $local ) );
}

/**
 * Activation URL for a token.
 *
 * @param string $token Token.
 * @return string
 */
function communicationstoday_newsletter_activation_url( $token ) {
	return add_query_arg(
		'ct_nl_activate',
		rawurlencode( $token ),
		home_url( '/' )
	);
}

/**
 * Send HTML email.
 *
 * @param string $to      Recipient.
 * @param string $subject Subject.
 * @param string $html    HTML body.
 * @return bool
 */
function communicationstoday_newsletter_send_html_mail( $to, $subject, $html ) {
	$from_email = communicationstoday_newsletter_from_email();
	$from_name  = communicationstoday_newsletter_from_name();

	$headers = array(
		'Content-Type: text/html; charset=UTF-8',
		sprintf( 'From: %s <%s>', $from_name, $from_email ),
	);

	return (bool) wp_mail( $to, $subject, $html, $headers );
}

/**
 * Activation email HTML (matches site newsletter template).
 *
 * @param string $email Subscriber email.
 * @param string $token Activation token.
 * @return string
 */
function communicationstoday_newsletter_activation_email_html( $email, $token ) {
	$name    = communicationstoday_newsletter_display_name_from_email( $email );
	$activate_url = esc_url( communicationstoday_newsletter_activation_url( $token ) );
	$year    = (string) gmdate( 'Y' );
	$site    = esc_html( communicationstoday_newsletter_from_name() );

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo esc_html( $site ); ?></title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
	<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f4f4f4;padding:24px 12px;">
		<tr>
			<td align="center">
				<table role="presentation" width="600" cellspacing="0" cellpadding="0" style="max-width:600px;width:100%;background:#ffffff;border:1px solid #e0e0e0;">
					<tr>
						<td style="padding:32px 28px 20px;text-align:center;">
							<p style="margin:0;font-size:22px;font-weight:bold;color:#1a5fb4;letter-spacing:0.02em;">COMMUNICATIONS TODAY</p>
						</td>
					</tr>
					<tr>
						<td style="padding:0 28px 24px;color:#222;font-size:15px;line-height:1.6;">
							<p style="margin:0 0 16px;font-size:16px;"><strong><?php printf( esc_html__( 'Hi! %s', 'communicationstoday' ), esc_html( $name ) ); ?></strong></p>
							<p style="margin:0 0 12px;"><?php esc_html_e( 'Thank you for registering at', 'communicationstoday' ); ?> <strong><?php echo esc_html( $site ); ?>!</strong></p>
							<p style="margin:0 0 24px;"><?php esc_html_e( 'Click on the link below to activate your account and have our morning newsletter delivered straight to your inbox.', 'communicationstoday' ); ?></p>
							<p style="margin:0 0 28px;text-align:center;">
								<a href="<?php echo esc_url( $activate_url ); ?>" style="display:inline-block;padding:14px 48px;background:#1a5fb4;color:#ffffff;text-decoration:none;font-weight:bold;font-size:16px;border-radius:2px;"><?php esc_html_e( 'Activate', 'communicationstoday' ); ?></a>
							</p>
							<p style="margin:0 0 8px;font-size:14px;color:#444;"><?php esc_html_e( 'Kindly whitelist our email ID to receive our mails correctly.', 'communicationstoday' ); ?></p>
							<p style="margin:16px 0 0;font-size:14px;color:#444;"><?php esc_html_e( 'Regards,', 'communicationstoday' ); ?><br>-<?php esc_html_e( 'Team CT', 'communicationstoday' ); ?></p>
						</td>
					</tr>
					<tr>
						<td style="padding:14px 28px;background:#1a5fb4;text-align:center;">
							<p style="margin:0;font-size:12px;color:#ffffff;"><?php printf( esc_html__( 'Copyright © %s Communications Today', 'communicationstoday' ), esc_html( $year ) ); ?></p>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}

/**
 * Admin notification email after activation.
 *
 * @param string $email Subscriber email.
 * @return string
 */
function communicationstoday_newsletter_admin_notify_html( $email ) {
	$safe_email = esc_html( $email );
	$site       = esc_html( communicationstoday_newsletter_from_name() );

	ob_start();
	?>
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:20px;font-family:Arial,Helvetica,sans-serif;font-size:15px;color:#222;">
	<p><?php esc_html_e( 'A new subscriber has activated the weekly newsletter.', 'communicationstoday' ); ?></p>
	<p><strong><?php esc_html_e( 'Email address:', 'communicationstoday' ); ?></strong><br>
	<a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $safe_email ); ?></a></p>
	<p style="color:#666;font-size:13px;"><?php echo esc_html( $site ); ?></p>
</body>
</html>
	<?php
	return (string) ob_get_clean();
}

/**
 * Get subscriber row by email.
 *
 * @param string $email Email.
 * @return object|null
 */
function communicationstoday_newsletter_get_subscriber_by_email( $email ) {
	global $wpdb;
	$table = communicationstoday_newsletter_table_name();
	$email = sanitize_email( $email );
	if ( ! is_email( $email ) ) {
		return null;
	}
	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE email = %s LIMIT 1",
			$email
		)
	);
}

/**
 * Get subscriber by token.
 *
 * @param string $token Token.
 * @return object|null
 */
function communicationstoday_newsletter_get_subscriber_by_token( $token ) {
	global $wpdb;
	$table = communicationstoday_newsletter_table_name();
	$token = sanitize_text_field( $token );
	if ( '' === $token ) {
		return null;
	}
	return $wpdb->get_row(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE token = %s LIMIT 1",
			$token
		)
	);
}

/**
 * Create or refresh pending subscription and send activation email.
 *
 * @param string $email Email.
 * @return array{success:bool,message:string}
 */
function communicationstoday_newsletter_subscribe( $email ) {
	global $wpdb;

	communicationstoday_newsletter_maybe_install_table();

	$email = sanitize_email( $email );
	if ( ! is_email( $email ) ) {
		return array(
			'success' => false,
			'message' => __( 'Please enter a valid email address.', 'communicationstoday' ),
		);
	}

	$table = communicationstoday_newsletter_table_name();
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . esc_sql( $table ) . "'" ) !== $table ) {
		communicationstoday_newsletter_install_table();
	}
	$existing   = communicationstoday_newsletter_get_subscriber_by_email( $email );
	$token      = bin2hex( random_bytes( 32 ) );
	$now        = current_time( 'mysql' );

	if ( $existing && 'active' === $existing->status ) {
		return array(
			'success' => true,
			'message' => __( 'This email is already subscribed. Check your inbox for our weekly newsletter.', 'communicationstoday' ),
		);
	}

	if ( $existing ) {
		$wpdb->update(
			$table,
			array(
				'token'         => $token,
				'status'        => 'pending',
				'subscribed_at' => $now,
				'activated_at'  => null,
			),
			array( 'id' => (int) $existing->id ),
			array( '%s', '%s', '%s', '%s' ),
			array( '%d' )
		);
	} else {
		$inserted = $wpdb->insert(
			$table,
			array(
				'email'         => $email,
				'token'         => $token,
				'status'        => 'pending',
				'subscribed_at' => $now,
			),
			array( '%s', '%s', '%s', '%s' )
		);
		if ( false === $inserted ) {
			return array(
				'success' => false,
				'message' => __( 'Could not save your subscription. Please try again.', 'communicationstoday' ),
			);
		}
	}

	$html    = communicationstoday_newsletter_activation_email_html( $email, $token );
	$subject = sprintf(
		/* translators: %s: site name */
		__( 'Activate your %s newsletter subscription', 'communicationstoday' ),
		communicationstoday_newsletter_from_name()
	);

	$sent = communicationstoday_newsletter_send_html_mail( $email, $subject, $html );

	if ( ! $sent ) {
		return array(
			'success' => false,
			'message' => __( 'Could not send activation email. Please try again later.', 'communicationstoday' ),
		);
	}

	return array(
		'success' => true,
		'message' => __( 'Please check your inbox and click Activate to confirm your subscription.', 'communicationstoday' ),
	);
}

/**
 * Activate subscription and notify admin.
 *
 * @param string $token Activation token.
 * @return array{success:bool,message:string,email?:string}
 */
function communicationstoday_newsletter_activate( $token ) {
	global $wpdb;

	$row = communicationstoday_newsletter_get_subscriber_by_token( $token );
	if ( ! $row ) {
		return array(
			'success' => false,
			'message' => __( 'Invalid or expired activation link.', 'communicationstoday' ),
		);
	}

	if ( 'active' === $row->status ) {
		return array(
			'success' => true,
			'message' => __( 'Your subscription is already active. Thank you!', 'communicationstoday' ),
			'email'   => $row->email,
		);
	}

	$table = communicationstoday_newsletter_table_name();
	$wpdb->update(
		$table,
		array(
			'status'       => 'active',
			'activated_at' => current_time( 'mysql' ),
		),
		array( 'id' => (int) $row->id ),
		array( '%s', '%s' ),
		array( '%d' )
	);

	$email   = $row->email;
	$subject = sprintf(
		/* translators: %s: subscriber email */
		__( 'New Weekly Newsletter Subscription: %s', 'communicationstoday' ),
		$email
	);
	$html    = communicationstoday_newsletter_admin_notify_html( $email );
	$notify  = communicationstoday_newsletter_notify_email();

	communicationstoday_newsletter_send_html_mail( $notify, $subject, $html );

	return array(
		'success' => true,
		'message' => __( 'Thank you! Your newsletter subscription is now active.', 'communicationstoday' ),
		'email'   => $email,
	);
}

/**
 * AJAX: subscribe.
 *
 * @return void
 */
function communicationstoday_ajax_newsletter_subscribe() {
	if ( ! check_ajax_referer( 'communicationstoday_newsletter', 'nonce', false ) ) {
		wp_send_json_error(
			array(
				'message' => __( 'Security check failed. Please refresh the page and try again.', 'communicationstoday' ),
			)
		);
	}

	$email  = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$result = communicationstoday_newsletter_subscribe( $email );

	if ( $result['success'] ) {
		wp_send_json_success( array( 'message' => $result['message'] ) );
	}

	wp_send_json_error( array( 'message' => $result['message'] ) );
}
add_action( 'wp_ajax_communicationstoday_newsletter_subscribe', 'communicationstoday_ajax_newsletter_subscribe' );
add_action( 'wp_ajax_nopriv_communicationstoday_newsletter_subscribe', 'communicationstoday_ajax_newsletter_subscribe' );

/**
 * Handle activation link from email.
 *
 * @return void
 */
function communicationstoday_newsletter_handle_activation_link() {
	if ( ! isset( $_GET['ct_nl_activate'] ) ) {
		return;
	}

	$token  = sanitize_text_field( wp_unslash( $_GET['ct_nl_activate'] ) );
	$result = communicationstoday_newsletter_activate( $token );

	$redirect = add_query_arg(
		array(
			'newsletter' => $result['success'] ? 'activated' : 'error',
		),
		home_url( '/' )
	);
	$redirect .= '#newsletter-section';

	wp_safe_redirect( $redirect );
	exit;
}
add_action( 'template_redirect', 'communicationstoday_newsletter_handle_activation_link', 1 );

/**
 * Show activation / error notice after redirect.
 *
 * @return void
 */
function communicationstoday_newsletter_activation_notice() {
	if ( ! isset( $_GET['newsletter'] ) ) {
		return;
	}

	$status = sanitize_key( wp_unslash( $_GET['newsletter'] ) );
	$message = '';
	$class   = 'notice-success';

	if ( 'activated' === $status ) {
		$message = __( 'Thank you! Your newsletter subscription is now active.', 'communicationstoday' );
	} elseif ( 'error' === $status ) {
		$message = __( 'We could not activate your subscription. The link may be invalid or expired.', 'communicationstoday' );
		$class   = 'notice-error';
	}

	if ( '' === $message ) {
		return;
	}
	?>
	<div class="newsletter-flash-message <?php echo esc_attr( $class ); ?>" role="status">
		<div class="container">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
	</div>
	<?php
}
add_action( 'wp_body_open', 'communicationstoday_newsletter_activation_notice', 5 );
