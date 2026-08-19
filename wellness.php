<?php
/**
 * THE ROMA PALACE — Wellness & Spa
 * BTech CSE DBMS Mini Project &bull; Founder: Rishabh Srivastava
 */
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/auth.php';

$pageTitle = 'Wellness, Spa & Vitality Reimagined';

require_once __DIR__ . '/includes/header.php';
?>

<!-- Page Banner -->
<section style="background: linear-gradient(rgba(18,19,22,0.7), rgba(18,19,22,0.85)), url('https://images.unsplash.com/photo-1545205597-3d9d02c29597?auto=format&fit=crop&w=1600&q=85') center/cover no-repeat; padding: 7rem 2rem 4rem 2rem; text-align: center; color: #FFFFFF;">
  <div class="container">
    <span class="section-tag" style="color: var(--color-gold-light);">HOLISTIC LIVING</span>
    <h1 style="color: #FFFFFF; font-size: clamp(2rem, 4vw, 3.2rem); margin-bottom: 0.8rem;">WELLNESS, REIMAGINED</h1>
    <p style="color: var(--text-light-secondary); max-width: 650px; margin: 0 auto;">
      Ancient Himalayan yogic science, authentic Kerala Ayurveda, and modern thermal hydrotherapy designed to restore inner equilibrium.
    </p>
  </div>
</section>

<!-- Wellness Pillars -->
<section class="section-spacing bg-ivory">
  <div class="container">
    
    <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 2rem; margin-bottom: 5rem;">
      
      <!-- Spa -->
      <div style="background: var(--color-white); border-radius: 4px; padding: 2.5rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-soft);">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--color-cream); border: 1.5px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: var(--color-gold-dark); margin-bottom: 1.5rem;">
          <i class="fa-solid fa-spa"></i>
        </div>
        <h3 style="font-size: 1.4rem; margin-bottom: 0.8rem; color: var(--color-charcoal);">The Imperial Spa</h3>
        <p style="font-size: 0.9rem; line-height: 1.7; color: var(--text-dark-secondary);">
          Centuries-old Ayurvedic treatments using freshly blended organic botanicals, cold-pressed sesame oils, and therapeutic herbal scrubs.
        </p>
      </div>

      <!-- Yoga -->
      <div style="background: var(--color-white); border-radius: 4px; padding: 2.5rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-soft);">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--color-cream); border: 1.5px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: var(--color-gold-dark); margin-bottom: 1.5rem;">
          <i class="fa-solid fa-peace"></i>
        </div>
        <h3 style="font-size: 1.4rem; margin-bottom: 0.8rem; color: var(--color-charcoal);">Yoga & Pranayama</h3>
        <p style="font-size: 0.9rem; line-height: 1.7; color: var(--text-dark-secondary);">
          Sunrise and twilight breathwork at lakefront open-air pavilions led by resident yogic scholars trained in classical Hatha discipline.
        </p>
      </div>

      <!-- Hydrotherapy -->
      <div style="background: var(--color-white); border-radius: 4px; padding: 2.5rem; border: 1px solid var(--border-light); box-shadow: var(--shadow-soft);">
        <div style="width: 50px; height: 50px; border-radius: 50%; background: var(--color-cream); border: 1.5px solid var(--color-gold); display: flex; align-items: center; justify-content: center; font-size: 1.3rem; color: var(--color-gold-dark); margin-bottom: 1.5rem;">
          <i class="fa-solid fa-water-ladder"></i>
        </div>
        <h3 style="font-size: 1.4rem; margin-bottom: 0.8rem; color: var(--color-charcoal);">Pools & Hydrothermal</h3>
        <p style="font-size: 0.9rem; line-height: 1.7; color: var(--text-dark-secondary);">
          Temperature-controlled ozone swimming pools, traditional Turkish hammams, cedarwood dry saunas, and ice plunge showers.
        </p>
      </div>

    </div>

    <!-- Editorial Treatment Story -->
    <div class="welcome-grid">
      <div class="welcome-image-wrapper">
        <img src="https://images.unsplash.com/photo-1540555700478-4be289fbecef?auto=format&fit=crop&w=1200&q=85" alt="Ayurvedic Treatment" style="height: 480px; object-fit: cover;">
      </div>
      
      <div class="welcome-text">
        <span class="section-tag">ANCIENT THERAPIES</span>
        <h2>SIGNATURE AYURVEDIC JOURNEYS</h2>
        <p class="lead-quote">
          “Indulge in our 120-minute Royal Abhyanga & Shirodhara ritual, formulated exclusively for The Roma Palace by master Vaidyas.”
        </p>
        <p>
          Each wellness journey begins with an insightful consultation with our Ayurvedic doctor to diagnose your primary Dosha (Vata, Pitta, Kapha) and customize warm medicinal oils to your physiological state.
        </p>
        <div style="margin-top: 2rem;">
          <a href="booking.php" class="btn-primary">
            <i class="fa-solid fa-calendar-check"></i>
            <span>BOOK A WELLNESS ESCAPE</span>
          </a>
        </div>
      </div>
    </div>

  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
