<?php

namespace Mail\Mail;

use Zend\Mail\Message;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part;
use Zend\Mime\Mime;
use Zend\Mail\Transport\Smtp as SmtpTransport;
use Zend\Mail\Transport\SmtpOptions;

class MailService {

    protected $smtpOptions;
    protected $from;
    protected $enable;

    public function __construct($options) {
        if ($options['enable'] === true) {
            $this->smtpOptions = new SmtpOptions($options['smtp_options']);
            $this->from = array($options['mail_address'] => mb_convert_encoding($options['display_name'], 'gb2312', 'utf8'));
            $this->enable = true;
        } else
            $this->enable = false;
    }

    public function send($message) {
        if ($this->enable !== true)
            return;

        $mimeMessage = new MimeMessage();

        $messageText = new Part(mb_convert_encoding($message['body'], 'gb2312', 'utf8'));
        $messageText->type = 'text/html';
        $messageText->charset = 'gb2312';
        $parts = array($messageText);

        if (isset($message['attachments']))
            foreach ($message['attachments'] as $attachment) {
                $data = fopen($filename = "data/uploads/" . $attachment['id'], 'r');
                $messageAttachment = new Part($data);
                $messageAttachment->filename = mb_convert_encoding($attachment['name'], 'gb2312', 'utf8');
                $messageAttachment->encoding = Mime::ENCODING_BASE64;
                $messageAttachment->disposition = Mime::DISPOSITION_ATTACHMENT;
                array_push($parts, $messageAttachment);
            }

        $mimeMessage->setParts($parts);

        $mail = new Message();
        $mail->setSubject(mb_convert_encoding($message['subject'], 'gb2312', 'utf8'));
        $mail->setBody($mimeMessage);
        $mail->setFrom($this->from);
        $mail->setEncoding('gb2312');
        foreach ($message['to'] as $user)
            if ($user['email'] !== null)
                $mail->addTo($user['email'], mb_convert_encoding($user['name'], 'gb2312', 'utf8'));

        if (isset($message['cc']) && is_array($message['cc']))
            foreach ($message['cc'] as $user)
                if ($user['email'] !== null)
                    $mail->addCc($user['email'], mb_convert_encoding($user['name'], 'gb2312', 'utf8'));

        if (isset($message['bcc']) && is_array($message['bcc']))
            foreach ($message['bcc'] as $user)
                if ($user['email'] !== null)
                    $mail->addBcc($user['email'], mb_convert_encoding($user['name'], 'gb2312', 'utf8'));

        $transport = new SmtpTransport();
        $transport->setOptions($this->smtpOptions);
        $transport->send($mail);
    }

}
