<?php

/*
Plugin Name: Wordpress Dev Assist
Plugin URI: http://www.brendonboshell.co.uk/
Description: Provides tools to help with Wordpress plugin development
Author: Brendon Boshell
Version: 1.0
Author URI: http://www.brendonboshell.co.uk/
*/

function bbpp_wda_init() {
    wp_register_script("bbpp_wda_scripts", plugins_url("scripts.js", __FILE__), array("jquery"));
    wp_enqueue_script("bbpp_wda_scripts");
    
    wp_register_style("bbpp_wda_styles", plugins_url("styles.css", __FILE__));
    wp_enqueue_style("bbpp_wda_styles");
    if (!function_exists("get_plugins")) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    
    //$plugins = get_plugins();
    //foreach ($plugins as $name => $nu) {
    //    //activate_plugin('/var/www/wp-content/plugins' . \\\$name);
    //    echo $name;
    //    activate_plugin($name);
    //}

}

function bbpp_wda_script_params() {
  if (wp_script_is("bbpp_wda_scripts", "done")) {
      $params = array(
        "wp_versions" => bbpp_wda_wp_versions()
      );
      
      echo "<script>\n";
      echo "var bbpp_wda_params = " . json_encode($params) . ";\n";
      echo "</script>\n";
  }
}

function bbpp_wda_wp_versions() {
    $versions = array();
    
    foreach (glob("/vagrant/wordpress/*") as $filename) {
        $versions[] = preg_replace("/^.*?wordpress\-/i", "", $filename);
    }
    
    return $versions;
}

function bbpp_wda_install($version) {
    if (!in_array($version, bbpp_wda_wp_versions())) {
        die("Version doesn't exist");
    }
    
    // Uninstall current version
    exec("rm -rf /var/www");
    exec("echo \"DROP DATABASE wordpress;\" | mysql -u root -pvagrant");
    
    // Install new version
    exec("cp -R " . escapeshellarg("/vagrant/wordpress/wordpress-{$version}/") . "* /var/www");
    exec("cp /var/www/wp-config-sample.php /var/www/wp-config.php");
    exec("mkdir /var/www/wp-content/plugins/wordpress-dev-assist");
    exec("cp -R \"/vagrant/wordpress-dev-assist/\"* /var/www/wp-content/plugins/wordpress-dev-assist");
    exec("chown -R www-data: /var/www");
    exec("echo \"CREATE DATABASE wordpress;\" | mysql -u root -pvagrant");
    exec("sed -i 's/database_name_here/wordpress/' /var/www/wp-config.php");
    exec("sed -i 's/username_here/root/' /var/www/wp-config.php");
    exec("sed -i 's/password_here/vagrant/' /var/www/wp-config.php");
    exec("ln -s /plugin/ /var/www/wp-content/plugins/plugin");
    exec("/usr/bin/php -r \"
define('WP_SITEURL', 'http://localhost:8080');
include '/var/www/wp-admin/install.php';
wp_install('Blog Title', 'admin', 'admin@example.com', 1, '', 'vagrant');
\" > /dev/null 2>&1");
    exec("/usr/bin/php -r \"    
require_once('/var/www/wp-load.php'); 
require_once('/var/www/wp-admin/includes/admin.php');
\\\$plugins = get_plugins();
foreach (\\\$plugins as \\\$name => \\\$nu) {
activate_plugin('/var/www/wp-content/plugins/' . \\\$name);
}
\" > /dev/null 2>&1");
    echo json_encode(array("done" => true));
    exit;
}

function bbpp_wda_login_scripts() {
    wp_enqueue_script("bbpp_wda_login_scripts", plugins_url("login-scripts.js", __FILE__), array("jquery"));
}

add_action("login_enqueue_scripts", "bbpp_wda_login_scripts");

add_action("init", "bbpp_wda_init");
add_action("wp_footer", "bbpp_wda_script_params");
add_action("admin_footer", "bbpp_wda_script_params");

if (isset($_GET["bbpp-wda-version"])) {
    bbpp_wda_install($_GET["bbpp-wda-version"]);
}