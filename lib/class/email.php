<?php
// 
// Easy mailer class
// Sends email to multiple recipients in plain or HTML (or both)
// 
// (c) Steve H 2008 :: GPL v3
//
// Use email::plain() for plaintext
// Use email::html() for HTML
// 
// Don't forget to send the email with email::send()
// 
class class_email {
    var $to = false;
    var $from = false;
    var $subject = '(no subject)';
    var $plain = '';
    var $html = '';
    
    
    function to() {$this->to = func_get_args();}
    function from($address) {$this->from = $from;}
    function subject($subject) {$this->subject = $subject;}
    function plain($plain) {$this->plain = $plain;}
    function html($html) {$this->html = $html;}
    
    
    function send() {
        if (!$this->to) return trigger_error('No recipients set');
        if (!$this->from) $this->from = constant('mailfrom');
        
        $headers = 'From: '.str_replace(array("\n","\r"),'',$this->from);
        if ($this->html) {
            $headers .= "\n".'MIME-Version: 1.0';
            if ($this->plain) {
                // Mixed message
                $boundary = '==Multipart_Boundary_x'.md5(time().rand(9999,99999999)).'x'; 
                $headers .= "\n".'Content-type: multipart/alternative;';
                $headers .= "\n".'             boundary="'.$boundary.'"';
                // Info
                $message = 'This is a multi-part message in MIME format. Please view the attached plaintext file if your email client does not support this correctly.';
                // Plaintext
                $message .= "\n\n".'--'.$boundary;
                $message .= "\n".'Content-Type: text/plain; charset="iso-8859-1"'."\n".'Content-Transfer-Encoding: 7bit';
                $message .= "\n\n".$this->plain;
                // HTML
                $message .= "\n\n".'--'.$boundary;
                $message .= "\n".'Content-Type: text/html; charset="iso-8859-1"'."\n".'Content-Transfer-Encoding: 7bit';
                $message .= "\n\n".$this->html;
                // End
                $message .= "\n\n".'--'.$boundary.'--';
            } else {
                // HTML message
                $headers .= "\n".'Content-type: text/html; charset=iso-8859-1';
                $message = $this->html;
            }
        } elseif ($this->plain) {
            // Plaintext
            $message = $this->plain;
        } else return trigger_error('No email body set');
        
        // Send
        foreach ($this->to as $to) {
            if (!mail(str_replace(array("\n","\r"),'',$to),$this->subject,$message,$headers)) {
                trigger_error('Email failed - unknown cause');
            }
        }
    }
}
?>