<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: usrinfo.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}$GLOBALS['__revfs'] = array('&quot;', '&lt;', '&gt;', '&amp;');
$GLOBALS['__revfd'] = array('"', '<', '>', '&');

function reverse_fmt($data)
{
	$s = $d = array();
	foreach ($GLOBALS['__revfs'] as $k => $v) {
		if (strpos($data, $v) !== false) {
			$s[] = $v;
			$d[] = $GLOBALS['__revfd'][$k];
		}
	}

	return $s ? str_replace($s, $d, $data) : $data;
}function &get_all_read_perms($uid, $mod)
{
	$limit = array(0);

	$r = uq('SELECT resource_id, group_cache_opt FROM fud26_group_cache WHERE user_id='. _uid);
	while ($ent = db_rowarr($r)) {
		$limit[$ent[0]] = $ent[1] & 2;
	}
	unset($r);

	if (_uid) {
		if ($mod) {
			$r = uq('SELECT forum_id FROM fud26_mod WHERE user_id='. _uid);
			while ($ent = db_rowarr($r)) {
				$limit[$ent[0]] = 2;
			}
			unset($r);
		}

		$r = uq('SELECT resource_id FROM fud26_group_cache WHERE resource_id NOT IN ('. implode(',', array_keys($limit)) .') AND user_id=2147483647 AND '. q_bitand('group_cache_opt', 2) .' > 0');
		while ($ent = db_rowarr($r)) {
			if (!isset($limit[$ent[0]])) {
				$limit[$ent[0]] = 2;
			}
		}
		unset($r);
	}

	return $limit;
}

function perms_from_obj($obj, $adm)
{
	$perms = 1|2|4|8|16|32|64|128|256|512|1024|2048|4096|8192|16384|32768|262144;

	if ($adm || $obj->md) {
		return $perms;
	}

	return ($perms & $obj->group_cache_opt);
}

function make_perms_query(&$fields, &$join, $fid='')
{
	if (!$fid) {
		$fid = 'f.id';
	}

	if (_uid) {
		$join = ' INNER JOIN fud26_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id='. $fid .' LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id='. $fid .' ';
		$fields = ' COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt ';
	} else {
		$join = ' INNER JOIN fud26_group_cache g1 ON g1.user_id=0 AND g1.resource_id='. $fid .' ';
		$fields = ' g1.group_cache_opt ';
	}
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
}include $GLOBALS['FORUM_SETTINGS_PATH'] .'ip_filter_cache';
	include $GLOBALS['FORUM_SETTINGS_PATH'] .'login_filter_cache';
	include $GLOBALS['FORUM_SETTINGS_PATH'] .'email_filter_cache';

function is_ip_blocked($ip)
{
	if (empty($GLOBALS['__FUD_IP_FILTER__'])) {
		return;
	}
	$block =& $GLOBALS['__FUD_IP_FILTER__'];
	list($a,$b,$c,$d) = explode('.', $ip);

	if (!isset($block[$a])) {
		return;
	}
	if (isset($block[$a][$b][$c][$d])) {
		return 1;
	}

	if (isset($block[$a][256])) {
		$t = $block[$a][256];
	} else if (isset($block[$a][$b])) {
		$t = $block[$a][$b];
	} else {
		return;
	}

	if (isset($t[$c])) {
		$t = $t[$c];
	} else if (isset($t[256])) {
		$t = $t[256];
	} else {
		return;
	}

	if (isset($t[$d]) || isset($t[256])) {
		return 1;
	}
}

function is_login_blocked($l)
{
	foreach ($GLOBALS['__FUD_LGN_FILTER__'] as $v) {
		if (preg_match($v, $l)) {
			return 1;
		}
	}
	return;
}

function is_email_blocked($addr)
{
	if (empty($GLOBALS['__FUD_EMAIL_FILTER__'])) {
		return;
	}
	$addr = strtolower($addr);
	foreach ($GLOBALS['__FUD_EMAIL_FILTER__'] as $k => $v) {
		if (($v && (strpos($addr, $k) !== false)) || (!$v && preg_match($k, $addr))) {
			return 1;
		}
	}
	return;
}

function is_allowed_user(&$usr, $simple=0)
{
	/* Check if the ban expired. */
	if (($banned = $usr->users_opt & 65536) && $usr->ban_expiry && $usr->ban_expiry < __request_timestamp__) {
		q('UPDATE fud26_users SET users_opt = '. q_bitand('users_opt', ~65536) .' WHERE id='. $usr->id);
		$usr->users_opt ^= 65536;
		$banned = 0;
	} 

	if ($banned || is_email_blocked($usr->email) || is_login_blocked($usr->login) || is_ip_blocked(get_ip())) {
		$ban_expiry = (int) $usr->ban_expiry;
		if (!$simple) { // On login page we already have anon session.
			ses_delete($usr->sid);
			$usr = ses_anon_make();
		}
		setcookie($GLOBALS['COOKIE_NAME'].'1', 'd34db33fd34db33fd34db33fd34db33f', ($ban_expiry ? $ban_expiry : (__request_timestamp__ + 63072000)), $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
		if ($banned) {
			error_dialog('ОШИБКА: Вы были забанены.', 'Вам был '.($ban_expiry ? 'запрещен вход на форум до '.strftime("%a, %d %B %Y %H:%M", $ban_expiry) : 'навсегда запрещен вход на форум' )  .', скорее всего в результате грубого нарушения правил.');
		} else {
			error_dialog('ОШИБКА: Ваш аккаунт внесен в список запрещенных.', 'Вам был заблокирован вход на форум одним из установленных фильтров.');
		}
	}

	if ($simple) {
		return;
	}

	if ($GLOBALS['FUD_OPT_1'] & 1048576 && $usr->users_opt & 262144) {
		error_dialog('ОШИБКА: Ваша регистрация пока не была утверждена', 'Мы пока не получили разрешение от ваших родителей/опекунов на ваше участие в форуме. Если вы потеряли форму разрешения COPPA, <a href="index.php?t=coppa_fax&amp;'._rsid.'">просмотрите ее еще раз</a>.');
	}

	if ($GLOBALS['FUD_OPT_2'] & 1 && !($usr->users_opt & 131072)) {
		std_error('emailconf');
	}

	if ($GLOBALS['FUD_OPT_2'] & 1024 && $usr->users_opt & 2097152) {
		error_dialog('Непроверенная учётная запись', 'Администратор установил режим ручного просмотра всех учетных записей пользователей перед их активацией. Пока ваша учетная запись не будет проверена администратором, вы не сможете использовать все возможности форума.');
	}
}

	if (!isset($_GET['id']) || !(int)$_GET['id']) {
		invl_inp_err();
	}
	if ($FUD_OPT_3 & 32 && !_uid) {
		if (__fud_real_user__) {
			is_allowed_user($usr);
		} else {
			std_error('login');
		}
	}

	if (!($u = db_sab('SELECT s.time_sec, u.*, u.alias AS login, l.name AS level_name, l.level_opt, l.img AS level_img FROM fud26_users u LEFT JOIN fud26_ses s ON u.id=s.user_id LEFT JOIN fud26_level l ON l.id=u.level_id WHERE u.id='. (int)$_GET['id']))) {
		std_error('user');
	}

	if (!_uid && __fud_cache($u->last_visit)) {
		return;
	}

	$obj = $u; // A little hack for online status, so we don't need to add more messages.

	if ($FUD_OPT_1 & 28 && $u->users_opt & 8388608 && $u->level_opt & (2|1) == 1) {
		$level_name = $level_image = '';
	} else {
		$level_name = $u->level_name ? $u->level_name.'<br />' : '';
		$level_image = $u->level_img ? '<img src="images/'.$u->level_img.'" alt="" /><br />' : '';
	}

	if (!$is_a) {
		$frm_perms = get_all_read_perms(_uid, ($usr->users_opt & 524288));
		$forum_list = implode(',', array_keys($frm_perms, 2));
	} else {
		$forum_list = 1;
	}

	$moderation = '';
	if ($u->users_opt & 524288 && $forum_list) {
		$c = uq('SELECT f.id, f.name FROM fud26_mod mm INNER JOIN fud26_forum f ON mm.forum_id=f.id INNER JOIN fud26_cat c ON f.cat_id=c.id WHERE '. ($is_a ? '' : 'f.id IN('. $forum_list .') AND ') .'mm.user_id='. $u->id);
		while ($r = db_rowarr($c)) {
			$moderation .= '<a href="index.php?t='.t_thread_view.'&amp;frm_id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a>&nbsp;';
		}
		unset($c);
		if ($moderation) {
			$moderation = 'Модерируемые форумы:&nbsp;'.$moderation;
		}
	}

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}

	$TITLE_EXTRA = ': Информация об участнике '.$u->alias;

	ses_update_status($usr->sid, 'Просмотр персональной информации о <a href="index.php?t=usrinfo&amp;id='.$u->id.'">'.$u->alias.'</a>');

	$avg = round($u->posted_msg_count / ((__request_timestamp__ - $u->join_date) / 86400), 2);
	if ($avg > $u->posted_msg_count) {
		$avg = $u->posted_msg_count;
	}

	$last_post = '';
	if ($u->u_last_post_id) {
		$r = db_saq('SELECT m.subject, m.id, m.post_stamp, t.forum_id FROM fud26_msg m INNER JOIN fud26_thread t ON m.thread_id=t.id WHERE m.id='. $u->u_last_post_id);
		if ($is_a || !empty($frm_perms[$r[3]])) {
			$last_post = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="vt nw GenText">Последнее сообщение:</td><td class="GenText"><span class="DateText">'.strftime("%a, %d %B %Y %H:%M", $r[2]).'</span><br /><a href="index.php?t='.d_thread_view.'&amp;goto='.$r[1].'&amp;'._rsid.'#msg_'.$r[1].'">'.$r[0].'</a></td></tr>';
		}
	}

	if ($u->users_opt & 1) {
		$email_link = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="GenText nw">Электронная почта:</td><td class="GenText"><a href="mailto:'.$u->email.'">'.$u->email.'</a></td></tr>';
	} else if ($FUD_OPT_2 & 1073741824) {
		$email_link = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Электронная почта:</td><td class="GenText">[<a href="index.php?t=email&amp;toi='.$u->id.'&amp;'._rsid.'" rel="nofollow">Отправить пользователю письмо</a>]</td></tr>';
	} else {
		$email_link = '';
	}

	if ($FUD_OPT_2 & 8192 && ($referals = q_singleval('SELECT count(*) FROM fud26_users WHERE referer_id='. $u->id))) {
		$referals = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Приглашенные:</td><td class="GenText"><a href="index.php?t=list_referers&amp;'._rsid.'">'.$referals.' пользователей</a></td></tr>';
	} else {
		$referals = '';
	}

	if (_uid && _uid != $u->id && !q_singleval('SELECT id FROM fud26_buddy WHERE user_id='. _uid .' AND bud_id='. $u->id)) {
		$buddy = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Контакт:</td><td class="GenText"><a href="index.php?t=buddy_list&amp;add='.$u->id.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">в контакты</a></td></tr>';
	} else {
		$buddy = '';
	}

	if ($forum_list && ($polls = q_singleval('SELECT count(*) FROM fud26_poll p INNER JOIN fud26_forum f ON p.forum_id=f.id WHERE p.owner='. $u->id .' AND f.cat_id>0 '.($is_a ? '' : ' AND f.id IN('. $forum_list .')')))) {
		$polls = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Голосования:</td><td class="GenText"><a href="index.php?t=polllist&amp;uid='.$u->id.'&amp;'._rsid.'">'.$polls.'</a></td></tr>';
	} else {
		$polls = '';
	}

	if ($u->users_opt & 1024) {
		$gender = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Пол:</td><td class="GenText">Мужской</td></tr>';
	} else if (!($u->users_opt & 512)) {
		$gender = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Пол:</td><td class="GenText">Женский</td></tr>';
	} else {
		$gender = '';
	}

	if ($u->birthday) {
		// Convert birthday string to a date.
		$yyyy = (int)substr($u->birthday, 4);
		$mm   = (int)substr($u->birthday, 0, 2);
		$dd   = (int)substr($u->birthday, 2, 2);
		$u->birthday = mktime(0, 0, 0, $mm, $dd, $yyyy);
		$birth_date = '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Дата рождения:</td><td class="GenText">'.strftime("%a, %B %d, %Y", $u->birthday).'</td></tr>';
	} else {
		$birth_date = '';
	}
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
<link rel="stylesheet" href="theme/vp1/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
<link rel="search" type="application/opensearchdescription+xml" title="<?php echo $GLOBALS['FORUM_TITLE']; ?> Search" href="<?php echo $GLOBALS['WWW_ROOT']; ?>open_search.php" />
<?php echo $RSS; ?>
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground" valign="top">
<!--  -->
<div class="ForumBackground vpheader">
<span class="headdescr">
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr height=100><td width=320 valign=bottom>
<a href="/"><img src="/forum/logo.gif" width=312 height=79 border=0 alt="ВелоПитер"></a>
</td><td valign=bottom>
<?php echo ($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 &0 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="index.php">'._hs.'
      <br /><label accesskey="f" title="Поиск в форумах">Поиск в форумах:<br />
      <input type="text" name="srch" value="" size="15" placeholder="Поиск в форумах" /></label>
      <input type="hidden" name="t" value="search" />
      <input type="submit" name="btn_submit" value="Поиск" class="headbutton" />&nbsp;
    </form>
</div>
' : ''); ?>
</td>
<td align=left valign=bottom cellpadding=5 width="25%">
 <? include("../newstape_inc.php"); ?></td>
<td align=right width=350 valign=bottom>


<!-- banners start -->
<div align=right>
<table cellspacing=5 cellpadding=5>
<tr valign=top>

<td width="100" height=100 align=right>
<a href="http://mountainpeaks.ru/" target="_blank">
<img border="0" src="http://velopiter.spb.ru/banner_gv.gif" alt="www.chillengrillen.ru"
width="100" height="100"></a>

<td width="100" height=100 align=right>
<a href="http://velopiter.spb.ru/activeinfo/info.php?fid=14&c=1" target="_blank">
<img border="0" src="http://velopiter.spb.ru/bc.gif" alt="Балтийская торговая группа"
width="100" height="100"></a>

<td width="100" height=100 align=right>
<a href="http://www.velodrive.ru/" target="_blank">
<img border="0" src="http://velopiter.spb.ru/bf.gif" alt="Велодрайв"
width="100" height="100"></a>

<td width="100" height=100 align=right>
<a href="http://www.chillengrillen.ru/" target="_blank">
<img border="0" src="http://velopiter.spb.ru/chillengrillen.gif" alt="www.chillengrillen.ru"
width="100" height="100"></a>

</tr></table></div>
<!--- banners end-->


</td></tr></table>
</span>
</div>
<div class="UserControlPanel">
<a href="/forum/index.php?t=msg&th=102972" class="UserControlPanel nw" title="Правила"><img src="/forum/images/message_icons/icon4.gif" alt=""> Правила форума </a>&nbsp;&nbsp;
  <?php echo $private_msg; ?> 
  <?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<a class="UserControlPanel nw" href="index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Участники"><img src="theme/vp1/images/top_members'.img_ext.'" alt="" /> Участники</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_3 & 134217728 ? '<a class="UserControlPanel nw" href="index.php?t=cal&amp;'._rsid.'" title="Календарь"><img src="theme/vp1/images/calendar'.img_ext.'" alt="" /> Календарь</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_1 & 16777216 ? '<a class="UserControlPanel nw" href="index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Поиск"><img src="theme/vp1/images/top_search'.img_ext.'" alt="" /> Поиск</a>
&nbsp;&nbsp;
<a class="UserControlPanel nw" href="/search.html" title="Yandex поиск"><img src="theme/vp1/images/top_search'.img_ext.'" alt="" /> Поиск через Yandex</a>
&nbsp;&nbsp;' : ''); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" accesskey="h" href="index.php?t=help_index&amp;<?php echo _rsid; ?>" title="F.A.Q."><img src="theme/vp1/images/top_help<?php echo img_ext; ?>" alt="" /> F.A.Q.</a>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=uc&amp;'._rsid.'" title="Доступ к панели управления пользователя"><img src="theme/vp1/images/top_profile'.img_ext.'" alt="" /> Настройки</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=register&amp;'._rsid.'" title="Регистрация"><img src="theme/vp1/images/top_register'.img_ext.'" alt="" /> Регистрация</a>'); ?>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Выход"><img src="theme/vp1/images/top_logout'.img_ext.'" alt="" /> Выход [ '.$usr->alias.' ]</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'" title="Вход"><img src="theme/vp1/images/top_login'.img_ext.'" alt="" /> Вход</a>'); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=index&amp;<?php echo _rsid; ?>" title="Начало"><img src="theme/vp1/images/top_home<?php echo img_ext; ?>" alt="" /> Начало</a>
  <?php echo ($is_a || ($usr->users_opt & 268435456) ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Административный центр"><img src="theme/vp1/images/top_admin'.img_ext.'" alt="" /> Административный центр</a>' : ''); ?>
</div>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="2" class="wa"><?php echo (!($u->users_opt & 32768) && (($u->time_sec + $LOGEDIN_TIMEOUT * 60) > __request_timestamp__) ? '<img src="theme/vp1/images/online'.img_ext.'" alt="онлайн" title="онлайн" />' : '<img src="theme/vp1/images/offline'.img_ext.'" alt="оффлайн" title="оффлайн" />'); ?>&nbsp;Информация об участнике <?php echo $u->alias; ?></th></tr>
<tr class="RowStyleA"><td class="nw GenText">Дата регистрации:</td><td class="wa DateText"><?php echo strftime("%a, %B %d, %Y", $u->join_date); ?></td></tr>
<tr class="RowStyleB"><td class="vt nw GenText">Количество сообщений:</td><td class="GenText"><?php echo convertPlural($u->posted_msg_count, array(''. $u->posted_msg_count.' сообщение',''. $u->posted_msg_count.' сообщения',''. $u->posted_msg_count.' сообщений')); ?> (в среднем <?php echo convertPlural($avg, array(''. $avg.' сообщение',''. $avg.' сообщения',''. $avg.' сообщений')); ?> в день)<br /><a href="index.php?t=showposts&amp;id=<?php echo $u->id; ?>&amp;<?php echo _rsid; ?>">Показать все сообщения от <?php echo $u->alias; ?></a></td></tr>
<?php echo ($u->users_opt & 32768 ? '' : '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Реальное имя:</td><td class="GenText">'.$u->name.'</td></tr>'); ?>
<?php echo (($level_name || $moderation || $level_image || $u->custom_status) ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw vt GenText">Статус:</td><td class="GenText">
<span class="LevelText">
'.$level_name.'
'.$level_image.'
'.($u->custom_status ? $u->custom_status.'<br />' : '' )  .'
</span>
'.$moderation.'
</td></tr>' : ''); ?>
<?php echo (($FUD_OPT_1 & 28 && $u->users_opt & 8388608 && !($u->level_opt & 2)) ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="vt nw GenText">Картинка:</td><td class="GenText">'.$u->avatar_loc.'</td></tr>' : ''); ?>
<?php echo $last_post; ?>
<?php echo ($u->last_visit && !($u->users_opt & 32768) ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="vt nw GenText">Последнее посещение:</td><td class="GenText DateText">'.strftime("%a, %d %B %Y %H:%M", $u->last_visit).'</td></tr>' : ''); ?>
<?php echo $polls; ?>
<?php echo (($FUD_OPT_2 & 65536 && $u->user_image && strpos($u->user_image, '://')) ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="vt nw GenText">Картинка:</td><td class="GenText"><img src="'.$u->user_image.'" alt="" /></td></tr>' : ''); ?>
<?php echo $email_link; ?>
<?php echo (($FUD_OPT_1 & 1024 && _uid) ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Личное сообщение:</td><td class="GenText"><a href="index.php?t=ppost&amp;'._rsid.'&amp;toi='.$u->id.'"><img src="theme/vp1/images/msg_pm.gif" alt="" /></a></td></tr>' : ''); ?>
<?php echo $buddy; ?>
<?php echo $referals; ?>
<?php echo ($u->home_page ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Домашняя страница, сайт:</td><td class="GenText"><a href="'.$u->home_page.'" rel="nofollow">'.$u->home_page.'</a></td></tr>' : ''); ?>
<?php echo $gender; ?>
<?php echo ($u->location ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Город:</td><td class="GenText">'.$u->location.'</td></tr>' : ''); ?>
<?php echo ($u->occupation ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Род деятельности:</td><td class="GenText">'.$u->occupation.'</td></tr>' : ''); ?>
<?php echo ($u->interests ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Интересы:</td><td class="GenText">'.$u->interests.'</td></tr>' : ''); ?>
<?php echo ($u->bio ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Биография:</td><td class="GenText">'.$u->bio.'</td></tr>' : ''); ?>
<?php echo $birth_date; ?>
<?php echo ($u->icq ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw vt GenText"><a name="icq_msg">Послать сообщение на ICQ</a></td><td class="GenText">
		'.$u->icq.' <img src="http://web.icq.com/whitepages/online?icq='.$u->icq.'&amp;img=5" /><br />
			<table class="icqCP">
			<tr><td colspan="2">
				<form action="http://wwp.icq.com/scripts/WWPMsg.dll" method="post">
				<b>Панель присутствия в сети ICQ</b>
			</td></tr>
			<tr>
				<td>
					Имя автора:<br />
					<input type="text" name="from" value="" size="15" maxlength="40" onfocus="this.select()" />
				</td>
				<td>
					E-mail автора:<br />
					<input type="text" name="fromemail" value="" size="15" maxlength="40" onfocus="this.select()" />
				</td>
			</tr>
			<tr>
				<td colspan="2">
					Тема<br />
					<input type="text" spellcheck="true" name="subject" value="" size="32" /><br />
					Сообщение<br />
					<textarea name="body" rows="3" cols="32" wrap="Virtual"></textarea>
					<input type="hidden" name="to" value="'.$u->icq.'" /><br />
				</td>
			</tr>
			<tr><td colspan="2" align="right"><input type="submit" class="button" name="Send" value="Отправить" /></td></tr>
			</form>
			</table>
			</td>
</tr>' : ''); ?>
<?php echo ($u->aim ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Сеть AIM:</td><td class="GenText"><a href="aim:goim?screenname='.$u->aim.'&amp;message=Hello+Are+you+there?"><img src="theme/vp1/images/aim'.img_ext.'" title="'.$obj->aim.'" alt="" />'.htmlentities(urldecode($u->aim)).'</a></td></tr>' : ''); ?>
<?php echo ($u->yahoo ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Сеть Yahoo:</td><td class="GenText"><a href="http://edit.yahoo.com/config/send_webmesg?.target='.$u->yahoo.'&amp;.src=pg"><img src="theme/vp1/images/yahoo'.img_ext.'" title="'.$obj->yahoo.'" alt="" />'.htmlentities(urldecode($u->yahoo)).'</a></td></tr>' : ''); ?>
<?php echo ($u->msnm ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Сеть MSN:</td><td class="GenText"><img src="theme/vp1/images/msnm'.img_ext.'" title="'.$obj->msnm.'" alt="" />'.char_fix(htmlspecialchars(urldecode($u->msnm))).'</td></tr>' : ''); ?>
<?php echo ($u->jabber ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Jabber:</td><td class="GenText"><img src="theme/vp1/images/jabber'.img_ext.'" title="'.$obj->jabber.'" alt="" />'.$u->jabber.'</td></tr>' : ''); ?>
<?php echo ($u->google ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Google Talk:</td><td class="GenText"><img src="theme/vp1/images/google'.img_ext.'" title="'.$obj->google.'" alt="" />'.$u->google.'</td></tr>' : ''); ?>
<?php echo ($u->skype ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Имя в Skype:</td><td class="GenText"><a href="callto://'.$u->skype.'"><img src="theme/vp1/images/skype'.img_ext.'" title="'.$obj->skype.'" alt="" />'.$u->skype.'</a></td></tr>' : ''); ?>
<?php echo ($u->twitter ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Twitter:</td><td class="GenText"><a href="http://twitter.com/'.$u->twitter.'"><img src="theme/vp1/images/twitter'.img_ext.'" title="'.$obj->twitter.'" alt="" />'.$u->twitter.'</a></td></tr>' : ''); ?>
<?php echo (($FUD_OPT_2 & 2048 && $u->affero) ? '<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Имя в системе Affero</td><td class="GenText"><a href="http://svcs.affero.net/user-history.php?u='.$u->affero.'">'.htmlspecialchars(urldecode($u->affero)).'</a></td></tr>' : ''); ?>
<?php echo ($is_a ? '
<tr class="'.alt_var('search_alt','RowStyleA','RowStyleB').'"><td class="nw GenText">Опции админа.</td>
<td>
<a href="'.$GLOBALS['WWW_ROOT'].'adm/admuser.php?usr_id='.$u->id.'&amp;S='.s.'&amp;act=1&amp;SQ='.$GLOBALS['sq'].'">Править</a> || <a href="'.$GLOBALS['WWW_ROOT'].'adm/admuser.php?usr_id='.$u->id.'&amp;S='.s.'&amp;act=del&amp;SQ='.$GLOBALS['sq'].'">Удалить</a> || 
'.($u->users_opt & 65536 ? '
<a href="'.$GLOBALS['WWW_ROOT'].'adm/admuser.php?act=block&amp;usr_id='.$u->id.'&amp;S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Разрешить</a>
' : '
<a href="'.$GLOBALS['WWW_ROOT'].'adm/admuser.php?act=block&amp;usr_id='.$u->id.'&amp;S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Запретить</a>
' )  .'
</td></tr>
' : ''); ?>

<tr class="RowStyleC"><td class="nw ar GenText" colspan="2"><a href="index.php?t=showposts&amp;id=<?php echo $u->id; ?>&amp;<?php echo _rsid; ?>">Показать все сообщения от <?php echo $u->alias; ?></a></td></tr>
</table>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
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
<div align=right>
<span class="SmallText">

<!-- SpyLOG v2 f:0211 -->
<script language="javascript">
u="u166.09.spylog.com";
d=document;
nv=navigator;
na=nv.appName;
p=0;
j="N";
d.cookie="b=b";
c=0;
bv=Math.round(parseFloat(nv.appVersion)*100);
if (d.cookie) c=1;
n=(na.substring(0,2)=="Mi")?0:1;
rn=Math.random();
z="p="+p+"&rn="+rn+"&c="+c;
if (self!=top) {fr=1;} else {fr=0;}
sl="1.0";
pl="";
sl="1.1";
j = (navigator.javaEnabled()?"Y":"N");
sl="1.2";
s=screen;
px=(n==0)?s.colorDepth:s.pixelDepth;
z+="&"+"wh=";
z+="s.width";
z+="x"+s.height+"&";
z+="px="+px;
sl="1.3"
y="";
y+="<a href=\"http://"+u+"/cnt?f=3&p="+p+"&rn="+rn+"\" target=_blank>";
y+="<img src=\"http://"+u+"/cnt?"+z+"&j="+j+"&sl="+sl+ "&r="+escape(d.referrer)+"&fr="+fr+"&pg="+escape(window.location.href); y+="\" border=0 width=88 height=31 alt=\"SpyLOG\">"; 
y+="</a>";
d.write(y);if(!n) { d.write("<"+"!--"); }
//-->
</script>
<noscript>
<a href="http://u166.09.spylog.com/cnt?f=3&p=0" target=_blank><img src="http://u166.09.spylog.com/cnt?p=0" alt="SpyLOG" border="0" width=88 height=31></a>
</noscript>
<script language="javascript1.2">
<!-- if(!n){ d.write("--"+">"); }
//-->
</script>
<!-- SpyLOG -->

 <!-- Yandex.Metrika -->
<script src="http://mc.yandex.ru/metrika/watch.js" type="text/javascript"></script>
<script type="text/javascript">
try { var yaCounter147212 = new Ya.Metrika(147212); } catch(e){}
</script>
<noscript><img src="http://mc.yandex.ru/watch/147212" style="position:absolute" alt="" /></noscript>
<!-- /Yandex.Metrika -->

<a href="http://www.vvv.ru/cnt.php3?id=99" target=_top><img
src="http://cnt.vvv.ru/cgi-bin/cnt?id=99" width=88 height=31 border=0
alt="Экстремальный портал VVV.RU"></a>
</span>

</div>
</body></html>
