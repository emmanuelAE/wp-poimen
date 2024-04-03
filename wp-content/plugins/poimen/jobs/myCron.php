<?php

// Chargement de WordPress
define('WP_USE_THEMES', false);

require('../../../wp-load.php') ; 

// Désactivation de la sortie HTTP
define('DISABLE_WP_CRON', true);

// Exécuter les tâches programmées WordPress
wp_cron();
