00 08 * * * deploy php /services/kom-inn/crons/stale_matches.php -v 2>&1 > /var/log/kom-inn/stale_matches.log
00 18 * * * deploy php /services/kom-inn/crons/update_people.php -v 2>&1 > /var/log/kom-inn/update_people.log
