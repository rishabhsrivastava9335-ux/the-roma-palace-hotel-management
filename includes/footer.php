<?php
/**
 * THE ROMA PALACE — Global Luxury Editorial Footer
 * BTech CSE DBMS Mini Project
 */
?>
  <!-- Global Editorial Footer (Dark Background for Strict Contrast) -->
  <footer class="site-footer" id="about">
    <div class="container">
      <div class="footer-top-grid">
        
        <!-- Brand Summary -->
        <div class="footer-brand-col">
          <div style="display: flex; align-items: center; gap: 0.8rem; margin-bottom: 1rem;">
            <div class="rp-monogram" style="background: rgba(255,255,255,0.05); border-color: var(--color-gold);">
              <span>RP</span>
            </div>
            <div>
              <h3>THE ROMA PALACE</h3>
              <p class="footer-tagline">“A Legacy of Luxury, A Stay to Remember.”</p>
            </div>
          </div>
          <p class="footer-about-text">
            For over two decades, The Roma Palace has preserved the soul of Indian regal hospitality, blending timeless palatial architecture with intuitive contemporary luxury.
          </p>
          <div class="footer-social-links">
            <a href="#" class="social-icon-btn" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
            <a href="#" class="social-icon-btn" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
            <a href="#" class="social-icon-btn" aria-label="LinkedIn"><i class="fa-brands fa-linkedin-in"></i></a>
            <a href="#" class="social-icon-btn" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
          </div>
        </div>

        <!-- Palaces -->
        <div class="footer-col">
          <h4>OUR PALACES</h4>
          <ul class="footer-links">
            <li><a href="hotels.php#jaipur">Roma Palace Jaipur</a></li>
            <li><a href="hotels.php#goa">Roma Palace Goa</a></li>
            <li><a href="hotels.php#udaipur">Roma Palace Udaipur</a></li>
            <li><a href="hotels.php#lucknow">Roma Palace Lucknow</a></li>
            <li><a href="hotels.php">Kerala & Varanasi</a></li>
          </ul>
        </div>

        <!-- Stays & Suites -->
        <div class="footer-col">
          <h4>EXPLORE</h4>
          <ul class="footer-links">
            <li><a href="rooms.php?type=Deluxe+Room">Deluxe Rooms</a></li>
            <li><a href="rooms.php?type=Premium+Room">Premium Rooms</a></li>
            <li><a href="rooms.php?type=Executive+Room">Executive Suites</a></li>
            <li><a href="rooms.php?type=Luxury+Suite">Luxury Suites</a></li>
            <li><a href="rooms.php?type=Royal+Suite">The Royal Suite</a></li>
            <li><a href="offers.php">Signature Offers</a></li>
          </ul>
        </div>

        <!-- Experiences & Dining -->
        <div class="footer-col">
          <h4>LIFESTYLE</h4>
          <ul class="footer-links">
            <li><a href="dining.php">The Roma Table</a></li>
            <li><a href="dining.php">Palazzo Café Goa</a></li>
            <li><a href="dining.php">Spice Route Awadh</a></li>
            <li><a href="dining.php">Azure Lake Lounge</a></li>
            <li><a href="wellness.php">Ayurvedic Spa & Gym</a></li>
            <li><a href="experiences.php">Curated Tours</a></li>
          </ul>
        </div>

        <!-- Newsletter & Concierge -->
        <div class="footer-col">
          <h4>CONCIERGE DESK</h4>
          <p style="font-size: 0.85rem; color: var(--text-light-secondary); margin-bottom: 0.8rem;">
            Subscribe to receive private invitations to royal events and seasonal privilege packages.
          </p>
          <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing to The Roma Palace private dispatch.');">
            <input type="email" class="newsletter-input" placeholder="Your email address" required>
            <button type="submit" class="newsletter-btn"><i class="fa-solid fa-arrow-right"></i></button>
          </form>
          <div style="margin-top: 1.5rem; font-size: 0.82rem; color: var(--color-gold-light);">
            <div><i class="fa-solid fa-phone" style="margin-right: 6px;"></i> +91 (0) 141 278 9000</div>
            <div style="margin-top: 4px;"><i class="fa-solid fa-envelope" style="margin-right: 6px;"></i> concierge@romapalace.com</div>
          </div>
        </div>

      </div>

      <!-- Footer Bottom -->
      <div class="footer-bottom">
        <p>Copyright &copy; <?php echo date('Y'); ?> The Roma Palace Hotels & Resorts Ltd. All rights reserved.</p>
        <div class="footer-legal-links">
          <a href="#">Privacy Policy</a>
          <a href="#">Terms of Stay</a>
          <a href="#">Cancellation Policy</a>
          <a href="admin/admin-login.php">Staff & Admin Login</a>
          <a href="admin/demo-presentation.php" style="color: var(--color-gold); font-weight: 600;">DBMS Project Demo</a>
        </div>
      </div>

    </div>
  </footer>

  <!-- Scripts -->
  <script src="assets/js/main.js"></script>
</body>
</html>
