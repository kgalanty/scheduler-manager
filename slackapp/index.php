<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


require_once('./vendor/autoload.php');
require_once('../../init.php');
use WHMCS\Database\Capsule as DB;

$admins = DB::table('tbladmins')->where('disabled', '0')->get(['id', 'email']);
$api = \JoliCode\Slack\ClientFactory::create('xoxb-4872684148-3126978654322-MQEMiX0uG4U4BcedtdaPWUVh');

$userData = [];

foreach ($admins as $admin) {
	try {
		$user = $api->usersLookupByEmail(['email' => $admin->email]);
		if ($user) {

			$userData[] = [
				'agent_id' => $admin->id,
				'phone' => $user->getUser()->getProfile()->getPhone(),
				'email' => $admin->email,
				'slackid' => $user->getUser()->getId(),
				'realname' => $user->getUser()->getProfile()->getRealName(),
				'realnamenormalized' => $user->getUser()->getProfile()->getRealNameNormalized()
			];
		}
	} catch (\Exception $e) {
		continue;
	}
}
echo ('<pre>');
var_dump($userData);
die;
if ($userData) {
	foreach ($userData as $userdataItem) {
		DB::table('schedule_slackusers')->updateOrInsert(['agent_id' => $userdataItem['agent_id']], $userdataItem);
	}
}
die;


//$user = $api->usersList()->getMembers();
$user = $api->usersLookupByEmail(['email' => 'k.galanty@tmdhosting.com'])->getUser();


$r = $api->chatPostMessage([
	'username' => 'Schedule Bot',
	'channel' => 'UESJKKE84',
	'text' => 'This is a test notification from slack app, directed in private messages.',

]);
echo ('<pre>');
var_dump($r);
die;
