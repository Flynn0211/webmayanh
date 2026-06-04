<?php
/**
 * Lớp ProductModel xử lý các truy vấn liên quan đến hàng hóa (sản phẩm) từ cơ sở dữ liệu.
 */
class ProductModel {
    /**
     * Lấy toàn bộ danh sách sản phẩm đang hoạt động (trang_thai = 'DangBan')
     * Kết hợp (JOIN) với bảng danh mục, nhà cung cấp và thông tin khuyến mãi đang diễn ra.
     *
     * @param mysqli|false $conn Đối tượng kết nối CSDL
     * @return array Danh sách các sản phẩm kèm giá đã giảm (nếu có khuyến mãi)
     */
    public static function getActiveProducts($conn) {
        // Kiểm tra kết nối cơ sở dữ liệu
        if ($conn === false) {
            return [];
        }

        // Câu lệnh SQL truy vấn sản phẩm và tính toán giá bán cuối cùng sau khi áp dụng khuyến mãi
        $sql = "SELECT 
                    hh.ma_hh AS id,
                    ncc.ten_ncc AS brand,
                    hh.ten_hang_hoa AS name,
                    IFNULL(
                        CASE 
                            WHEN km.loai_giam_gia = 'PhanTram' THEN hh.gia_hien_tai * (1 - km.gia_tri_giam / 100)
                            WHEN km.loai_giam_gia = 'TienMat' THEN hh.gia_hien_tai - km.gia_tri_giam
                            ELSE hh.gia_hien_tai
                        END, hh.gia_hien_tai
                    ) AS price,
                    hh.gia_hien_tai AS original_price,
                    hh.mo_ta AS description,
                    hh.thong_so_ky_thuat AS specs,
                    hh.anh AS image,
                    hh.anh_phu AS additional_images,
                    dm.slug AS category_slug
                FROM hang_hoa hh
                LEFT JOIN danh_muc dm ON hh.ma_dm = dm.ma_dm
                LEFT JOIN nha_cung_cap ncc ON hh.ma_ncc = ncc.ma_ncc
                LEFT JOIN chi_tiet_khuyen_mai ctkm ON hh.ma_hh = ctkm.ma_hh
                LEFT JOIN (
                    SELECT ma_km, loai_giam_gia, gia_tri_giam 
                    FROM khuyen_mai 
                    WHERE trang_thai = 'HoatDong' AND NOW() BETWEEN ngay_bat_dau AND ngay_het_han
                ) km ON ctkm.ma_km = km.ma_km
                WHERE hh.trang_thai = 'DangBan'
                GROUP BY hh.ma_hh";

        $res = $conn->query($sql);
        $products = [];
        if ($res) {
            while ($row = $res->fetch()) {
                $products[] = $row;
            }
        }
        return $products;
    }
}

