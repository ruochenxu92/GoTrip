<?php

namespace Mail\Mail;

/**
 * MailComposerService constructs different email messages
 * It does not deliver the mail (as oppose to MailService which only delivers the mail)
 * 
 */
class MailComposerService {

    const DEBUG_TEST = -1;

    const ORDER_REQUEST = 0; //we have received the order(customer processed to payment method)
    const PAYMENT_PENDING = 1; // we have received the payment but not checked yet
    const PAYMENT_SETTLE = 2;
    const PAYMENT_UNSETTLED_ERROR = 3;
    const CONFIRM_ORDER = 10;
    const CUSTOMER_PRODUCT_QUERY = 20;

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
            case MailComposerService::DEBUG_TEST: //this is debug test
                $template = file_get_contents('./module/Mail/template/testemail_template.html');  //path start with www/xiyouji/
                $message['body'] = $template;
////                $bodyStr = '<!DOCTYPE html>
//                    <html>
//                    <body>
//
//                    <h1>This is heading 1</h1>
//                    <h2>This is heading 2</h2>
//                    <h3>This is heading 3</h3>
//                    <h4>This is heading 4</h4>
//
//                    </body>
//                    </html>';
//                $message['body'] =$bodyStr;
                $message['subject'] = 'test auto mail';
                $message['to'] = array(array('email'=> 'xxu46@illinois.edu', 'name'=>'Xu'),array('email'=> 'xxu@aumail.averett.edu', 'name'=>'Xu'));
                break;


            case MailComposerService::CUSTOMER_PRODUCT_QUERY:
                $message_opts = $message_opts['content'];
                $message['subject'] = '用户提问：产品编号'.$message_opts['product_id'].'题目：'.$message_opts['title'];
                $bdstr =  '<!DOCTYPE html>'.
                                    '<html><body>'.
                                    '<h1>用户问题</h1>'.
                                    '<br>'.
                                    '<p>'.$message_opts['question'].'</p>'.
                                    '<h1>用户姓名</h1>'.
                                    '<br>'.
                                    '<p>'.$message_opts['name'].'</p>'.
                                    '<h1>用户邮箱</h1>'.
                                    '<br>'.
                                    '<p>'.$message_opts['email'].'</p>'.
                                    '</body></html>';
                $message['body'] = $bdstr;
                $message['to'] = array(array('email' => 'support@xiyouus.com', 'name' => 'Customer Support'));
                break;
                
                case MailComposerService::PAYMENT_PENDING:
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
