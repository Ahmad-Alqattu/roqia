// assets/js/search.js

document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.trim();
    if(query.length > 2) {
        fetch(`search/live_search.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                // Display suggestions
                let suggestions = '<ul>';
                data.results.forEach(item => {
                    suggestions += `<li><a href="product_detail.php?id=${item.product_id}">${item.product_name}</a></li>`;
                });
                suggestions += '</ul>';
                document.getElementById('searchSuggestions').innerHTML = suggestions;
            })
            .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('searchSuggestions').innerHTML = '';
    }
});

// assets/js/main.js

document.getElementById('searchInput').addEventListener('input', function() {
    const query = this.value.trim();
    if(query.length > 2) {
        fetch(`search/live_search.php?query=${encodeURIComponent(query)}`)
            .then(response => response.json())
            .then(data => {
                if(data.results.length > 0) {
                    let suggestions = '<ul>';
                    data.results.forEach(item => {
                        suggestions += `<li><a href="product_detail.php?id=${item.product_id}">${item.product_name}</a></li>`;
                    });
                    suggestions += '</ul>';
                    const suggestionsBox = document.getElementById('searchSuggestions');
                    suggestionsBox.innerHTML = suggestions;
                    suggestionsBox.style.display = 'block';
                } else {
                    document.getElementById('searchSuggestions').innerHTML = '';
                    document.getElementById('searchSuggestions').style.display = 'none';
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('searchSuggestions').innerHTML = '';
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

// Hide suggestions when clicking outside
document.addEventListener('click', function(e) {
    if(!document.getElementById('searchSuggestions').contains(e.target) && e.target.id !== 'searchInput') {
        document.getElementById('searchSuggestions').style.display = 'none';
    }
});

