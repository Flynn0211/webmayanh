<?php
$files = ['assets/js/mayanh.js', 'assets/js/ongkinh.js', 'assets/js/phukien.js'];
foreach ($files as $f) {
    $content = file_get_contents($f);
    
    $search = "        const totalPages = Math.ceil(filtered.length / itemsPerPage);";
    $replace = <<<EOD
        if (filtered.length === 0) {
            noProductsMsg.classList.remove('hidden');
            productGrid.innerHTML = '';
            productGrid.appendChild(noProductsMsg);
            return;
        }
        noProductsMsg.classList.add('hidden');

        const totalPages = Math.ceil(filtered.length / itemsPerPage);
EOD;
    
    $content = str_replace($search, $replace, $content);
    file_put_contents($f, $content);
}
echo "Fixed empty state check\n";
