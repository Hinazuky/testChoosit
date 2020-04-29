# testChoosit

## Installation de l'application 

Pour commencer, il vous faudras cloner le projet dans un dossier.  
Une fois le projet cloné, ouvrir un terminal, se rendre dans le dossier local et lancer la commande 'composer update'.  
Se rendre sur https://symfony.com/download et télécharger l'installateur correspondant à votre système. 

## création de la base de données

Lancer les commandes suivantes :   
php bin/console doctrine:database:create  
php bin/console doctrine:migrations:migrate  
php bin/console doctrine:fixture:load  

## chargement des assets 
Lancer la commande :   
php bin/console assets:install  

## lancement du projet
Maintenant il vous suffit de lancer la commande symfony server:start

## test unitaire et fonctionnel 
il vous suffit de lancer la commande : php bin/phpunit  
le projet va installer certaines choses puis lancer les tests que vous pouvez retrouver dans le dossier test.

## utilisation de l'interface

Il vous suffit de vous rendre sur http://localhost:8000/, une liste apparaîtras, vous pouvez cliquer sur chaque item de cette list ce qui vous conduiras sur une vu personnel à chaque item, sur cette page vous pourrez ajouter une quantité voulut à votre pannier.
Vous pouvez voir le nombre d'articles différents dans votre pannier sur chaque page avec un lien pour vous rendre sur la page du panier où vous pourrez le gerer.

## Export CSV
Il vous est possible de réaliser un export csv des entrées de la base de données en utilisant la commande suivante :  
php bin/console app:csv  
le répertoire dans le quel se trouveras votre fichier seras affiché après création  

## Point API
Il est possible de récupérer tous les produit au format json en allant sur l'url http://localhost:8000/api/products 