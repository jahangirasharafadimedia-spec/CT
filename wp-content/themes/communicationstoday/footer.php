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
                <!-- Company Info -->
                <div class="footer-column footer-company-info">
                    <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-company-logo-link">
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/asset/img/Adinewsloago.png'); ?>" class="footer-company-logo" alt="<?php bloginfo('name'); ?>">
                    </a>
                    <p class="company-description">We create rich business content, reach targeted business
                        audiences, and provide valuable business information to our readers.</p>
                    <div class="contact-info">
                        <div class="contact-item">
                            <h4 class="contact-label">Address</h4>
                            <p class="contact-value">C-35, Sector 62, Noida, Uttar Pradesh 201307</p>
                        </div>
                        <div class="contact-item">
                            <h4 class="contact-label">Email</h4>
                            <p class="contact-value">Circulation@adi-media.com</p>
                        </div>
                        <div class="contact-item">
                            <h4 class="contact-label">Phone Number</h4>
                            <p class="contact-value">+91-9350590707</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Links -->
                <div class="footer-column">
                    <h3 class="footer-column-title">Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="#">Home</a></li>
                        <li><a href="#">About Us</a></li>
                        <li><a href="#">Contact Us</a></li>
                        <li><a href="#">Terms &amp; Conditions</a></li>
                        <li><a href="#">Privacy Policy</a></li>
                        <li><a href="#">Disclaimer</a></li>
                    </ul>
                </div>

                <!-- ENTERPRISE -->
                <div class="footer-column">
                    <h3 class="footer-column-title">Enterprise</h3>
                    <ul class="footer-links">
                        <li><a href="#">Company News</a></li>
                        <li><a href="#">International News</a></li>
                        <li><a href="#">M&amp;A</a></li>
                        <li><a href="#">New Launches</a></li>
                        <li><a href="#">Financial Results</a></li>
                        <li><a href="#">Market Foresight</a></li>
                    </ul>
                </div>

                <!-- CARRIERS -->
                <div class="footer-column">
                    <h3 class="footer-column-title">Carriers</h3>
                    <ul class="footer-links">
                        <li><a href="#">News Watch</a></li>
                        <li><a href="#">Regulatory</a></li>
                        <li><a href="#">Carrier Update</a></li>
                        <li><a href="#">Carrier Investment</a></li>
                        <li><a href="#">Company News</a></li>
                        <li><a href="#">New Models</a></li>
                        <li><a href="#">Globe Trotting</a></li>
                        <li><a href="#">Trends</a></li>
                    </ul>
                </div>

                <!-- MORE NEWS -->
                <div class="footer-column">
                    <h3 class="footer-column-title">More News</h3>
                    <ul class="footer-links">
                        <li><a href="#">Movements</a></li>
                        <li><a href="#">Blogs</a></li>
                        <li><a href="#">Events</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-social">
                <div class="footer-social-icons">
                    <a href="#" class="footer-social-icon"><i class="fab fa-linkedin-in"></i></a>
                    <a href="#" class="footer-social-icon"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="footer-social-icon"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
            <!-- Footer Bottom -->
            <div class="footer-bottom">

                <p class="footer-copyright">Copyright © 2025 Communications Today</p>
            </div>
        </div>
    </div>
</footer>



<!-- Swiper JS -->
<script data-cfasync="false" src="/cdn-cgi/scripts/5c5dd728/cloudflare-static/email-decode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<!-- JavaScript -->
<script src="<?php echo esc_url(get_template_directory_uri() . '/asset/js/custom.js'); ?>"></script>
<script defer src="https://static.cloudflareinsights.com/beacon.min.js/v8c78df7c7c0f484497ecbca7046644da1771523124516" integrity="sha512-8DS7rgIrAmghBFwoOTujcf6D9rXvH8xm8JQ1Ja01h9QX8EzXldiszufYa4IFfKdLUKTTrnSFXLDkUEOTrZQ8Qg==" data-cf-beacon='{"version":"2024.11.0","token":"7e978dec01fc46bd9b0793d4f04fa0e1","r":1,"server_timing":{"name":{"cfCacheStatus":true,"cfEdge":true,"cfExtPri":true,"cfL4":true,"cfOrigin":true,"cfSpeedBrain":true},"location_startswith":null}}' crossorigin="anonymous"></script>


<?php wp_footer(); ?>
</body>

</html>