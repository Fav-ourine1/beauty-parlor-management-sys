<?php $pageTitle = 'Contact Us'; ?>
<?php include BASE_PATH . '/app/views/layouts/public_head.php'; ?>

<section class="page-hero">
  <h1>Get in Touch</h1>
  <p>We'd love to hear from you. Visit us, call us, or send a message.</p>
</section>

<section class="content-section">
  <div class="contact-grid">

    <div>
      <h3 style="margin-bottom:20px">Contact Information</h3>
      <ul class="contact-list">
        <li>
          <div class="contact-icon-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          </div>
          <div>
            <strong>Address</strong>
            <p>Mbagathi Way, Nairobi, Kenya</p>
          </div>
        </li>
        <li>
          <div class="contact-icon-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.15 12 19.79 19.79 0 0 1 1.08 3.38 2 2 0 0 1 3.06 1.2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L7.09 8.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 21 16.92z"/></svg>
          </div>
          <div>
            <strong>Phone</strong>
            <p><a href="tel:+254700000000">+254 700 000 000</a></p>
          </div>
        </li>
        <li>
          <div class="contact-icon-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          </div>
          <div>
            <strong>Email</strong>
            <p><a href="mailto:hello@mbagathi.com">hello@mbagathi.com</a></p>
          </div>
        </li>
        <li>
          <div class="contact-icon-box">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
          </div>
          <div>
            <strong>Opening Hours</strong>
            <p>Monday – Friday: 8:00 AM – 7:00 PM<br>Saturday: 8:00 AM – 6:00 PM<br>Sunday: Closed</p>
          </div>
        </li>
      </ul>
    </div>

    <div class="contact-form-card">
      <h3 style="margin-bottom:20px">Send Us a Message</h3>
      <form id="contact-form">
        <div class="form-group">
          <label class="form-label">Your Name</label>
          <input type="text" class="form-input" name="name" placeholder="Jane Doe" required>
        </div>
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-input" name="email" placeholder="jane@example.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">Phone (optional)</label>
          <input type="tel" class="form-input" name="phone" placeholder="07xx xxx xxx">
        </div>
        <div class="form-group">
          <label class="form-label">Message</label>
          <textarea class="form-input" name="message" rows="5" placeholder="How can we help you?" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary" style="width:100%">Send Message</button>
      </form>
    </div>

  </div>
</section>

<?php
$pageScript = <<<JS
document.getElementById('contact-form').addEventListener('submit', function(e) {
  e.preventDefault();
  toast('Message sent! We will get back to you shortly.', 'success');
  this.reset();
});
JS;
?>
<?php include BASE_PATH . '/app/views/layouts/public_foot.php'; ?>
