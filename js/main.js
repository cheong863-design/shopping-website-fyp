

document.addEventListener('DOMContentLoaded', function() {

    const qtyButtons = document.querySelectorAll('.qty-btn');
    if (qtyButtons.length > 0) {
        qtyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const actionType = this.getAttribute('data-action');

                fetch(`cart-actions.php?action=update_qty&id=${id}&type=${actionType}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(data => {
                        if (data.trim() === "success") {
                            location.reload();
                        } else {
                            console.error("Update payload mismatch, forcing reload to ensure data integrity.");
                            location.reload();
                        }
                    })
                    .catch(error => console.error('Error updating quantity:', error));
            });
        });
    }

    const searchInput = document.getElementById('nav-core-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function() {
            showSuggestions(this.value);
        });

        searchInput.addEventListener('blur', function() {
            hideSuggestions();
        });
    }
});

function showSuggestions(str) {
    const box = document.getElementById('suggestion-box');
    if (!box) return;

    if (str.length === 0) {
        box.style.display = "none";
        return;
    }

    fetch(`fetch_suggestions.php?q=${encodeURIComponent(str)}`)
        .then(response => response.text())
        .then(data => {
            if (data.trim() !== "") {
                box.innerHTML = data;
                box.style.display = "block";
            } else {
                box.style.display = "none";
            }
        })
        .catch(error => console.error('Error fetching suggestions:', error));
}

function selectSuggestion(val) {
    const searchInput = document.getElementById('nav-core-input');
    const searchForm = document.getElementById('nav-search-form');

    if (searchInput && searchForm) {
        searchInput.value = val;
        searchForm.submit();
    }
}

function hideSuggestions() {
    const box = document.getElementById('suggestion-box');
    if (box) {
        setTimeout(() => {
            box.style.display = "none";
        }, 250);
    }
}
