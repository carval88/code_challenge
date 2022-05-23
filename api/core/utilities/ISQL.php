<?php
class ISQL
{
	public static $conexion;
	public static function on()
	{
		if (self::$conexion) {
		} else {
			self::$conexion = mysqli_connect(
				DB_HOST,
				DB_USER,
				DB_PASS,
				'',
				DB_PORT
			);
			if (!self::$conexion) {
				IOException::set('ICCNNFL');
			}
			mysqli_set_charset(self::$conexion, 'utf8');
			if (!mysqli_select_db(self::$conexion, DB_NAME)) {
				IOException::set('ICSLCFL');
			}
		}
		return self::$conexion;
	}
	public static function off()
	{
		return mysqli_close(self::on());
	}
	public static function begin()
	{
		return mysqli_query(self::on(), 'BEGIN');
	}
	public static function end($saving)
	{
		return mysqli_query(self::on(), $saving ? 'COMMIT' : 'ROLLBACK');
	}
	public static function exe($Q)
	{
		$res = mysqli_query(self::on(), $Q);
		if ($res) {
			return $res;
		} else {
			IOException::set(mysqli_error(self::on()));
		}
	}
	public static function query($Q)
	{
		return self::resultToArray(self::exe($Q));
	}
	public static function queryOne($Q)
	{
		$r = self::resultToArray(self::exe($Q));
		if (isset($r[0])) {
			return $r[0];
		}
		IOException::set('ICNTFND');
	}

	public static function fn($Q)
	{
		$r = self::queryOne('select ' . $Q . ' as r');
		return $r['r'];
	}
	public static function resultToArray($q)
	{
		$o = array();
		while ($r = mysqli_fetch_assoc($q)) {
			$o[] = $r;
		}
		return $o;
	}
	public static function rs2Array($q, $className = null)
	{
		$o = array();
		while ($r = mysqli_fetch_assoc($q)) {
			$o[] = $r;
		}
		return $o;
	}
	public static function lastID()
	{
		return mysqli_insert_id(self::on());
	}
}
