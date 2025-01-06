# Instructions pour la rédaction automatique des messages de commit avec Copilot

## Format général

[Type de modification](Scope facultatif): Titre du commit  
(fichiers_impactés)  
Description courte et concise

1. **Type de modification**

    - feat : ajout ou suppression d’une fonctionnalité
    - fix : correction d’un bug
    - refactor : réécriture / restructuration du code (sans changement de comportement)
    - perf : amélioration de performance
    - style : modifications n’affectant pas le sens du code (espaces, formatage, etc.)
    - test : ajout ou correction de tests
    - docs : modifications de la documentation uniquement
    - build : modifications liées à l’outil de build, pipeline CI, dépendances, etc.
    - ops : modifications liées à l’infrastructure, au déploiement, à la sauvegarde, etc.
    - chore : autres modifications diverses (p. ex. mise à jour du `.gitignore`)

2. **Scope (facultatif)**

    - Indique la partie du projet concernée (ex. frontend, backend, infra, etc.).
    - N’utilise pas de numéros de tickets comme scope.

3. **Titre du commit**

    - Bref, à la forme impérative, en français (ex. “Corrige la gestion des sessions”).

4. **(fichiers_impactés)**

    - Entre parenthèses, liste rapide des fichiers ou ressources modifiés (ex. `(ContactForm.vue, styles.css)`).

5. **Description courte et concise**
    - Une phrase ou deux maximum, en français, pour décrire brièvement la raison ou la nature de la modification.

---

## Exemples

**Commit avec scope `frontend`**  
fix(frontend): Corrige l'affichage du formulaire de contact  
(ContactForm.vue, styles.css)  
Corrige un bug où le formulaire ne s'affichait pas correctement sur mobile.

**Commit avec scope `backend`**  
refactor(backend): Simplifie la logique de validation des tokens  
(TokenService.js)  
Réorganise la vérification pour faciliter la maintenance et réduire la duplication de code.

**Commit sans scope**  
docs: Met à jour la documentation de l'API  
(README.md)  
Améliore la section des endpoints pour clarifier l'utilisation.

**Commit avec amélioration de performance**  
perf(frontend): Optimise le chargement des images  
(Gallery.vue, imageHelpers.js)  
Réduit la taille des images et améliore le lazy loading.

---

### Rappel rapide

Toujours rédiger en français.  
Utiliser la structure :  
[Type](Scope facultatif): Titre  
(Fichiers modifiés)  
Description courte

Types autorisés : feat, fix, refactor, perf, style, test, docs, build, ops, chore  
Scopes (optionnel) : frontend, backend, infra, CI, etc.

Exemple :  
fix(frontend): Corrige l'affichage du menu  
(HeaderMenu.vue, navbar.css)  
Empêche le menu de déborder sur les petits écrans.
