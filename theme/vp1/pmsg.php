<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: pmsg.php.t 4994 2010-09-02 17:33:29Z naudefj $
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
}$folders = array(1=>'Входящие', 2=>'Сохранено', 4=>'Черновики', 3=>'Отправленные', 5=>'Корзина');

function tmpl_cur_ppage($folder_id, $folders, $msg_subject='')
{
	if (!$folder_id || (!$msg_subject && $_GET['t'] == 'ppost')) {
		$user_action = 'Создание личного письма';
	} else {
		$user_action = $msg_subject ? '<a href="index.php?t=pmsg&amp;folder_id='.$folder_id.'&amp;'._rsid.'">'.$folders[$folder_id].'</a> &raquo; '.$msg_subject : 'Просмотр папки <b>'.$folders[$folder_id].'</b>';
	}

	return '<span class="GenText"><a href="index.php?t=pmsg&amp;'._rsid.'">Личная почта</a>&nbsp;&raquo;&nbsp;'.$user_action.'</span><br /><img src="blank.gif" alt="" height="4" width="1" /><br />';
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
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
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

	/* Empty trash. */
	if (isset($_POST['btn_trash'])) {
		$c = q('SELECT id FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=5');
		while ($r = db_rowarr($c)) {
			pmsg_del((int)$r[0], 5);
		}
		unset($c, $_POST['sel'], $_GET['sel']); /* Prevent message selection cofusion. */
		if (isset($_POST['old_folder_id'])) {
			$_GET['folder_id'] = $_POST['old_folder_id'];
		}
	}

	/* Moving or deleting a message. */
	if (isset($_POST['sel']) || isset($_GET['sel'])) {
		if (!empty($_POST['btn_pdf'])) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'pdf.php?sel[]='. implode('&sel[]=', $_POST['sel']) .'&'. _rsidl);
			exit;		
		}
		$sel = isset($_POST['sel']) ? (array)$_POST['sel'] : (array)$_GET['sel'];
		$move_to = (!isset($_POST['btn_delete']) && isset($_POST['moveto'], $folders[$_POST['moveto']])) ? (int) $_POST['moveto'] : 0;

		if (!$move_to && !isset($_POST['old_folder_id'])) {
			$_POST['old_folder_id'] = q_singleval('SELECT fldr FROM fud26_pmsg WHERE id='. $sel[0]);
		}

		foreach ($sel as $m) {
			if ($move_to) {
				pmsg_move((int)$m, $move_to, 0);
			} else {
				pmsg_del((int)$m);
			}
		}
		if (!$move_to && isset($_POST['old_folder_id'])) {
			$_GET['folder_id'] = $_POST['old_folder_id'];
		}
	}

	if (isset($_GET['folder_id'], $folders[(int)$_GET['folder_id']])) {
		$folder_id = (int) $_GET['folder_id'];
	} else if (isset($_POST['folder_id'], $folders[(int)$_POST['folder_id']])) {
		$folder_id = (int) $_POST['folder_id'];
	} else {
		$folder_id = 1;
	}

	ses_update_status($usr->sid, 'Личная почта');

	$select_options_cur_folder = tmpl_draw_select_opt(implode("\n", array_keys($folders)), implode("\n", $folders), $folder_id);

	if ($GLOBALS['FUD_OPT_3'] & 32768) {
		$disk_usage  = q_singleval('SELECT SUM(length) FROM fud26_pmsg WHERE foff>0 AND duser_id='. _uid);
		$disk_usage += q_singleval('SELECT SUM(LENGTH(data)) FROM fud26_pmsg p INNER JOIN fud26_msg_store m ON p.length=m.id WHERE foff<0 AND duser_id='. _uid);
	} else {
		$disk_usage = q_singleval('SELECT SUM(length) FROM fud26_pmsg WHERE duser_id='. _uid);
	}
	if ($usr->users_opt & 524288) {
		$ms = $MAX_PMSG_FLDR_SIZE_PM;
	} else if ($usr->users_opt & 1048576) {
		$ms = $MAX_PMSG_FLDR_SIZE_AD;
	} else {
		$ms = $MAX_PMSG_FLDR_SIZE;
	}

	$percent_full = ceil($disk_usage / $ms * 100);
	$full_indicator = ceil($percent_full * 1.69);

	if ($percent_full < 90) {
		$full_indicator = '<td class="pmSn"><img src="blank.gif" alt="" width="'.$full_indicator.'" height="8" /></td>';
	} else if ($percent_full >= 90 && $percent_full < 100) {
		$full_indicator = '<td class="pmSa"><img src="blank.gif" alt="" width="'.$full_indicator.'" height="8" /></td>';
	} else {
		$full_indicator = '<td class="pmSf"><img src="blank.gif" alt="" width="'.$full_indicator.'" height="8" /></td>';
	}

	if (isset($_GET['o'])) {
		switch ($_GET['o']) {
			case 'subject':
				$o = 'p.subject';
			case 'alias':
				$o = 'u.alias';
			case 'alias2':
				$o = 'u2.alias';
				break;
			default:
				$o = 'p.post_stamp';
		}	
	} else {
		$o = 'post_stamp';
	}

	if (isset($_GET['s']) && $_GET['s'] == 'a') {
		$s = 'ASC';
	} else {
		$s = 'DESC';
	}

	$ttl = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr='. $folder_id);
	$count = $usr->posts_ppg ? $usr->posts_ppg : $POSTS_PER_PAGE;
	$start = (empty($_GET['start']) || $_GET['start'] >= $ttl) ? 0 : (int)$_GET['start'];

	$c = uq(q_limit('SELECT p.id, p.read_stamp, p.post_stamp, p.duser_id, p.ouser_id, p.subject, p.pmsg_opt, p.fldr, p.pdest, p.to_list,
			u.users_opt, u.alias, u.last_visit AS time_sec,
			u2.users_opt AS users_opt2, u2.alias AS alias2, u2.last_visit AS time_sec2
		FROM fud26_pmsg p
		INNER JOIN fud26_users u ON p.ouser_id=u.id
		LEFT JOIN fud26_users u2 ON p.pdest=u2.id
		WHERE duser_id='. _uid .' AND fldr='. $folder_id .' ORDER BY '. $o .' '. $s,
		$count, $start));

	$private_msg_entry = '';
	while ($obj = db_rowobj($c)) {
		switch ($obj->fldr) {
			case 1:
			case 2:
				$action = '<a href="index.php?t=ppost&amp;'._rsid.'&amp;reply='.$obj->id.'"><img src="theme/vp1/images/msg_reply.gif" alt="" /></a>&nbsp;<a href="index.php?t=ppost&amp;quote='.$obj->id.'&amp;'._rsid.'"><img src="theme/vp1/images/msg_quote.gif" alt="" /></a>&nbsp;<a href="index.php?t=ppost&amp;forward='.$obj->id.'&amp;'._rsid.'"><img src="theme/vp1/images/msg_forward.gif" alt="" /></a>';
				break;
			case 3:
				$obj->users_opt = $obj->users_opt2;
				$obj->alias = $obj->alias2;
				$obj->time_sec = $obj->time_sec2;
				$obj->ouser_id = $obj->pdest;
				$action = '';
				break;
			case 5:
				$action = '<a href="index.php?t=ppost&amp;'._rsid.'&amp;forward='.$obj->id.'"><img src="theme/vp1/images/msg_forward.gif" alt="" /></a>';
				break;
			case 4:
				$action = '<a href="index.php?t=ppost&amp;'._rsid.'&amp;msg_id='.$obj->id.'"><img src="theme/vp1/images/msg_edit.gif" alt="" /></a>';
				break;
		}

		if ($FUD_OPT_2 & 32768 && !empty($_SERVER['PATH_INFO'])) {
			$goto = $folder_id != 4 ? 'index.php/pmv/'. $obj->id .'/'. _rsid : 'index.php/pmm/msg_id/'. $obj->id .'/'. _rsid;
		} else {
			$goto = $folder_id != 4 ? 'index.php?t=pmsg_view&amp;'. _rsid .'&amp;id='. $obj->id : 'index.php?t=ppost&amp;'. _rsid .'&amp;msg_id='. $obj->id;
		}

		if ($FUD_OPT_2 & 32 && (!($obj->users_opt & 32768) || $is_a)) {
			$obj->login =& $obj->alias;
			if (($obj->time_sec + $LOGEDIN_TIMEOUT * 60) > __request_timestamp__) {
				$online_indicator = '<img src="theme/vp1/images/online'.img_ext.'" alt="онлайн" title="онлайн" />&nbsp;';
			} else {
				$online_indicator = '<img src="theme/vp1/images/offline'.img_ext.'" alt="оффлайн" title="оффлайн" />&nbsp;';
			}
		} else {
			$online_indicator = '';
		}

		if ($obj->pmsg_opt & 64) {
			$msg_type ='<span class="SmallText">(отвечено)</span>&nbsp;';
		} else if ($obj->pmsg_opt & 32) {
			$msg_type = '';
		} else {
			$msg_type ='<span class="SmallText">(переправлено)</span>&nbsp;';
		}

		$private_msg_entry .= '<tr class="RowStyleB">
<td class="ac GenText"><input type="checkbox" name="sel[]" value="'.$obj->id.'" /></td>
<td>'.($obj->read_stamp ? '<img src="theme/vp1/images/pmsg_read'.img_ext.'" alt="Прочитанное личное сообщение" title="Прочитанное личное сообщение" />' : '<img src="theme/vp1/images/pmsg_unread'.img_ext.'" alt="Непрочитанное личное сообщение" title="Непрочитанное личное сообщение" />' ) .'</td><td width="100%" class="GenText">'.$msg_type.'<a href="'.$goto.'">'.$obj->subject.'</a>'.(($obj->pmsg_opt & 4 && $obj->pmsg_opt & 16 && $obj->duser_id == _uid && $obj->ouser_id != _uid) ? '<span class="SmallText">&nbsp;&nbsp;[<a href="index.php?t=pmsg_view&amp;'._rsid.'&amp;dr=1&amp;id='.$obj->id.'" title="Не отправлять извещение автору о прочтении вами данного сообщения">запретить извещение</a>]</span>' : '' ) .'</td>
<td class="nw GenText">'.$online_indicator.'<a href="index.php?t=usrinfo&amp;'._rsid.'&amp;id='.$obj->ouser_id.'" title="'.char_fix(htmlspecialchars($obj->to_list)).'">'.$obj->alias.'</a></td>
<td class="nw DateText">'.strftime("%a, %d %B %Y %H:%M", $obj->post_stamp).'</td>
<td class="nw ac GenText">'.$action.'</td>
</tr>';
	}
	unset($c);

	if (!$private_msg_entry) {
		$private_msg_entry = '<tr class="RowStyleC"><td colspan="6" class="ac">В этом каталоге нет сообщений</td></tr>';
		$private_tools = '';
	} else {
		if ($folder_id == 5) {
			$btn_action = 'Восстановить в:';
			$btn_del_name = 'btn_trash';
			$btn_del_title = 'Очистить корзину';
		} else {
			$btn_action = 'Переместить в:';
			$btn_del_name = 'btn_delete';
			$btn_del_title = 'Удалить';
		}
		$tmp = $folders;
		unset($tmp[$folder_id]);
		$moveto_list = tmpl_draw_select_opt(implode("\n", array_keys($tmp)), implode("\n", $tmp), 2);
		$private_tools = '<tr class="RowStyleB">
<td class="SmallText al" colspan="3"><a href="index.php?t=pmsg_dump&amp;'._rsid.'">Загрузка всех сообщений</a></td>
<td colspan="3" class="SmallText ar">
<input type="submit" class="button" name="btn_move" value="'.$btn_action.'" />
<select name="moveto">'.$moveto_list.'</select>
<input type="hidden" name="old_folder_id" value="'.$folder_id.'" />
&nbsp;&nbsp;&nbsp;<input type="submit" class="button" name="'.$btn_del_name.'" value="'.$btn_del_title.'" />'.($FUD_OPT_2 & 2097152 ? '&nbsp;&nbsp;&nbsp;<input type="submit" class="button" name="btn_pdf" value="Создать PDF-архив" />' : '' )  .'</td></tr>';
	}

	if ($FUD_OPT_2 & 32768) {
		$page_pager = tmpl_create_pager($start, $count, $ttl, 'index.php/pdm/'. $folder_id .'/0/'. strtolower($s[0]) .'/'. $o .'/', '/'. _rsid);
	} else {
		$page_pager = tmpl_create_pager($start, $count, $ttl, 'index.php?t=pmsg&amp;s='. strtolower($s[0]) .'&amp;o='. $o .'&amp;folder_id='. $folder_id .'&amp;'. _rsid);
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
<?php echo $tabs; ?>
<br /><?php echo tmpl_cur_ppage($folder_id, $folders); ?>
<table cellspacing="1" cellpadding="2" class="pmDu">
<tr>
	<td colspan="3" class="RowStyleA wa nw SmallText">Ваши личные папки заполнены на <?php echo $percent_full; ?>%.</td>
</tr>
<tr>
	<td colspan="3" class="RowStyleB wa"><table cellspacing="0" cellpadding="1" border="0"><tr><?php echo $full_indicator; ?></tr></table></td>
</tr>
<tr class="RowStyleA">
	<td class="SmallText" width="58">0%</td>
	<td class="SmallText ac" width="58">50%</td>
	<td class="SmallText ar" width="58">100%</td>
</tr>
</table>
<div class="ar"><a href="index.php?t=ppost&amp;<?php echo _rsid; ?>"><img src="theme/vp1/images/new_pm.gif" alt="" /></a></div>
<form action="index.php?t=pmsg" method="post" id="priv_frm"><?php echo _hs; ?>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr class="RowStyleB">
<td class="SmallText al" colspan="3"><?php echo ($private_tools ? '<a href="index.php?t=pmsg_dump&amp;'._rsid.'">Загрузка всех сообщений</a>' : '&nbsp;' )  .'</td>
<td colspan="3" class="SmallText ar" style="white-space: nowrap">
Папка: <select name="folder_id" onchange="document.forms[\'priv_frm\'].submit();">
'.$select_options_cur_folder.'
</select> <input type="submit" class="button" name="sel_folder" value="Переход" />
</td></tr>
<tr>
	<th class="nw">Выбрано <input type="checkbox" name="toggle" title="все/нет" onclick="$(\'input:checkbox\').attr(\'checked\', (this.checked)?\'checked\':\'\');" /> </th>
	<th>&nbsp;</th>
	<th class="wa"><a href="index.php?t=pmsg&amp;folder_id='.$folder_id.'&amp;'._rsid.'&amp;o=subject&amp;s='.($o=='subject' && $s=='ASC' ? 'd' : 'a'); ?>" class="thLnk">Тема</a></th>
	<th class="ac"><a href="index.php?t=pmsg&amp;folder_id=<?php echo $folder_id; ?>&amp;<?php echo _rsid; ?>&amp;o=<?php echo ($folder_id == 3 ? 'alias2' : 'alias' )  .'&amp;s='.(($o=='alias' || $o=='alias2') && $s=='ASC' ? 'd' : 'a' )  .'" class="thLnk">'.($folder_id == 3 ? 'Получатель' : 'Автор'); ?></a></th>
	<th class="ac"><a href="index.php?t=pmsg&amp;folder_id=<?php echo $folder_id; ?>&amp;<?php echo _rsid; ?>&amp;o=post_stamp&amp;s=<?php echo ($o=='post_stamp' && $s=='ASC' ? 'd' : 'a'); ?>" class="thLnk">Время</a></th>
	<th class="ac">Действие</th>
</tr>
<?php echo $private_msg_entry; ?>
<?php echo $private_tools; ?>
</table></form>
<?php echo $page_pager; ?>
<div class="ar pmL"><a href="index.php?t=ppost&amp;<?php echo _rsid; ?>"><img src="theme/vp1/images/new_pm.gif" alt="" /></a></div>
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
