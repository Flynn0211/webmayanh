<footer class="footer-light">
    <div class="footer-light__inner">
        <div class="footer-light__grid">
            <div>
                <a href="index.php?page=trangchu" class="footer-light__brand-name">LENS &amp; LIGHT</a>
                <p class="footer-light__desc">Chúng tôi mang đến những sản phẩm quang học tinh xảo nhất cho các nghệ sĩ nhiếp ảnh.</p>
            </div>
            <div>
                <span class="footer-light__col-title">SẢN PHẨM</span>
                <div class="footer-light__link-group">
                    <a class="footer-light__link" href="index.php?page=mayanh">Máy ảnh</a>
                    <a class="footer-light__link" href="index.php?page=ongkinh">Ống kính</a>
                    <a class="footer-light__link" href="index.php?page=phukien">Phụ kiện</a>
                </div>
            </div>
            <div>
                <span class="footer-light__col-title">HỖ TRỢ</span>
                <div class="footer-light__link-group">
                    <a class="footer-light__link" href="index.php?page=trangchu">Chính sách đổi trả</a>
                    <a class="footer-light__link" href="index.php?page=donhang">Tra cứu đơn hàng</a>
                    <a class="footer-light__link" href="index.php?page=lienhe">Liên hệ</a>
                </div>
            </div>
            <div>
                <span class="footer-light__col-title">CÔNG TY</span>
                <div class="footer-light__link-group">
                    <a class="footer-light__link" href="index.php?page=lienhe">Về chúng tôi</a>
                    <a class="footer-light__link" href="index.php?page=baiviet">Bài viết</a>
                </div>
                <?php if (!isset($activeNav) || $activeNav !== 'lienhe'): ?>
                <div style="margin-top: 0.75rem; border-radius: 6px; overflow: hidden; height: 80px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <iframe src="https://www.google.com/maps?q=624+Au+Co,+Ho+Chi+Minh&output=embed" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <div class="footer-light__bottom">
            <span class="footer-light__copyright">&copy; 2026 LENS &amp; LIGHT. ALL RIGHTS RESERVED.</span>
            <div class="footer-light__socials">
                <span class="material-symbols-outlined" style="cursor:pointer;" title="Facebook">public</span>
                <span class="material-symbols-outlined" style="cursor:pointer;" title="Instagram">photo_camera</span>
            </div>
        </div>
    </div>
</footer>

