<?php

//////////////////////////////////////////////////////////////
//===========================================================
// inc.php
//===========================================================
// SOFTACULOUS 
// Version : 1.1
// Inspired by the DESIRE to be the BEST OF ALL
// ----------------------------------------------------------
// Started by: Alons
// Date:       10th Jan 2009
// Time:       21:00 hrs
// Site:       http://www.softaculous.com/ (SOFTACULOUS)
// ----------------------------------------------------------
// Please Read the Terms of use at http://www.softaculous.com
// ----------------------------------------------------------
//===========================================================
// (c)Softaculous Inc.
//===========================================================
//////////////////////////////////////////////////////////////

global $virtualizor_conf;

// Common Functions

if(!function_exists('v_fn')){

function v_fn($f){
	global $virtualizor_conf;
	
	if(empty($virtualizor_conf['fields'][$f])){
		$r = $f;
	}else{
		$r = $virtualizor_conf['fields'][$f];
	}
	
	return $r;	
}

}

// The following function is a variation of v_fn() to support virtualizor_cloud_account as is uses another config variable $virtcloud_acc
if(!function_exists('vc_fn')){

function vc_fn($f){
	global $virtcloud_acc;
	
	if(empty($virtcloud_acc['fields'][$f])){
		$r = $f;
	}else{
		$r = $virtcloud_acc['fields'][$f];
	}
	
	return $r;	
}

}


if(!function_exists('make_apikey')){

function make_apikey($key, $pass){
	return $key.md5($pass.$key);
}

}



if(!function_exists('_unserialize')){
	
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

}


if(!function_exists('generateRandStr')){
//generates random strings
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

}
if(!function_exists('virtualizor_generateRandPwd')){

	function virtualizor_generateRandPwd($length){

		$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%&*";
		return substr(str_shuffle($string),0,$length);

	}

}

if(!function_exists('valid_ipv6')){

function valid_ipv6($ip){

	$pattern = '/^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/';
	
	if(!preg_match($pattern, $ip)){
		return false;	
	}
	
	return true;
	
}

}

if(!function_exists('cexplode')){

// Clean explode a string
function cexplode($chars, $str, $int = 0){
	
	$r = explode($chars, $str);
	
	foreach($r as $k => $v){
		if($int){
			$r[$k] = (int) trim($v);	
		}else{
			$r[$k] = trim_check($v);
		}
	}
	
	return $r;
	
}

}

if(!function_exists('vlang_vars_name')){
// Replaces the Soft Variables with the supplied ones
function vlang_vars_name($str, $array){
	
	foreach($array as $k => $v){
		
		$str = str_replace('{{'.$k.'}}', $v, $str);
	
	}
	
	return $str;

}
}


if(!function_exists('vparse_lang')){
// Parse Virtualizor Languages
function vparse_lang($str){
	
	global $vlang;
	
	foreach($vlang as $k => $v){
		
		$str = str_replace('{{'.$k.'}}', $v, $str);
	
	}
	
	return $str;

}
}


if(!function_exists('vload_lang')){
// Load Virtualizor Languages
function vload_lang($lang = 'en_us'){
	
	global $vlang;

	$langMap = array();
	$langMap['zh_cn'] = $langMap['zh_tw'] = 'chinese';
	$langMap['en_us'] = $langMap['en_uk'] = 'english';
	$langMap['fr_fr'] = $langMap['fi_fi'] = 'french';
	$langMap['de_de'] = 'german';
	$langMap['pl_pl'] = 'polish';
	$langMap['pt_br'] = $langMap['pt_pt'] = 'portuguese';
	$langMap['ru_ru'] = 'russian';
	$langMap['tr_tr'] = 'turkish';

	if(!@include_once(dirname(__FILE__).'/virt_lang/'.$langMap[$lang].'/index_lang.php')){
		include_once(dirname(__FILE__).'/virt_lang/english/index_lang.php');
	}

	if(!@include_once(dirname(__FILE__).'/virt_lang/'.$langMap[$lang].'/enduser_lang.php')){
		include_once(dirname(__FILE__).'/virt_lang/english/enduser_lang.php');
	}
	
	$vlang = $l;

}
}

if(!function_exists('virt_val')){

function virt_val($val){
	return (empty($val) && strlen($val) < 1 ? 0 : $val);
}

}

if(!class_exists('hash_encryption')){

	// WHMCS Decrypter
	class hash_encryption {
		/**
		 * Hashed value of the user provided encryption key
		 * @var	string
		 **/
		var $hash_key;
		/**
		 * String length of hashed values using the current algorithm
		 * @var	int
		 **/	
		var $hash_lenth;
		/**
		 * Switch base64 enconding on / off
		 * @var	bool	true = use base64, false = binary output / input
		 **/	
		var $base64;
		/**
		 * Secret value added to randomize output and protect the user provided key
		 * @var	string	Change this value to add more randomness to your encryption
		 **/	
		var $salt = 'Change this to any secret value you like. "d41d8cd98f00b204e9800998ecf8427e" might be a good example.';
		
	
		/**
		 * Constructor method
		 *
		 * Used to set key for encryption and decryption.
		 * @param	string	$key	Your secret key used for encryption and decryption
		 * @param	boold	$base64	Enable base64 en- / decoding
		 * @return mixed
		 */
		function __construct($key, $base64 = true) {
			
			global $cc_encryption_hash;
			
			// Toggle base64 usage on / off
			$this->base64 = $base64;
			
			// Instead of using the key directly we compress it using a hash function
			$this->hash_key = $this->_hash($key);
			
			// Remember length of hashvalues for later use
			$this->hash_length = strlen($this->hash_key);
		}
			
		/**
		 * Method used for encryption
		 * @param	string	$string	Message to be encrypted
		 * @return string	Encrypted message
		 */
		function encrypt($string) {
			$iv = $this->_generate_iv();
			
			// Clear output
			$out = '';
			
			// First block of output is ($this->hash_hey XOR IV)
			for($c=0;$c < $this->hash_length;$c++) {
				$out .= chr(ord($iv[$c]) ^ ord($this->hash_key[$c]));
			}
	
			// Use IV as first key
			$key = $iv;
			$c = 0;
	
			// Go through input string
			while($c < strlen($string)) {
				// If we have used all characters of the current key we switch to a new one
				if(($c != 0) and ($c % $this->hash_length == 0)) {
					// New key is the hash of current key and last block of plaintext
					$key = $this->_hash($key . substr($string,$c - $this->hash_length,$this->hash_length));
				}
				// Generate output by xor-ing input and key character for character
				$out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
				$c++;
			}
			// Apply base64 encoding if necessary
			if($this->base64) $out = base64_encode($out);
			return $out;
		}
		
		/**
		 * Method used for decryption
		 * @param	string	$string	Message to be decrypted
		 * @return string	Decrypted message
		 */
		function decrypt($string) {
			// Apply base64 decoding if necessary
			if($this->base64) $string = base64_decode($string);
			
			// Extract encrypted IV from input
			$tmp_iv = substr($string,0,$this->hash_length);
			
			// Extract encrypted message from input
			$string = substr($string,$this->hash_length,strlen($string) - $this->hash_length);
			$iv = $out = '';
			
			// Regenerate IV by xor-ing encrypted IV from block 1 and $this->hashed_key
			// Mathematics: (IV XOR KeY) XOR Key = IV
			for($c=0;$c < $this->hash_length;$c++) 
			{
				$iv .= chr(ord($tmp_iv[$c]) ^ ord($this->hash_key[$c]));
			}
			// Use IV as key for decrypting the first block cyphertext
			$key = $iv;
			$c = 0;
			
			// Loop through the whole input string
			while($c < strlen($string)) {
				// If we have used all characters of the current key we switch to a new one
				if(($c != 0) and ($c % $this->hash_length == 0)) {
					// New key is the hash of current key and last block of plaintext
					$key = $this->_hash($key . substr($out,$c - $this->hash_length,$this->hash_length));
				}
				// Generate output by xor-ing input and key character for character
				$out .= chr(ord($key[$c % $this->hash_length]) ^ ord($string[$c]));
				$c++;
			}
			return $out;
		}
	
		/**
		 * Hashfunction used for encryption
		 *
		 * This class hashes any given string using the best available hash algorithm.
		 * Currently support for md5 and sha1 is provided. In theory even crc32 could be used
		 * but I don't recommend this.
		 *
		 * @access	private
		 * @param	string	$string	Message to hashed
		 * @return string	Hash value of input message
		 */
		function _hash($string) {
			// Use sha1() if possible, php versions >= 4.3.0 and 5
			if(function_exists('sha1')) {
				$hash = sha1($string);
			} else {
				// Fall back to md5(), php versions 3, 4, 5
				$hash = md5($string);
			}
			$out ='';
			// Convert hexadecimal hash value to binary string
			for($c=0;$c<strlen($hash);$c+=2) {
				$out .= $this->_hex2chr($hash[$c] . $hash[$c+1]);
			}
			return $out;
		}
		
		/**
		 * Generate a random string to initialize encryption
		 *
		 * This method will return a random binary string IV ( = initialization vector).
		 * The randomness of this string is one of the crucial points of this algorithm as it
		 * is the basis of encryption. The encrypted IV will be added to the encrypted message
		 * to make decryption possible. The transmitted IV will be encoded using the user provided key.
		 *
		 * @todo	Add more random sources.
		 * @access	private
		 * @see function	hash_encryption
		 * @return string	Binary pseudo random string
		 **/
		function _generate_iv() {
			// Initialize pseudo random generator
			srand ((double)microtime()*1000000);
			
			// Collect random data.
			// Add as many "pseudo" random sources as you can find.
			// Possible sources: Memory usage, diskusage, file and directory content...
			$iv  = $this->salt;
			$iv .= rand(0,getrandmax());
			// Changed to serialize as the second parameter to print_r is not available in php prior to version 4.4
			$iv .= serialize($GLOBALS);
			return $this->_hash($iv);
		}
		
		/**
		 * Convert hexadecimal value to a binary string
		 *
		 * This method converts any given hexadecimal number between 00 and ff to the corresponding ASCII char
		 *
		 * @access	private
		 * @param	string	Hexadecimal number between 00 and ff
		 * @return	string	Character representation of input value
		 **/
		function _hex2chr($num) {
			return chr(hexdec($num));
		}
	}
}

if(!function_exists('get_server_pass_from_whmcs')){
	function get_server_pass_from_whmcs($enc_pass){
		
		global $cc_encryption_hash;
		// Include WHMCS database configuration file
		include_once(dirname(dirname(dirname(dirname(__FILE__)))).'/configuration.php');
		$key1 = md5 (md5 ($cc_encryption_hash));
		$key2 = md5 ($cc_encryption_hash);
		$key = $key1.$key2;
		$hasher = new hash_encryption($key);
		return $hasher->decrypt($enc_pass);
	}
}

if(!function_exists('load_database')){

	function load_database(){
		
		global $db;
		$config_file = file_get_contents(dirname(dirname(dirname(__DIR__))).'/config/blesta.php');
		preg_match('/\'host\'\s+\=\>\s+\'(.*?)\'\,/is', $config_file, $matches);
		$host = $matches[1];
		preg_match('/\'user\'\s+\=\>\s+\'(.*?)\'\,/is', $config_file, $matches);
		$user = $matches[1];
		preg_match('/\'port\'\s+\=\>\s+\'(.*?)\'\,/is', $config_file, $matches);
		$port = (int) $matches[1];
		$port = (empty($port) ? 3306 : $port);
		preg_match('/\'database\'\s+\=\>\s+\'(.*?)\'\,/is', $config_file, $matches);
		$database = $matches[1];
		preg_match('/\'pass\'\s+\=\>\s+\'(.*?)\'\,/is', $config_file, $matches);
		$pass = $matches[1];

		if(class_exists('PDO')){
			try{
				$db['pdo_conn'] = new PDO('mysql:host='.$host.';port='.$port.';dbname='.$database.';charset=utf8', $user, $pass, array(PDO::MYSQL_ATTR_MULTI_STATEMENTS => false));
			}catch(PDOException $e){
				return false;
			}
			
		// If PDO is not there we will have to use the mysql
		}else{
			
			$db['conn'] = @mysqli_connect($host, $user, $pass, $database, $port) or die( "Unable to select database");
			if(!$db['conn']){
				return false;
			}

		}

		return true;

	}

}

if(!function_exists('makequery')){
	
	function makequery($query, $token_vals = array()){

		global $db;
		$errmsg = 'Could not make the Query.<br />'.$query."<br>\n";
		// If PDO class is available and pdo_conn is made, ONLY then we will do a PDO calls
		if(class_exists('PDO')){
			try{

				$stmt = $db['pdo_conn']->prepare($query);

				// If it is an array in $token_vals we will pass to execute()
				if(!empty($token_vals) && is_array($token_vals)){
					
					// Clena the array values as it may give an error if the value is not there.
					$token_vals = clean_pdo_vals($token_vals);
					//r_print($token_vals);
					$stmt_result = $stmt->execute($token_vals);

				// We can not pass an empty Array to execute
				}else{
					$stmt_result = $stmt->execute();
				}

				if(!$stmt_result){
					
					return false;

				}

				// This is important as the other functions like fetch_assoc etc depends on this.
				$result = $stmt;

			}catch(PDOException $e){
				return false;
			}

		}else{

			// If token values are there ew will have to do a string replace otherwise mysql will fail to execute the query.
			if(!empty($token_vals)){			
				
				// Sort by string length of values.
				uksort($token_vals, 'v_ksort');
				
				foreach($token_vals as $k => $v){
					if(strpos($v, "'") !== false){
						$v = addslashes($v);
					}
					
					$query = str_replace($k, "'".$v."'", $query);
					
				}

			}

			$result = mysqli_query($db['conn'], $query);

		}

		return $result;
	}

}

if(!function_exists('vsql_fetch_assoc')){
    
	function vsql_fetch_assoc($res){
	    	global $db;
		if(class_exists('PDO') && !empty($db['pdo_conn'])){
			return $res->fetch(PDO::FETCH_ASSOC);
		}else{
			return mysqli_fetch_assoc($res);
		}
	}

}

if(!function_exists('vsql_num_row')){
    
	function vsql_num_row($res){
	    	global $db;
		if(class_exists('PDO') && !empty($db['pdo_conn'])){
			return $res->rowCount();
		}else{
	
			// mysqli num rows does not need connection for this function.
			return mysqli_num_rows($res);
		}
	}

}

if(!function_exists('clean_pdo_vals')){

	function clean_pdo_vals($token_vals){
	
		$tmp = array();
		foreach($token_vals as $k => $v){
			if(is_null($v)){
				$token_vals[$k] = '';
			}
		}
		
		return $token_vals;
	}

}

if(!function_exists('trim_check')){

	function trim_check($value){
		
		if(is_null($value)){
			$value = '';
		}

		return trim($value);
	}

}


?>