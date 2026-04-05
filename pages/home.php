<?php
// ============================================================
// Dhoti Mahal - Homepage
// ============================================================
$pageTitle = 'Premium Traditional Dhotis | Weddings & Festivals';
require_once __DIR__ . '/../includes/header.php';

$banners  = getBanners();
$featured = getFeaturedProducts(8);
$cats     = getCategories();
?>

<!-- BASE_URL for JS -->
<script>window.__BASE_URL = '<?= BASE_URL ?>';</script>

<!-- ===== Hero Slider ===== -->
<section class="hero">
    <?php if (!empty($banners)): ?>
        <?php foreach ($banners as $i => $banner): ?>
            <div class="hero-slide <?= $i === 0 ? 'active' : '' ?>"
                 style="background-image: url('<?= bannerImage($banner['image']) ?>');">
                <div class="container">
                    <div class="hero-content">
                        <h1><?= sanitize($banner['title']) ?></h1>
                        <p><?= sanitize($banner['subtitle']) ?></p>
                        <?php if ($banner['link']): ?>
                            <a href="<?= sanitize($banner['link']) ?>" class="btn btn-gold">
                                <?= sanitize($banner['button_text'] ?: 'Shop Now') ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Default hero if no banners -->
        <div class="hero-slide active" style="background: linear-gradient(135deg,#7A1515 0%,#1a0505 100%);">
            <div class="container">
                <div class="hero-content">
                    <h1>The Finest Traditional Dhotis</h1>
                    <p>Handpicked from master weavers across India. Silk, cotton, and designer dhotis for every occasion.</p>
                    <a href="<?= BASE_URL ?>pages/category.php" class="btn btn-gold">
                        Explore Collection <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (count($banners) > 1): ?>
        <div class="hero-controls">
            <?php foreach ($banners as $i => $b): ?>
                <button class="hero-dot <?= $i === 0 ? 'active' : '' ?>"></button>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<!-- ===== Features Bar ===== -->
<section class="features-bar">
    <div class="container features-grid">
        <div class="feature-item">
            <i class="fas fa-truck"></i>
            <h4>Free Shipping</h4>
            <p>On orders above ₹<?= getSetting('free_shipping_above', '999') ?></p>
        </div>
        <div class="feature-item">
            <i class="fas fa-shield-alt"></i>
            <h4>Authentic Guarantee</h4>
            <p>100% genuine handloom products</p>
        </div>
        <div class="feature-item">
            <i class="fas fa-undo-alt"></i>
            <h4>Easy Returns</h4>
            <p>7-day hassle-free return policy</p>
        </div>
        <div class="feature-item">
            <i class="fas fa-headset"></i>
            <h4>Dedicated Support</h4>
            <p>WhatsApp & call support 7 days</p>
        </div>
    </div>
</section>

<!-- ===== Categories ===== -->
<section class="section">
    <div class="container">
        <div class="section-title">
            <h2>Shop By Category</h2>
            <div class="divider"><span></span><i class="fas fa-om"></i><span></span></div>
            <p>Explore our curated collection of traditional Indian dhotis</p>
        </div>
        <div class="category-grid">
            <?php
            $catIcons = ['🧵','🥻','👘','✨','👔','🪬'];
            foreach ($cats as $i => $cat):
            ?>
                <a href="<?= BASE_URL ?>pages/category.php?slug=<?= urlencode($cat['slug']) ?>" class="category-card">
                    <div class="category-icon"><?= $catIcons[$i % count($catIcons)] ?></div>
                    <div class="category-name"><?= sanitize($cat['name']) ?></div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===== Featured Products ===== -->
<section class="section" style="background: var(--cream); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border);">
    <div class="container">
        <div class="section-title">
            <h2>Featured Collection</h2>
            <div class="divider"><span></span><i class="fas fa-star"></i><span></span></div>
            <p>Our best-selling and most-loved traditional dhotis</p>
        </div>

        <?php if (!empty($featured)): ?>
            <div class="product-grid">
                <?php foreach ($featured as $product): ?>
                    <?php include __DIR__ . '/../includes/product-card.php'; ?>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No featured products yet</h3>
                <p>Check back soon for our curated picks</p>
            </div>
        <?php endif; ?>

        <div class="text-center mt-20">
            <a href="<?= BASE_URL ?>pages/category.php" class="btn btn-outline">
                View All Products <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<!-- ===== Trust Banner ===== -->
<section class="section">
    <div class="container">
        <div style="background: linear-gradient(135deg, var(--crimson) 0%, #6B1111 100%); border-radius: 10px; padding: 48px 40px; text-align: center; color: white; position: relative; overflow: hidden;">
            <div style="font-size: 3rem; margin-bottom: 16px;">🪷</div>
            <h2 style="font-family: var(--font-head); font-size: 2rem; margin-bottom: 12px;">Weaving Tradition Since Generations</h2>
            <p style="max-width: 580px; margin: 0 auto 28px; opacity: 0.85; font-size: 1rem;">
                Every dhoti at Dhoti Mahal is handpicked from the finest weavers across West Bengal, Tamil Nadu, Kerala and Varanasi. We bring you the authenticity of centuries-old craftsmanship.
            </p>
            <a href="<?= BASE_URL ?>pages/category.php" class="btn btn-gold">
                Explore Our Story <i class="fas fa-arrow-right"></i>
            </a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
