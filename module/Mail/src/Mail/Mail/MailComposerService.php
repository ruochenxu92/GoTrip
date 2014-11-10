<?php

namespace Mail\Mail;

/**
 * MailComposerService constructs different email messages
 * It does not deliver the mail (as oppose to MailService which only delivers the mail)
 * 
 */
class MailComposerService {

    const SEND_MAIL = -1;

    private $_message;
    private $_mail_config;

    public function __construct($config) {
        //this will set message array
        //$message['body']
        //$message['attachments']
        //$message['subject']
        //$message['to']:
        //          user['email']
        //          user['name']
        //$message['cc']:
        //          array of user[]
        //$message['bcc']
        //          array of user[]
        //first check the type of the message
        $this->_mail_config = $config;
    }

    public function createMessage($message_opts) {
        $type = $message_opts['type'];
        $message = array();
        switch ($type) {
            case MailComposerService::SEND_MAIL: //this is debug test
                $template = file_get_contents('./module/Mail/template/testemail_template.html');  //path start with www/xiyouji/
                $message['body'] = $template;
                $message['subject'] = 'test auto mail';
                $message['to'] = array(array('email'=> 'xxu46@illinois.edu', 'name'=>'Xu'),array('email'=> 'xxu@aumail.averett.edu', 'name'=>'Xu'));
                break;

                
            case MailComposerService::SEND_MAIL:
                $confirm_id = $message_opts['confirm_id'];
                $email = $message_opts['email'];
                $name = $message_opts['name'];
                $template = file_get_contents('./module/Mail/template/testemail_template.html');  //path start with www/xiyouji/
                $message['body'] = $template;
                $message['subject'] = '【西游迹】您已成功支付：确认号'.$confirm_id;
                $message['to'] = array(array('email'=> $email, 'name'=> $name),array('email'=> 'thomasxu46@gmail.com', 'name'=>'Xu'));
                break;
            
            default:
                break;
        }
        $this->_message = $message;
        return $this->_message;
    }

    public function getMessage() {
        return $this->_message;
    }

}
