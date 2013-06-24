<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: index.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function draw_user_link($login, $type, $custom_color='')
{
	if ($custom_color) {
		return '<span style="color: '.$custom_color.'">'.$login.'</span>';
	}

	switch ($type & 1572864) {
		case 0:
		default:
			return $login;
		case 1048576:
			return '<span class="adminColor">'.$login.'</span>';
		case 524288:
			return '<span class="modsColor">'.$login.'</span>';
	}
}

	$RSS = ($FUD_OPT_2 & 1048576 ? '
<link rel="alternate" type="application/rss+xml" title="Сформировать XML" href="'.$GLOBALS['WWW_ROOT'].'feed.php?mode=m&amp;l=1&amp;basic=1" />
' : '' )  ;
	$collapse = $usr->cat_collapse_status ? unserialize($usr->cat_collapse_status) : array();
	$cat_id = !empty($_GET['cat']) ? (int) $_GET['cat'] : 0;

	if ($cat_id && !empty($collapse[$cat_id])) {
		$collapse[$cat_id] = 0;
	}

	ses_update_status($usr->sid, 'Просмотр <a href="index.php?t=index">списка форумов</a>');

	require $FORUM_SETTINGS_PATH .'idx.inc';
	if (!isset($cidxc[$cat_id])) {
		$cat_id = 0;
	}

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
	}if (!isset($th)) {
	$th = 0;
}
if (!isset($frm->id)) {
	$frm = new stdClass();	// Initialize to prevent 'strict standards' notice.
	$frm->id = 0;
}
	$TITLE_EXTRA = ': Добро пожаловать в наш форум';

	$cbuf = $forum_list_table_data = $cat_path = '';

	if ($cat_id) {
		$cid = $cat_id;
		while (($cid = $cidxc[$cid][4]) > 0) {
			$cat_path = '&nbsp;&raquo; <a href="index.php?t=i&amp;cat='.$cid.'&amp;'._rsid.'">'.$cidxc[$cid][1].'</a>'. $cat_path;
		}
		$cat_path = '<br/><a href="index.php?t=i&amp;'._rsid.'">Начало</a>'.$cat_path.'&nbsp;&raquo; <b>'.$cidxc[$cat_id][1].'</b>';
	}

	/* List of fetched fields & their ids
	  0	msg.subject,
	  1	msg.id AS msg_id,
	  2	msg.post_stamp,
	  3	users.id AS user_id,
	  4	users.alias
	  5	forum.cat_id,
	  6	forum.forum_icon
	  7	forum.id
	  8	forum.last_post_id
	  9	forum.moderators
	  10	forum.name
	  11	forum.descr
	  12	forum.url_redirect
	  13	forum.post_count
	  14	forum.thread_count
	  15	forum_read.last_view
	  16	is_moderator
	  17	read perm
	  18	is the category using compact view
	*/
	$c = uq('SELECT
				m.subject, m.id, m.post_stamp,
				u.id, u.alias,
				f.cat_id, f.forum_icon, f.id, f.last_post_id, f.moderators, f.name, f.descr, f.url_redirect, f.post_count, f.thread_count,
				'. (_uid ? 'fr.last_view, mo.id, COALESCE(g2.group_cache_opt, g1.group_cache_opt) AS group_cache_opt' : '0,0,g1.group_cache_opt') .',
				c.cat_opt
			FROM fud26_fc_view v
			INNER JOIN fud26_cat c ON c.id=v.c
			INNER JOIN fud26_forum f ON f.id=v.f
			INNER JOIN fud26_group_cache g1 ON g1.user_id='. (_uid ? 2147483647 : 0) .' AND g1.resource_id=f.id
			LEFT JOIN fud26_msg m ON f.last_post_id=m.id
			LEFT JOIN fud26_users u ON u.id=m.poster_id '.
			(_uid ? ' LEFT JOIN fud26_forum_read fr ON fr.forum_id=f.id AND fr.user_id='._uid.' LEFT JOIN fud26_mod mo ON mo.user_id='._uid.' AND mo.forum_id=f.id LEFT JOIN fud26_group_cache g2 ON g2.user_id='._uid.' AND g2.resource_id=f.id' : '').
			((!$is_a || $cat_id) ?  ' WHERE ' : '') .
			($is_a ? '' : (_uid ? ' (mo.id IS NOT NULL OR ('. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 1) .' > 0))' : ' ('. q_bitand('g1.group_cache_opt', 1) .' > 0)')) .
			($cat_id ? ($is_a ? '' : ' AND ') .' v.c IN('. implode(',', ($cf = $cidxc[$cat_id][5])) .') ' : '') .' ORDER BY v.id');

	$post_count = $thread_count = $last_msg_id = $cat = 0;
	while ($r = db_rowarr($c)) {
		/* Increase thread & post count. */
		$post_count += $r[13];
		$thread_count += $r[14];

		$cid = (int) $r[5];

		if ($cat != $cid) {
			if ($cbuf) { /* If previous category was using compact view, print forum row. */
				if (empty($collapse[$i[4]])) { /* Only show if parent is not collapsed as well. */
					$forum_list_table_data .= '<tr class="RowStyleB"><td colspan="6">Доступные форумы:'.$cbuf.'</td></tr>';
				}
				$cbuf = '';
			}

			while (list($k, $i) = each($cidxc)) {
				/* 2nd check ensures that we don't end up displaying categories without any children. */ 
				if (($cat_id && !isset($cf[$k])) || ($cid != $k && $i[4] >= $cidxc[$cid][4])) {
					continue;
				}

				/* If parent category is collapsed, hide child category. */
				if ($i[4] && !empty($collapse[$i[4]])) {
					$collapse[$k] = 1;
				}

				if ($i[3] & 1 && $k != $cat_id && !($i[3] & 4)) {
					if (!isset($collapse[$k])) {
						$collapse[$k] = !($i[3] & 2);
					}
					$forum_list_table_data .= '<tr id="c'.$r[5].'" style="display: '.(empty($collapse[$i[4]]) ? 'table-row' : 'none' )  .';">
<td class="CatDesc '.(empty($collapse[$cid]) ? 'expanded' : 'collapsed' )  .'" colspan="5" style="padding-left: '.($i[0] ? $i[0] * 20 : '0').'px;">
<a href="index.php?t=index&amp;cat='.$k.'&amp;'._rsid.'" class="CatLink">'.$i[1].'</a> '.$i[2].'</td><td class="CatDesc">
	'.(key($cidxc) ? '<a href="javascript://" onclick=\'nextCat("c'.$k.'")\'><img src="theme/vp1/images/down'.img_ext.'" alt="" border="0" style="vertical-align: top; float: right;" /></a>' : '' )  .'
	'.($cat ? '<a href="javascript://" onclick=\'prevCat("c'.$k.'")\'><img src="theme/vp1/images/up'.img_ext.'" border="0" alt="" style="vertical-align: top; float: right;" /></a>' : '' )  .'
</td></tr>';
				} else {
					if ($i[3] & 4) {
						++$i[0];
					}
					$forum_list_table_data .= '<tr id="c'.$r[5].'" style="display: '.(empty($collapse[$i[4]]) ? 'table-row' : 'none' )  .';">
<td class="CatDesc CatLockPad" colspan="5" style="padding-left: '.($i[0] ? $i[0] * 20 : '0').'px;">
<span class="CatLockedName"><a href="index.php?t=index&amp;cat='.$k.'&amp;'._rsid.'" class="CatLink">'.$i[1].'</a></span> '.$i[2].'</td><td class="CatDesc">
	'.(key($cidxc) ? '<a href="javascript://" onclick=\'nextCat("c'.$k.'")\'><img src="theme/vp1/images/down'.img_ext.'" alt="" border="0" style="vertical-align: top; float: right;" /></a>' : '' )  .'
	'.($cat ? '<a href="javascript://" onclick=\'prevCat("c'.$k.'")\'><img src="theme/vp1/images/up'.img_ext.'" border="0" alt="" style="vertical-align: top; float: right;" /></a>' : '' )  .'
</td></tr>';
				}
			
				if ($k == $cid) {
					break;
				}
			}
			$cat = $cid;
		}

		/* Compact view check. */
		if ($r[18] & 4) {
			$cbuf .= '&nbsp; '.(_uid && $r[15] < $r[2] && $usr->last_read < $r[2] ? '**' : '' )  .'<a href="'.(empty($r[12]) ? 'index.php?t='.t_thread_view.'&amp;frm_id='.$r[7].'&amp;'._rsid.'' : $r[12].'' )  .'">'.$r[10].'</a>';
			continue;
		}

		if (!($r[17] & 2) && !$is_a && !$r[16]) { /* Visible forum with no 'read' permission. */
			$forum_list_table_data .= '<tr style="display: '.(empty($collapse[$cid]) ? 'table-row' : 'none' )  .'" class="child-c'.$r[5].'">
	<td class="RowStyleA" colspan="6">'.$r[10].($r[11] ? '<br />'.$r[11] : '').'</td>
</tr>';
			continue;
		}

		/* Code to determine the last post id for 'latest' forum message. */
		if ($r[8] > $last_msg_id) {
			$last_msg_id = $r[8];
		}

		if (!_uid) { /* Anon user. */
			$forum_read_indicator = '<img title="Только зарегистрированные участники форума могут отслеживать и читать &amp; новые сообщений" src="theme/vp1/images/existing_content'.img_ext.'" alt="Только зарегистрированные участники форума могут отслеживать и читать &amp; новые сообщений" />';
		} else if ($r[15] < $r[2] && $usr->last_read < $r[2]) {
			$forum_read_indicator = '<img title="Новые сообщения" src="theme/vp1/images/new_content'.img_ext.'" alt="Новые сообщения" />';
		} else {
			$forum_read_indicator = '<img title="Нет новых сообщений" src="theme/vp1/images/existing_content'.img_ext.'" alt="Нет новых сообщений" />';
		}

		if ($r[9] && ($mods = unserialize($r[9]))) {
			$moderators = '';	// List of forum modeators.
			$modcount = 0;		// Use singular or plural message form.
			foreach($mods as $k => $v) {
				$moderators .= '<a href="index.php?t=usrinfo&amp;id='.$k.'&amp;'._rsid.'">'.$v.'</a> &nbsp;';
				$modcount++;
			}
			$moderators = '<div class="TopBy"><b>'.convertPlural($modcount, array('Модератор','Модераторы')).':</b> '.$moderators.'</div>';
		} else {
			$moderators = '&nbsp;';
		}

		$forum_list_table_data .= '<tr style="display: '.(empty($collapse[$cid]) ? 'table-row' : 'none' )  .'" class="child-c'.$r[5].'">
	<td class="RowStyleA wo">'.($r[6] ? '<img src="images/forum_icons/'.$r[6].'" alt="Иконка форуму" />' : '&nbsp;' ) .'</td>
	<td class="RowStyleB ac wo">'.(empty($r[12]) ? $forum_read_indicator.'' : '<img title="Перенаправление" src="theme/vp1/images/moved'.img_ext.'" alt="" />' )  .'</td>
	<td class="RowStyleA wa"><a href="'.(empty($r[12]) ? 'index.php?t='.t_thread_view.'&amp;frm_id='.$r[7].'&amp;'._rsid.'' : $r[12].'' )  .'" class="big">'.$r[10].'</a>'.($r[11] ? '<br />'.$r[11] : '').'

'.($r[7]==333333333333 ? '<a style="margin-bottom: -20px;" href="http://chillengrillen.ru/webscript/"><img border=0 src="/tmp/cg_banner_150_40.gif" alt="ChillenGrillen"></a>' : '' ) .'
'.($r[7]==76 ? '' : '' ) .'

'.$moderators.'</td>
	<td class="RowStyleB ac">'.(empty($r[12]) ? $r[13].'' : '--' )  .'</td>
	<td class="RowStyleB ac">'.(empty($r[12]) ? $r[14].'' : '--' )  .'</td>
	<td class="RowStyleA ac nw">'.(empty($r[12]) ? ($r[8] ? '<span class="DateText">'.strftime("%a, %d %B %Y", $r[2]).'</span><br />От: '.($r[3] ? '<a href="index.php?t=usrinfo&amp;id='.$r[3].'&amp;'._rsid.'">'.$r[4].'</a>' : $GLOBALS['ANON_NICK'].'' ) .' <a href="index.php?t='.d_thread_view.'&amp;goto='.$r[8].'&amp;'._rsid.'#msg_'.$r[8].'"><img title="'.$r[0].'" src="theme/vp1/images/goto.gif" alt="'.$r[0].'" /></a>' : 'н/д' ) : '--' )  .'</td>
</tr>';
	}
	unset($c);

	if ($cbuf) { /* If previous category was using compact view, print forum row. */
		$forum_list_table_data .= '<tr class="RowStyleB"><td colspan="6">Доступные форумы:'.$cbuf.'</td></tr>';
	}

function &rebuild_stats_cache($last_msg_id)
{
	$tm_expire = __request_timestamp__ - ($GLOBALS['LOGEDIN_TIMEOUT'] * 60);

	$obj = new stdClass();	// Initialize to prevent 'strict standards' notice.
	list($obj->last_user_id, $obj->user_count) = db_saq('SELECT MAX(id), count(*)-1 FROM fud26_users');

	$obj->online_users_anon	= q_singleval('SELECT count(*) FROM fud26_ses s WHERE time_sec>'. $tm_expire .' AND user_id>2000000000');
	$obj->online_users_hidden = q_singleval('SELECT count(*) FROM fud26_ses s INNER JOIN fud26_users u ON u.id=s.user_id WHERE s.time_sec>'. $tm_expire .' AND '. q_bitand('u.users_opt', 32768) .'>0');
	$obj->online_users_reg = q_singleval('SELECT count(*) FROM fud26_ses s INNER JOIN fud26_users u ON u.id=s.user_id WHERE s.time_sec>'. $tm_expire .' AND '. q_bitand('u.users_opt', 32768) .'=0');
	$c = uq('SELECT u.id, u.alias, u.users_opt, u.custom_color FROM fud26_ses s INNER JOIN fud26_users u ON u.id=s.user_id WHERE s.time_sec>'. $tm_expire .' AND '. q_bitand('u.users_opt', 32768) .'=0 ORDER BY s.time_sec DESC LIMIT '. $GLOBALS['MAX_LOGGEDIN_USERS']);
	$obj->online_users_text = array();
	while ($r = db_rowarr($c)) {
		$obj->online_users_text[$r[0]] = draw_user_link($r[1], $r[2], $r[3]);
	}
	unset($c);

	q('UPDATE fud26_stats_cache SET
		cache_age='. __request_timestamp__ .',
		last_user_id='. (int)$obj->last_user_id .',
		user_count='. (int)$obj->user_count .',
		online_users_anon='. (int)$obj->online_users_anon .',
		online_users_hidden='. (int)$obj->online_users_hidden .',
		online_users_reg='. (int)$obj->online_users_reg .',
		online_users_text='. ssn(serialize($obj->online_users_text)));

	$obj->last_user_alias = q_singleval('SELECT alias FROM fud26_users WHERE id='. $obj->last_user_id);
	$obj->last_msg_subject = q_singleval('SELECT subject FROM fud26_msg WHERE id='. $last_msg_id);

	list($obj->most_online,$obj->most_online_time) = db_saq('SELECT most_online, most_online_time FROM fud26_stats_cache');
	/* Update most online users stats if needed. */
	if (($obj->online_users_reg + $obj->online_users_hidden + $obj->online_users_anon) > $obj->most_online) {
		$obj->most_online = $obj->online_users_reg + $obj->online_users_hidden + $obj->online_users_anon;
		$obj->most_online_time = __request_timestamp__;
		q('UPDATE fud26_stats_cache SET most_online='. $obj->most_online .', most_online_time='. $obj->most_online_time);
	} else if (!$obj->most_online_time) {
		$obj->most_online_time = __request_timestamp__;
	}

	return $obj;
}

$logedin = $forum_info = '';

if ($FUD_OPT_1 & 1073741824 || $FUD_OPT_2 & 16) {
	if (!($st_obj = db_sab('SELECT sc.*,m.subject AS last_msg_subject, u.alias AS last_user_alias FROM fud26_stats_cache sc INNER JOIN fud26_users u ON u.id=sc.last_user_id LEFT JOIN fud26_msg m ON m.id='. $last_msg_id .' WHERE sc.cache_age>'. (__request_timestamp__ - $STATS_CACHE_AGE)))) {
		$st_obj = rebuild_stats_cache($last_msg_id);
	} else if ($st_obj->online_users_text && (_uid || !($FUD_OPT_3 & 262144))) {
		$st_obj->online_users_text = unserialize($st_obj->online_users_text);
	}

	if (!$st_obj->most_online_time) {
		$st_obj->most_online_time = __request_timestamp__;
	}

	if ($FUD_OPT_1 & 1073741824 && (_uid || !($FUD_OPT_3 & 262144))) {
		if (!empty($st_obj->online_users_text)) {
			foreach($st_obj->online_users_text as $k => $v) {
				$logedin .= '<a href="index.php?t=usrinfo&amp;id='.$k.'&amp;'._rsid.'">'.$v.'</a> ';
			}
		}
		$logedin = '<tr><th class="wa">Сейчас на форуме '.(($FUD_OPT_1 & 536870912) ? (_uid || !($FUD_OPT_3 & 131072) ? '[ <a href="index.php?t=actions&amp;'._rsid.'" class="thLnk">показать кто чем занимается</a> ]' : '' ) .(_uid || !($FUD_OPT_3 & 262144) ? ' [ <a href="index.php?t=online_today&amp;'._rsid.'" class="thLnk">Сегодняшние посетители</a> ]' : '' )  : '' ) .'</th></tr>
<tr><td class="RowStyleA">
<span class="SmallText">В настоящее время на форуме присутствуют <b>'.convertPlural($st_obj->online_users_reg, array(''.$st_obj->online_users_reg.' участник',''.$st_obj->online_users_reg.' участника',''.$st_obj->online_users_reg.' участников')).'</b>, <b>'.convertPlural($st_obj->online_users_hidden, array(''.$st_obj->online_users_hidden.' невидимый участник',''.$st_obj->online_users_hidden.' невидимых участника',''.$st_obj->online_users_hidden.' невидимых участников')).'</b> и <b>'.convertPlural($st_obj->online_users_anon, array(''.$st_obj->online_users_anon.' гость',''.$st_obj->online_users_anon.' гостя',''.$st_obj->online_users_anon.' гостей')).'</b>.&nbsp;&nbsp;&nbsp;<span class="adminColor">[Администратор]</span>&nbsp;&nbsp;<span class="modsColor">[Модератор]</span></span><br />
'.$logedin.'
</td></tr>';
	}
	if ($FUD_OPT_2 & 16) {
		$forum_info = '<tr><td class="RowStyleB SmallText">
Нашими пользователями оставлено <b>'.convertPlural($post_count, array(''.$post_count.' сообщение',''.$post_count.' сообщения',''.$post_count.' сообщений')).'</b> по <b>'.convertPlural($thread_count, array(''.$thread_count.' теме',''.$thread_count.' темам',''.$thread_count.' темам')).'</b>.<br />
Наибольшее количество посетителей (<b>'.$st_obj->most_online.'</b>) было на форуме в <b>'.strftime("%a, %d %B %Y %H:%M", $st_obj->most_online_time).'</b><br />
У нас <b>'.$st_obj->user_count.'</b> '.convertPlural($st_obj->user_count, array('зарегистрированный участник','зарегистрированных участника','зарегистрированных участника')).'.<br />
Последний зарегистрированный участник: <a href="index.php?t=usrinfo&amp;id='.$st_obj->last_user_id.'&amp;'._rsid.'"><b>'.$st_obj->last_user_alias.'</b></a>'.($last_msg_id ? '<br />Последнее сообщение в форуме: <a href="index.php?t='.d_thread_view.'&amp;goto='.$last_msg_id.'&amp;'._rsid.'#msg_'.$last_msg_id.'"><b>'.$st_obj->last_msg_subject.'</b></a>' : '' ) .'</td></tr>';
	}
}if ($FUD_OPT_2 & 2 || $is_a) {	// PUBLIC_STATS is enabled or Admin user.
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
<?php echo (_uid ? '<span class="GenText">Добро пожаловать, <b>'.$usr->alias.'</b>, ваше последнее посещение форума: '.strftime("%a, %d %B %Y %H:%M", $usr->last_visit).'</span><br />' : ''); ?>
<span class="GenText fb">Показать:</span> <a href="index.php?t=selmsg&amp;date=today&amp;<?php echo _rsid; ?>&amp;frm_id=<?php echo (isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'&amp;th='.$th.'" title="Показать все отправленные сегодня сообщения">Сегодняшние сообщения</a>&nbsp;'.(_uid ? '<b>::</b> <a href="index.php?t=selmsg&amp;unread=1&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Показать все непрочитанные сообщения">Непрочитанные сообщения</a>&nbsp;' : '' ) .(!$th ? '<b>::</b> <a href="index.php?t=selmsg&amp;reply_count=0&amp;'._rsid.'&amp;frm_id='.(isset($frm->forum_id) ? $frm->forum_id.'' : $frm->id.'' )  .'" title="Показать все сообщения, на которые нет ответа">Сообщения без ответа</a>' : ''); ?> <b>::</b> <a href="index.php?t=polllist&amp;<?php echo _rsid; ?>">Показать голосования</a> <b>::</b> <a href="index.php?t=mnav&amp;<?php echo _rsid; ?>">Навигатор по сообщениям</a><br /><img src="blank.gif" alt="" height="2" /><?php echo $admin_cp; ?>
<?php echo $cat_path; ?>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr>
	<th colspan="3" class="wa">Форум</th>
	<th style="white-space: nowrap">Сообщений</th>
	<th style="white-space: nowrap">Тем</th>
	<th style="white-space: nowrap">Последнее</th>
</tr>
<?php echo $forum_list_table_data; ?>
</table>
<?php echo (_uid ? '<div class="SmallText ar">[ <a href="index.php?t=markread&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'&amp;cat='.$cat_id.'" title="Все непрочитанные вами сообщения были помечены как прочитанные">Отметить все сообщения как прочитанные</a> ]
'.($FUD_OPT_2 & 1048576 ? '[ <a href="feed.php?mode=m&amp;l=1&amp;basic=1"><img src="theme/vp1/images/rss.gif" title="Сформировать XML" alt="Сформировать XML"/></a> ]' : '' )  .'
</div>' : ''); ?>
<?php echo (__fud_real_user__ ? '' : '<table class="wa" border="0" cellspacing="0" cellpadding="0"><tr><td align="right">
<form id="quick_login_form" method="post" action="index.php?t=login"'.($GLOBALS['FUD_OPT_3'] & 256 ? ' autocomplete="off"' : '').'>'._hs.'
<table border="0" cellspacing="0" cellpadding="3">
<tr class="SmallText">
	<td><label>Имя:<br /><input class="SmallText" type="text" name="quick_login" size="18" /></label></td>
	<td><label>Пароль:<br /><input class="SmallText" type="password" name="quick_password" size="18" /></label></td>
	'.($FUD_OPT_1 & 128 ? '<td>&nbsp;<br /><label><input type="checkbox" checked="checked" name="quick_use_cookies" value="1" /> Использовать Cookies?</label></td>' : '' )  .'
	<td>&nbsp;<br /><input type="submit" class="button" name="quick_login_submit" value="Войти" /></td>
</tr>
</table></form></td></tr></table>'); ?>
<?php echo ($logedin || $forum_info ? '<br />
<table cellspacing="1" cellpadding="2" class="ContentTable">
'.$logedin.'
'.$forum_info.'
</table>' : ''); ?>
<br /><fieldset>
<legend>Легенда:</legend>
<img src="theme/vp1/images/new_content<?php echo img_ext; ?>" alt="Есть новые сообщения с момента последнего посещения" /> Есть новые сообщения с момента последнего посещения&nbsp;&nbsp;
<img src="theme/vp1/images/existing_content<?php echo img_ext; ?>" alt="Нет новых сообщения с момента последнего посещения" /> Нет новых сообщения с момента последнего посещения&nbsp;&nbsp;
<img src="theme/vp1/images/moved<?php echo img_ext; ?>" alt="Перенаправление" /> Перенаправление
</fieldset>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<script type="text/javascript">
/* <![CDATA[ */
min_max_cats("theme/vp1/images", "<?php echo img_ext; ?>", "Свернуть категорию", "Развернуть категорию", "<?php echo $usr->sq; ?>", "<?php echo s; ?>");
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
