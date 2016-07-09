# WowFramework
A lightweight extendable and powerful PHP MVC framework. Includes only general needs and optimized for speed.

# Sample .htaccess file
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f

RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ index.php [QSA,L]

WoW Framework