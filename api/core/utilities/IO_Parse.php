<?php 
class IParse {
	public static function encode(&...$li){
		foreach ($li as $i => &$s) {
			# if(strpos($s, '<br />')>=0||strpos($s, '&amp;')>=0||strpos($s, '&aacute;')>=0||strpos($s, '&eacute;')>=0||strpos($s, '&iacute;')>=0||strpos($s, '&oacute;')>=0||strpos($s, '&uacute;')>=0||strpos($s, '&ntilde;')>=0){ AFParse::decode($s); }
			// Dejar solamente comillas dobles
			$s = str_replace("\\", '', $s);
			$s = str_replace("'", '\'', $s);
			$s = str_replace("`", '\'', $s);
			// Convertir a entidades
			$s = htmlentities($s,ENT_IGNORE,'UTF-8'); 
			#$s = str_replace('"', '&quot;', $s);
			// Se obtiene numeros, letras & y signos ;., _
			$s = preg_replace('/[^0-9A-Za-z\<\>\(\)\{\} \!\¡\¿\?\/\=\%\@\$\.\_\:\+\-\*\&\,\;\¬\#\|\~\[\]]/','',$s); 
			$s = str_replace("\r\n", "<br />", $s);
		}
	}
	public static function escape(&...$li){
		foreach ($li as $i => &$s) {
			// Dejar solamente comillas dobles
			$s = str_replace("\\", '', $s);
			$s = str_replace("'", '\'', $s);
			//$s = addslashes($s);
			//$s = str_replace("'", "`", $s);
		}
	}
	public static function decode(&...$li){
		foreach ($li as $i => &$s) {
			$s = str_replace("<br />","\n",$s);
			$s = html_entity_decode($s, ENT_IGNORE, 'UTF-8');
		}
	}
	public static function email(&...$li){
		foreach ($li as $i => &$e) {
			if(!preg_match('/^[a-z]+([\.]?[a-z0-9_-]+)*@[a-z0-9]+([\.-]+[a-z0-9]+)*\.[a-z]{2,3}$/', strtolower($e) )){
				CORE::exception('ICIVLDML');
			}
		}
	}
	public static function date(&...$li){
		foreach ($li as $i => &$e) {
			$isValid = false;
			if(preg_match("/([0-9]{1,2}):([0-9]{1,2})$/",$e,$r)){
				$isValid = true;
			}else if(preg_match("/([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$/",$e,$r)){
				$isValid = true;
			}else if(preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})$/",$e,$r)){
				$isValid = true;
			}else if(preg_match("/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/",$e,$r)){ 
				if(checkdate($r[2],$r[3],$r[1])){
					$isValid = true;
				}
	    	}
	    	if(!$isValid){
	    		CORE::exception('ADIVLDT');
	    	}
		}
	}
	public static function int(&...$li){
		foreach ($li as $i=>&$n) {
			if(is_numeric($n)){
				$n = floor((float)$n);
			}else{
				$n = 0;
			}
			//$n = preg_replace('/\D/','',$n);
			//$n = $n==''?0:$n;
	    }
	}
	public static function bool(&...$li){
		foreach ($li as $i=>&$b) {
			$b = ($b=='off'?0:($b=='on'?1:($b==1?1:0)));
	    }
	}
	public static function float(&...$li){
		foreach ($li as $i => &$f) {
			$f = str_replace(',','.',$f);
			if(is_numeric($f)){
				$f = (float)$f==floor($f)?floor($f):(float)$f; 
			}else{
				$f = 0;
			}
			//$f = str_replace(',','.',preg_replace('/[^0-9.,-]/','',$f));
			//$f = $f==''?0:$f;
		}
	}
	public static function fromBase64(&...$li){
		foreach ($li as $i => &$s) {
			$s = addslashes(base64_decode(str_replace(' ','+', $s)));
		}
	}
	public static function blob(&$f,$formatosPermitidos=array(),$autoResizeTo=0){ 
		self::int($autoResizeTo);
		if($f['error']==1){
			IOException::set(str_replace('<:size:>', ini_get('upload_max_filesize'), CORE::_('AFMXUPLSZ')));
		}
		if(is_array($f) and isset($f['tmp_name']) and $f['tmp_name']!='' and $f['size']!=''){
			$file = file_get_contents($f['tmp_name']);
			
			if(isset($formatosPermitidos[0])){
				if(!AFValidator::isExtension($f['name'],$formatosPermitidos)){
					$tipos = '';
					$ctipos = count($formatosPermitidos);
					foreach($formatosPermitidos as $i=>$a){
						$tipos .= ($i==0?'':(($i==$ctipos-1)?' o ':', ')).$a;
					}
					throw new Exception(
						str_replace(
							'<:file:>', 
							$f['name'], 
							str_replace(
								'<:types:>', 
								$tipos, 
								AF::_('AFFLFRMTNTSPPTD')
							)
						)
					);
				}
			}
			if($autoResizeTo>0){
				if(AFValidator::isExtension($f['name'],array('jpeg','png'))){
					$file = AFImage::Cuadro($autoResizeTo,$file,true);  
				}
			}
			$f = $file;
		}else{
			CORE::exception('AFSLCTFL');
		}
	}

	public static function number_format(&...$li){
		foreach ($li as $i => &$f) {
			$dec = explode('.', $f);
			$dec = $dec[1]>0?2:0;
			$f = number_format($f,$dec,",",".");
		}
	}
}
