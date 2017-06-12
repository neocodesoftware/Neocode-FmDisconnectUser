<?php

// index.php - Process web requests on disconnecting clients
// Vers. 1.0 , YP 06/12/2016
//
// Receive username and disconnect user, return status for the request
//
// index.php?token
//
// Copyright © 2015 Neo Code Software Ltd
// Artisanal FileMaker & PHP hosting and development since 2002
// 1-888-748-0668 http://store.neocodesoftware.com
//
// History:
// 1.0 , YP 06/12/2016 - Initial release
//

include_once('config.php');			// configuration and common stuff
                                // Global vars
$LOG->message("request received");
$message = '';						      // Notification message
$action = '';						        // Action we need to do

                                // Start here
try {
  $cmd = 'php '.$CONFIG['DO_ACTION'];
  pclose(popen($cmd , 'r'));
}
catch (Exception $e) {				// 
    $LOG->message("Send command error: ".$e->getMessage());
    printLogAndDie("Send command error: ".$e->getMessage());
    showPage("Error: connection error");
}
showPage('Processing');


//
// showPage - show page to user
//
function showPage($message) {
  print $message;
  exit;
}								// -- showPage --

?>
