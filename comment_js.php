<?php
$js_dir = 'assets/js/';
$files = scandir($js_dir);

foreach ($files as $filename) {
    if (pathinfo($filename, PATHINFO_EXTENSION) === 'js') {
        $path = $js_dir . $filename;
        $content = file_get_contents($path);

        $replacements = [
            '/\\/\\/ ====== DATA ======/' => '// ====== DỮ LIỆU TẠM (MOCK DATA) ======',
            '/\\/\\/ ====== UTILS ======/' => '// ====== HÀM TIỆN ÍCH (UTILITIES) ======',
            '/\\/\\/ ====== RENDER ======/' => '// ====== RENDER GIAO DIỆN ======',
            '/\\/\\/ ====== EVENTS ======/' => '// ====== XỬ LÝ SỰ KIỆN ======',
            '/\\/\\/ ── Helpers ──────────────────────────────────────────────/' => '// ── Hàm Hỗ Trợ (Helpers) ──────────────────────────────',
            '/\\/\\/ ── Render ────────────────────────────────────────────────/' => '// ── Render Giao Diện ────────────────────────────────',
            '/\\/\\/ ── Pagination Variables ──────────────────────────────────/' => '// ── Khai báo Biến Phân Trang ──────────────────────────',
            '/\\/\\/ ── Events ────────────────────────────────────────────────/' => '// ── Lắng nghe Sự kiện ───────────────────────────────',
            '/\\/\\/ File:/' => '// Tệp tin:',
        ];

        $content = preg_replace(array_keys($replacements), array_values($replacements), $content);

        file_put_contents($path, $content);
    }
}
echo "Updated JS section comments to Vietnamese\n";
