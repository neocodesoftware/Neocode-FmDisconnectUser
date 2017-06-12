<?php
// config.php - Config for candeal vacuum
// Vers. 1.0 , YP 06/11/2016
//
// Copyright © 2015 Neo Code Software Ltd
// Artisanal FileMaker & PHP hosting and development since 2002
// 1-888-748-0668 http://store.neocodesoftware.com
//
// History:
// 1.0 , YP 06/11/2016 - Initial release
//

include_once('class.Log.php');		// Log class
include_once('class.FmAdmin.php');
include_once('class.Mail.php');
require 'phpmailer/PHPMailerAutoload.php';
									// Global vars
define('PROCESSING','Processing');	
define('DONE','Done');
define('QUEUE_FILE','queue.dat');
define('QUEUE_LOCK','queue.lock');
define('STAT_LIFETIME',3600);		// Status file lifetime

$INIFILE = 'config.ini';
$LOGFILE = 'index.log';

$CONFIG = parse_ini_file($INIFILE, true);
$LOG = new LOG($CONFIG['VAR_DIR'].$LOGFILE);		// Default log
$FMADMIN = new FmAdmin($CONFIG);
$MAILIT = new MailIt($CONFIG);

set_error_handler("error_handler", E_ALL); // Catch all error/notice messages

//
// error_handler - catch notice and warnings
//
function error_handler($errno, $errstr, $errfile, $errline) {
  global $LOG;
  if($errno == E_WARNING) {
	$LOG->message("Warning. File: $errfile, Line: $errline. $errstr");
//  	throw new Exception($errstr);
  } else if($errno == E_NOTICE) {
//      throw new Exception($errstr);
	$LOG->message("Notice. File: $errfile, Line: $errline. $errstr");
	exit;
  }
}								// -- error_handler --

//
// printLogAndDie - log and print  message and exit
// Call:	printLogAndDie($msg)
// Where:	$msg - message to write in log file
//
function printLogAndDie($str) {
  global $LOG;
  $LOG->message($str);
  exit;
}								// -- printLogAndDie --


