# OVS-WP-Setup

## Installation
A l'intérieur de votre projet Wordpress créer un dossier "mu-plugins" à l'emplacelement suivant : "/wp-content"

Une fois le dossier créé, aller dans le dossier "mu-plugins" et cloner le repo à l'intérieur.

[! Attention]
Certain dossier sont supprimer à la fin du processus lancer par le plugin pour des raison de sécurité. Si vous souhaitez avoir tous les dossiers nécessaires au bon fonctionnement du plugin cloner bine le repo de puis la brache main. Les autres branches correspondent aux projets Wordpress sur lequel est présent ce plugin.

## Étapes

Accédez au répertoire cloné :
bash
Copy code
cd OVS-WP-Setup
1. Créer une nouvelle branche
Utilisez la commande git checkout -b pour créer une nouvelle branche à partir de votre branche actuelle.

bash
Copy code
git checkout -b ma-branche
2. Déconnecter la branche de la branche principale
Travaillez localement sur votre nouvelle branche sans avoir besoin de la relier à la branche principale. Vous pouvez commencer à effectuer vos modifications sans impacter la branche principale.

3. Pousser la nouvelle branche
Une fois que vous avez terminé vos modifications et que vous souhaitez les pousser vers le dépôt distant, utilisez la commande git push avec l'option -u pour définir l'upstream de votre branche.

bash
Copy code
git push -u origin ma-branche
Cela créera votre branche sur le dépôt distant, sans qu'elle ne soit liée à la branche principale. Vous pouvez continuer à travailler sur cette branche sans impacter la branche principale.
