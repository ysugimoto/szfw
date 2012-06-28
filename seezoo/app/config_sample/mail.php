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
$mail['type']      = 'php';

/*
 * --------------------------------------------------------------------
 * Mail-From
 * 
 * Seezoo mail library use this parameter at default
 * ( enable change in your script )
 * --------------------------------------------------------------------
 */
$mail['from']      = 'you@example.com';

/*
 * --------------------------------------------------------------------
 * Mail-From-Name
 * 
 * Seezoo mail library use this parameter at default
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
$mail['smtp']['hostname']  = 'localhost';
$mail['smtp']['port']      = 25;
$mail['smtp']['crypto']    = FALSE;
$mail['smtp']['username']  = '';
$mail['smtp']['password']  = '';
$mail['smtp']['keepalive'] = FALSE;


// End of mail.php