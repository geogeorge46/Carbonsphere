// Product page JavaScript functionality

// Toast notification system
function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    toast.textContent = message;
    toast.className = `toast ${type}`;
    toast.classList.add('show');

    setTimeout(() => {
        toast.classList.remove('show');
    }, 3000);
}

// Add to cart functionality
async function addToCart(productId, productName) {
    try {
        const quantity = document.getElementById('quantity') ?
            parseInt(document.getElementById('quantity').value) : 1;

        const formData = new FormData();
        formData.append('action', 'add');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        const response = await fetch('CartController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast(`${productName} added to cart!`);
            updateCartCount(result.cart_count);
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showToast('Failed to add item to cart. Please try again.', 'error');
    }
}

// Update cart count in navigation
function updateCartCount(count) {
    const cartCountElement = document.getElementById('cartCount');
    if (cartCountElement) {
        cartCountElement.textContent = count;
    }
}

// Live search functionality
let searchTimeout;
function liveSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        const searchTerm = document.querySelector('input[name="search"]').value;
        if (searchTerm.length >= 2) {
            performSearch(searchTerm);
        } else if (searchTerm.length === 0) {
            // Reload all products if search is cleared
            location.reload();
        }
    }, 500);
}

// Perform AJAX search
async function performSearch(searchTerm) {
    try {
        const response = await fetch(`products.php?search=${encodeURIComponent(searchTerm)}&ajax=1`);
        const html = await response.text();

        // Update products grid with search results
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newGrid = doc.querySelector('.products-grid');

        if (newGrid) {
            document.querySelector('.products-grid').innerHTML = newGrid.innerHTML;
        }
    } catch (error) {
        console.error('Search error:', error);
    }
}

// Filter products
async function applyFilters() {
    const category = document.querySelector('select[name="category"]').value;
    const minPrice = document.querySelector('input[name="min_price"]').value;
    const maxPrice = document.querySelector('input[name="max_price"]').value;
    const search = document.querySelector('input[name="search"]').value;

    try {
        let url = 'products.php?ajax=1';
        if (search) url += `&search=${encodeURIComponent(search)}`;
        if (category) url += `&category=${encodeURIComponent(category)}`;
        if (minPrice) url += `&min_price=${encodeURIComponent(minPrice)}`;
        if (maxPrice) url += `&max_price=${encodeURIComponent(maxPrice)}`;

        const response = await fetch(url);
        const html = await response.text();

        // Update products grid with filtered results
        const parser = new DOMParser();
        const doc = parser.parseFromString(html, 'text/html');
        const newGrid = doc.querySelector('.products-grid');

        if (newGrid) {
            document.querySelector('.products-grid').innerHTML = newGrid.innerHTML;
        }
    } catch (error) {
        console.error('Filter error:', error);
    }
}

// Clear all filters
function clearFilters() {
    document.querySelector('input[name="search"]').value = '';
    document.querySelector('select[name="category"]').value = '';
    document.querySelector('input[name="min_price"]').value = '';
    document.querySelector('input[name="max_price"]').value = '';

    location.href = 'products.php';
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Add event listeners for live search if on products page
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        searchInput.addEventListener('input', liveSearch);
    }

    // Add event listeners for filter changes
    const categorySelect = document.querySelector('select[name="category"]');
    const minPriceInput = document.querySelector('input[name="min_price"]');
    const maxPriceInput = document.querySelector('input[name="max_price"]');

    if (categorySelect) {
        categorySelect.addEventListener('change', applyFilters);
    }

    if (minPriceInput) {
        minPriceInput.addEventListener('input', applyFilters);
    }

    if (maxPriceInput) {
        maxPriceInput.addEventListener('input', applyFilters);
    }

    // Quantity input validation
    const quantityInputs = document.querySelectorAll('.quantity-input');
    quantityInputs.forEach(input => {
        input.addEventListener('input', function() {
            const value = parseInt(this.value);
            const max = parseInt(this.getAttribute('max')) || 999;

            if (value < 1) this.value = 1;
            if (value > max) this.value = max;
        });
    });
});

// Utility functions for cart operations
async function removeFromCart(cartItemId) {
    try {
        const formData = new FormData();
        formData.append('action', 'remove');
        formData.append('cart_item_id', cartItemId);

        const response = await fetch('CartController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast('Item removed from cart');
            updateCartCount(result.cart_count);
            // Reload cart items if on cart page
            if (typeof loadCartItems === 'function') {
                loadCartItems();
            }
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error removing from cart:', error);
        showToast('Failed to remove item from cart', 'error');
    }
}

async function updateCartQuantity(cartItemId, quantity) {
    try {
        const formData = new FormData();
        formData.append('action', 'update');
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', quantity);

        const response = await fetch('CartController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast('Cart updated successfully');
            updateCartCount(result.cart_count);
            // Reload cart items if on cart page
            if (typeof loadCartItems === 'function') {
                loadCartItems();
            }
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error updating cart:', error);
        showToast('Failed to update cart', 'error');
    }
}

async function clearCart() {
    if (!confirm('Are you sure you want to clear your entire cart?')) {
        return;
    }

    try {
        const formData = new FormData();
        formData.append('action', 'clear');

        const response = await fetch('CartController.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.success) {
            showToast('Cart cleared successfully');
            updateCartCount(0);
            // Reload cart items if on cart page
            if (typeof loadCartItems === 'function') {
                loadCartItems();
            }
        } else {
            showToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Error clearing cart:', error);
        showToast('Failed to clear cart', 'error');
    }
}

// Get cart items (for cart page)
async function getCartItems() {
    try {
        const response = await fetch('CartController.php?action=get');
        const result = await response.json();

        if (result.success) {
            return result;
        } else {
            console.error('Failed to get cart items:', result.message);
            return null;
        }
    } catch (error) {
        console.error('Error getting cart items:', error);
        return null;
    }
}

// Get cart count
async function getCartCount() {
    try {
        const response = await fetch('CartController.php?action=count');
        const result = await response.json();

        if (result.success) {
            updateCartCount(result.count);
            return result.count;
        }
    } catch (error) {
        console.error('Error getting cart count:', error);
    }
    return 0;
}