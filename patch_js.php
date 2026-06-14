<?php
$files = ['assets/js/mayanh.js', 'assets/js/ongkinh.js', 'assets/js/phukien.js', 'assets/js/trangchu.js'];

foreach ($files as $f) {
    $content = file_get_contents($f);
    
    if ($f !== 'assets/js/trangchu.js') {
        $replacement = <<<EOD
    // ── Pagination Variables ──────────────────────────────────
    let currentPage = 1;
    const itemsPerPage = 8;

    window.changePage = function(page) {
        currentPage = page;
        renderProducts(false);
        const grid = document.getElementById('productGrid');
        if (grid) {
            const headerOffset = 100;
            const elementPosition = grid.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({
                 top: offsetPosition,
                 behavior: "smooth"
            });
        }
    };

    // ── Render ────────────────────────────────────────────────
    function renderProducts(resetPage = true) {
        if (resetPage) currentPage = 1;
EOD;
        $content = preg_replace('/function renderProducts\(\) \{/', trim($replacement), $content);
        
        $map_start = strpos($content, 'productGrid.innerHTML = filtered.map(product => {');
        if ($map_start === false) continue;
        
        $map_end = strpos($content, ".join('');", $map_start) + strlen(".join('');");
        $mapping_code = str_replace('filtered.map', 'currentProducts.map', substr($content, $map_start, $map_end - $map_start));
        
        $new_render_logic = <<<EOD
        const existingPagination = document.getElementById('paginationControls');
        if (existingPagination) existingPagination.remove();

        if (filtered.length === 0) {
            noProductsMsg.classList.remove('hidden');
            productGrid.innerHTML = '';
            productGrid.appendChild(noProductsMsg);
            return;
        }

        noProductsMsg.classList.add('hidden');

        const totalPages = Math.ceil(filtered.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const currentProducts = filtered.slice(start, start + itemsPerPage);

        productGrid.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        productGrid.style.opacity = '0';
        productGrid.style.transform = 'translateY(10px)';

        setTimeout(() => {
            $mapping_code

            if (totalPages > 1) {
                let paginationHTML = '<div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; width: 100%; margin-top: 2rem; grid-column: 1 / -1;">';
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `<button onclick="window.changePage(\${i})" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid \${i === currentPage ? 'var(--primary)' : 'var(--border-color)'}; background: \${i === currentPage ? 'var(--primary)' : 'transparent'}; color: \${i === currentPage ? '#fff' : 'inherit'}; cursor: pointer; transition: 0.3s; border-radius: 4px; font-family: 'Geist', sans-serif;">\${i}</button>`;
                }
                paginationHTML += '</div>';
                productGrid.insertAdjacentHTML('beforeend', paginationHTML);
            }

            productGrid.style.opacity = '1';
            productGrid.style.transform = 'translateY(0)';
        }, 300);
EOD;
        
        $content = substr_replace($content, trim($new_render_logic), $map_start, $map_end - $map_start);
        $content = preg_replace('/if \(filtered\.length === 0\) \{[^{}]*\}[^{}]*noProductsMsg\.classList\.add\(\'hidden\'\);/', '', $content);
        
    } else {
        $replacement = <<<EOD
    const products    = liveProducts.filter(p => p.category === 'camera');
    
    let currentPage = 1;
    const itemsPerPage = 8;
    window.changePage = function(page) {
        currentPage = page;
        renderHomeProducts();
        const grid = document.getElementById('productGrid');
        if (grid) {
            const headerOffset = 100;
            const elementPosition = grid.getBoundingClientRect().top;
            const offsetPosition = elementPosition + window.pageYOffset - headerOffset;
            window.scrollTo({
                 top: offsetPosition,
                 behavior: "smooth"
            });
        }
    };
EOD;
        $content = str_replace("const products    = liveProducts.filter(p => p.category === 'camera');", $replacement, $content);
        
        $map_start = strpos($content, 'productGrid.innerHTML = products.map(product => {');
        if ($map_start !== false) {
            $map_end = strpos($content, ".join('');", $map_start) + strlen(".join('');");
            $mapping_code = str_replace('products.map', 'currentProducts.map', substr($content, $map_start, $map_end - $map_start));
            
            $new_render_logic = <<<EOD
    function renderHomeProducts() {
        const existingPagination = document.getElementById('paginationControls');
        if (existingPagination) existingPagination.remove();

        const totalPages = Math.ceil(products.length / itemsPerPage);
        const start = (currentPage - 1) * itemsPerPage;
        const currentProducts = products.slice(start, start + itemsPerPage);

        productGrid.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
        productGrid.style.opacity = '0';
        productGrid.style.transform = 'translateY(10px)';

        setTimeout(() => {
            $mapping_code

            if (totalPages > 1) {
                let paginationHTML = '<div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; width: 100%; margin-top: 2rem; grid-column: 1 / -1;">';
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `<button onclick="window.changePage(\${i})" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid \${i === currentPage ? 'var(--primary)' : 'var(--border-color)'}; background: \${i === currentPage ? 'var(--primary)' : 'transparent'}; color: \${i === currentPage ? '#fff' : 'inherit'}; cursor: pointer; transition: 0.3s; border-radius: 4px; font-family: 'Geist', sans-serif;">\${i}</button>`;
                }
                paginationHTML += '</div>';
                productGrid.insertAdjacentHTML('beforeend', paginationHTML);
            }

            productGrid.style.opacity = '1';
            productGrid.style.transform = 'translateY(0)';
        }, 300);
    }
    renderHomeProducts();
EOD;
            $content = substr_replace($content, trim($new_render_logic), $map_start, $map_end - $map_start);
            $content = str_replace("if (productGrid) {\n    function renderHomeProducts()", "if (productGrid) {\n        function renderHomeProducts()", $content);
        }
    }
    
    file_put_contents($f, $content);
}
echo "Done updating javascript files\n";
