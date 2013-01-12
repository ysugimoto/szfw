<?php if ( ! defined('SZ_EXEC') ) exit('access_denied');

/**
 * ====================================================================
 * 
 * Seezoo-Framework
 * 
 * A simple MVC/action Framework on PHP 5.1.0 or newer
 * 
 * 
 * Email settings
 * 
 * @package  Seezoo-Framework
 * @category config
 * @author   Yoshiaki Sugimoto <neo.yoshiaki.sugimoto@gmail.com>
 * @license  MIT Licence
 * 
 * ====================================================================
 */

/*
 * --------------------------------------------------------------------
 * Mail sending method
 * 
 * you can choose "smtp" or "php"
 * --------------------------------------------------------------------
 */
$mail['type']      = 'smtp';

/*
 * --------------------------------------------------------------------
 * Mail-From
 * 
 * Dog mail library use this parameter at default
 * ( enable change in your script )
 * --------------------------------------------------------------------
 */
$mail['from']      = 'you@example.com';

/*
 * --------------------------------------------------------------------
 * Mail-From-Name
 * 
 * Dog mail library use this parameter at default
 * ( enable change in your script )
 * --------------------------------------------------------------------
 */
$mail['from_name'] = '';

/*
 * --------------------------------------------------------------------
 * SMTP setting
 * 
 * If you use SMTP mail sending,
 * please set these parameters.
 * --------------------------------------------------------------------
 */
$mail['smtp']['hostname']  = 'ssl://smtp.gmail.com';
$mail['smtp']['port']      = 465;
$mail['smtp']['crypto']    = FALSE;
$mail['smtp']['username']  = 'neo.yoshiaki.sugimoto@gmail.com';
$mail['smtp']['password']  = 'yoshiakisugimoto';
$mail['smtp']['keepalive'] = FALSE;


/* ===================================================================
 *  Mail receiver settings
 * ===================================================================*/

/*
 * --------------------------------------------------------------------
 * Mail receiver
 * 
 * you can choose these:
 *   imap  : IMAP server
 *   pop3  : POP3 server
 *   stdin : get mail from STDIN
 * --------------------------------------------------------------------
 */
$mail['receiver'] = 'pop3';

/*
 * --------------------------------------------------------------------
 * IMAP settings
 * 
 * If you are using a mail server that supports IMAP,
 * please set the following parameters.
 * --------------------------------------------------------------------
 */
$mail['imap']['hostname']   = 'localhost';
$mail['imap']['port']       = 143;
$mail['imap']['user']       = '';
$mail['imap']['password']   = '';
$mail['imap']['ssl']        = FALSE;
$mail['imap']['persistent'] = FALSE;
$mail['imap']['timeout']    = 30;
$mail['imap']['authenticate'] = 'LOGIN';
$mail['imap']['saslkey']    = '';


/*
 * --------------------------------------------------------------------
 * POP3 settings
 * 
 * If you are using a mail server that supports POP3,
 * please set the following parameters.
 * --------------------------------------------------------------------
 */
$mail['pop3']['hostname']   = 'localhost';
$mail['pop3']['port']       = 110;
$mail['pop3']['user']       = '';
$mail['pop3']['password']   = '';
$mail['pop3']['ssl']        = FALSE;
$mail['pop3']['persistent'] = FALSE;
$mail['pop3']['timeout']    = 30;



// End of mail.php