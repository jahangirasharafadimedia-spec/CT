<?php

/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Communicationstoday
 */

?>
<footer class="main-footer">
    <!-- Newsletter Section -->
    <div class="newsletter-section">
        <div class="container">
            <div class="newsletter-content" data-np-autofill-form-type="subscribe" data-np-watching="1">
                <i class="fas fa-envelope newsletter-icon"></i>
                <h2 class="newsletter-heading">Subscribe to our Weekly Newsletter</h2>
                <p class="newsletter-subheading">Top stories, delivered to your inbox every week.</p>
                <div class="newsletter-form">
                    <input type="email" class="newsletter-input" placeholder="Your email" data-np-checked="1" data-np-autofill-field-type="email">
                    <button class="newsletter-button" data-np-autofill-submit="">Subscribe now</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer Content Section -->
    <div class="footer-content-section">
        <div class="container">
            <div class="footer-grid">


				<?php
				for ( $fi = 1; $fi <= 5; $fi++ ) :
					$footer_id   = 'footer-' . $fi;
					$column_class = array( 'footer-column', 'footer-widget-area', 'footer-widget-area-' . $fi );
					if ( 1 === $fi ) {
						$column_class[] = 'footer-company-info';
					}
					?>
                <div class="<?php echo esc_attr( implode( ' ', $column_class ) ); ?>">
					<?php if ( is_active_sidebar( $footer_id ) ) : ?>
						<?php dynamic_sidebar( $footer_id ); ?>
					<?php endif; ?>
                </div>
					<?php
				endfor;
				?>
            </div>
            <div class="footer-social">
                <div class="footer-social-icons">
					<?php communicationstoday_render_social_links( 'footer-social-icon', array( 'linkedin', 'twitter', 'youtube' ) ); ?>
                </div>
            </div>
            <!-- Footer Bottom -->
            <div class="footer-bottom">

                <p class="footer-copyright"><?php echo esc_html( communicationstoday_get_footer_copyright_text() ); ?></p>
            </div>
        </div>
    </div>
</footer>



<!-- Swiper + theme JS load via wp_enqueue_script in functions.php -->
<?php if ( 'production' === wp_get_environment_type() ) : ?>
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v8c78df7c7c0f484497ecbca7046644da1771523124516" integrity="sha512-8DS7rgIrAmghBFwoOTujcf6D9rXvH8xm8JQ1Ja01h9QX8EzXldiszufYa4IFfKdLUKTTrnSFXLDkUEOTrZQ8Qg==" data-cf-beacon='{"version":"2024.11.0","token":"7e978dec01fc46bd9b0793d4f04fa0e1","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>
<?php endif; ?>


<?php wp_footer(); ?>
</body>

</html>