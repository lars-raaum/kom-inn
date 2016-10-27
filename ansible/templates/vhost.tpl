<VirtualHost *:80>
    ServerAdmin webmaster@localhost
    ServerName {{ server_name }}
    ServerAlias {{ server_alias }}
    DocumentRoot {{ doc_root }}

    ErrorLog ${APACHE_LOG_DIR}/{{ server_name }}.error.log
    CustomLog ${APACHE_LOG_DIR}/{{ server_name }}.access.log combined

    <Directory {{ doc_root }}>
        AllowOverride All
{% if basic_auth_file is defined %}
        AuthType Basic
        AuthName "Restricted Area - Kom Inn"
        AuthBasicProvider file
        AuthUserFile {{ basic_auth_file }}
        Require valid-user
{% else %}
        Require all granted
{% endif %}
    </Directory>
</VirtualHost>
