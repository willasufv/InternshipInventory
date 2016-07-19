<?php

namespace Intern\Email;

/**
 * Allows for the simple sending of email messages. Follows the general flow:
 *
 * sendSpecialMessage() -> sendTemplateMessage() -> sendEmail() -> logEmail()
 *
 * A message can be processed at any point in this flow, depending on the
 * desired function of the email.
 */
abstract class Email {

  /**
   * Template method for specialized email messages. Subclasses will
   * call this method and implement their own setUpSpecial() hook to meet
   * their specialized needs.
   *
   * @param  $i
   * @param  $agency
   * @param  $note     Necessary for class RegistrationIssue.
   */
  protected final function sendSpecialMessage(Internship $i,
    Agency $agency = null, $note = null) {

    $settings = InternSettings::getInstance();

    $tpl = array();
    $tpl['NAME'] = $i->getFullName();
    $tpl['BANNER'] = $i->getBannerId();
    $tpl['USER'] = $i->getEmailAddress();
    $tpl['PHONE'] = $i->getPhoneNumber();
    $tpl['TERM'] = Term::rawToRead($i->getTerm(), false);

    $outputs = self::setUpSpecial();

    self::sendTemplateMessage($outputs['to'], $outputs['subject'], $outputs['doc'], $tpl);
  }

  /**
   * Hook for the template method sendSpecialMessage(). Allows Email subclasses
   * to provide additional information to sendTemplateMessage() for their
   * specialized purpose.
   */
  protected absract function setUpSpecial();

  public static function sendTemplateMessage($to,
  $subject, $tpl, $tags, $cc = null){
    $settings = InternSettings::getInstance();

    $content = \PHPWS_Template::process($tags, 'intern', $tpl);

    self::sendEmail($to, $settings->getEmailFromAddress(), $subject, $content, $cc);
  }

  /**
   * Performs the email delivery process.
   *
   * @param  $to
   * @param  $from
   * @param  $subject
   * @param  $content
   * @param  $cc
   * @param  $bcc
   * @return True if successful.
   */
  public static function sendEmail($to, $from,
  $subject, $content, $cc = NULL, $bcc = NULL){
    $settings = InternSettings::getInstance();

    // Sanity checking
    if(!isset($to) || is_null($to)){
        return false;
    }

    if(!isset($from) || is_null($from)){
        $from = $settings->getSystemName() . ' <' . $settings->getEmailFromAddress() .'>';
    }

    if(!isset($subject) || is_null($subject)){
        return false;
    }

    if(!isset($content) || is_nulL($content)){
        return false;
    }

    // Create a Mail object and set it up
    \PHPWS_Core::initCoreClass('Mail.php');
    $message = new \PHPWS_Mail;

    $message->addSendTo($to);
    $message->setFrom($from);
    $message->setSubject($subject);
    $message->setMessageBody($content);

    if(isset($cc)){
        $message->addCarbonCopy($cc);
    }

    if(isset($bcc)){
        $message->addBlindCopy($bcc);
    }

    // Send the message
    if(EMAIL_TEST_FLAG){
        $result = true;
    }else{
        $result = $message->send();
    }

    if(\PHPWS_Error::logIfError($result)){
        return false;
    }

    self::logEmail($message);

    return true;
  }

  /**
   * Stores the email in file email.log
   *
   * @param  $message
   */
  public static function logEmail($message){
    // Log the message to a text file
    $fd = fopen(PHPWS_SOURCE_DIR . 'logs/email.log',"a");

    fprintf($fd, "=======================\n");

    foreach($message->send_to as $recipient){
        fprintf($fd, "To: %s\n", $recipient);
    }

    if(isset($message->carbon_copy)){
        foreach($message->carbon_copy as $recipient){
            fprintf($fd, "Cc: %s\n", $recipient);
        }
    }

    if(isset($message->blind_copy)){
        foreach($message->blind_copy as $recipient){
            fprintf($fd, "Bcc: %s\n", $recipient);
        }
    }

    fprintf($fd, "From: %s\n", $message->from_address);
    fprintf($fd, "Subject: %s\n", $message->subject_line);
    fprintf($fd, "Content: \n");
    fprintf($fd, "%s\n\n", $message->message_body);

    fclose($fd);
  }
}