<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: qbud.php.t 4994 2010-09-02 17:33:29Z naudefj $
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

	if (!_uid) {
		std_error('login');
	}

	if (isset($_POST['names']) && is_array($_POST['names'])) {
		$names = addcslashes(implode(';', $_POST['names']), '"\\');
?>
<html><body>
<script type="text/javascript">
/*  <![CDATA[ */
if (window.opener.document.forms['post_form'].msg_to_list.value.length > 0) {
	window.opener.document.forms['post_form'].msg_to_list.value = window.opener.document.forms['post_form'].msg_to_list.value+';'+"<?php echo $names; ?>";
} else {
	window.opener.document.forms['post_form'].msg_to_list.value = window.opener.document.forms['post_form'].msg_to_list.value+"<?php echo $names; ?>";
}
window.close();
/* ]]> */
</script>
</body></html>
<?php
		exit;
	}



	$buddies = '';
	$c = uq('SELECT u.alias FROM fud26_buddy b INNER JOIN fud26_users u ON b.bud_id=u.id WHERE b.user_id='. _uid .' AND b.user_id>1');
	while ($r = db_rowarr($c)) {
		$buddies .= '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="GenText">'.$r[0].'</td><td class="ac"><input type="checkbox" name="names[]" value="'.$r[0].'" /></td></tr>';
	}
	unset($c);


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
<form action="index.php?t=qbud" id="qbud" method="post"><?php echo _hs; ?>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<?php echo ($buddies ? '<tr><th class="wa">Псевдоним</th><th class="nw">Выбрано <input type="checkbox" name="toggle" title="все/нет" onclick="$(\'input:checkbox\').attr(\'checked\', (this.checked)?\'checked\':\'\');" /> </th></tr>
'.$buddies.'
<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td colspan="2" class="GenText ar"><input type="submit" class="button" name="submit" value="Добавить выбранных" /></td></tr>' : '<tr class="RowStyleA"><td class="GenText ac">Никого нет в списке для выбора</td></tr>'); ?>
</table>
</form>
</td></tr></table></body></html>
