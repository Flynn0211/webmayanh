import re

files = ['assets/js/mayanh.js', 'assets/js/ongkinh.js', 'assets/js/phukien.js', 'assets/js/trangchu.js']

for f in files:
    with open(f, 'r', encoding='utf-8') as file:
        content = file.read()
    
    if f != 'assets/js/trangchu.js':
        replacement = """
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
"""
        # Replace function declaration
        content = re.sub(r'function renderProducts\(\) \{', replacement.strip(), content)
        
        # Replace innerHTML mapping with slice and pagination
        map_start = content.find('productGrid.innerHTML = filtered.map(product => {')
        if map_start == -1: continue
        
        # Find the end of the map function (where join('') is)
        map_end = content.find(".join('');", map_start) + len(".join('');")
        
        mapping_code = content[map_start:map_end].replace('filtered.map', 'currentProducts.map')
        
        new_render_logic = """
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
            """ + mapping_code + """

            if (totalPages > 1) {
                let paginationHTML = '<div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; width: 100%; margin-top: 2rem; grid-column: 1 / -1;">';
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `<button onclick="window.changePage(${i})" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid ${i === currentPage ? 'var(--primary)' : 'var(--border-color)'}; background: ${i === currentPage ? 'var(--primary)' : 'transparent'}; color: ${i === currentPage ? '#fff' : 'inherit'}; cursor: pointer; transition: 0.3s; border-radius: 4px; font-family: 'Geist', sans-serif;">${i}</button>`;
                }
                paginationHTML += '</div>';
                productGrid.insertAdjacentHTML('beforeend', paginationHTML);
            }

            productGrid.style.opacity = '1';
            productGrid.style.transform = 'translateY(0)';
        }, 300);
"""
        
        content = content[:map_start] + new_render_logic.strip() + content[map_end:]
        content = re.sub(r'if \(filtered\.length === 0\) \{[^{}]*\}[^{}]*noProductsMsg\.classList\.add\(\'hidden\'\);', '', content)
        
    else:
        # trangchu.js uses `const products = liveProducts.filter...` and `productGrid.innerHTML = products.map...`
        # We wrap the map in a render function
        content = content.replace("const products    = liveProducts.filter(p => p.category === 'camera');", """
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
""")
        
        map_start = content.find('productGrid.innerHTML = products.map(product => {')
        if map_start != -1:
            map_end = content.find(".join('');", map_start) + len(".join('');")
            mapping_code = content[map_start:map_end].replace('products.map', 'currentProducts.map')
            
            new_render_logic = """
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
            """ + mapping_code + """

            if (totalPages > 1) {
                let paginationHTML = '<div id="paginationControls" style="display: flex; justify-content: center; gap: 0.5rem; width: 100%; margin-top: 2rem; grid-column: 1 / -1;">';
                for (let i = 1; i <= totalPages; i++) {
                    paginationHTML += `<button onclick="window.changePage(${i})" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; border: 1px solid ${i === currentPage ? 'var(--primary)' : 'var(--border-color)'}; background: ${i === currentPage ? 'var(--primary)' : 'transparent'}; color: ${i === currentPage ? '#fff' : 'inherit'}; cursor: pointer; transition: 0.3s; border-radius: 4px; font-family: 'Geist', sans-serif;">${i}</button>`;
                }
                paginationHTML += '</div>';
                productGrid.insertAdjacentHTML('beforeend', paginationHTML);
            }

            productGrid.style.opacity = '1';
            productGrid.style.transform = 'translateY(0)';
        }, 300);
    }
    renderHomeProducts();
"""
            content = content[:map_start] + new_render_logic.strip() + content[map_end:]
            
            # Remove `if (productGrid) {` block that was wrapping it, but keep the closing brace handled.
            content = content.replace("if (productGrid) {\n    function renderHomeProducts()", "if (productGrid) {\n        function renderHomeProducts()")

    with open(f, 'w', encoding='utf-8') as file:
        file.write(content)

print("Done updating javascript files")
