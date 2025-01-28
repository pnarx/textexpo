<?php 

namespace App\Email;

class SendApplicationMail {
    protected $subject;
    protected $template;
    protected $replyTo = "hasannnolur06@gmail.com";

    public function __construct($subject, $template) {
        $this->subject = $subject;
        $this->template = $template;
    }

    public function handle($data) {
        $content = $this->template;
        $content = str_replace('{{full_name}}', $data['full_name'], $content);

        return [
            'subject' => $this->subject,
            'html' => $content
        ];
    }
}