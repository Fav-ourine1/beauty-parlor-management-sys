<?php $pageTitle = 'About Us'; ?>
<?php include BASE_PATH . '/app/views/layouts/public_head.php'; ?>

<section class="page-hero">
  <h1>About Mbagathi Beauty Parlour</h1>
  <p>Your neighbourhood destination for professional beauty services in Nairobi.</p>
</section>

<section class="content-section">
  <div class="content-grid">

    <div class="content-card">
      <div class="content-icon">🌸</div>
      <h3>Our Story</h3>
      <p>Founded on Mbagathi Way, we started as a small family-run salon with a big dream — to make professional beauty care accessible to every woman in our community. Over the years we've grown into a full-service parlour trusted by hundreds of clients across Nairobi.</p>
    </div>

    <div class="content-card">
      <div class="content-icon">💫</div>
      <h3>Our Mission</h3>
      <p>To provide exceptional beauty services in a warm, welcoming environment where every client feels valued. We combine the latest techniques with high-quality products to ensure you leave looking and feeling your absolute best.</p>
    </div>

    <div class="content-card">
      <div class="content-icon">🏆</div>
      <h3>Why Choose Us</h3>
      <ul style="padding-left:1.2rem;color:var(--text-muted)">
        <li>Certified, experienced stylists</li>
        <li>Premium, skin-safe products</li>
        <li>Clean, hygienic environment</li>
        <li>Easy online booking</li>
        <li>M-Pesa payments accepted</li>
      </ul>
    </div>

  </div>
</section>

<section class="content-section" style="background:var(--primary-light)">
  <div class="section-heading">
    <h2>Our Team</h2>
    <p>Skilled professionals dedicated to your beauty.</p>
  </div>
  <div class="team-grid">
    <div class="team-card">
      <div class="team-avatar">JW</div>
      <div class="team-name">Jane Wanjiku</div>
      <div class="team-role">Senior Hair Stylist</div>
      <p class="team-bio">Specialises in natural hair care, braids, and colour treatments. 8+ years of experience.</p>
    </div>
    <div class="team-card">
      <div class="team-avatar">MA</div>
      <div class="team-name">Mary Akinyi</div>
      <div class="team-role">Nail Technician</div>
      <p class="team-bio">Expert in gel nails, nail art, and manicures. Brings creativity to every set.</p>
    </div>
    <div class="team-card">
      <div class="team-avatar">GM</div>
      <div class="team-name">Grace Muthoni</div>
      <div class="team-role">Skincare Specialist</div>
      <p class="team-bio">Certified aesthetician with expertise in facial treatments and skin health.</p>
    </div>
  </div>
</section>

<section class="content-section" style="text-align:center">
  <h2>Ready for your transformation?</h2>
  <p style="color:var(--text-muted);margin-bottom:24px">Book an appointment online in minutes.</p>
  <a href="<?= APP_URL ?>/register" class="btn btn-primary" style="font-size:1rem;padding:14px 32px">Book Now</a>
</section>

<?php include BASE_PATH . '/app/views/layouts/public_foot.php'; ?>
