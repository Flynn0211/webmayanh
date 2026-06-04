<footer class="footer-dark">
    <div class="footer-dark__grid">
        <!-- Brand col -->
        <div class="footer-dark__brand-col">
            <span class="footer-dark__brand-name">
                <span class="material-symbols-outlined" style="color:var(--primary);">camera</span>
                LENS &amp; LIGHT
            </span>
            <p class="footer-dark__brand-desc">Chúng tôi mang đến những sản phẩm quang học tinh xảo nhất cho những nghệ sĩ khắt khe nhất thế giới.</p>
            <div class="footer-dark__socials">
                <a href="#" class="footer-dark__social-btn" aria-label="Facebook">
                    <span class="material-symbols-outlined" style="font-size:1.125rem;">public</span>
                </a>
                <a href="#" class="footer-dark__social-btn" aria-label="Instagram">
                    <span class="material-symbols-outlined" style="font-size:1.125rem;">photo_camera</span>
                </a>
                <a href="#" class="footer-dark__social-btn" aria-label="Youtube">
                    <span class="material-symbols-outlined" style="font-size:1.125rem;">smart_display</span>
                </a>
            </div>
        </div>

        <!-- Products -->
        <div class="footer-dark__col">
            <span class="footer-dark__col-title">SẢN PHẨM</span>
            <ul class="footer-dark__link-list">
                <li><a class="footer-dark__link" href="index.php?page=mayanh">Máy ảnh Mirrorless</a></li>
                <li><a class="footer-dark__link" href="index.php?page=mayanh">Máy ảnh DSLR</a></li>
                <li><a class="footer-dark__link" href="index.php?page=ongkinh">Ống kính</a></li>
                <li><a class="footer-dark__link" href="index.php?page=phukien">Phụ kiện</a></li>
                <li><a class="footer-dark__link" href="index.php?page=trangchu">Sản phẩm giới hạn</a></li>
            </ul>
        </div>

        <!-- Company -->
        <div class="footer-dark__col">
            <span class="footer-dark__col-title">CÔNG TY</span>
            <ul class="footer-dark__link-list">
                <li><a class="footer-dark__link" href="index.php?page=lienhe">Về chúng tôi</a></li>
                <li><a class="footer-dark__link" href="index.php?page=donhang">Đơn hàng của tôi</a></li>
                <li><a class="footer-dark__link" href="index.php?page=baiviet">Bài viết</a></li>
                <li><a class="footer-dark__link" href="index.php?page=lienhe">Liên hệ</a></li>
            </ul>
        </div>

        <!-- Contact -->
        <div class="footer-dark__col">
            <span class="footer-dark__col-title">LIÊN HỆ</span>
            <div style="display:flex;flex-direction:column;gap:0.75rem;">
                <div class="footer-dark__contact-item">
                    <span class="material-symbols-outlined footer-dark__contact-icon">location_on</span>
                    <span>624 Âu Cơ, Bảy Hiền, Hồ Chí Minh</span>
                </div>
                <div class="footer-dark__contact-item">
                    <span class="material-symbols-outlined footer-dark__contact-icon">phone</span>
                    <span>0123 456 789</span>
                </div>
                <div class="footer-dark__contact-item">
                    <span class="material-symbols-outlined footer-dark__contact-icon">mail</span>
                    <span>hello@lensandlight.vn</span>
                </div>
            </div>
            <?php if (!isset($activeNav) || $activeNav !== 'lienhe'): ?>
            <div style="margin-top: 0.75rem; border-radius: 6px; overflow: hidden; height: 80px; box-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                <iframe src="https://www.google.com/maps?q=624+Au+Co,+Ho+Chi+Minh&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="footer-dark__bottom">
        <div class="footer-dark__bottom-inner">
            <span class="footer-dark__copyright">&copy; 2026 LENS &amp; LIGHT. ALL RIGHTS RESERVED.</span>
            <div class="footer-dark__payments">
                <span class="footer-dark__payment-badge">VISA</span>
                <span class="footer-dark__payment-badge">MASTERCARD</span>
                <span class="footer-dark__payment-badge">MOMO</span>
                <span class="footer-dark__payment-badge">VNPAY</span>
            </div>
        </div>
    </div>
</footer>

