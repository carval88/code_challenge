<?php
require __dir__ . '/autoload.php';


__display_errors();
__cors();

$app = new Route([
	'db_host' => 'mysql_db',
	'db_user' => 'code_challenge_u',
	'db_pass' => 'code_challenge_2022',
	'db_name' => 'code_challenge_db',
	'db_port' => 3306
]);
$app->get('/?', fn () => IO::output('Welcome to code_challenge api'));
$app->post('/income', fn ($d) => IO::output(MovementController::incomeAdd($d)));
$app->post('/expense', fn ($d) => IO::output(MovementController::expenseAdd($d)));
$app->get('/movements', fn () => IO::output(MovementController::readAll($_GET['page'] ??= 1)));

$app->get('/summary/?' . ROUTE_FORMAT::DIGIT_PLUS . '?', fn ($month = null) => IO::output(MovementController::monthlySummary($month)));


__run($app);
