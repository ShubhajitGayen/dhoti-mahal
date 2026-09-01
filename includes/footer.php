<?php
// ============================================================
// Dhoti Mahal - Common Footer
// ============================================================
?>
</main>

<!-- Footer -->
<footer class="site-footer">
    <div class="container footer-grid">
        <!-- Brand -->
        <div class="footer-col">
            <div class="footer-logo"><img style="width: 100px; height: auto; background: #f0f0f0; border-radius: 50%;"
                    src="<?= BASE_URL ?>\uploads\icon\icon.png" alt="">
                <?= sanitize(getSetting('site_name', SITE_NAME)) ?></div>
            <p><?= sanitize(getSetting('site_tagline', SITE_TAGLINE)) ?></p>
            <p class="footer-address"><i class="fas fa-map-marker-alt"></i> <?= sanitize(getSetting('site_address')) ?>
            </p>
            <div class="footer-social">
                <?php if ($fb = getSetting('facebook_url')): ?>
                <a href="<?= sanitize($fb) ?>" target="_blank" rel="noopener"><i class="fab fa-facebook-f"></i></a>
                <?php endif; ?>
                <?php if ($ig = getSetting('instagram_url')): ?>
                <a href="<?= sanitize($ig) ?>" target="_blank" rel="noopener"><i class="fab fa-instagram"></i></a>
                <?php endif; ?>
                <?php if ($wa = getSetting('whatsapp_number')): ?>
                <a href="https://wa.me/<?= sanitize($wa) ?>" target="_blank" rel="noopener"><i
                        class="fab fa-whatsapp"></i></a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories -->
        <div class="footer-col">
            <h4>Shop By Category</h4>
            <ul>
                <?php foreach (getCategories() as $cat): ?>
                <li><a
                        href="<?= BASE_URL ?>pages/category.php?slug=<?= urlencode($cat['slug']) ?>"><?= sanitize($cat['name']) ?></a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Quick Links -->
        <div class="footer-col">
            <h4>Quick Links</h4>
            <ul>
                <li><a href="<?= BASE_URL ?>pages/cart.php">My Cart</a></li>
                <li><a href="<?= BASE_URL ?>pages/track-order.php">Track Order</a></li>
                <li><a href="<?= BASE_URL ?>pages/login.php">Login / Register</a></li>
                <li><a href="<?= BASE_URL ?>pages/user-profile.php">My Account</a></li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="footer-col">
            <h4>Contact Us</h4>
            <p><i class="fas fa-phone-alt"></i> <?= sanitize(getSetting('site_phone')) ?></p>
            <p><i class="fas fa-envelope"></i> <?= sanitize(getSetting('site_email')) ?></p>
            <p class="gst-text">GST: <?= sanitize(getSetting('gst_number')) ?></p>
            <div class="payment-icons">
                <span>UPI</span>
                <span>GPay</span>
                <span>PhonePe</span>
                <span>Paytm</span>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <p>
                © <?= date('Y') ?> <?= sanitize(getSetting('site_name', SITE_NAME)) ?>. All rights reserved.
                | Developed by <a href="https://shubhajit-portfolio.vercel.app/" target="_blank">Shubhajit Gayen</a>
                | Made with ❤️ in West Bengal
            </p>
        </div>
    </div>
</footer>

<!-- Main JS -->
<script src="<?= BASE_URL ?>assets/js/main.js"></script>
</body>

</html>