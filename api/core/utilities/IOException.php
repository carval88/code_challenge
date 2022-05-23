<?php
class IOException
{

	const DEFAULT_MESSAGE = 'DEFAULT-ERROR-MESSAGE';

	public static function set($message = self::DEFAULT_MESSAGE,  $code = 200, $ct = 'application/json;')
	{
		if ($code == 200) {
			$msg = array('error' => IOLanguage::translate($message));
		} else {
			$msg = IOLanguage::translate($message);
		}

		IO::output($msg, $code, $ct);
	}
}
