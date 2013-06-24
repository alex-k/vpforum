<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: thr_exch.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function send_status_update($uid, $ulogin, $uemail, $title, $msg)
{
	if ($GLOBALS['FUD_OPT_1'] & 1024) {	// PM_ENABLED
		if (defined('no_inline')) {
			fud_use('private.inc');
			fud_use('iemail.inc');
			fud_use('rev_fmt.inc');
		}
		$GLOBALS['recv_user_id'] = (array)$uid;
		$pmsg = new fud_pmsg;
		$pmsg->to_list = $ulogin;
		$pmsg->ouser_id = _uid;
		$pmsg->post_stamp = __request_timestamp__;
		$pmsg->subject = $title;
		$pmsg->host_name = 'NULL';
		$pmsg->ip_addr = '0.0.0.0';
		list($pmsg->foff, $pmsg->length) = write_pmsg_body(nl2br($msg));
		$pmsg->send_pmsg();
		return;
	}

	if (defined('no_inline')) {
		fud_use('iemail.inc');
	}
	send_email($GLOBALS['NOTIFY_FROM'], $uemail, $title, $msg);
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
}function logaction($user_id, $res, $res_id=0, $action=null)
{
	q('INSERT INTO fud26_action_log (logtime, logaction, user_id, a_res, a_res_id)
		VALUES('. __request_timestamp__ .', '. ssn($action) .', '. $user_id .', '. ssn($res) .', '. (int)$res_id .')');
}function th_add($root, $forum_id, $last_post_date, $thread_opt, $orderexpiry, $replies=0, $views=0, $lpi=0, $descr='')
{
	if (!$lpi) {
		$lpi = $root;
	}

	return db_qid('INSERT INTO
		fud26_thread
			(forum_id, root_msg_id, last_post_date, replies, views, rating, last_post_id, thread_opt, orderexpiry, tdescr)
		VALUES
			('. $forum_id .', '. $root .', '. $last_post_date .', '. $replies .', '. $views .', 0, '. $lpi .', '. $thread_opt .', '. $orderexpiry.','. _esc($descr) .')');
}

function th_move($id, $to_forum, $root_msg_id, $forum_id, $last_post_date, $last_post_id, $descr)
{
	if (!db_locked()) {
		if ($to_forum != $forum_id) {
			$lock = 'fud26_tv_'. $to_forum .' WRITE, fud26_tv_'. $forum_id;
		} else {
			$lock = 'fud26_tv_'. $to_forum;
		}
		
		db_lock('fud26_poll WRITE, '. $lock .' WRITE, fud26_thread WRITE, fud26_forum WRITE, fud26_msg WRITE');
		$ll = 1;
	}
	$msg_count = q_singleval('SELECT count(*) FROM fud26_thread LEFT JOIN fud26_msg ON fud26_msg.thread_id=fud26_thread.id WHERE fud26_msg.apr=1 AND fud26_thread.id='. $id);

	q('UPDATE fud26_thread SET forum_id='. $to_forum .' WHERE id='. $id);
	q('UPDATE fud26_forum SET post_count=post_count-'. $msg_count .' WHERE id='. $forum_id);
	q('UPDATE fud26_forum SET thread_count=thread_count+1,post_count=post_count+'. $msg_count .' WHERE id='. $to_forum);
	q('DELETE FROM fud26_thread WHERE forum_id='. $to_forum .' AND root_msg_id='. $root_msg_id .' AND moved_to='. $forum_id);
	if (($aff_rows = db_affected())) {
		q('UPDATE fud26_forum SET thread_count=thread_count-'. $aff_rows .' WHERE id='. $to_forum);
	}
	q('UPDATE fud26_thread SET moved_to='. $to_forum .' WHERE id!='. $id .' AND root_msg_id='. $root_msg_id);

	q('INSERT INTO fud26_thread
		(forum_id, root_msg_id, last_post_date, last_post_id, moved_to, tdescr)
	VALUES
		('. $forum_id .', '. $root_msg_id .', '. $last_post_date .', '. $last_post_id .', '. $to_forum .','. _esc($descr) .')');

	rebuild_forum_view_ttl($forum_id);
	rebuild_forum_view_ttl($to_forum);

	$p = db_all('SELECT poll_id FROM fud26_msg WHERE thread_id='. $id .' AND apr=1 AND poll_id>0');
	if ($p) {
		q('UPDATE fud26_poll SET forum_id='. $to_forum .' WHERE id IN('. implode(',', $p) .')');
	}

	if (isset($ll)) {
		db_unlock();
	}
}

function __th_cron_emu($forum_id, $run=1)
{
	/* Let's see if we have sticky threads that have expired. */
	$exp = db_all('SELECT fud26_thread.id FROM fud26_tv_'. $forum_id .'
			INNER JOIN fud26_thread ON fud26_thread.id=fud26_tv_'. $forum_id .'.thread_id
			INNER JOIN fud26_msg ON fud26_thread.root_msg_id=fud26_msg.id
			WHERE fud26_tv_'. $forum_id .'.id>'. (q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' ORDER BY seq DESC LIMIT 1') - 50).' 
				AND fud26_tv_'. $forum_id .'.iss>0
				AND fud26_thread.thread_opt>=2 
				AND (fud26_msg.post_stamp+fud26_thread.orderexpiry)<='. __request_timestamp__);
	if ($exp) {
		q('UPDATE fud26_thread SET orderexpiry=0, thread_opt=(thread_opt & ~(2|4)) WHERE id IN('. implode(',', $exp) .')');
		$exp = 1;
	}

	/* Remove expired moved thread pointers. */
	q('DELETE FROM fud26_thread WHERE forum_id='. $forum_id .' AND moved_to>0 AND last_post_date<'.(__request_timestamp__ - 86400 * $GLOBALS['MOVED_THR_PTR_EXPIRY']));
	if (($aff_rows = db_affected())) {
		q('UPDATE fud26_forum SET thread_count=thread_count-'. $aff_rows .' WHERE thread_count>0 AND id='. $forum_id);
		if (!$exp) {
			$exp = 1;
		}
	}

	if ($exp && $run) {
		rebuild_forum_view_ttl($forum_id,1);
	}

	return $exp;
}

function rebuild_forum_view_ttl($forum_id, $skip_cron=0)
{
	if (!$skip_cron) {
		__th_cron_emu($forum_id, 0);
	}

	if (!db_locked()) {
		$ll = 1;
		db_lock('fud26_tv_'. $forum_id .' WRITE, fud26_thread READ, fud26_msg READ');
	}

	q('DELETE FROM fud26_tv_'. $forum_id);

	q('INSERT INTO fud26_tv_'. $forum_id .' (thread_id,iss,seq) SELECT id, iss, '. q_rownum() .' FROM
		(SELECT fud26_thread.id AS id, '. q_bitand('thread_opt', (2|4|8)) .' AS iss FROM fud26_thread 
		INNER JOIN fud26_msg ON fud26_thread.root_msg_id=fud26_msg.id 
		WHERE forum_id='. $forum_id .' AND fud26_msg.apr=1 
		ORDER BY (CASE WHEN thread_opt>=2 THEN (4294967294 + (('. q_bitand('thread_opt', 8) .') * 100000000) + fud26_thread.last_post_date) ELSE fud26_thread.last_post_date END) ASC) q1');

	if (__dbtype__ == 'sqlite') {
		// q_rownum() is not implemented for SQLite.
		// If we empty a table (the DELETE above), the ID (internal sequence) will be reset to 0. We will misuse it as the ROWNUM.
		q('UPDATE fud26_tv_'. $forum_id .' SET seq=id');
	}

	if (isset($ll)) {
		db_unlock();
	}
}

function th_delete_rebuild($forum_id, $th)
{
	if (!db_locked()) {
		$ll = 1;
		db_lock('fud26_tv_'. $forum_id .' WRITE');
	}

	/* Get position. */
	if (($pos = q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' WHERE thread_id='. $th))) {
		q('DELETE FROM fud26_tv_'. $forum_id .' WHERE thread_id='. $th);
		/* Move every one down one, if placed after removed topic. */
		q('UPDATE fud26_tv_'. $forum_id .' SET seq=seq-1 WHERE seq>'. $pos);
	}

	if (isset($ll)) {
		db_unlock();
	}
}

function th_new_rebuild($forum_id, $th, $sticky)
{
	if (__th_cron_emu($forum_id)) {
		return;
	}

	if (!db_locked()) {
		$ll = 1;
		db_lock('fud26_tv_'. $forum_id .' WRITE');
	}

	list($max,$iss) = db_saq('SELECT seq,iss FROM fud26_tv_'. $forum_id .' ORDER BY seq DESC LIMIT 1');
	if ((!$sticky && $iss) || $iss >=8) { /* Sub-optimal case, non-sticky topic and thre are stickies in the forum. */
		/* Find oldest sticky message. */
		if ($sticky && $iss >= 8) {
			$iss = q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' WHERE seq>'. ($max - 50) .' AND iss>=8 ORDER BY seq ASC LIMIT 1');
		} else {
			$iss = q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' WHERE seq>'. ($max - 50) .' AND iss>0 ORDER BY seq ASC LIMIT 1');
		}
		/* Move all stickies up one. */
		q('UPDATE fud26_tv_'. $forum_id .' SET seq=seq+1 WHERE seq>='. $iss);
		/* We do this, since in optimal case we just do ++max. */
		$max = --$iss;
	}
	q('INSERT INTO fud26_tv_'. $forum_id .' (thread_id,iss,seq) VALUES('. $th .','. (int)$sticky .','. (++$max) .')');

	if (isset($ll)) {
		db_unlock();
	}
}

function th_reply_rebuild($forum_id, $th, $sticky)
{
	if (!db_locked()) {
		$ll = 1;
		db_lock('fud26_tv_'. $forum_id .' WRITE');
	}

	list($max,$tid,$iss) = db_saq('SELECT seq,thread_id,iss FROM fud26_tv_'. $forum_id .' ORDER BY seq DESC LIMIT 1');

	if ($tid == $th) {
		/* NOOP: quick elimination, topic is already 1st. */
	} else if (!$iss || ($sticky && $iss < 8)) { /* Moving to the very top. */
		/* Get position. */
		$pos = q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' WHERE thread_id='. $th);
		/* Move everyone ahead, 1 down. */
		q('UPDATE fud26_tv_'. $forum_id .' SET seq=seq-1 WHERE seq>'. $pos);
		/* Move to top of the stack. */
		q('UPDATE fud26_tv_'. $forum_id .' SET seq='. $max .' WHERE thread_id='. $th);
	} else {
		/* Get position. */
		$pos = q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' WHERE thread_id='. $th);
		/* Find oldest sticky message. */
		$iss = q_singleval('SELECT seq FROM fud26_tv_'. $forum_id .' WHERE seq>'. ($max - 50) .' AND iss>'. ($sticky && $iss >= 8 ? '=8' : '0') .' ORDER BY seq ASC LIMIT 1');
		/* Move everyone ahead, unless sticky, 1 down. */
		q('UPDATE fud26_tv_'. $forum_id .' SET seq=seq-1 WHERE seq BETWEEN '. ($pos + 1) .' AND '. ($iss - 1));
		/* Move to top of the stack. */
		q('UPDATE fud26_tv_'. $forum_id .' SET seq='. ($iss - 1) .' WHERE thread_id='. $th);
	}

	if (isset($ll)) {
		db_unlock();
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
}

	/* Only admins & moderators have access to this control panel. */
	if (!_uid) {
		std_error('login');
	} if (!($usr->users_opt & (1048576|524288))) {
		std_error('access');
	}

	if (isset($_GET['appr']) || isset($_GET['decl']) || isset($_POST['decl'])) {
		fud_use('thrx_adm.inc', true);
	}
	$decl = 0;

	/* Verify that we got a valid thread-x-change approval. */
	if (isset($_GET['appr']) && ($thrx = thx_get((int)$_GET['appr']))) {
		$data = db_sab('SELECT
					t.forum_id, t.last_post_id, t.root_msg_id, t.last_post_date, t.last_post_id, t.tdescr,
					f1.id, f1.last_post_id as f1_lpi, f2.last_post_id AS f2_lpi,
					'. ($is_a ? ' 1 ' : ' mm.id ') .' AS md
				FROM fud26_thread t
				INNER JOIN fud26_forum f1 ON t.forum_id=f1.id
				INNER JOIN fud26_forum f2 ON f2.id='. $thrx->frm .'
				LEFT JOIN fud26_mod mm ON mm.forum_id=f2.id AND mm.user_id='. _uid .'
				WHERE t.id='. $thrx->th);
		if (!$data) {
			invl_inp_err();
		}
		if (!$data->md) {
			std_error('access');
		}

		th_move($thrx->th, $thrx->frm, $data->root_msg_id, $data->forum_id, $data->last_post_date, $data->last_post_id, $data->tdescr);

		if ($data->f1_lpi == $data->last_post_id) {
			$mid = (int) q_singleval('SELECT MAX(last_post_id) FROM fud26_thread t INNER JOIN fud26_msg m ON t.root_msg_id=m.id WHERE t.forum_id='. $data->forum_id .' AND t.moved_to=0 AND m.apr=1');
			q('UPDATE fud26_forum SET last_post_id='. $mid .' WHERE id='. $data->forum_id);
		}

		if ($data->f2_lpi < $data->last_post_id) {
			q('UPDATE fud26_forum SET last_post_id='. $data->last_post_id .' WHERE id='. $thrx->frm);
		}

		thx_delete($thrx->id);
		logaction($usr->id, 'THRXAPPROVE', $thrx->th);
	} else if ((isset($_GET['decl']) || isset($_POST['decl'])) && ($thrx = thx_get(($decl = (int)(isset($_GET['decl']) ? $_GET['decl'] : $_POST['decl']))))) {
		$data = db_sab('SELECT u.email, u.login, u.id, m.subject, f1.name AS f1_name, f2.name AS f2_name, '. ($is_a ? ' 1 ' : ' mm.id ') .' AS md
				FROM fud26_thread t
				INNER JOIN fud26_forum f1 ON t.forum_id=f1.id
				INNER JOIN fud26_forum f2 ON f2.id='. $thrx->frm .'
				INNER JOIN fud26_msg m ON m.id=t.root_msg_id
				INNER JOIN fud26_users u ON u.id='. $thrx->req_by .'
				LEFT JOIN fud26_mod mm ON mm.forum_id='. $thrx->frm .' AND mm.user_id='. _uid .'
				WHERE t.id='. $thrx->th);
		if (!$data) {
			invl_inp_err();
		}
		if (!$data->md) {
			std_error('access');
		}

		if (!empty($_POST['reason'])) {
			send_status_update($data->id, $data->login, $data->email, 'В переносе темы '.$data->subject.' на форум '.$data->f2_name.' было отказано.', htmlspecialchars($_POST['reason']));
			thx_delete($thrx->id);
			$decl = 0;
		} else {
			$thr_exch_data = '<form method="post" action="index.php?t=thr_exch" name="thr_exch">
'._hs.'<input type="hidden" name="decl" value="'.$decl.'" />
<tr><td class="RowStyleC">Причина отказа переноса темы <b>'.$data->subject.'</b> в форум <b>'.$data->f2_name.'</b><br /><textarea name="reason" cols="60" rows="10"></textarea><br /><input type="submit" class="button" name="btn_submit" value="Продолжить" /></td><tr>
</form>';
		}

		logaction($usr->id, 'THRXDECLINE', $thrx->th);
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
	}

	if (!$decl) {
		$thr_exch_data = '';
		$thx = array();

		$r = uq('SELECT
				thx.*, m.subject, f1.name AS sf_name, f2.name AS df_name, u.alias
			 FROM fud26_thr_exchange thx
			 INNER JOIN fud26_thread t ON thx.th=t.id
			 INNER JOIN fud26_msg m ON t.root_msg_id=m.id
			 INNER JOIN fud26_forum f1 ON t.forum_id=f1.id
			 INNER JOIN fud26_forum f2 ON thx.frm=f2.id
			 INNER JOIN fud26_users u ON thx.req_by=u.id
			 LEFT JOIN fud26_mod mm ON mm.forum_id=f2.id AND mm.user_id='. _uid .
			 ($is_a ? '' : ' WHERE mm.id IS NOT NULL'));

		while ($obj = db_rowobj($r)) {
			if ($is_a) {
				$thx[] = $obj->id;	
			}
			$thr_exch_data .= '<tr><td>
<table border="0" cellspacing="0" cellpadding="3" class="wa">
	<tr class="RowStyleB">
		<td class="al nw vt SmallText"><b>Перенос темы затребован:</b> <a href="index.php?t=usrinfo&amp;id='.$obj->req_by.'&amp;'._rsid.'">'.$obj->alias.'</a><br /></td>
		<td class="ac wa vt SmallText"><b>Причина переноса:</b><br /><table border="1" cellspacing="1" cellpadding="0"><tr><td class="al">&nbsp;'.$obj->reason_msg.' &nbsp;</td></tr></table></td>
	</tr>
	<tr class="RowStyleC">
		<td colspan="2">
			<table border="0" cellspacing="0" cellpadding="3" class="wa">
			<tr>
				<td class="al SmallText"><b>Исходный форум:</b> '.$obj->sf_name.'<br /><b>Форум для переноса:</b> '.$obj->df_name.'<br /><b>Тема:</b> <a href="index.php?t=msg&amp;'._rsid.'&amp;th='.$obj->th.'">'.$obj->subject.'</a></td>
				<td class="ar">[<a href="index.php?t=thr_exch&amp;appr='.$obj->id.'&amp;'._rsid.'">утвердить</a>]&nbsp;&nbsp;[<a href="index.php?t=thr_exch&amp;decl='.$obj->id.'&amp;'._rsid.'">отказать</a>]</td>
			</tr>
			</table>
		</td>
	</tr>
</table>
</td></tr>';
		}
		unset($r);

		if (!$thr_exch_data) {
			$thr_exch_data = '<tr><td class="RowStyleA ac">Нет ожидающих утверждения тем.</td></tr>';
		} else if ($is_a && $thx) {
			q('DELETE FROM fud26_thr_exchange WHERE id NOT IN('. implode(',', $thx) .')');
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
<br /><?php echo $admin_cp; ?>
<table cellspacing="0" cellpadding="0" class="ContentTable">
<tr><th>Перенос темы</th></tr>
<?php echo $thr_exch_data; ?>
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