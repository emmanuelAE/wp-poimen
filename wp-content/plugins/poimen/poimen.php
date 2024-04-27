<?php
/*
Plugin Name: poimen
Plugin URI: http://example.com
Description: poimen
Version: 0.2.3.2
Author: poimen
Author URI: http://example.com
License: GPL2
*/
date_default_timezone_set('Europe/Paris');

define('POIMEN_PLUGIN_DIR', plugin_dir_path(__FILE__));


function __createProdConstants(){
    // CRON Constant
    define ('FILL_FREQUENCY', 14) ;
    define('FILL_NUMBER', 'days');
    define('CRON_NAME', 'poimen_cron');
    define('CRON_NUMBER', 1);
    define('CRON_FREQUENCY', DAY_IN_SECONDS);
    define('DEADLINE', '-'.FILL_FREQUENCY.FILL_NUMBER);

    define('FIRST_REMINDER', '-'.(FILL_FREQUENCY-3).FILL_NUMBER);
    define('FIRST_REMINDER_MESSAGE',"Bonjour, \nVous, n'avez pas rempli le formulaire pour certaines de vos âmes.\nIl vous reste 3 jours avant le rapport de non soumission.\nMerci et Bonne journée.");
    define('SECOND_REMINDER', '-'.(FILL_FREQUENCY-1).FILL_NUMBER);
    define('SECOND_REMINDER_MESSAGE',"Bonjour, \nVous, n'avez pas rempli le formulaire pour certaines de vos âmes.\nIl vous reste 1 jour avant le rapport de non soumission.\nMerci et Bonne journée.");

    // Email Constant
    define(
        'ADMIN_EMAIL', array('mouangaa2000@yahoo.fr','ferdinandensa@gmail.com', 'samiaemilielizeph@gmail.com',
        'jujusafouesse@gmail.com', 'beniehana3@gmail.com')
    );
    define('EMAIL_SUBJECT', 'Rapport de non soumission') ;
    define(
        'ADMIN_EMAIL_BODY', "Ces différents leaders accompagnateurs n'ont pas renseigné le formulaire pour les âmes suivantes :\n "
    ) ;
    define('LEADER_EMAIL_BODY',"Bonjour,\nVous n'avez pas rempli le formulaire pour les âmes suivantes : \n\n");

    // Formulaires constant
    define('SUIVI_PAGE_NAME', 'suivi-des-ames') ; 
    define('SUIVI_FORM_ID', '123');
    define('SUIVI_SOUL_FIELD_ID', 1);
    define('SUIVI_DROPDOWN_FIELD_NAME', 'wpforms-'.SUIVI_FORM_ID.'-field_'.SUIVI_SOUL_FIELD_ID);

    define('GESTION_PAGE_NAME', 'gestion-des-ames') ; 
    define('GESTION_FORM_ID', '126');
    define('GESTION_LEADER_FIELD_ID',1);
    define('GESTION_DROPDOWN_FIELD_NAME', 'wpforms-'.GESTION_FORM_ID.'-field_'.GESTION_LEADER_FIELD_ID);
    define('GESTION_SOUL_FIELD_ID',2);
    define('GESTION_ACTION_FIELD_ID',3);
    define('GESTION_COMMENT_FIELD_ID',4);

    define('VOIR_SOULS_PAGE_NAME', 'gestion-leader-ames') ;
}
// function __createDevConstants(){
//     // CRON Constant
//     define ('FILL_FREQUENCY', 50) ;
//     define('FILL_NUMBER', 'minutes');
//     define('CRON_NAME', 'poimen_cron');
//     define('CRON_NUMBER', 5);
//     define('CRON_FREQUENCY', MINUTE_IN_SECONDS);
//     define('DEADLINE', '-'.FILL_FREQUENCY.FILL_NUMBER);

//     define('FIRST_REMINDER', '-'.(FILL_FREQUENCY-4).FILL_NUMBER);
//     define('FIRST_REMINDER_MESSAGE',"Bonjour, \nVous n'avez pas rempli le formulaire pour certaines de vos âmes.\nIl vous reste 3 jours avant le rapport de non soumission.\n.");
//     define('SECOND_REMINDER', '-'.(FILL_FREQUENCY-2).FILL_NUMBER);
//     define('SECOND_REMINDER_MESSAGE',"Bonjour, \nVous n'avez pas rempli le formulaire pour certaines de vos âmes.\nIl vous reste 1 jour avant le rapport de non soumission.\n.");

//     // Email Constant
//     define(
//         'ADMIN_EMAIL', array('emmanuelabo@icloud.com')
//     );
//     define('EMAIL_SUBJECT', 'Rapport de non soumission') ;
//     define(
//         'ADMIN_EMAIL_BODY', "Ces différents leaders accompagnateurs n'ont pas renseigné le formulaire pour les âmes suivantes :\n "
//     ) ;
//  define('LEADER_EMAIL_BODY',"Bonjour,\nVous n'avez pas rempli le formulaire pour les âmes suivantes : \n\n");

//     // Formulaires constant
//     define('SUIVI_PAGE_NAME', 'suivi-des-ames') ; 
//     define('SUIVI_FORM_ID', '70');
//     define('SUIVI_SOUL_FIELD_ID', 3);
//     define('SUIVI_DROPDOWN_FIELD_NAME', 'wpforms-'.SUIVI_FORM_ID.'-field_'.SUIVI_SOUL_FIELD_ID);

//     define('GESTION_PAGE_NAME', 'gestion-des-ames') ; 
//     define('GESTION_FORM_ID', '84');
//     define('GESTION_LEADER_FIELD_ID',3);
//     define('GESTION_DROPDOWN_FIELD_NAME', 'wpforms-'.GESTION_FORM_ID.'-field_'.GESTION_LEADER_FIELD_ID);
//     define('GESTION_SOUL_FIELD_ID',4);
//     define('GESTION_ACTION_FIELD_ID',5);
//     define('GESTION_COMMENT_FIELD_ID',2);

//     define('VOIR_SOULS_PAGE_NAME', 'leader-ames') ;
//     // define('VOIR_SOULS_FORM_ID', '100');
//     // define('VOIR_SOULS_LEADER_FIELD_ID', 1);
//     // define('VOIR_SOULS_LEADER_DROPDOWN_FIELD_NAME', 'wpforms-'.VOIR_SOULS_FORM_ID.'-field_'.VOIR_SOULS_LEADER_FIELD_ID);
//     // define('VOIR_SOULS_SOUL_FIELD_ID', 2);
//     // define('VOIR_SOULS_SOUL_DROPDOWN_FIELD_NAME', 'wpforms-'.VOIR_SOULS_FORM_ID.'-field_'.VOIR_SOULS_SOUL_FIELD_ID);
// }
// javascript constant
define('JS_FOLDER', plugin_dir_url(__FILE__) . 'src/assets/javascript/');

// Frequence de remplissage du formulaire en jours
require plugin_dir_path(__FILE__). 'vendor/autoload.php';

// __createDevConstants() ;
 __createProdConstants() ;

use IccGrenoble\Poimen\PoimenObject ;
use IccGrenoble\Poimen\WPFormObject;


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$poimenController = new PoimenObject(__FILE__);
$wpformController = new WPFormObject();

// error_log('POIMEN : REMINDERS'.FIRST_REMINDER .'-'.SECOND_REMINDER ) ; 

