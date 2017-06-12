<?php

// doaction.php - kill duplicate FM user connections
//
// Vers. 1.0 , YP 06/12/2016
//
// Check filemaker server for duplicate users
// If there are duplicate users then all instances of the duplication users are disconnected
// If duplicate users are not disconnected until timeout then email error 
//
// Config parameters in config.ini
// Call: php doaction.php 
//
// Copyright © 2015 Neo Code Software Ltd
// Artisanal FileMaker & PHP hosting and development since 2002
// 1-888-748-0668 http://store.neocodesoftware.com
//
// History:
// 1.0 , YP 12/06/2016 - Initial release
//

include_once('config.php');		// configuration and common stuff
                              // Global vars
$SLEEP_TIME = 10;					    // Sleep time between cycles
$LIFETIME_CYCLES = 12;			  // Number of cycles to wait
$notKilled = array();         // List of not killed users


                              // Start here
$LOG->message("start");
                              // 1. Load list of active FM users
$fmClientList = $FMADMIN->getFmClientsList();
if ($FMADMIN->err) {			    // Error loading list of users - can't proceed
  SendNotificationAndExit("Error loading clients: ".$FMADMIN->err);
} 

$duplicates = array();
                              // Count keys by values => duplicates will have > 1
foreach (array_count_values ($fmClientList) as $userName => $count) {
  if ($count > 1) {           // Duplicate connection for this user
    $allConnections = array_keys($fmClientList,$userName);  // All connection for this user 
    rsort($allConnections);      // All connections sorted high-to-low
    $duplicates[$userName] = array_slice($allConnections,1); // Get all connection but the one with highest connection ID
  }
}

if (!count($duplicates)) {    // No duplicates - exit
  $LOG->message("No duplicates found");
  exit;
}
  
$LOG->message("Found duplicates: ". var_export($duplicates, true));

                              // Kill all duplicates
foreach ($duplicates as $user => $idlist) {                              
  foreach ($idlist as $id) {
    $FMADMIN->killFMClient($id);
    if ($FMADMIN->err) {		// Error killing user
      SendNotificationAndExit("Error killing user: ".$FMADMIN->err);
	  }
  }
}
                              // Check disconnected users by connection IDs. User can reconnect and get new connection ID
for ($i=0;$i<$LIFETIME_CYCLES;$i++) {
  sleep($SLEEP_TIME);
  reset($notKilled);          // Empty array on every interation
                              // Load list of active FM users
  $fmClientList = $FMADMIN->getFmClientsList();
  if ($FMADMIN->err) {			// Error loading list of users - can't proceed
	  SendNotificationAndExit("Error loading clients: ".$FMADMIN->err);
	} 
  $notKilled = array();        // List of not killed users
  foreach ($duplicates as $user => $idlist) {                              
    foreach ($idlist as $id) {
      if (array_key_exists($id,$fmClientList)) {  // User is not disconnected
        $notKilled[$id] = $user;    // Save not disconnected user
      }
    }
  }    

  if (!count($notKilled)) {		  // All users are disconnected
    break;
  }

}  	
if (count($notKilled)) {		  // Not disconnected users
  $msg = "Failed to disconnect users:\n";
  foreach ($notKilled as $id => $userName) {
    $msg .= "[".$id."] ".$userName."\n";
  }  
  SendNotificationAndExit($msg);
}
$LOG->message("exit");
exit;								// Finish here


function SendNotificationAndExit($msg) {
  global $CONFIG,$LOG,$MAILIT;
  $msg2Sent = date_format(new DateTime(),DATE_RFC822)."\r\n".
              (array_key_exists('SERVER_NAME',$CONFIG) ? "Server: ".$CONFIG['SERVER_NAME']."\r\n" : "").
              (array_key_exists('FM_DATABASE',$CONFIG) ? "Database: ".$CONFIG['FM_DATABASE']."\r\n" : "").
              $msg;
  $LOG->message($msg2Sent);
  $MAILIT->MailIt("Disconnect user notification", $msg2Sent);
  if ($MAILIT->err) {
    $LOG->message("Send email error: ".$MAILIT->err);
  }
  exit;
}



?>
