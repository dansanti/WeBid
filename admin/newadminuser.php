<?php
/***************************************************************************
 *   copyright				: (C) 2008 - 2015 WeBid
 *   site					: http://www.webidsupport.com/
 ***************************************************************************/

/***************************************************************************
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version. Although none of the code may be
 *   sold. If you have been sold this script, get a refund.
 ***************************************************************************/

define('InAdmin', 1);
$current_page = 'users';
include '../common.php';
include $include_path . 'functions_admin.php';
include 'loggedin.inc.php';

unset($ERR);

if (isset($_POST['action']) && $_POST['action'] == 'update')
{
	if (empty($_POST['username']) || empty($_POST['password']) || empty($_POST['repeatpassword']))
	{
		$ERR = $ERR_047;
	}
	elseif ((!empty($_POST['password']) && empty($_POST['repeatpassword'])) || empty($_POST['password']) && !empty($_POST['repeatpassword']))
	{
		$ERR = $ERR_054;
	}
	elseif ($_POST['password'] != $_POST['repeatpassword'])
	{
		$ERR = $ERR_006;
	}
	else
	{
		// Check if "username" already exists in the database
		$query = "SELECT id FROM " . $DBPrefix . "adminusers WHERE username = :username";
		$params = array();
		$params[] = array(':username', $system->cleanvars($_POST['username']), 'str');
		$db->query($query, $params);
		if ($db->numrows() > 0)
		{
			$ERR = sprintf($ERR_055, $_POST['username']);
		}
		else
		{
			include $include_path . 'PasswordHash.php';
			$phpass = new PasswordHash(8, false);
			$query = "INSERT INTO " . $DBPrefix . "adminusers VALUES
					(NULL, :username, :password, :hash, :created, '0', :status, '')";
			$params = array();
			$params[] = array(':username', $system->cleanvars($_POST['username']), 'str');
			$params[] = array(':password', $phpass->HashPassword($_POST['password']), 'str');
			$params[] = array(':hash', get_hash(), 'str');
			$params[] = array(':created', date('Ymd'), 'str');
			$params[] = array(':status', $_POST['status'], 'int');
			$db->query($query, $params);
			header('location: adminusers.php');
			exit;
		}
	}
}

loadblock($MSG['003'], '', 'text', 'username', '');
loadblock($MSG['004'], '', 'password', 'password', '');
loadblock($MSG['564'], '', 'password', 'repeatpassword', '');
loadblock('', '', 'batch', 'status', '1', array($MSG['566'], $MSG['567']));

$template->assign_vars(array(
		'ERROR' => (isset($ERR)) ? $ERR : '',
		'SITEURL' => $system->SETTINGS['siteurl'],
		'TYPENAME' => $MSG['25_0010'],
		'PAGENAME' => $MSG['367']
		));

$template->set_filenames(array(
		'body' => 'adminpages.tpl'
		));
$template->display('body');
?>
