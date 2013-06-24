<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: mnav.php.t 5072 2010-11-11 17:12:40Z naudefj $
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
}function read_msg_body($off, $len, $id)
{
	if ($off == -1) {	// Fetch from DB and return.
		return q_singleval('SELECT data FROM fud26_msg_store WHERE id='. $id);
	}

	if (!$len) {	// Empty message.
		return;
	}

	// Open file if it's not already open.
	if (!isset($GLOBALS['__MSG_FP__'][$id])) {
		$GLOBALS['__MSG_FP__'][$id] = fopen($GLOBALS['MSG_STORE_DIR'] .'msg_'. $id, 'rb');
	}

	// Read from file.
	fseek($GLOBALS['__MSG_FP__'][$id], $off);
	return fread($GLOBALS['__MSG_FP__'][$id], $len);
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

	if (!isset($_GET['start']) || !($start = (int)$_GET['start'])) {
		$start = 0;
	}
	$forum_limiter = isset($_GET['forum_limiter']) ? (string)$_GET['forum_limiter'] : '';
	$rng = isset($_GET['rng']) ? (float) $_GET['rng'] : 1;
	$rng2 = isset($_GET['rng2']) ? (float) $_GET['rng2'] : 0;
	$unit = isset($_GET['u']) ? (int) $_GET['u'] : 86400;
	$ppg = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;
	$subl = !empty($_GET['sub']);

	require $FORUM_SETTINGS_PATH .'cat_cache.inc';

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
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}if (!isset($th)) {
	$th = 0;
}
if (!isset($frm->id)) {
	$frm = new stdClass();	// Initialize to prevent 'strict standards' notice.
	$frm->id = 0;
}/* Draw search engine selection boxes. */
if ($is_a) {
	$c = uq('SELECT f.id, f.name, c.id
			FROM fud26_fc_view v
			INNER JOIN fud26_forum f ON f.id=v.f
			INNER JOIN fud26_cat c ON f.cat_id=c.id
			WHERE f.url_redirect IS NULL ORDER BY v.id');
} else {
	$c = uq('SELECT f.id, f.name, c.id
			FROM fud26_fc_view v
			INNER JOIN fud26_forum f ON f.id=v.f
			INNER JOIN fud26_cat c ON f.cat_id=c.id
			INNER JOIN fud26_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=f.id
			LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .'
			LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id
			WHERE f.url_redirect IS NULL AND mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', (1|262144)) .' >= '. (1|262144) .'
			ORDER BY v.id');
}
$oldc = $forum_limit_data = ''; $g = $f = array();
if ($forum_limiter) {
	if ($forum_limiter{0} != 'c') {
		$f[$forum_limiter] = 1;
	} else {
		$g[(int)ltrim($forum_limiter, 'c')] = 1;
	}
}

while ($r = db_rowarr($c)) {
	if ($oldc != $r[2]) {
		while (list($k, $i) = each($cat_cache)) {
			$forum_limit_data .= '<option value="c'.$k.'"'.(isset($g[$k]) ? ' selected="selected"' : '').'>- '.($tabw = ($i[0] ? str_repeat('&nbsp;&nbsp;&nbsp;', $i[0]) : '')).$i[1].'</option>';
			if ($k == $r[2]) {
				break;
			}
		}
		$oldc = $r[2];
	}
	$forum_limit_data .= '<option value="'.$r[0].'"'.(isset($f[$r[0]]) ? ' selected="selected"' : '').'>'.$tabw.'&nbsp;&nbsp;&nbsp;'.$r[1].'</option>';
}
unset($c);

/* User has no permissions to any forum, so as far as they are concerned the search is disabled. */
if (!$forum_limit_data) {
	std_error('disabled');
}

function trim_body($body)
{
	/* Remove stuff in old bad quote tags - remove in future release. */
	while (($p = strpos($body, '<table border="0" align="center" width="90%" cellpadding="3" cellspacing="1"><tr><td class="SmallText"><b>')) !== false) {
		if (($pos = strpos($body, '<br></td></tr></table>', $p)) === false) {
			$pos = strpos($body, '<br /></td></tr></table>', $p);
			if ($pos === false) {
				break;
			}
			$e = $pos + strlen('<br /></td></tr></table>');
		} else {
			$e = $pos + strlen('<br></td></tr></table>');
		}
		$body = substr($body, 0, $p) . substr($body, $e);
	}

	/* Remove stuff in quotes */
	while (preg_match('!<cite>(.*?)</cite><blockquote>(.*?)</blockquote>!is', $body)) {
		$body = preg_replace('!<cite>(.*?)</cite><blockquote>(.*?)</blockquote>!is', '', $body);
	}

	$body = strip_tags($body);
	if (strlen($body) > $GLOBALS['MNAV_MAX_LEN']) {
		if (function_exists('mb_substr')) {
			$body = mb_substr($body, 0, $GLOBALS['MNAV_MAX_LEN']) .'...';
		} else {
			$body = substr($body, 0, $GLOBALS['MNAV_MAX_LEN']) .'...';
		}
	}
	return $body;
}

	$TITLE_EXTRA = ': Навигатор по сообщениям';

	ses_update_status($usr->sid, 'Просмотр сообщений, используя <a href="index.php?t=mnav">Навигатор по сообщениям</a>');

	if ($forum_limiter) {
		if ($forum_limiter[0] != 'c') {
			$qry_lmt = ' AND f.id='. (int)$forum_limiter .' ';
		} else {
			$qry_lmt = ' AND c.id='. (int)substr($forum_limiter, 1) .' ';
		}
	} else {
		$qry_lmt = '';
	}

	$mnav_time_unit = tmpl_draw_select_opt("60\n3600\n86400\n604800\n2635200", "Минуты\nЧасы\nДни\nНедели\nМесяцы", $unit);

	$mnav_pager = '';
	if (!$rng) {
		$rng = ''; $unit = 86400;
		$mnav_data = '<br />
<div class="ctb">
<table cellspacing="1" cellpadding="2" class="mnavWarnTbl">
<tr>
	<td class="GenTextRed">Вы должны задать правильный период времени. Число может быть не целым (0,12), но оно должно быть больше нуля.</td>
</tr>
</table>
</div>';
	} else if ($unit <= 0) {
		$rng = ''; $unit = 86400;
		$mnav_data = '<br />
<div class="ctb">
<table cellspacing="1" cellpadding="2" class="mnavWarnTbl">
<tr>
	<td class="GenTextRed">Вы должны указать правильную единицу времени.</td>
</tr>
</table>
</div>';
	} else if (($mage = round($rng * $unit)) > ($MNAV_MAX_DATE * 86400) && $MNAV_MAX_DATE > 0) {
		$mnav_data = '<br />
<div class="ctb">
<table cellspacing="1" cellpadding="2" class="mnavWarnTbl">
<tr>
	<td class="GenTextRed">Период времени, который вы указали больше, чем разрешено администратором. Пожалуйста, уменьшите период времени для поиска.</td>
</tr>
</table>
</div>';
	} else if (isset($_GET['u'])) {
		$tm = __request_timestamp__ - $mage;

		if ($rng2 > 0) {
			$date_limit = ' AND m.post_stamp < '. (__request_timestamp__ - ($rng2 * $unit));
		} else {
			$date_limit = '';
		}

		if (_uid && $subl) {
			if ($sf = db_all('SELECT forum_id FROM fud26_forum_notify WHERE user_id='. _uid)) {
				$qry_lmt .= ' AND f.id IN('. implode(',', $sf) .') ';
			} else {
				$qry_lmt .= ' AND f.id=-1 ';
			}
		}

		$c = q(q_limit('SELECT /*!40000 SQL_CALC_FOUND_ROWS */ u.alias, f.name AS forum_name, f.id AS forum_id,
				m.poster_id, m.id, m.thread_id, m.subject, m.foff, m.length, m.post_stamp, m.file_id, m.icon
				FROM fud26_msg m
				INNER JOIN fud26_thread t ON m.thread_id=t.id
				INNER JOIN fud26_forum f ON t.forum_id=f.id
				INNER JOIN fud26_cat c ON f.cat_id=c.id
				INNER JOIN fud26_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=f.id
				LEFT JOIN fud26_users u ON m.poster_id=u.id
				LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .'
				LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id
			WHERE
				m.post_stamp > '. $tm .' '. $date_limit .' AND m.apr=1 '. $qry_lmt .'
				'.($is_a ? '' : ' AND (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0)').'
				ORDER BY m.thread_id, t.forum_id, m.post_stamp DESC',
			$ppg, $start));

		$oldf = $oldt = 0;
		$mnav_data = '<div class="ctb">
<table cellspacing="0" cellpadding="0" class="ContentTable">';
		while ($r = db_rowobj($c)) {
			if ($oldf != $r->forum_id) {
				$mnav_data .= '<tr><th colspan="3"> Форум: <a class="thLnk" href="index.php?t='.t_thread_view.'&amp;frm_id='.$r->forum_id.'&amp;'._rsid.'"><span class="lg">'.htmlspecialchars($r->forum_name).'</span></a></th></tr>';
				$oldf = $r->forum_id;
			}
			if ($oldt != $r->thread_id) {
				$mnav_data .= '<tr><th class="RowStyleC">&nbsp;&nbsp;&nbsp;</th><th colspan="2"> Тема: <a class="thLnk" href="index.php?t='.d_thread_view.'&amp;goto='.$r->id.'&amp;'._rsid.'#msg_'.$r->id.'">'.$r->subject.'</a></th></tr>';
				$oldt = $r->thread_id;
			}
			$mnav_data .= '<tr><td class="RowStyleC">&nbsp;&nbsp;&nbsp;</td><td class="RowStyleC">&nbsp;&nbsp;&nbsp;</td><td>
<table cellspacing="0" cellpadding="2" border="0" class="mnavMsg">
<tr class="mnavH">
	<td class="nw al"><a href="index.php?t='.d_thread_view.'&amp;goto='.$r->id.'&amp;'._rsid.'#msg_'.$r->id.'">'.$r->subject.'</a></td>
	<td class="TopBy wa ac">Отправлено: '.(!empty($r->poster_id) ? '<a href="index.php?t=usrinfo&amp;id='.$r->poster_id.'&amp;'._rsid.'">'.$r->alias.'</a>' : $GLOBALS['ANON_NICK'].'' ) .'</td>
	<td class="DateText nw ar">'.strftime("%a, %d %B %Y %H:%M", $r->post_stamp).'</td>
</tr>
<tr class="mnavM SmallText">
	<td colspan="3">'.trim_body(read_msg_body($r->foff, $r->length, $r->file_id)).' <a href="index.php?t='.d_thread_view.'&amp;goto='.$r->id.'&amp;'._rsid.'#msg_'.$r->id.'">Подробнее &raquo;&raquo;</a></td>
</tr>
</table>
</td></tr>';
		}
		unset($c);

		if (($total = (int) q_singleval('SELECT /*!40000 FOUND_ROWS(), */ -1')) < 0) {
			$total = q_singleval('SELECT count(*) FROM fud26_msg m
					INNER JOIN fud26_thread t ON m.thread_id=t.id
					INNER JOIN fud26_forum f ON t.forum_id=f.id
					INNER JOIN fud26_cat c ON f.cat_id=c.id
					INNER JOIN fud26_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=f.id
					LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .'
					LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id
				WHERE
					m.post_stamp > '. $tm .' '. $date_limit .' AND m.apr=1 '. $qry_lmt .'
					'. ($is_a ? '' : ' AND (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0)'));
		}

		if (!$total) {
			$mnav_data = '<div class="GenText mnavNoRes ac"><p>Нет сообщений, удовлетворяющих запросу.</p></div>';
		} else {
			$mnav_data .= '</table>
</div>';

			/* Handle pager if needed. */
			if ($total > $ppg) {
				if ($FUD_OPT_2 & 32768) {
					$mnav_pager = tmpl_create_pager($start, $ppg, $total, 'index.php/ma/'. $rng .'/'. $rng2 .'/'. $unit .'/', '/'. $subl .'/'. _rsid);
				} else {
					$mnav_pager = tmpl_create_pager($start, $ppg, $total, 'index.php?t=mnav&amp;rng='. $rng .'&amp;u='. $unit .'&amp;'. _rsid .'&amp;forum_limiter='. $forum_limiter .'&amp;rng2='. $rng2 .'&amp;sub='. $subl);
				}
			}
		}
	} else {
		$mnav_data = '';
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
<span class="GenText fb">Показать:</span> <a href="index.php?t=selmsg&amp;date=today&amp;<?php echo _rsid; ?>&amp;frm_id=<?php echo (isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'&amp;th='.$th.'" title="Показать все отправленные сегодня сообщения">Сегодняшние сообщения</a>&nbsp;'.(_uid ? '<b>::</b> <a href="index.php?t=selmsg&amp;unread=1&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Показать все непрочитанные сообщения">Непрочитанные сообщения</a>&nbsp;' : '' ) .(!$th ? '<b>::</b> <a href="index.php?t=selmsg&amp;reply_count=0&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Показать все сообщения, на которые нет ответа">Сообщения без ответа</a>' : ''); ?> <b>::</b> <a href="index.php?t=polllist&amp;<?php echo _rsid; ?>">Показать голосования</a> <b>::</b> <a href="index.php?t=mnav&amp;<?php echo _rsid; ?>">Навигатор по сообщениям</a><br /><img src="blank.gif" alt="" height="2" /><?php echo $admin_cp; ?>

<form id="mnav" method="get" action="index.php"><?php echo _hs; ?><input type="hidden" name="t" value="mnav" />
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="4" class="wa">Навигатор по сообщениям</th></tr>
<tr class="<?php echo alt_var('color_alt','RowStyleA','RowStyleB'); ?>">
	<td class="GenText nw" width="30%">Период времени:</td>
	<td class="GenText SmallText">новее, чем<br /><input tabindex="1" type="text" name="rng" value="<?php echo $rng; ?>" maxlength="10" size="11" /></td>
	<td class="GenText SmallText">старее, чем<br /><input tabindex="2" type="text" name="rng2" value="<?php echo $rng2; ?>" maxlength="10" size="11" /></td>
	<td class="al vb" width="60%"><select name="u" tabindex="3"><?php echo $mnav_time_unit; ?></select></td></tr>
<tr class="<?php echo alt_var('color_alt','RowStyleA','RowStyleB'); ?>">
	<td class="GenText nw">Искать в:</td>
	<td colspan="3" class="vt">
		<select name="forum_limiter" tabindex="4"><option value="">Искать во всех форумах</option>
		<?php echo $forum_limit_data; ?>
		</select>
	</td>
</tr>
<?php echo (_uid ? '
<tr class="'.alt_var('color_alt','RowStyleA','RowStyleB').'">
	<td class="GenText nw">Поиск только в подписанных форумах</td>
	<td colspan="3" class="vt"><input type="checkbox" name="sub" value="1" '.($subl ? 'checked="checked" ' : '' )  .' /></td>
' : ''); ?>
<tr class="RowStyleC"><td class="GenText ar" colspan="4"><input type="submit" tabindex="5" class="button" name="btn_submit" value="Начать поиск" /></td></tr>
</table></form>
<br />
<?php echo $mnav_data; ?>
<div class="al"><?php echo $mnav_pager; ?></div>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<script type="text/javascript">
/* <![CDATA[ */ 
document.forms['mnav'].rng.focus();
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
