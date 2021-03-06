<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: index.php 5072 2010-11-11 17:12:40Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	require('./GLOBALS.php');
	fud_use('adm.inc', true);

	include($WWW_ROOT_DISK .'adm/header.php');
	$tbl = $GLOBALS['DBHOST_TBL_PREFIX'];

	// Reset most users ever online. 
	if (isset($_POST['btn_clear_online'])) {
		q('UPDATE '. $tbl .'stats_cache SET most_online = (online_users_reg+online_users_anon+online_users_hidden), most_online_time ='. __request_timestamp__);
		echo successify('The forum\'s "most online users" statistic was successfully reset.');
	}
	if (isset($_POST['btn_clear_sessions'])) {
		q('DELETE FROM '. $tbl .'ses');
		echo successify('All forum sessions were cleared.');
		echo errorify('You (and all your users) will have to log in again!');
	}

?>
<h2>Forum Dashboard</h2>

<?php
	if (@file_exists($WWW_ROOT_DISK .'install.php')) {
		echo '<div class="alert dismiss" title="'. $WWW_ROOT_DISK .'install.php">Unless you want to <a href="../install.php">reinstall</a> your forum, please <a href="admbrowse.php?cur='. urlencode($WWW_ROOT_DISK) .'&amp;'. __adm_rsid .'#flagged">delete the install script</a> before a hacker does it for you.<br /></div>';
	}
	if (@file_exists($WWW_ROOT_DISK .'uninstall.php')) {
		echo '<div class="alert dismiss" title="'. $WWW_ROOT_DISK .'uninstall.php">Please <a href="../uninstall.php">run the uninstall script</a> or <a href="admbrowse.php?cur='. urlencode($WWW_ROOT_DISK) .'&amp;'. __adm_rsid .'#flagged">delete it</a> to prevent hackers from destroying your forum.<br /></div>';
	}
	if (@file_exists($WWW_ROOT_DISK .'upgrade.php')) {
		echo '<div class="alert dismiss" title="'. $WWW_ROOT_DISK .'upgrade.php">Please <a href="../upgrade.php">run the upgrade script</a> and <a href="admbrowse.php?cur='. urlencode($WWW_ROOT_DISK) .'&amp;'. __adm_rsid .'#flagged">delete it</a> when you are done to prevent hackers from destroying your forum.<br /></div>';
	}
	if (@file_exists($WWW_ROOT_DISK .'unprotect.php')) {
		echo '<div class="alert dismiss" title="'. $WWW_ROOT_DISK .'unprotect.php">Please <a href="admbrowse.php?cur='. urlencode($WWW_ROOT_DISK) .'&amp;'. __adm_rsid .'#flagged">delete the unprotect script</a> before a hacker destroys your forum.<br /></div>';
	}

	/* Check load. */
	if (function_exists('sys_getloadavg') && ($load = sys_getloadavg()) && $load[0] > 25) {
		echo '<div class="alert dismiss">You web server is quite busy (CPU load is '. $load[1] .'). This may impact your forum\'s performance!</div><br />';
	}

	/* Check version. */
	if (@file_exists($FORUM_SETTINGS_PATH .'latest_version')) {
		$verinfo = trim(file_get_contents($FORUM_SETTINGS_PATH .'latest_version'));
		$display_ver = substr($verinfo, 0, strpos($verinfo, '::'));
		if (version_compare($display_ver, $FORUM_VERSION, '>')) {
			echo '<div class="alert dismiss">You are running an old forum version. Please upgrade to FUDforum '. $display_ver .' ASAP!<br /></div>';
		}
	}
?>

<div class="tutor">
Welcome to your forum's Admin Control Panel. From here you can control how your forum looks and behaves. To continue, please click on one of the links in the left sidebar of the window. First time users should start with the <b><a href="admglobal.php?<?php echo __adm_rsid; ?>">Global Settings Manager</a></b>.
</div>

<table border="0"><tr><td width="50%" valign="top">

<h4>Getting help:</h4>
FUDforum's documentation is available on our <b><a href="http://cvs.prohost.org/">development and documentation wiki</a></b>. Please report any problems on the support forum at <b><a href="http://fudforum.org">fudforum.org</a></b>.

</td><td width="50%" valign="top">

<h4>Versions:</h4>
<?php if (!isset($display_ver)) { ?>
	<b>FUDforum</b>: <?php echo $FORUM_VERSION; ?><br />
<?php } elseif (version_compare($display_ver, $FORUM_VERSION, '>')) { ?>
	<b>FUDforum</b>: <?php echo $FORUM_VERSION; ?> <span style="color:red">please upgrade ASAP!</span><br />
<?php } else { ?>
	<b>FUDforum</b>: <?php echo $FORUM_VERSION; ?> (<span style="color:green">latest version</span>)<br />
<?php } ?>
<b>PHP</b>: <?php echo PHP_VERSION; ?><br />
<b>Database</b>: <?php echo __dbtype__ .' '. db_version() .' ('. $GLOBALS['DBHOST_DBTYPE'] .')'; ?><br />
<b>Operating system</b>: <?php echo (@php_uname() ? php_uname('s') .' '. php_uname('r') : 'n/a') ?><br />

<span style="float:right;"><a href="admsysinfo.php?<?php echo __adm_rsid; ?>">More... &raquo;</a></span>

</td></tr></table>

<?php
	$forum_stats['MESSAGES']         = q_singleval('SELECT count(*) FROM '. $tbl .'msg');
	$forum_stats['THREADS']          = q_singleval('SELECT count(*) FROM '. $tbl .'thread');
	$forum_stats['PRIVATE_MESSAGES'] = q_singleval('SELECT count(*) FROM '. $tbl .'pmsg');
	$forum_stats['FORUMS']           = q_singleval('SELECT count(*) FROM '. $tbl .'forum');
	$forum_stats['CATEGORIES']       = q_singleval('SELECT count(*) FROM '. $tbl .'cat');
	$forum_stats['MEMBERS']          = q_singleval('SELECT count(*) FROM '. $tbl .'users');
	$forum_stats['ADMINS']           = q_singleval('SELECT count(*) FROM '. $tbl .'users WHERE users_opt>=1048576 AND '. q_bitand('users_opt', 1048576) .' > 0');
	$forum_stats['MODERATORS']       = q_singleval('SELECT count(DISTINCT(user_id)) FROM '. $tbl .'mod');
	$forum_stats['GROUPS']           = q_singleval('SELECT count(*) FROM '. $tbl .'groups');
	$forum_stats['GROUP_MEMBERS']    = q_singleval('SELECT count(*) FROM '. $tbl .'group_members');
?>

<h4>Forum statistics:</h4>
<table class="resulttable fulltable">
<tr class="field">
	<td><b>Messages:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['MESSAGES']; ?></td>
	<td width="100">&nbsp;</td>
	<td></td>
</tr>

<tr class="field">
	<td valign="top"><b>Topics:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['THREADS']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1"><b><?php echo @sprintf('%.2f', $forum_stats['MESSAGES']/$forum_stats['THREADS']); ?></b> messages per topic</font></td>
</tr>

<tr class="field">
	<td valign="top"><b>Forums:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['FORUMS']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1">
		<b><?php echo @sprintf('%.2f', $forum_stats['MESSAGES']/$forum_stats['FORUMS']); ?></b> messages per forum<br />
		<b><?php echo @sprintf('%.2f', $forum_stats['THREADS']/$forum_stats['FORUMS']); ?></b> topics per forum
	</font></td>
</tr>

<tr class="field">
	<td valign="top"><b>Categories:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['CATEGORIES']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1">
		<b><?php echo @sprintf('%.2f', $forum_stats['MESSAGES']/$forum_stats['CATEGORIES']); ?></b> messages per category<br />
		<b><?php echo @sprintf('%.2f', $forum_stats['THREADS']/$forum_stats['CATEGORIES']); ?></b> topics per category<br />
		<b><?php echo @sprintf('%.2f', $forum_stats['FORUMS']/$forum_stats['CATEGORIES']); ?></b> forums per category
	</font></td>
</tr>

<tr class="field">
	<td><b>Private Messages:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['PRIVATE_MESSAGES']; ?></td>
	<td width="100">&nbsp;</td>
	<td></td>
</tr>

<tr class="field">
	<td valign="top"><b>Users:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['MEMBERS']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1">
		<b><?php echo @sprintf('%.2f', $forum_stats['MESSAGES']/$forum_stats['MEMBERS']); ?></b> messages per user<br />
		<b><?php echo @sprintf('%.2f', $forum_stats['THREADS']/$forum_stats['MEMBERS']); ?></b> topics per user<br />
		<b><?php echo @sprintf('%.2f', $forum_stats['PRIVATE_MESSAGES']/$forum_stats['MEMBERS']); ?></b> private messages per user
	</font></td>
</tr>

<tr class="field">
	<td valign="top"><b>Moderators:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['MODERATORS']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1">
		<b><?php echo @sprintf('%.2f', ($forum_stats['MODERATORS']/$forum_stats['MEMBERS'])*100); ?>%</b> of all users<br />
		<b><?php echo @sprintf('%.2f', $forum_stats['MODERATORS']/$forum_stats['FORUMS']); ?></b> per forum
	</font></td>
</tr>

<tr class="field">
	<td valign="top"><b>Administrators:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['ADMINS']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1"><b><?php echo @sprintf('%.2f', $forum_stats['ADMINS']/$forum_stats['MEMBERS']); ?>%</b> of all users</font></td>
</tr>

<tr class="field">
	<td valign="top"><b>User Groups:</b></td>
	<td align="right" valign="top"><?php echo $forum_stats['GROUPS']; ?></td>
	<td width="100">&nbsp;</td>
	<td><font size="-1"><b><?php echo @sprintf('%.2f', $forum_stats['GROUP_MEMBERS']/$forum_stats['GROUPS']); ?></b> members per group</font></td>
</tr>
</table>
<span style="float:right;"><a href="admstats.php?<?php echo __adm_rsid; ?>">More... &raquo;</a></span>
<br /><br />

<hr />
<form method="post" action="index.php"><?php echo _hs; ?>
<input type="submit" name="btn_clear_online" class="button" value="Reset the 'most online users' counter" />
<input type="submit" name="btn_clear_sessions" class="button" value="Clear ALL Forum Sessions" />
</form>
<br />

<?php require($WWW_ROOT_DISK .'adm/footer.php'); ?>
