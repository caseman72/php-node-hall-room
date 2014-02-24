#!/usr/bin/php
<?php
date_default_timezone_set('America/Los_Angeles');

$cwd = dirname(__FILE__);

// must have .standups
$dest = rtrim(getenv('HOME'), '\/') . '/.standups';
if (!file_exists($dest)) {
	die("Error - no standups directory [$dest]\n");
}

// roomid
$room_id = trim(getenv('BEACON_ROOMID'));

// init time
$now = $yestertime = strtotime('midnight');

// find last standup
$prev_file = '';
$prev_contents = '';

// find yesterday
$daycount = 0;
while($daycount++ < 20) {
	$yestertime -= 86400;
	$yesterday = date('Y-m-d', $yestertime);
	if (file_exists("$dest/standup.{$yesterday}.txt")) {
		$prev_contents = file_get_contents("$dest/standup.{$yesterday}.txt");
		$prev_file = "$dest/standup.{$yesterday}.txt";
		break;
	}
}

$yesterday_string = $prev_contents
	? preg_replace("/^.*?Today[^\n]*\n+(.*?)\n+Yesterday.*$/s", "$1", $prev_contents)
	: '';

$today_string = preg_replace("/^/m", "#- ", $yesterday_string);

// default block
$standup_template = <<<EOS
Today:
$today_string

Yesterday:
$yesterday_string

Confidence:
* Normal

Blocks:
* None

----------------------------------------
EOS;

$today = date('Y-m-d', $now);
$curr_file = "{$dest}/standup.{$today}.txt";

// prepare comment file
file_put_contents($curr_file, "$standup_template\n\n");

// append prev day
if ($prev_file) {
	system("echo '#- {$yesterday}' >> {$curr_file}");
	system("grep -v '^#-' {$prev_file} | sed 's/^/#- /;s/[ ]*$//' >> {$curr_file}");
}

if (getenv('OSTYPE') == 'cygwin') {
	// launch and wait
	system("psexec C:\\\\cygwin\\\\vim.bat /cygwin{$curr_file} >& /dev/null");
}
else {

	$pid = pcntl_fork();
	if ($pid == -1) {
		die("Exiting - could not fork\n");
	}
	elseif ($pid) {
		// we are the parent
		pcntl_wait($status);

		// done with vim ...
		$standup = trim(preg_replace("/[ ]+$/m", '', `grep -v '^#-' {$curr_file}`));
		$today_standup = preg_replace("/^.*?\n?Today[^\n]*\n(.*?)\nYesterday.*$/s", "$1", $standup);

		if (!$today_standup) {
			die("Exiting - no entry for Today!\n");
		}

		system("node ./post-to-hall.js '{$room_id}' '{$curr_file}'");
	}
	else
	{
		// we are the child exit when done...
		system("/usr/bin/vim {$curr_file} > `tty`");
		exit;
	}
}

