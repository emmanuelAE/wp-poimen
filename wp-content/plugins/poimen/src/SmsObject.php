<?php
namespace IccGrenoble\Poimen ; 
require_once 'HTTP/Request2.php';

class SmsObject { 

    public function __construct() {
        $this->request = new HTTP_Request2();
        $this->request->setUrl('https://dk4jyr.api.infobip.com/sms/2/text/advanced');
        $this->request->setMethod(HTTP_Request2::METHOD_POST);
        $this->request->setConfig(array(
            'follow_redirects' => TRUE
        ));
        $this->request->setHeader(array(
            'Authorization' => 'App ********************************-********-****-****-****-********c876',
            'Content-Type' => 'application/json',
            'Accept' => 'application/json'
        ));
    }

    public function sendSMS(string $number, string $message){
        $body = '{"messages":[{"destinations":[{"to":'.$number.
            '}],"from":"ServiceSMS","text":'
            .$message.'}]}';
        $this->request->setBody($body) ;
        try {
            $response = $this->request->send();
            if ($response->getStatus() == 200) {
            error_log("SMS sent");
            }
            else {
                error_log("An error occured when sending sms") ;
                // echo 'Unexpected HTTP status: ' . $response->getStatus() . ' ' .
                // $response->getReasonPhrase();
            }
        }
        catch(HTTP_Request2_Exception $e) {
            echo 'Error: ' . $e->getMessage();
        }
    }

}