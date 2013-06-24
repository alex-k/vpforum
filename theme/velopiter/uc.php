<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: uc.php.t 5071 2010-11-10 18:32:04Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function tmpl_draw_select_opt($values, $names, $selected)
{
	$vls = explode("\n", $values);
	$nms = explode("\n", $names);

	if (count($vls) != count($nms)) {
		exit("FATAL ERROR: inconsistent number of values inside a select<br />\n");
	}

	$options = '';
	foreach ($vls as $k => $v) {
		$options .= '<option value="'.$v.'"'.($v == $selected ? ' selected="selected"' : '' )  .'>'.$nms[$k].'</option>';
	}

	return $options;
}function pager_replace(&$str, $s, $c)
{
	$str = str_replace(array('%s', '%c'), array($s, $c), $str);
}

function tmpl_create_pager($start, $count, $total, $arg, $suf='', $append=1, $js_pager=0, $no_append=0)
{
	if (!$count) {
		$count =& $GLOBALS['POSTS_PER_PAGE'];
	}
	if ($total <= $count) {
		return;
	}

	$upfx = '';
	if ($GLOBALS['FUD_OPT_2'] & 32768 && (!empty($_SERVER['PATH_INFO']) || strpos($arg, '?') === false)) {
		if (!$suf) {
			$suf = '/';
		} else if (strpos($suf, '//') !== false) {
			$suf = preg_replace('!/+!', '/', $suf);
		}
	} else if (!$no_append) {
		$upfx = '&amp;start=';
	}

	$cur_pg = ceil($start / $count);
	$ttl_pg = ceil($total / $count);

	$page_pager_data = '';

	if (($page_start = $start - $count) > -1) {
		if ($append) {
			$page_first_url = $arg . $upfx . $suf;
			$page_prev_url = $arg . $upfx . $page_start . $suf;
		} else {
			$page_first_url = $page_prev_url = $arg;
			pager_replace($page_first_url, 0, $count);
			pager_replace($page_prev_url, $page_start, $count);
		}

		$page_pager_data .= !$js_pager ? '&nbsp;<a href="'.$page_first_url.'" class="PagerLink">&laquo;</a>&nbsp;&nbsp;<a href="'.$page_prev_url.'" accesskey="p" class="PagerLink">&lsaquo;</a>&nbsp;&nbsp;' : '&nbsp;<a href="javascript://" onclick="'.$page_first_url.'" class="PagerLink">&laquo;</a>&nbsp;&nbsp;<a href="javascript://" onclick="'.$page_prev_url.'" class="PagerLink">&lsaquo;</a>&nbsp;&nbsp;';
	}

	$mid = ceil($GLOBALS['GENERAL_PAGER_COUNT'] / 2);

	if ($ttl_pg > $GLOBALS['GENERAL_PAGER_COUNT']) {
		if (($mid + $cur_pg) >= $ttl_pg) {
			$end = $ttl_pg;
			$mid += $mid + $cur_pg - $ttl_pg;
			$st = $cur_pg - $mid;
		} else if (($cur_pg - $mid) <= 0) {
			$st = 0;
			$mid += $mid - $cur_pg;
			$end = $mid + $cur_pg;
		} else {
			$st = $cur_pg - $mid;
			$end = $mid + $cur_pg;
		}

		if ($st < 0) {
			$start = 0;
		}
		if ($end > $ttl_pg) {
			$end = $ttl_pg;
		}
		if ($end - $start > $GLOBALS['GENERAL_PAGER_COUNT']) {
			$end = $start + $GLOBALS['GENERAL_PAGER_COUNT'];
		}
	} else {
		$end = $ttl_pg;
		$st = 0;
	}

	while ($st < $end) {
		if ($st != $cur_pg) {
			$page_start = $st * $count;
			if ($append) {
				$page_page_url = $arg . $upfx . $page_start . $suf;
			} else {
				$page_page_url = $arg;
				pager_replace($page_page_url, $page_start, $count);
			}
			$st++;
			$page_pager_data .= !$js_pager ? '<a href="'.$page_page_url.'" class="PagerLink">'.$st.'</a>&nbsp;&nbsp;' : '<a href="javascript://" onclick="'.$page_page_url.'" class="PagerLink">'.$st.'</a>&nbsp;&nbsp;';
		} else {
			$st++;
			$page_pager_data .= !$js_pager ? $st.'&nbsp;&nbsp;' : $st.'&nbsp;&nbsp;';
		}
	}

	$page_pager_data = substr($page_pager_data, 0 , strlen((!$js_pager ? '&nbsp;&nbsp;' : '&nbsp;&nbsp;')) * -1);

	if (($page_start = $start + $count) < $total) {
		$page_start_2 = ($st - 1) * $count;
		if ($append) {
			$page_next_url = $arg . $upfx . $page_start . $suf;
			$page_last_url = $arg . $upfx . $page_start_2 . $suf;
		} else {
			$page_next_url = $page_last_url = $arg;
			pager_replace($page_next_url, $upfx . $page_start, $count);
			pager_replace($page_last_url, $upfx . $page_start_2, $count);
		}
		$page_pager_data .= !$js_pager ? '&nbsp;&nbsp;<a href="'.$page_next_url.'" accesskey="n" class="PagerLink">&rsaquo;</a>&nbsp;&nbsp;<a href="'.$page_last_url.'" class="PagerLink">&raquo;</a>' : '&nbsp;&nbsp;<a href="javascript://" onclick="'.$page_next_url.'" class="PagerLink">&rsaquo;</a>&nbsp;&nbsp;<a href="javascript://" onclick="'.$page_last_url.'" class="PagerLink">&raquo;</a>';
	}

	return !$js_pager ? '<span class="SmallText fb">Страниц ('.$ttl_pg.'): ['.$page_pager_data.']</span>' : '<span class="SmallText fb">Страниц ('.$ttl_pg.'): ['.$page_pager_data.']</span>';
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
}function buddy_add($user_id, $bud_id)
{
	q('INSERT INTO fud26_buddy (bud_id, user_id) VALUES ('. $bud_id .', '. $user_id .')');
	return buddy_rebuild_cache($user_id);
}

function buddy_delete($user_id, $bud_id)
{
	q('DELETE FROM fud26_buddy WHERE user_id='. $user_id .' AND bud_id='. $bud_id);
	return buddy_rebuild_cache($user_id);
}

function buddy_rebuild_cache($uid)
{
	$arr = array();
	$q = uq('SELECT bud_id FROM fud26_buddy WHERE user_id='. $uid);
	while ($ent = db_rowarr($q)) {
		$arr[$ent[0]] = 1;
	}
	unset($q);

	if ($arr) {
		q('UPDATE fud26_users SET buddy_list='. _esc(serialize($arr)) .' WHERE id='. $uid);
		return $arr;
	}
	q('UPDATE fud26_users SET buddy_list=NULL WHERE id='. $uid);
}function is_notified($user_id, $thread_id)
{
	return q_singleval('SELECT * FROM fud26_thread_notify WHERE thread_id='. (int)$thread_id .' AND user_id='. $user_id);
}

function thread_notify_add($user_id, $thread_id)
{
	db_li('INSERT INTO fud26_thread_notify (user_id, thread_id) VALUES ('. $user_id .', '. (int)$thread_id .')', $ret);
}

function thread_notify_del($user_id, $thread_id)
{
	q('DELETE FROM fud26_thread_notify WHERE thread_id='. (int)$thread_id .' AND user_id='. $user_id);
}

function thread_bookmark_add($user_id, $thread_id)
{
	db_li('INSERT INTO fud26_bookmarks (user_id, thread_id) VALUES ('. $user_id .', '. (int)$thread_id .')', $ret);
}

function thread_bookmark_del($user_id, $thread_id)
{
	q('DELETE FROM fud26_bookmarks WHERE thread_id='. (int)$thread_id .' AND user_id='. $user_id);
}function is_forum_notified($user_id, $forum_id)
{
	return q_singleval('SELECT id FROM fud26_forum_notify WHERE forum_id='. $forum_id .' AND user_id='. $user_id);
}

function forum_notify_add($user_id, $forum_id)
{
	db_li('INSERT INTO fud26_forum_notify (user_id, forum_id) VALUES ('. $user_id .', '. $forum_id .')', $ret);
}

function forum_notify_del($user_id, $forum_id)
{
	q('DELETE FROM fud26_forum_notify WHERE forum_id='. $forum_id .' AND user_id='. $user_id);
}

	if (__fud_real_user__) {
		is_allowed_user($usr);
	} else {
		std_error('login');
	}

	ses_update_status($usr->sid, 'Просмотр персональной контрольной панели.');

if (_uid) {
	$admin_cp = $accounts_pending_approval = $group_mgr = $reported_msgs = $custom_avatar_queue = $mod_que = $thr_exch = '';

	if ($usr->users_opt & 524288 || $is_a) {	// is_mod or admin.
		if ($is_a) {
			// Approval of custom Avatars.
			if ($FUD_OPT_1 & 32 && ($avatar_count = q_singleval('SELECT count(*) FROM fud26_users WHERE users_opt>=16777216 AND '. q_bitand('users_opt', 16777216) .' > 0'))) {
				$custom_avatar_queue = '| <a href="adm/admapprove_avatar.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Очередь внешних картинок</a> <span class="GenTextRed">('.$avatar_count.')</span>';
			}

			// All reported messages.
			if ($report_count = q_singleval('SELECT count(*) FROM fud26_msg_report')) {
				$reported_msgs = '| <a href="index.php?t=reported&amp;'._rsid.'" rel="nofollow">Извещения о сообщениях</a> <span class="GenTextRed">('.$report_count.')</span>';
			}

			// All thread exchange requests.
			if ($thr_exchc = q_singleval('SELECT count(*) FROM fud26_thr_exchange')) {
				$thr_exch = '| <a href="index.php?t=thr_exch&amp;'._rsid.'">Перенос темы</a> <span class="GenTextRed">('.$thr_exchc.')</span>';
			}

			// All account approvals.
			if ($FUD_OPT_2 & 1024 && ($accounts_pending_approval = q_singleval('SELECT count(*) FROM fud26_users WHERE users_opt>=2097152 AND '. q_bitand('users_opt', 2097152) .' > 0 AND id > 0'))) {
				$accounts_pending_approval = '| <a href="adm/admaccapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Учётные записи, ожидающие утверждения</a> <span class="GenTextRed">('.$accounts_pending_approval.')</span>';
			} else {
				$accounts_pending_approval = '';
			}

			$q_limit = '';
		} else {
			// Messages reported in moderated forums.
			if ($report_count = q_singleval('SELECT count(*) FROM fud26_msg_report mr INNER JOIN fud26_msg m ON mr.msg_id=m.id INNER JOIN fud26_thread t ON m.thread_id=t.id INNER JOIN fud26_mod mm ON t.forum_id=mm.forum_id AND mm.user_id='. _uid)) {
				$reported_msgs = '| <a href="index.php?t=reported&amp;'._rsid.'" rel="nofollow">Извещения о сообщениях</a> <span class="GenTextRed">('.$report_count.')</span>';
			}

			// Thread move requests in moderated forums.
			if ($thr_exchc = q_singleval('SELECT count(*) FROM fud26_thr_exchange te INNER JOIN fud26_mod m ON m.user_id='. _uid .' AND te.frm=m.forum_id')) {
				$thr_exch = '| <a href="index.php?t=thr_exch&amp;'._rsid.'">Перенос темы</a> <span class="GenTextRed">('.$thr_exchc.')</span>';
			}

			$q_limit = ' INNER JOIN fud26_mod mm ON f.id=mm.forum_id AND mm.user_id='. _uid;
		}

		// Messages requiring approval.
		if ($approve_count = q_singleval('SELECT count(*) FROM fud26_msg m INNER JOIN fud26_thread t ON m.thread_id=t.id INNER JOIN fud26_forum f ON t.forum_id=f.id '. $q_limit .' WHERE m.apr=0 AND f.forum_opt>=2')) {
			$mod_que = '<a href="index.php?t=modque&amp;'._rsid.'">Очередь модератора</a> <span class="GenTextRed">('.$approve_count.')</span>';
		}
	} else if ($usr->users_opt & 268435456 && $FUD_OPT_2 & 1024 && ($accounts_pending_approval = q_singleval('SELECT count(*) FROM fud26_users WHERE users_opt>=2097152 AND '. q_bitand('users_opt', 2097152) .' > 0 AND id > 0'))) {
		$accounts_pending_approval = '| <a href="adm/admaccapr.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'">Учётные записи, ожидающие утверждения</a> <span class="GenTextRed">('.$accounts_pending_approval.')</span>';
	} else {
		$accounts_pending_approval = '';
	}
	if ($is_a || $usr->group_leader_list) {
		$group_mgr = '| <a href="index.php?t=groupmgr&amp;'._rsid.'">Менеджер групп</a>';
	}

	if ($thr_exch || $accounts_pending_approval || $group_mgr || $reported_msgs || $custom_avatar_queue || $mod_que) {
		$admin_cp = '<br /><span class="GenText fb">Админ:</span> '.$mod_que.' '.$reported_msgs.' '.$thr_exch.' '.$custom_avatar_queue.' '.$group_mgr.' '.$accounts_pending_approval.'<br />';
	}
} else {
	$admin_cp = '';
}if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/velopiter/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/velopiter/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
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

	if (!empty($_GET['ufid']) && sq_check(0, $usr->sq)) {
		forum_notify_del(_uid, (int)$_GET['ufid']);
	}
	if (!empty($_GET['utid']) && sq_check(0, $usr->sq)) {
		thread_notify_del(_uid, (int)$_GET['utid']);
	}
	if (!empty($_GET['ubid']) && sq_check(0, $usr->sq)) {
		buddy_delete(_uid, (int)$_GET['ubid']);
	}

	$uc_buddy_ents = '';
	$c = uq('SELECT u.id, u.alias, u.last_visit, '. q_bitand('users_opt', 32768) .' FROM fud26_buddy b INNER JOIN fud26_users u ON b.bud_id=u.id WHERE b.user_id='. _uid .' ORDER BY u.last_visit DESC');
	while ($r = db_rowarr($c)) {
		$uc_pm = ($FUD_OPT_1 & 1024) ? '<a href="index.php?t=ppost&toi='.$r[0].'&amp;'._rsid.'">PM</a>&nbsp;||&nbsp;' : '';
		$obj = new stdClass();
		$obj->login = $r[1];
		$uc_online = (!$r[3] && ($r[2] + $LOGEDIN_TIMEOUT * 60) > __request_timestamp__) ? '<img src="theme/velopiter/images/online'.img_ext.'" alt="онлайн" title="онлайн" />' : '<img src="theme/velopiter/images/offline'.img_ext.'" alt="оффлайн" title="оффлайн" />';
		$uc_buddy_ents .= '<tr class="RowStyleA">
	<td class="vm">'.$uc_online.'</td>
	<td class="nw vm wa"><a href="index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a></td>
	<td class="nw vm RowStyleB SmallText">'.$uc_pm.'<a href="index.php?t=uc&amp;ubid='.$r[0].'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">X</a></td>
</tr>';
	}
	unset($c);

	$uc_new_pms = '';
	$c = uq('SELECT m.ouser_id, u.alias, m.post_stamp, m.subject, m.id FROM fud26_pmsg m INNER JOIN fud26_users u ON u.id=m.ouser_id WHERE m.duser_id='. _uid .' AND fldr=1 AND read_stamp=0 ORDER BY post_stamp DESC LIMIT '. ($usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE));
	while ($r = db_rowarr($c)) {
		$uc_new_pms .= '<tr class="RowStyleB">
	<td><a href="index.php?t=pmsg_view&amp;id='.$r[4].'&amp;'._rsid.'">'.$r[3].'</a></td>
	<td class="nw"><a href="index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a></td>
	<td class="DateText nw">'.strftime("%b %d %Y %H:%M", $r[2]).'</td>
</tr>';
	}
	unset($c);
	if ($uc_new_pms) {
		$uc_new_pms = '<tr>
	<th class="wa">Тема</th>
	<th class="nw">Автор</th>
	<th class="nw">Время</th>
</tr>
'.$uc_new_pms;
	}

	$uc_sub_forum = '';
	$c = uq('SELECT
		f.name, f.id, f.descr, f.thread_count, f.post_count,
		u.alias,
		m.subject, m.id AS mid, m.post_stamp, m.poster_id,
		c.name AS cat_name
		FROM fud26_forum_notify fn
		INNER JOIN fud26_forum f ON f.id=fn.forum_id
		INNER JOIN fud26_cat c ON c.id=f.cat_id
		INNER JOIN fud26_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=f.id
		LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id
		LEFT JOIN fud26_msg m ON f.last_post_id=m.id
		LEFT JOIN fud26_users u ON u.id=m.poster_id
		LEFT JOIN fud26_forum_read fr ON fr.forum_id=f.id AND fr.user_id='. _uid .'
		LEFT JOIN fud26_mod mo ON mo.user_id='. _uid .' AND mo.forum_id=f.id
		WHERE fn.user_id='. _uid .'
		AND '. $usr->last_read .' < m.post_stamp AND (fr.last_view IS NULL OR m.post_stamp > fr.last_view)
		'. ($is_a ? '' : ' AND (mo.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 1) .'> 0)') .'
		ORDER BY m.post_stamp DESC');
	while ($r = db_rowobj($c)) {
		$uc_sub_forum .= '<tr>
	<td class="RowStyleA SmallText wa"><a href="index.php?t='.t_thread_view.'&amp;frm_id='.$r->id.'&amp;'._rsid.'" class="big">'.htmlspecialchars($r->cat_name).' &raquo; '.$r->name.'</a>'.($r->descr ? '<br />'.$r->descr.'' : '' ) .'<br /><a href="index.php?t=post&amp;frm_id='.$r->id.'&amp;'._rsid.'">Новая тема</a> || <a href="index.php?t=uc&amp;ufid='.$r->id.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">Отписаться от форума</a></td>
	<td class="RowStyleB ac">'.$r->post_count.'</td>
	<td class="RowStyleB ac">'.$r->thread_count.'</td>
	<td class="RowStyleA SmallText ar nw">'.($r->mid ? '<a href="index.php?t='.d_thread_view.'&amp;goto='.$r->mid.'&amp;'._rsid.'#msg_'.$r->mid.'" title="'.$r->subject.'">'.substr($r->subject, 0, min(25, strlen($r->subject))).'</a><br />
<span class="DateText">'.strftime("%a, %d %B %Y", $r->post_stamp).'</span><br />От: '.($r->alias ? '<a href="index.php?t=usrinfo&amp;id='.$r->poster_id.'&amp;'._rsid.'">'.$r->alias.'</a>' : $GLOBALS['ANON_NICK'].'' ) : '' ) .'</td>
</tr>';
	}
	if ($uc_sub_forum) {
		$uc_sub_forum = '<tr>
        <th class="wa">Категория &raquo; Форум</th>
	<th style="white-space: nowrap">Сообщений</th>
        <th style="white-space: nowrap">Тем</th>
        <th style="white-space: nowrap">Последнее</th>
</tr>
'.$uc_sub_forum;
	}
	unset($c);

	$uc_sub_topic = '';
	$ppg = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;
	$c = uq('SELECT
			m2.subject, m.post_stamp, m.poster_id,
			u.alias,
			t.replies, t.views, t.thread_opt, t.id, t.last_post_id
		FROM fud26_thread_notify tn
		INNER JOIN fud26_thread t ON tn.thread_id=t.id
		INNER JOIN fud26_msg m ON t.last_post_id=m.id
		INNER JOIN fud26_msg m2 ON t.root_msg_id=m2.id
		INNER JOIN fud26_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=t.forum_id
		LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=t.forum_id
		LEFT JOIN fud26_users u ON u.id=m.poster_id
		LEFT JOIN fud26_read r ON t.id=r.thread_id AND r.user_id='. _uid .'
		LEFT JOIN fud26_mod mo ON mo.user_id='. _uid .' AND mo.forum_id=t.forum_id
		WHERE tn.user_id='. _uid .' AND m.post_stamp > '. $usr->last_read .' AND m.post_stamp > r.last_view '.
		($is_a ? '' : ' AND (mo.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 1) .'> 0)').
		'ORDER BY m.post_stamp DESC LIMIT '. ($usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE));
	while ($r = db_rowobj($c)) {
		$msg_count = $r->replies + 1;
		if ($msg_count > $ppg && $usr->users_opt & 256) {
			if ($THREAD_MSG_PAGER < ($pgcount = ceil($msg_count / $ppg))) {
				$i = $pgcount - $THREAD_MSG_PAGER;
				$mini_pager_data = '&nbsp;...';
			} else {
				$mini_pager_data = '';
				$i = 0;
			}
			while ($i < $pgcount) {
				$mini_pager_data .= '&nbsp;<a href="index.php?t='.d_thread_view.'&amp;th='.$r->id.'&amp;start='.($i * $ppg).'&amp;'._rsid.'">'.++$i.'</a>';
			}
			$mini_thread_pager = $mini_pager_data ? '<span class="SmallText">(<img src="theme/velopiter/images/pager.gif" alt="" />'.$mini_pager_data.')</span>' : '';
		} else {
			$mini_thread_pager = '';
		}

		$uc_sub_topic .= '<tr>
	<td class="RowStyleA"><a href="index.php?t='.d_thread_view.'&amp;th='.$r->id.'&amp;unread=1&amp;'._rsid.'"><img src="theme/velopiter/images/newposts.gif" title="Перейти к первому непрочитанному сообщению в этой теме" alt="" /></a>&nbsp;<a class="big" href="index.php?t='.d_thread_view.'&amp;th='.$r->id.'&amp;'._rsid.'">'.$r->subject.'</a> '.$mini_thread_pager.'<br /><div class="ar"><a href="index.php?t=post&amp;th_id='.$r->id.'&amp;'._rsid.'">Ответ</a> || <a href="index.php?t=uc&amp;utid='.$r->id.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">Отписаться от форума</a></div></td>
	<td class="RowStyleB ac">'.$r->replies.'</td>
	<td class="RowStyleB ac">'.$r->views.'</td>
	<td class="RowStyleC ar nw"><span class="DateText">'.strftime("%a, %d %B %Y", $r->post_stamp).'</span><br />От: '.($r->alias ? '<a href="index.php?t=usrinfo&amp;id='.$r->poster_id.'&amp;'._rsid.'">'.$r->alias.'</a>' : $GLOBALS['ANON_NICK'].'' ) .' <a href="index.php?t='.d_thread_view.'&amp;goto='.$r->last_post_id.'&amp;'._rsid.'#msg_'.$r->last_post_id.'"><img src="theme/velopiter/images/goto.gif" title="Перейти к последнему сообщению в этой теме" alt="" /></a></td>
</tr>';
	}
	if ($uc_sub_topic) {
		$uc_sub_topic = '<tr>
        <th class="wa">Тема</th>
	<th style="white-space: nowrap">Ответы</th>
        <th style="white-space: nowrap">Просмотров</th>
        <th style="white-space: nowrap">Последнее</th>
</tr>
'.$uc_sub_topic;
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
<link rel="stylesheet" href="theme/velopiter/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
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
<td width=210>
<a href="http://velodrive.ru/" target="_blank"><img src="http://velopiter.spb.ru/vdr.gif" border="0" width="200" height="100" alt="Велосипеды Велодрайв"></a>
</td>

<td><a href="http://pk-99.ru/" target="_blank">
  <img border="0" src="http://velopiter.spb.ru/pk.gif" alt="ПИК-99"
width="100" height="100"></a></td>

<td width="100" height=100 align=right>
<a href="http://www.alienbike.ru"></a><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100" height="100" id="090406_5" align="right">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="http://velopiter.spb.ru/090406_5.swf" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#000000" /><embed src="http://velopiter.spb.ru/090406_5.swf" loop="false" menu="false" quality="high" bgcolor="#000000" width="100" height="100" name="../090406_5" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object></td>
</tr></table></div>
<!--- banners end-->
</td></tr></table>
</span>
</div>
<div class="UserControlPanel"><?php echo $private_msg; ?> 
  <?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<a class="UserControlPanel nw" href="index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Участники"><img src="theme/velopiter/images/top_members'.img_ext.'" alt="" /> Участники</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_3 & 134217728 ? '<a class="UserControlPanel nw" href="index.php?t=cal&amp;'._rsid.'" title="Календарь"><img src="theme/velopiter/images/calendar'.img_ext.'" alt="" /> Календарь</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_1 & 16777216 ? '<a class="UserControlPanel nw" href="index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Поиск"><img src="theme/velopiter/images/top_search'.img_ext.'" alt="" /> Поиск</a>&nbsp;&nbsp;' : ''); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" accesskey="h" href="index.php?t=help_index&amp;<?php echo _rsid; ?>" title="F.A.Q."><img src="theme/velopiter/images/top_help<?php echo img_ext; ?>" alt="" /> F.A.Q.</a>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=uc&amp;'._rsid.'" title="Доступ к панели управления пользователя"><img src="theme/velopiter/images/top_profile'.img_ext.'" alt="" /> Настройки</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=register&amp;'._rsid.'" title="Регистрация"><img src="theme/velopiter/images/top_register'.img_ext.'" alt="" /> Регистрация</a>'); ?>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Выход"><img src="theme/velopiter/images/top_logout'.img_ext.'" alt="" /> Выход [ '.$usr->alias.' ]</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'" title="Вход"><img src="theme/velopiter/images/top_login'.img_ext.'" alt="" /> Вход</a>'); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=index&amp;<?php echo _rsid; ?>" title="Начало"><img src="theme/velopiter/images/top_home<?php echo img_ext; ?>" alt="" /> Начало</a>
  <?php echo ($is_a || ($usr->users_opt & 268435456) ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Административный центр"><img src="theme/velopiter/images/top_admin'.img_ext.'" alt="" /> Административный центр</a>' : ''); ?>
</div>
<?php echo $tabs; ?>

<table cellspacing="3" cellpadding="3" border="0" class="wa">
<tr>
	<td class="vt"><table border="0" cellspacing="1" cellpadding="2" class="ucPW">
<tr><th colspan="3">Список контактов</th></tr>
<?php echo $uc_buddy_ents; ?>
</table></td>

	<td class="wa vt">
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="3">Новые личные сообщения</th></tr>
<?php echo $uc_new_pms; ?>
</table>
<br /><br />
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="4">Форумы, на которые вы подписаны, содержащие с новые сообщения</th></tr>
<?php echo $uc_sub_forum; ?>
</table>
<br /><br />
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="4">Темы, на которые вы подписаны, содержащие с новые сообщения</th></tr>
<?php echo $uc_sub_topic; ?>
</table></td>
</tr>
</table>

<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
</td>
<!-- <td class="ForumBackground" valign="top"></td> -->
</tr></table>

<div class="ForumBackground ac foot">
<b>.::</b> <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>">Обратная связь</a> <b>::</b> <a href="index.php?t=index&amp;<?php echo _rsid; ?>">Начало</a> <b>::.</b>
<p>
<span class="SmallText">При поддержке: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br /> Copyright © 2001-2010 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></span>
</p>
</div>
<div align=right>
<span class="SmallText">

<!-- SpyLOG v2 f:0211 -->
<script language="javascript">
u="u166.09.spylog.com";d=document;nv=navigator;na=nv.appName;p=0;j="N";
d.cookie="b=b";c=0;bv=Math.round(parseFloat(nv.appVersion)*100);
if (d.cookie) c=1;n=(na.substring(0,2)=="Mi")?0:1;rn=Math.random();
z="p="+p+"&rn="+rn+"&c="+c;if (self!=top) {fr=1;} else {fr=0;} sl="1.0";
</script>
<script language="javascript1.1">
pl="";sl="1.1";j = (navigator.javaEnabled()?"Y":"N");</script>
<script language=javascript1.2>sl="1.2";s=screen;px=(n==0)?s.colorDepth:s.pixelDepth;
z+="&wh="+s.width+'x'+s.height+"&px="+px;</script>
<script language=javascript1.3>sl="1.3"</script>
<script language="javascript">y="";y+="<a href='http://"+u+"/cnt?f=3&p="+p+"&rn="+rn+"' target=_blank>";
y+="<img src='http://"+u+"/cnt?"+z+"&j="+j+"&sl="+sl+ "&r="+escape(d.referrer)+"&fr="+fr+"&pg="+escape(window.location.href); y+="' border=0 width=88 height=31 alt='SpyLOG'>"; y+="</a>";
d.write(y);if(!n) { d.write("<"+"!--"); }
//-->
</script>
<noscript><a href="http://u166.09.spylog.com/cnt?f=3&p=0" target=_blank><img src="http://u166.09.spylog.com/cnt?p=0" alt='SpyLOG' border='0' width=88 height=31></a></noscript>
<script language="javascript1.2"><!-- if(!n){ d.write("--"+">"); }
//--></script>
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
