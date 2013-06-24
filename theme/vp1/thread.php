<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: thread.php.t 5067 2010-11-06 07:37:22Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
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
}/* Check moved topic permissions. */
function th_moved_perm_chk($frm_id)
{
	make_perms_query($fields, $join, $frm_id);
	$res = db_sab('SELECT m.forum_id, '. $fields.
		' FROM fud26_forum f '. $join.
		' LEFT JOIN fud26_mod m ON m.user_id='._uid.' AND m.forum_id='. $frm_id .
		' WHERE f.id='. $frm_id .' LIMIT 1');
	if (!$res || (!($res->group_cache_opt & 2) && !$res->forum_id)) {
		return;
	}
	return 1;
}

/* Make sure that we have what appears to be a valid forum id. */
if (!isset($_GET['frm_id']) || (!($frm_id = (int)$_GET['frm_id']))) {
	invl_inp_err();
}

if (!isset($_GET['start']) || ($start = (int)$_GET['start']) < 1) {
	$start = 0;
}

/* This query creates frm object that contains info about the current
 * forum, category & user's subscription status & permissions to the
 * forum.
 */

make_perms_query($fields, $join, $frm_id);

$frm = db_sab('SELECT	f.id, f.name, f.thread_count, f.cat_id,'.
			(_uid ? ' fn.forum_id AS subscribed, m.forum_id AS md, ' : ' 0 AS subscribed, 0 AS md, ').
			'a.ann_id AS is_ann, ms.post_stamp, '. $fields .'
		FROM fud26_forum f
		INNER JOIN fud26_cat c ON c.id=f.cat_id '.
		(_uid ? ' LEFT JOIN fud26_forum_notify fn ON fn.user_id='._uid.' AND fn.forum_id='. $frm_id .' LEFT JOIN fud26_mod m ON m.user_id='. _uid .' AND m.forum_id='. $frm_id : ' ')
		.$join.'
		LEFT JOIN fud26_ann_forums a ON a.forum_id='. $frm_id .'
		LEFT JOIN fud26_msg ms ON ms.id=f.last_post_id
		WHERE f.id='. $frm_id .' LIMIT 1');

if (!$frm) {
	invl_inp_err();
}
$frm->forum_id = $frm->id;
$MOD = ($is_a || $frm->md);
$lwi = q_singleval('SELECT seq FROM fud26_tv_'. $frm_id .' ORDER BY seq DESC LIMIT 1');

/* Check that the user has permissions to access this forum. */
if (!($frm->group_cache_opt & 2) && !$MOD) {
	if (!isset($_GET['logoff'])) {
		std_error('login');
	}
	if ($FUD_OPT_2 & 32768) {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/i/'. _rsidl);
	} else {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. _rsidl);
	}
	exit;
}

if ($_GET['t'] == 'threadt') {
	$ann_cols = '5';
	$cur_frm_page = $start + 1;
} else {
	$ann_cols = '6';
	$cur_frm_page = floor($start / $THREADS_PER_PAGE) + 1;
}

/* Do various things for registered users. */
if (_uid) {
	if (isset($_GET['sub']) && sq_check(0, $usr->sq)) {
		forum_notify_add(_uid, $frm->id);
		$frm->subscribed = 1;
	} else if (isset($_GET['unsub']) && sq_check(0, $usr->sq)) {
		forum_notify_del(_uid, $frm->id);
		$frm->subscribed = 0;
	}
} else if (__fud_cache((int)$frm->post_stamp)) {
	return;
}

$ppg = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;

/* Handling of announcements. */
$announcements = '';
if ($frm->is_ann) {
	$today = gmdate('Ymd', __request_timestamp__);
	$res = uq('SELECT a.subject, a.text FROM fud26_announce a INNER JOIN fud26_ann_forums af ON a.id=af.ann_id AND af.forum_id='. $frm->id .' WHERE a.date_started<='. $today .' AND a.date_ended>='. $today);
	while ($r = db_rowarr($res)) {
		$announcements .= '<tr><td class="AnnText" colspan="'.$ann_cols.'"><span class="AnnSubjText">'.$r[0].'</span><br />'.$r[1].'</td></tr>';
	}
	unset($res);
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
}

	ses_update_status($usr->sid, 'Просмотр форума <a href="index.php?t=thread&amp;frm_id='.$frm->id.'">'.$frm->name.'</a>', $frm_id);
	$RSS = ($FUD_OPT_2 & 1048576 ? '
<link rel="alternate" type="application/rss+xml" title="Сформировать XML" href="'.$GLOBALS['WWW_ROOT'].'feed.php?mode=m&amp;l=1&amp;basic=1&amp;frm='.$frm->id.'&amp;n=10" />
' : '' )  ;

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
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}function tmpl_create_forum_select($frm_id, $mod)
{
	if (!isset($_GET['t']) || ($_GET['t'] != 'thread' && $_GET['t'] != 'threadt')) {
		$dest = t_thread_view;
	} else {
		$dest = $_GET['t'];
	}

	if ($mod) { /* Admin optimization. */
		$c = uq('SELECT f.id, f.name, c.id FROM fud26_fc_view v INNER JOIN fud26_forum f ON f.id=v.f INNER JOIN fud26_cat c ON f.cat_id=c.id WHERE f.url_redirect IS NULL ORDER BY v.id');
	} else {
		$c = uq('SELECT f.id, f.name, c.id
			FROM fud26_fc_view v
			INNER JOIN fud26_forum f ON f.id=v.f
			INNER JOIN fud26_cat c ON f.cat_id=c.id
			INNER JOIN fud26_group_cache g1 ON g1.user_id='. (_uid ? '2147483647' : '0') .' AND g1.resource_id=f.id '.
			(_uid ? ' LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .' LEFT JOIN fud26_group_cache g2 ON g2.user_id='. _uid .' AND g2.resource_id=f.id WHERE mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 1) .' > 0 '  : ' WHERE '. q_bitand('g1.group_cache_opt', 1) .' > 0 AND f.url_redirect IS NULL ').
			'ORDER BY v.id');
	}
	$f = array($frm_id => 1);

	$frmcount = 0;
	$oldc = $selection_options = '';
	while ($r = db_rowarr($c)) {
		if ($oldc != $r[2]) {
			while (list($k, $i) = each($GLOBALS['cat_cache'])) {
				if ($r[2] != $k && $i[0] >= $GLOBALS['cat_cache'][$r[2]][0]) {
					continue;
				}
	
				$selection_options .= '<option disabled="disabled">- '.($tabw = ($i[0] ? str_repeat('&nbsp;&nbsp;&nbsp;', $i[0]) : '')).$i[1].'</option>';
				if ($k == $r[2]) {
					break;
				}
			}
			$oldc = $r[2];
		}
		$selection_options .= '<option value="'.$r[0].'"'.(isset($f[$r[0]]) ? ' selected="selected"' : '').'>'.$tabw.'&nbsp;&nbsp;'.$r[1].'</option>';
		$frmcount++;
	}
	unset($c);
	
	return ($frmcount > 1 ? '
<span class="SmallText fb">Переход к форуму:</span>
<form action="index.php" id="frmquicksel" method="get">
<input type="hidden" name="t" value="'.$dest.'" />'._hs.'
<select class="SmallText" name="frm_id">
'.$selection_options.'
</select>&nbsp;&nbsp;<input type="submit" class="button small" name="frm_goto" value="Переход" /></form>
' : '' ) ;
}if (!isset($th)) {
	$th = 0;
}
if (!isset($frm->id)) {
	$frm = new stdClass();	// Initialize to prevent 'strict standards' notice.
	$frm->id = 0;
}require $GLOBALS['FORUM_SETTINGS_PATH'] .'cat_cache.inc';

function draw_forum_path($cid, $fn='', $fid=0, $tn='')
{
	global $cat_par, $cat_cache;

	$data = '';
	do {
		$data = '&nbsp;&raquo; <a href="index.php?t=i&amp;cat='.$cid.'&amp;'._rsid.'">'.$cat_cache[$cid][1].'</a>'. $data;
	} while (($cid = $cat_par[$cid]) > 0);

	if ($fid) {
		$data .= '&nbsp;&raquo; <a href="index.php?t='.t_thread_view.'&amp;frm_id='.$fid.'&amp;'._rsid.'">'.$fn.'</a>';
	} else if ($fn) {
		$data .= '&nbsp;&raquo; <b>'.$fn.'</b>';
	}

	return '<a href="index.php?t=i&amp;'._rsid.'">Начало</a>'.$data.($tn ? '&nbsp;&raquo; <b>'.$tn.'</b>' : '');
}

	$TITLE_EXTRA = ': '.$frm->name;

	$result = q('SELECT
		m.attach_cnt, m.poll_id, m.subject, m.icon, m.post_stamp,
		u.alias, u.id,
		u2.id, u2.alias,
		m2.id, m2.post_stamp,
		f.id, f.name,
		t.id, t.moved_to, t.root_msg_id, t.replies, t.rating, t.thread_opt, t.views, 
		r.last_view, t.n_rating, t.tdescr
		FROM fud26_tv_'. $frm_id .' tv
			INNER JOIN fud26_thread	t	ON tv.thread_id=t.id
			INNER JOIN fud26_msg	m	ON t.root_msg_id=m.id
			INNER JOIN fud26_msg	m2	ON m2.id=t.last_post_id
			LEFT JOIN fud26_users	u	ON u.id=m.poster_id
			LEFT JOIN fud26_users	u2	ON u2.id=m2.poster_id
			LEFT JOIN fud26_forum	f	ON f.id=t.moved_to
			LEFT JOIN fud26_read 	r	ON t.id=r.thread_id AND r.user_id='. _uid .'
			WHERE tv.seq BETWEEN '. ($lwi - ($cur_frm_page * $THREADS_PER_PAGE) + 1) .' AND '. ($lwi - (($cur_frm_page - 1) * $THREADS_PER_PAGE)) .'
			ORDER BY tv.seq DESC');
	/* Field Defenitions
	 * 0 msg.attach_cnt
	 * 1 msg.poll_id
	 * 2 msg.subject
	 * 3 msg.icon
	 * 4 msg.post_stamp
	 * 5 users.alias
	 * 6 users.id
	 * 7 fud_users_2.id
	 * 8 fud_users_2.alias
	 * 9 fud_msg_2.id
	 * 10 fud_msg_2.post_stamp
	 * 11 forum.id
	 * 12 forum.name
	 * 13 thread.id
	 * 14 thread.moved_to
	 * 15 thread.root_msg_id
	 * 16 thread.replies
	 * 17 thread.thread_opt
	 * 18 thread.rating
	 * 19 thread.views
	 * 20 read.last_view
	 * 21 thread.n_rating
	 * 22 thread.tdescr
	 */

	if (!($r = db_rowarr($result))) {
		$thread_list_table_data = '<tr><td class="RowStyleA ac" colspan="'.$ann_cols.'"><span class="GenText">В этом форуме нет сообщений.</span></td></tr>';
		$threaded_view = $admin_heading_row = ''; $mo = 0;
	} else {
		$admin_heading_row = ($MOD || ($mo = $frm->group_cache_opt & 8224));
		$admin_control_row = $thread_list_table_data = '';

		do {
			$r[18] = (int) $r[18];

			if ($r[14]) {
				/* Additional security check for moved forums. */
				if (!$is_a && $r[11] && !th_moved_perm_chk($r[11])) {
					continue;
				}
				$thread_list_table_data .= '<tr>
<td class="RowStyleB wo"><img src="theme/vp1/images/moved'.img_ext.'" title="Эта тема была перенесена в другой форум" alt="" /></td>
<td class="RowStyleB ac GenText" colspan="5"><a href="index.php?t='.d_thread_view.'&amp;goto='.$r[15].'&amp;'._rsid.'#msg_'.$r[15].'">'.$r[2].'</a> перенесено модератором в <a href="index.php?t='.t_thread_view.'&amp;frm_id='.$r[11].'&amp;'._rsid.'">'.$r[12].'</a></td>
</tr>';
				continue;
			}
			$msg_count = $r[16] + 1;

			if ($msg_count > $ppg && $usr->users_opt & 256) {
				if ($THREAD_MSG_PAGER < ($pgcount = ceil($msg_count/$ppg))) {
					$i = $pgcount - $THREAD_MSG_PAGER;
					$mini_pager_data = '&nbsp;...';
				} else {
					$mini_pager_data = '';
					$i = 0;
				}

				while ($i < $pgcount) {
					$mini_pager_data .= '&nbsp;<a href="index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;start='.($i * $ppg).'&amp;'._rsid.'">'.++$i.'</a>';
				}

				$mini_thread_pager = $mini_pager_data ? '<span class="SmallText">(<img src="theme/vp1/images/pager.gif" alt="" />'.$mini_pager_data.')</span>' : '';
			} else {
				$mini_thread_pager = '';
			}

			$thread_read_status = $first_unread_msg_link = '';
			if (_uid && $usr->last_read < $r[10] && $r[10] > $r[20]) {
				$thread_read_status = ($r[18] & 1) ? '<img src="theme/vp1/images/unreadlocked'.img_ext.'" title="Закрытая тема с непрочитанными сообщениями" alt="" />' : '<img src="theme/vp1/images/unread'.img_ext.'" title="Эта тема содержит не прочитанные вами сообщения" alt="" />';
				/* Do not show 1st unread message link if thread has no replies. */
				if ($r[16]) {
					$first_unread_msg_link = '<a href="index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;unread=1&amp;'._rsid.'"><img src="theme/vp1/images/newposts.gif" title="к непрочитанному в теме" alt="к непрочитанному в теме" /></a>&nbsp;';
				}
			} else if ($r[18] & 1) {
				$thread_read_status = '<img src="theme/vp1/images/readlocked'.img_ext.'" title="Эта тема была закрыта" alt="" />';
			} else if (!_uid) {
				$thread_read_status = '<img src="theme/vp1/images/read'.img_ext.'" title="Отслеживание прочитанных и непрочитанных сообщений доступно только зарегистрированным участникам" alt="" />';
			} else {
				$thread_read_status = '<img src="theme/vp1/images/read'.img_ext.'" title="В этой теме нет непрочитанных сообщений" alt="" />';
			}

			if ($admin_heading_row) {
				if ($MOD || $mo == 8224) {
					$admin_control_row = '<div class="ModOpt">Действия модератора: <a href="javascript://" onclick="window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=mvthread&amp;'._rsid.'&amp;th='.$r[13].'\', \'th_move\', 300, 400);">Перенести</a> | <a href="index.php?t=mmod&amp;'._rsid.'&amp;th='.$r[13].'&amp;del='.$r[15].'">Удалить</a> | <a href="index.php?t=mmod&amp;'._rsid.'&amp;th='.$r[13].'&amp;'.($r[18] & 1 ? 'unlock' : 'lock' )  .'=1&amp;SQ='.$GLOBALS['sq'].'">'.($r[18] & 1 ? 'Открыть тему' : 'Закрыть тему' )  .'</a> <input type="checkbox" name="_sel[]" value="'.$r[13].'" /></div>';
				} else if ($mo & 32) {
					$admin_control_row = '<div class="ModOpt">Действия модератора: <a href="index.php?t=mmod&amp;'._rsid.'&amp;th='.$r[13].'&amp;del='.$r[15].'">Удалить</a></div>';
				} else {
					$admin_control_row = '<div class="ModOpt">Действия модератора: <a href="javascript://" onclick="window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=mvthread&amp;'._rsid.'&amp;th='.$r[13].'\', \'th_move\', 300, 400);">Перенести</a></div>';
				}
			}
			$thread_list_table_data .= '<tr>
<td class="RowStyleB wo">'.$thread_read_status.'</td>
<td class="RowStyleB wo ac">'.($r[3] ? '<img src="images/message_icons/'.$r[3].'" alt="'.$r[3].'" />' : '&nbsp;' ) .'</td>
<td class="RowStyleA">'.(($r[18] > 1) ? ($r[18] & 4 ? '<span class="StClr">стикер:&nbsp;</span>' : '<span class="AnClr">анонс:&nbsp;</span>' )  : '' ) .$first_unread_msg_link.($r[1] ? 'Голосование:&nbsp;' : '' ) .($r[0] ? '<img src="theme/vp1/images/attachment.gif" alt="" />' : '' ) .'<a class="big" href="index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;start=0&amp;'._rsid.'">'.$r[2].'</a>'.($r[22] ? '<br /><span class="small">'.$r[22].'</span>' : '' )  .' '.((($FUD_OPT_2 & 4096) && $r[17]) ? ($MOD || $mo == 8224 ? '<a href="javascript://" onclick="window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=ratingtrack&amp;'._rsid.'&amp;th='.$r[13].'\', \'th_rating_track\', 300, 400);">' : '' ) .'<img src="theme/vp1/images/'.$r[17].'stars.gif" title="'.$r[17].' из '.convertPlural($r[21], array(''.$r[21].' голоса',''.$r[21].' голосов',''.$r[21].' голосов')).'" alt="" />'.($MOD || $mo == 8224 ? '</a>' : '' ) : '' ) .' '.$mini_thread_pager.' <div class="TopBy">От: '.($r[5] ? '<a href="index.php?t=usrinfo&amp;id='.$r[6].'&amp;'._rsid.'">'.$r[5].'</a>' : $GLOBALS['ANON_NICK'].'' ) .' - <span class="DateText">'.strftime("%a, %d %B %Y", $r[4]).'</span></div>'.$admin_control_row.'</td>
<td class="RowStyleB ac">'.$r[16].'</td>
<td class="RowStyleB ac">'.$r[19].'</td>
<td class="RowStyleC nw"><span class="DateText">'.strftime("%a, %d %B %Y %H:%M", $r[10]).'</span><br />От: '.($r[8] ? '<a href="index.php?t=usrinfo&amp;id='.$r[7].'&amp;'._rsid.'">'.$r[8].'</a>' : $GLOBALS['ANON_NICK'].'' ) .' <a href="index.php?t='.d_thread_view.'&amp;th='.$r[13].'&amp;goto='.$r[9].'&amp;'._rsid.'#msg_'.$r[9].'"><img src="theme/vp1/images/goto.gif" title="к последнему сообщению" alt="к последнему сообщению" /></a></td>
</tr>';
		} while (($r = db_rowarr($result)));
	}

	if ($FUD_OPT_2 & 32768) {
		$page_pager = tmpl_create_pager($start, $THREADS_PER_PAGE, $frm->thread_count, 'index.php/sf/thread/'. $frm_id .'/1/', '/'. _rsid);
	} else {
		$page_pager = tmpl_create_pager($start, $THREADS_PER_PAGE, $frm->thread_count, 'index.php?t=thread&amp;frm_id='. $frm_id .'&amp;'. _rsid);
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
<?php echo $admin_cp; ?>
<table class="wa" border="0" cellspacing="0" cellpadding="0"><tr>
<td class="al wa"><?php echo draw_forum_path($frm->cat_id, $frm->name); ?><br /><span class="GenText fb">Показать:</span> <a href="index.php?t=selmsg&amp;date=today&amp;<?php echo _rsid; ?>&amp;frm_id=<?php echo (isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'&amp;th='.$th.'" title="Показать все отправленные сегодня сообщения">Сегодняшние сообщения</a>&nbsp;'.(_uid ? '<b>::</b> <a href="index.php?t=selmsg&amp;unread=1&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Показать все непрочитанные сообщения">Непрочитанные сообщения</a>&nbsp;' : '' ) .(!$th ? '<b>::</b> <a href="index.php?t=selmsg&amp;reply_count=0&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Показать все сообщения, на которые нет ответа">Сообщения без ответа</a>' : ''); ?> <b>::</b> <a href="index.php?t=polllist&amp;<?php echo _rsid; ?>">Показать голосования</a> <b>::</b> <a href="index.php?t=mnav&amp;<?php echo _rsid; ?>">Навигатор по сообщениям</a><br /><img src="blank.gif" alt="" height="2" /><br /><?php echo (_uid ? ($frm->subscribed ? '<a href="index.php?t='.$_GET['t'].'&amp;unsub=1&amp;frm_id='.$frm->id.'&amp;start='.$start.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'" title="Прекратить получение извещений о новых темах в этом форуме">Отписаться от форума</a>' : '<a href="index.php?t='.$_GET['t'].'&amp;sub=1&amp;frm_id='.$frm->id.'&amp;start='.$start.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'" title="Получать извещения при создании кем-либо новых тем в этом форуме">Подписка на форум</a>' )  : '' ) .((_uid && ($MOD || $frm->group_cache_opt & 2048)) ? '&nbsp;<a href="index.php?t=merge_th&amp;frm_id='.$frm->id.'&amp;'._rsid.'">Объединить темы</a>' : ''); ?></td>
<td class="GenText nw vb ar"><?php echo ($FUD_OPT_2 & 512 ? '<a href="index.php?t=threadt&amp;frm_id='.$frm->id.'&amp;'._rsid.'"><img title="древовидный вид" alt="древовидный вид" src="theme/vp1/images/tree_view.gif" /></a>&nbsp;' : ''); ?><a href="index.php?t=post&amp;frm_id=<?php echo $frm->id; ?>&amp;<?php echo _rsid; ?>"><img src="theme/vp1/images/new_thread.gif" alt="Создать новую тему" /></a></td>
</tr></table>
<?php echo ($MOD || $mo == 8224 ? '<form method="post" action="index.php?t=mmd">' : ''); ?>
<table border="0" cellspacing="1" cellpadding="2" class="pad">
<tr>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th class="wa">Тема</th>
	<th class="wo">Ответы</th>
	<th class="wo">Просмотров</th>
	<th class="nw">Последнее</th>
</tr>
<?php echo $announcements; ?>
<?php echo ($MOD || $mo == 8224 ? '
<tr>
	<td colspan="3" class="RowStyleC ar">
		<input type="submit" class="button small" name="del_sel_all" value="Удалить отмеченные" />
		<input type="submit" class="button small" name="mov_sel_all" value="Перенести отмеченные" />
		<input type="submit" class="button small" name="merge_sel_all" value="Объединить все выбранные" />
		<input type="submit" class="button small" name="loc_sel_all" value="Блокировать / разблокировать всех выбранных" />
		<input type="checkbox" name="toggle" title="Select all/none" onclick="$(\'input:checkbox\').attr(\'checked\', (this.checked)?\'checked\':\'\');" />
	</td>
	<td colspan="3" class="RowStyleC"> </td>
</tr>
' : ''); ?>
<?php echo $thread_list_table_data; ?>
<?php echo ($MOD || $mo == 8224 ? '
<tr>
	<td colspan="3" class="RowStyleC ar">
		<input type="submit" class="button small" name="del_sel_all" value="Удалить отмеченные" />
		<input type="submit" class="button small" name="mov_sel_all" value="Перенести отмеченные" />
		<input type="submit" class="button small" name="merge_sel_all" value="Объединить все выбранные" />
		<input type="submit" class="button small" name="loc_sel_all" value="Блокировать / разблокировать всех выбранных" />
		<input type="checkbox" name="toggle" title="Select all/none" onclick="$(\'input:checkbox\').attr(\'checked\', (this.checked)?\'checked\':\'\')" />
	</td>
	<td colspan="3" class="RowStyleC"> </td>
</tr>
' : ''); ?>
</table><?php echo ($MOD || $mo == 8224 ? _hs.'</form>' : ''); ?>
<table border="0" cellspacing="0" cellpadding="0" class="wa">
<tr>
<td class="vt"><?php echo $page_pager; ?>&nbsp;</td>
<td class="GenText nw vb ar"><?php echo ($FUD_OPT_2 & 512 ? '<a href="index.php?t=threadt&amp;frm_id='.$frm->id.'&amp;'._rsid.'"><img title="древовидный вид" alt="древовидный вид" src="theme/vp1/images/tree_view.gif" /></a>&nbsp;' : ''); ?><a href="index.php?t=post&amp;frm_id=<?php echo $frm->id; ?>&amp;<?php echo _rsid; ?>"><img src="theme/vp1/images/new_thread.gif" alt="Создать новую тему" /></a></td>
</tr>
</table>
<?php echo tmpl_create_forum_select((isset($frm->forum_id) ? $frm->forum_id : $frm->id), $usr->users_opt & 1048576); ?>
<?php echo (_uid ? '
	<div class="ar SmallText">[ <a href="index.php?t=markread&amp;'._rsid.'&amp;id='.$frm->id.'&amp;SQ='.$GLOBALS['sq'].'" title="Все сообщения в этом форуме будут отмечены как прочитанные вами">отметить все сообщения форума как прочитанные</a> ]'.($FUD_OPT_2 & 1048576 ? '&nbsp;[ <a href="index.php?t=help_index&amp;section=boardusage#syndicate">Сформировать XML</a> ]
[ <a href="feed.php?mode=m&amp;l=1&amp;basic=1&amp;frm='.$frm->id.'&amp;n=10"><img src="theme/vp1/images/rss.gif" title="Сформировать XML" alt="RSS" /></a> ]' : '' ) .(($FUD_OPT_2 & 270532608) == 270532608 ? '&nbsp;[ <a href="'.$GLOBALS['WWW_ROOT'].'pdf.php?frm='.$frm->id.'&amp;page='.$cur_frm_page.'&amp;'._rsid.'"><img src="theme/vp1/images/pdf.gif" title="Сформировать PDF для печати" alt="PDF" /></a> ]' : '' )  .'</div>
' : '
	<div class="ar SmallText">'.(($FUD_OPT_2 & 270532608) == 270532608 ? '&nbsp;[ <a href="'.$GLOBALS['WWW_ROOT'].'pdf.php?frm='.$frm->id.'&amp;page='.$cur_frm_page.'&amp;'._rsid.'"><img src="theme/vp1/images/pdf.gif" title="Сформировать PDF для печати" alt="PDF" /></a> ]' : '' ) .($FUD_OPT_2 & 1048576 ? '&nbsp;[ <a href="index.php?t=help_index&amp;section=boardusage#syndicate">Сформировать XML</a> ]
[ <a href="feed.php?mode=m&amp;l=1&amp;basic=1&amp;frm='.$frm->id.'&amp;n=10"><img src="theme/vp1/images/rss.gif" title="Сформировать XML" alt="RSS" /></a> ]' : '' )  .'</div>
'); ?>
<fieldset>
        <legend>Легенда:</legend>
<img src="theme/vp1/images/unread<?php echo img_ext; ?>" alt="Новые сообщения" />&nbsp;Новые сообщения&nbsp;&nbsp;
<img src="theme/vp1/images/read<?php echo img_ext; ?>" alt="Нет новых сообщений" />&nbsp;Нет новых сообщений&nbsp;&nbsp;
<img src="theme/vp1/images/unreadlocked<?php echo img_ext; ?>" alt="Тема закрыта (есть непрочитанные сообщения)" />&nbsp;Тема закрыта (есть непрочитанные сообщения)&nbsp;&nbsp;
<img src="theme/vp1/images/readlocked<?php echo img_ext; ?>" alt="Тема закрыта" />&nbsp;Тема закрыта&nbsp;&nbsp;
<img src="theme/vp1/images/moved<?php echo img_ext; ?>" alt="Тема перенесена в другой форум" />&nbsp;Тема перенесена в другой форум
</fieldset>
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
<?php
	if (_uid) {
		while (ob_get_level() > 0) ob_end_flush();
		user_register_forum_view($frm_id);
	}
?>
