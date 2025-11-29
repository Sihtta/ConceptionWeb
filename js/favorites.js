// Gestion des favoris en AJAX
document.addEventListener('DOMContentLoaded', function() {
    const onFavoritesPage = document.querySelector('[data-page="favorites"]') !== null;

    document.querySelectorAll('.favoriteForm').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const cocktailName = this.querySelector('input[name="cocktail"]').value;
            const heartButton = this.querySelector('.heart-btn');
            const card = this.closest('.cocktailCard');
            
            // Appel AJAX
            fetch('./controllers/toggle_favorite_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cocktail=' + encodeURIComponent(cocktailName)
            })
            .then(res => res.json())
            .then(data => {
                // Change le cœur
                if (data.isFavorite) {
                    heartButton.innerHTML = "<i class='fas fa-heart' style='color:#e74c3c;'></i>";
                } else {
                    heartButton.innerHTML = "<i class='far fa-heart' style='color:#95a5a6;'></i>";
                    
                    // Supprime la card SI on est sur la page favoris
                    if (onFavoritesPage) {
                        card.style.opacity = '0';
                        setTimeout(() => {
                            card.remove();
                            // Affiche "Aucune recette" si plus rien
                            if (document.querySelectorAll('.cocktailCard').length === 0) {
                                document.querySelector('.CocktailList').innerHTML = '<p>Aucune recette favorite.</p>';
                            }
                        }, 300);
                    }
                }
            })
            .catch(err => alert('Erreur : ' + err));
        });
    });
});