<?php
/**
 * The header for our theme
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Communicationstoday
 */
?>

<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e( 'Skip to content', 'communicationstoday' ); ?></a>

		<!-- Sidebar Menu -->
		<div class="sidebar-menu" id="sidebarMenu">
			<div class="sidebar-header">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sidemenu_logo">
					<?php communicationstoday_the_custom_logo_image(); ?>
				</a>
				<i class="fas fa-times sidebar-close"></i>
			</div>
			<nav class="sidebar-nav" aria-label="<?php esc_attr_e( 'Mobile menu', 'communicationstoday' ); ?>">
				<?php
				if ( has_nav_menu( 'sidebar-popup' ) ) {
					wp_nav_menu(
						array(
							'theme_location' => 'sidebar-popup',
							'container'      => false,
							'menu_class'     => '',
							'menu_id'        => '',
							'items_wrap'     => '%3$s',
							'depth'          => 2,
							'fallback_cb'    => false,
							'walker'         => new Communicationstoday_Sidebar_Nav_Walker(),
						)
					);
				} elseif ( current_user_can( 'edit_theme_options' ) ) {
					?>
				<p class="sidebar-menu-empty">
					<?php
					echo esc_html(
						sprintf(
							/* translators: %s: Menu location name */
							__( 'Assign a menu to "%s" under Appearance - Menus.', 'communicationstoday' ),
							__( 'Mobile / sidebar panel', 'communicationstoday' )
						)
					);
					?>
				</p>
					<?php
				}
				?>
			</nav>
			<div class="sidebar-footer">
				<div class="connect-with-us">
					<h3>CONNECT WITH US</h3>
					<div class="social-icons">
						<?php communicationstoday_render_social_links( 'social-icon', array( 'twitter', 'youtube', 'linkedin' ) ); ?>
					</div>
				</div>
			</div>
		</div>
		<div class="sidebar-overlay" id="sidebarOverlay"></div>

		<!-- Search Sidebar -->
		<div class="search-sidebar" id="searchSidebar">
			<div class="search-sidebar-header">
				<h2>SEARCH</h2>
				<i class="fas fa-times search-sidebar-close"></i>
			</div>
			<div class="search-sidebar-content">
				<div class="search-input-wrapper">
					<input type="search" class="search-input" id="liveSearchInput" placeholder="<?php echo esc_attr__( 'Search…', 'communicationstoday' ); ?>" autocomplete="off" aria-label="<?php echo esc_attr__( 'Search', 'communicationstoday' ); ?>">
					<i class="fas fa-search search-input-icon" aria-hidden="true"></i>
				</div>
				<div class="search-results live-search-results" id="liveSearchResults" aria-live="polite"></div>
			</div>
		</div>
		<div class="search-sidebar-overlay" id="searchSidebarOverlay"></div>

		<!-- Top Of Header-->
		<header class="header-container">

			<div class="top-banner">
				<div class="container">
					<div class="top-banner-content">
						<div class="top-banner-left">
							<i class="fas fa-bars hamburger-menu-icon"></i>
							<i class="fas fa-search search-icon"></i>
						</div>
						<div class="top-banner-center">
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
								<?php communicationstoday_the_custom_logo_image(); ?>
							</a>
						</div>
						<div class="top-banner-right">
							<?php communicationstoday_render_social_links( 'top-banner-social-link', array( 'linkedin', 'twitter', 'youtube' ) ); ?>
						</div>
					</div>
				</div>
			</div>

			<div class="nav-bar">
				<div class="container">
					<?php
					if ( has_nav_menu( 'menu-1' ) ) {
						wp_nav_menu(
							array(
								'theme_location' => 'menu-1',
								'container'      => false,
								'menu_class'     => '',
								'menu_id'        => '',
								'items_wrap'     => '<nav class="nav-menu" aria-label="%2$s">%3$s</nav>',
								'depth'          => 2,
								'fallback_cb'    => false,
								'walker'         => new Communicationstoday_Primary_Nav_Walker(),
							)
						);
					} elseif ( current_user_can( 'edit_theme_options' ) ) {
						?>
					<nav class="nav-menu" aria-label="<?php esc_attr_e( 'Primary menu', 'communicationstoday' ); ?>">
						<p class="nav-menu-empty">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: Menu location name */
									__( 'Assign a menu to "%s" under Appearance - Menus.', 'communicationstoday' ),
									__( 'Primary', 'communicationstoday' )
								)
							);
							?>
						</p>
					</nav>
						<?php
					}
					?>
				</div>
			</div>
		</header>
		<!-- End of Header -->

		<?php
		$communicationstoday_ticker_posts = communicationstoday_get_ticker_posts();
		if ( ! empty( $communicationstoday_ticker_posts ) ) :
			?>
		<!-- News Ticker -->
		<div class="news-ticker">
			<div class="ticker-container">
				<div class="ticker-label">
					<span><?php echo esc_html( communicationstoday_get_ticker_label() ); ?></span>
					<i class="fas fa-bolt" aria-hidden="true"></i>
				</div>
				<div class="ticker-wrapper">
					<div class="ticker-content">
						<?php
						$ticker_i = 0;
						foreach ( $communicationstoday_ticker_posts as $communicationstoday_ticker_post ) :
							if ( ! $communicationstoday_ticker_post instanceof WP_Post ) {
								continue;
							}
							if ( $ticker_i > 0 ) {
								echo '<div class="ticker-separator"></div>';
							}
							++$ticker_i;
							?>
						<a href="<?php echo esc_url( get_permalink( $communicationstoday_ticker_post ) ); ?>" class="ticker-item"><?php echo esc_html( get_the_title( $communicationstoday_ticker_post ) ); ?></a>
							<?php
						endforeach;
						?>
					</div>
				</div>
			</div>
		</div>
		<!-- End of News Ticker -->
			<?php
		endif;
		?>

		<?php communicationstoday_render_leaderboard_banner(); ?>
