// Fetch products from backend API and display them
document.addEventListener('DOMContentLoaded', function() {
    const productContainer = document.querySelector('.lg\\:flex-row');
    
    if (!productContainer) return;
    
    // Create products section
    const productsSection = document.createElement('div');
    productsSection.className = 'w-full lg:w-1/2 p-6';
    productsSection.innerHTML = `
        <div class="bg-white dark:bg-[#161615] rounded-lg p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
            <h2 class="text-lg font-medium mb-4 text-[#1b1b18] dark:text-[#EDEDEC]">Products from API</h2>
            <div id="products-list" class="space-y-3">
                <p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">Loading products...</p>
            </div>
        </div>
    `;
    
    productContainer.appendChild(productsSection);
    
    // Fetch products from API
    const apiUrl = '/api/products';
    console.log('Fetching from:', apiUrl);
    
    fetch(apiUrl, {
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers.get('Content-Type'));
        return response.json();
    })
    .then(data => {
        console.log('API Response:', data);
        const listContainer = document.getElementById('products-list');
        
        if (!data || !data.data || data.data.length === 0) {
            listContainer.innerHTML = '<p class="text-[#706f6c] dark:text-[#A1A09A] text-sm">No products found in database.</p>';
            return;
        }
        
        listContainer.innerHTML = data.data.map(product => `
            <div class="flex justify-between items-center p-3 bg-[#FDFDFC] dark:bg-[#161615] border border-[#e3e3e0] dark:border-[#3E3E3A] rounded">
                <div>
                    <h3 class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">${product.name || 'Unnamed'}</h3>
                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">${product.description || 'No description'}</p>
                </div>
                <span class="font-medium text-[#f53003]">$${parseFloat(product.price || 0).toFixed(2)}</span>
            </div>
        `).join('');
    })
    .catch(error => {
        console.error('API fetch error:', error);
        const listContainer = document.getElementById('products-list');
        listContainer.innerHTML = `<p class="text-red-500 text-sm">Error loading products: ${error.message}<br><small class="text-[#706f6c]">Check browser console and network tab</small></p>`;
    });
});

