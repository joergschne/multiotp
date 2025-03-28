<?php

/****************************************************************
 * Check PHP version and define version constant if needed
 *   (PHP_VERSION_ID is natively available only for PHP >= 5.2.7)
 ****************************************************************/
if (!function_exists('constant_defined')) {
  function constant_defined(
    $constant_name
  ) {
    $result = false;
    foreach (get_defined_constants() as $key=>$value) {
      if (strtoupper($key) == strtoupper($constant_name)) {
        $result = true;
        break;
      }
    }
    return $result;
  }
}


if (!constant_defined('PHP_VERSION_ID'))
{
    $version = explode('.', PHP_VERSION);
    define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
}

if (PHP_VERSION_ID < 50207)
{
    define('PHP_MAJOR_VERSION',   $version[0]);
    define('PHP_MINOR_VERSION',   $version[1]);
    define('PHP_RELEASE_VERSION', $version[2]);
}  


if (!function_exists('nullable_trim')) {
  function nullable_trim(
    $string
  ) {
    return (is_null($string) ? "" : trim($string));
  }
}


if (!function_exists('nullable_bin2hex')) {
  function nullable_bin2hex(
    $string
  ) {
    return (is_null($string) ? "" : bin2hex($string));
  }
}


if (!function_exists('pcre_fnmatch')) {
  function pcre_fnmatch(
    $pattern,
    $string,
    $flags = 0
  ) {
    define('FNM_PATHNAME', 1);
    define('FNM_NOESCAPE', 2);
    define('FNM_PERIOD', 4);
    define('FNM_CASEFOLD', 16);

    $modifiers = null;
    $transforms = array(
      '\*'    => '.*',
      '\?'    => '.',
      '\[\!'    => '[^',
      '\['    => '[',
      '\]'    => ']',
      '\.'    => '\.',
      '\\'    => '\\\\'
    );
   
    // Forward slash in string must be in pattern:
    if ($flags & FNM_PATHNAME) {
      $transforms['\*'] = '[^/]*';
    }
   
    // Back slash should not be escaped:
    if ($flags & FNM_NOESCAPE) {
      unset($transforms['\\']);
    }
   
    // Perform case insensitive match:
    if ($flags & FNM_CASEFOLD) {
      $modifiers .= 'i';
    }
   
    // Period at start must be the same as pattern:
    if ($flags & FNM_PERIOD) {
      if (strpos($string, '.') === 0 && strpos($pattern, '.') !== 0) return false;
    }
   
    $pattern = '#^'
      . strtr(preg_quote($pattern, '#'), $transforms)
      . '$#'
      . $modifiers;
   
    return (boolean)preg_match($pattern, $string);
  }
} 


if (!function_exists('fnmatch')) {
  function fnmatch(
    $pattern,
    $string,
    $flags = 0)
  {
    return pcre_fnmatch($pattern, $string, $flags);
  }
}


if (!function_exists('is64bitPHP')) {
  function is64bitPHP() {
    return strstr(php_uname("m"), '64') == '64';
  }
}


/***********************************************************************
 * Name: ram_total_space
 * Short description: return total RAM in Bytes.
 *
 * @return int Bytes
 ***********************************************************************/
if (!function_exists('ram_total_space')) {
    function ram_total_space() {
        $size = 0;
        if (mb_strtolower(mb_substr(PHP_OS, 0, 3),'UTF-8') === 'win') {
            $lines = null;
            $matches = null;
            exec('wmic ComputerSystem get TotalPhysicalMemory /Value', $lines);
            if (preg_match('/^TotalPhysicalMemory\=(\d+)$/', $lines[2], $matches)) {
                $size = $matches[1];
            }
        } else {
            $meminfo_file = fopen('/proc/meminfo', 'r');
            while ($line = fgets($meminfo_file)) {
                $elements = array();
                if (preg_match('/^MemTotal:\s+(\d+)\skB$/', $line, $elements)) {
                    $size = $elements[1] * 1024;
                    break;
                }
            }
            fclose($meminfo_file);
        }
        return (double) $size;
    }
}


/***********************************************************************
 * Name: ram_free_space
 * Short description: return free RAM in Bytes.
 *
 * @return int Bytes
 ***********************************************************************/
if (!function_exists('ram_free_space')) {
    function ram_free_space() {
        $size = 0;
        if (mb_strtolower(mb_substr(PHP_OS, 0, 3),'UTF-8') === 'win') {
            $lines = null;
            $matches = null;
            exec('wmic OS get FreePhysicalMemory /Value', $lines);
            if (preg_match('/^FreePhysicalMemory\=(\d+)$/', $lines[2], $matches)) {
                $size = $matches[1] * 1024;
            }
        } else {
            $meminfo_file = fopen('/proc/meminfo', 'r');
            while ($line = fgets($meminfo_file)) {
                $elements = array();
                if (preg_match('/^MemFree:\s+(\d+)\skB$/', $line, $elements)) {
                    // KB to Bytes
                    $size = $elements[1] * 1024;
                    break;
                }
            }
            fclose($meminfo_file);
        }
        return (double) $size;
    }
}


/***********************************************************************
 * Name: bytes_nice_format
 * Short description: nice format for a size in bytes
 *
 * Creation 2021-03-14
 * Update   2021-03-14
 * @version 1.0.0
 * @author  Adapted from https://www.php.net/manual/en/function.disk-free-space.php#103382
 *
 * @param   int     $bytes   size in bytes
 * @return  string           nice size in a string
 ***********************************************************************/
if (!function_exists('bytes_nice_format')) {
    function bytes_nice_format($bytes) {
        $size_prefix = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB' );
        $base = 1024;
        $class = min((int)log($bytes , $base) , count($size_prefix) - 1);
        return sprintf('%1.2f' , $bytes / pow($base,$class)) . ' ' . $size_prefix[$class];
    }
}


/***********************************************************************
 * Name: bcmod
 * Short description: description: Patch for bcmod
 *
 * Creation 2018-11-15
 * Update   2018-11-15
 * @version 1.0.0
 * @author  Adapted from http://php.net/manual/en/function.bcmod.php#38474
 *
 * @param   string  $dividend  dividend
 *          string  $divisor   divisor
 * @return  string             modulus as a string
 ***********************************************************************/
if (!function_exists('bcmod')) {
    function bcmod($dividend, $divisor) {
        // how many numbers to take at once? carefull not to exceed (int)
        $take = 5;    
        $mod = '';
        $div = $dividend;
        do {
            $a = (int)$mod.substr( $div, 0, $take );
            $div = substr( $div, $take );
            $mod = $a % $divisor;   
        } while ( strlen($div) );

        return (int)$mod;
    }
}


/***********************************************************************
 * Name: is_valid_ipv4
 * Short description: Check if the string is a valid IP address
 *
 * Creation 2010-03-??
 * Update   2014-01-18
 * @version 1.0.0
 * @author  Adapted from http://andrewensley.com/2010/03/php-validate-an-ip-address/
 *
 * @param   string  $ip  String to check
 * @return  boolean      TRUE if it is a valid IP address
 ***********************************************************************/
if (!function_exists('is_valid_ipv4')) {
    function is_valid_ipv4($ip)
    {
        // filter_var is available with PHP >= 5.2
        if (function_exists('filter_var')) {
            return (filter_var($ip, FILTER_VALIDATE_IP) !== FALSE);
        } else {
            return preg_match('/\b(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'.
                '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'.
                '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.'.
                '(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\b/', $ip) !== 0;
        }
    }
}


/***********************************************************************
 * Name: is_public_ip
 * Short description: Check if the string is a public IP address
 *
 * Creation 2020-05-20
 * Update   2020-05-20
 * @version 1.0.0
 *
 * @param   string  $ip  String to check
 * @return  boolean      TRUE if it is a valid public IP address
 ***********************************************************************/
if (!function_exists('is_public_ip')) {
    function is_public_ip($ip) {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_NO_PRIV_RANGE |  FILTER_FLAG_NO_RES_RANGE);
    }
}


/***********************************************************************
 * Name: ip2long32bit
 * Short description: Patch for ip2long
 * @author  Adapted from php.net
 *
 * @param   string   $ip_address  String to convert
 * @return  unsigned              Unsigned integer, or FALSE
 ***********************************************************************/
if (!function_exists('ip2long32bit')) {
    function ip2long32bit($ip_address)
    {
        $long = ip2long($ip_address);
        if ($long == -1 || $long === FALSE) {
            return(FALSE);
        } else {
            return(sprintf("%u", ip2long($ip_address)));
        }
    }
}


/***********************************************************************
 * Name: long2ip32bit
 * Short description: Patch for long2ip
 * @author  Adapted from php.net
 *
 * @param   unsigned $ip_unsigned  Unsigned integer to convert
 * @return  unsigned               String with the IP address (a.b.c.d)
 ***********************************************************************/
if (!function_exists('long2ip32bit')) {
    function long2ip32bit($ip_unsigned)
    {
        return long2ip((float)$ip_unsigned);
    }
}


/***********************************************************************
 * Name: http_response_code
 * Short description: Change the HTTP response code for 4.3.0 <= PHP <= 5.4.0
 *
 * Creation 2016-11-04
 * Update   2016-11-04
 * @version 1.0.0
 *
 * @param   string  $code_to_send  HTTP response code to be send
 * @return  string                 Current response code
 ***********************************************************************/
// For 4.3.0 <= PHP <= 5.4.0
if (!function_exists('http_response_code'))
{
    function http_response_code($code_to_send = 0)
    {
        $actual_code = 200;
        if ($code_to_send != 0) {
            header('X-Response-Code: '.$code_to_send, true, $code_to_send);
            if (!headers_sent()) {
                $actual_code = $code_to_send;
            }
        }       
        return $actual_code;
    }
}


/***********************************************************************
 * Name: json_encode
 * Short description: Define the custom function json_encode
 *   if it is not available in the actual configuration
 *   (because this function is natively available only for PHP >= 5.2.0,
 *    or when the extension is activated)
 *
 * Creation 2013-10-??
 * Update   2014-01-08
 * @version 1.0.0
 * @author  eep2004@ukr.net (only function_exists added by SysCo/al)
 *
 * @param   string  $val  Value to encode in JSON
 * @return  string        JSON encoded value
 ***********************************************************************/
if (!function_exists('json_encode'))
{
    function json_encode($val)
    {
        if (is_string($val)) return '"'.addslashes($val).'"';
        if (is_numeric($val)) return $val;
        if ($val === null) return 'null';
        if ($val === true) return 'true';
        if ($val === false) return 'false';

        $assoc = false;
        $i = 0;
        foreach ($val as $k=>$v){
            if ($k !== $i++){
                $assoc = true;
                break;
            }
        }
        $res = array();
        foreach ($val as $k=>$v){
            $v = json_encode($v);
            if ($assoc){
                $k = '"'.addslashes($k).'"';
                $v = $k.':'.$v;
            }
            $res[] = $v;
        }
        $res = implode(',', $res);
        return ($assoc)? '{'.$res.'}' : '['.$res.']';
    }
}


// Phalanger compatibility
if (!function_exists('memory_get_peak_usage')) {
    function memory_get_peak_usage($real_usage = FALSE)
    {
        return memory_get_usage($real_usage);
    }
}


/***********************************************************************
 * Name: sys_get_temp_dir
 * Short description: Define the custom function sys_get_temp_dir
 *   if it is not available in the actual configuration
 *   (because this function is natively available only for PHP >= 5.2.1)
 *
 * Creation 2017-05-18
 * Update   2017-05-18
 * @version 1.0.0
 * @author  SysCo/al
 *
 * @param   none
 * @return  string  Temporary folder
 ***********************************************************************/
if ( !function_exists('sys_get_temp_dir')) {
  function sys_get_temp_dir() {
    if (!empty($_ENV['TMP'])) { return realpath($_ENV['TMP']); }
    if (!empty($_ENV['TMPDIR'])) { return realpath( $_ENV['TMPDIR']); }
    if (!empty($_ENV['TEMP'])) { return realpath( $_ENV['TEMP']); }
    $tempfile=tempnam(__FILE__,'');
    if (file_exists(dirname($tempfile))) {
      unlink($tempfile);
      return realpath(dirname($tempfile));
    }
    return null;
  }
}


/***********************************************************************
 * Name: hex2bin
 * Short description: Define the custom function hex2bin
 *   if it is not available in the actual configuration
 *   (because this function is natively available only for PHP >= 5.4.0)
 *
 * Creation 2010-06-07
 * Update   2013-02-09
 * @version 2.0.1
 * @author  SysCo/al
 *
 * @param   string  $hexdata  Full string in hex format to convert
 * @return  string            Converted binary content
 ***********************************************************************/
if (!function_exists('hex2bin'))
{
    function hex2bin($hexdata)
    {
        $bindata = '';
        for ($i=0;$i<strlen($hexdata);$i+=2)
        {
            $bindata.=chr(hexdec(substr($hexdata,$i,2)));
        }
        return $bindata;
    }
}


/*******************************************************************
 * Define the custom function str_split
 *   if it is not available in the actual configuration
 *   (because this function is natively available only for PHP >= 5)
 *
 * Source: http://www.php.net/manual/fr/function.str-split.php#84891
 *
 * @author "rrelmy"
 *******************************************************************/
if (!function_exists('str_split'))
{
    function str_split($string,$string_length=1)
    {
        if(strlen($string)>$string_length || !$string_length)
        {
            do
            {
                $c = strlen($string);
                $parts[] = substr($string,0,$string_length);
                $string = substr($string,$string_length);
            }
            while($string !== false);
        }
        else
        {
            $parts = array($string);
        }
        return $parts;
    }
}    


/***********************************************************************
 * Define the custom function hash_hmac
 *   if it is not available in the actual configuration
 *   (because this function is natively available only for PHP >= 5.1.2)
 *
 * Source: http://www.php.net/manual/fr/function.hash-hmac.php#93440
 *
 * @author "KC Cloyd"
 ***********************************************************************/
if (!function_exists('hash_hmac'))
{
echo "\n*DEBUG*: function hash_hmac() created\n";
    function hash_hmac($algo, $data, $key, $raw_output = FALSE) {
        return hash_hmac_php($algo, $data, $key, $raw_output);
    }
}


function hash_hmac_php($algo, $data, $key, $raw_output = FALSE) {
	$algo = strtolower($algo);
	$pack = 'H'.strlen($algo('test'));
	$size = 64;
	$opad = str_repeat(chr(0x5C), $size);
	$ipad = str_repeat(chr(0x36), $size);

	if (strlen($key) > $size)
	{
		$key = str_pad(pack($pack, $algo($key)), $size, chr(0x00));
	}
	else
	{
		$key = str_pad($key, $size, chr(0x00));
	}

	for ($i = 0; $i < strlen($key) - 1; $i++)
	{
		$opad[$i] = $opad[$i] ^ $key[$i];
		$ipad[$i] = $ipad[$i] ^ $key[$i];
	}

	$output = $algo($opad.pack($pack, $algo($ipad.$data)));

	return ($raw_output) ? pack($pack, $output) : $output;
}


/*******************************************************************
 * Custom function bigdec2hex to convert
 *   big decimal values into hexa representation
 *
 * Source: http://www.php.net/manual/fr/function.dechex.php#21086
 *
 * @author joost@bingopaleis.com
 *******************************************************************/
if (!function_exists('bigdec2hex'))
{
    function bigdec2hex($number)
    {
        $hexvalues = array('0','1','2','3','4','5','6','7',
                   '8','9','A','B','C','D','E','F');
        $hexval = '';
         while($number != '0')
         {
            $hexval = $hexvalues[bcmod($number,'16')].$hexval;
            $number = bcdiv($number,'16',0);
        }
        return $hexval;
    }
}


/***********************************************************************
 * Custom function providing base32_encode
 *   if it is not available in the actual configuration
 *
 * Source: Bryan Ruiz (https://www.php.net/manual/fr/function.base-convert.php#102232)
 ***********************************************************************/
if (!function_exists('base32_encode'))
{
    /**
     *    Use padding false when encoding for urls
     *
     * @return base32 encoded string
     * @author Bryan Ruiz
     **/
    function base32_encode($input, $padding = true)
    {
        $map = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );
       
        if(empty($input)) return "";
        $input = str_split($input);
        $binaryString = "";
        for($i = 0; $i < count($input); $i++) {
            $binaryString .= str_pad(base_convert(ord($input[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32 = "";
        $i=0;
        while($i < count($fiveBitBinaryArray)) {   
            $base32 .= $map[base_convert(str_pad($fiveBitBinaryArray[$i], 5,'0'), 2, 10)];
            $i++;
        }
        if($padding && ($x = strlen($binaryString) % 40) != 0) {
            if($x == 8) $base32 .= str_repeat($map[32], 6);
            else if($x == 16) $base32 .= str_repeat($map[32], 4);
            else if($x == 24) $base32 .= str_repeat($map[32], 3);
            else if($x == 32) $base32 .= $map[32];
        }
        return $base32;
    }
}


/***********************************************************************
 * Custom function providing base32_decode
 *   if it is not available in the actual configuration
 *
 * Source: Bryan Ruiz (https://www.php.net/manual/fr/function.base-convert.php#102232)
 *         (patched to be able to decode correctly non-8 chars multiple length)
 ***********************************************************************/
if (!function_exists('base32_decode'))
{
    function base32_decode($input)
    {
        $map = array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );

        $flippedMap = array(
            'A'=>'0', 'B'=>'1', 'C'=>'2', 'D'=>'3', 'E'=>'4', 'F'=>'5', 'G'=>'6', 'H'=>'7',
            'I'=>'8', 'J'=>'9', 'K'=>'10', 'L'=>'11', 'M'=>'12', 'N'=>'13', 'O'=>'14', 'P'=>'15',
            'Q'=>'16', 'R'=>'17', 'S'=>'18', 'T'=>'19', 'U'=>'20', 'V'=>'21', 'W'=>'22', 'X'=>'23',
            'Y'=>'24', 'Z'=>'25', '2'=>'26', '3'=>'27', '4'=>'28', '5'=>'29', '6'=>'30', '7'=>'31'
        );

        if(empty($input)) return;
        $paddingCharCount = substr_count($input, $map[32]);
        $allowedValues = array(6,4,3,1,0);
        if(!in_array($paddingCharCount, $allowedValues)) return false;
        for($i=0; $i<4; $i++){
            if($paddingCharCount == $allowedValues[$i] &&
                substr($input, -($allowedValues[$i])) != str_repeat($map[32], $allowedValues[$i])) return false;
        }
        $input = str_replace('=','', $input);
        $result_length = intval((5 * strlen($input)) / 8);
        $input = str_split($input);
        $binaryString = "";
        for($i=0; $i < count($input); $i = $i+8) {
            $x = "";
            if(!in_array($input[$i], $map)) return false;
            for($j=0; $j < 8; $j++) {
                if (!is_null(@$flippedMap[@$input[$i + $j]])) {
                  $x .= str_pad(base_convert(@$flippedMap[@$input[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
                }
            }
            $eightBits = str_split($x, 8);
            for($z = 0; $z < count($eightBits); $z++) {
                if (!is_null($eightBits[$z])) {
                  $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y:"";
                }
            }
        }
        return substr($binaryString, 0, $result_length);
    }
}


/*******************************************************************
 * Custom function encode_utf8_if_needed (now also décoding octal notation)
 *
 * @author SysCo/al
 *******************************************************************/
/***********************************************************************
 * Name: encode_utf8_if_needed
 * Short description: encode to UTF-8 if needed, and also converting ISO octal notation
 *
 * Creation 2022-05-20
 * Update   2021-03-14
 * @version 1.1.0
 * @author  SysCo/al
 *
 * @param   string  $data   string to encode if needed
 * @return  string          UTF-8 string
 ***********************************************************************/
if (!function_exists('encode_utf8_if_needed')) {
	function encode_utf8_if_needed(
    $data
  ) {
		$text = $data;

    preg_match_all('#\\\\[0-9]{3}#', $text, $matches);
    foreach($matches[0] as $match){
      $char = preg_replace("#(\\\)#", "", $match);
      $a = pack("H*", base_convert($char, 8, 16));
      $text = preg_replace('#(\\\\)'.$char.'#',$a,$text);
    }
		$encoding = mb_detect_encoding($text . 'a' , 'UTF-8, ISO-8859-1, WINDOWS-1252');
    if ("UTF-8" != $encoding) {
      $text = mb_convert_encoding($text, "UTF-8", "UTF-8, ISO-8859-1, WINDOWS-1252");
		}
		return $text;
	}
}


/*******************************************************************
 * Custom function decode_utf8_if_needed
 *
 * @author SysCo/al
 *******************************************************************/
if (!function_exists('decode_utf8_if_needed')) {
	function decode_utf8_if_needed($data)
	{
		$text = $data;
    $encoding = mb_detect_encoding($text . 'a' , 'UTF-8, ISO-8859-1, WINDOWS-1252');
    if ("UTF-8" == $encoding) {
      $text = mb_convert_encoding($text, "ISO-8859-1", "UTF-8, ISO-8859-1, WINDOWS-1252");
		}
		return $text;
	}
}


/*
 * SHA-256 (stub for phpseclib version)
 */
if (!function_exists('sha256'))
{
    function sha256($str)
    {
        $ch = new Crypt_Hash();
        return bin2hex($ch->_sha256($str));
    }
}


################################################################################
# #
# MD4 pure PHP edition by DKameleon (http://dkameleon.com) #
# #
# A PHP implementation of the RSA Data Security, Inc. MD4 Message #
# Digest Algorithm, as defined in RFC 1320. #
# Based on JavaScript realization taken from: http://pajhome.org.uk/crypt/md5/ #
# #
# Updates and new versions: http://my-tools.net/md4php/ #
# #
# Adapted by SysCo/al #
# #
################################################################################
if (!function_exists('md4'))
{
    class MultiotpMD4
    {
        var $sa_mode = 0; // safe_add mode. got one report about optimization

        function __construct($init = true)
        {
            if ($init) { $this->Init(); }
        }


        function Init()
        {
            $this->sa_mode = 0;
            $result = $this->Calc('12345678') == '012d73e0fab8d26e0f4d65e36077511e';
            if ($result) { return true; }

            $this->sa_mode = 1;
            $result = $this->Calc('12345678') == '012d73e0fab8d26e0f4d65e36077511e';
            if ($result) { return true; }

            die('MD4 Init failed. Please send bugreport.');
        }


        function str2blks($str)
        {
            $nblk = ((strlen($str) + 8) >> 6) + 1;
            for($i = 0; $i < $nblk * 16; $i++) $blks[$i] = 0;
            for($i = 0; $i < strlen($str); $i++)
                $blks[$i >> 2] |= ord($str[$i]) << (($i % 4) * 8);
            $blks[$i >> 2] |= 0x80 << (($i % 4) * 8);
            $blks[$nblk * 16 - 2] = strlen($str) * 8;
            return $blks;
        }


        function safe_add($x, $y)
        {
            if ($this->sa_mode == 0) {
                return ($x + $y) & 0xFFFFFFFF;
            }

            $lsw = ($x & 0xFFFF) + ($y & 0xFFFF);
            $msw = ($x >> 16) + ($y >> 16) + ($lsw >> 16);
            return ($msw << 16) | ($lsw & 0xFFFF);
        }


        function zeroFill($a, $b)
        {
            $z = hexdec(80000000);
            if ($z & $a) {
                $a >>= 1;
                $a &= (~$z);
                $a |= 0x40000000;
                $a >>= ($b-1);
            } else {
                $a >>= $b;
            }
            return $a;
        }


        function rol($num, $cnt)
        {
            return ($num << $cnt) | ($this->zeroFill($num, (32 - $cnt)));
        }


        function cmn($q, $a, $b, $x, $s, $t)
        {
            return $this->safe_add($this->rol($this->safe_add($this->safe_add($a, $q), $this->safe_add($x, $t)), $s), $b);
        }


        function ffMD4($a, $b, $c, $d, $x, $s)
        {
            return $this->cmn(($b & $c) | ((~$b) & $d), $a, 0, $x, $s, 0);
        }


        function ggMD4($a, $b, $c, $d, $x, $s)
        {
            return $this->cmn(($b & $c) | ($b & $d) | ($c & $d), $a, 0, $x, $s, 1518500249);
        }


        function hhMD4($a, $b, $c, $d, $x, $s)
        {
            return $this->cmn($b ^ $c ^ $d, $a, 0, $x, $s, 1859775393);
        }


        function Calc($str, $raw = false)
        {
            $x = $this->str2blks($str);

            $a =  1732584193;
            $b = -271733879;
            $c = -1732584194;
            $d =  271733878;

            for($i = 0; $i < count($x); $i += 16)
            {
                $olda = $a;
                $oldb = $b;
                $oldc = $c;
                $oldd = $d;

                $a = $this->ffMD4($a, $b, $c, $d, $x[$i+ 0], 3 );
                $d = $this->ffMD4($d, $a, $b, $c, $x[$i+ 1], 7 );
                $c = $this->ffMD4($c, $d, $a, $b, $x[$i+ 2], 11);
                $b = $this->ffMD4($b, $c, $d, $a, $x[$i+ 3], 19);
                $a = $this->ffMD4($a, $b, $c, $d, $x[$i+ 4], 3 );
                $d = $this->ffMD4($d, $a, $b, $c, $x[$i+ 5], 7 );
                $c = $this->ffMD4($c, $d, $a, $b, $x[$i+ 6], 11);
                $b = $this->ffMD4($b, $c, $d, $a, $x[$i+ 7], 19);
                $a = $this->ffMD4($a, $b, $c, $d, $x[$i+ 8], 3 );
                $d = $this->ffMD4($d, $a, $b, $c, $x[$i+ 9], 7 );
                $c = $this->ffMD4($c, $d, $a, $b, $x[$i+10], 11);
                $b = $this->ffMD4($b, $c, $d, $a, $x[$i+11], 19);
                $a = $this->ffMD4($a, $b, $c, $d, $x[$i+12], 3 );
                $d = $this->ffMD4($d, $a, $b, $c, $x[$i+13], 7 );
                $c = $this->ffMD4($c, $d, $a, $b, $x[$i+14], 11);
                $b = $this->ffMD4($b, $c, $d, $a, $x[$i+15], 19);

                $a = $this->ggMD4($a, $b, $c, $d, $x[$i+ 0], 3 );
                $d = $this->ggMD4($d, $a, $b, $c, $x[$i+ 4], 5 );
                $c = $this->ggMD4($c, $d, $a, $b, $x[$i+ 8], 9 );
                $b = $this->ggMD4($b, $c, $d, $a, $x[$i+12], 13);
                $a = $this->ggMD4($a, $b, $c, $d, $x[$i+ 1], 3 );
                $d = $this->ggMD4($d, $a, $b, $c, $x[$i+ 5], 5 );
                $c = $this->ggMD4($c, $d, $a, $b, $x[$i+ 9], 9 );
                $b = $this->ggMD4($b, $c, $d, $a, $x[$i+13], 13);
                $a = $this->ggMD4($a, $b, $c, $d, $x[$i+ 2], 3 );
                $d = $this->ggMD4($d, $a, $b, $c, $x[$i+ 6], 5 );
                $c = $this->ggMD4($c, $d, $a, $b, $x[$i+10], 9 );
                $b = $this->ggMD4($b, $c, $d, $a, $x[$i+14], 13);
                $a = $this->ggMD4($a, $b, $c, $d, $x[$i+ 3], 3 );
                $d = $this->ggMD4($d, $a, $b, $c, $x[$i+ 7], 5 );
                $c = $this->ggMD4($c, $d, $a, $b, $x[$i+11], 9 );
                $b = $this->ggMD4($b, $c, $d, $a, $x[$i+15], 13);

                $a = $this->hhMD4($a, $b, $c, $d, $x[$i+ 0], 3 );
                $d = $this->hhMD4($d, $a, $b, $c, $x[$i+ 8], 9 );
                $c = $this->hhMD4($c, $d, $a, $b, $x[$i+ 4], 11);
                $b = $this->hhMD4($b, $c, $d, $a, $x[$i+12], 15);
                $a = $this->hhMD4($a, $b, $c, $d, $x[$i+ 2], 3 );
                $d = $this->hhMD4($d, $a, $b, $c, $x[$i+10], 9 );
                $c = $this->hhMD4($c, $d, $a, $b, $x[$i+ 6], 11);
                $b = $this->hhMD4($b, $c, $d, $a, $x[$i+14], 15);
                $a = $this->hhMD4($a, $b, $c, $d, $x[$i+ 1], 3 );
                $d = $this->hhMD4($d, $a, $b, $c, $x[$i+ 9], 9 );
                $c = $this->hhMD4($c, $d, $a, $b, $x[$i+ 5], 11);
                $b = $this->hhMD4($b, $c, $d, $a, $x[$i+13], 15);
                $a = $this->hhMD4($a, $b, $c, $d, $x[$i+ 3], 3 );
                $d = $this->hhMD4($d, $a, $b, $c, $x[$i+11], 9 );
                $c = $this->hhMD4($c, $d, $a, $b, $x[$i+ 7], 11);
                $b = $this->hhMD4($b, $c, $d, $a, $x[$i+15], 15);

                $a = $this->safe_add($a, $olda);
                $b = $this->safe_add($b, $oldb);
                $c = $this->safe_add($c, $oldc);
                $d = $this->safe_add($d, $oldd);
            }
            $x = pack('V4', $a, $b, $c, $d);
            return $raw ? $$x : bin2hex($x);
        }
    }
    function md4($str)
    {
        $calc_md4 = new MultiotpMD4();
        return $calc_md4->Calc($str);
    }
}


/***********************************************************************
 * Name: hash
 * Short description: Define the custom function hash
 *   if it is not available in the actual configuration
 *   (because this function is natively available only for PHP >= 5.1.2)
 *
 * Creation 2013-08-14
 * Update   2013-08-14
 * @version 1.0.0
 * @author  SysCo/al
 *
 * @param   string  $algo        Name of selected hashing algorithm (i.e. "md5", "sha256", etc..) 
 * @param   string  $data        Message to be hashed
 * @param   string  $raw_output  When set to TRUE, outputs raw binary data. FALSE outputs lowercase hexits. 
 * @return  string               Calculated message digest as lowercase (or binary)
 ***********************************************************************/
if (!function_exists('hash'))
{
    function hash($algo, $data, $raw_output = FALSE)
    {
        $result = '';
        switch (strtolower($algo))
        {
            case 'md4':
                $result = strtolower(md4($data));
                break;
            case 'md5':
                $result = strtolower(md5($data));
                break;
            case 'sha1':
                $result = strtolower(sha1($data));
                break;
            case 'sha256':
                $result = strtolower(sha256($data));
                break;
            default:
                $result = '';
                break;
        }
        if ($raw_output)
        {
            $result = hex2bin($result);
        }
        return $result;
    }
}


/**
 * Remove the directory and its content (all files and subdirectories).
 * @param string $dir the directory name
 *
 * wang yun (2010)
 */
if (!function_exists('rmrf')) {
    function rmrf($dir) {
        foreach (glob($dir) as $file) {
            if (is_dir($file)) {
                rmrf("$file/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}


/***********************************************************************
 * Name: html2text
 * Short description: Convert html to text
 *   Based on http://snipplr.com/view/57982/convert-html-to-text/
 *
 * Creation 2011-08-18 kendsnyder
 * Update   2021-03-23
 * @version 2.0.0
 * @author  SysCo/al
 ***********************************************************************/

if (!function_exists('html2text'))
{
    function html2text($value)
    {
        $Document = $value;
        $Document = str_replace('<p ','<br /><p ',$Document);
        $Document = str_replace('</p>','</p><br />',$Document);
        $Document = str_replace('</tr>','</tr><br />',$Document);
        $Document = str_replace('</th>','</th><br />',$Document);
        $Document = str_replace('</div>','</div><br />',$Document);
        $Document = str_replace('<br />','*CRLF*',$Document);
        
        $Rules = array ('@<script[^>]*?>.*?</script>@si', // Strip out javascript
                        '@<style[^>]*?>.*?</style>@si',   // Strip out style
                        '@<title[^>]*?>.*?</title>@si',   // Strip out title
                        '@<head[^>]*?>.*?</head>@si',     // Strip out head
                        '@<[\/\!]*?[^<>]*?>@si',          // Strip out HTML tags
                        '@([\r\n])[\s]+@',                // Strip out white space
                        '@&(quot|#34);@i',                // Replace HTML entities
                        '@&(amp|#38);@i',                 //   Ampersand &
                        '@&(lt|#60);@i',                  //   Less Than <
                        '@&(gt|#62);@i',                  //   Greater Than >
                        '@&(nbsp|#160);@i',               //   Non Breaking Space
                        '@&(iexcl|#161);@i',              //   Inverted Exclamation point
                        '@&(cent|#162);@i',               //   Cent
                        '@&(pound|#163);@i',              //   Pound
                        '@&(copy|#169);@i',               //   Copyright
                        '@&(reg|#174);@i');               //   Registered
        $Replace = array ('',  // Strip out javascript
                          '',  // Strip out style
                          '',  // Strip out title
                          '',  // Strip out head
                          '',  // Strip out HTML tags
                          ' ',  // Strip out white space
                          '"',  // Replace HTML entities
                          '&',  // Ampersand &
                          '<',  // Less Than <
                          '>',  // Greater Than >
                          ' ',  // Non Breaking Space
                          chr(161), // Inverted Exclamation point
                          chr(162), // Cent
                          chr(163), // Pound
                          chr(169), // Copyright
                          chr(174)); // Registered
        $Document = preg_replace($Rules, $Replace, $Document);
        
        $Document = preg_replace_callback('@&#(d+);@i', function ($match) { return (((intval($match) >= 1) && (intval($match) <= 255)) ? chr(intval($match)) : ''); }, $Document);

        $Document = preg_replace('@[\r\n]@', '', $Document);
        $Document = str_replace('*CRLF*',chr(13).chr(10),$Document);
        $Document = preg_replace('@[\r\n][ ]+@', chr(13).chr(10), $Document);
        $Document = preg_replace('@[\r\n][\r\n]+@', chr(13).chr(10).chr(13).chr(10), $Document);
        return nullable_trim($Document);
    }
}


/***********************************************************************
 * Name: lastIndexOf
 ***********************************************************************/
if (!function_exists('lastIndexOf'))
{
    function lastIndexOf($haystack, $needle)
    {
        $index = strpos(strrev($haystack), strrev($needle));
        $index = strlen($haystack) - strlen($needle) - $index;
        return $index;
    }
}


/***********************************************************************
 * Custom function escape_mysql_string
 *
 * http://www.php.net/manual/fr/function.mysql-real-escape-string.php#101248
 *
 * @author " feedr"
 ***********************************************************************/
if (!function_exists('escape_mysql_string'))
{
    function escape_mysql_string($string)
    {
        $result = $string;
        if (is_array($result))
            return array_map(__METHOD__, $result);

        if (!empty($result) && is_string($result))
        {
            return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"),
                               array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'),
                               $result
                              );
        }
        return $result;
    }
}


/***********************************************************************
 * Custom function nice_json
 *
 * http://stackoverflow.com/a/9776726
 *
 * @author Kendall Hopkins
 ***********************************************************************/
if (!function_exists('nice_json'))
{
    function nice_json($json, $separator = "\t")
    {
        $result = '';
        $level = 0;
        $in_quotes = false;
        $in_escape = false;
        $ends_line_level = NULL;
        $json_length = strlen( $json );

        for( $i = 0; $i < $json_length; $i++ ) {
            $char = $json[$i];
            $new_line_level = NULL;
            $post = "";
            if( $ends_line_level !== NULL ) {
                $new_line_level = $ends_line_level;
                $ends_line_level = NULL;
            }
            if ( $in_escape ) {
                $in_escape = false;
            } elseif( $char === '"' ) {
                $in_quotes = !$in_quotes;
            } elseif( ! $in_quotes ) {
                switch( $char ) {
                    case '}': case ']':
                        $level--;
                        $ends_line_level = NULL;
                        $new_line_level = $level;
                        break;

                    case '{':
                    case '[':
                        $level++;
                    case ',':
                        $ends_line_level = $level;
                        break;

                    case ':':
                        $post = " ";
                        break;

                    case " ":
                    case "\t":
                    case "\n":
                    case "\r":
                        $char = "";
                        $ends_line_level = $new_line_level;
                        $new_line_level = NULL;
                        break;
                }
            } elseif ( $char === '\\' ) {
                $in_escape = true;
            }
            if( $new_line_level !== NULL ) {
                $result .= "\n".str_repeat($separator, $new_line_level);
            }
            $result .= $char.$post;
        }

        return $result;
    }
}


if (!function_exists('mask2cidr'))
{
    // https://gist.github.com/linickx/1309388
    function mask2cidr($mask) {
        $mask = explode(".", $mask);
        $bits = 0;
        foreach ($mask as $octet) {
            $bin = decbin($octet);
            $bin = str_replace ( "0" , "" , $bin);
            $bits = $bits + strlen($bin);
        }
        return $bits;
    }
}


if (!function_exists('protect_file'))
{
  function protect_file(
    $file,
    $sid
  ) {
    if (mb_strtolower(mb_substr(PHP_OS, 0, 3),'UTF-8') === 'win') {
      $sidAdmin = 'S-1-5-32-544';
      $sidUsers = 'S-1-5-32-545';
      $sidAuthenticatedUsers = 'S-1-5-11';
      exec("icacls \"$file\" /grant *$sid:F");
      exec("icacls \"$file\" /grant *$sidAdmin:F");
      exec("icacls \"$file\" /inheritance:r /remove:g *$sidUsers");
      exec("icacls \"$file\" /inheritance:r /remove:g *$sidAuthenticatedUsers");
    }
  }
}
?>