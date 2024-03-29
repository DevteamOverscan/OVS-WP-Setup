# OVS-WP-Setup

## Installation
A l'intérieur de votre projet Wordpress créer un dossier "mu-plugins" à l'emplacelement suivant : "/wp-content"

Une fois le dossier créé, aller dans le dossier "mu-plugins" et cloner le repo à l'intérieur.

[! Attention]
Certain dossier sont supprimer à la fin du processus lancer par le plugin pour des raison de sécurité. Si vous souhaitez avoir tous les dossiers nécessaires au bon fonctionnement du plugin cloner bine le repo de puis la brache main. Les autres branches correspondent aux projets Wordpress sur lequel est présent ce plugin.

## Étapes

1. Accédez au répertoire cloné :
`cd OVS-WP-Setup`

2. Créer une nouvelle branche
Utilisez la commande `git checkout -b nom-de-ma-nouvelle-branche` pour créer une nouvelle branche à partir de votre branche actuelle. Le nom de votre nouvelle branche doit correspondre au nom du projet Wordpress sur lequel est mis en place le plugin.

3. Déconnecter la branche de la branche principale
Utilisez la commande git push avec l'option -u pour définir l'upstream de votre branche.

`git push -u origin ma-branche`

Cela créera votre branche sur le dépôt distant, sans qu'elle ne soit liée à la branche principale. Vous pouvez continuer à travailler sur cette branche sans impacter la branche principale.
