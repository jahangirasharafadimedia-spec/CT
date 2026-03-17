<?php

/**
 * The header for our theme
 *
 * This is the template that displays all of the <head> section and everything up until <div id="content">
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Communicationstoday
 */

?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="profile" href="https://gmpg.org/xfn/11">

	<!-- Theme main stylesheet -->
	<link rel="stylesheet" href="<?php echo esc_url( get_stylesheet_uri() ); ?>">

	<!-- Font Awesome -->
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
	<!-- Swiper CSS -->
	<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">

	<?php wp_head(); ?>



</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div id="page" class="site">
		<a class="skip-link screen-reader-text" href="#primary"><?php esc_html_e('Skip to content', 'communicationstoday'); ?></a>

		<!-- Sidebar Menu -->
		<div class="sidebar-menu" id="sidebarMenu">
			<div class="sidebar-header">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="sidemenu_logo">
					<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/logo.png' ); ?>"
						alt="<?php bloginfo( 'name' ); ?>">
				</a>
				<i class="fas fa-times sidebar-close"></i>
			</div>
			<nav class="sidebar-nav">
				<div class="sidebar-menu-item-dropdown">
					<a href="#" class="sidebar-menu-item sidebar-dropdown-toggle">
						<span>ENTERPRISE</span>
						<i class="fas fa-plus"></i>
					</a>
					<ul class="sidebar-dropdown-menu">
						<li><a href="#">Enterprise Solutions</a></li>
						<li><a href="#">Enterprise News</a></li>
						<li><a href="#">Enterprise Reports</a></li>
					</ul>
				</div>
				<div class="sidebar-menu-item-dropdown">
					<a href="#" class="sidebar-menu-item sidebar-dropdown-toggle">
						<span>CARRIERS</span>
						<i class="fas fa-plus"></i>
					</a>
					<ul class="sidebar-dropdown-menu">
						<li><a href="#">Carrier News</a></li>
						<li><a href="#">Carrier Analysis</a></li>
						<li><a href="#">Carrier Reports</a></li>
					</ul>
				</div>
				<div class="sidebar-menu-item-dropdown">
					<a href="#" class="sidebar-menu-item sidebar-dropdown-toggle">
						<span>BROADCAST</span>
						<i class="fas fa-plus"></i>
					</a>
					<ul class="sidebar-dropdown-menu">
						<li><a href="#">Broadcast News</a></li>
						<li><a href="#">Broadcast Technology</a></li>
						<li><a href="#">Broadcast Industry</a></li>
					</ul>
				</div>
				<div class="sidebar-menu-item-dropdown">
					<a href="#" class="sidebar-menu-item sidebar-dropdown-toggle">
						<span>DAILY NEWS</span>
						<i class="fas fa-plus"></i>
					</a>
					<ul class="sidebar-dropdown-menu">
						<li><a href="#">Today's News</a></li>
						<li><a href="#">Weekly Roundup</a></li>
						<li><a href="#">News Archive</a></li>
					</ul>
				</div>
				<a href="#" class="sidebar-menu-item">EDITOR'S DESK</a>
				<a href="#" class="sidebar-menu-item">PERSPECTIVE</a>
				<a href="#" class="sidebar-menu-item">REPORTS</a>
				<a href="#" class="sidebar-menu-item">THINK TANK</a>
				<a href="#" class="sidebar-menu-item">FACE-TO-FACE (BLOCKCHAIN)</a>
				<a href="#" class="sidebar-menu-item">VIDEOS</a>
				<a href="#" class="sidebar-menu-item">CURRENT</a>
				<a href="#" class="sidebar-menu-item">5G</a>
				<a href="#" class="sidebar-menu-item">IMC 2025</a>
				<a href="#" class="sidebar-menu-item">CONVERGENCE INDIA 2025</a>
				<a href="#" class="sidebar-menu-item">MWC 2025</a>
				<a href="#" class="sidebar-menu-item">CONVERGENCE INDIA 2024</a>
				<a href="#" class="sidebar-menu-item">MWC 2024</a>
			</nav>
			<div class="sidebar-footer">
				<div class="connect-with-us">
					<h3>CONNECT WITH US</h3>
					<div class="social-icons">
						<a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
						<a href="#" class="social-icon"><i class="fab fa-youtube"></i></a>
						<a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
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
					<input type="text" class="search-input" placeholder="Search...">
					<i class="fas fa-search search-input-icon"></i>
				</div>
				<div class="search-results">
					<div class="search-result-card">
						<div class="search-result-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png' ); ?>" alt="Post 1" class="w-100">
						</div>
						<div class="search-result-content">
							<h3 class="search-result-title">Breaking: New 5G Technology Launched in India</h3>
							<p class="search-result-excerpt">Revolutionary 5G infrastructure promises faster connectivity
								and better coverage across India with advanced network capabilities.</p>
						</div>
					</div>

					<div class="search-result-card">
						<div class="search-result-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png' ); ?>" alt="Post 1" class="w-100">
						</div>
						<div class="search-result-content">
							<h3 class="search-result-title">AI Innovation in Telecommunications Sector</h3>
							<p class="search-result-excerpt">Leading telecom companies invest heavily in AI-driven solutions
								for better customer experience and network optimization.</p>
						</div>
					</div>

					<div class="search-result-card">
						<div class="search-result-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png' ); ?>" alt="Post 1" class="w-100">
						</div>
						<div class="search-result-content">
							<h3 class="search-result-title">Digital India Initiative Progress Report 2025</h3>
							<p class="search-result-excerpt">Government reports significant progress in digital
								infrastructure development nationwide with improved connectivity.</p>
						</div>
					</div>

					<div class="search-result-card">
						<div class="search-result-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png' ); ?>" alt="Post 1" class="w-100">
						</div>
						<div class="search-result-content">
							<h3 class="search-result-title">Broadband Expansion Plans for Rural Areas</h3>
							<p class="search-result-excerpt">New initiatives aim to bring high-speed internet to remote
								villages and improve digital inclusion across the country.</p>
						</div>
					</div>

					<div class="search-result-card">
						<div class="search-result-image">
							<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/4a5e7e0aed3b12697a186a692abd5914622822d6.png' ); ?>" alt="Post 1" class="w-100">
						</div>
						<div class="search-result-content">
							<h3 class="search-result-title">Telecom Regulatory Updates and Policy Changes</h3>
							<p class="search-result-excerpt">Recent regulatory changes impact the telecommunications
								industry with new guidelines for service providers.</p>
						</div>
					</div>
				</div>
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
								<img src="<?php echo esc_url( get_template_directory_uri() . '/asset/img/logo.png' ); ?>"
									alt="<?php bloginfo( 'name' ); ?>">
							</a>
						</div>
						<div class="top-banner-right">
							<i class="fab fa-linkedin-in"></i>
							<i class="fab fa-twitter"></i>
							<i class="fab fa-youtube"></i>
						</div>
					</div>
				</div>
			</div>


				<div class="nav-bar">
					<div class="container">
						<nav class="nav-menu">
							<div class="nav-item">
								<a href="#">HOME</a>
							</div>
							<div class="nav-item dropdown">
								<a href="#" class="dropdown-toggle">ENTERPRISE <i class="fas fa-chevron-down"></i></a>
								<ul class="dropdown-menu">
									<li><a href="#">Enterprise Solutions</a></li>
									<li><a href="#">Enterprise News</a></li>
									<li><a href="#">Enterprise Reports</a></li>
								</ul>
							</div>
							<div class="nav-item dropdown">
								<a href="#" class="dropdown-toggle">CARRIERS <i class="fas fa-chevron-down"></i></a>
								<ul class="dropdown-menu">
									<li><a href="#">Carrier News</a></li>
									<li><a href="#">Carrier Analysis</a></li>
									<li><a href="#">Carrier Reports</a></li>
								</ul>
							</div>
							<div class="nav-item dropdown">
								<a href="#" class="dropdown-toggle">BROADCAST <i class="fas fa-chevron-down"></i></a>
								<ul class="dropdown-menu">
									<li><a href="#">Broadcast News</a></li>
									<li><a href="#">Broadcast Technology</a></li>
									<li><a href="#">Broadcast Industry</a></li>
								</ul>
							</div>
							<div class="nav-item dropdown">
								<a href="#" class="dropdown-toggle">DAILY NEWS <i class="fas fa-chevron-down"></i></a>
								<ul class="dropdown-menu">
									<li><a href="#">Today's News</a></li>
									<li><a href="#">Weekly Roundup</a></li>
									<li><a href="#">News Archive</a></li>
								</ul>
							</div>
							<div class="nav-item">
								<a href="#">EDITOR'S DESK</a>
							</div>
							<div class="nav-item">
								<a href="#">PERSPECTIVE</a>
							</div>
							<div class="nav-item">
								<a href="#">REPORTS</a>
							</div>
							<div class="nav-item">
								<a href="#">VIDEOS</a>
							</div>
							<div class="nav-item">
								<a href="#">5G</a>
							</div>
							<div class="nav-item">
								<a href="#">IMC 2024</a>
							</div>
							<div class="nav-item dropdown">
								<a href="#" class="dropdown-toggle">NEWSLETTER <i class="fas fa-chevron-down"></i></a>
								<ul class="dropdown-menu">
									<li><a href="#">Subscribe</a></li>
									<li><a href="#">Newsletter Archive</a></li>
									<li><a href="#">Manage Subscription</a></li>
								</ul>
							</div>
						</nav>
					</div>
				</div>
		</header>
		<!-- End of Header -->

		<!-- News Ticker -->
		<div class="news-ticker">
			<div class=" ticker-container">
				<div class="ticker-label">
					<span>Weekly Feed</span>
					<i class="fas fa-bolt"></i>
				</div>
				<div class="ticker-wrapper">
					<div class="ticker-content">
						<a href="#" class="ticker-item">OpenAI plans data center in India</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">India's time to lead in AI innovation</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Kazakhstan to invest national wealth in AI infrastructure</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Latin America smartphone market grows 2% in Q2 2025</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Samsung resumes $17B Texas chip plant construction</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Digital India 2025</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">OpenAI plans data center in India</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">India's time to lead in AI innovation</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Kazakhstan to invest national wealth in AI infrastructure</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Latin America smartphone market grows 2% in Q2 2025</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Samsung resumes $17B Texas chip plant construction</a>
						<div class="ticker-separator"></div>
						<a href="#" class="ticker-item">Digital India 2025</a>

					</div>
				</div>
			</div>
		</div>
		<!-- End of News Ticker -->