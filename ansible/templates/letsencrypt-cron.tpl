PATH=/root/bin:/root/.local/bin:/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/sbin:/bin:/snap/bin:/snap/bin

00 21 * * * root letsencrypt renew --verbose 2>&1 > /var/log/letsencrypt-renewal.log && systemctl reload apache2
