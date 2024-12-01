<?php

error_reporting(0);

// The Hask Key in Blesta
$hash_key = array();

// The Virtualizor Master IP
$connection_ip = array();

// The Virtualizor Master HOSTNAME 
//Provide the array in following format:
//$hostnames = array('SERVER1_IP' => 'SERVER1_HOSTNAME', 'SERVER2_IP' => 'SERVER2_HOSTNAME');
//If the hostname of server in blesta is ip itself than no need to change $hostnames variable or else provide the hostname that is added in blesta's hostname field.
$hostnames = array();

///////////////////////////////////
// DO NOT EDIT BEYOND THIS LINE 
///////////////////////////////////

if(empty($hash_key) || empty($connection_ip)){
	virt_callback_die('<error>ERROR: Callback NOT configured</error>');
}

// Get the variables
$hash = $_POST['hash'];
$acts = $_POST['act'];
$vpsid = (int) $_POST['vpsid'];
$extra = $_POST['data'];

if(!empty($extra)){
	$extra_var = unserialize(base64_decode($extra), ['allowed_classes' => false]);
}

// Is the request from a valid server ?
if(!in_array($_SERVER['REMOTE_ADDR'], $connection_ip)  && (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) && !in_array($_SERVER['HTTP_X_FORWARDED_FOR'], $connection_ip))){
	virt_callback_die('<error>ERROR: Connection from an INVALID IP !</error>');
}

// Does the KEY MATCH ?
if(!in_array($hash, $hash_key)){
	virt_callback_die('<error>ERROR: Your security HASH does not match</error>');
}

// Is it a valid
if(empty($vpsid) && $vpsid < 1){
	virt_callback_die('<error>ERROR: Invalid VPSID</error>');
}

include_once(dirname(__FILE__).'/functions.php');

if(!load_database()){
	virt_callback_die('<error>could not load database!</error>');
}

$token = array(':vpsid' => $vpsid, ':ser_ip' => $_SERVER['REMOTE_ADDR']);
if(!empty($hostnames[$_SERVER['REMOTE_ADDR']])){
	$token[':ser_ip'] = $hostnames[$_SERVER['REMOTE_ADDR']];
}

$res = makequery('SELECT sf.* FROM service_fields sf, module_row_meta mr, services s 
					WHERE s.module_row_id = mr.module_row_id AND
					s.id = sf.service_id AND
					mr.key = "host" AND
					mr.value = :ser_ip AND
					sf.key = "vpsid" AND
					sf.value = :vpsid', $token);

$service = vsql_fetch_assoc($res);

if(empty($service)){
    virt_callback_die('<error>service not found!</error>');
}

$service_id = $service['service_id'];

if(empty($service_id)){
    virt_callback_die('<error>service ID not found!</error>');
}

foreach($acts as $k => $act){
    // Do as necessary
	switch ($act){
	    
	    //==================
		// HostName Changed
		//==================
		case 'changehostname':
		    $res = makequery('UPDATE service_fields SET 
		                        `value` = :value 
		                        WHERE `key` = "virtualizor_domain" AND 
		                        `service_id` = :service_id', array(':value' => $extra_var['newhostname'], ':service_id' => $service_id));
			if(!$res){
				echo '<error>Error in changing hostname in service!</error>';
			}else{
				echo '<success>1</success>';
			}        
		    
		    break;
		//===============
		// VPS Suspended
		//===============
		case 'suspend':
		    
		    if(empty($extra_var['suspendreason'])){
				$extra_var['suspendreason'] = "";
			}
			$time = date('Y-m-d H:i:s', $extra_var['time']);
			$res = makequery('UPDATE services SET status = "suspended",
							 suspension_reason = :suspension_reason,
							 date_suspended = :date_suspended
			                WHERE id = '.$service_id, array(':suspension_reason' => $extra_var['suspendreason'], ':date_suspended' => $time));
			if(!$res){
				echo '<error>Error in suspending service!</error>';
			}else{
				echo '<success>1</success>';
			}
			
		    break;
		//===============
		// VPS Unsuspended
		//===============
		case 'unsuspend':
		    
			$res = makequery('UPDATE services SET status = "active",
							 suspension_reason = "",
							 date_suspended = NULL
			                WHERE id = '.$service_id);
			                
			 if(!$res){
				echo '<error>Error in unsuspending service!</error>';
			}else{
				echo '<success>1</success>';
			}
			
		    break;
		//===============
		// VPS terminate
		//===============
		case 'terminate':
			$res = makequery('UPDATE services SET status = "canceled",
							 cancellation_reason = "" 
			                WHERE id = '.$service_id);
			if(!$res){
				echo '<error>Error in cancelling service!</error>';
			}else{
				echo '<success>1</success>';
			}
			
		    break;
		//=============
		// IPs Changed
		//=============
		case 'changeip':

			$ip_list = array();

			if($extra_var['ipv4']){
				$ipv4_list = explode(",", $extra_var['ipv4']);
				foreach ($ipv4_list as $ipv4){
					$ip_list[] = $ipv4;
				}
			}

			if($extra_var['ipv6']){
				$ipv6_list = explode(",", $extra_var['ipv6']);
				foreach ($ipv6_list as $ipv6){
					$ip_list[] = $ipv6;
				}
			}

			if(count($ip_list) > 1){
				$tmplist = $ip_list;
				unset($tmplist[0]);
				$ips = implode("\n", $tmplist);
			}else{
				$ips = "";
			}

			$res = makequery('UPDATE service_fields SET 
		                        `value` = :value 
		                        WHERE `key` = "virtualizor_ip" AND 
		                        `service_id` = :service_id', array(':value' => $ip_list[0], ':service_id' => $service_id));
			if(!$res){
				echo '<error>Error in changing primary IP in service!</error>';
			}else{
				echo '<success>1</success>';
			}  

			$res = makequery('UPDATE service_fields SET 
		                        `value` = :value 
		                        WHERE `key` = "virtualizor_additional_ips" AND 
		                        `service_id` = :service_id', array(':value' => $ips, ':service_id' => $service_id));
			if(!$res){
				echo '<error>Error in changing IP in service!</error>';
			}else{
				echo '<success>1</success>';
			} 

			break;
	    //==================
		// Default Scenario
		//==================
		default:
			echo "1";
	}
    
}

function virt_callback_die($msg){
	die($msg);
}

?>