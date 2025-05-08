<?php

/*
* ip_in_range.php - Function to determine if an IP is located in a
*                   specific range as specified via several alternative
*                   formats.
*
* Network ranges can be specified as:
* 1. Wildcard format:     1.2.3.*
* 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
* 3. Start-End IP format: 1.2.3.0-1.2.3.255
*
* Return value BOOLEAN : ip_in_range($ip, $range);
*
* Copyright 2008: Paul Gregg <pgregg@pgregg.com>
* 10 January 2008
* Version: 1.2
*
* Source website: http://www.pgregg.com/projects/php/ip_in_range/
* Version 1.2
*
* This software is Donationware - if you feel you have benefited from
* the use of this tool then please consider a donation. The value of
* which is entirely left up to your discretion.
* http://www.pgregg.com/donate/
*
* Please do not remove this header, or source attibution from this file.
*/


// decbin32
// In order to simplify working with IP addresses (in binary) and their
// netmasks, it is easier to ensure that the binary strings are padded
// with zeros out to 32 characters - IP addresses are 32 bit numbers
Function decbin32 ($dec) {
  return str_pad(decbin($dec), 32, '0', STR_PAD_LEFT);
}

// ip_in_range
// This function takes 2 arguments, an IP address and a "range" in several
// different formats.
// Network ranges can be specified as:
// 1. Wildcard format:     1.2.3.*
// 2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
// 3. Start-End IP format: 1.2.3.0-1.2.3.255
// The function will return true if the supplied IP is within the range.
// Note little validation is done on the range inputs - it expects you to
// use one of the above 3 formats.
Function ip_in_range($ip, $range) {
  if (strpos($range, '/') !== false) {
    // $range is in IP/NETMASK format
    list($range, $netmask) = explode('/', $range, 2);
    if (strpos($netmask, '.') !== false) {
      // $netmask is a 255.255.0.0 format
      $netmask = str_replace('*', '0', $netmask);
      $netmask_dec = ip2long($netmask);
      return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
    } else {
      // $netmask is a CIDR size block
      // fix the range argument
      $x = explode('.', $range);
      while(count($x)<4) $x[] = '0';
      list($a,$b,$c,$d) = $x;
      $range = sprintf("%u.%u.%u.%u", empty($a)?'0':$a, empty($b)?'0':$b,empty($c)?'0':$c,empty($d)?'0':$d);
      $range_dec = ip2long($range);
      $ip_dec = ip2long($ip);

      # Strategy 1 - Create the netmask with 'netmask' 1s and then fill it to 32 with 0s
      #$netmask_dec = bindec(str_pad('', $netmask, '1') . str_pad('', 32-$netmask, '0'));

      # Strategy 2 - Use math to create it
      $wildcard_dec = pow(2, (32-$netmask)) - 1;
      $netmask_dec = ~ $wildcard_dec;

      return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
    }
  } else {
    // range might be 255.255.*.* or 1.2.3.0-1.2.3.255
    if (strpos($range, '*') !==false) { // a.b.*.* format
      // Just convert to A-B format by setting * to 0 for A and 255 for B
      $lower = str_replace('*', '0', $range);
      $upper = str_replace('*', '255', $range);
      $range = "$lower-$upper";
    }

    if (strpos($range, '-')!==false) { // A-B format
      list($lower, $upper) = explode('-', $range, 2);
      $lower_dec = (float)sprintf("%u",ip2long($lower));
      $upper_dec = (float)sprintf("%u",ip2long($upper));
      $ip_dec = (float)sprintf("%u",ip2long($ip));
      return ( ($ip_dec>=$lower_dec) && ($ip_dec<=$upper_dec) );
    }

    echo 'Range argument is not in 1.2.3.4/24 or 1.2.3.4/255.255.255.0 format';
    return false;
  }

}

Function ip_in_ranges($ip, $ranges_array) {
  // Ensure $ranges_array is actually an array and not empty
  if (!is_array($ranges_array) || empty($ranges_array)) {
      // Optionally log an error or warning here
      // echo "Error: The ranges argument must be a non-empty array.";
      return false;
  }

  foreach ($ranges_array as $range) {
      // Use the existing ip_in_range function for each individual range
      if (ip_in_range($ip, $range)) {
          return true; // Found the IP in this range, no need to check further
      }
  }

  // If the loop finishes without finding the IP in any range
  return false;
}





/*
* crear_editar_log - Función PHP para hacer escritura en un archivo de texo
*                   plano definido por el usuario, con el objetivo de registrar
*                   un log de eventos ocurridos en una ejecució web
* 
* Copyright 2025: Jaime Jeovanny Cortez Flores <jaime.jeovanny.cortez.flores@gmail.com>
* 07 mayo del 2025
* Version: 1.0
*/
function crear_editar_log($ruta_archivo,$contenido,$tipo,$ip,$referer,$useragent){
  //Se define los tipos de log que queremos registrar
  $arr_tipo_log = array("[info]:","[notice]:","[warning]:","[error]:");
  //Se obtiene la fecha y la hora actual
	$now = DateTime::createFromFormat('U.u', microtime(true));
  //Se aplica la zona horaria que necesitamos
	$now->setTimeZone(new DateTimeZone('America/El_Salvador'));
  //Se verifica si existe el archivo de log
	if ( file_exists($ruta_archivo) ){
    //Si existe, por lo tanto, únicamente haremos escritura en él, entonces,
    //Primero se abre el apuntador del archivo.
		$archivo = fopen($ruta_archivo, "a");
    //Se hace escritura en la última línea.
		fwrite($archivo, PHP_EOL .$now->format("m-d-Y H:i:s.u T")." $ip $arr_tipo_log[$tipo] referer: $referer $contenido $useragent");
		//Se cierra la escritura
    fclose($archivo);
	}else{
    //Se crea la carpeta "logs"
    mkdir("logs",0777,true);
    //Se apertura el archivo con permisos de escritura
		$archivo = fopen($ruta_archivo, "w+");
    //Se se escribe en la primer línea del archivo
		fwrite($archivo, PHP_EOL .$now->format("m-d-Y H:i:s.u T")." $ip $arr_tipo_log[$tipo] referer: $referer $contenido $useragent");
    //Se cierra la escritura
		fclose($archivo);
	}
}
?>