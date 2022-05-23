<?php
class IO
{
	private static $priv_key = __dir__ . '/../settings/priv.key';
	public static function user($name, $token)
	{
		return ISQL::queryOne(str_replace('<:session:>', $token, constant('USER_QUERY_' . $name)));
	}

	public static function headers()
	{
		$arh = array();
		$rx_http = '/\AHTTP_/';
		foreach ($_SERVER as $key => $val) {
			if (preg_match($rx_http, $key)) {
				$arh_key = preg_replace($rx_http, '', $key);
				$rx_matches = array();
				// do some nasty string manipulations to restore the original letter case
				// this should work in most cases
				$rx_matches = explode('_', $arh_key);
				if (count($rx_matches) > 0 and strlen($arh_key) > 2) {
					foreach ($rx_matches as $ak_key => $ak_val) $rx_matches[$ak_key] = ucfirst($ak_val);
					$arh_key = implode('-', $rx_matches);
				}
				$arh[$arh_key] = $val;
			}
		}
		return ($arh);
	}

	public static function execute($s, $d = array(), $ct = 'application/json;')
	{

		$_SERVER['LANG_SELECTED'] = self::headers()['LANGUAGE'] ? self::headers()['LANGUAGE'] : 'es';

		try {
			$controller = explode('@', $s)[0];
			$method = explode('@', $s)[1];
			$x = new ReflectionClass($controller);

			if (!method_exists($controller, $method)) {
				IOException::set('ICNOMTHD', 404);
			}
			$x = $x->getMethod($method);
			$x = $x->getParameters();
			$v = array();
			if (count($x) !== count($d)) {
				//self::exception('INVALIDARGUMENTSQUANTITY',404);
			}
			if (is_array($d)) {
				foreach ($x as $i => $p) {
					// if(isset($d[$p->name])){
					// 	$v[] = $d[$p->name];
					// }
					$v[] = $d[$p->name];
				}
			} else {
				$v[] = $d;
			}

			self::output(self::call($controller, $method, $v), 200, $ct);
		} catch (Exception $e) {
			$error_message = json_decode($e->getMessage(), true);
			if (!is_array($error_message)) {
				$error_message = $e->getMessage();
			}
			IOException::set($error_message, $e->getCode(), $ct);
		}
	}

	public static function call($controller, $method, $v)
	{
		try {
			return call_user_func_array($controller . '::' . $method, $v);
		} catch (ArgumentCountError $e) {
			echo $e->getMessage();
		}
	}

	public static function output($response, $status_code = 200, $ct = 'application/json;')
	{
		http_response_code($status_code);
		header('Content-Type: ' . $ct . ' charset=utf-8');
		$output = array(
			'status' 		=> IOLanguage::translate('STATUS_CODE_' . $status_code),
			'status_code' 	=> $status_code,
			'response' 		=> $response
		);
		switch ($ct) {
			case 'text/html;':
				echo $response;
				break;
			default:
				echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
				break;
		}

		die();
	}

	public static function request($arr)
	{

		$ops = array(
			CURLOPT_URL => $arr['URL'],
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => $arr['METHOD']
		);
		if (isset($arr['HEADER'])) {
			$ops[CURLOPT_HTTPHEADER] = $arr['HEADER'];
		}
		if (isset($arr['USERPWD'])) {
			$ops[CURLOPT_USERPWD] = $arr['USERPWD'];
		}
		if (isset($arr['POST'])) {
			$ops[CURLOPT_POST] = $arr['POST'];
		}
		if (isset($arr['POSTFIELDS'])) {
			$ops[CURLOPT_POSTFIELDS] = $arr['POSTFIELDS'];
		}

		$ch = curl_init();
		curl_setopt_array($ch, $ops);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, true);
		//Errors::manage($response);

		return $response;
	}
}
