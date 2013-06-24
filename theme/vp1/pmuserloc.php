<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: pmuserloc.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	define('plain_form', 1);

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function alt_var($key)
{
	if (!isset($GLOBALS['_ALTERNATOR_'][$key])) {
		$args = func_get_args(); unset($args[0]);
		$GLOBALS['_ALTERNATOR_'][$key] = array('p' => 2, 't' => func_num_args(), 'v' => $args);
		return $args[1];
	}
	$k =& $GLOBALS['_ALTERNATOR_'][$key];
	if ($k['p'] == $k['t']) {
		$k['p'] = 1;
	}
	return $k['v'][$k['p']++];
}

	if (empty($_GET['js_redr'])) {
		exit;
	}

	if (!_uid) {
		std_error('login');
	} else if (!($FUD_OPT_1 & (8388608|4194304))) {
		std_error('disabled');
	}



	$usr_login = isset($_GET['usr_login']) && is_string($_GET['usr_login']) ? trim($_GET['usr_login']) : '';
	$overwrite = isset($_GET['overwrite']) ? (int)$_GET['overwrite'] : 0;

	$js_redr = $_GET['js_redr'];
	switch ($js_redr) {
		case 'post_form.msg_to_list':
		case 'groupmgr.gr_member':
		case 'buddy_add.add_login':
			break;
		default:
			exit;
	}
	list($frm, $fld) = explode('.', $js_redr);

	$find_user_data = '';
	if ($usr_login) {
		$c = uq('SELECT alias FROM fud26_users WHERE alias LIKE '. _esc(char_fix(htmlspecialchars(addcslashes($usr_login.'%','\\')))) .' AND id>1');
		$i = 0;
		while ($r = db_rowarr($c)) {
			if ($overwrite) {
				$retlink = 'javascript: window.opener.document.forms[\''. $frm .'\'].'. $fld .'.value=\''. addcslashes($r[0], "'\\") .'\'; window.close();';
			} else {
				$retlink = 'javascript:
						if (!window.opener.document.forms[\''. $frm .'\'].'. $fld .'.value) {
							window.opener.document.forms[\''. $frm .'\'].'. $fld .'.value = \''. addcslashes($r[0], "'\\") .'\';
						} else {
							window.opener.document.forms[\''. $frm .'\'].'. $fld .'.value = window.opener.document.forms[\''. $frm .'\'].'. $fld .'.value + \'; \' + \''. addcslashes($r[0], "'\\") .'; \';
						}
					window.close();';
			}
			$find_user_data .= '<tr class="'.alt_var('pmuserloc_alt','RowStyleA','RowStyleB').'"><td><a href="'.$retlink.'">'.$r[0].'</a></td></tr>';
			++$i;
		}
		unset($c);
		if (!$find_user_data) {
			$find_user_data = '<tr><td colspan="2">Ничего не найдено</td></tr>';
		}
	}


?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
<base href="<?php echo $GLOBALS['WWW_ROOT']; ?>" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="theme/vp1/forum.css" type="text/css" />
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground">
<form id="pmuserloc" action="index.php" method="get"><?php echo _hs; ?>
<table cellspacing="0" cellpadding="3" class="dashed">
<tr>
	<td class="fb">Имя:</td>
	<td><input tabindex="1" type="text" name="usr_login" value="<?php echo char_fix(htmlspecialchars($usr_login)); ?>" /></td>
	<td><input tabindex="2" type="submit" class="button" name="btn_submit" value="Найти" /></td>
</tr>
</table>
<input type="hidden" name="js_redr" value="<?php echo $js_redr; ?>" />
<input type="hidden" name="overwrite" value="<?php echo $overwrite; ?>" />
<input type="hidden" name="t" value="pmuserloc" />
</form>
<br />
<table cellspacing="0" cellpadding="3" class="dashed wa">
<tr><th>Пользователь</th></tr>
<?php echo $find_user_data; ?>
</table>
<script type="text/javascript">
/* <![CDATA[ */ 
document.forms['pmuserloc'].usr_login.focus();
/* ]]> */
</script>
</td></tr></table></body></html>
