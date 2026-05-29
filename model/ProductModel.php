<?php
class ProductModel {
    /**
     * Retrieve all active products joined with category and supplier info.
     * Mapped strictly to README.md database checking standards.
     */
    public static function getActiveProducts($conn) {
        if ($conn === false) {
            return [];
        }

        $sql = "SELECT 
                    hh.ma_hh AS id,
                    ncc.ten_ncc AS brand,
                    hh.ten_hang_hoa AS name,
                    hh.gia_hien_tai AS price,
                    hh.mo_ta AS description,
                    hh.thong_so_ky_thuat AS specs,
                    hh.anh AS image,
                    dm.slug AS category_slug
                FROM hang_hoa hh
                LEFT JOIN danh_muc dm ON hh.ma_dm = dm.ma_dm
                LEFT JOIN nha_cung_cap ncc ON hh.ma_ncc = ncc.ma_ncc
                WHERE hh.trang_thai = 'DangBan'";

        $res = $conn->query($sql);
        $products = [];
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $products[] = $row;
            }
        }
        return $products;
    }
}
?>

