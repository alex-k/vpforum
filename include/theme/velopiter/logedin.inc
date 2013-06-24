<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: logedin.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

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
		$logedin = '<tr><th class="wa">Сейчас на форуме '.(($FUD_OPT_1 & 536870912) ? ''.(_uid || !($FUD_OPT_3 & 131072) ? '[ <a href="index.php?t=actions&amp;'._rsid.'" class="thLnk">показать кто чем занимается</a> ]' : '' ) .(_uid || !($FUD_OPT_3 & 262144) ? ' [ <a href="index.php?t=online_today&amp;'._rsid.'" class="thLnk">Сегодняшние посетители</a> ]' : '' ) .'' : '' ) .'</th></tr>
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
}
?>