<?php
namespace IccGrenoble\Poimen ; 
class WPFormObject {
    public function __construct() {
        error_log("WPFORMOBJECT : Creation de l'object WPFormObject");
        add_action('wpforms_process_entry_save', [$this, 'processEntries'], 10, 4);

        // Custom tags 
        add_filter('wpforms_smart_tags' , '__wpf_register_LA_email_smarttag', 10,1) ;
        add_filter('wpforms_smart_tag_process', '__LA_email_custom_tag', 10, 1) ;
    }
    
    public function verifyEntries($fields, $entry, $form_data, $entry_id) {
        if ($form_data['id'] == GESTION_FORM_ID) {
            self::__verifyGestionForm($fields, $entry, $form_data, $entry_id);
        } elseif ($form_data['id'] == SUIVI_FORM_ID) {
            self::__verifySuiviForm($fields, $entry, $form_data, $entry_id);
        }
    }

    public function __verifySuiviForm($fields, $entry, $form_data, $entry_id) {
    }
    
    public function __verifyGestionForm($fields, $entry, $form_data, $entry_id) {
    }

    public function processEntries($fields, $entry, $form_id, $form_data) {

        if ($form_id === GESTION_FORM_ID) {
            error_log('WPFORMOBJECT : Gestion form found') ;
            self::__processGestionForm($fields, $entry, $form_id, $form_data);
        } elseif ($form_id === SUIVI_FORM_ID) {
            self::__processSuiviForm($fields, $entry, $form_id, $form_data);
        }
        else{
            error_log('WPFORMOBJECT : No form found') ;
        }

    }

    public function __processSuiviForm($fields, $entry, $form_data, $entry_id) {
        
        $soulName = $fields[SUIVI_SOUL_FIELD_ID]['value'] ;
        $leaderID = get_current_user_id();
        self::__updateSoulSubmissionDate($leaderID, $soulName, $fields);
    }

    public function __processGestionForm($fields, $entry, $form_data, $entry_id) {
        $action = $fields[GESTION_ACTION_FIELD_ID]['value'];
        $leaderLogin = $fields[GESTION_LEADER_FIELD_ID]['value'];
        $leader = get_user_by('login', $leaderLogin);
        $leaderID = $leader->ID;
        $soulName = $fields[GESTION_SOUL_FIELD_ID]['value'];

        if ($action === 'Ajouter') {
            self::__associateLeaderToSoul($leaderID, $soulName);
        } elseif ($action === 'Retirer') {
            self::__removeSoulFromLeader($leaderID, $soulName);
        } 
        else {
            return ;
        }

        // Send email to leader
        $leaderEmail = $leader->user_email;
        $subject = "Une nouvelle action de votre administrateur";
        $message = "L'âme ". $soulName . ' vient de vous être ' . $action . 
        '.Voici quelques commentaires de votre administrateur : ' . $fields[GESTION_COMMENT_FIELD_ID]['value'];
        //sleep(2) ; 
        //self::sendEmail(array($leaderEmail), $subject, $message);

    }

    public function sendEmail(array $recipient, string $subject, string $message) {
         $r = wp_mail($recipient, $subject, $message) ;

    }

    public function __associateLeaderToSoul(int $leaderID, string $soulName) {
        $leaderSouls = get_user_meta($leaderID, 'associated_clients', true) ;
        $leaderSouls = is_array($leaderSouls) ? $leaderSouls : array() ;
    
        $nextIndex = count($leaderSouls) + 1;

        while(isset($leaderSouls[$nextIndex])) {
            $nextIndex = $nextIndex + 1;
        }
    
        $leaderSouls[$nextIndex] = array(
            'name' => $soulName,
            'last_submitted_date' => time(),
            'reminder_level' => 0 // Ajout de cet attribut pour gerer les rappels. Cela sert à inviter de spamer
                                                    // les L.A avec des rappels des mêmes âmes à chaque email
        );
        update_user_meta($leaderID, 'associated_clients', $leaderSouls);
    }
    
    public function __removeSoulFromLeader(int $leaderID, string $soulName) {
        $leaderSouls = get_user_meta($leaderID, 'associated_clients', true);
        $leaderSouls = array_reverse($leaderSouls, true); //Reverse the array to remove the oldest soul
        error_log('WPFORMOBJECT : Nb of leader : ' . print_r(count($leaderSouls),true));
        if (empty($leaderSouls)) {
            return ;
        }
        foreach ($leaderSouls as $soulID => $soul) {
            error_log('WPFORMOBJECT : Soul name : ' . print_r($soul['name'],true) . ' : ' . print_r($soulName,true)) ;
            error_log('WPFORMOBJECT : Soul : ' . print_r($soul,true)) ;
            if (strtolower($soul['name']) === strtolower($soulName)) {
                unset($leaderSouls[$soulID]);
                update_user_meta($leaderID, 'associated_clients', $leaderSouls);
                return ;
            }
        }
    }

    public function __updateSoulSubmissionDate(int $leaderID, string $soulName, array $form_fields = array()) {
        $lastSubmittedFormInfo = array();
        $leaderSouls = get_user_meta($leaderID, 'associated_clients', true);
        if (empty($leaderSouls)) {
            return ;
        }
        foreach ($leaderSouls as $soulID => $soul) {
            if (strtolower($soul['name']) === strtolower($soulName)) {
                // Build form info to store in DB
                foreach ($form_fields as $field) {
                    $lastSubmittedFormInfo[$field['name']] = $field['value'];
                }
                $leaderSouls[$soulID]['last_submitted_date'] = time();
                $leaderSouls[$soulID]['reminder_level'] = 0; // Reset reminder level
                $leaderSouls[$soulID]['last_submitted_form_info'] = $lastSubmittedFormInfo;
                update_user_meta($leaderID, 'associated_clients', $leaderSouls);
                return ;
            }
        }
    }

    public function __LA_email_custom_tag($content, $tag){
        if ($tag === 'LA_email'){
            
            if (isset( $_POST['wpforms']['fields'][GESTION_LEADER_FIELD_ID] ) ) {
                $email = sanitize_email( $_POST['wpforms']['fields'][GESTION_LEADER_FIELD_ID] );
                // Replace the tag with the email.
                $content = str_replace( '{LA_email}', $email, $content );
            }
            else {
                $content = str_replace( '{LA_email}', '', $content ) ;
            }

        }
        return $content ;
    }

    public function __wpf_register_LA_email_smarttag($tags){
        $tags['LA_email'] = 'LA_email' ; 
        return $tags ;
    }

}