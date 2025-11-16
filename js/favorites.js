// Gestion des favoris en AJAX
document.addEventListener('DOMContentLoaded', function() {
    const surPageFavoris = document.querySelector('[data-page="favorites"]') !== null;

    document.querySelectorAll('.favoriteForm').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nomCocktail = this.querySelector('input[name="cocktail"]').value;
            const boutonCoeur = this.querySelector('.heart-btn');
            const carte = this.closest('.cocktailCard');
            
            // Appel AJAX
            fetch('./controllers/toggle_favorite_ajax.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'cocktail=' + encodeURIComponent(nomCocktail)
            })
            .then(res => res.json())
            .then(data => {
                // Change le cœur
                if (data.isFavorite) {
                    boutonCoeur.innerHTML = "<i class='fas fa-heart' style='color:#e74c3c;'></i>";
                } else {
                    boutonCoeur.innerHTML = "<i class='far fa-heart' style='color:#95a5a6;'></i>";
                    
                    // Supprime la carte SI on est sur la page favoris
                    if (surPageFavoris) {
                        carte.style.opacity = '0';
                        setTimeout(() => {
                            carte.remove();
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