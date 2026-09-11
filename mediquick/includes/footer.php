</main>
<footer class="site-footer">
  <div class="container py-5">
    <div class="row g-4 pb-4 border-bottom-slate">
      <div class="col-md-4">
        <div class="d-flex align-items-center gap-2 mb-2">
          <span class="brand-mark brand-mark-sm"><i class="fa-solid fa-heart-pulse"></i></span>
          <span class="text-white fw-bold">MediQuick Pharmacy</span>
        </div>
        <p class="mb-0">Your trusted healthcare partner in Kurunegala. Sourcing 100% authentic medicine with island-wide delivery.</p>
      </div>
      <div class="col-md-3">
        <h4 class="footer-heading">Quick links</h4>
        <ul class="footer-links">
          <li><a href="<?= url('shop.php') ?>">Shop catalog</a></li>
          <li><a href="<?= url('prescription-upload.php') ?>">Prescription services</a></li>
          <li><a href="<?= url('health-hub.php') ?>">Health Hub &amp; articles</a></li>
          <li><a href="<?= url('contact.php') ?>">Contact us</a></li>
        </ul>
      </div>
      <div class="col-md-2">
        <h4 class="footer-heading">Regulatory</h4>
        <ul class="footer-links">
          <li>License #PMC-SRL-9872</li>
          <li>Reg. Pharmacist 4567-LK</li>
          <li>Coursework demo only</li>
        </ul>
      </div>
      <div class="col-md-3">
        <h4 class="footer-heading">Contact support</h4>
        <p class="mb-1"><i class="fa-solid fa-location-dot me-2 text-brand"></i>No. 12, Kandy Road, Kurunegala</p>
        <p class="mb-1"><i class="fa-solid fa-phone me-2 text-brand"></i>+94 37 222 4455</p>
        <p class="mb-0"><i class="fa-solid fa-envelope me-2 text-brand"></i>care@mediquick.lk</p>
      </div>
    </div>
    <div class="d-flex flex-column flex-sm-row justify-content-between gap-2 pt-4 small">
      <span>&copy; <?= date('Y') ?> MediQuick Pharmacy. All rights reserved.</span>
      <span>Built for CSE4206 coursework — not a real pharmacy.</span>
    </div>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
