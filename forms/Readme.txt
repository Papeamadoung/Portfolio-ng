Deux backends de formulaire

Le formulaire de la page dâ€™accueil utilise `/api/contact`, une fonction Vercel qui envoie les messages avec Resend.
Le fichier `/forms/contact.php` est conservÃ© comme backend PHP autonome pour un hÃ©bergement classique
(Apache, Nginx ou mutualisÃ© avec PHP). Il utilise PHPMailer et peut remplacer lâ€™endpoint Vercel dans ce cas.

## DÃ©ploiement Vercel

Dans Vercel > Project > Settings > Environment Variables, ajoutez :
- RESEND_API_KEY : votre clÃ© API Resend
- CONTACT_FROM_EMAIL : une adresse expÃ©ditrice vÃ©rifiÃ©e dans Resend
- CONTACT_TO_EMAIL : doumbiabecaye7@gmail.com

Pour les tests locaux, copiez .env.example sous le nom .env.local et complÃ©tez les valeurs.
Ne publiez jamais .env.local, une clÃ© API Resend ou forms/config.local.php.

## DÃ©ploiement PHP

Copiez `config.example.php` vers `config.local.php`, renseignez les identifiants SMTP,
puis utilisez `/forms/contact.php` comme action du formulaire. Le fichier `config.local.php`
est exclu de Git et ne doit jamais Ãªtre rendu public.

