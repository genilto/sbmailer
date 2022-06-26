<?php

require_once ( __DIR__ . "/vendor/autoload.php");

use MailerSend\MailerSend;
use MailerSend\Helpers\Builder\Recipient;
use MailerSend\Helpers\Builder\Attachment;
use MailerSend\Helpers\Builder\EmailParams;
use MailerSend\Exceptions\MailerSendValidationException;

class SBMailersendAdapter implements iSBMailerAdapter {

    private $mailersend;
    private $email;
    
    private $recipients = [];
    private $cc = [];
    private $bcc = [];
    private $attachments = [];
    
    /**
     * Create a mailersend Adapter
     *
     * @param string $apiKey
     */
    public function __construct ($apiKey) {
        $this->mailersend = new MailerSend(['api_key' => $apiKey]);
        $this->email = new EmailParams();
    }
    public function getMailerName () {
        return 'Mailersend';
    }
    public function setFrom($address, $name = '') {
        $this->email->setFrom($address)
                    ->setFromName($name);
    }
    public function addReplyTo($address, $name = '') {
        $this->email->setReplyTo($address)
                    ->setReplyToName($name);
        return true;
    }
    public function addAddress ($address, $name = '') {
        $this->recipients[] = new Recipient($address, $name);
        return true;
    }
    public function addCC($address, $name = '') {
        $this->cc[] = new Recipient($address, $name);
        return true;
    }
    public function addBcc($address, $name = '') {
        $this->bcc[] = new Recipient($address, $name);
        return true;
    }
    public function addAttachment($path, $name = '') {
        $contents = SBMailerUtils::getFileContents($path);
        if ('' === $name) {
            $name = (string) SBMailerUtils::mb_pathinfo($path, PATHINFO_BASENAME);
        }
        $this->attachments[] = new Attachment($contents, $name);
        return true;
    }
    public function setSubject($subject) {
        $this->email->setSubject( $subject );
    }
    public function setHtmlBody($body) {
        $this->email->setHtml($body);
    }
    public function setTextBody($body) {
        $this->email->setText($body);
    }
    public function setTag($tagName) {
        $this->email->setTags([$tagName]);
    }
    private function adjustRecipients () {
        if (count($this->recipients) > 0) {
            $this->email->setRecipients($this->recipients);
        }
        if (count($this->cc) > 0) {
            $this->email->setCc($this->cc);
        }
        if (count($this->bcc) > 0) {
            $this->email->setBcc($this->bcc);
        }
    }
    public function adjustBody () {
        if (empty($this->email->getText())) {
            $this->email->setText($this->email->getHtml());
        }
    }
    public function adjustAttachments () {
        if (count($this->attachments) > 0) {
            $this->email->setAttachments($this->attachments);
        }
    }
    public function send () {
        $this->adjustRecipients();
        $this->adjustBody();
        $this->adjustAttachments();

        try {
            $this->mailersend->email->send($this->email);
            return true;
        } catch (MailerSendValidationException $e) {
            $response = $e->getResponse();

            $errorMessage = "Status Code returned by Mailersend: " . 
                $response->getStatusCode();

            if (!empty($response->getReasonPhrase())) {
                $errorMessage .= " - " . $response->getReasonPhrase();
            }

            if (!empty($response->getBody())) {
                $data = json_decode($response->getBody());
                
                if (isset($data->message)) {
                    $errorMessage .= " - Message: " . $data->message;
                }

                if (isset($data->errors)) {
                    foreach($data->errors as $field => $errors) {
                        $errorMessage .= " Detail: (" . $field . ") ";
                        if (is_array($errors)) {
                            foreach($errors as $index => $error) {
                                $errorMessage .= " " . $error;
                            }
                        }
                    }
                }
            }
            throw new Exception($errorMessage);
        }
        return false;
    }
}