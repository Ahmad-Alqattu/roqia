// assets/js/main.js

document.addEventListener('DOMContentLoaded', () => {
    // Example: Fetch cart count from server or local storage
    updateCartCount();

    // Add event listener for search functionality
    const searchBtn = document.getElementById('searchBtn');
    searchBtn.addEventListener('click', () => {
        const query = document.getElementById('searchInput').value.trim();
        if (query !== '') {
            window.location.href = `search_results.php?query=${encodeURIComponent(query)}`;
        }
    });
});


// assets/js/main.js



function addToCart(productId) {
    fetch('add_to_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `product_id=${productId}&quantity=1`
    })
    .then(response => response.json())
    .then(data => {
        alert(data.message);
    })
    .catch(error => console.error('Error:', error));
}



// assets/js/main.js

function changeImage(newSrc) {
    document.getElementById('currentImage').src = newSrc;
}


// assets/js/main.js

function updateCart(cartItemId, newQuantity) {
    if(newQuantity < 1) {
        alert('Quantity must be at least 1');
        return;
    }

    fetch(`cart/update_cart.php?cart_item_id=${cartItemId}&quantity=${newQuantity}`, {
        method: 'GET'
    })
    .then(response => response.json())
    .then(data => {
        if(data.success) {
            location.reload(); // Refresh the page to update totals
        } else {
            alert('Failed to update cart.');
        }
    })
    .catch(error => console.error('Error:', error));
}

function removeFromCart(cartItemId) {
    if(confirm('Are you sure you want to remove this item from your cart?')) {
        fetch(`cart/remove_from_cart.php?cart_item_id=${cartItemId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Refresh the page to update cart
            } else {
                alert('Failed to remove item from cart.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}


// assets/js/main.js

function removeFromWishlist(wishlistId) {
    if(confirm('Are you sure you want to remove this item from your favorites?')) {
        fetch(`wishlist/remove_from_wishlist.php?wishlist_id=${wishlistId}`, {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                location.reload(); // Refresh the page to update favorites
            } else {
                alert('Failed to remove item from favorites.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

