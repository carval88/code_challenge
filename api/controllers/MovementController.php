<?php

class MovementController
{
	public static function monthlySummary(?int $month = null)
	{
		$month ??= (int) date('n');
		$incomes = Movement::incomesSummary($month);
		$expenses = Movement::expensesSummary($month);
		return array(
			'incomes' => $incomes,
			'expenses' => $expenses,
			'month' => $month
		);
	}

	public static function readAll(int $page = 1)
	{
		return Movement::readAll($page);
	}

	public static function read(int $mo_id)
	{
		$movement = new Movement($mo_id);
		return $movement->__read();
	}

	public static function incomeAdd(array $d)
	{
		$d['mo_type'] = 'income';
		$mo = new Movement($d);
		return $mo->__create();
	}

	public static function expenseAdd(array $d)
	{
		$d['mo_type'] = 'expense';
		$mo = new Movement($d);
		return $mo->__create();
	}

	public static function update(array $d)
	{
		$mo = new Movement($d);
		return $mo->__update();
	}


	public static function delete(int $mo_id)
	{
		try {
			$mo = new Movement($mo_id);
			return $mo->__delete();
		} catch (\Throwable $th) {
			IOException::set($th->getMessage());
		}
	}
}
