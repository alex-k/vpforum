<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: selmsg.php.t 5072 2010-11-11 17:12:40Z naudefj $
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
}/* Handle poll votes if any are present. */
function register_vote(&$options, $poll_id, $opt_id, $mid)
{
	/* Invalid option or previously voted. */
	if (!isset($options[$opt_id]) || q_singleval('SELECT id FROM fud26_poll_opt_track WHERE poll_id='. $poll_id .' AND user_id='. _uid)) {
		return;
	}

	if (db_li('INSERT INTO fud26_poll_opt_track(poll_id, user_id, poll_opt) VALUES('. $poll_id .', '. _uid .', '. $opt_id .')', $a)) {
		q('UPDATE fud26_poll_opt SET count=count+1 WHERE id='. $opt_id);
		q('UPDATE fud26_poll SET total_votes=total_votes+1 WHERE id='. $poll_id);
		$options[$opt_id][1] += 1;
		q('UPDATE fud26_msg SET poll_cache='. _esc(serialize($options)) .' WHERE id='. $mid);
	}

	return 1;
}

$GLOBALS['__FMDSP__'] = array();

/* Needed for message threshold & reveling messages. */
if (isset($_GET['rev'])) {
	$_GET['rev'] = htmlspecialchars((string)$_GET['rev']);
	foreach (explode(':', $_GET['rev']) as $v) {
		$GLOBALS['__FMDSP__'][(int)$v] = 1;
	}
	if ($GLOBALS['FUD_OPT_2'] & 32768) {
		define('reveal_lnk', '/'. $_GET['rev']);
	} else {
		define('reveal_lnk', '&amp;rev='. $_GET['rev']);
	}
} else {
	define('reveal_lnk', '');
}

/* Initialize buddy & ignore list for registered users. */
if (_uid) {
	if ($usr->buddy_list) {
		$usr->buddy_list = unserialize($usr->buddy_list);
	}
	if ($usr->ignore_list) {
		$usr->ignore_list = unserialize($usr->ignore_list);
		if (isset($usr->ignore_list[1])) {
			$usr->ignore_list[0] =& $usr->ignore_list[1];
		}
	}

	/* Handle temporarily un-hidden users. */
	if (isset($_GET['reveal'])) {
		$_GET['reveal'] = htmlspecialchars((string)$_GET['reveal']);
		foreach(explode(':', $_GET['reveal']) as $v) {
			$v = (int) $v;
			if (isset($usr->ignore_list[$v])) {
				$usr->ignore_list[$v] = 0;
			}
		}
		if ($GLOBALS['FUD_OPT_2'] & 32768) {
			define('unignore_tmp', '/'. $_GET['reveal']);
		} else {
			define('unignore_tmp', '&amp;reveal='. $_GET['reveal']);
		}
	} else {
		define('unignore_tmp', '');
	}
} else {
	define('unignore_tmp', '');
	if (isset($_GET['reveal'])) {
		unset($_GET['reveal']);
	}
}

if ($GLOBALS['FUD_OPT_2'] & 2048) {
	$GLOBALS['affero_domain'] = parse_url($WWW_ROOT);
	$GLOBALS['affero_domain'] = $GLOBALS['affero_domain']['host'];
}

$_SERVER['QUERY_STRING_ENC'] = htmlspecialchars($_SERVER['QUERY_STRING']);

function make_tmp_unignore_lnk($id)
{
	if ($GLOBALS['FUD_OPT_2'] & 32768 && strpos($_SERVER['QUERY_STRING_ENC'], '?') === false) {
		$_SERVER['QUERY_STRING_ENC'] .= '?1=1';
	}

	if (!isset($_GET['reveal'])) {
		return $_SERVER['QUERY_STRING_ENC'] .'&amp;reveal='. $id;
	} else {
		return str_replace('&amp;reveal='. $_GET['reveal'], unignore_tmp .':'. $id, $_SERVER['QUERY_STRING_ENC']);
	}
}

function make_reveal_link($id)
{
	if ($GLOBALS['FUD_OPT_2'] & 32768 && strpos($_SERVER['QUERY_STRING_ENC'], '?') === false) {
		$_SERVER['QUERY_STRING_ENC'] .= '?1=1';
	}

	if (empty($GLOBALS['__FMDSP__'])) {
		return $_SERVER['QUERY_STRING_ENC'] .'&amp;rev='. $id;
	} else {
		return str_replace('&amp;rev='. $_GET['rev'], reveal_lnk .':'. $id, $_SERVER['QUERY_STRING_ENC']);
	}
}

/* Draws a message, needs a message object, user object, permissions array,
 * flag indicating wether or not to show controls and a variable indicating
 * the number of the current message (needed for cross message pager)
 * last argument can be anything, allowing forms to specify various vars they
 * need to.
 */
function tmpl_drawmsg($obj, $usr, $perms, $hide_controls, &$m_num, $misc)
{
	$o1 =& $GLOBALS['FUD_OPT_1'];
	$o2 =& $GLOBALS['FUD_OPT_2'];
	$a = (int) $obj->users_opt;
	$b =& $usr->users_opt;

	$next_page = $next_message = $prev_message = '';
	/* Draw next/prev message controls. */
	if (!$hide_controls && $misc) {
		/* Tree view is a special condition, we only show 1 message per page. */
		if ($_GET['t'] == 'tree' || $_GET['t'] == 'tree_msg') {
			$prev_message = $misc[0] ? '<a href="javascript://" onclick="changeMsgFocus('.$misc[0].')"><img src="theme/velopiter/images/up'.img_ext.'" title="Переход к предыдущему сообщения" alt="Переход к предыдущему сообщения" width="16" height="11" /></a>' : '';
			$next_message = $misc[1] ? '<a href="javascript://" onclick="changeMsgFocus('.$misc[1].')"><img alt="Переход к предыдущему сообщения" title="Переход к следующему сообщения" src="theme/velopiter/images/down'.img_ext.'" width="16" height="11" /></a>' : '';
		} else {
			/* Handle previous link. */
			if (!$m_num && $obj->id > $obj->root_msg_id) { /* prev link on different page */
				$prev_message = '<a href="index.php?t='.$_GET['t'].'&amp;'._rsid.'&amp;prevloaded=1&amp;th='.$obj->thread_id.'&amp;start='.($misc[0] - $misc[1]).reveal_lnk.unignore_tmp.'"><img src="theme/velopiter/images/up'.img_ext.'" title="Переход к предыдущему сообщения" alt="Переход к предыдущему сообщения" width="16" height="11" /></a>';
			} else if ($m_num) { /* Inline link, same page. */
				$prev_message = '<a href="javascript://" onclick="chng_focus(\'#msg_num_'.$m_num.'\');"><img alt="Переход к предыдущему сообщения" title="Переход к предыдущему сообщения" src="theme/velopiter/images/up'.img_ext.'" width="16" height="11" /></a>';
			}

			/* Handle next link. */
			if ($obj->id < $obj->last_post_id) {
				if ($m_num && !($misc[1] - $m_num - 1)) { /* next page link */
					$next_message = '<a href="index.php?t='.$_GET['t'].'&amp;'._rsid.'&amp;prevloaded=1&amp;th='.$obj->thread_id.'&amp;start='.($misc[0] + $misc[1]).reveal_lnk.unignore_tmp.'"><img alt="Переход к предыдущему сообщения" title="Переход к следующему сообщения" src="theme/velopiter/images/down'.img_ext.'" width="16" height="11" /></a>';
					$next_page = '<a href="index.php?t='.$_GET['t'].'&amp;'._rsid.'&amp;prevloaded=1&amp;th='.$obj->thread_id.'&amp;start='.($misc[0] + $misc[1]).reveal_lnk.unignore_tmp.'">Следующая страница <img src="theme/velopiter/images/goto.gif" alt="" /></a>';
				} else {
					$next_message = '<a href="javascript://" onclick="chng_focus(\'#msg_num_'.($m_num + 2).'\');"><img alt="Переход к следующему сообщения" title="Переход к следующему сообщения" src="theme/velopiter/images/down'.img_ext.'" width="16" height="11" /></a>';
				}
			}
		}
		++$m_num;
	}

	$user_login = $obj->user_id ? $obj->login : $GLOBALS['ANON_NICK'];

	/* Check if the message should be ignored and it is not temporarily revelead. */
	if ($usr->ignore_list && !empty($usr->ignore_list[$obj->poster_id]) && !isset($GLOBALS['__FMDSP__'][$obj->id])) {
		return !$hide_controls ? '<tr><td><table border="0" cellspacing="0" cellpadding="0" class="MsgTable"><tr><td class="MsgIg al">
<a name="msg_num_'.$m_num.'"></a>
<a name="msg_'.$obj->id.'"></a>
'.($obj->user_id ? 'Сообщение от <a href="index.php?t=usrinfo&amp;'._rsid.'&amp;id='.$obj->user_id.'">'.$obj->login.'</a> игнорировано' : $GLOBALS['ANON_NICK'].' игнорирован' )  .'&nbsp;
[<a href="index.php?'. make_reveal_link($obj->id).'">показать сообщение</a>]&nbsp;
[<a href="index.php?'.make_tmp_unignore_lnk($obj->poster_id).'">показать все сообщения от '.$user_login.'</a>]&nbsp;
[<a href="index.php?t=ignore_list&amp;del='.$obj->poster_id.'&amp;redr=1&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">прекратить игнорирование участника</a>]</td>
<td class="MsgIg" align="right">'.$prev_message.$next_message.'</td></tr>
</table></td></tr>' : '<tr class="MsgR1 GenText">
<td><a name="msg_num_'.$m_num.'"></a> <a name="msg_'.$obj->id.'"></a>Post by '.$user_login.' is ignored&nbsp;</td>
</tr>';
	}

	if ($obj->user_id && !$hide_controls) {
		$custom_tag = $obj->custom_status ? '<br />'.$obj->custom_status : '';
		$c = (int) $obj->level_opt;

		if ($obj->avatar_loc && $a & 8388608 && $b & 8192 && $o1 & 28 && !($c & 2)) {
			if (!($c & 1)) {
				$level_name =& $obj->level_name;
				$level_image = $obj->level_img ? '&nbsp;<img src="images/'.$obj->level_img.'" alt="" />' : '';
			} else {
				$level_name = $level_image = '';
			}
		} else {
			$level_image = $obj->level_img ? '&nbsp;<img src="images/'.$obj->level_img.'" alt="" />' : '';
			$obj->avatar_loc = '';
			$level_name =& $obj->level_name;
		}
		$avatar = ($obj->avatar_loc || $level_image) ? '<td class="avatarPad wo">'.$obj->avatar_loc.$level_image.'</td>' : '';
		$dmsg_tags = ($custom_tag || $level_name) ? '<div class="ctags">'.$level_name.$custom_tag.'</div>' : '';

		if (($o2 & 32 && !($a & 32768)) || $b & 1048576) {
			$online_indicator = (($obj->time_sec + $GLOBALS['LOGEDIN_TIMEOUT'] * 60) > __request_timestamp__) ? '<img src="theme/velopiter/images/online'.img_ext.'" alt="онлайн" title="онлайн" />&nbsp;' : '<img src="theme/velopiter/images/offline'.img_ext.'" alt="оффлайн" title="оффлайн" />&nbsp;';
		} else {
			$online_indicator = '';
		}

		$user_link = '<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'">'.$user_login.'</a>';

		$location = $obj->location ? '<br /><b>Город: </b> <br />'.(strlen($obj->location) > $GLOBALS['MAX_LOCATION_SHOW'] ? substr($obj->location, 0, $GLOBALS['MAX_LOCATION_SHOW']) . '...' : $obj->location) : '';

		if (_uid && _uid != $obj->user_id) {
			$buddy_link	= !isset($usr->buddy_list[$obj->user_id]) ? '<a href="index.php?t=buddy_list&amp;add='.$obj->user_id.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">в контакты</a><br />' : '<a href="index.php?t=buddy_list&amp;del='.$obj->user_id.'&amp;redr=1&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">удалить из контактов</a><br />';
			$ignore_link	= !isset($usr->ignore_list[$obj->user_id]) ? '<a href="index.php?t=ignore_list&amp;add='.$obj->user_id.'&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">игнорировать все сообщения<br />от этого участника</a>' : '<a href="index.php?t=ignore_list&amp;del='.$obj->user_id.'&amp;redr=1&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">прекратить игнорировать сообщения этого участника</a>';
			$dmsg_bd_il	= $buddy_link.$ignore_link.'<br />';
		} else {
			$dmsg_bd_il = '';
		}

		/* Show im buttons if need be. */
		if ($b & 16384) {
			$im = '';
			if ($obj->icq) {
				$im .= '<a href="index.php?t=usrinfo&amp;id='.$obj->poster_id.'&amp;'._rsid.'#icq_msg"><img title="'.$obj->icq.'" src="theme/velopiter/images/icq'.img_ext.'" alt="" /></a>';
			}
			if ($obj->aim) {
				$im .= '<a href="aim:goim?screenname='.$obj->aim.'&amp;message=Hi.+Are+you+there?"><img alt="" src="theme/velopiter/images/aim'.img_ext.'" title="'.$obj->aim.'" /></a>';
			}
			if ($obj->yahoo) {
				$im .= '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.$obj->yahoo.'&amp;.src=pg"><img alt="" src="theme/velopiter/images/yahoo'.img_ext.'" title="'.$obj->yahoo.'" /></a>';
			}
			if ($obj->msnm) {
				$im .= '<a href="mailto: '.$obj->msnm.'"><img alt="" src="theme/velopiter/images/msnm'.img_ext.'" title="'.$obj->msnm.'" /></a>';
			}
			if ($obj->jabber) {
				$im .=  '<img src="theme/velopiter/images/jabber'.img_ext.'" title="'.$obj->jabber.'" alt="" />';
			}
			if ($obj->google) {
				$im .= '<img src="theme/velopiter/images/google'.img_ext.'" title="'.$obj->google.'" alt="" />';
			}
			if ($obj->skype) {
				$im .=  '<a href="callto://'.$obj->skype.'"><img src="theme/velopiter/images/skype'.img_ext.'" title="'.$obj->skype.'" alt="" /></a>';
			}
			if ($obj->twitter) {
				$im .=  '<a href="http://twitter.com/'.$obj->twitter.'"><img src="theme/velopiter/images/twitter'.img_ext.'" title="'.$obj->twitter.'" alt="" /></a>';
			}
			if ($o2 & 2048) {
				if ($obj->affero) {
					$im .= '<a href="http://svcs.affero.net/rm.php?r='.$obj->affero.'&amp;ll='.$obj->forum_id.'.'.$GLOBALS['affero_domain'].'&amp;lp='.$obj->forum_id.'.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;ls='.urlencode($obj->subject).'"><img alt="" src="theme/velopiter/images/affero_reg.gif" /></a>';
				} else {
					$im .= '<a href="http://svcs.affero.net/rm.php?m='.urlencode($obj->email).'&amp;ll='.$obj->forum_id.'.'.$GLOBALS['affero_domain'].'&amp;lp='.$obj->forum_id.'.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;ls='.urlencode($obj->subject).'"><img alt="" src="theme/velopiter/images/affero_noreg.gif" /></a>';
				}
			}
			if ($im) {
				$dmsg_im_row = $im.'<br />';
			} else {
				$dmsg_im_row = '';
			}
		} else {
			$dmsg_im_row = '';
		}
	} else {
		$user_link = $obj->user_id ? $user_login : $user_login;
		$dmsg_tags = $dmsg_im_row = $dmsg_bd_il = $location = $online_indicator = $avatar = '';
	}

	/* Display message body.
	 * If we have message threshold & the entirity of the post has been revelead show a
	 * preview otherwise if the message body exists show an actual body.
	 * If there is no body show a 'no-body' message.
	 */
	if (!$hide_controls && $obj->message_threshold && $obj->length_preview && $obj->length > $obj->message_threshold && !isset($GLOBALS['__FMDSP__'][$obj->id])) {
		$msg_body = '<span class="MsgBodyText">'.read_msg_body($obj->offset_preview, $obj->length_preview, $obj->file_id_preview).'</span>
...<br /><br /><div class="ac">[ <a href="index.php?'.make_reveal_link($obj->id).'">Показать остальное</a> ]</div>';
	} else if ($obj->length) {
		$msg_body = '<span class="MsgBodyText">'.read_msg_body($obj->foff, $obj->length, $obj->file_id).'</span>';
	} else {
		$msg_body = 'Нет текста сообщения';
	}

	/* Draw file attachments if there are any. */
	$drawmsg_file_attachments = '';
	if ($obj->attach_cnt && !empty($obj->attach_cache)) {
		$atch = unserialize($obj->attach_cache);
		if (!empty($atch)) {
			foreach ($atch as $v) {
				$sz = $v[2] / 1024;
				$drawmsg_file_attachments .= '<li><a href="index.php?t=getfile&amp;id='.$v[0].'&amp;'._rsid.'"><img alt="" src="images/mime/'.$v[4].'" class="at" /></a>
<span class="GenText fb">Вложение:</span> <a href="index.php?t=getfile&amp;id='.$v[0].'&amp;'._rsid.'">'.$v[1].'</a><br />
<span class="SmallText">(Размер: '.($sz < 1000 ? number_format($sz, 2).'KB' : number_format($sz/1024, 2).'MB').', Загружено '.convertPlural($v[3], array(''.$v[3].' раз',''.$v[3].' раза',''.$v[3].' раз')).')</span></li>';
			}
			$drawmsg_file_attachments = '<ul class="AttachmentsList">
'.$drawmsg_file_attachments.'
</ul>';
		}
		/* Append session to getfile. */
		if (_uid) {
			if ($o1 & 128 && !isset($_COOKIE[$GLOBALS['COOKIE_NAME']])) {
				$msg_body = str_replace('<img src="index.php?t=getfile', '<img src="index.php?t=getfile&amp;S='. s, $msg_body);
				$tap = 1;
			}
			if ($o2 & 32768 && (isset($tap) || $o2 & 8192)) {
				$pos = 0;
				while (($pos = strpos($msg_body, '<img src="index.php/fa/', $pos)) !== false) {
					$pos = strpos($msg_body, '"', $pos + 11);
					$msg_body = substr_replace($msg_body, _rsid, $pos, 0);
				}
			}
		}
	}

	if ($obj->poll_cache) {
		$obj->poll_cache = unserialize($obj->poll_cache);
	}

	/* Handle poll votes. */
	if (!empty($_POST['poll_opt']) && ($_POST['poll_opt'] = (int)$_POST['poll_opt']) && !($obj->thread_opt & 1) && $perms & 512) {
		if (register_vote($obj->poll_cache, $obj->poll_id, $_POST['poll_opt'], $obj->id)) {
			$obj->total_votes += 1;
			$obj->cant_vote = 1;
		}
		unset($_GET['poll_opt']);
	}

	/* Display poll if there is one. */
	if ($obj->poll_id && $obj->poll_cache) {
		/* We need to determine if we allow the user to vote or see poll results. */
		$show_res = 1;

		if (isset($_GET['pl_view']) && !isset($_POST['pl_view'])) {
			$_POST['pl_view'] = $_GET['pl_view'];
		}

		/* Various conditions that may prevent poll voting. */
		if (!$hide_controls && !$obj->cant_vote &&
			(!isset($_POST['pl_view']) || $_POST['pl_view'] != $obj->poll_id) &&
			($perms & 512 && (!($obj->thread_opt & 1) || $perms & 4096)) &&
			(!$obj->expiry_date || ($obj->creation_date + $obj->expiry_date) > __request_timestamp__) &&
			/* Check if the max # of poll votes was reached. */
			(!$obj->max_votes || $obj->total_votes < $obj->max_votes)
		) {
			$show_res = 0;
		}

		$i = 0;

		$poll_data = '';
		foreach ($obj->poll_cache as $k => $v) {
			++$i;
			if ($show_res) {
				$length = ($v[1] && $obj->total_votes) ? round($v[1] / $obj->total_votes * 100) : 0;
				$poll_data .= '<tr class="'.alt_var('msg_poll_alt_clr','RowStyleB','RowStyleA').'"><td>'.$i.'.</td><td>'.$v[0].'</td><td><img src="theme/velopiter/images/poll_pix.gif" alt="" height="10" width="'.$length.'" /> '.$v[1].' / '.$length.'%</td></tr>';
			} else {
				$poll_data .= '<tr class="'.alt_var('msg_poll_alt_clr','RowStyleB','RowStyleA').'"><td>'.$i.'.</td><td colspan="2"><label><input type="radio" name="poll_opt" value="'.$k.'" />&nbsp;&nbsp;'.$v[0].'</label></td></tr>';
			}
		}

		if (!$show_res) {
			$poll = '<br />
<form action="index.php?'.htmlspecialchars($_SERVER['QUERY_STRING']).'#msg_'.$obj->id.'" method="post">'._hs.'
<table cellspacing="1" cellpadding="2" class="PollTable">
<tr><th class="nw" colspan="3">'.$obj->poll_name.'<span class="ptp">[ '.$obj->total_votes.' '.convertPlural($obj->total_votes, array('голос','голоса','голосов')).' ]</span></th></tr>
'.$poll_data.'
<tr class="'.alt_var('msg_poll_alt_clr','RowStyleB','RowStyleA').' ar"><td colspan="3"><input type="submit" class="button" name="pl_vote" value="Проголосовать" />&nbsp;'.($obj->total_votes ? '<input type="submit" class="button" name="pl_res" value="Просмотр результатов" />' : '' )  .'</td></tr>
</table><input type="hidden" name="pl_view" value="'.$obj->poll_id.'" /></form><br />';
		} else {
			$poll = '<br /><table cellspacing="1" cellpadding="2" class="PollTable">
<tr><th class="nw" colspan="3">'.$obj->poll_name.'<span class="vt">[ '.$obj->total_votes.' '.convertPlural($obj->total_votes, array('голос','голоса','голосов')).' ]</span></th></tr>
'.$poll_data.'
</table><br />';
		}

		if (($p = strpos($msg_body, '{POLL}')) !== false) {
			$msg_body = substr_replace($msg_body, $poll, $p, 6);
		} else {
			$msg_body = $poll . $msg_body;
		}
	}

	/* Determine if the message was updated and if this needs to be shown. */
	if ($obj->update_stamp) {
		if ($obj->updated_by != $obj->poster_id && $o1 & 67108864) {
			$modified_message = '<br /><p class="fl">[Обновления: '.strftime("%a, %d %B %Y %H:%M", $obj->update_stamp).'] от Модератора</p>';
		} else if ($obj->updated_by == $obj->poster_id && $o1 & 33554432) {
			$modified_message = '<br /><p class="fl">[Обновления: '.strftime("%a, %d %B %Y %H:%M", $obj->update_stamp).']</p>';
		} else {
			$modified_message = '';
		}
	} else {
		$modified_message = '';
	}

	if ($_GET['t'] != 'tree' && $_GET['t'] != 'msg') {
		$lnk = d_thread_view;
	} else {
		$lnk =& $_GET['t'];
	}

	$rpl = '';
	if (!$hide_controls) {

		/* Show reply links, eg: [message #1 is a reply to message #2]. */
		if ($o2 & 536870912) {
			if ($obj->reply_to && $obj->reply_to != $obj->id) {
				$rpl = '<span class="SmallText">[<a href="index.php?t='.$lnk.'&amp;th='.$obj->thread_id.'&amp;goto='.$obj->id.'&amp;'._rsid.'#msg_'.$obj->id.'">сообщение #'.$obj->id.'</a> является ответом на <a href="index.php?t='.$lnk.'&amp;th='.$obj->thread_id.'&amp;goto='.$obj->reply_to.'&amp;'._rsid.'#msg_'.$obj->reply_to.'">сообщение #'.$obj->reply_to.'</a>]</span>';
			} else {
				$rpl = '<span class="SmallText">[<a href="index.php?t='.$lnk.'&amp;th='.$obj->thread_id.'&amp;goto='.$obj->id.'&amp;'._rsid.'#msg_'.$obj->id.'">сообщение #'.$obj->id.'</a>]</span>';
			}
		}

		/* Little trick, this variable will only be available if we have a next link leading to another page. */
		if (empty($next_page)) {
			$next_page = '&nbsp;';
		}

		if (_uid && ($perms & 16 || (_uid == $obj->poster_id && (!$GLOBALS['EDIT_TIME_LIMIT'] || __request_timestamp__ - $obj->post_stamp < $GLOBALS['EDIT_TIME_LIMIT'] * 60)))) {
			$edit_link = '<a href="index.php?t=post&amp;msg_id='.$obj->id.'&amp;'._rsid.'"><img alt="edit" src="theme/velopiter/images/msg_edit.gif" /></a>&nbsp;&nbsp;&nbsp;&nbsp;';
		} else {
			$edit_link = '';
		}

		if (!($obj->thread_opt & 1) || $perms & 4096) {
			$reply_link = '<a href="index.php?t=post&amp;reply_to='.$obj->id.'&amp;'._rsid.'"><img alt="reply" src="theme/velopiter/images/msg_reply.gif" /></a>&nbsp;';
			$quote_link = '<a href="index.php?t=post&amp;reply_to='.$obj->id.'&amp;quote=true&amp;'._rsid.'"><img alt="quote" src="theme/velopiter/images/msg_quote.gif" /></a>';
		} else {
			$reply_link = $quote_link = '';
		}
	}

	return '<tr><td class="MsgSpacer"><table cellspacing="0" cellpadding="0" class="MsgTable">
<tr>
<td colspan="2" class="MsgR1"><table cellspacing="0" cellpadding="0" class="ContentTable"><tr><td class="MsgR1 vt al MsgSubText"><a name="msg_num_'.$m_num.'"></a><a name="msg_'.$obj->id.'"></a>'.($obj->icon && !$hide_controls ? '<img src="images/message_icons/'.$obj->icon.'" alt="'.$obj->icon.'" />&nbsp;&nbsp;' : '' )  .$obj->subject.$rpl.'</td>
<td class="MsgR1 vt ar"><span class="DateText">'.strftime("%a, %d %B %Y %H:%M", $obj->post_stamp).'</span> '.$prev_message.$next_message.'</td></tr></table></td></tr>

<tr class="MsgR2">
<td class="MsgR2" width="15%" valign="top">
<table cellspacing="0" cellpadding="0" class="ContentTable"><tr class="MsgR2">'.$online_indicator.$user_link.(!$hide_controls ? ($obj->user_id ? '<br />'.$avatar.'<tr class="MsgR2"><td class="msgud">'.$dmsg_tags.'</td></tr><tr class="MsgR2"> <td class="msgud">Сообщений:'.$obj->posted_msg_count.'<br />
Зарегистрирован:'.strftime("%B %Y", $obj->join_date).' '.$location : '' )   : '' )  .'</td></tr><tr class="MsgR2"><td class="msgud">'.$dmsg_bd_il.$dmsg_im_row.(!$hide_controls ? (($obj->host_name && $o1 & 268435456) ? 'От:'.$obj->host_name.'<br />' : '' )  .(($b & 1048576 || $usr->md || $o1 & 134217728) ? 'IP: <a href="index.php?t=ip&amp;ip='.$obj->ip_addr.'&amp;'._rsid.'" target="_blank">'.$obj->ip_addr.'</a>' : '' )   : '' )  .'</td></tr></table></td>

<td class="MsgR3" width="85%" valign="top">'.$msg_body.$drawmsg_file_attachments.'
'.$modified_message.(!$hide_controls ? (($obj->sig && $o1 & 32768 && $obj->msg_opt & 1 && $b & 4096 && !($a & 67108864)) ? '<br /><br /><hr class="sig" />'.$obj->sig : '' )  .'<p class="fr"><a href="index.php?t=report&amp;msg_id='.$obj->id.'&amp;'._rsid.'" rel="nofollow">Известить модератора</a></p>' : '' )  .'
</td></tr>
'.(!$hide_controls ? '<tr><td colspan="2" class="MsgToolBar"><table border="0" cellspacing="0" cellpadding="0" class="wa"><tr>
<td class="al nw">'.($obj->user_id ? '<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'"><img alt="" src="theme/velopiter/images/msg_about.gif" /></a>&nbsp;'.(($o1 & 4194304 && $a & 16) ? '<a href="index.php?t=email&amp;toi='.$obj->user_id.'&amp;'._rsid.'" rel="nofollow"><img alt="" src="theme/velopiter/images/msg_email.gif" /></a>&nbsp;' : '' )  .($o1 & 1024 ? '<a href="index.php?t=ppost&amp;toi='.$obj->user_id.'&amp;rmid='.$obj->id.'&amp;'._rsid.'"><img alt="Отправить личное сообщение этому участнику" title="Отправить личное сообщение этому участнику" src="theme/velopiter/images/msg_pm.gif" /></a>' : '' )   : '' )  .'</td>
<td class="GenText wa ac">'.$next_page.'</td>
<td class="nw ar">'.($perms & 32 ? '<a href="index.php?t=mmod&amp;del='.$obj->id.'&amp;'._rsid.'"><img alt="" src="theme/velopiter/images/msg_delete.gif" /></a>&nbsp;' : '' )  .$edit_link.$reply_link.$quote_link.'</td>
</tr></table></td></tr>' : '' )  .'
</table></td></tr>';
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
}

function path_info_lnk($var, $val)
{
	$a = $_GET;
	unset($a['rid'], $a['S'], $a['t']);
	if (isset($a[$var])) {
		unset($a[$var]);
		$rm = 1;
	}
	$url = '/sel';

	foreach ($a as $k => $v) {
		$url .= '/'. $k .'/'. $v;
	}
	if (!isset($rm)) {
		$url .= '/'. $var .'/'. $val;
	}

	return htmlspecialchars($url, ENT_QUOTES) .'/'. _rsid;
}

	ses_update_status($usr->sid, 'Просмотр <a href="index.php?t=selmsg&amp;date=today&amp;rid='.$usr->id.'">сегодняшних сообщений</a>');

	$count = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;
	if (!isset($_GET['start']) || !($start = (int)$_GET['start'])) {
		$start = 0;
	}

	/* Limited to today. */
	if (isset($_GET['date'])) {
		if ($_GET['date'] != 'today') {
			$tm = __request_timestamp__ - ((int)$_GET['date'] - 1) * 86400;
		} else {
			$tm = __request_timestamp__;
		}
		$dt = getdate($tm);
		$tm_today_start = mktime(0, 0, 0, $dt['mon'], $dt['mday'], $dt['year']);
		$tm_today_end = $tm_today_start + 86400;
		$date_limit = ' AND m.post_stamp>'. $tm_today_start .' AND m.post_stamp<'. $tm_today_end .' ';
	} else {
		$date_limit = '';
	}
	if (!_uid) { /* These options are restricted to registered users. */
		unset($_GET['sub_forum_limit'], $_GET['sub_th_limit'], $_GET['unread']);
	}

	$unread_limit = (isset($_GET['unread']) && _uid) ? ' AND m.post_stamp > '. $usr->last_read .' AND (r.id IS NULL OR r.last_view < m.post_stamp) ' : '';
	$th = isset($_GET['th']) ? (int)$_GET['th'] : 0;
	$frm_id = isset($_GET['frm_id']) ? (int)$_GET['frm_id'] : 0;
	$perm_limit = $is_a ? '' : ' AND (mm.id IS NOT NULL OR ('. q_bitand(_uid ? 'COALESCE(g2.group_cache_opt, g1.group_cache_opt)' : '(g1.group_cache_opt)', 2) .') > 0)';

	/* Mark messages read for registered users. */
	if (_uid && isset($_GET['mr']) && !empty($usr->data)) {
		foreach ($usr->data as $ti => $mi) {
			if (!(int)$ti || !(int)$mi) {
				break;
			}
			user_register_thread_view($ti, __request_timestamp__, $mi);
		}
	}
	ses_putvar((int)$usr->sid, null);

	/* No other limiters are present, assume 'today' limit. */
	if (!$unread_limit && !isset($_GET['date']) && !isset($_GET['reply_count'])) {
		$_GET['date'] = 'today';
		$dt = getdate(__request_timestamp__);
		$tm_today_start =  mktime(0, 0, 0, $dt['mon'], $dt['mday'], $dt['year']);
		$tm_today_end = $tm_today_start + 86400;
		$date_limit = ' AND m.post_stamp>'. $tm_today_start .' AND m.post_stamp<'. $tm_today_end .' ';
	}

	$_SERVER['QUERY_STRING'] = htmlspecialchars($_SERVER['QUERY_STRING'], ENT_QUOTES);

	/* Date limit. */
	if ($FUD_OPT_2 & 32768) {
		$dt_opt = path_info_lnk('date', '1');
		$rp_opt = path_info_lnk('reply_count', '0');
	} else {
		$dt_opt = isset($_GET['date']) ? str_replace('&amp;date='.$_GET['date'], '', $_SERVER['QUERY_STRING']) : $_SERVER['QUERY_STRING'] .'&amp;date=1';
		$rp_opt = isset($_GET['reply_count']) ? str_replace('&amp;reply_count='. (int)$_GET['reply_count'], '', $_SERVER['QUERY_STRING']) : $_SERVER['QUERY_STRING'] .'&amp;reply_count=0';
	}

	if (_uid) {
		if ($FUD_OPT_2 & 32768) {
			$un_opt = path_info_lnk('unread', '1');
			$frm_opt = path_info_lnk('sub_forum_limit', '1');
			$th_opt =path_info_lnk('sub_th_limit', '1');
		} else {
			$un_opt = isset($_GET['unread']) ? str_replace('&amp;unread='. $_GET['unread'], '', $_SERVER['QUERY_STRING']) : $_SERVER['QUERY_STRING'] .'&amp;unread=1';
			$frm_opt = isset($_GET['sub_forum_limit']) ? str_replace('&amp;sub_forum_limit='. $_GET['sub_forum_limit'], '', $_SERVER['QUERY_STRING']) : $_SERVER['QUERY_STRING'] .'&amp;sub_forum_limit=1';
			$th_opt = isset($_GET['sub_th_limit']) ? str_replace('&amp;sub_th_limit='. $_GET['sub_th_limit'], '', $_SERVER['QUERY_STRING']) : $_SERVER['QUERY_STRING'] .'&amp;sub_th_limit=1';
		}
	}

	make_perms_query($fields, $join);

	if (!$unread_limit) {
		$total = (int) q_singleval('SELECT count(*) FROM fud26_msg m INNER JOIN fud26_thread t ON m.thread_id=t.id INNER JOIN fud26_forum f ON t.forum_id=f.id INNER JOIN fud26_cat c ON f.cat_id=c.id '. (isset($_GET['sub_forum_limit']) ? 'INNER JOIN fud26_forum_notify fn ON fn.forum_id=f.id AND fn.user_id='. _uid : '') .' '. (isset($_GET['sub_th_limit']) ? 'INNER JOIN fud26_thread_notify tn ON tn.thread_id=t.id AND tn.user_id='. _uid : '') .' '. $join .' LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .' WHERE m.apr=1 '. $date_limit .' '. ($frm_id ? ' AND f.id='. $frm_id : '') .' '. ($th ? ' AND t.id='. $th : '') .' '. (isset($_GET['reply_count']) ? ' AND t.replies='. (int)$_GET['reply_count'] : '') .' '. $perm_limit);
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
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/velopiter/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/velopiter/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}

	if ($unread_limit || $total) {
		$ord = isset($_GET['reply_count']) ? ' DESC ' : ' ASC ';

		/* figure out the query */
		$c = q(q_limit('SELECT
			m.*, COALESCE(m.flag_cc, u.flag_cc) AS disp_flag_cc, COALESCE(m.flag_country, u.flag_country) AS disp_flag_country,
			t.thread_opt, t.root_msg_id, t.last_post_id, t.forum_id,
			f.message_threshold, f.name,
			u.id AS user_id, u.alias AS login, u.avatar_loc, u.email, u.posted_msg_count, u.join_date, u.location,
			u.sig, u.custom_status, u.icq, u.jabber, u.affero, u.aim, u.msnm, u.yahoo, u.skype, u.google, u.twitter, u.last_visit AS time_sec, u.users_opt,
			l.name AS level_name, l.level_opt, l.img AS level_img,
			p.max_votes, p.expiry_date, p.creation_date, p.name AS poll_name, p.total_votes,
			pot.id AS cant_vote,
			r.last_view,
			mm.id AS md,
			m2.subject AS thr_subject,
			'. $fields .'
		FROM
			fud26_msg m
			INNER JOIN fud26_thread t ON m.thread_id=t.id
			INNER JOIN fud26_msg m2 ON m2.id=t.root_msg_id
			INNER JOIN fud26_forum f ON t.forum_id=f.id
			INNER JOIN fud26_cat c ON f.cat_id=c.id
			'. (isset($_GET['sub_forum_limit']) ? 'INNER JOIN fud26_forum_notify fn ON fn.forum_id=f.id AND fn.user_id='. _uid : '') .'
			'. (isset($_GET['sub_th_limit']) ? 'INNER JOIN fud26_thread_notify tn ON tn.thread_id=t.id AND tn.user_id='. _uid : '') .'
			'. $join .'
			LEFT JOIN fud26_read r ON r.thread_id=t.id AND r.user_id='. _uid .'
			LEFT JOIN fud26_users u ON m.poster_id=u.id
			LEFT JOIN fud26_level l ON u.level_id=l.id
			LEFT JOIN fud26_poll p ON m.poll_id=p.id
			LEFT JOIN fud26_poll_opt_track pot ON pot.poll_id=p.id AND pot.user_id='. _uid .'
			LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. _uid .'
		WHERE
			m.apr=1
			'. $date_limit .'
			'. ($frm_id ? ' AND f.id='. $frm_id : '') .'
			'. ($th ? ' AND t.id='. $th : '') .'
			'. (isset($_GET['reply_count']) ? ' AND t.replies='. (int)$_GET['reply_count'] : '') .'
			'. $unread_limit .'
			'. $perm_limit .'
		ORDER BY
			f.last_post_id '. $ord .', t.last_post_date '. $ord .', m.post_stamp '. $ord,
		$count, $start));

		/* Message drawing code. */
		$message_data = $n = $prev_frm = $prev_th = '';
		$thl = $mark_read = array();
		while ($r = db_rowobj($c)) {
			if ($prev_frm != $r->forum_id) {
				$prev_frm = $r->forum_id;
				$message_data .= '<tr><th class="SelFS">Форум: <a class="thLnk" href="index.php?t='.t_thread_view.'&amp;frm_id='.$r->forum_id.'&amp;'._rsid.'"><span class="lg">'.$r->name.'</span></a></th></tr>';
				$perms = perms_from_obj($r, $is_a);
			}
			if ($prev_th != $r->thread_id) {
				$thl[] = $r->thread_id;
				$prev_th = $r->thread_id;
				$message_data .= '<tr><th class="SelTS">&nbsp;Тема: <a class="thLnk" href="index.php?t='.d_thread_view.'&amp;goto='.$r->id.'&amp;'._rsid.'#msg_'.$r->id.'">'.$r->thr_subject.'</a></th></tr>';
			}
			if (_uid && $r->last_view < $r->post_stamp && $r->post_stamp > $usr->last_read && !isset($mark_read[$r->thread_id])) {
				$mark_read[$r->thread_id] = $r->id;
			}
			$usr->md = $r->md;
			$message_data .= tmpl_drawmsg($r, $usr, $perms, false, $n, '');
		}
		unset($c);

		if ($thl) {
			q('UPDATE fud26_thread SET views=views+1 WHERE id IN('. implode(',', $thl) .')');
		}

		if (_uid && $mark_read) {
			ses_putvar((int)$usr->sid, $mark_read);
		}
	} else {
		$message_data = '';
	}

	if (!$unread_limit && $total > $count) {
		if (!isset($_GET['mr'])) {
			if ($FUD_OPT_2 & 32768 && isset($_SERVER['PATH_INFO'])) {
				$_SERVER['PATH_INFO'] .= 'mr/1/';
			} else {
				$_SERVER['QUERY_STRING'] .= '&mr=1';
			}
		}
		if ($FUD_OPT_2 & 32768 && isset($_SERVER['PATH_INFO'])) {
			$p = htmlspecialchars(str_replace(_rsid, '', $_SERVER['PATH_INFO']), ENT_QUOTES);
			if (strpos($p, 'start/') !== false) {
				$p = preg_replace('!start/[0-9]+/!', '', $p);
			}
			$pager = tmpl_create_pager($start, $count, $total, 'index.php'. $p .'start/', '/'. _rsid);
		} else {
			$pager = tmpl_create_pager($start, $count, $total, 'index.php?'. str_replace('&amp;start='. $start, '', $_SERVER['QUERY_STRING']));
		}
	} else if ($unread_limit) {
		if (!isset($_GET['mark_page_read'])) {
			if ($FUD_OPT_2 & 32768) {
				$_SERVER['QUERY_STRING'] = htmlspecialchars(str_replace(_rsid, '', $_SERVER['PATH_INFO']), ENT_QUOTES) .'make_page_read/1/mr/1/'. _rsid;
			} else {
				$_SERVER['QUERY_STRING'] .= '&amp;mark_page_read=1&amp;mr=1';
			}
		}
		$pager = $message_data ? '<div class="GenText ac">[<a href="index.php?'.$_SERVER['QUERY_STRING'].'" title="Показать другие непрочитанные сообщения и пометить текущее как прочитанное">другие непрочитанные сообщения</a>]</div><img src="blank.gif" alt="" height="3" />' : '';
	} else {
		$pager = '';
	}

	if (!$message_data) {
		if (isset($_GET['unread'])) {
			$message_data = '<tr><th class="ac">Не найдено соответствующих вашему запросу непрочитанных сообщений.</th></tr>';
			if (!$frm_id && !$th) {
				user_mark_all_read(_uid);
			} else if ($frm_id) {
				user_mark_forum_read(_uid, $frm_id, $usr->last_read);
			}
		} else {
			$message_data = '<tr><th align="center">Нет сообщений</th></tr>';
		}
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
<a href="index.php?<?php echo $dt_opt; ?>" title="Переключение показа только сегодняшних сообщений">Сегодняшние сообщения <?php echo (isset($_GET['date']) ? '<span class="selmsgInd">(<span class="GenTextRed">вкл</span>)</span>' : '<span class="selmsgInd">(откл)</span>' )  .'</a>
'.(_uid ? '&nbsp;| <a href="index.php?'.$un_opt.'" title="Переключение показа только непрочитанных сообщений">Непрочитанные сообщения '.(isset($_GET['unread']) ? '<span class="selmsgInd">(<span class="GenTextRed">вкл</span>)</span>' : '<span class="selmsgInd">(откл)</span>' )  .'</a>' : ''); ?>
<?php echo (_uid ? '&nbsp;| <a href="index.php?'.$frm_opt.'" title="Переключение показа только сообщений из форумов, на которые вы подписаны">Подписанные форумы '.(isset($_GET['sub_forum_limit']) ? '<span class="selmsgInd">(<span class="GenTextRed">вкл</span>)</span>' : '<span class="selmsgInd">(откл)</span>' )  .'</a>' : ''); ?>
<?php echo (_uid ? '&nbsp;| <a href="index.php?'.$th_opt.'" title="Переключение показа только сообщений и тем, на которые вы подписаны">Подписанные темы '.(isset($_GET['sub_th_limit']) ? '<span class="selmsgInd">(<span class="GenTextRed">вкл</span>)</span>' : '<span class="selmsgInd">(откл)</span>' )  .'</a>' : ''); ?>
&nbsp;| <a href="index.php?<?php echo $rp_opt; ?>" title="Переключение показа только оставленных без ответа сообщений">Сообщения без ответа <?php echo (isset($_GET['reply_count']) ? '<span class="selmsgInd">(<span class="GenTextRed">вкл</span>)</span>' : '<span class="selmsgInd">(откл)</span>'); ?></a>
<br /><?php echo $admin_cp; ?><br />
<table cellspacing="0" cellpadding="0" class="ContentTable"><?php echo $message_data; ?></table>
<?php echo $pager; ?>
<br /><br />
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<script type="text/javascript">
/* <![CDATA[ */
min_max_posts("theme/velopiter/images", "<?php echo img_ext; ?>", "Минимайз", "Максимайз");
format_code('CODE:', 'Выделить все', 'Показать / скрыть');
/* ]]> */
</script>
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
