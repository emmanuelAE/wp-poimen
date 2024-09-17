<?php
namespace IccGrenoble\Poimen ; 

class PoimenObject { 
    public function __construct(string $file) {
        // print to the navigation console
        error_log("POIMENOBJECT : Creation de l'object PoimenObject");
        // Cron
        add_filter( 'cron_schedules', [$this, 'customCron'] , 10, 1);
        register_activation_hook( $file, [$this, 'launchCron'] );
        register_deactivation_hook( $file, [$this, 'removeCron'] );
        add_action( 'myCustomCronHook', [$this, 'customCronHandler']); 

        // Dropdown
        add_action('wp_enqueue_scripts', [$this, 'enqueueDropdownScript']);

    }
    
    public function customCron($schedules) {
        $schedules[CRON_NAME] = array(
            'interval' => CRON_NUMBER * CRON_FREQUENCY,
            'display' => __('Once Every '. CRON_NAME)
        );
        return $schedules;
    }

    public function launchCron() {
        if ( ! wp_next_scheduled(CRON_NAME) ) {
            wp_schedule_event( time(), CRON_NAME, 'myCustomCronHook' );
        }
    }

    public function customCronHandler() {
        // affichage de l'heure 
        error_log('POIMENOBJECT : heure : '.date('Y-m-d H:i:s',time()));

        // Notify admins 
        $this->__notifyAdmin() ;
        // Notify the late leaders
        $this->__reminderLeader() ;
        // foreach ($lateLeader as $leader) {
        //     self::sendEmail($leader['email'], EMAIL_SUBJECT, self::__createEmailBody([$leader], false)) ;
        // }

        $this->__notify_all_late_leaders();

    }

    public function __notifyAdmin() {
        // Notify the admin of the late leaders
        
        $meta_key = 'notify_admin_last_run';
        $today = date('Y-m-d');
        $last_run = get_option($meta_key);
        
        if($last_run === $today){
            return;
        }

        $lateLeader = self::__lateLeader() ;
        $___lateLeader = print_r($lateLeader, true) ;
        // error_log('POIMENOBJECT : Late Leader : ' . $___lateLeader) ;
        if (empty($lateLeader)) {
            return ;
        }

        self::sendEmail(ADMIN_EMAIL, EMAIL_SUBJECT, self::__createEmailBody($lateLeader)) ;
       update_option($meta_key, $today); 
    } 
    
    public function __lateLeader(string $DEADLINE = DEADLINE) {
        $lateLeader = array();
        $leaders = get_users(array(
        'role__in' => array('subscriber', 'administrator'),
        ));
        foreach ($leaders as $leader) {
            $leaderSouls = get_user_meta($leader->ID, 'associated_clients', true);
            if (!empty($leaderSouls)) {
                $notSubmittedSoul = array(); // Initialise un tableau pour stocker les âmes non soumises
                $notSubmittedSoulDetails = array() ; // On stock plus de details. Rajouter pour stocker le niveau de rappel
                                                                                    // Sans détruire le fonctionnel passé.
                foreach ($leaderSouls as $soulID => $soul) {
                    $lastSubmissionTimestamp = isset($soul['last_submitted_date']) ? $soul['last_submitted_date'] : null;
                    if ($lastSubmissionTimestamp !== null && $lastSubmissionTimestamp <= strtotime($DEADLINE, time())) {
                        $notSubmittedSoul[] = $soul['name']; // Ajoute les âmes non soumises au tableau
                        $notSubmittedSoulDetails[] = array('name' => $soul['name'],
                                                                                         'reminder_level' => isset($soul['reminder_level']) ? $soul['reminder_level'] : null
                                                                                         ) ;
                    }
                }
                // Ajoute les informations sur le leader au tableau principal avec les âmes non soumises agrégées
                if (!empty($notSubmittedSoul)) {
                    $lateLeader[] = array(
                        'ID' => $leader->ID,
                        'name' => $leader->display_name,
                        'email' => $leader->user_email,
                        'notSubmittedSoul' => implode(',', $notSubmittedSoul), // Convertit le tableau en une chaîne séparée par des virgules
                        'notSubmittedSoulDetails' => $notSubmittedSoulDetails // Ajout des détails
                    );
                }
            }
        }
        return $lateLeader;
    }
    public function __notify_all_late_leaders() {

        $meta_key = '__notify_all_late_leaders_last_run';
        $today = date('Y-m-d');
        $last_run = get_option($meta_key);

        if ($last_run === $today){
            return;
        }
        // Récupère tous les leaders en retard
        $lateLeaders = self::__lateLeader(DEADLINE);
        
        if (!empty($lateLeaders)) {
            foreach ($lateLeaders as $leader) {
                // Créer un message personnalisé pour chaque leader
                $soulNames = array_column($leader['notSubmittedSoulDetails'], 'name');
                if (!empty($soulNames)) {
                    $message = DAILY_REMINDER . "Voici les âmes dont les rapports n'ont pas été soumis : " . implode(", ", $soulNames) . ".\r\nMerci et bonne journée.";
                    // Envoyer le message à l'email du leader
                    self::sendEmail([$leader['email']], 'Rappel de soumission', $message);
                }
            }
        }
        update_option($meta_key, $today);
    }


    public function __reminderLeader() {
        $firstReminderLateLeader = self::__lateLeader(FIRST_REMINDER);
        $secondReminderLateLeader = self::__lateLeader(SECOND_REMINDER);
    
        // Préparation des emails de rappel de niveau 1
        $firstReminderEmails = [];
        foreach ($firstReminderLateLeader as $leader) {
            $soulNames = array_column(array_filter($leader['notSubmittedSoulDetails'], function($soulDetails) {
                return $soulDetails['reminder_level'] === 0;
            }), 'name');
    
            if (!empty($soulNames)) {
                $firstReminderEmails[] = [
                    'leader' => $leader,
                    'email' => $leader['email'],
                    'soulNames' => $soulNames
                ];
    
                // Mettre à jour le niveau de rappel uniquement si l'email est envoyé
                $leaderSouls = get_user_meta($leader['ID'], 'associated_clients', true);
                foreach ($leaderSouls as &$soul) {
                    if (in_array($soul['name'], $soulNames)) {
                        $soul['reminder_level'] = 1;
                    }
                }
                update_user_meta($leader['ID'], 'associated_clients', $leaderSouls);
            }
        }
    
        // Préparation des emails de rappel de niveau 2
        $secondReminderEmails = [];
        foreach ($secondReminderLateLeader as $leader) {
            $soulNames = array_column(array_filter($leader['notSubmittedSoulDetails'], function($soulDetails) {
                return $soulDetails['reminder_level'] === 1;
            }), 'name');
    
            if (!empty($soulNames)) {
                $secondReminderEmails[] = [
                    'leader' => $leader,
                    'email' => $leader['email'],
                    'soulNames' => $soulNames
                ];
    
                // Mettre à jour le niveau de rappel uniquement si l'email est envoyé
                $leaderSouls = get_user_meta($leader['ID'], 'associated_clients', true);
                foreach ($leaderSouls as &$soul) {
                    if (in_array($soul['name'], $soulNames)) {
                        $soul['reminder_level'] = 2;
                    }
                }
                update_user_meta($leader['ID'], 'associated_clients', $leaderSouls);
            }
        }
    
        // Envoi des emails de rappel
        foreach ($firstReminderEmails as $reminder) {
            $message = FIRST_REMINDER_MESSAGE . "Voici les âmes dont les rapports n'ont pas été soumis : " . implode(',  ', $reminder['soulNames']) . ".\r\nMerci et bonne journée.";
            self::sendEmail([$reminder['email']], 'Rappel', $message);
        }
    
        foreach ($secondReminderEmails as $reminder) {
            $message = SECOND_REMINDER_MESSAGE . "Voici les âmes dont les rapports n'ont pas été soumis : " . implode(', ', $reminder['soulNames']) . ".\r\nMerci et bonne journée.";
            self::sendEmail([$reminder['email']], 'Rappel', $message);
        }
    
    
        
        // $lateLeader = self::__lateLeader(SECOND_REMINDER) ;
        // if (!empty($lateLeader)) {
        //     foreach ($lateLeader as $leader) {
        //         self::sendEmail([$leader['email']], 'Rappel', SECOND_REMINDER_MESSAGE) ;
        //     }
        //     return ;
        // }
        
        // $lateLeader = self::__lateLeader(FIRST_REMINDER) ;
        // if (!empty($lateLeader)) {
        //     foreach ($lateLeader as $leader) {
        //         self::sendEmail([$leader['email']], 'Rappel', FIRST_REMINDER_MESSAGE) ;
        //     }
        //     return ;
        // }
    }

    public function sendEmail(array $recipient, string $subject, string $message) {
        wp_mail($recipient, $subject, $message) ;
    }

    public function __createEmailBody($lateLeader, bool $admin = true) {
        $body = $admin ? ADMIN_EMAIL_BODY : LEADER_EMAIL_BODY;
        foreach ($lateLeader as $leader) {
            $body .= $leader['name'].'('.$leader['email'].')'. ' : ' . $leader['notSubmittedSoul'] . ".\n";
        }
        return $body ;
    }

    public function enqueueDropdownScript() {
        if (is_page(GESTION_PAGE_NAME)) {
            wp_enqueue_script('dropdown', JS_FOLDER.'dropdown.js', array('jquery'), '1.0', true);
            wp_localize_script('dropdown', 'datas', array('dropdownFieldName' => GESTION_DROPDOWN_FIELD_NAME, 
                    'dropdownOptionName' => self::__getUserLogin()));
        }
        elseif (is_page(SUIVI_PAGE_NAME)) {
            wp_enqueue_script('dropdown', JS_FOLDER.'dropdown.js' , array('jquery'), '1.0', true) ;
            wp_localize_script('dropdown', 'datas', array('dropdownFieldName' => SUIVI_DROPDOWN_FIELD_NAME, 
                    'dropdownOptionName' => self::__getSoulsNames(get_current_user_id())));
        }
        elseif (is_page(VOIR_SOULS_PAGE_NAME)) {
            wp_enqueue_script('dropdown', JS_FOLDER.'visualizeLaSoul.js' , array('jquery'), '1.0', true) ;
            wp_localize_script('dropdown', 'datas', array('dropdownOptionName' => self::__getUsersWithMetaKey(array('associated_clients'))));
        }
        else {
            return ;
        }

    }

    // Utilities functions
    public function __getUsersDisplayName() {
        $users = get_users(array('role_in' => array('subscriber', 'administrator')));
        $usersDisplayName = array();
        foreach ($users as $user) {
            $usersDisplayName[] = $user->display_name;
        }
        return $usersDisplayName;
    }

    public function __getUserLogin() {
        $users = get_users(array('role_in' => array('subscriber', 'administrator')));
        $usersLogin = array();
        foreach ($users as $user) {
            $usersLogin[] = $user->user_login;
        }
        return $usersLogin;
    }

    public function __getSoulsNames(int $leaderID) {
        $leaderSouls = get_user_meta($leaderID, 'associated_clients', true);
        $soulsNames = array();
        if (!empty($leaderSouls)) {
            foreach ($leaderSouls as $soul) {
                $soulsNames[] = $soul['name'];
            }
        }
        // error_log('POIMENOBJECT : Souls Names : ' . print_r($soulsNames, true));
        return $soulsNames;
    }   

    public function __getUsersWithMetaKey(array $metaKeys) {
        $users = get_users();
        error_log('POIMENOBJECT : Users : ' . print_r($users, true));
        if (count($users) > 0) {
            $users_with_metadata = array();
            foreach ($users as $user) {
                $user_meta = [];
                foreach ($metaKeys as $metaKey) {
                    $user_meta[$metaKey] = get_user_meta($user->ID, $metaKey, true);
                }
                $user_data = [
                    'ID' => $user->ID,
                    'user_login' => $user->user_login,
                    'display_name' => $user->display_name,
                    'user_meta' => $user_meta
                ];
                $users_with_metadata[] = $user_data;
            }
            return $users_with_metadata;
        } 
        else {
            return array(); 
        }
    }
    

    

    public function removeCron() {
        wp_clear_scheduled_hook('myCustomCronHook');
    }
    
}