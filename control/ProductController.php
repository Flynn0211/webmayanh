<?php
// Load product model layer
require_once __DIR__ . '/../model/ProductModel.php';

class ProductController {
    /**
     * Fetch active database products and clean/format them for frontend JSON consumption.
     */
    public static function getAllActiveProducts($conn) {
        $rawProducts = ProductModel::getActiveProducts($conn);
        $db_products = [];

        foreach ($rawProducts as $row) {
            $cat = ($row['category_slug'] === 'ong-kinh') ? 'lens' : 'camera';
            $price_formatted = number_format($row['price'], 0, '', ',') . ' ₫';

            $specs_raw = $row['specs'];
            $specs_formatted = '';
            $specs_arr = json_decode($specs_raw, true);
            if (is_array($specs_arr)) {
                $parts = [];
                foreach ($specs_arr as $key => $val) {
                    $parts[] = "$key: $val";
                }
                $specs_formatted = implode(', ', $parts);
            } else {
                $specs_formatted = $specs_raw;
            }

            $db_products[] = [
                'id' => (int)$row['id'],
                'brand' => $row['brand'],
                'name' => $row['name'],
                'price' => $price_formatted,
                'description' => $row['description'],
                'specs' => $specs_formatted,
                'image' => $row['image'],
                'category' => $cat
            ];
        }

        return $db_products;
    }
}
?>

