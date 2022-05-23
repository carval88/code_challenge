<?php
class IOLanguage
{

	const DEFAULT_LANGUAGE = 'es';
	public static $lang = null;

	public static function translate($t)
	{
		if (is_null(self::$lang)) {
			$f = __dir__ . '/../languages/lang-' . (isset($_SERVER['LANG_SELECTED']) ? $_SERVER['LANG_SELECTED'] : self::DEFAULT_LANGUAGE) . '.json';
			if (file_exists($f)) {
				self::$lang = json_decode(file_get_contents($f), true);
			} else {
				return $t;
			}
		}
		return isset(self::$lang[$t]) ? self::$lang[$t] : $t;
	}
}
