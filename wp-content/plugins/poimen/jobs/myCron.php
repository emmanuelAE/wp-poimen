<?php
// Chemin vers le fichier wp-cron.php de votre installation WordPress
$wp_cron_path = './../wp-cron.php';

// Vérifier si le fichier wp-cron.php existe
if (file_exists($wp_cron_path)) {
    // Charger le fichier wp-cron.php
    require_once $wp_cron_path;

    // Exécuter les tâches planifiées WordPress
    wp_cron();
    
    // Réponse pour indiquer que les tâches ont été exécutées avec succès
    error_log('myCron.php : Les tâches planifiées ont été exécutées avec succès.');
} else {
    // Si le fichier wp-cron.php n'existe pas, afficher un message d'erreur
    error_log('myCron.php : Le fichier wp-cron.php n\'existe pas.');
}
?>
