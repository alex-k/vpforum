<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: pmsg_view.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}$folders = array(1=>'Входящие', 2=>'Сохранено', 4=>'Черновики', 3=>'Отправленные', 5=>'Корзина');

function tmpl_cur_ppage($folder_id, $folders, $msg_subject='')
{
	if (!$folder_id || (!$msg_subject && $_GET['t'] == 'ppost')) {
		$user_action = 'Создание личного письма';
	} else {
		$user_action = $msg_subject ? '<a href="index.php?t=pmsg&amp;folder_id='.$folder_id.'&amp;'._rsid.'">'.$folders[$folder_id].'</a> &raquo; '.$msg_subject : 'Просмотр папки <b>'.$folders[$folder_id].'</b>';
	}

	return '<span class="GenText"><a href="index.php?t=pmsg&amp;'._rsid.'">Личная почта</a>&nbsp;&raquo;&nbsp;'.$user_action.'</span><br /><img src="blank.gif" alt="" height="4" width="1" /><br />';
}$GLOBALS['affero_domain'] = parse_url($GLOBALS['WWW_ROOT']);

function tmpl_drawpmsg($obj, $usr, $mini)
{
	$o1 =& $GLOBALS['FUD_OPT_1'];
	$o2 =& $GLOBALS['FUD_OPT_2'];
	$a = (int) $obj->users_opt;
	$b =& $usr->users_opt;

	if (!$mini) {
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
			$obj->login = $obj->alias;
			$online_indicator = (($obj->last_visit + $GLOBALS['LOGEDIN_TIMEOUT'] * 60) > __request_timestamp__) ? '<img src="theme/velopiter/images/online'.img_ext.'" alt="онлайн" title="онлайн" />' : '<img src="theme/velopiter/images/offline'.img_ext.'" alt="оффлайн" title="оффлайн" />';
		} else {
			$online_indicator = '';
		}

		if ($obj->location) {
			if (strlen($obj->location) > $GLOBALS['MAX_LOCATION_SHOW']) {
				$location = substr($obj->location, 0, $GLOBALS['MAX_LOCATION_SHOW']) .'...';
			} else {
				$location = $obj->location;
			}
			$location = '<br /><b>Город:</b> '.$location;
		} else {
			$location = '';
		}
		$usr->buddy_list = $usr->buddy_list ? unserialize($usr->buddy_list) : array();
		if ($obj->user_id != _uid && $obj->user_id > 0) {
			$buddy_link = !isset($usr->buddy_list[$obj->user_id]) ? '<a href="index.php?t=buddy_list&amp;'._rsid.'&amp;add='.$obj->user_id.'&amp;SQ='.$GLOBALS['sq'].'">в контакты</a><br />' : '<br />[<a href="index.php?t=buddy_list&amp;del='.$obj->user_id.'&amp;redr=1&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">удалить из контактов</a>]';
		} else {
			$buddy_link = '';
		}
		/* Show im buttons if need be. */
		if ($b & 16384) {
			$im = '';
			if ($obj->icq) {
				$im .= '<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'#icq_msg"><img src="theme/velopiter/images/icq'.img_ext.'" alt="" title="'.$obj->icq.'" /></a>&nbsp;';
			}
			if ($obj->aim) {
				$im .= '<a href="aim:goim?screenname='.$obj->aim.'&amp;message=Hi.+Are+you+there?"><img src="theme/velopiter/images/aim'.img_ext.'" title="'.$obj->aim.'" alt="" /></a>&nbsp;';
			}
			if ($obj->yahoo) {
				$im .= '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.$obj->yahoo.'&amp;.src=pg"><img src="theme/velopiter/images/yahoo'.img_ext.'" alt="" title="'.$obj->yahoo.'" /></a>&nbsp;';
			}
			if ($obj->msnm) {
				$im .= '<a href="mailto:'.$obj->msnm.'"><img src="theme/velopiter/images/msnm'.img_ext.'" title="'.$obj->msnm.'" alt="" /></a>';
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
					$im .= '<a href="http://svcs.affero.net/rm.php?r='.$obj->affero.'&amp;ll=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;lp=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;ls='.urlencode($obj->subject).'" target=_blank><img alt="" src="theme/velopiter/images/affero_reg.gif" /></a>';
				} else {
					$im .= '<a href="http://svcs.affero.net/rm.php?m='.urlencode($obj->email).'&amp;ll=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;lp=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;ls='.urlencode($obj->subject).'" target=_blank><img alt="" src="theme/velopiter/images/affero_noreg.gif" /></a>';
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
		if ($obj->ouser_id != _uid) {
			$user_profile = '<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_about.gif" alt="" /></a>';
			$email_link = ($o1 & 4194304 && $a & 16) ? '<a href="index.php?t=email&amp;toi='.$obj->user_id.'&amp;'._rsid.'" rel="nofollow"><img src="theme/velopiter/images/msg_email.gif" alt="" /></a>' : '';
			$private_msg_link = '<a href="index.php?t=ppost&amp;toi='.$obj->user_id.'&amp;'._rsid.'"><img title="Отправить личное сообщение этому участнику" src="theme/velopiter/images/msg_pm.gif" alt="" /></a>';
		} else {
			$user_profile = $email_link = $private_msg_link = '';
		}
		$msg_toolbar = '<tr><td colspan="2" class="MsgToolBar"><table border="0" cellspacing="0" cellpadding="0" class="wa"><tr>
<td class="nw al">'.$user_profile.'&nbsp;'.$email_link.'&nbsp;'.$private_msg_link.'</td>
<td class="nw ar"><a href="index.php?t=pmsg&amp;'._rsid.'&amp;btn_delete=1&amp;sel='.$obj->id.'&amp;SQ='.$GLOBALS['sq'].'"><img src="theme/velopiter/images/msg_delete.gif" alt="delete" /></a>&nbsp;'.($obj->fldr == 4 ? '<a href="index.php?t=ppost&amp;msg_id='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_edit.gif" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;' : '' )  .($obj->fldr == 1 ? '<a href="index.php?t=ppost&amp;reply='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_reply.gif" alt="reply" /></a>&nbsp;<a href="index.php?t=ppost&amp;quote='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_quote.gif" alt="quote" /></a>&nbsp;' : '' )  .'<a href="index.php?t=ppost&amp;forward='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_forward.gif" alt="forward" /></a></td>
</tr></table></td></tr>';
	} else {
		$dmsg_tags = $dmsg_im_row = $user_profile = $msg_toolbar = $buddy_link = $avatar = $online_indicator = $host_name = $location = '';
	}
	if ($obj->length > 0) {
		$msg_body = read_pmsg_body($obj->foff, $obj->length);
	} else {
		$msg_body = 'Нет текста сообщения';
	}

	$msg_body = $obj->length ? read_pmsg_body($obj->foff, $obj->length) : 'Нет текста сообщения';

	$file_attachments = '';
	if ($obj->attach_cnt) {
		$c = uq('SELECT a.id, a.original_name, a.dlcount, m.icon, a.fsize FROM fud26_attach a LEFT JOIN fud26_mime m ON a.mime_type=m.id WHERE a.message_id='. $obj->id .' AND attach_opt=1');
		while ($r = db_rowobj($c)) {
			$sz = $r->fsize/1024;
			$sz = $sz<1000 ? number_format($sz, 2) .'KB' : number_format($sz / 1024 ,2) .'MB';
			if(!$r->icon) {
				$r->icon = 'unknown.gif';
			}
			$file_attachments .= '<li />
<a href="index.php?t=getfile&amp;id='.$r->id.'&amp;'._rsid.'&amp;private=1"><img src="images/mime/'.$r->icon.'" class="at" alt="" /></a>
<span class="GenText fb">Вложение:</span> <a href="index.php?t=getfile&amp;id='.$r->id.'&amp;'._rsid.'&amp;private=1">'.$r->original_name.'</a><br />
<span class="SmallText">(Размер: '.$sz.', Загружено '.convertPlural($r->dlcount, array(''.$r->dlcount.' раз',''.$r->dlcount.' раза',''.$r->dlcount.' раз')).')<br /><br /></span>';
		}
		unset($c);
		if ($file_attachments) {
			$file_attachments = '<ul class="AttachmentsList">
'.$file_attachments.'
</ul>';
			/* Append session to getfile. */
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

	return '<tr><td><table cellspacing="0" cellpadding="0" class="MsgTable">
<tr>
<td colspan="2">
  <table cellspacing="0" cellpadding="0" class="ContentTable"><tr>
  <td class="MsgR1 vt al MsgSubText">'.(!$mini && $obj->icon ? '<img src="images/message_icons/'.$obj->icon.'" alt="" />&nbsp;&nbsp;' : '' )  .$obj->subject.'</td>
  <td class="MsgR1 vt ar DateText">'.strftime("%a, %d %B %Y %H:%M", $obj->post_stamp).'</td>
  </tr></table>
</tr>

<tr class="MsgR2">
<td class="MsgR2" width="15%" valign="top">
<table cellspacing="0" cellpadding="0" class="ContentTable"><tr class="MsgR2">'.$online_indicator.'<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'">'.$obj->alias.'</a>'.(!$mini ? ($obj->user_id ? '<br />'.$avatar.'<tr class="MsgR2"><td class="msgud">'.$dmsg_tags.'</td></tr><tr class="MsgR2"> <td class="msgud">Сообщений:'.$obj->posted_msg_count.'<br />
Зарегистрирован:'.strftime("%B %Y", $obj->join_date).' '.$location : '' )   : '' )  .'<br />'.$buddy_link.'<br />'.(($b & 1048576 || $usr->md || $o1 & 134217728) ? 'IP: <a href="index.php?t=ip&amp;ip='.$obj->ip_addr.'&amp;'._rsid.'" target="_blank">'.$obj->ip_addr.'</a>' : '' )  .'</td></tr><tr class="MsgR2"><td class="msgud">'.$dmsg_im_row.'</td></tr></table></td>

<td class="MsgR3" width="85%" valign="top">'.$msg_body.$file_attachments.(($obj->sig && $o1 & 32768 && $obj->pmsg_opt & 1 && $b & 4096) ? '<br /><br /><hr class="sig" />'.$obj->sig : '' )  .'</td></tr>'.$msg_toolbar.'
<tr><td class="MsgR2 ac" colspan="2">'.$GLOBALS['dpmsg_prev_message'].' '.$GLOBALS['dpmsg_next_message'].'</td></tr>
</table></td></tr>';
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
}$GLOBALS['recv_user_id'] = array();

class fud_pmsg
{
	var	$id, $to_list, $ouser_id, $duser_id, $pdest, $ip_addr, $host_name, $post_stamp, $icon, $fldr,
		$subject, $attach_cnt, $pmsg_opt, $length, $foff, $login, $ref_msg_id, $body;

	function add($track='')
	{
		$this->post_stamp = __request_timestamp__;
		$this->ip_addr = get_ip();
		$this->host_name = $GLOBALS['FUD_OPT_1'] & 268435456 ? _esc(get_host($this->ip_addr)) : 'NULL';

		if ($this->fldr != 1) {
			$this->read_stamp = $this->post_stamp;
		}

		if ($GLOBALS['FUD_OPT_3'] & 32768) {
			$this->foff = $this->length = -1;
		} else {
			list($this->foff, $this->length) = write_pmsg_body($this->body);
		}

		$this->id = db_qid('INSERT INTO fud26_pmsg (
			ouser_id,
			duser_id,
			pdest,
			to_list,
			ip_addr,
			host_name,
			post_stamp,
			icon,
			fldr,
			subject,
			attach_cnt,
			read_stamp,
			ref_msg_id,
			foff,
			length,
			pmsg_opt
			) VALUES(
				'. $this->ouser_id .',
				'. ($this->duser_id ? $this->duser_id : $this->ouser_id) .',
				'. (isset($GLOBALS['recv_user_id'][0]) ? (int)$GLOBALS['recv_user_id'][0] : '0') .',
				'. ssn($this->to_list) .',
				\''. $this->ip_addr .'\',
				'. $this->host_name .',
				'. $this->post_stamp .',
				'. ssn($this->icon) .',
				'. $this->fldr .',
				'. _esc($this->subject) .',
				'. (int)$this->attach_cnt .',
				'. $this->read_stamp .',
				'. ssn($this->ref_msg_id) .',
				'. (int)$this->foff .',
				'. (int)$this->length .',
				'. $this->pmsg_opt .'
			)');

		if ($GLOBALS['FUD_OPT_3'] & 32768 && $this->body) {
			$fid = db_qid('INSERT INTO fud26_msg_store (data) VALUES('. _esc($this->body) .')');
			q('UPDATE fud26_pmsg SET length='. $fid .' WHERE id='. $this->id);
		}

		if ($this->fldr == 3 && !$track) {
			$this->send_pmsg();
		}
	}

	function send_pmsg()
	{
		$this->pmsg_opt |= 16|32;
		$this->pmsg_opt &= 16|32|1|2|4;

		foreach($GLOBALS['recv_user_id'] as $v) {
			$id = db_qid('INSERT INTO fud26_pmsg (
				to_list,
				ouser_id,
				ip_addr,
				host_name,
				post_stamp,
				icon,
				fldr,
				subject,
				attach_cnt,
				foff,
				length,
				duser_id,
				ref_msg_id,
				pmsg_opt
			) VALUES (
				'. ssn($this->to_list).',
				'. $this->ouser_id .',
				\''. $this->ip_addr .'\',
				'. $this->host_name .',
				'. $this->post_stamp .',
				'. ssn($this->icon) .',
				1,
				'. _esc($this->subject) .',
				'. (int)$this->attach_cnt .',
				'. $this->foff .',
				'. $this->length .',
				'. $v .',
				'. ssn($this->ref_msg_id) .',
				'. $this->pmsg_opt .')');

			if ($GLOBALS['FUD_OPT_3'] & 32768 && $this->body) {
				$fid = db_qid('INSERT INTO fud26_msg_store (data) VALUES('. _esc($this->body) .')');
				q('UPDATE fud26_pmsg SET length='. $fid .' WHERE id='. $id);
			}

			$GLOBALS['send_to_array'][] = array($v, $id);
			$um[$v] = $id;
		}
		$c =  uq('SELECT id, email FROM fud26_users WHERE id IN('. implode(',', $GLOBALS['recv_user_id']) .') AND users_opt>=64 AND '. q_bitand('users_opt', 64) .' > 0');

		$from = reverse_fmt($GLOBALS['usr']->alias);
		$subject = reverse_fmt($this->subject);

		while ($r = db_rowarr($c)) {
			/* Do not send notifications about messages sent to self. */
			if ($r[0] == $this->ouser_id) {
				continue;
			}
			send_pm_notification($r[1], $um[$r[0]], $subject, $from);
		}
		unset($c);
	}

	function sync()
	{
		$this->post_stamp = __request_timestamp__;
		$this->ip_addr = get_ip();
		$this->host_name = $GLOBALS['FUD_OPT_1'] & 268435456 ? _esc(get_host($this->ip_addr)) : 'NULL';

		if ($GLOBALS['FUD_OPT_3'] & 32768) {
			if ($fid = q_singleval('SELECT length FROM fud26_pmsg WHERE id='. $this->id .' AND foff!=-1')) {
				q('DELETE FROM fud26_msg_store WHERE id='. $this->length);
			}
			$this->foff = $this->length = -1;
		} else {
			list($this->foff, $this->length) = write_pmsg_body($this->body);
		}

		q('UPDATE fud26_pmsg SET
			to_list='. ssn($this->to_list) .',
			icon='. ssn($this->icon) .',
			ouser_id='. $this->ouser_id .',
			duser_id='. $this->ouser_id .',
			post_stamp='. $this->post_stamp .',
			subject='. _esc($this->subject) .',
			ip_addr=\''. $this->ip_addr .'\',
			host_name='. $this->host_name .',
			attach_cnt='. (int)$this->attach_cnt .',
			fldr='. $this->fldr .',
			foff='. (int)$this->foff .',
			length='. (int)$this->length .',
			pmsg_opt='. $this->pmsg_opt .'
		WHERE id='. $this->id);

		if ($GLOBALS['FUD_OPT_3'] & 32768 && $this->body) {
			$fid = db_qid('INSERT INTO fud26_msg_store (data) VALUES('. _esc($this->body) .')');
			q('UPDATE fud26_pmsg SET length='. $fid .' WHERE id='. $this->id);
		}

		if ($this->fldr == 3) {
			$this->send_pmsg();
		}
	}
}

function set_nrf($nrf, $id)
{
	q('UPDATE fud26_pmsg SET pmsg_opt='. q_bitor( q_bitand('pmsg_opt', ~96), $nrf) .' WHERE id='. $id);
}

function write_pmsg_body($text)
{
	if (($ll = !db_locked())) {
		db_lock('fud26_fl_pm WRITE');
	}

	$fp = fopen($GLOBALS['MSG_STORE_DIR'] .'private', 'ab');
	if (!$fp) {
		exit("FATAL ERROR: cannot open private message store<br />\n");
	}

	fseek($fp, 0, SEEK_END);
	if (!($s = ftell($fp))) {
		$s = __ffilesize($fp);
	}

	if (($len = fwrite($fp, $text)) !== strlen($text)) {
		exit("FATAL ERROR: system has ran out of disk space<br />\n");
	}
	fclose($fp);

	if ($ll) {
		db_unlock();
	}

	if (!$s) {
		chmod($GLOBALS['MSG_STORE_DIR'] .'private', ($GLOBALS['FUD_OPT_2'] & 8388608 ? 0600 : 0666));
	}

	return array($s, $len);
}

function read_pmsg_body($offset, $length)
{
	if ($length < 1) {
		return;
	}

	if ($GLOBALS['FUD_OPT_3'] & 32768 && $offset == -1) {
		return q_singleval('SELECT data FROM fud26_msg_store WHERE id='. $length);
	}

	$fp = fopen($GLOBALS['MSG_STORE_DIR'].'private', 'rb');
	fseek($fp, $offset, SEEK_SET);
	$str = fread($fp, $length);
	fclose($fp);

	return $str;
}

function pmsg_move($mid, $fid, $validate)
{
	if (!$validate && !q_singleval('SELECT id FROM fud26_pmsg WHERE duser_id='. _uid .' AND id='. $mid)) {
		return;
	}

	q('UPDATE fud26_pmsg SET fldr='. $fid .' WHERE duser_id='. _uid .' AND id='. $mid);
}

function pmsg_del($mid, $fldr=0)
{
	if (!$fldr && !($fldr = q_singleval('SELECT fldr FROM fud26_pmsg WHERE duser_id='. _uid .' AND id='. $mid))) {
		return;
	}

	if ($fldr != 5) {
		pmsg_move($mid, 5, 0);
	} else {
		if ($GLOBALS['FUD_OPT_3'] & 32768 && ($fid = q_singleval('SELECT length FROM fud26_pmsg WHERE id='. $mid .' AND foff=-1'))) {
			q('DELETE FROM fud26_msg_store WHERE id='. $fid);
		}
		q('DELETE FROM fud26_pmsg WHERE id='.$mid);
		$c = uq('SELECT id FROM fud26_attach WHERE message_id='. $mid .' AND attach_opt=1');
		while ($r = db_rowarr($c)) {
			@unlink($GLOBALS['FILE_STORE'] . $r[0] .'.atch');
		}
		unset($c);
		q('DELETE FROM fud26_attach WHERE message_id='. $mid .' AND attach_opt=1');
	}
}

function send_pm_notification($email, $pid, $subject, $from)
{
	send_email($GLOBALS['NOTIFY_FROM'], $email, '['.$GLOBALS['FORUM_TITLE'].'] Уведомление о новом личном сообщении', 'Вы получили новое личное сообщение с заголовком "'.$subject.'" от "'.$from.'" с форума "'.$GLOBALS['FORUM_TITLE'].'" forum.\nЧтобы посмотреть сообщение, кликните по ссылке: '.$GLOBALS['WWW_ROOT'].'index.php?t=pmsg_view&id='.$pid.'\n\nЧтобы не получать более эти уведомления, отключите "Уведомления о личных сообщениях" в своих настройках.');
}function validate_email($email)
{
	$bits = explode('@', $email);
	if (count($bits) != 2) {
		return 1;
	}
	$doms = explode('.', $bits[1]);
	$last = array_pop($doms);

	// Validate domain extension 2-4 characters A-Z
	if (!preg_match('!^[A-Za-z]{2,4}$!', $last)) {
		return 1;
	}

	// (Sub)domain name 63 chars long max A-Za-z0-9_
	foreach ($doms as $v) {
		if (!$v || strlen($v) > 63 || !preg_match('!^[A-Za-z0-9_-]+$!', $v)) {
			return 1;
		}
	}

	// Now the hard part, validate the e-mail address itself.
	if (!$bits[0] || strlen($bits[0]) > 255 || !preg_match('!^[-A-Za-z0-9_.+{}~\']+$!', $bits[0])) {
		return 1;
	}
}

function encode_subject($text)
{
	if (preg_match('![\x7f-\xff]!', $text)) {
		$text = '=?utf-8?B?'. base64_encode($text) .'?=';
	}

	return $text;
}

function send_email($from, $to, $subj, $body, $header='', $munge_newlines=1)
{
	if (empty($to)) {
		return;
	}

	/* HTML entities check. */
	if (strpos($subj, '&') !== false) {
		$subj = html_entity_decode($subj);
	}

	if ($header) {
		$header = "\n" . str_replace("\r", '', $header);
	}
	$extra_header = '';
	if (strpos($header, 'MIME-Version') === false) {
		$extra_header = "\nMIME-Version: 1.0\nContent-Type: text/plain; charset=utf-8\nContent-Transfer-Encoding: 8bit". $header;
	}
	$header = 'From: '. $from ."\nErrors-To: ". $from ."\nReturn-Path: ". $from ."\nX-Mailer: FUDforum v". $GLOBALS['FORUM_VERSION']. $extra_header. $header;

	$body = str_replace("\r", '', $body);
	if ($munge_newlines) {
		$body = str_replace('\n', "\n", $body);
	}
	$subj = encode_subject($subj);

	if (defined('forum_debug')) {
		logaction(_uid, 'SEND EMAIL', 0, 'To=['. implode(',', (array)$to) .']<br />Subject=['. $subj .']<br />Headers=['. str_replace("\n", '<br />', htmlentities($header)) .']<br />Message=['. $body .']');
	}

	if ($GLOBALS['FUD_OPT_1'] & 512) {
		if (!class_exists('fud_smtp')) {
			fud_use('smtp.inc');
		}
		$smtp = new fud_smtp;
		$smtp->msg = str_replace(array('\n', "\n."), array("\n", "\n.."), $body);
		$smtp->subject = encode_subject($subj);
		$smtp->to = $to;
		$smtp->from = $from;
		$smtp->headers = $header;
		$smtp->send_smtp_email();
		return;
	}

	foreach ((array)$to as $email) {
		if (!@mail($email, $subj, $body, $header)) {
			fud_logerror('Your system didn\'t accept E-mail ['. $subj .'] to ['. $email .'] for delivery.', 'fud_errors');
		}
	}
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
}function get_host($ip)
{
	if (!$ip || $ip == '0.0.0.0') {
		return;
	}

	$name = gethostbyaddr($ip);

	if ($name == $ip) {
		$name = substr($name, 0, strrpos($name, '.')) .'*';
	} else if (substr_count($name, '.') > 1) {
		$name = '*'. substr($name, strpos($name, '.')+1);
	}

	return $name;
}class fud_smtp
{
	var $fs, $last_ret, $msg, $subject, $to, $from, $headers;

	function get_return_code($cmp_code='250')
	{
		if (!($this->last_ret = @fgets($this->fs, 515))) {
			return;
		}
		if ((int)$this->last_ret == $cmp_code) {
			return 1;
		}
		return;
	}

	function wts($string)
	{
		/* Write to stream. */
		fwrite($this->fs, $string ."\r\n");
	}

	function open_smtp_connex()
	{
		if( !($this->fs = @fsockopen($GLOBALS['FUD_SMTP_SERVER'], $GLOBALS['FUD_SMTP_PORT'], $errno, $errstr, $GLOBALS['FUD_SMTP_TIMEOUT'])) ) {
			fud_logerror('ERROR: SMTP server at '. $GLOBALS['FUD_SMTP_SERVER'] ." is not available<br />\n". ($errno ? "Additional Problem Info: $errno -> $errstr <br />\n" : ''), 'fud_errors');
			return;
		}
		if (!$this->get_return_code(220)) {	// 220 == Ready to speak SMTP.
			return;
		}

		$es = strpos($this->last_ret, 'ESMTP') !== false;
		$smtp_srv = $_SERVER['SERVER_NAME'];
		if ($smtp_srv == 'localhost' || $smtp_srv == '127.0.0.1') {
			$smtp_srv = 'FUDforum SMTP server';
		}

		$this->wts(($es ? 'EHLO ' : 'HELO ') . $smtp_srv);
		if (!$this->get_return_code()) {
			return;
		}

		/* Scan all lines and look for TLS support. */
		$tls = false;
		if ($es) {
			while($str = @fgets($this->fs, 515)) {
				if (substr($str, 0, 12) == '250-STARTTLS') $tls = true;
				if (substr($str, 3,  1) == ' ') break;	// Done reading if 4th char is a space.

			}
		}

		/* Do SMTP Auth if needed. */
		if ($GLOBALS['FUD_SMTP_LOGIN']) {
			if ($tls) {
				/*  Initiate TSL communication with server. */
				$this->wts('STARTTLS');
				if (!$this->get_return_code(220)) {
					return;
				}
				/* Encrypt the connection. */
				if (!stream_socket_enable_crypto($this->fs, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
					return false;
				} 
				/* Say hi again. */
				$this->wts(($es ? 'EHLO ' : 'HELO ').$smtp_srv);
				if (!$this->get_return_code()) {
					return;
				}
				/* we need to scan all other lines */
				while($str = @fgets($this->fs, 515)) {
					if (substr($str, 3, 1) == ' ') break;
				}
			}

			$this->wts('AUTH LOGIN');
			if (!$this->get_return_code(334)) {
				return;
			}
			$this->wts(base64_encode($GLOBALS['FUD_SMTP_LOGIN']));
			if (!$this->get_return_code(334)) {
				return;
			}
			$this->wts(base64_encode($GLOBALS['FUD_SMTP_PASS']));
			if (!$this->get_return_code(235)) {
				return;
			}
		}

		return 1;
	}

	function send_from_hdr()
	{
		$this->wts('MAIL FROM: <'. $GLOBALS['NOTIFY_FROM'] .'>');
		return $this->get_return_code();
	}

	function send_to_hdr()
	{
		$this->to = (array) $this->to;

		foreach ($this->to as $to_addr) {
			$this->wts('RCPT TO: <'. $to_addr .'>');
			if (!$this->get_return_code()) {
				return;
			}
		}
		return 1;
	}

	function send_data()
	{
		$this->wts('DATA');
		if (!$this->get_return_code(354)) {
			return;
		}

		/* This is done to ensure what we comply with RFC requiring each line to end with \r\n */
		$this->msg = preg_replace('!(\r)?\n!si', "\r\n", $this->msg);

		if( empty($this->from) ) $this->from = $GLOBALS['NOTIFY_FROM'];

		$this->wts('Subject: '. $this->subject);
		$this->wts('Date: '. date('r'));
		$this->wts('To: '. (count($this->to) == 1 ? $this->to[0] : $GLOBALS['NOTIFY_FROM']));
		$this->wts('From: '. $this->from);
		$this->wts('X-Mailer: FUDforum v'. $GLOBALS['FORUM_VERSION']);
		$this->wts($this->headers ."\r\n");
		$this->wts($this->msg);
		$this->wts('.');

		return $this->get_return_code();
	}

	function close_connex()
	{
		$this->wts('QUIT');
		fclose($this->fs);
	}

	function send_smtp_email()
	{
		if (!$this->open_smtp_connex()) {
			if ($this->last_ret) {
				fud_logerror('Open SMTP connection - invalid return code: '. $this->last_ret, 'fud_errors');
			}
			return false;
		}
		if (!$this->send_from_hdr()) {
			fud_logerror('Send "From:" header - invalid SMTP return code: '. $this->last_ret, 'fud_errors');
			$this->close_connex();
			return false;
		}
		if (!$this->send_to_hdr()) {
			fud_logerror('Send "To:" header - invalid SMTP return code: '. $this->last_ret, 'fud_errors');
			$this->close_connex();
			return false;
		}
		if (!$this->send_data()) {
			fud_logerror('Send data - invalid SMTP return code: '. $this->last_ret, 'fud_errors');
			$this->close_connex();
			return false;
		}

		$this->close_connex();
		return true;
	}
}

	if (!($FUD_OPT_1 & 1024)) {
		error_dialog('ОШИБКА: Система личных сообщений отключена', 'Вы не можете использовать систему личных сообщений, так как она была выключена администратором.');
	}

	if (__fud_real_user__) {
		is_allowed_user($usr);
	} else {
		std_error('login');
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

	if (!isset($_GET['id']) || !($id = (int)$_GET['id'])) {
		invl_inp_err();
	}

	$m = db_sab('SELECT
		p.*,
		u.id AS user_id, u.alias, u.users_opt, u.avatar_loc, u.email, u.posted_msg_count, u.join_date,
		u.location, u.sig, u.icq, u.aim, u.msnm, u.yahoo, u.jabber, u.affero, u.google, u.skype, u.twitter, u.custom_status, u.last_visit,
		l.name AS level_name, l.level_opt, l.img AS level_img
	FROM
		fud26_pmsg p
		INNER JOIN fud26_users u ON p.ouser_id=u.id
		LEFT JOIN fud26_level l ON u.level_id=l.id
	WHERE p.duser_id='. _uid .' AND p.id='. $id);

	if (!$m) {
		invl_inp_err();
	}

	ses_update_status($usr->sid, 'Личная почта');

	/* Next Msg */
	if (($nid = q_singleval('SELECT p.id FROM fud26_pmsg p INNER JOIN fud26_users u ON u.id=p.ouser_id WHERE p.duser_id='. _uid .' AND p.fldr='. $m->fldr .' AND post_stamp>'. $m->post_stamp .' ORDER BY p.post_stamp ASC LIMIT 1'))) {
		$dpmsg_next_message = '<a href="index.php?t=pmsg_view&amp;'._rsid.'&amp;id='.$nid.'">Следующее сообщение <img src="theme/velopiter/images/goto.gif" alt="" /></a>';
	} else {
		$dpmsg_next_message = '';
	}

	/* Prev Msg */
	if (($pid = q_singleval('SELECT p.id FROM fud26_pmsg p INNER JOIN fud26_users u ON u.id=p.ouser_id WHERE p.duser_id='. _uid .' AND p.fldr='. $m->fldr .' AND p.post_stamp<'. $m->post_stamp .' ORDER BY p.post_stamp DESC LIMIT 1'))) {
		$dpmsg_prev_message = '<a href="index.php?t=pmsg_view&amp;'._rsid.'&amp;id='.$pid.'"><img src="theme/velopiter/images/goback.gif" alt="" /> Предыдущее сообщение</a>';
	} else {
		$dpmsg_prev_message = '';
	}

	if (!$m->read_stamp && $m->pmsg_opt & 16) {
		q('UPDATE fud26_pmsg SET read_stamp='. __request_timestamp__ .', pmsg_opt='. q_bitor( q_bitand('pmsg_opt', ~4), 8) .' WHERE id='. $m->id);
		if ($m->ouser_id != _uid && $m->pmsg_opt & 4 && !isset($_GET['dr'])) {
			$track_msg = new fud_pmsg;
			$track_msg->ouser_id = $track_msg->duser_id = $m->ouser_id;
			$track_msg->ip_addr = $track_msg->host_name = null;
			$track_msg->post_stamp = __request_timestamp__;
			$track_msg->read_stamp = 0;
			$track_msg->fldr = 1;
			$track_msg->pmsg_opt = 16|32;
			$track_msg->subject = 'Чтение извещения о: '.$m->subject;
			$track_msg->body = 'Привет,<br />'.$usr->login.' прочел ваше личное сообщение, "'.$m->subject.'", от '.strftime("%a, %d %B %Y %H:%M", __request_timestamp__).'<br />';
			$track_msg->add(1);
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
<?php echo tmpl_cur_ppage($m->fldr, $folders, $m->subject); ?>
<?php echo $tabs; ?>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="2">Автор</th>
<?php echo tmpl_drawpmsg($m, $usr, false); ?>
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
