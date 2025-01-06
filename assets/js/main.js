
document.addEventListener('DOMContentLoaded', () => {
    updateCartCount();

    const searchBtn = document.getElementById('searchBtn');
    searchBtn.addEventListener('click', () => {
        const query = document.getElementById('searchInput').value.trim();
        if (query !== '') {
            window.location.href = `search_results.php?query=${encodeURIComponent(query)}`;
        }
    });
});





function changeImage(newSrc) {
    document.getElementById('currentImage').src = newSrc;
}



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
            location.reload();
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
                location.reload(); 
            } else {
                alert('Failed to remove item from cart.');
            }
        })
        .catch(error => console.error('Error:', error));
    }
}



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
