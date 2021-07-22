# Pour travailler sur le Projet mobile-guy.com

###Guide d'installation ( . = Prérequis ; // = Conseillé ) :

- Avoir Wamp ou Mamp ou Lamp ( avec PHP 7.4.9 ou supérieur, MySQL 8.0 ou supérieur et phpMyAdmin 5.0 ou supérieur )
- Avoir un IDE	( // Avoir PhpStorm 2021.1.2  ou supérieur )
- Avoir un terminal de commande ( // Installer et ouvrir Git Bash dans le bon dossier )
- Télécharger composer-setup.exe ( https://getcomposer.org/download/ ) ; et vérifier sa présence avec un composer -v
- Saisir en Ligne de Commande :  php -r  "copy('https://getcomposer.org/installer', 'composer-setup.php');"  
- Saisir en Ligne de Commande : php composer-setup.php
- Saisir en Ligne de Commande : php -r  "unlink('composer-setup.php');"  
- Installer Symfony ( https://symfony.com/download ) ; et vérifier sa présence avec un symfony -v
- Cloner le Projet https://github.com/rmurail/mobile-guy/  en commençant par faire un Fork, puis [ Code ] et [ copier https ]
- Avec Git Bash, se placer à l'endroit où on veut placer le projet : $ git clone https://github.com/rmurail/mobile-guy/
- Saisir en Ligne de Commande : $ composer install ( pour build le projet avec tous les fichiers, comme vendor… )
- Ajouter une branche upstream ( // Avec PhpStorm, dans l'onglet Git, cliquer sur [ Manage Remote… ], puis sur la croix "+" )
- Remplir 'Define Remote' avec [ Name = upstream ] et [ URL: = https://github.com/rmurail/mobile-guy.git  ]  (! avec .git !)
- Dans phpMyAdmin, créer une nouvelle Base de Données nommée mobile-guy
- Importer la BDD nommée mobile-guy_{date et heure la plus récente }.sql
- Créer à la racine du projet, le fichier .env.local avec DATABASE_URL="mysql://root:@127.0.0.1:3306/mobile-guys?serverVersion=5.7"
- Lier le Projet à la BDD , en saisissant en Ligne de Commande : $ php bin/console doctrine:database:create
- Pour faire une migration vers la BDD, il faut d’abord créer le fichier : $ php bin/console make:migration
- Puis saisir en Ligne de Commande : $ php bin/console make:migrations:migrate
- Dans Git/Log/Local/ ajouter le fichier dev (le Chef de Projet est sur la branche Main et les Collaborateurs, sur la branche Dev)
- Pour lancer le serveur, saisir en Ligne de Commande : symfony server:start
- Ouvrir dans le navigateur le site avec https://127.0.0.1:8000/


###Guide de Contribution (Pour proposer ses propres modifications à l'équipe) ( . = Prérequis ; // = Conseillé ) :

- Se mettre à jour du dépôt principal ( // Dans PhpStorm, avec un [ Fetch All Remotes ]  { flèche bleue en bas à gauche } ) </br>
( // Dans PhpStorm :  Puis un [clic-droit] sur Git/Log/Remote/upstream/main et choisir [ Rebase Current onto Selected ] )
- Cocher les fichiers à envoyer ( // dans l'onglet à gauche Commit ) et Mettre un texte explicite des modifications apportées
- Cliquer sur [ Commit ], et Faire un Push, en cliquant sur la flèche verte  en haut à droite ,  puis cliquer sur [ Push ]
- Sur son propre Git Hub, à la bonne branche, cliquer sur [ Contribute ], puis [ Open pull request ] et [ Create pull request ]
