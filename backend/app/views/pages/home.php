<?php $pageTitle = 'Mbagathi Beauty Parlour — Nairobi'; ?>
<?php include BASE_PATH . '/app/views/layouts/public_head.php'; ?>

<!-- Hero -->
<section class="home-hero">
  <div class="hero-content">
    <div class="hero-badge">Nairobi's Trusted Beauty Destination</div>
    <h1 class="hero-headline">
      Look your best,<br>
      <span class="hero-accent">feel unstoppable.</span>
    </h1>
    <p class="hero-sub">Professional hair, nails, skin, and makeup services — all in one place. Easy online booking, M-Pesa payments accepted.</p>
    <div class="hero-actions">
      <a href="<?= APP_URL ?>/register" class="btn btn-primary hero-btn">Book an Appointment</a>
      <a href="<?= APP_URL ?>/about"    class="btn btn-outline   hero-btn">Learn About Us</a>
    </div>
    <div class="hero-trust">
      <span>✓ Certified stylists</span>
      <span>✓ M-Pesa accepted</span>
      <span>✓ Mon–Sat 8 AM–7 PM</span>
    </div>
  </div>
  <div class="hero-visual" aria-hidden="true">
    <div class="hero-blob">
      <div class="blob-ring blob-ring-1"></div>
      <div class="blob-ring blob-ring-2"></div>
      <div class="blob-ring blob-ring-3"></div>
      <div class="blob-emoji">💅</div>
    </div>
  </div>
</section>

<!-- Stats bar -->
<div class="stats-bar">
  <div class="stats-bar-inner">
    <div class="stat-pill"><strong>500+</strong> Happy Clients</div>
    <div class="stat-divider"></div>
    <div class="stat-pill"><strong>6</strong> Service Categories</div>
    <div class="stat-divider"></div>
    <div class="stat-pill"><strong>3</strong> Expert Stylists</div>
    <div class="stat-divider"></div>
    <div class="stat-pill"><strong>5★</strong> Rated in Nairobi</div>
  </div>
</div>

<!-- Services -->
<section class="home-section">
  <div class="home-section-inner">
    <div class="section-heading">
      <div class="section-eyebrow">What We Offer</div>
      <h2>Services tailored for you</h2>
      <p>From everyday touch-ups to full transformations — we've got you covered.</p>
    </div>
    <?php
    $catIcons = [
        'Hairdressing'              => ['icon' => '✂️',  'desc' => 'Cuts, braids, blow-dries, and everything in between.'],
        'Hair Colouring & Treatment'=> ['icon' => '🎨',  'desc' => 'Colour, highlights, deep conditioning and repair.'],
        'Nail Care'                 => ['icon' => '💅',  'desc' => 'Manicures, pedicures, gel nails, and nail art.'],
        'Facial & Skincare'         => ['icon' => '✨',  'desc' => 'Cleansing, exfoliation, and targeted skin treatments.'],
        'Makeup'                    => ['icon' => '💄',  'desc' => 'Everyday glam, events, and full bridal packages.'],
        'Eyebrow & Threading'       => ['icon' => '🪡',  'desc' => 'Precise shaping and threading for a clean finish.'],
    ];
    ?>
    <div class="services-grid">
      <?php foreach ($categories as $cat):
        $meta = $catIcons[$cat['name']] ?? ['icon' => '🌸', 'desc' => 'Professional beauty services.'];
      ?>
        <div class="service-tile">
          <div class="service-tile-icon"><?= $meta['icon'] ?></div>
          <h3><?= e($cat['name']) ?></h3>
          <p><?= $meta['desc'] ?></p>
        </div>
      <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin-top:36px">
      <a href="<?= APP_URL ?>/register" class="btn btn-primary">See All Services & Prices</a>
    </div>
  </div>
</section>

<!-- How it works -->
<section class="home-section home-section-alt">
  <div class="home-section-inner">
    <div class="section-heading">
      <div class="section-eyebrow">Simple Process</div>
      <h2>Book in 3 easy steps</h2>
    </div>
    <div class="steps-grid">
      <div class="step-card">
        <div class="step-number">1</div>
        <h3>Create an account</h3>
        <p>Sign up for free in under a minute — just your name, phone, and email.</p>
      </div>
      <div class="step-arrow">→</div>
      <div class="step-card">
        <div class="step-number">2</div>
        <h3>Choose your services</h3>
        <p>Pick from our full menu, select a date and time that works for you.</p>
      </div>
      <div class="step-arrow">→</div>
      <div class="step-card">
        <div class="step-number">3</div>
        <h3>Come in & relax</h3>
        <p>We'll send a confirmation. Show up and let our team take care of the rest.</p>
      </div>
    </div>
  </div>
</section>

<!-- Why us -->
<section class="home-section">
  <div class="home-section-inner">
    <div class="why-grid">
      <div class="why-text">
        <div class="section-eyebrow">Why Mbagathi</div>
        <h2>Beauty services you can trust</h2>
        <p style="color:var(--text-muted);margin:16px 0 28px;line-height:1.8">We combine skill, care, and quality products to give you results that last. Every client who walks through our doors leaves feeling their absolute best.</p>
        <ul class="why-list">
          <li><span class="why-check">✓</span> Certified, experienced beauty professionals</li>
          <li><span class="why-check">✓</span> Premium, skin-safe products only</li>
          <li><span class="why-check">✓</span> Hygienic, welcoming environment</li>
          <li><span class="why-check">✓</span> Flexible appointment scheduling</li>
          <li><span class="why-check">✓</span> M-Pesa & cash payments accepted</li>
        </ul>
        <a href="<?= APP_URL ?>/about" class="btn btn-secondary" style="margin-top:24px">Meet Our Team</a>
      </div>
      <div class="why-cards">
        <div class="why-card why-card-accent">
          <div class="why-card-icon">🌸</div>
          <strong>Client-First Approach</strong>
          <p>Your comfort and satisfaction come before everything else.</p>
        </div>
        <div class="why-card">
          <div class="why-card-icon">⏱️</div>
          <strong>On-Time, Every Time</strong>
          <p>We respect your schedule and keep appointments running smoothly.</p>
        </div>
        <div class="why-card">
          <div class="why-card-icon">💎</div>
          <strong>Premium Quality</strong>
          <p>We use only trusted, professional-grade beauty products.</p>
        </div>
        <div class="why-card why-card-accent">
          <div class="why-card-icon">📱</div>
          <strong>Easy Online Booking</strong>
          <p>Book from your phone anytime — no calls needed.</p>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- CTA Banner -->
<section class="cta-banner">
  <div class="cta-banner-inner">
    <h2>Ready for your glow-up?</h2>
    <p>Join hundreds of happy clients. Book your appointment today.</p>
    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:28px">
      <a href="<?= APP_URL ?>/register" class="btn btn-cta-primary">Create Free Account</a>
      <a href="<?= APP_URL ?>/contact"  class="btn btn-cta-outline">Contact Us</a>
    </div>
  </div>
</section>

<?php include BASE_PATH . '/app/views/layouts/public_foot.php'; ?>
