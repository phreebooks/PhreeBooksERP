<?php
/*
 * PhreeBooks 5 - Customized Mail Methods
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-02-18
 * @filesource /portal/mail.php
 *
 */
namespace bizuno;

class portalMail
{
    function __construct() { }

    public function sendMail()
    {
        error_reporting(E_ALL & ~E_NOTICE); // This is to eliminate errors from undefined constants in phpmailer
        require_once(BIZUNO_ROOT."apps/PHPMailer/class.phpmailer.php");
        $mail = new \PHPMailer(true);
        try {
            $mail->CharSet = defined('CHARSET') ? CHARSET : 'utf-8'; // default "iso-8859-1";
            $mail->isHTML(true); // set email format to HTML
            $mail->SetLanguage(substr(getUserCache('profile', 'language', false, 'en_US'), 0, 2), BIZUNO_ROOT."apps/PHPMailer/language/");
            if (!$mail->ValidateAddress($this->FromEmail)) { return msgAdd(sprintf(lang('error_invalid_email'), $this->FromEmail)); }
            $mail->setFrom($this->FromEmail, $this->FromName);
            $mail->addReplyTo($this->FromEmail, $this->FromName);
            $mail->Subject = $this->Subject;
            $mail->Body    = '<html><body>'.$this->Body.'</body></html>';
            // clean message for text only mail recipients
            $textOnly = str_replace(['<br />','<br/>','<BR />','<BR/>','<br>','<BR>'], "\n", $this->Body);
            $mail->AltBody =  strip_tags($textOnly);
            foreach ($this->toEmail as $addr) {
                if (!$mail->ValidateAddress($addr['email'])) { return msgAdd(sprintf(lang('error_invalid_email'), "{$addr['name']} <{$addr['email']}>")); }
                $mail->AddAddress($addr['email'], $addr['name']);
            }
            foreach ($this->toCC as $addr) {
                if (!$mail->ValidateAddress($addr['email'])) { return msgAdd(sprintf(lang('error_invalid_email'), "{$addr['name']} <{$addr['email']}>")); }
                $mail->addCC($addr['email'], $addr['name']);
            }
            foreach ($this->attach as $file) { $mail->AddAttachment($file['path'], $file['name']); }
            $smtp = $this->setTransport();
            if (!empty($smtp['smtp_enable'])) {
                require_once(BIZUNO_ROOT."apps/PHPMailer/class.smtp.php");
                $mail->isSMTP();
                $mail->SMTPAuth = true;
                $mail->Host = $smtp['smtp_host'];
                $mail->Port = $smtp['smtp_port'];
                if ($smtp['smtp_port'] == 587) { $mail->SMTPSecure = 'tls'; }
                $mail->Username = $smtp['smtp_user'];
                $mail->Password = $smtp['smtp_pass'];
            }
            $mail->send();
        } catch (phpmailerException $e) {
            msgAdd(sprintf("Email send failed to: $this->ToName"));
            msgAdd($e->errorMessage());
            return false;
        } catch (Exception $e) {
            msgAdd(sprintf("Email send failed to: $this->ToName"));
            msgAdd($e->getMessage());
            return false;
        }
        return true;
    }

    /**
     * Tests to retrieve the sending email address transport preferences
     * @param obj $mail - phpMailer object
     */
    private function setTransport()
    {
        $smtp  = getModuleCache('bizuno', 'settings', 'mail');
        $settings = dbGetValue(BIZUNO_DB_PREFIX.'users', 'settings', "email='{$this->FromEmail}'");
        if (!empty($settings)) {
            $settings = json_decode($settings, true);
            if (!empty($settings['profile']['smtp_enable'])) {
                $smtp = [
                    'smtp_enable'=> $settings['profile']['smtp_enable'],
                    'smtp_host'  => $settings['profile']['smtp_host'],
                    'smtp_port'  => $settings['profile']['smtp_port'],
                    'smtp_user'  => $settings['profile']['smtp_user'],
                    'smtp_pass'  => $settings['profile']['smtp_pass']];
            }
        }
        return $smtp;
    }
}