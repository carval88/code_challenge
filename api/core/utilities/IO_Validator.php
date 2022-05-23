<?php
class IO_Validator {
	public static function isInt($dato){
		return intval($dato)==$dato;
	}
	public static function isFloat($dato){
		return floatval($dato)==$dato;
	}
	public static function isEmail($dato){
		return (ereg('^[a-z]+([\.]?[a-z0-9_-]+)*@[a-z0-9]+([\.-]+[a-z0-9]+)*\.[a-z]{2,3}$', strtolower($dato) ));
	}
	public static function isDate($dato){

		if ( ereg( "([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})", $dato, $r ) ) {
			return true;
		} else if ( ereg( "([0-9]{1,4})-([0-9]{1,2})-([0-9]{1,2})", $dato, $r ) ) {
			return true;
		} else if (preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $dato, $r)) { 
			if (checkdate($r[2], $r[3], $r[1])) { 
				return true; 
			} 
    	}  else {
			return false;
		}
	}
	public static function getExtension($dato){
		$res = explode('.', $dato); 
		if(isset($res[count($res)-1])){
			return strtolower($res[count($res)-1]);
		}else{
			return '';
		}
	}
	public static function isExtension($dato, $parametros){
		$res = explode('.', $dato);
		return (
			in_array(
				(isset($res[count($res)-1]))?strtolower($res[count($res)-1]):'', 
				$parametros
			)
			||
			in_array(
				(isset($res[count($res)-1]))?strtoupper($res[count($res)-1]):'', 
				$parametros
			)
		);
	}
    public static function onlyChars($String, $PermitidosList='abcdefghijklmnopqrstuvwxyz0123456789-_'){
		for($i=0 ; $i < strlen($String) ; $i++){
			if(strpos($PermitidosList, strtolower($String[$i])) === false){
				return false;
			}
		}
        return true;
	}
}
