<?php
//
// class.FmAdmin.php - Filemaker admin console class
//
// Vers. 1.1, YP 11/07/2016
//
//
// History:
// 1.0 , YP 11/07/2016 - initial release
// 1.1 , YP 12/07/2016 - getFmClientsList output format changed. Use extended list of clients
//


class FmAdmin {

  private $cmd = '';
  public $err = '';

  function __construct($cfg) {
	$this->CFG = $cfg;
    $this->cmd = $this->CFG['fmsadmin_path'].' -u '.$this->CFG['fmsadmin_user'].' -p '.$this->CFG['fmsadmin_pass'].' -y ';
  }

  //
  // runFMCommand - run FileMaker Admin Console command
  // Call: $out = runFMCommand($cmd);
  // Where:		$cmd - command to run
  // 			$out - output
  //
  private function runFMCommand($cmd) {
	  $this->err = '';

    try	{								
      return shell_exec($cmd.' 2>&1');	        // Run local command
    }
    catch (Exception $e) {				// Error disconnecting clients
      $this->err = "Send command error: ".$e->getMessage();
      return '';
    }    
  }	  

  //
  // getFmClientsList - load connected FM clients list
  // Call:	$list = getFmClientsList();
  // Where:	$list - array of users in the following format:
  //			$list[Client ID] = User Name 
  //
  // Example of fmsadmin output:
  // Client ID User Name Computer Name Ext Privilege IP Address      MAC Address          Connect Time           Duration App Version        App Language User Connections License File Name         Account Name Privilege Set           
  // 698       neocode   marion        fmapp         207.230.229.35  8e:5b:71:c1:1d:00    12/10/2016 12:58:38 AM 0:55:30  ProAdvanced 15.0.1 English      No                       NeoCode_SpeedTest user1        [Data Entry Only]      
  // 760       Yurii     LUCY-VAN-PELT fmapp         207.230.229.204 00:15:5d:18:a4:7e... 12/10/2016 1:53:14 AM  0:00:54  ProAdvanced 14.0.4 English      No                       NeoCode_SpeedTest [Guest]      [Data Entry Only]      
  // 761       Yurii     LUCY-VAN-PELT fmapp         207.230.229.204 00:15:5d:18:a4:7e... 12/10/2016 1:53:17 AM  0:00:51  ProAdvanced 13.0v2 English      No                       NeoCode_SpeedTest [Guest]      [Data Entry Only]      
  //
  public function getFmClientsList() {
	  $list = array();  
	  $this->err = '';
	  $out = $this->runFMCommand($this->cmd.' -s LIST CLIENTS');      // Load extended list of clients
    if ($this->err) {
      return $list;
    }	
    $result = explode("\n",$out);		// Explode to lines
    $header = array_shift($result);		// Get header 
    if (!$header) {						// No result, return empty list
      return $list;
    }
	  //print "Header: $header\n";
	  $clientIdPos = strpos($header, 'Client ID');	// 
	  $userPos = strpos($header, 'User Name');
    $accPos = strpos($header, 'Account Name');
	  $privPos = strpos($header, 'Privilege Set');
	  if ($clientIdPos != 0 || $accPos <=0 || $accPos <=0 || $privPos <=0) { // Can't parse output - invalid format or error
	    $this->err = "Error loading list of users: ".$out;
	    return $list;
	  }  	
	  foreach ($result as $str) {			// Write all users in array
  	  $id = trim(substr($str,0,$userPos-1));
	    $name = trim(substr($str,$accPos,$privPos-$accPos));
	    $list[$id] = $name;
	  }  
	  return $list;
  }	

  //
  // killFMClient - disconnect FM client
  // Call:	killFMUser($clientId);
  // Where:	$clientId - FM client ID
  //
  public function killFMClient($clientId) {
	  $this->err = '';
	  $out = $this->runFMCommand($this->cmd.' DISCONNECT CLIENT '.$clientId);
    if ($this->err) {
      return '';
    }
    if (strpos($out,'Error:	')) {
	    $this->err = $out;
	  }
  }	

}
