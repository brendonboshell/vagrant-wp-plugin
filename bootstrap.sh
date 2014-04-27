#!/usr/bin/env bash

# Update
apt-get update

# Install MySQL
debconf-set-selections <<< "mysql-server-5.5 mysql-server/root_password password vagrant"
debconf-set-selections <<< "mysql-server-5.5 mysql-server/root_password_again password vagrant"
apt-get -y install mysql-server mysql-client
sleep 5
# set key_buffer to 614M
sed -i -e "/\[mysqld\]/,/\[.*\]/s/^key_buffer/#key_buffer/" /etc/mysql/my.cnf
sed -i -e "s/\(\[mysqld\]\)/\1\nkey_buffer = 614M/" /etc/mysql/my.cnf

# Install PHP + Apache
apt-get -y install php5 php5-mysql libapache2-mod-php5
apt-get -y install apache2

# Install Wordpress
rm -rf /var/www/*
cp -R "/vagrant/wordpress/wordpress-3.9/"* /var/www
mkdir /var/www/wp-content/plugins/wordpress-dev-assist
cp -R "/vagrant/wordpress-dev-assist/"* /var/www/wp-content/plugins/wordpress-dev-assist
cp /var/www/wp-config-sample.php /var/www/wp-config.php
chown -R www-data: /var/www
echo "CREATE DATABASE wordpress;" | mysql -u root -pvagrant
sed -i 's/database_name_here/wordpress/' /var/www/wp-config.php
sed -i 's/username_here/root/' /var/www/wp-config.php
sed -i 's/password_here/vagrant/' /var/www/wp-config.php
ln -s /plugin/ /var/www/wp-content/plugins/plugin
/usr/bin/php -r "
define('WP_SITEURL', 'http://localhost:8080');
include '/var/www/wp-admin/install.php';
wp_install('Blog Title', 'admin', 'admin@example.com', 1, '', 'vagrant');
" > /dev/null 2>&1
/usr/bin/php -r "
require_once('/var/www/wp-load.php');
require_once('/var/www/wp-admin/includes/admin.php');
\$plugins = get_plugins();
foreach (\$plugins as \$name => \$nu) {
activate_plugin('/var/www/wp-content/plugins/' . \$name);
}
" > /dev/null 2>&1

# restart services
/etc/init.d/mysql restart
/etc/init.d/apache2 restart