<?php

declare(strict_types=1);

namespace app\models;

use yii\base\Model;
use yii\mail\MailerInterface;

/**
 * ContactForm is the model behind the contact form.
 */
class ContactForm extends Model
{
    /**
     * User's name.
     *
     * @var string
     */
    public string $name = '';

    /**
     * User's email address.
     *
     * @var string
     */
    public string $email = '';

    /**
     * Email subject.
     *
     * @var string
     */
    public string $subject = '';

    /**
     * Email body.
     *
     * @var string
     */
    public string $body = '';

    /**
     * CAPTCHA verification code.
     *
     * @var string
     */
    public string $verifyCode = '';

    /**
     * Returns the validation rules.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            // name, email, subject and body are required
            [['name', 'email', 'subject', 'body'], 'required'],
            // email has to be a valid email address
            ['email', 'email'],
            // verifyCode needs to be entered correctly
            ['verifyCode', 'captcha'],
        ];
    }

    /**
     * Returns customized attribute labels.
     *
     * @return array
     */
    public function attributeLabels(): array
    {
        return [
            'verifyCode' => 'Verification Code',
        ];
    }

    /**
     * Sends an email to the specified email address using the information
     * collected by this model.
     *
     * @param MailerInterface $mailer The mailer component.
     * @param string $email The target email address.
     * @param string $senderEmail The sender email address.
     * @param string $senderName The sender name.
     *
     * @return bool Whether the model passes validation and the email is sent.
     */
    public function contact(
        MailerInterface $mailer,
        string $email,
        string $senderEmail,
        string $senderName
    ): bool {
        if ($this->validate()) {
            $mailer->compose()
                ->setTo($email)
                ->setFrom([$senderEmail => $senderName])
                ->setReplyTo([$this->email => $this->name])
                ->setSubject($this->subject)
                ->setTextBody($this->body)
                ->send();

            return true;
        }

        return false;
    }
}
