<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: ignore_list.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function ignore_add($user_id, $ignore_id)
{
	q('INSERT INTO fud26_user_ignore (ignore_id, user_id) VALUES ('. $ignore_id .', '. $user_id .')');
	q('DELETE FROM fud26_buddy WHERE user_id='. $ignore_id .' AND bud_id='. $user_id);
	if (db_affected()) {
		fud_use('buddy.inc');
		buddy_rebuild_cache($ignore_id);
	}

	return ignore_rebuild_cache($user_id);
}

function ignore_delete($user_id, $ignore_id)
{
	q('DELETE FROM fud26_user_ignore WHERE user_id='. $user_id .' AND ignore_id='. $ignore_id);
	return ignore_rebuild_cache($user_id);
}

function ignore_rebuild_cache($uid)
{
	$arr = array();
	$q = uq('SELECT ignore_id FROM fud26_user_ignore WHERE user_id='. $uid);
	while ($ent = db_rowarr($q)) {
		$arr[$ent[0]] = 1;
	}
	unset($q);

	if ($arr) {
		q('UPDATE fud26_users SET ignore_list='. _esc(serialize($arr)) .' WHERE id='. $uid);
		return $arr;
	}
	q('UPDATE fud26_users SET ignore_list=NULL WHERE id='. $uid);
}function check_return($returnto)
{
	if ($GLOBALS['FUD_OPT_2'] & 32768 && !empty($_SERVER['PATH_INFO'])) {
		if (!$returnto || !strncmp($returnto, '/er/', 4)) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/i/'. _rsidl);
		} else if ($returnto[0] == '/') { /* Unusual situation, path_info & normal themes are active. */
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php'. $returnto);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. $returnto);
		}
	} else if (!$returnto || !strncmp($returnto, 't=error', 7)) {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=index&'. _rsidl);
	} else if (strpos($returnto, 'S=') === false && $GLOBALS['FUD_OPT_1'] & 128) {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. $returnto .'&S='. s);
	} else {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. $returnto);
	}
	exit;
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

function ignore_alias_fetch($al, &$is_mod)
{
	if (!($tmp = db_saq('SELECT id, '. q_bitand('users_opt', 1048576) .' FROM fud26_users WHERE alias='. _esc(char_fix(htmlspecialchars($al)))))) {
		return;
	}
	$is_mod = $tmp[1];

	return $tmp[0];
}

	if (isset($_POST['add_login']) && is_string($_POST['add_login'])) {
		if (!($ignore_id = ignore_alias_fetch($_POST['add_login'], $is_mod))) {
			error_dialog('Пользователь не найден', 'Пользователь, которого вы пытаетесь добавить в список игнорируемых, не найден.');
		}
		if ($is_mod) {
			error_dialog('Информация', 'Вы не можете игнорировать этого участника');
		}
		if (!empty($usr->ignore_list)) {
			$usr->ignore_list = unserialize($usr->ignore_list);
		}
		if (!isset($usr->ignore_list[$ignore_id])) {
			ignore_add(_uid, $ignore_id);
		} else {
			error_dialog('Информация', 'Этот пользователь уже был внесен в ваш список игнорируемых ранее');
		}
	}

	/* Incomming from message display page (ignore link). */
	if (isset($_GET['add']) && ($_GET['add'] = (int)$_GET['add'])) {
		if (!sq_check(0, $usr->sq)) {
			check_return($usr->returnto);
		}

		if (!empty($usr->ignore_list)) {
			$usr->ignore_list = unserialize($usr->ignore_list);
		}

		if (($ignore_id = q_singleval('SELECT id FROM fud26_users WHERE id='. $_GET['add'] .' AND '. q_bitand('users_opt', 1048576) .'=0')) && !isset($usr->ignore_list[$ignore_id])) {
			ignore_add(_uid, $ignore_id);
		}
		check_return($usr->returnto);
	}

	/* Anon user hack. */
	if (isset($_GET['del']) && $_GET['del'] === '0') {
		$_GET['del'] = 1;
	}

	if (isset($_GET['del']) && ($_GET['del'] = (int)$_GET['del'])) {
		if (!sq_check(0, $usr->sq)) {
			check_return($usr->returnto);
		}

		ignore_delete(_uid, $_GET['del']);
		/* Needed for external links to this form. */
		if (isset($_GET['redr'])) {
			check_return($usr->returnto);
		}
	}

	ses_update_status($usr->sid, 'Просмотр списка игнорируемых');

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}$tabs = '';
if (_uid) {
	$tablist = array(
'Извещения'=>'uc',
'Настройки'=>'register',
'Подписка'=>'subscribed',
'Закладки'=>'bookmarked',
'Приглашенные'=>'referals',
'Список контактов'=>'buddy_list',
'Список игнорируемых'=>'ignore_list',
'Показать свои сообщения'=>'showposts'
);

	if (!($FUD_OPT_2 & 8192)) {
		unset($tablist['Приглашенные']);
	}

	if (isset($_POST['mod_id'])) {
		$mod_id_chk = $_POST['mod_id'];
	} else if (isset($_GET['mod_id'])) {
		$mod_id_chk = $_GET['mod_id'];
	} else {
		$mod_id_chk = null;
	}

	if (!$mod_id_chk) {
		if ($FUD_OPT_1 & 1024) {
			$tablist['Личная почта'] = 'pmsg';
		}
		$pg = ($_GET['t'] == 'pmsg_view' || $_GET['t'] == 'ppost') ? 'pmsg' : $_GET['t'];

		foreach($tablist as $tab_name => $tab) {
			$tab_url = 'index.php?t='. $tab . (s ? '&amp;S='. s : '');
			if ($tab == 'referals') {
				if (!($FUD_OPT_2 & 8192)) {
					continue;
				}
				$tab_url .= '&amp;id='. _uid;
			} else if ($tab == 'showposts') {
				$tab_url .= '&amp;id='. _uid;
			}
			$tabs .= $pg == $tab ? '<td class="tabON"><div class="tabT"><a class="tabON" href="'.$tab_url.'">'.$tab_name.'</a></div></td>' : '<td class="tabI"><div class="tabT"><a href="'.$tab_url.'">'.$tab_name.'</a></div></td>';
		}

		$tabs = '<table cellspacing="1" cellpadding="0" class="tab">
<tr>'.$tabs.'</tr>
</table>';
	}
}

	$c = uq('SELECT ui.ignore_id, ui.id as ignoreent_id,
			u.id, u.alias AS login, u.join_date, u.posted_msg_count, u.home_page
		FROM fud26_user_ignore ui
		LEFT JOIN fud26_users u ON ui.ignore_id=u.id
		WHERE ui.user_id='. _uid);

	$ignore_list = '';
	if (($r = db_rowarr($c))) {
		do {
			$ignore_list .= $r[0] ? '<tr class="'.alt_var('ignore_alt','RowStyleA','RowStyleB').'">
	<td class="GenText wa"><a href="index.php?t=usrinfo&amp;id='.$r[2].'&amp;'._rsid.'">'.$r[3].'</a>&nbsp;<span class="SmallText">(<a href="index.php?t=ignore_list&amp;del='.$r[0].'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">удалить</a>)</span></td>
	<td class="ac">'.$r[5].'</td>
	<td class="ac nw">'.strftime("%a, %d %B %Y %H:%M", $r[4]).'</td>
	<td class="GenText nw"><a href="index.php?t=showposts&amp;'._rsid.'&amp;id='.$r[2].'"><img src="theme/default/images/show_posts.gif" alt="" /></a> '.($FUD_OPT_2 & 1073741824 ? '<a href="index.php?t=email&amp;toi='.$r[2].'&amp;'._rsid.'" rel="nofollow"><img src="theme/default/images/msg_email.gif" alt="" /></a>' : '' ) .($r[6] ? '<a href="'.$r[6].'"><img src="theme/default/images/homepage.gif" alt="" /></a>' : '' ) .'</td>
</tr>' : '<tr class="'.alt_var('ignore_alt','RowStyleA','RowStyleB').'">
	<td colspan="4" class="wa GenText"><span class="anon">'.$GLOBALS['ANON_NICK'].'</span>&nbsp;<span class="SmallText">(<a href="index.php?t=ignore_list&amp;del='.$r[1].'&amp;'._rsid.'">удалить</a>)</span></td>
</tr>';
		} while (($r = db_rowarr($c)));
		$ignore_list = '<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th>Игнорируемые участники</th><th class="nw ac">Сообщения</th><th class="nw ac">Дата регистрации</th><th class="nw ac">Действие</th></tr>
'.$ignore_list.'
</table>';
	}
	unset($c);

if ($FUD_OPT_2 & 2 || $is_a) {	// PUBLIC_STATS is enabled or Admin user.
	$page_gen_time = number_format(microtime(true) - __request_timestamp_exact__, 5);
	$page_stats = $FUD_OPT_2 & 2 ? '<br /><div class="SmallText al">Общее время, затраченное на создание страницы: '.convertPlural($page_gen_time, array(''.$page_gen_time.' секунда',''.$page_gen_time.' секунд')).'</div>' : '<br /><div class="SmallText al">Общее время, затраченное на создание страницы: '.convertPlural($page_gen_time, array(''.$page_gen_time.' секунда',''.$page_gen_time.' секунд')).'</div>';
} else {
	$page_stats = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
<meta name="description" content="<?php echo (!empty($META_DESCR) ? $META_DESCR.'' : $GLOBALS['FORUM_DESCR'].''); ?>" />
<base href="<?php echo $GLOBALS['WWW_ROOT']; ?>" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="theme/default/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
<link rel="search" type="application/opensearchdescription+xml" title="<?php echo $GLOBALS['FORUM_TITLE']; ?> Search" href="<?php echo $GLOBALS['WWW_ROOT']; ?>open_search.php" />
<?php echo $RSS; ?>
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground" valign="top">
<!-- <td class="ForumBackground" valign="top"> -->
<div class="ForumBackground header">
<?php echo ($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="index.php">'._hs.'
      <br /><label accesskey="f" title="Поиск в форумах">Поиск в форумах:<br />
      <input type="text" name="srch" value="" size="15" placeholder="Поиск в форумах" /></label>
      <input type="hidden" name="t" value="search" />
      <input type="submit" name="btn_submit" value="Поиск" class="headbutton" />&nbsp;
    </form>
  </div>
' : ''); ?>
<a href="index.php/.." title="Начало"><img src="theme/default/images/header.gif" alt="" align="left" height="80" />
  <span class="headtitle"><?php echo $GLOBALS['FORUM_TITLE']; ?></span>
</a><br />
<span class="headdescr"><?php echo $GLOBALS['FORUM_DESCR']; ?><br /><br /></span>
</div>
<div class="UserControlPanel">
<a href="/forum/index.php?t=msg&th=102972" class="UserControlPanel nw" title="Правила"><img src="/forum/images/message_icons/icon4.gif" alt=""> Правила форума </a>&nbsp;&nbsp;
  <?php echo $private_msg; ?> 
  <?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<a class="UserControlPanel nw" href="index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Участники"><img src="theme/default/images/top_members'.img_ext.'" alt="" /> Участники</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_3 & 134217728 ? '<a class="UserControlPanel nw" href="index.php?t=cal&amp;'._rsid.'" title="Календарь"><img src="theme/default/images/calendar'.img_ext.'" alt="" /> Календарь</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_1 & 16777216 ? '<a class="UserControlPanel nw" href="index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Поиск"><img src="theme/default/images/top_search'.img_ext.'" alt="" /> Поиск</a>
&nbsp;&nbsp;
<a class="UserControlPanel nw" href="/search.html" title="Yandex поиск"><img src="theme/default/images/top_search'.img_ext.'" alt="" /> Поиск через Yandex</a>
&nbsp;&nbsp;' : ''); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" accesskey="h" href="index.php?t=help_index&amp;<?php echo _rsid; ?>" title="F.A.Q."><img src="theme/default/images/top_help<?php echo img_ext; ?>" alt="" /> F.A.Q.</a>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=uc&amp;'._rsid.'" title="Доступ к панели управления пользователя"><img src="theme/default/images/top_profile'.img_ext.'" alt="" /> Настройки</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=register&amp;'._rsid.'" title="Регистрация"><img src="theme/default/images/top_register'.img_ext.'" alt="" /> Регистрация</a>'); ?>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Выход"><img src="theme/default/images/top_logout'.img_ext.'" alt="" /> Выход [ '.$usr->alias.' ]</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'" title="Вход"><img src="theme/default/images/top_login'.img_ext.'" alt="" /> Вход</a>'); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=index&amp;<?php echo _rsid; ?>" title="Начало"><img src="theme/default/images/top_home<?php echo img_ext; ?>" alt="" /> Начало</a>
  <?php echo ($is_a || ($usr->users_opt & 268435456) ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Административный центр"><img src="theme/default/images/top_admin'.img_ext.'" alt="" /> Административный центр</a>' : ''); ?>
</div>
<?php echo $tabs; ?>
<?php echo $ignore_list; ?>
<br /><br />
<form id="buddy_add" action="index.php?t=ignore_list" method="post"><?php echo _hs; ?><div class="ctb">
<table cellspacing="1" cellpadding="2" class="MiniTable">
<tr><th class="nw">Добавить игнорируемого</th></tr>
<tr class="RowStyleA">
<td class="GenText nw Smalltext">Введите имя участника, которого вы хотите добавить.<?php echo (($FUD_OPT_1 & (8388608|4194304)) ? '<br />Или используйте возможность <a href="javascript://" onclick="javascript: window_open(&#39;'.$GLOBALS['WWW_ROOT'].'index.php?t=pmuserloc&amp;'._rsid.'&amp;js_redr=buddy_add.add_login&amp;overwrite=1&#39;, &#39;user_list&#39;, 400,250);">Поиска</a> для нахождения нужного участника.' : ''); ?><br /><br />
<input type="text" name="add_login" tabindex="1" value="" maxlength="100" size="25" /> <input tabindex="2" type="submit" class="button" name="submit" value="Добавить" /></td></tr>
</table></div></form>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<script type="text/javascript">
/* <![CDATA[ */
document.forms['buddy_add'].add_login.focus();
/* ]]> */
</script>
</td>
<!-- <td class="ForumBackground" valign="top"></td> -->
</tr></table>

<div class="ForumBackground ac foot">
<b>.::</b> <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>">Обратная связь</a> 
<b>::</b> <a href="index.php?t=index&amp;<?php echo _rsid; ?>">Начало</a> 
<b>::</b> <a href="http://www.phpbee.org/">Создание и поддержка сайта www.phpbee.org</a> 

<b>::.</b>
<p>
<span class="SmallText">При поддержке: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br /> Copyright © 2001-2010 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></span>
</p>
</div>
</body></html>
