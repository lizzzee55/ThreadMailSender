# ThreadMailSender

crontab -e
* 3 * * * /usr/bin/php /var/www/sender/sender.php
* 3 * * * /usr/bin/php /var/www/sender/checker.php
