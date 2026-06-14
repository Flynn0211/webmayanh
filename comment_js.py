import os
import re

js_dir = 'assets/js/'
for filename in os.listdir(js_dir):
    if filename.endswith('.js'):
        path = os.path.join(js_dir, filename)
        with open(path, 'r', encoding='utf-8') as f:
            content = f.read()

        # Replace common English comments with Vietnamese
        replacements = [
            (r'// ====== DATA ======', r'// ====== DỮ LIỆU TẠM (MOCK DATA) ======'),
            (r'// ====== UTILS ======', r'// ====== HÀM TIỆN ÍCH (UTILITIES) ======'),
            (r'// ====== RENDER ======', r'// ====== RENDER GIAO DIỆN ======'),
            (r'// ====== EVENTS ======', r'// ====== XỬ LÝ SỰ KIỆN ======'),
            (r'// ── Helpers ──────────────────────────────────────────────', r'// ── Hàm Hỗ Trợ (Helpers) ──────────────────────────────'),
            (r'// ── Render ────────────────────────────────────────────────', r'// ── Render Giao Diện ────────────────────────────────'),
            (r'// ── Pagination Variables ──────────────────────────────────', r'// ── Khai báo Biến Phân Trang ──────────────────────────'),
            (r'// ── Events ────────────────────────────────────────────────', r'// ── Lắng nghe Sự kiện ───────────────────────────────'),
            (r'// File:', r'// Tệp tin:'),
        ]

        for eng, vie in replacements:
            content = re.sub(eng, vie, content, flags=re.IGNORECASE)

        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)

print("Updated JS section comments to Vietnamese")
