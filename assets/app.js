import './stimulus_bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

// ================================================
// PANIER EN AJAX : ajouter / retirer sans recharger
// ================================================
document.addEventListener('submit', async (event) => {
    const form = event.target;

    // On ne s'occupe que des formulaires marqués "js-panier"
    if (!form.classList.contains('js-panier')) {
        return;
    }

    event.preventDefault(); // on empêche le rechargement de la page

    // 1. On envoie le formulaire au serveur, en cachette
    const reponse = await fetch(form.action, {
        method: 'POST',
        body: new FormData(form),
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
    });

    if (!reponse.ok) {
        alert('Une erreur est survenue, veuillez réessayer.');
        return;
    }

    // 2. On lit la réponse JSON du serveur
    const data = await reponse.json();

    // 3. On met à jour le compteur du menu
    document.getElementById('compteur-panier').textContent = data.nombre;

    // 4. Sur la page panier : on enlève la ligne et on met à jour le total
    const ligne = form.closest('tr');
    if (ligne) {
        ligne.remove();
        document.getElementById('total-panier').textContent = data.total + ' €';
        if (data.nombre === 0) {
            location.reload(); // pour afficher "Votre panier est vide"
        }
    }

    // 5. Petit message vert qui disparaît après 3 secondes
    afficherMessage(data.message);
});

function afficherMessage(texte) {
    const alerte = document.createElement('div');
    alerte.className = 'alert alert-success';
    alerte.textContent = texte;
    document.querySelector('main').prepend(alerte);
    setTimeout(() => alerte.remove(), 3000);
}