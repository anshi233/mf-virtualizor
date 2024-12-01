<?php

//////////////////////////////////////////////////////////////
//===========================================================
// api.php
//===========================================================
// SOFTACULOUS VIRTUALIZOR
// Version : 1.0
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Alons
// Date:       8th Mar 2010
// Time:       23:00 hrs
// Site:       http://www.virtualizor.com/ (SOFTACULOUS VIRTUALIZOR)
// ----------------------------------------------------------
// Please Read the Terms of use at http://www.virtualizor.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Ltd.
//===========================================================
//////////////////////////////////////////////////////////////

class Virtualizor_Admin_API {
	
	var $key = '';
	var $pass = '';
	var $ip = '';
	var $port = 4085;
	var $protocol = 'https';
	var $error = array();
	
	/**
	 * Contructor
	 *
	 * @author       Pulkit Gupta
	 * @param        string $ip IP of the NODE
	 * @param        string $key The API KEY of your NODE
	 * @param        string $pass The API Password of your NODE
	 * @param        int $port (Optional) The port to connect to. Port 4085 is the default. 4084 is non-SSL
	 * @return       NULL
	 */
	function Virtualizor_Admin_API($ip, $key, $pass, $port = 4085){
		$this->key = $key;
		$this->pass = $pass;
		$this->ip = $ip;
		$this->port = $port;
		if($port != 4085){
			$this->protocol = 'http';
		}
	}
	
	/**
	 * Dumps a variable
	 *
	 * @author       Pulkit Gupta
	 * @param        array $re The Array or any other variable.
	 * @return       NULL
	 */
	function r($re){
		echo '<pre>';
		print_r($re);
		echo '</pre>';	
	}
	
	/**
	 * Unserializes a string
	 *
	 * @author       Pulkit Gupta
	 * @param        string $str The serialized string
	 * @return       array The unserialized array on success OR false on failure
	 */
	function _unserialize($str){

		$var = @unserialize($str);
		
		if(empty($var)){
			
			preg_match_all('!s:(\d+):"(.*?)";!s', $str, $matches);
			foreach($matches[2] as $mk => $mv){
				$tmp_str = 's:'.strlen($mv).':"'.$mv.'";';
				$str = str_replace($matches[0][$mk], $tmp_str, $str);
			}
			$var = @unserialize($str);
		
		}
		
		//If it is still empty false
		if(empty($var)){
		
			return false;
		
		}else{
		
			return $var;
		
		}
	
	}
	
	/**
	 * Make an API Key
	 *
	 * @author       Pulkit Gupta
	 * @param        string $key An 8 bit random string
	 * @param        string $pass The API Password of your NODE
	 * @return       string The new APIKEY which will be used to query
	 */
	function make_apikey($key, $pass){
		return $key.md5($pass.$key);
	}
	
	/**
	 * Generates a random string for the given length
	 *
	 * @author       Pulkit Gupta
	 * @param        int $length The length of the random string to be generated
	 * @return       string The generated random string
	 */
	function generateRandStr($length){	
		$randstr = "";	
		for($i = 0; $i < $length; $i++){	
			$randnum = mt_rand(0,61);		
			if($randnum < 10){		
				$randstr .= chr($randnum+48);			
			}elseif($randnum < 36){		
				$randstr .= chr($randnum+55);			
			}else{		
				$randstr .= chr($randnum+61);			
			}		
		}	
		return strtolower($randstr);	
	}
	
	/**
	 * Makes an API request to the server to do a particular task
	 *
	 * @author       Pulkit Gupta
	 * @param        string $path The action you want to do
	 * @param        array $post An array of DATA that should be posted
	 * @param        array $cookies An array FOR SENDING COOKIES
	 * @return       array The unserialized array on success OR false on failure
	 */
	function call($path, $data = array(), $post = array(), $cookies = array()){
		
		$key = $this->generateRandStr(8);
		$apikey = $this->make_apikey($key, $this->pass);
		
		$url = ($this->protocol).'://'.$this->ip.':'. $this->port .'/'. $path;
		$url .= (strstr($url, '?') ? '' : '?');
		$url .= '&api=serialize&apikey='.rawurlencode($apikey);

		// Pass some data if there
		if(!empty($data)){
			$url .= '&apidata='.rawurlencode(base64_encode(serialize($data)));
		}
		// Set the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
			
		// Time OUT
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 3);
		
		// Turn off the server and peer verification (TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			
		// UserAgent
		curl_setopt($ch, CURLOPT_USERAGENT, 'Softaculous');
		
		// Cookies
		if(!empty($cookies)){
			curl_setopt($ch, CURLOPT_COOKIESESSION, true);
			curl_setopt($ch, CURLOPT_COOKIE, http_build_query($cookies, '', '; '));
		}
		
		if(!empty($post)){
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post));
		}
		
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		// Get response from the server.
		$resp = curl_exec($ch);
		curl_close($ch);

		// The following line is a method to test
		//if(preg_match('/sync/is', $url)) echo $resp;
		
		if(empty($resp)){
			return false;
		}
		
		$r = @unserialize($resp);
				
		if(empty($r)){
			return false;
		}
		
		return $r;
		
	}
	
	/**
	 * Create a VPS
	 *
	 * @author       Pulkit Gupta
	 * @param        string $path The action you want to do
	 * @param        array $post An array of DATA that should be posted
	 * @param        array $cookies An array FOR SENDING COOKIES
	 * @return       array The unserialized array on success OR false on failure
	 */
	function addippool($post){
		$post['addippool'] = 1;
		$path = 'index.php?act=addippool';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}

	function addips($post){
		$path = 'index.php?act=addips';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function addiso($post){
		$path = 'index.php?act=addiso';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function addplan($post){
		
		$path ='index.php?act=addplan';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function addserver($post){
		$path ='index.php?act=addserver';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function addtemplate($post){
		$path ='index.php?act=addtemplate';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function adduser($post = 0){
		$path ='index.php?act=adduser';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
		
	/**
	 * Create a VPS
	 *
	 * @author       Pulkit Gupta
	 * @param        array $post An array of DATA that should be posted
	 * @param        array $cookies An array FOR SENDING COOKIES
	 * @return       array The unserialized array on success OR false on failure
	 */
	function addvs($post, $cookies){
		$path = 'index.php?act=addvs';
		$post = $this->clean_post($post);
		$ret = $this->call($path, '', $post, $cookies);
		return array(
			'title' => $ret['title'],
			'error' => @empty($ret['error']) ? array() : $ret['error'],
			'vs_info' => $ret['newvs'],
			'globals' => $ret['globals']
		);
	}
	
	function addiprange($post){
		$path ='index.php?act=addiprange';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}

	function addsg($post){
		$path ='index.php?act=addsg';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}

	function addbackupserver($post){
		$path ='index.php?act=addbackupserver';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}	
	
	function addstorage($post){
		$path ='index.php?act=addstorage';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}

	function adddnsplan($post){
		$path ='index.php?act=adddnsplan';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function add_admin_acl($post){
		$path ='index.php?act=add_admin_acl';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function addmg($post){
		$path ='index.php?act=addmg';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function add_distro($post){
		$path ='index.php?act=add_distro';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}

	function addpdns($post){
		$path ='index.php?act=addpdns';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}


	
	function adminindex(){
		$path = 'index.php?act=adminindex';
		$res = $this->call($path);
		return $res;
	}
	
	function apidoings(){

	}
	
	function backup($post){
		$path ='index.php?act=backup';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function bandwidth(){
		$path ='index.php?act=backup';
		$ret = $this->call($path);
		return $ret;
	}
	
	/**
	 * Cleaning the POST variables
	 *
	 * @author       Pulkit Gupta
	 * @param        array $post An array of DATA that should be posted
	 * @param        array $cookies An array FOR SENDING COOKIES
	 * @return       array The unserialized array on success OR false on failure
	 */
	function clean_post(&$post, $edit = 0){
		$post['serid'] = !isset($post['serid']) ? 0 : (int)$post['serid'];
		$post['uid'] = !isset($post['uid']) ? 0 : (int)$post['uid'];
		$post['plid'] = !isset($post['plid']) ? 0 : (int)$post['plid'];
		$post['osid'] = !isset($post['osid']) ? 0 : (int)$post['osid'];
		$post['iso'] = !isset($post['iso']) ? 0 : (int)$post['iso'];
		$post['space'] = !isset($post['space']) ? 10 : (int)$post['space'];
		$post['ram'] = !isset($post['ram']) ? 512 : (int)$post['ram'];
		$post['swapram'] = !isset($post['swapram']) ? 1024 : (int)$post['swapram'];
		$post['bandwidth'] = !isset($post['bandwidth']) ? 0 : (int)$post['bandwidth'];
		$post['network_speed'] = !isset($post['network_speed']) ? 0 : (int)$post['network_speed'];
		$post['cpu'] = !isset($post['cpu']) ? 1000 : (int)$post['cpu'];
		$post['cores'] = !isset($post['cores']) ? 4 : (int)$post['cores'];
		$post['cpu_percent'] = !isset($post['cpu_percent']) ? 100 : (int)$post['cpu_percent'];
		$post['vnc'] = !isset($post['vnc']) ? 1 : (int)$post['vnc'];
		$post['vncpass'] = !isset($post['vncpass']) ? 'test' : $post['vncpass'];
		$post['sec_iso'] = !isset($post['sec_iso']) ? 0 : $post['sec_iso'];
		$post['kvm_cache'] = !isset($post['kvm_cache']) ? 0 : $post['kvm_cache'];
		$post['io_mode'] = !isset($post['io_mode']) ? 0 : $post['io_mode'];
		$post['vnc_keymap'] = !isset($post['vnc_keymap']) ? 'en-us' : $post['vnc_keymap'];
		$post['nic_type'] =  !isset($post['nic_type']) ? 'default' : $post['nic_type']; 
		$post['osreinstall_limit'] = !isset($post['osreinstall_limit']) ? 0 : (int)$post['osreinstall_limit'];
		$post['mgs'] = !isset($post['mgs']) ? 0 : $post['mgs']; 
		$post['tuntap'] = !isset($post['tuntap']) ? 0 : $post['tuntap']; 
		$post['virtio'] = !isset($post['virtio']) ? 0 : $post['virtio']; 
		if(isset($post['hvm'])){
			$post['hvm'] = $post['hvm'];
		}
		$post['noemail'] = !isset($post['noemail']) ? 0 : $post['noemail']; 
		$post['boot'] = !isset($post['boot']) ? 'dca' : $post['boot'];
		$post['band_suspend'] = !isset($post['band_suspend']) ? 0 : $post['band_suspend'];
		$post['vif_type'] = !isset($post['vif_type']) ? 'netfront' : $post['vif_type'];
		if($edit == 0){
			$post['addvps'] = !isset($post['addvps']) ? 1 : (int)$post['addvps']; 
		}else{
			$post['editvps'] = !isset($post['editvps']) ? 1 : $post['editvps']; 
			$post['acpi'] = !isset($post['acpi']) ? 1 : $post['acpi'];
			$post['apic'] = !isset($post['apic']) ? 1 : $post['apic'];
			$post['pae'] = !isset($post['pae']) ? 1 : $post['pae'];
			$post['dns'] = !isset($post['dns']) ? array('4.2.2.1','4.2.2.2') : $post['dns'];
			$post['editvps'] = !isset($post['editvps']) ? 1 : (int)$post['editvps']; 
		}

		return $post;
	}
	
	function cluster(){

	}
	
	function config($post){
		$path ='index.php?act=config';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	/**
	 * Get CPU usage details
	 *
	 * @author       Pulkit Gupta
	 * @param        
	 * @return       array The unserialised array is returned on success or 
	 *				 empty array is returned on failure 
	 */
	function cpu($serverid = 0){
		$path = 'index.php?act=manageserver&changeserid='.$serverid;
		$ret = $this->call($path);
		return $ret['usage']['cpu'];
	}
	
	function createssl($post){
		$path ='index.php?act=createssl';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function createtemplate($post){
		$path ='index.php?act=createtemplate';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function databackup(){

	}
	
	function defaultvsconf($post){	
		$path ='index.php?act=defaultvsconf';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	/**
	 * Delete a VPS
	 *
	 * @author       Pulkit Gupta
	 * @param        array $post An array of DATA that should be posted
	 * @return       boolean 1 on success OR 0 on failure
	 */	
	function delete_vs($vid){
		$path = 'index.php?act=vs&delete='.(int)$vid;
		$res = $this->call($path);
		return $res['done'];
	}
	
	/**
	 * Get Disk usage details
	 *
	 * @author       Pulkit Gupta
	 * @param        
	 * @return       array The unserialised array is returned on success or 
	 *				 empty array is returned on failure 
	 */
	function disk($serverid = 0){
		$path = 'index.php?act=manageserver&changeserid='.$serverid;
		$ret = $this->call($path);
		return $ret['usage']['disk'];
	}
	
	function downloadiso(){

	}
	
	function editemailtemp(){

	}
	
	function editippool($post){
		$path = 'index.php?act=editippool';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function editips($post){
		$path = 'index.php?act=editips';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function editplan($post){
		$path = 'index.php?act=editplan';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function editserver($post){
		$path = 'index.php?act=editserver';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function edittemplate($post){
		$path = 'index.php?act=edittemplate';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function edituser($post){
		$path = 'index.php?act=edituser';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	/**
	 * Create a VPS
	 *
	 * @author       Pulkit Gupta
	 * @param        array $post An array of DATA that should be posted
	 * @return       array The unserialized array on success OR false on failure
	 */
	function editvs($post, $cookies = array()){
		$path = 'index.php?act=editvs&vpsid='.$post['vpsid'];
		$post = $this->clean_post($post, 1);
		$ret = $this->call($path, '', $post, $cookies);
		return array(
			'title' => $ret['title'],
			'error' => @empty($ret['error']) ? array() : $ret['error'],
			'vs_info' => $ret['vps']
		);
	}
	
	function emailconfig($post){
		$path = 'index.php?act=emailconfig';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function emailtemp($post){
		$path = 'index.php?act=emailtemp';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function filemanager($post){
		$path = 'index.php?act=filemanager';
		$res = $this->call($path,'', $post);
		return $res;
	}
	
	function firewall($post){
		$path = 'index.php?act=firewall';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function giveos(){

	}

	function health(){

	}
	
	function hostname($post){
		$path = 'index.php?act=hostname';
		$res = $this->call($path,'',$post);
		return $res;
	}
	
	function import($post){
		$path = 'index.php?act=import';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function ippool($page = 1, $reslen = 50){
		$path = 'index.php?act=ippool&page='.$page.'&reslen='.$reslen;
		$res = $this->call($path);
		return $res;
	}
	
	/**
	 * Get list of IPs
	 *
	 * @author       Pulkit Gupta
	 * @param        
	 * @return       array The unserialised array on success.
	 */
	function ips($page = 1, $reslen = 50){
		$path = 'index.php?act=ips&page='.$page.'&reslen='.$reslen;
		$ret = $this->call($path);
		return $ret;
	}
	
	function iso($page = 1, $reslen = 50){
		$path = 'index.php?act=iso&page='.$page.'&reslen='.$reslen;
		$ret = $this->call($path);
		return $ret;
	}
	
	function kernelconf($post = 0){
		$path = 'index.php?act=kernelconf';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function license(){
		$path = 'index.php?act=license';
		$ret = $this->call($path);
		return $ret;
	}
	
	/**
	 * List VPS 
	 *
	 * @author       Pulkit Gupta
	 * @param        int page number, if not specified then only 50 records are returned.
	 * @return       array The unserialized array on success OR false on failure
	 *				 
	 */
	function listvs($page = 1, $reslen = 50){	
		$path = 'index.php?act=vs&page='.$page.'&reslen='.$reslen;
		return $this->call($path);
	}
	
	function login(){

	}
	
	function loginlogs($page = 1, $reslen = 50){
		$path = 'index.php?act=loginlogs&page='.$page.'&reslen='.$reslen;
		$ret = $this->call($path);
		return $ret;
	}
	
	function logs($page = 1, $reslen = 50){
		$path = 'index.php?act=logs&page='.$page.'&reslen='.$reslen;
		$ret = $this->call($path);
		return $ret;
	}
	
	function maintenance($post){
		$path = 'index.php?act=maintenance';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function makeslave(){

	}
	
	function os($post){
		$path = 'index.php?act=os';
		$ret = $this->call($path, array(), $post);
		return $ret;
	}
	
	function ostemplates($page = 1, $reslen = 50){
		$path = 'index.php?act=ostemplates&page='.$page.'&reslen='.$reslen;
		$ret = $this->call($path);
		return $ret;
	}
	
	function performance(){
		$path = 'index.php?act=performance';
		$ret = $this->call($path);
		return $ret;
	}
	
	function phpmyadmin(){

	}
	
	function plans($page = 1, $reslen = 50){
		$path = 'index.php?act=plans&page='.$page.'&reslen='.$reslen;
		$ret = $this->call($path);
		return $ret;
	}
	
	/**
	 * POWER OFF a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       bool TRUE on success or FALSE on failure
	 */
	function poweroff($vid){
		// Make the Request
		$res = $this->call('index.php?act=vs&action=poweroff&serid=0&vpsid='.(int)$vid);
		return $res;
	}
	
	function processes(){
		$path = 'index.php?act=processes';
		$ret = $this->call($path);
		return $ret;
	}
	
	/**
	 * Get RAM details
	 *
	 * @author       Pulkit Gupta
	 * @param        
	 * @return       array The unserialised array is returned on success or 
	 *				 empty array is returned on failure 
	 */
	function ram($serverid = 0){
		$path = 'index.php?act=manageserver&changeserid='.$serverid;
		$ret = $this->call($path);
		return $ret['usage']['ram'];
	}

	/**
	 * Rebuild a VPS
	 *
	 * @author       Pulkit Gupta
	 * @param        array $post An array of DATA that should be posted
	 * @return       array The unserialized array on success OR false on failure
	 */	
	function rebuild($post){
		$path = 'index.php?act=rebuild';
		return $this->call($path, '', $post);
	}
	
	/**
	 * RESTART a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       bool TRUE on success or FALSE on failure
	 */
	function restart($vid){
		// Make the Request
		$res = $this->call('index.php?act=vs&action=restart&serid=0&vpsid='.(int)$vid);
		return $res;
	}
	
	function restartservices($post){
		$path = 'index.php?act=restartservices';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	/**
	 * Current server information
	 *
	 * @author       Pulkit Gupta
	 * @param        
	 * @return       array The unserialized array on success OR false on failure
	 */
	function serverinfo(){
		$path = 'index.php?act=serverinfo';
		return $this->call($path);
	}
	
	/**
	 * List Servers
	 *
	 * @author       Pulkit Gupta
	 * @param        
	 * @return       array The unserialized array on success OR false on failure
	 */
	function servers(){
		$path = 'index.php?act=servers';
		return $this->call($path);
	}
	
	function services($post = 0){
		$path = 'index.php?act=services';
		$res = $this->call($path, '', $post);
		return $res;
	}
	
	function ssh(){
	/*	$path = 'index.php?act=ssh';
		$res = $this->call($path);
		return $res;*/
	}
	
	function ssl($post = 0){
	/*	$path = 'index.php?act=ssl';
		$res = $this->call($path, '', $post);
		return $res;*/
	}
	
	function sslcert(){
	/*	$path = 'index.php?act=sslcert';
		$res = $this->call($path);
		return $res;*/
	}
	
	/**
	 * START a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       bool TRUE on success or FALSE on failure
	 */
	function start($vid){

		$res = $this->call('index.php?act=vs&action=start&serid=0&vpsid='.(int)$vid);
		return $res;	
	}
	
	/**
	 * STOP a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       bool TRUE on success or FALSE on failure
	 */
	function stop($vid){
		// Make the Request
		$res = $this->call('index.php?act=vs&action=stop&serid=0&vpsid='.(int)$vid);
		return $res;
	}
	
	/**
	 * Gives status of a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       int 1 if the VM is ON, 0 if its OFF
	 */
	function status($vid){
		
		// Make the Request
		$res = $this->start((int)$vid);
		return $res['vsop']['status'][(int)$vid];
		
	}
	
	/**
	 * Suspends a VM of a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       int 1 if the VM is ON, 0 if its OFF
	 */
	function suspend($vid){
		$path = 'index.php?act=vs&suspend='.(int)$vid;
		$res = $this->call($path);
		return $res;
	}
	
	/**
	 * Unsuspends a VM of a Virtual Server
	 *
	 * @author       Pulkit Gupta
	 * @param        int $vid The VMs ID
	 * @return       int 1 if the VM is ON, 0 if its OFF
	 */
	function unsuspend($vid){
		$path = 'index.php?act=vs&unsuspend='.(int)$vid;
		$res = $this->call($path);
		return $res;
	}
	
	function tools(){

	}
	
	function ubc($post){
		$path = 'index.php?act=ubc';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function updates(){
		$path = 'index.php?act=updates';
		$res = $this->call($path);
		return $res;
	}
	
	function userlogs($page = 1, $reslen = 50){
		$path = 'index.php?act=userlogs&page='.$page.'&reslen='.$reslen;
		$res = $this->call($path);
		return $res;
	}
	
	function users($page = 1, $reslen = 50){
		$path = 'index.php?act=users&page='.$page.'&reslen='.$reslen;
		$res = $this->call($path);
		return $res;
	}
	
	function vnc($post){
		$path = 'index.php?act=vnc';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function vpsbackupsettings($post){
		$path = 'index.php?act=vpsbackupsettings';
		$res = $this->call($path, array(), $post);
		return $res;
	}
	
	function vpsbackups($post){
		$path = 'index.php?act=vpsbackups';
		$res = $this->call($path, '', $post);
		return $res;
	}
	
	function vs($page = 1, $reslen = 50){
		$path = 'index.php?act=vs&page='.$page.'&reslen='.$reslen;
		$res = $this->call($path);
		return $res;
	}
	
	function vsbandwidth(){
		$path = 'index.php?act=vsbandwidth';
		$res = $this->call($path);
		return $res;
	}
	
	function vscpu(){
		$path = 'index.php?act=vscpu';
		$res = $this->call($path);
		return $res;
	}
	
	function vsram(){
		$path = 'index.php?act=vsram';
		$res = $this->call($path);
		return $res;

	}

} // Class Ends

?>