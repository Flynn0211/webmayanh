<?php
$activeNav = 'lienhe';
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description"
        content="Liên hệ với LENS & LIGHT để nhận được tư vấn tốt nhất về các thiết bị nhiếp ảnh." />
    <title>LENS &amp; LIGHT - Liên Hệ</title>
    <link href="https://fonts.googleapis.com/css2?family=Geist:wght@100..900&family=Inter:wght@100..900&display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap"
        rel="stylesheet" />
    <link href="assets/css/base.css" rel="stylesheet" />
    <link href="assets/css/client.css" rel="stylesheet" />
    <link href="assets/css/responsive.css" rel="stylesheet" />
    <style>
        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 4rem;
            padding: 4rem 5%;
            max-width: 1200px;
            margin: 0 auto;
        }

        @media (max-width: 768px) {
            .contact-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }

        .contact-info {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }

        .contact-info h2 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-family: 'Geist', sans-serif;
            color: var(--on-surface);
        }

        .contact-info p {
            color: var(--on-surface-variant);
            line-height: 1.6;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }

        .info-item .material-symbols-outlined {
            color: var(--primary);
            font-size: 1.75rem;
            margin-top: 2px;
        }

        .info-item div {
            display: flex;
            flex-direction: column;
        }

        .info-item strong {
            font-size: 1.1rem;
            margin-bottom: 0.25rem;
            color: var(--on-surface);
        }

        .info-item span {
            color: var(--on-surface-variant);
            line-height: 1.5;
        }

        .contact-form {
            background: var(--surface-container-low);
            padding: 3rem;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }

        .form-group label {
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--on-surface);
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--outline-variant);
            border-radius: 6px;
            font-family: inherit;
            font-size: 1rem;
            background: var(--surface);
            color: var(--on-surface);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(230, 57, 70, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 150px;
        }

        .btn-submit {
            background: var(--primary);
            color: var(--on-primary);
            border: none;
            padding: 1rem 2rem;
            font-size: 1rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 6px;
            cursor: pointer;
            transition: background 0.3s, transform 0.2s;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background: var(--primary-container);
            transform: translateY(-2px);
        }

        .btn-submit:active {
            transform: translateY(0);
        }
        .map-container {
            max-width: 1200px;
            margin: 0 auto 4rem auto;
            padding: 0 5%;
        }

        .map-iframe {
            width: 100%;
            height: 400px;
            border: 0;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
    </style>
</head>

<body>

    <?php include 'view/client/layout/_navbar.php'; ?>

    <main>
        <!-- Page Header -->
        <div class="page-header">
            <div class="page-header__inner">
                <h1 class="page-header__title">LIÊN HỆ VỚI CHÚNG TÔI</h1>
                <p class="page-header__desc">Hãy để lại lời nhắn, đội ngũ LENS & LIGHT sẽ hỗ trợ bạn trong thời gian sớm
                    nhất.</p>
            </div>
        </div>

        <!-- Contact Section -->
        <div class="contact-container">

            <!-- Thông tin liên hệ -->
            <div class="contact-info">
                <h2>Kết nối ngay</h2>
                <p>Chúng tôi luôn sẵn sàng lắng nghe và giải đáp mọi thắc mắc của bạn về thiết bị nhiếp ảnh, dịch vụ bảo
                    hành hay hợp tác kinh doanh.</p>

                <div class="info-item">
                    <span class="material-symbols-outlined">location_on</span>
                    <div>
                        <strong>Địa chỉ Showroom</strong>
                        <span>624 Âu Cơ, Bảy Hiền, Hồ Chí Minh, Việt Nam</span>
                    </div>
                </div>

                <div class="info-item">
                    <span class="material-symbols-outlined">call</span>
                    <div>
                        <strong>Điện thoại liên hệ</strong>
                        <span>Hotline: 1900 1234<br>CSKH: 0909 123 456</span>
                    </div>
                </div>

                <div class="info-item">
                    <span class="material-symbols-outlined">mail</span>
                    <div>
                        <strong>Email hỗ trợ</strong>
                        <span>support@lenslight.com<br>contact@lenslight.com</span>
                    </div>
                </div>

                <div class="info-item">
                    <span class="material-symbols-outlined">schedule</span>
                    <div>
                        <strong>Giờ mở cửa</strong>
                        <span>Thứ 2 - Thứ 7: 8:30 - 21:00<br>Chủ nhật: 9:00 - 18:00</span>
                    </div>
                </div>
            </div>

            <!-- Form liên hệ -->
            <form class="contact-form" id="contactForm">
                <div class="form-group">
                    <label for="fullname">HỌ TÊN</label>
                    <input type="text" id="fullname" placeholder="Nhập họ và tên của bạn" required>
                </div>

                <div class="form-group">
                    <label for="email">EMAIL</label>
                    <input type="email" id="email" placeholder="Nhập địa chỉ email" required>
                </div>

                <div class="form-group">
                    <label for="phone">SỐ ĐIỆN THOẠI</label>
                    <input type="tel" id="phone" placeholder="Nhập số điện thoại" required>
                </div>

                <div class="form-group">
                    <label for="message">NỘI DUNG LỜI NHẮN</label>
                    <textarea id="message" placeholder="Bạn cần LENS & LIGHT hỗ trợ về vấn đề gì?" required></textarea>
                </div>

                <button type="submit" class="btn-submit">GỬI LỜI NHẮN</button>
            </form>
        </div>

        <!-- Bản đồ Google Maps -->
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps?q=624+Au+Co,+Ho+Chi+Minh&output=embed" 
                class="map-iframe"
                allowfullscreen="" 
                loading="lazy" 
                referrerpolicy="no-referrer-when-downgrade">
            </iframe>
        </div>
    </main>

    <?php include 'view/client/layout/_footer_dark.php'; ?>

    <script>
        document.getElementById('contactForm').addEventListener('submit', function (e) {
            e.preventDefault();
            alert('Cảm ơn bạn đã liên hệ! Lời nhắn của bạn đã được ghi nhận. LENS & LIGHT sẽ phản hồi lại trong thời gian sớm nhất qua Email hoặc SĐT cung cấp.');
            this.reset();
        });
    </script>
    <script src="assets/js/auth.js?v=2.0"></script>
</body>

</html>