<?php 

namespace App\Email;

class ApprovalPreApplicationMail {
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
        $content = str_replace('{{company_name}}', $data['company_name'], $content);
        $content = str_replace('/{{link}}', 'https://koyweb.xyz/basvuru-tamamla/'.$data['token'], $content);

        return [
            'subject' => $this->subject,
            'html' => $content
        ];
    }
}