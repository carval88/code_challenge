<?php
class Route
{

	var $prefix = '/';
	var $routes = array(
		'POST' 		=> array(),
		'GET' 		=> array(),
		'PUT'	 	=> array(),
		'DELETE' 	=> array()
	);

	var $db_host = '';
	var $db_user = '';
	var $db_pass = '';
	var $db_name = '';
	var $db_port = '';
	var $url     = '';
	var $tbk_env = '';
	var $env = '';

	//public function __construct($prefix = '', $db_host = 'localhost', $db_user = '', $db_pass = '', $db_name = '',$url = '', $tbk_env = 'TEST', $env = 'DEV'){
	public function __construct($p)
	{
		$this->prefix .= $p['prefix'] ?? '';

		$this->db_host = $p['db_host'] ?? 'localhost';
		$this->db_user = $p['db_user'] ?? 'root';
		$this->db_pass = $p['db_pass'] ?? 'root';
		$this->db_name = $p['db_name'] ?? '';
		$this->db_port = $p['db_port'] ?? '3306';
		$this->url = $p['url'] ?? '';
		$this->tbk_env = $p['tbk_env'] ?? 'TEST';
		$this->env = $p['env'] ?? 'DEV';
	}

	public function pathNotFound($path)
	{
		header('HTTP/1.0 404 Not Found');
		die();
	}
	public function methodNotAllowed($path, $method)
	{
		header('HTTP/1.0 405 Method Not Allowed');
		die();
	}
	public function methodNotAuthorized($path, $method)
	{
		header('HTTP/1.0 401 Unauthorized');
		die();
	}
	public function methodNotImplemented($method)
	{
		header('HTTP/1.0 501 Not Implemented');
		die();
	}

	public function post($exp, $fn, $auth = false)
	{
		$this->validate_AuthType($auth);
		array_push(
			$this->routes['POST'],
			array(
				'exp' 	=> $exp,
				'fn' 		=> $fn,
				'auth' 	=> $auth
			)
		);
	}

	public function get($exp, $fn, $auth = false)
	{
		$this->validate_AuthType($auth);
		array_push(
			$this->routes['GET'],
			array(
				'exp' 	=> $exp,
				'fn' 		=> $fn,
				'auth' 	=> $auth
			)
		);
	}

	public function put($exp, $fn, $auth = false)
	{
		$this->validate_AuthType($auth);
		array_push(
			$this->routes['PUT'],
			array(
				'exp' 	=> $exp,
				'fn' 		=> $fn,
				'auth' 	=> $auth
			)
		);
	}
	public function delete($exp, $fn, $auth = false)
	{
		$this->validate_AuthType($auth);
		array_push(
			$this->routes['DELETE'],
			array(
				'exp' 	=> $exp,
				'fn' 		=> $fn,
				'auth' 	=> $auth
			)
		);
	}

	private function validate_AuthType($auth)
	{
		if ($auth) {
			if (!in_array($auth, array('basic', 'bearer')))
				throw new Exception("Auth type \"" . $auth . "\" is not allowed.", 500);
		}
	}

	private function check($method, $path, $case_matters, $trailing_slash_matters, $multimatch)
	{
		$path_match_found = false;
		$route_match_found = false;
		$out_route = null;
		$out_matches = null;
		// find route in a selected method 
		foreach ($this->routes[$method] as $i => $route) {
			if ($this->prefix != '/') {
				$route['exp'] = '(' . $this->prefix . ')' . $route['exp'];
			}
			// Add 'find string start' automatically
			$route['exp'] = '^' . $route['exp'];

			// Add 'find string end' automatically
			$route['exp'] = $route['exp'] . '$';
			// Check path match

			if (preg_match('#' . $route['exp'] . '#' . ($case_matters ? '' : 'i'), urldecode($path), $matches)) {
				$path_match_found = true;

				array_shift($matches); // Always remove first element. This contains the whole string
				$out_matches = $matches;
				if ($this->prefix != '/') {
					array_shift($matches); // Remove basepath
					$out_matches = $matches;
				}
				$route_match_found = true;
				$out_route = $route;
				break;
			}
		}


		return array(
			'path_match_found' => $path_match_found,
			'route_match_found' => $route_match_found,
			'route' => $out_route,
			'matches' => $out_matches
		);
	}

	public function run($case_matters = false, $trailing_slash_matters = false, $multimatch = false)
	{
		$path_match_found = false;
		$route_match_found = false;
		$parsed_url = parse_url($_SERVER['REQUEST_URI']);
		$path = '/';
		// si es una ruta disponible
		if (isset($parsed_url['path'])) {
			// es una ruta perteneciente al prefijo?
			if (strpos($parsed_url['path'], $this->prefix, 0) !== 0) {
				// call_user_func_array($this->pathNotFound, Array($path));
				$this->pathNotFound($path);
			}

			// Si el "/" final importa
			if ($trailing_slash_matters) {
				$path = $parsed_url['path'];
			} else {
				// Si la ruta no es igual a la ruta base, incluido el "/" final
				if ($this->prefix . '/' != $parsed_url['path']) {
					// Corto el "/" final porque no importa
					$path = rtrim($parsed_url['path'], '/');
				} else {
					$path = $parsed_url['path'];
				}
			}
		}

		$method = $_SERVER['REQUEST_METHOD'];

		if (isset($this->routes[$method])) {
			$chekInPassedMethod = $this->check($method, $path, $case_matters, $trailing_slash_matters, $multimatch);
			if (!$chekInPassedMethod['path_match_found']) {
				// find route in another methods
				$otherMethods = array_keys($this->routes);
				array_splice($otherMethods, array_search($method, $otherMethods), 1);
				$some_path_found = false;

				foreach ($otherMethods as $j => $method) {
					$chekInOtherMethod = $this->check($method, $path, $case_matters, $trailing_slash_matters, $multimatch);
					if ($chekInOtherMethod['path_match_found']) {
						$some_path_found = true;
						// call_user_func_array($this->methodNotAllowed, Array($path,$method));
						$this->methodNotAllowed($path, $method);
						break;
					}
				}
				if (!$some_path_found) {
					// call_user_func_array($this->pathNotFound, Array($path));
					$this->pathNotFound($path);
				}
			}
			$route = $chekInPassedMethod['route'];
			$matches = $chekInPassedMethod['matches'];

			switch ($method) {
				case 'POST':
					$matches = array_merge($matches, array(array_merge($_POST, $_FILES, json_decode(file_get_contents('php://input'), true) ? json_decode(file_get_contents('php://input'), true) : array())));
					break;
				case 'PUT':
					$matches = array_merge($matches, array(array_merge($this->parsePut(), json_decode(file_get_contents('php://input'), true) ? json_decode(file_get_contents('php://input'), true) : array())));
					break;
			}

			if ($route['auth']) {

				$headers = IO::headers();
				if (isset($headers['AUTHORIZATION'])) {
					$auth_type = explode(' ', $headers['AUTHORIZATION'])[0];
					$auth_token = explode(' ', $headers['AUTHORIZATION'])[1];

					if (strcmp(strtolower($auth_type), $route['auth']) != 0)
						$this->methodNotAuthorized($path, $method);

					switch ($auth_type) {
						case 'Basic':
							if (!in_array($auth_token, BASIC_API_KEY)) {
								$this->methodNotAuthorized($path, $method);
							} else {
								$this->setOPS();
								call_user_func_array($route['fn'], $matches);
							}
							break;
						case 'Bearer':
							define('AUTH_TOKEN', $auth_token);
							$this->setOPS();
							call_user_func_array($route['fn'], $matches);
							break;
						default:
							$this->methodNotAuthorized($path, $method);
							break;
					}
				} else {
					$this->methodNotAuthorized($path, $method);
				}
			} else {
				$this->setOPS();
				call_user_func_array($route['fn'], $matches);
			}
		} else {
			// IOException::set(null, 501);
			$this->methodNotImplemented($method);
		}
	}

	private function setOPS()
	{

		define('DB_HOST', $this->db_host);
		define('DB_USER', $this->db_user);
		define('DB_PASS', $this->db_pass);
		define('DB_NAME', $this->db_name);
		define('DB_PORT', $this->db_port);
		define('ROUTE_PREFIX', $this->prefix);
		define('IO_ENVIRONMENT', $this->env);
	}

	public static function formData($boundary, $fields, $files)
	{
		$data = '';
		$eol = "\r\n";

		$delimiter = '-------------' . $boundary;

		foreach ($fields as $name => $content) {
			$data .= "--" . $delimiter . $eol
				. 'Content-Disposition: form-data; name="' . $name . "\"" . $eol . $eol
				. $content . $eol;
		}


		foreach ($files as $i => $file) {
			$data .= "--" . $delimiter . $eol
				. 'Content-Disposition: form-data; name="' . $file['name'] . '"; filename="' . $file['path'] . '"' . $eol
				//. 'Content-Type: image/png'.$eol
				. 'Content-Transfer-Encoding: binary' . $eol;

			$data .= $eol;
			$data .= $file['resource'] . $eol;
		}
		$data .= "--" . $delimiter . "--" . $eol;


		return $data;
	}

	public function parsePut()
	{
		$raw_data = file_get_contents('php://input');
		$boundary = substr($raw_data, 0, strpos($raw_data, "\r\n"));

		// Fetch each part
		$parts = array_slice(explode($boundary, $raw_data), 1);
		$data = array();

		foreach ($parts as $part) {
			// If this is the last part, break
			if ($part == "--\r\n") break;

			// Separate content from headers
			$part = ltrim($part, "\r\n");
			list($raw_headers, $body) = explode("\r\n\r\n", $part, 2);

			// Parse the headers list
			$raw_headers = explode("\r\n", $raw_headers);
			$headers = array();
			foreach ($raw_headers as $header) {
				list($name, $value) = explode(':', $header);
				$headers[strtolower($name)] = ltrim($value, ' ');
			}

			// Parse the Content-Disposition to get the field name, etc.
			if (isset($headers['content-disposition'])) {
				$filename = null;
				preg_match(
					'/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/',
					$headers['content-disposition'],
					$matches
				);
				list(, $type, $name) = $matches;
				isset($matches[4]) and $filename = $matches[4];

				// handle your fields here
				switch ($name) {
						// this is a file upload
					case 'userfile':
						file_put_contents($filename, $body);
						break;

						// default for all other files is to populate $data
					default:
						$data[$name] = substr($body, 0, strlen($body) - 2);
						break;
				}
			}
		}

		return $data;
	}
}

class ROUTE_FORMAT
{
	const ALPHA 			= /*'([:alpha:]+)'*/ '([a-z-0-9]+)';
	const DIGIT 			= '[:digit:]';
	const DIGIT_PLUS		= '([1-9][0-9]*)';
	const ALNUM 			= /*'([:alnum:]+)'*/ '([a-z-0-9]+)';
	const ALNUM_UNDERLINE	= '([a-z-0-9_+]*)';
	const ALNUM_MIDDLELINE	= '([a-z-0-9-]*)';
	const MD5 				= '([a-f0-9]{32})';
	const ALPHA_UTF8		= '[a-zA-ZáéíóúüñÁÉÍÓÚÜÑ\s]+';
	const ALNUM_UTF8		= '([a-zA-Z0-9áéíóúüñÁÉÍÓÚÜÑ\s]+)';
	const TEST_MD5 			= '([:xdigit:]{32})';
}
