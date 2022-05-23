<?php
class IO_JWT
{
	private static $priv_key = __dir__ . '/../../settings/priv.key';

	public static function create($sub, $aud, $iss, $extra = [], $scopes = [])
	{
		$data = [
			'nbf'	 =>  time(), 					// Fecha y hora en formato segundos en que el token comienza a ser válido
			'sub' 	 =>  $sub, 				// Identifica el objeto o usuario en nombre del cual fue emitido el JWT en inicio seguro es el id del usuario en la base de datos
			'exp'	 =>  time() + 3600, 				// Fecha y hora en formato segundos en que el token expira
			'aud'    => $aud,	 		//Identifica la audiencia o receptores para lo que el JWT fue emitido, normalmente el/los servidor/es de recursos (e.g. la API protegida). Cada servicio que recibe un JWT para su validación tiene que controlar la audiencia a la que el JWT está destinado. Si el proveedor del servicio no se encuentra presente en el campo aud, entonces el JWT tiene que ser rechazado
			'iat'	 =>  time(), 					//Identifica la marca temporal en qué el JWT fue emitido.
			'iss'    => $iss 		//Identifica el proveedor de identidad que emitió el JWT

		];

		if (!empty($extra)) {
			$data = array_merge($data, array('extra' => $extra));
		}

		$access_token   = (new JWT(self::$priv_key, 'RS512', 3600))->encode($data);

		//$payload = (new JWT('topSecret', 'HS512', 1800))->decode($token);

		$result = [
			'access_token' 	=> $access_token,
			'expires_in'	=> 3600,
			'token_type'	=> 'Bearer',
			'scopes' 		=> $scopes
		];

		return $result;
	}


	public static function sub(bool $db_validate = false)
	{

		$token = (defined('AUTH_TOKEN') ? AUTH_TOKEN : (defined('AUTH_BRIDGE_TOKEN') ? AUTH_BRIDGE_TOKEN : ''));

		try {
			$TK = (new JWT(self::$priv_key, 'HS256', 3600))->decode($token, false);
			// retorna el identificador del token tanto para apis bbapp y apis bbapp wordpress
			$sub = isset($TK['sub']) ? $TK['sub'] : $TK['data']->user->id;
			if ($db_validate) {
				return self::checkDbToken('user', $sub);
			}
			return $sub;
		} catch (Exception $e) {
			IO::output(IOLanguage::translate('invalid-token'), 401);
			die;
		}
	}

	public static function checkDbToken(string $name = '', string $token = '')
	{
		try {
			$st = mysqli_prepare(ISQL::on(), constant('API_USER_QUERY_' . $name));
			if (!$st)
				throw new Exception(mysqli_error(ISQL::on()));

			$st->bind_param(
				's',
				$token
			);
			$st->execute();
			$r = $st->get_result();
			if (!$r)
				throw new Exception(mysqli_error(ISQL::on()));

			$user = $r->fetch_object(User::class);
			if (isset($user))
				return $user;

			IO::output(IOLanguage::translate('invalid-token'), 401);
		} catch (\Throwable $th) {
			IOException::set($th->getMessage());
		}
	}
}
