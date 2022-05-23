<?php
require_once __dir__ . '/core/IO.php';
require_once __dir__ . '/core/Route.php';
require_once __dir__ . '/settings/Setup.php';
require_once __dir__ . '/core/jwt/JWT.php';

function __class_exists($cn)
{
	$r = false;
	if (file_exists(__dir__ . '/controllers/' . $cn . '.php')) {
		$r = true;
	} else {
		if (file_exists(__dir__ . '/core/utilities/' . $cn . '.php')) {
			$r = true;
		} else {
			if (file_exists(__dir__ . '/models/' . $cn . '.php')) {
				$r = true;
			}
		}
	}
	return $r;
}

spl_autoload_register(function ($cn) {
	if (file_exists(__dir__ . '/controllers/' . $cn . '.php')) {
		include __dir__ . '/controllers/' . $cn . '.php';
	} else {
		if (file_exists(__dir__ . '/core/utilities/' . $cn . '.php')) {
			include __dir__ . '/core/utilities/' . $cn . '.php';
		} else {
			include __dir__ . '/models/' . $cn . '.php';
		}
	}
	if (!class_exists($cn)) {
		IOException::set($cn . ' no existe.', 404);
	}
});

function __cors()
{
	// header("Access-Control-Allow-Origin: https://admin.jplazamotores.com");
	// header('Access-Control-Allow-Credentials: true');
	// header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE');
	// header("Access-Control-Allow-Headers: X-Requested-With");
	// header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

	header('Access-Control-Allow-Origin: *');
	header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE');
	header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Authorization");
	//header('Content-Type: application/json');

	$method = $_SERVER['REQUEST_METHOD'];
	if ($method == "OPTIONS") {
		header("HTTP/1.1 200 OK");
		die();
	}
}

function __display_errors()
{
	error_reporting(0);
	if (isset($_GET['debug'])) {
		error_reporting(E_ALL);
		ini_set('display_errors', true);
	}
}

function __run(...$routes)
{
	$found = 404;
	$cl = null;
	$parsed_url = parse_url($_SERVER['REQUEST_URI']);
	if (isset($parsed_url['path'])) {
		foreach ($routes as $i => $r) {
			if (strpos($parsed_url['path'], $r->prefix, 0) === 0) {
				$found = 200;
				$routes[$i]->run();
				break;
			} else {
				$cl = $r;
			}
		}
	}
	if ($found == 404) {
		$cl->pathNotFound('');
	}
}

function __array_to_class(array $data, string $className)
{

	try {
		$rf = new ReflectionClass($className);
		$proterties = $rf->getProperties(ReflectionProperty::IS_PUBLIC);
		$v = array();
		if (isset($data[0])) {
			return $rf->newInstanceArgs($data);
		} else {
			foreach ($proterties as $i => $p) {
				if (array_key_exists($p->name, $data)) {
					$data[$p->name] ??= null;
					if (__class_exists($p->getType()->getName())) {
						if (is_a($data[$p->name], $p->getType()->getName())) {
							$v[] = $data[$p->name];
						} else {
							$v[] = __array_to_class((array) $data[$p->name], $p->getType()->getName());
						}
					} else {
						settype($data[$p->name], $p->getType()->getName());
						$v[] = $data[$p->name];
					}
				}
				// if (isset($data[$p->name])) {
				// 	if (__class_exists($p->getType()->getName())) {
				// 		$v[] = __array_to_class($data[$p->name], $p->getType()->getName());
				// 	} else {
				// 		$v[] = $data[$p->name];
				// 	}
				// }
			}
			return $rf->newInstanceArgs($v);
		}
	} catch (\Throwable | Exception $th) {
		IOException::set($th->getMessage());
	}
}
function __array_to_class_old(array $data, string $className)
{
	try {
		$rf = new ReflectionClass($className);
		$proterties = $rf->getProperties(ReflectionProperty::IS_PUBLIC);
		$v = array();
		if (isset($data[0])) {
			return $rf->newInstanceArgs($data);
		} else {
			foreach ($proterties as $i => $p) {
				if (isset($data[$p->name])) {
					$v[] = $data[$p->name];
				}
			}
			return $rf->newInstanceArgs($v);
		}
	} catch (\Throwable $th) {
		echo $th->getMessage();
	}
}
