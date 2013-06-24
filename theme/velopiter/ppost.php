<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: ppost.php.t 5030 2010-10-08 18:27:42Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}$GLOBALS['__SML_CHR_CHK__'] = array("\n"=>1, "\r"=>1, "\t"=>1, ' '=>1, ']'=>1, '['=>1, '<'=>1, '>'=>1, '\''=>1, '"'=>1, '('=>1, ')'=>1, '.'=>1, ','=>1, '!'=>1, '?'=>1);

function smiley_to_post($text)
{
	$text_l = strtolower($text);
	include $GLOBALS['FORUM_SETTINGS_PATH'] .'sp_cache';

	/* remove all non-formatting blocks */
	foreach (array('</pre>'=>'<pre>', '</span>' => '<span name="php">') as $k => $v) {
		$p = 0;
		while (($p = strpos($text_l, $v, $p)) !== false) {
			if (($e = strpos($text_l, $k, $p)) === false) {
				$p += 5;
				continue;
			}
			$text_l = substr_replace($text_l, str_repeat(' ', $e - $p), $p, ($e - $p));
			$p = $e;
		}
	}

	foreach ($SML_REPL as $k => $v) {
		$a = 0;
		$len = strlen($k);
		while (($a = strpos($text_l, $k, $a)) !== false) {
			if ((!$a || isset($GLOBALS['__SML_CHR_CHK__'][$text_l[$a - 1]])) && ((@$ch = $text_l[$a + $len]) == '' || isset($GLOBALS['__SML_CHR_CHK__'][$ch]))) {
				$text_l = substr_replace($text_l, $v, $a, $len);
				$text = substr_replace($text, $v, $a, $len);
				$a += strlen($v) - $len;
			} else {
				$a += $len;
			}
		}
	}

	return $text;
}

function post_to_smiley($text)
{
	/* include once since draw_post_smiley_cntrl() may use it too */
	include_once $GLOBALS['FORUM_SETTINGS_PATH'].'ps_cache';
	if (isset($PS_SRC)) {
		$GLOBALS['PS_SRC'] = $PS_SRC;
		$GLOBALS['PS_DST'] = $PS_DST;
	} else {
		$PS_SRC = $GLOBALS['PS_SRC'];
		$PS_DST = $GLOBALS['PS_DST'];
	}

	/* check for emoticons */
	foreach ($PS_SRC as $k => $v) {
		if (strpos($text, $v) === false) {
			unset($PS_SRC[$k], $PS_DST[$k]);
		}
	}

	return $PS_SRC ? str_replace($PS_SRC, $PS_DST, $text) : $text;
}$GLOBALS['__error__'] = 0;
$GLOBALS['__err_msg__'] = array();

function set_err($err, $msg)
{
	$GLOBALS['__err_msg__'][$err] = $msg;
	$GLOBALS['__error__'] = 1;
}

function is_post_error()
{
	return $GLOBALS['__error__'];
}

function get_err($err, $br=0)
{
	if (isset($err, $GLOBALS['__err_msg__'][$err])) {
		return ($br ? '<span class="ErrorText">'.$GLOBALS['__err_msg__'][$err].'</span><br />' : '<br /><span class="ErrorText">'.$GLOBALS['__err_msg__'][$err].'</span>');
	}
}

function post_check_images()
{
	if (!empty($_POST['msg_body']) && $GLOBALS['MAX_IMAGE_COUNT'] && $GLOBALS['MAX_IMAGE_COUNT'] < count_images((string)$_POST['msg_body'])) {
		return -1;
	}

	return 0;
}

function check_post_form()
{
	/* Make sure we got a valid subject. */
	if (!strlen(trim((string)$_POST['msg_subject']))) {
		set_err('msg_subject', 'Указание темы является обязательным');
	}

	/* Make sure the number of images [img] inside the body do not exceed the allowed limit. */
	if (post_check_images()) {
		set_err('msg_body', 'Пожалуйста сократите количество картинок в сообщении до максимально допустимого, которое составляет '.$GLOBALS['MAX_IMAGE_COUNT']);
	}

	/* Captcha check for anon users. */
	if (!_uid && $GLOBALS['FUD_OPT_3'] & 8192 ) {
		if (empty($_POST['turing_test']) || empty($_POST['turing_res']) || !test_turing_answer($_POST['turing_test'], $_POST['turing_res'])) {
			set_err('reg_turing', 'Invalid validation code.');
		}
	}

	if (defined('fud_bad_sq')) {
		unset($_POST['submitted']);
		set_err('msg_session', '<h4 class="ErrorText ac">Ваш сеанс работы истек, пожалуйста, отправьте форму заново. Приносим свои извинения за неудобство.</h4>');
	}

	/* Check for duplicate topics (exclude replies and edits). */
	if (($GLOBALS['FUD_OPT_3'] & 67108864) && $_POST['reply_to'] == 0 && $_POST['msg_id'] == 0) {
		$c = q_singleval('SELECT count(*) FROM fud26_msg WHERE subject='. _esc($_POST['msg_subject']) .' AND reply_to=0 AND poster_id='. _uid .' AND post_stamp >= '. (__request_timestamp__ - 86400));
		if ( $c > 0 ) {
			set_err('msg_body', 'Пожалуйста, не создавайте дубликатов тем.');
		}
	}

	/* Check against minimum post length. */
	if ($GLOBALS['POST_MIN_LEN']) {
		$body_without_bbcode = preg_replace('/\[(.*?)\]|\s+/', '', $_POST['msg_body']);	// Remove tags and whitespace.
		if (strlen($body_without_bbcode) < $GLOBALS['POST_MIN_LEN']) {
			$post_min_len = $GLOBALS['POST_MIN_LEN'];
			set_err('msg_body', 'Ваше сообщение слишком коротко. Минимальный размер составляет '.convertPlural($post_min_len, array(''.$post_min_len.' символ',''.$post_min_len.' символа',''.$post_min_len.' символов')).'.');
		}
		unset($body_without_bbcode);
	}

	/* Check if user is allowed to post links. */
	if ($GLOBALS['POSTS_BEFORE_LINKS'] && !empty($_POST['msg_body'])) {
		if (preg_match('?(\[url)|(http://)|(https://)?i', $_POST['msg_body'])) {
			$c = q_singleval('SELECT posted_msg_count FROM fud26_users WHERE id='. _uid);
			if ( $GLOBALS['POSTS_BEFORE_LINKS'] > $c ) {
				$posts_before_links = $GLOBALS['POSTS_BEFORE_LINKS'];
				set_err('msg_body', 'Вы не можете использовать ссылки, пока вами отправлено менее '.convertPlural($posts_before_links, array(''.$posts_before_links.' сообщения',''.$posts_before_links.' сообщений',''.$posts_before_links.' сообщений')).'.');
			}
		}
	}

	return $GLOBALS['__error__'];
}

function check_ppost_form($msg_subject)
{
	if (!strlen(trim($msg_subject))) {
		set_err('msg_subject', 'Указание темы является обязательным');
	}

	if (post_check_images()) {
		set_err('msg_body', 'Пожалуйста сократите количество картинок в сообщении до максимально допустимого, которое составляет '.$GLOBALS['MAX_IMAGE_COUNT']);
	}

	if (empty($_POST['msg_to_list'])) {
		set_err('msg_to_list', 'Невозможно послать сообщение если не указан получатель');
	} else {
		$GLOBALS['recv_user_id'] = array();
		/* Hack for login names containing HTML entities ex. &#123; */
		if (($hack = strpos($_POST['msg_to_list'], '&#')) !== false) {
			$hack_str = preg_replace('!&#([0-9]+);!', '&#\1#', $_POST['msg_to_list']);
		} else {
			$hack_str = $_POST['msg_to_list'];
		}
		foreach(explode(';', $hack_str) as $v) {
			$v = trim($v);
			if (strlen($v)) {
				if ($hack !== false) {
					$v = preg_replace('!&#([0-9]+)#!', '&#\1;', $v);
				}
				if (!($obj = db_sab('SELECT u.users_opt, u.id, ui.ignore_id FROM fud26_users u LEFT JOIN fud26_user_ignore ui ON ui.user_id=u.id AND ui.ignore_id='. _uid .' WHERE u.alias='. ssn(char_fix(htmlspecialchars($v)))))) {
					set_err('msg_to_list', 'В этом форуме нет участника с именем "'.char_fix(htmlspecialchars($v)).'"');
					break;
				}
				if (!empty($obj->ignore_id)) {
					set_err('msg_to_list', 'Вы не можете отправить личное сообщение для "'.char_fix(htmlspecialchars($v)).'", поскольку этот пользователь вас игнорирует.');
					break;
				} else if (!($obj->users_opt & 32) && !$GLOBALS['is_a']) {
					set_err('msg_to_list', 'Вы не можете отправить личное сообщение для "'.htmlspecialchars($v).'", потому что этот участник запретил прием личных сообщений.');
					break;
				} else {
					$GLOBALS['recv_user_id'][] = $obj->id;
				}
			}
		}
	}

	if (defined('fud_bad_sq')) {
		unset($_POST['btn_action']);
		set_err('msg_session', '<h4 class="ErrorText ac">Ваш сеанс работы истек, пожалуйста, отправьте форму заново. Приносим свои извинения за неудобство.</h4>');
	}

	return $GLOBALS['__error__'];
}

function check_femail_form()
{
	if (empty($_POST['femail']) || validate_email($_POST['femail'])) {
		set_err('femail', 'Пожалуйста введите правильный адрес e-mail.');
	}
	if (empty($_POST['subj'])) {
		set_err('subj', 'Невозможно послать сообщение если не указана тема.');
	}
	if (empty($_POST['body'])) {
		set_err('body', 'Невозможно послать сообщение если текст сообщения отсутствует.');
	}
	if (defined('fud_bad_sq')) {
		unset($_POST['posted']);
		set_err('msg_session', '<h4 class="ErrorText ac">Ваш сеанс работы истек, пожалуйста, отправьте форму заново. Приносим свои извинения за неудобство.</h4>');
	}

	return $GLOBALS['__error__'];
}

function count_images($text)
{
	$text = strtolower($text);
	$a = substr_count($text, '[img]');
	$b = substr_count($text, '[/img]');

	return (($a > $b) ? $b : $a);
}function init_spell($type, $dict)
{
	$pspell_config = pspell_config_create($dict);
	pspell_config_mode($pspell_config, $type);
	pspell_config_personal($pspell_config, $GLOBALS['FORUM_SETTINGS_PATH'] .'forum.pws');
	pspell_config_ignore($pspell_config, 2);
	define('__FUD_PSPELL_LINK__', pspell_new_config($pspell_config));

	return true;
}

function tokenize_string($data)
{
	if (!($len = strlen($data))) {
		return array();
	}
	$wa = array();

	$i = $p = 0;
	$seps = array(','=>1,' '=>1,'/'=>1,'\\'=>1,'.'=>1,','=>1,'!'=>1,'>'=>1,'?'=>1,"\n"=>1,"\r"=>1,"\t"=>1,')'=>1,'('=>1,'}'=>1,'{'=>1,'['=>1,']'=>1,'*'=>1,';'=>1,'='=>1,':'=>1,'1'=>1,'2'=>1,'3'=>1,'4'=>1,'5'=>1,'6'=>1,'7'=>1,'8'=>1,'9'=>1,'0'=>1);

	while ($i < $len) {
		if (isset($seps[$data{$i}])) {
			if (isset($str)) {
				$wa[] = array('token'=>$str, 'check'=>1);
				unset($str);
			}
			$wa[] = array('token'=>$data[$i], 'check'=>0);
		} else if ($data{$i} == '<') {
			if (($p = strpos($data, '>', $i)) !== false) {
				if (isset($str)) {
					$wa[] = array('token'=>$str, 'check'=>1);
					unset($str);
				}

				$wrd = substr($data,$i,($p-$i)+1);
				$p3 = $l = null;

				/* remove code blocks */
				if ($wrd == '<pre>') {
					$l = 'pre';
					
				/* Deal with bad old style quotes - remove in future release. */
				} else if ($wrd == '<table border="0" align="center" width="90%" cellpadding="3" cellspacing="1">') {
					$l = 1;
					$p3 = $p;

					while ($l > 0) {
						$p3 = strpos($data, 'table', $p3);

						if ($data[$p3-1] == '<') {
							$l++;
						} else if ($data[$p3-1] == '/' && $data[$p3-2] == '<') {
							$l--;
						}

						$p3 = strpos($data, '>', $p3);
					}
					
				/* Remove new style quotes. */
				} else if ($wrd == '<blockquote>') {
					$l = 1;
					$p3 = $p;

					while ($l > 0) {
						$p3 = strpos($data, 'blockquote', $p3);

						if ($data[$p3-1] == '<') {
							$l++;
						} else if ($data[$p3-1] == '/' && $data[$p3-2] == '<') {
							$l--;
						}

						$p3 = strpos($data, '>', $p3);
					}
				}

				if ($p3) {
					$p = $p3;
					$wrd = substr($data, $i, ($p-$i)+1);
				} else if ($l && ($p2 = strpos($data, '</'.$l.'>', $p))) {
					$p = $p2+1+strlen($l)+1;
					$wrd = substr($data,$i,($p-$i)+1);
				}

				$wa[] = array('token'=>$wrd, 'check'=>0);
				$i = $p;
			} else {
				$str .= $data[$i];
			}
		} else if ($data{$i} == '&') {
			if (isset($str)) {
				$wa[] = array('token'=>$str, 'check'=>1);
				unset($str);
			}

			$regs = array();
			if (preg_match('!(\&[A-Za-z0-9]{2,5}\;)!', substr($data,$i,6), $regs)) {
				$wa[] = array('token'=>$regs[1], 'check'=>0);
				$i += strlen($regs[1])-1;
			} else {
				$wa[] = array('token'=>$data[$i], 'check'=>0);
			}
		} else if (isset($str)) {
			$str .= $data[$i];
		} else {
			$str = $data[$i];
		}
		$i++;
	}

	if (isset($str)) {
		$wa[] = array('token'=>$str, 'check'=>1);
	}

	return $wa;
}

function draw_spell_sug_select($v,$k,$type)
{
	$sel_name = 'spell_chk_'. $type .'_'. $k;
	$data = '<select name="'. $sel_name .'">';
	$data .= '<option value="'. htmlspecialchars($v['token']) .'">'. htmlspecialchars($v['token']) .'</option>';
	$i = 0;
	foreach(pspell_suggest(__FUD_PSPELL_LINK__, $v['token']) as $va) {
		$data .= '<option value="'. $va .'">'. ++$i .') '. $va .'</option>';
	}

	if (!$i) {
		$data .= '<option value="">нет вариантов</option>';
	}

	$data .= '</select>';

	return $data;
}

function spell_replace($wa,$type)
{
	$data = '';

	foreach($wa as $k => $v) {
		if( $v['check']==1 && isset($_POST['spell_chk_'. $type .'_'. $k]) && strlen($_POST['spell_chk_'. $type .'_'. $k])) {
			$data .= $_POST['spell_chk_'. $type .'_'. $k];
		} else {
			$data .= $v['token'];
		}
	}

	return $data;
}

function spell_check_ar($wa,$type)
{
	foreach($wa as $k => $v) {
		if ($v['check'] > 0 && !pspell_check(__FUD_PSPELL_LINK__, $v['token'])) {
			$wa[$k]['token'] = draw_spell_sug_select($v, $k, $type);
		}
	}

	return $wa;
}

function reasemble_string($wa)
{
	$data = '';
	foreach($wa as $v) {
		$data .= $v['token'];
	}

	return $data;
}

function check_data_spell($data, $type, $dict)
{
	if (!$data || (!defined('__FUD_PSPELL_LINK__') && !init_spell(PSPELL_FAST, $dict))) {
		return $data;
	}

	return reasemble_string(spell_check_ar(tokenize_string($data), $type));
}function fud_wrap_tok($data)
{
	$wa = array();
	$len = strlen($data);

	$i=$j=$p=0;
	$str = '';
	while ($i < $len) {
		switch ($data{$i}) {
			case ' ':
			case "\n":
			case "\t":
				if ($j) {
					$wa[] = array('word'=>$str, 'len'=>($j+1));
					$j=0;
					$str ='';
				}

				$wa[] = array('word'=>$data[$i]);

				break;
			case '<':
				if (($p = strpos($data, '>', $i)) !== false) {
					if ($j) {
						$wa[] = array('word'=>$str, 'len'=>($j+1));
						$j=0;
						$str ='';
					}
					$s = substr($data, $i, ($p - $i) + 1);
					if ($s == '<pre>') {
						$s = substr($data, $i, ($p = (strpos($data, '</pre>', $p) + 6)) - $i);
						--$p;
					} else if ($s == '<span name="php">') {
						$s = substr($data, $i, ($p = (strpos($data, '</span>', $p) + 7)) - $i);
						--$p;
					}

					$wa[] = array('word' => $s);

					$i = $p;
					$j = 0;
				} else {
					$str .= $data[$i];
					$j++;
				}
				break;

			case '&':
				if (($e = strpos($data, ';', $i))) {
					$st = substr($data, $i, ($e - $i + 1));
					if (($st{1} == '#' && is_numeric(substr($st, 3, -1))) || !strcmp($st, '&nbsp;') || !strcmp($st, '&gt;') || !strcmp($st, '&lt;') || !strcmp($st, '&quot;')) {
						if ($j) {
							$wa[] = array('word'=>$str, 'len'=>($j+1));
							$j=0;
							$str ='';
						}

						$wa[] = array('word' => $st, 'sp' => 1);
						$i=$e;
						$j=0;
						break;
					}
				} /* fall through */
			default:
				$str .= $data[$i];
				$j++;
		}
		$i++;
	}

	if ($j) {
		$wa[] = array('word'=>$str, 'len'=>($j+1));
	}

	return $wa;
}

/* Wrap messages by inserting a space into strings longer the spesified length. */
function fud_wordwrap(&$data)
{
	$m = (int) $GLOBALS['WORD_WRAP'];
	if (!$m || $m >= strlen($data)) {
		return;
	}

	$wa = fud_wrap_tok($data);
	$l = 0;
	$data = '';
	foreach($wa as $v) {
		if (isset($v['len']) && $v['len'] > $m) {
			if ($v['len'] + $l > $m) {
				$l = 0;
				$data .= ' ';
			}
			$data .= wordwrap($v['word'], $m, ' ', 1);
			$l += $v['len'];
		} else {
			if (isset($v['sp'])) {
				if ($l > $m) {
					$data .= ' ';
					$l = 0;
				}
				++$l;
			} else if (!isset($v['len'])) {
				$l = 0;
			} else {
				$l += $v['len'];
			}
			$data .= $v['word'];
		}
	}
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
}function tmpl_post_options($arg, $perms=0)
{
	$post_opt_html		= '<b>HTML</b>  - <b>выключен</b>';
	$post_opt_fud		= '<b>BBcode</b> - <b>выключен</b>';
	$post_opt_images 	= '<b>Картинки</b> - <b>выключены</b>';
	$post_opt_smilies	= '<b>Смайлики</b> - <b>выключены</b>';
	$edit_time_limit	= '';

	if (is_int($arg)) {
		if ($arg & 16) {
			$post_opt_fud = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank"><b>BBcode</b> - <b>включен</b></a>';
		} else if (!($arg & 8)) {
			$post_opt_html = '<b>HTML</b> - <b>включен</b>';
		}
		if ($perms & 16384) {
			$post_opt_smilies = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>Смайлики</b> - <b>включен</b></a>';
		}
		if ($perms & 32768) {
			$post_opt_images = '<b>Картинки</b> - <b>включены</b>';
		}
		if ($GLOBALS['EDIT_TIME_LIMIT'] >= 0) {	// Time limit enabled,
			$edit_time_limit = $GLOBALS['EDIT_TIME_LIMIT'] ? '<br /><b>Период возможности редактирования</b>: <b>'.$GLOBALS['EDIT_TIME_LIMIT'].'</b> мин.' : '<br /><b>Период возможности редактирования</b>: <b>Неограниченно</b>';
		}
	} else if ($arg == 'private') {
		$o =& $GLOBALS['FUD_OPT_1'];

		if ($o & 4096) {
			$post_opt_fud = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank"><b>BBcode</b> - <b>включен</b></a>';
		} else if (!($o & 2048)) {
			$post_opt_html = '<b>HTML</b> - <b>включен</b>';
		}
		if ($o & 16384) {
			$post_opt_images = '<b>Картинки</b> - <b>включены</b>';
		}
		if ($o & 8192) {
			$post_opt_smilies = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>Смайлики</b> - <b>включен</b></a>';
		}
	} else if ($arg == 'sig') {
		$o =& $GLOBALS['FUD_OPT_1'];

		if ($o & 131072) {
			$post_opt_fud = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank"><b>BBcode</b> - <b>включен</b></a>';
		} else if (!($o & 65536)) {
			$post_opt_html = '<b>HTML</b> - <b>включен</b>';
		}
		if ($o & 524288) {
			$post_opt_images = '<b>Картинки</b> - <b>включены</b>';
		}
		if ($o & 262144) {
			$post_opt_smilies = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>Смайлики</b> - <b>включен</b></a>';
		}
	}

	return '<span class="SmallText"><b>Параметры форума</b><br />
'.$post_opt_html.'<br />
'.$post_opt_fud.'<br />
'.$post_opt_images.'<br />
'.$post_opt_smilies.$edit_time_limit.'</span>';
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
}$GLOBALS['seps'] = array(' '=>' ', "\n"=>"\n", "\r"=>"\r", '\''=>'\'', '"'=>'"', '['=>'[', ']'=>']', '('=>'(', ';'=>';', ')'=>')', "\t"=>"\t", '='=>'=', '>'=>'>', '<'=>'<');

function fud_substr_replace($str, $newstr, $pos, $len)
{
        return substr($str, 0, $pos) . $newstr . substr($str, $pos+$len);
}

function url_check($url)
{
	$url = preg_replace('!\s+!', '', $url);

	if (strpos($url, '&amp;#') !== false) {
		return preg_replace('!&#([0-9]{2,3});!e', "chr(\\1)", char_fix($url));
	}
	return $url;
}

function tags_to_html($str, $allow_img=1, $no_char=0)
{
	if (!$no_char) {
		$str = htmlspecialchars($str);
	}

	$str = nl2br($str);

	$ostr = '';
	$pos = $old_pos = 0;

	// Call all BBcode to HTML conversion plugins.
	if (defined('plugins')) {
		list($str) = plugin_call_hook('BBCODE2HTML', array($str));
	}

	while (($pos = strpos($str, '[', $pos)) !== false) {
		if (isset($str[$pos + 1], $GLOBALS['seps'][$str[$pos + 1]])) {
			++$pos;
			continue;
		}

		if (($epos = strpos($str, ']', $pos)) === false) {
			break;
		}
		if (!($epos-$pos-1)) {
			$pos = $epos + 1;
			continue;
		}
		$tag = substr($str, $pos+1, $epos-$pos-1);
		if (($pparms = strpos($tag, '=')) !== false) {
			$parms = substr($tag, $pparms+1);
			if (!$pparms) { /*[= exception */
				$pos = $epos+1;
				continue;
			}
			$tag = substr($tag, 0, $pparms);
		} else {
			$parms = '';
		}

		if (!$parms && ($tpos = strpos($tag, '[')) !== false) {
			$pos += $tpos;
			continue;
		}
		$tag = strtolower($tag);

		switch ($tag) {
			case 'quote title':
				$tag = 'quote';
				break;
			case 'list type':
				$tag = 'list';
				break;
			case 'hr':
				$str{$pos} = '<';
				$str{$pos+1} = 'h';
				$str{$pos+2} = 'r';
				$str{$epos} = '>';
				continue 2;
		}

		if ($tag[0] == '/') {
			if (isset($end_tag[$pos])) {
				if( ($pos-$old_pos) ) $ostr .= substr($str, $old_pos, $pos-$old_pos);
				$ostr .= $end_tag[$pos];
				$pos = $old_pos = $epos+1;
			} else {
				$pos = $epos+1;
			}

			continue;
		}

		$cpos = $epos;
		$ctag = '[/'. $tag .']';
		$ctag_l = strlen($ctag);
		$otag = '['. $tag;
		$otag_l = strlen($otag);
		$rf = 1;
		$nt_tag = 0;
		while (($cpos = strpos($str, '[', $cpos)) !== false) {
			if (isset($end_tag[$cpos]) || isset($GLOBALS['seps'][$str[$cpos + 1]])) {
				++$cpos;
				continue;
			}

			if (($cepos = strpos($str, ']', $cpos)) === false) {
				if (!$nt_tag) {
					break 2;
				} else {
					break;
				}
			}

			if (strcasecmp(substr($str, $cpos, $ctag_l), $ctag) == 0) {
				--$rf;
			} else if (strcasecmp(substr($str, $cpos, $otag_l), $otag) == 0) {
				++$rf;
			} else {
				$nt_tag++;
				++$cpos;
				continue;
			}

			if (!$rf) {
				break;
			}
			$cpos = $cepos;
		}

		if (!$cpos || ($rf && $str[$cpos] == '<')) { /* Left over [ handler. */
			++$pos;
			continue;
		}

		if ($cpos !== false) {
			if (($pos-$old_pos)) {
				$ostr .= substr($str, $old_pos, $pos-$old_pos);
			}
			switch ($tag) {
				case 'notag':
					$ostr .= '<span name="notag">'. substr($str, $epos+1, $cpos-1-$epos) .'</span>';
					$epos = $cepos;
					break;
				case 'url':
					if (!$parms) {
						$url = substr($str, $epos+1, ($cpos-$epos)-1);
					} else {
						$url = $parms;
					}

					$url = url_check($url);
					$url = str_replace('&quot;', '', $url); // Remove quotes from URL.

					if (!strncasecmp($url, 'www.', 4)) {
						$url = 'http&#58;&#47;&#47;'. $url;
					} else if (strpos(strtolower($url), 'script:') !== false) {
						$ostr .= substr($str, $pos, $cepos - $pos + 1);
						$epos = $cepos;
						$str[$cpos] = '<';
						break;
					} else {
						$url = str_replace('://', '&#58;&#47;&#47;', $url);
					}

					if ( strtolower(substr($str, $epos+1, 6)) == '[/url]' ) {
						$end_tag[$cpos] = $url .'</a>';  // Fill empty link.
					} else {
						$end_tag[$cpos] = '</a>';
					}
					$ostr .= '<a href="'. $url .'" target="_blank">';
					break;
				case 'i':
				case 'u':
				case 'b':
				case 's':
				case 'sub':
				case 'sup':
				case 'del':
					$end_tag[$cpos] = '</'. $tag .'>';
					$ostr .= '<'. $tag .'>';
					break;
				case 'h1':
				case 'h2':
				case 'h3':
				case 'h4':
					$end_tag[$cpos] = '</'.$tag.'>';
					$ostr .= '<'.$tag.'>';
					break;
				case 'email':
					if (!$parms) {
						$parms = str_replace('@', '&#64;', substr($str, $epos+1, ($cpos-$epos)-1));
						$ostr .= '<a href="mailto:'. $parms .'" target="_blank">'. $parms .'</a>';
						$epos = $cepos;
						$str[$cpos] = '<';
					} else {
						$end_tag[$cpos] = '</a>';
						$ostr .= '<a href="mailto:'. str_replace('@', '&#64;', $parms) .'" target="_blank">';
					}
					break;
				case 'color':
				case 'size':
				case 'font':
					if ($tag == 'font') {
						$tag = 'face';
					}
					$end_tag[$cpos] = '</font>';
					$ostr .= '<font '. $tag .'="'. $parms .'">';
					break;
				case 'code':
					$param = substr($str, $epos+1, ($cpos-$epos)-1);

					$ostr .= '<div class="pre"><pre>'. reverse_nl2br($param) .'</pre></div>';
					$epos = $cepos;
					$str[$cpos] = '<';
					break;
				case 'pre':
					$param = substr($str, $epos+1, ($cpos-$epos)-1);

					$ostr .= '<pre>'. reverse_nl2br($param) .'</pre>';
					$epos = $cepos;
					$str[$cpos] = '<';
					break;
				case 'php':
					$param = trim(reverse_fmt(reverse_nl2br(substr($str, $epos+1, ($cpos-$epos)-1))));

					if (strncmp($param, '<?php', 5)) {
						if (strncmp($param, '<?', 2)) {
							$param = "<?php\n". $param;
						} else {
							$param = "<?php\n". substr($param, 3);
						}
					}
					if (substr($param, -2) != '?>') {
						$param .= "\n?>";
					}

					$ostr .= '<SPAN name="php">'. trim(@highlight_string($param, true)) .'</SPAN>';
					$epos = $cepos;
					$str[$cpos] = '<';
					break;
				case 'img':
				case 'imgl':
				case 'imgr':
					if (!$allow_img) {
						$ostr .= substr($str, $pos, ($cepos-$pos)+1);
					} else {
						$class = ($tag == 'img') ? '' : 'class="'. $tag{3} .'" ';

						if (!$parms) {
							$parms = substr($str, $epos+1, ($cpos-$epos)-1);
							if (strpos(strtolower(url_check($parms)), 'script:') === false) {
								$ostr .= '<img '. $class .'src="'. $parms .'" border="0" alt="'. $parms .'" />';
							} else {
								$ostr .= substr($str, $pos, ($cepos-$pos)+1);
							}
						} else {
							if (strpos(strtolower(url_check($parms)), 'script:') === false) {
								$ostr .= '<img '. $class .'src="'. $parms .'" border="0" alt="'. substr($str, $epos+1, ($cpos-$epos)-1) .'" />';
							} else {
								$ostr .= substr($str, $pos, ($cepos-$pos)+1);
							}
						}
					}
					$epos = $cepos;
					$str[$cpos] = '<';
					break;
				case 'quote':
					if (!$parms) {
						$parms = 'Цитата:';
					} else {
						$parms = str_replace(array('@', ':'), array('&#64;', '&#58;'), $parms);
					}
					$ostr .= '<cite>'.$parms.'</cite><blockquote>';
					$end_tag[$cpos] = '</blockquote>';
					break;
				case 'align':
					$end_tag[$cpos] = '</div>';
					$ostr .= '<div align="'. $parms .'">';
					break;
				case 'list':
					$tmp = substr($str, $epos, ($cpos-$epos));
					$tmp_l = strlen($tmp);
					$tmp2 = str_replace('[*]', '<li>', $tmp);
					$tmp2_l = strlen($tmp2);
					$str = str_replace($tmp, $tmp2, $str);

					$diff = $tmp2_l - $tmp_l;
					$cpos += $diff;

					if (isset($end_tag)) {
						foreach($end_tag as $key => $val) {
							if ($key < $epos) {
								continue;
							}

							$end_tag[$key+$diff] = $val;
						}
					}

					switch (strtolower($parms)) {
						case '1':
						case 'decimal':
						case 'a':
							$end_tag[$cpos] = '</ol>';
							$ostr .= '<ol type="'. $parms .'">';
							break;
						case 'square':
						case 'circle':
						case 'disc':
							$end_tag[$cpos] = '</ul>';
							$ostr .= '<ul type="'. $parms .'">';
							break;
						default:
							$end_tag[$cpos] = '</ul>';
							$ostr .= '<ul>';
					}
					break;
				case 'spoiler':
					$rnd = rand();
					$end_tag[$cpos] = '</div></div>';
					$ostr .= '<div class="dashed" style="padding: 3px;" align="center"><a href="javascript://" onclick="javascript: layerVis(\'s'. $rnd .'\', 1);">'
						.($parms ? $parms : 'Показать скрытый текст') .'</a><div align="left" id="s'. $rnd .'" style="display: none;">';
					break;
				case 'acronym':
					$end_tag[$cpos] = '</acronym>';
					$ostr .= '<acronym title="'. ($parms ? $parms : ' ') .'">';
					break;
				case 'wikipedia':
					$end_tag[$cpos] = '</a>';
					$url = substr($str, $epos+1, ($cpos-$epos)-1);
					if ($parms && preg_match('!^[A-Za-z]+$!', $parms)) {
						$parms .= '.';
					} else {
						$parms = '';
					}
					$ostr .= '<a href="http://'. $parms .'wikipedia.com/wiki/'. $url .'" name="WikiPediaLink" target="_blank">';
					break;
			}

			$str[$pos] = '<';
			$pos = $old_pos = $epos+1;
		} else {
			$pos = $epos+1;
		}
	}
	$ostr .= substr($str, $old_pos, strlen($str)-$old_pos);

	/* URL paser. */
	$pos = 0;
	$ppos = 0;
	while (($pos = @strpos($ostr, '://', $pos)) !== false) {
		if ($pos < $ppos) {
			break;
		}
		// Check if it's inside any tag.
		$i = $pos;
		while (--$i && $i > $ppos) {
			if ($ostr[$i] == '>' || $ostr[$i] == '<') {
				break;
			}
		}
		if (!$pos || $ostr[$i] == '<') {
			$pos += 3;
			continue;
		}

		// Check if it's inside the a tag.
		if (($ts = strpos($ostr, '<a ', $pos)) === false) {
			$ts = strlen($ostr);
		}
		if (($te = strpos($ostr, '</a>', $pos)) == false) {
			$te = strlen($ostr);
		}
		if ($te < $ts) {
			$ppos = $pos += 3;
			continue;
		}

		// Check if it's inside the PRE tag.
		if (($ts = strpos($ostr, '<pre>', $pos)) === false) {
			$ts = strlen($ostr);
		}
		if (($te = strpos($ostr, '</pre>', $pos)) == false) {
			$te = strlen($ostr);
		}
		if ($te < $ts) {
			$ppos = $pos += 3;
			continue;
		}

		// Check if it's inside the SPAN tag
		if (($ts = strpos($ostr, '<span>', $pos)) === false) {
			$ts = strlen($ostr);
		}
		if (($te = strpos($ostr, '</span>', $pos)) == false) {
			$te = strlen($ostr);
		}
		if ($te < $ts) {
			$ppos = $pos += 3;
			continue;
		}

		$us = $pos;
		$l = strlen($ostr);
		while (1) {
			--$us;
			if ($ppos > $us || $us >= $l || isset($GLOBALS['seps'][$ostr[$us]])) {
				break;
			}
		}

		unset($GLOBALS['seps']['=']);
		$ue = $pos;
		while (1) {
			++$ue;
			if ($ue >= $l || isset($GLOBALS['seps'][$ostr[$ue]])) {
				break;
			}

			if ($ostr[$ue] == '&') {
				if ($ostr[$ue+4] == ';') {
					$ue += 4;
					continue;
				}
				if ($ostr[$ue+3] == ';' || $ostr[$ue+5] == ';') {
					break;
				}
			}

			if ($ue >= $l || isset($GLOBALS['seps'][$ostr[$ue]])) {
				break;
			}
		}
		$GLOBALS['seps']['='] = '=';

		$url = url_check(substr($ostr, $us+1, $ue-$us-1));
		if (strpos($url, 'script', strlen('script')) !== false || ($ue - $us - 1) < 9) {
			$pos = $ue;
			continue;
		}
		$html_url = '<a href="'. $url .'" target="_blank">'. $url .'</a>';
		$html_url_l = strlen($html_url);
		$ostr = fud_substr_replace($ostr, $html_url, $us+1, $ue-$us-1);
		$ppos = $pos;
		$pos = $us+$html_url_l;
	}

	/* E-mail parser. */
	$pos = 0;
	$ppos = 0;

	$er = array_flip(array_merge(range(0,9), range('A', 'Z'), range('a','z'), array('.', '-', '\'', '_')));

	while (($pos = @strpos($ostr, '@', $pos)) !== false) {
		if ($pos < $ppos) {
			break;
		}

		// Check if it's inside any tag.
		$i = $pos;
		while (--$i && $i>$ppos) {
			if ( $ostr[$i] == '>' || $ostr[$i] == '<') {
				break;
			}
		}
		if ($i < 0 || $ostr[$i]=='<') {
			++$pos;
			continue;
		}


		// Check if it's inside the a tag.
		if (($ts = strpos($ostr, '<a ', $pos)) === false) {
			$ts = strlen($ostr);
		}
		if (($te = strpos($ostr, '</a>', $pos)) == false) {
			$te = strlen($ostr);
		}
		if ($te < $ts) {
			$ppos = $pos += 1;
			continue;
		}

		// Check if it's inside the PRE tag.
		if (($ts = strpos($ostr, '<div class="pre"><pre>', $pos)) === false) {
			$ts = strlen($ostr);
		}
		if (($te = strpos($ostr, '</pre></div>', $pos)) == false) {
			$te = strlen($ostr);
		}
		if ($te < $ts) {
			$ppos = $pos += 1;
			continue;
		}

		for ($es = ($pos - 1); $es > ($ppos - 1); $es--) {
			if (isset($er[ $ostr[$es] ])) continue;
			++$es;
			break;
		}
		if ($es == $pos) {
			$ppos = $pos += 1;
			continue;
		}
		if ($es < 0) {
			$es = 0;
		}

		for ($ee = ($pos + 1); @isset($ostr[$ee]); $ee++) {
			if (isset($er[ $ostr[$ee] ])) continue;
			break;
		}
		if ($ee == ($pos+1)) {
			$ppos = $pos += 1;
			continue;
		}

		$email = str_replace('@', '&#64;', substr($ostr, $es, $ee-$es));
		if (strpos( substr($email, 1, -1), '.') === false) {	// E-mail mostly have dots in them.
			$ppos = $pos += 1; continue;
		}
		$email_url = '<a href="mailto:'. $email .'" target="_blank">'. $email .'</a>';
		$email_url_l = strlen($email_url);
		$ostr = fud_substr_replace($ostr, $email_url, $es, $ee-$es);
		$ppos =	$es+$email_url_l;
		$pos = $ppos;
	}

	return $ostr;
}

function html_to_tags($fudml)
{
	// Call all HTML to BBcode conversion plugins.
	if (defined('plugins')) {
		list($fudml) = plugin_call_hook('HTML2BBCODE', array($fudml));
	}

	// PHP code blocks.
	while (preg_match('!<span name="php">(.*?)</span>!is', $fudml, $res)) {
		$tmp = trim(html_entity_decode(strip_tags(str_replace('<br />', "\n", $res[1]))));
		$m = md5($tmp);
		$php[$m] = $tmp;
		$fudml = str_replace($res[0], "[php]\n". $m ."\n[/php]", $fudml);
	}

	// Wikipedia tags.
	while (preg_match('!<a href="http://(?:([A-ZA-z]+)?\.)?wikipedia.com/wiki/([^"]+)"( target="_blank")? name="WikiPediaLink">(.*?)</a>!s', $fudml, $res)) {
		if (count($res) == 5) {
			$fudml = str_replace($res[0], '[wikipedia='. $res[1] .']'. $res[2] .'[/wikipedia]', $fudml);
		} else {
			$fudml = str_replace($res[0], '[wikipedia]'. $res[2] .'[/wikipedia]', $fudml);
		}
	}

	// Quote tags.
	if (strpos($fudml, '<cite>') !== false) {
               $fudml = str_replace(array('<cite>','</cite><blockquote>','</blockquote>'), array('[quote title=', ']', '[/quote]'), $fudml);
	}
	// Old bad quote tags.
	if (preg_match('!class="quote"!', $fudml)) { 
		$fudml = preg_replace('!<table border="0" align="center" width="90%" cellpadding="3" cellspacing="1">(<tbody>)?<tr><td class="SmallText"><b>!', '[quote title=', $fudml);
		$fudml = preg_replace('!</b></td></tr><tr><td class="quote">(<br>)?!', ']', $fudml);
		$fudml = preg_replace('!(<br>)?</td></tr>(</tbody>)?</table>!', '[/quote]', $fudml);
	}

	/* Spoiler tags. */	
	if (preg_match('!<div class="dashed" style="padding: 3px;" align="center"( width="100%")?><a href="javascript://" OnClick="javascript: layerVis\(\'.*?\', 1\);">.*?</a><div align="left" id="(.*?)" style="display: none;">!is', $fudml)) {
		$fudml = preg_replace('!\<div class\="dashed" style\="padding: 3px;" align\="center"( width\="100%")?\>\<a href\="javascript://" OnClick\="javascript: layerVis\(\'.*?\', 1\);">(.*?)\</a\>\<div align\="left" id\=".*?" style\="display: none;"\>!is', '[spoiler=\2]', $fudml);
		$fudml = str_replace('</div></div>', '[/spoiler]', $fudml);
	}
	/* Old bad spoiler format. */
	if (preg_match('!<div class="dashed" style="padding: 3px;" align="center" width="100%"><a href="javascript://" OnClick="javascript: layerVis\(\'.*?\', 1\);">.*?</a><div align="left" id="(.*?)" style="visibility: hidden;">!is', $fudml)) {
		$fudml = preg_replace('!\<div class\="dashed" style\="padding: 3px;" align\="center" width\="100%"\>\<a href\="javascript://" OnClick\="javascript: layerVis\(\'.*?\', 1\);">(.*?)\</a\>\<div align\="left" id\=".*?" style\="visibility: hidden;"\>!is', '[spoiler=\1]', $fudml);
		$fudml = str_replace('</div></div>', '[/spoiler]', $fudml);
	}

	// Color, font and size tags.
	$fudml = str_replace('<font face=', '<font font=', $fudml);
	foreach (array('color', 'font', 'size') as $v) {
		while (preg_match('!<font '. $v .'=".+?">.*?</font>!is', $fudml, $m)) {
			$fudml = preg_replace('!<font '. $v .'="(.+?)">(.*?)</font>!is', '['. $v .'=\1]\2[/'. $v .']', $fudml);
		}
	}

	// Acronym tags.
	while (preg_match('!<acronym title=".+?">.*?</acronym>!is', $fudml)) {
		$fudml = preg_replace('!<acronym title="(.+?)">(.*?)</acronym>!is', '[acronym=\1]\2[/acronym]', $fudml);
	}

	// List tags.
	while (preg_match('!<(o|u)l type=".+?">.*?</\\1l>!is', $fudml)) {
		$fudml = preg_replace('!<(o|u)l type="(.+?)">(.*?)</\\1l>!is', '[list type=\2]\3[/list]', $fudml);
	}

	$fudml = str_replace(
	array(
		'<b>', '</b>', '<i>', '</i>', '<u>', '</u>', '<s>', '</s>', '<sub>', '</sub>', '<sup>', '</sup>', '<del>', '</del>',
		'<div class="pre"><pre>', '</pre></div>', '<div align="center">', '<div align="left">', '<div align="right">', '</div>',
		'<ul>', '</ul>', '<span name="notag">', '</span>', '<li>', '&#64;', '&#58;&#47;&#47;', '<br />', '<pre>', '</pre>','<hr>',
		'<h1>', '</h1>', '<h2>', '</h2>', '<h3>', '</h3>', '<h4>', '</h4>'
	),
	array(
		'[b]', '[/b]', '[i]', '[/i]', '[u]', '[/u]', '[s]', '[/s]', '[sub]', '[/sub]', '[sup]', '[/sup]', '[del]', '[/del]', 
		'[code]', '[/code]', '[align=center]', '[align=left]', '[align=right]', '[/align]', '[list]', '[/list]',
		'[notag]', '[/notag]', '[*]', '@', '://', '', '[pre]', '[/pre]','[hr]',
		'[h1]', '[/h1]', '[h2]', '[/h2]', '[h3]', '[/h3]', '[h4]', '[/h4]'
	),
	$fudml);

	while (preg_match('!<img src="(.*?)" border="?0"? alt="\\1" ?/?>!is', $fudml)) {
                $fudml = preg_replace('!<img src="(.*?)" border="?0"? alt="\\1" ?/?>!is', '[img]\1[/img]', $fudml);
	}
	while (preg_match('!<img class="(r|l)" src="(.*?)" border="?0"? alt="\\2" ?/?>!is', $fudml)) {
                $fudml = preg_replace('!<img class="(r|l)" src="(.*?)" border="?0"? alt="\\2" ?/?>!is', '[img\1]\2[/img\1]', $fudml);
	}
	while (preg_match('!<a href="mailto:(.+?)"( target="_blank")?>\\1</a>!is', $fudml)) {
		$fudml = preg_replace('!<a href="mailto:(.+?)"( target="_blank")?>\\1</a>!is', '[email]\1[/email]', $fudml);
	}
	while (preg_match('!<a href="(.+?)"( target="_blank")?>\\1</a>!is', $fudml)) {
		$fudml = preg_replace('!<a href="(.+?)"( target="_blank")?>\\1</a>!is', '[url]\1[/url]', $fudml);
	}

	if (strpos($fudml, '<img src="') !== false) {
                $fudml = preg_replace('!<img src="(.*?)" border="?0"? alt="(.*?)" ?/?>!is', '[img=\1]\2[/img]', $fudml);
	}
	if (strpos($fudml, '<img class="') !== false) {
                $fudml = preg_replace('!<img class="(r|l)" src="(.*?)" border="?0"? alt="(.*?)" ?/?>!is', '[img\1=\2]\3[/img\1]', $fudml);
	}
	if (strpos($fudml, '<a href="mailto:') !== false) {
		$fudml = preg_replace('!<a href="mailto:(.+?)"( target="_blank")?>(.+?)</a>!is', '[email=\1]\3[/email]', $fudml);
	}
	if (strpos($fudml, '<a href="') !== false) {
		$fudml = preg_replace('!<a href="(.+?)"( target="_blank")?>(.+?)</a>!is', '[url=\1]\3[/url]', $fudml);
	}

	if (isset($php)) {
		$fudml = str_replace(array_keys($php), array_values($php), $fudml);
	}

	/* Un-htmlspecialchars. */
	return reverse_fmt($fudml);
}

function filter_ext($file_name)
{
	include $GLOBALS['FORUM_SETTINGS_PATH'] .'file_filter_regexp';
	if (empty($GLOBALS['__FUD_EXT_FILER__'])) {
		return;
	}
	if (($p = strrpos($file_name, '.')) === false) {
		return 1;
	}
	return !in_array(strtolower(substr($file_name, ($p + 1))), $GLOBALS['__FUD_EXT_FILER__']);
}

function reverse_nl2br($data)
{
	if (strpos($data, '<br />') !== false) {
		return str_replace('<br />', '', $data);
	}
	return $data;
}/* Replace and censor text before it's stored. */
function apply_custom_replace($text)
{
	if (!defined('__fud_replace_init')) {
		make_replace_array();
	}
	if (empty($GLOBALS['__FUD_REPL__'])) {
		return $text;
	}

	return preg_replace($GLOBALS['__FUD_REPL__']['pattern'], $GLOBALS['__FUD_REPL__']['replace'], $text);
}

function make_replace_array()
{
	$GLOBALS['__FUD_REPL__']['pattern'] = $GLOBALS['__FUD_REPL__']['replace'] = array();
	$a =& $GLOBALS['__FUD_REPL__']['pattern'];
	$b =& $GLOBALS['__FUD_REPL__']['replace'];

	$c = uq('SELECT with_str, replace_str FROM fud26_replace WHERE replace_str IS NOT NULL AND with_str IS NOT NULL AND LENGTH(replace_str)>0');
	while ($r = db_rowarr($c)) {
		$a[] = $r[1];
		$b[] = $r[0];
	}
	unset($c);

	define('__fud_replace_init', 1);
}

/* Reverse replacement and censorship of text. */
function apply_reverse_replace($text)
{
	if (!defined('__fud_replacer_init')) {
		make_reverse_replace_array();
	}
	if (empty($GLOBALS['__FUD_REPLR__'])) {
		return $text;
	}
	return preg_replace($GLOBALS['__FUD_REPLR__']['pattern'], $GLOBALS['__FUD_REPLR__']['replace'], $text);
}

function make_reverse_replace_array()
{
	$GLOBALS['__FUD_REPLR__']['pattern'] = $GLOBALS['__FUD_REPLR__']['replace'] = array();
	$a =& $GLOBALS['__FUD_REPLR__']['pattern'];
	$b =& $GLOBALS['__FUD_REPLR__']['replace'];

	$c = uq('SELECT replace_opt, with_str, replace_str, from_post, to_msg FROM fud26_replace');
	while ($r = db_rowarr($c)) {
		if (!$r[0]) {
			$a[] = $r[3];
			$b[] = $r[4];
		} else if ($r[0] && strlen($r[1]) && strlen($r[2])) {
			$a[] = '/'.str_replace('/', '\\/', preg_quote(stripslashes($r[1]))).'/';
			preg_match('/\/(.+)\/(.*)/', $r[2], $regs);
			$b[] = str_replace('\\/', '/', $regs[1]);
		}
	}
	unset($c);

	define('__fud_replacer_init', 1);
}$folders = array(1=>'Входящие', 2=>'Сохранено', 4=>'Черновики', 3=>'Отправленные', 5=>'Корзина');

function tmpl_cur_ppage($folder_id, $folders, $msg_subject='')
{
	if (!$folder_id || (!$msg_subject && $_GET['t'] == 'ppost')) {
		$user_action = 'Создание личного письма';
	} else {
		$user_action = $msg_subject ? '<a href="index.php?t=pmsg&amp;folder_id='.$folder_id.'&amp;'._rsid.'">'.$folders[$folder_id].'</a> &raquo; '.$msg_subject : 'Просмотр папки <b>'.$folders[$folder_id].'</b>';
	}

	return '<span class="GenText"><a href="index.php?t=pmsg&amp;'._rsid.'">Личная почта</a>&nbsp;&raquo;&nbsp;'.$user_action.'</span><br /><img src="blank.gif" alt="" height="4" width="1" /><br />';
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
}function draw_post_smiley_cntrl()
{
	global $PS_SRC, $PS_DST; /* Import from global scope, if possible. */

	include_once $GLOBALS['FORUM_SETTINGS_PATH'].'ps_cache';

	/* Nothing to do. */
	if ($GLOBALS['MAX_SMILIES_SHOWN'] < 1 || !$PS_SRC) {
		return;
	}
	$limit = count($PS_SRC);
	if ($limit > $GLOBALS['MAX_SMILIES_SHOWN']) {
		$limit = $GLOBALS['MAX_SMILIES_SHOWN'];
	}

	$data = '';
	$i = 0;
	while ($i < $limit) {
		$data .= '<a href="javascript: insertTag(document.post_form.msg_body, \'\', \' '.$PS_DST[$i].' \');">'.$PS_SRC[$i++].'</a>&nbsp;';
	}
	return '<tr class="RowStyleA"><td class="nw vt GenText">Вставка смайликов:
	<br /><span class="small">[<a href="javascript://" onclick="window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=smladd\', \'sml_list\', 220, 200);">показать все смайлики</a>]</span>
</td>
<td class="vt"><table border="0" cellspacing="5" cellpadding="0"><tr class="vb"><td>'.$data.'</td></tr></table></td></tr>';
}

function draw_post_icons($msg_icon)
{
	include $GLOBALS['FORUM_SETTINGS_PATH'].'icon_cache';

 	/* Nothing to do. */
	if (!$ICON_L) {
		return;
	}

	$tmp = $data = '';
	$rl = (int) $GLOBALS['POST_ICONS_PER_ROW'];

	foreach ($ICON_L as $k => $f) {
		if ($k && !($k % $rl)) {
			$data .= '<tr>'.$tmp.'</tr>';
			$tmp = '';
		}
		$tmp .= '<td valign="middle" style="white-space: nowrap"><input type="radio" name="msg_icon" value="'.$f.'"'.($f == $msg_icon ? ' checked="checked"' : '' ) .' /><img src="images/message_icons/'.$f.'" alt="" /></td>';
	}
	if ($tmp) {
		$data .= '<tr>'.$tmp.'</tr>';
	}

	return '<tr class="RowStyleA"><td class="vt GenText">Иконка сообщения:</td><td>
<table border="0" cellspacing="0" cellpadding="2">
<tr><td class="GenText" colspan="'.$GLOBALS['POST_ICONS_PER_ROW'].'"><input type="radio" name="msg_icon" value=""'.(!$msg_icon ? ' checked="checked"' : '' ) .' />Нет иконки</td></tr>
'.$data.'
</table>
</td></tr>';
}

function draw_post_attachments($al, $max_as, $max_a, $attach_control_error, $private=0, $msg_id)
{
	$attached_files = '';
	$i = 0;

	if (!empty($al)) {
		$enc = base64_encode(serialize($al));

		ses_putvar((int)$GLOBALS['usr']->sid, md5($enc));

		$c = uq('SELECT a.id,a.fsize,a.original_name,m.mime_hdr
		FROM fud26_attach a
		LEFT JOIN fud26_mime m ON a.mime_type=m.id
		WHERE a.id IN('. implode(',', $al) .') AND message_id IN(0, '. $msg_id .') AND attach_opt='. ($private ? 1 : 0));
		while ($r = db_rowarr($c)) {
			$sz = ( $r[1] < 100000 ) ? number_format($r[1]/1024,2) .'KB' : number_format($r[1]/1048576,2) .'MB';
			$insert_uploaded_image = strncasecmp('image/', $r[3], 6) ? '' : '&nbsp;|&nbsp;<a href="javascript: insertTag(document.post_form.msg_body, \'[img]index.php?t=getfile&id='.$r[0].'&private='.$private.'\', \'[/img]\');">Вставка картинки в тело сообщения</a>';
			$attached_files .= '<tr>
	<td class="RowStyleB">'.$r[2].'</td>
	<td class="RowStyleB">'.$sz.'</td>
	<td class="RowStyleB"><a href="javascript: document.forms[\'post_form\'].file_del_opt.value=\''.$r[0].'\'; document.forms[\'post_form\'].submit();">Удалить</a>'.$insert_uploaded_image.'</td>
</tr>';
			$i++;
		}
		unset($c);
	}

	if (!$private && $GLOBALS['MOD'] && $GLOBALS['frm']->forum_opt & 32) {
		$allowed_extensions = '(нет ограничений)';
	} else {
		include $GLOBALS['FORUM_SETTINGS_PATH'] .'file_filter_regexp';
		if (empty($GLOBALS['__FUD_EXT_FILER__'])) {
			$allowed_extensions = '(нет ограничений)';
		} else {
			$allowed_extensions = implode(' ', $GLOBALS['__FUD_EXT_FILER__']);
		}
	}
	return '<tr class="RowStyleB"><td class="GenText vt nw">Вложение файла:</td><td>
'.($i ? '
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr>
	<th>Имя</th>
	<th>Размер</th>
	<th>Действие</th>
</tr>
'.$attached_files.'
</table>
<input type="hidden" name="file_del_opt" value="" />
' : '' )  .'
'.(isset($enc) ? '<input type="hidden" name="file_array" value="'.$enc.'" />' : '' ) .'
'.$attach_control_error.'
<span class="SmallText"><b>Допустимые расширения файла:</b> '.$allowed_extensions.'<br /><b>Максимальный размер файла:</b> '.$max_as.'KB<br /><b>Максимальное количество файлов в сообщении:</b> '.$max_a.($i ? '; <span class="SmallText"> вложений: '.$i.' '.convertPlural($i, array('файл','файла','файлов')).'</span>' : '' )  .'
</span>
'.((($i + 1) <= $max_a) ? '<input type="file" name="attach_control" /> <input type="submit" class="button" name="attach_control_add" value="Загрузить файл" />
<input type="hidden" name="tmp_f_val" value="1" />' : '' ) .'
</td></tr>';
}function safe_attachment_copy($source, $id, $ext)
{
	$loc = $GLOBALS['FILE_STORE'] . $id .'.atch';
	if (!$ext && !move_uploaded_file($source, $loc)) {
		error_dialog('unable to move uploaded file', 'From: '. $source .' To: '. $loc, 'ATCH');
	} else if ($ext && !copy($source, $loc)) {
		error_dialog('unable to handle file attachment', 'From: '. $source .' To: '. $loc, 'ATCH');
	}
	@unlink($source);

	@chmod($loc, ($GLOBALS['FUD_OPT_2'] & 8388608 ? 0600 : 0666));

	return $loc;
}

function attach_add($at, $owner, $attach_opt=0, $ext=0)
{
	$id = db_qid('INSERT INTO fud26_attach (location, message_id, original_name, owner, attach_opt, mime_type,fsize) '.
		q_limit('SELECT \'\' AS location, 0 AS message_id, '. _esc($at['name']) .' AS original_name, '. $owner .' AS owner, '. $attach_opt .' AS attach_opt, id AS mime_type, '. $at['size'] .' AS fsize 
			FROM fud26_mime WHERE fl_ext IN(\'*\', '. _esc(substr(strrchr($at['name'], '.'), 1)) .')
			ORDER BY fl_ext DESC'
		, 1)
	);

	safe_attachment_copy($at['tmp_name'], $id, $ext);

	return $id;
}

function attach_finalize($attach_list, $mid, $attach_opt=0)
{
	$id_list = '';
	$attach_count = 0;

	$tbl = !$attach_opt ? 'msg' : 'pmsg';

	foreach ($attach_list as $key => $val) {
		if (!$val) {
			@unlink($GLOBALS['FILE_STORE'] . (int)$key .'.atch');
		} else {
			$attach_count++;
			$id_list .= (int)$key .',';
		}
	}

	if ($id_list) {
		$id_list = substr($id_list, 0, -1);
		$cc = q_concat(_esc($GLOBALS['FILE_STORE']), 'id', _esc('.atch'));
		q('UPDATE fud26_attach SET location='. $cc .', message_id='. $mid .' WHERE id IN('. $id_list .') AND attach_opt='. $attach_opt);
		$id_list = ' AND id NOT IN('. $id_list .')';
	} else {
		$id_list = '';
	}

	/* delete any unneeded (removed, temporary) attachments */
	q('DELETE FROM fud26_attach WHERE message_id='. $mid .' '. $id_list);

	if (!$attach_opt && ($atl = attach_rebuild_cache($mid))) {
		q('UPDATE fud26_msg SET attach_cnt='. $attach_count .', attach_cache='. _esc(serialize($atl)) .' WHERE id='. $mid);
	}

	if (!empty($GLOBALS['usr']->sid)) {
		ses_putvar((int)$GLOBALS['usr']->sid, null);
	}
}

function attach_rebuild_cache($id)
{
	$ret = array();
	$c = uq('SELECT a.id, a.original_name, a.fsize, a.dlcount, COALESCE(m.icon, \'unknown.gif\') FROM fud26_attach a LEFT JOIN fud26_mime m ON a.mime_type=m.id WHERE message_id='. $id .' AND attach_opt=0');
	while ($r = db_rowarr($c)) {
		$ret[] = $r;
	}
	unset($c);
	return $ret;
}

function attach_inc_dl_count($id, $mid)
{
	q('UPDATE fud26_attach SET dlcount=dlcount+1 WHERE id='. $id);
	if (($a = attach_rebuild_cache($mid))) {
		q('UPDATE fud26_msg SET attach_cache='. _esc(serialize($a)) .' WHERE id='. $mid);
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
}function check_return($returnto)
{
	if ($GLOBALS['FUD_OPT_2'] & 32768 && !empty($_SERVER['PATH_INFO'])) {
		if (!$returnto || !strncmp($returnto, '/er/', 4)) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/i/'. _rsidl);
		} else if ($returnto[0] == '/') { /* Unusual situation, path_info & normal themes are active. */
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php'. $returnto);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. $returnto);
		}
	} else if (!$returnto || !strncmp($returnto, 't=error', 7)) {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=index&'. _rsidl);
	} else if (strpos($returnto, 'S=') === false && $GLOBALS['FUD_OPT_1'] & 128) {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. $returnto .'&S='. s);
	} else {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. $returnto);
	}
	exit;
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

function export_msg_data(&$m, &$msg_subject, &$msg_body, &$msg_icon, &$msg_smiley_disabled, &$msg_show_sig, &$msg_track, &$msg_to_list, $repl=0)
{
	$msg_subject = reverse_fmt($m->subject);
	$msg_body = read_pmsg_body($m->foff, $m->length);
	$msg_icon = $m->icon;
	$msg_smiley_disabled = $m->pmsg_opt & 2 ? '2' : '';
	$msg_show_sig = $m->pmsg_opt & 1 ? '1' : '';
	$msg_track = $m->pmsg_opt & 4 ? '4' : '';
	$msg_to_list = char_fix(htmlspecialchars($m->to_list));

	/* We do not revert replacment for forward/quote. */
	if ($repl) {
		$msg_subject = apply_reverse_replace($msg_subject);
		$msg_body = apply_reverse_replace($msg_body);
	}
	if (!$msg_smiley_disabled) {
		$msg_body = post_to_smiley($msg_body);
	}
	if ($GLOBALS['FUD_OPT_1'] & 4096) {
		$msg_body = html_to_tags($msg_body);
	} else if ($GLOBALS['FUD_OPT_1'] & 2048) {
		$msg_body = reverse_nl2br(reverse_fmt($msg_body));
	}
}

	if (__fud_real_user__) {
		is_allowed_user($usr);
	} else {
		std_error('login');
	}

	if (!($FUD_OPT_1 & 1024)) {
		error_dialog('ОШИБКА: Система личных сообщений отключена', 'Вы не можете использовать систему личных сообщений, так как она была выключена администратором.');
	}
	if (!($usr->users_opt & 32)) {
		error_dialog('Система личных сообщений отключена', 'Вы не можете посылать личные сообщения, не включив параметр &#39;Разрешить личные сообщения&#39; в ваших персональных настройках.');
	}
	
	if ($usr->users_opt & 524288) {
		$ms = $MAX_PMSG_FLDR_SIZE_PM;
	} else if ($usr->users_opt & 1048576) {
		$ms = $MAX_PMSG_FLDR_SIZE_AD;
	} else {
		$ms = $MAX_PMSG_FLDR_SIZE;
	}

	if ($GLOBALS['FUD_OPT_3'] & 32768) {
		$fldr_size  = q_singleval('SELECT SUM(length) FROM fud26_pmsg WHERE foff>0 AND duser_id='. _uid);
		$fldr_size += q_singleval('SELECT SUM(LENGTH(data)) FROM fud26_pmsg p INNER JOIN fud26_msg_store m ON p.length=m.id WHERE foff<0 AND duser_id='. _uid);
	} else {
		$fldr_size = q_singleval('SELECT SUM(length) FROM fud26_pmsg WHERE duser_id='. _uid);
	}
	if ($fldr_size > $ms) {
		error_dialog('Недостаточно свободного пространства!', 'Размер папок с вашими личными сообщениями превышает установленный предел в <b>'.$GLOBALS['MAX_PMSG_FLDR_SIZE'].' байт</b> и составляет <b>'.$fldr_size.' байт</b>.<br /><br /><font class="ErrorText">Удалите часть старых сообщений для высвобождения свободного пространства</font>.');
	}

	$attach_control_error = '';

	$attach_count = 0;
	$attach_list = array();

	if (!isset($_POST['prev_loaded'])) {
		/* Setup some default values. */
		$msg_subject = $msg_body = $msg_icon = $old_subject = $msg_ref_msg_id = '';
		$msg_track = '';
		$msg_show_sig = $usr->users_opt & 2048 ? '1' : '';
		$msg_smiley_disabled = $FUD_OPT_1 & 8192 ? '' : '2';
		$reply = $forward = $msg_id = 0;

		/* Deal with users passed via GET. */
		if (isset($_GET['toi']) && ($toi = (int)$_GET['toi'])) {
			$msg_to_list = q_singleval('SELECT alias FROM fud26_users WHERE id='. $toi .' AND id>1');
		} else {
			$msg_to_list = '';
		}

		/* See if we have pre-defined subject being passed (via message id). */
		if (isset($_GET['rmid']) && ($rmid = (int)$_GET['rmid'])) {
			fud_use('is_perms.inc');
			make_perms_query($fields, $join, 't.forum_id');

			$msg_subject = q_singleval('SELECT m.subject FROM fud26_msg m 
							INNER JOIN fud26_thread t ON t.id=m.thread_id
							'.$join.'
							LEFT JOIN fud26_mod mm ON mm.forum_id=t.forum_id AND mm.user_id='. _uid .'
							WHERE m.id='. $rmid . ($GLOBALS['is_a'] ? '' : ' AND (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0 )'));
			$msg_subject = html_entity_decode($msg_subject);
		}

		if (isset($_GET['msg_id']) && ($msg_id = (int)$_GET['msg_id'])) { /* Editing a message. */
			if (($msg_r = db_sab('SELECT id, subject, length, foff, to_list, icon, attach_cnt, pmsg_opt, ref_msg_id FROM fud26_pmsg WHERE id='. $msg_id .' AND duser_id='._uid))) {
				export_msg_data($msg_r, $msg_subject, $msg_body, $msg_icon, $msg_smiley_disabled, $msg_show_sig, $msg_track, $msg_to_list, 1);
			}
		} else if (isset($_GET['quote']) || isset($_GET['forward'])) { /* Quote or forward message. */
			if (($msg_r = db_sab('SELECT id, post_stamp, ouser_id, subject, length, foff, to_list, icon, attach_cnt, pmsg_opt, ref_msg_id '. (isset($_GET['quote']) ? ', to_list' : '') .' FROM fud26_pmsg WHERE id='. (int)(isset($_GET['quote']) ? $_GET['quote'] : $_GET['forward']) .' AND duser_id='. _uid))) {
				$reply = $quote = isset($_GET['quote']) ? (int)$_GET['quote'] : 0;
				$forward = isset($_GET['forward']) ? (int)$_GET['forward'] : 0;

				export_msg_data($msg_r, $msg_subject, $msg_body, $msg_icon, $msg_smiley_disabled, $msg_show_sig, $msg_track, $msg_to_list);
				$msg_id = $msg_to_list = '';

				if ($quote) {
					$msg_to_list = q_singleval('SELECT alias FROM fud26_users WHERE id='. $msg_r->ouser_id);
				}

				if ($quote) {
					if ($FUD_OPT_1 & 4096) {
						$msg_body = '[quote title='.$msg_to_list.' написал(а) '.strftime("%a, %d %B %Y %H:%M", $msg_r->post_stamp).']'.$msg_body.'[/quote]';
					} else if ($FUD_OPT_1 & 2048) {
						$msg_body = "> ".str_replace("\n", "\n> ", $msg_body);
						$msg_body = str_replace('<br />', "\n", 'Цитата: '.$msg_to_list.' написал(а) '.strftime("%a, %d %B %Y %H:%M", $msg_r->post_stamp).'<br />----------------------------------------------------<br />'.$msg_body.'<br />----------------------------------------------------<br />');
					} else {
						$msg_body = '<cite>'.$msg_to_list.' написал(а) '.strftime("%a, %d %B %Y %H:%M", $msg_r->post_stamp).'</cite><blockquote>'.$msg_body.'</blockquote>';
					}

					if (strncmp($msg_subject, 'Re: ', 4)) {
						$old_subject = $msg_subject = 'Re: '. $msg_subject;
					}
					$msg_ref_msg_id = 'R'.$reply;
					unset($msg_r);
				} else if ($forward && strncmp($msg_subject, 'Fwd: ', 5)) {
					$old_subject = $msg_subject = 'Fwd: '. $msg_subject;
					$msg_ref_msg_id = 'F'.$forward;
				}
			}
		} else if (isset($_GET['reply']) && ($reply = (int)$_GET['reply'])) {
			if (($msg_r = db_saq('SELECT p.subject, u.alias FROM fud26_pmsg p INNER JOIN fud26_users u ON p.ouser_id=u.id WHERE p.id='. $reply .' AND p.duser_id='. _uid))) {
				$msg_subject = $msg_r[0];
				$msg_to_list = $msg_r[1];

				if (strncmp($msg_subject, 'Re: ', 4)) {
					$old_subject = $msg_subject = 'Re: '. $msg_subject;
				}
				$msg_subject = reverse_fmt($msg_subject);
				unset($msg_r);
				$msg_ref_msg_id = 'R'.$reply;
			}
		}

		/* restore file attachments */
		if (!empty($msg_r->attach_cnt) && $PRIVATE_ATTACHMENTS > 0) {
			$c = uq('SELECT id FROM fud26_attach WHERE message_id='. $msg_r->id .' AND attach_opt=1');
	 		while ($r = db_rowarr($c)) {
	 			$attach_list[$r[0]] = $r[0];
	 		}
	 		unset($c);
		}
	} else {
		if (isset($_POST['btn_action'])) {
			if ($_POST['btn_action'] == 'draft') {
				$_POST['btn_draft'] = 1;
			} else if ($_POST['btn_action'] == 'send') {
				$_POST['btn_submit'] = 1;
			}
		}

		$msg_to_list = char_fix(htmlspecialchars($_POST['msg_to_list']));
		$msg_subject = $_POST['msg_subject'];
		$old_subject = $_POST['old_subject'];
		$msg_body = $_POST['msg_body'];
		$msg_icon = (isset($_POST['msg_icon']) && basename($_POST['msg_icon']) == $_POST['msg_icon'] && @file_exists($WWW_ROOT_DISK .'images/message_icons/'. $_POST['msg_icon'])) ? $_POST['msg_icon'] : '';
		$msg_track = isset($_POST['msg_track']) ? '4' : '';
		$msg_smiley_disabled = isset($_POST['msg_smiley_disabled']) ? '2' : '';
		$msg_show_sig = isset($_POST['msg_show_sig']) ? '1' : '';

		/* Microsoft Word Hack to eliminate special characters */
		$in = array('”','“','’','‘','…','—','–'); $out = array('"','"',"'","'",'...','--');
		$msg_body = str_replace($in,$out,$msg_body);
		$msg_subject = str_replace($in,$out,$msg_subject);

		$reply = isset($_POST['reply']) ? (int)$_POST['reply'] : 0;
		$forward = isset($_POST['forward']) ? (int)$_POST['forward'] : 0;
		$msg_id = isset($_POST['msg_id']) ? (int)$_POST['msg_id'] : 0;
		$msg_ref_msg_id = isset($_POST['msg_ref_msg_id']) ? (int)$_POST['msg_ref_msg_id'] : '';

		/* Restore file attachments. */
		if (!empty($_POST['file_array']) && $PRIVATE_ATTACHMENTS > 0 && $usr->data === md5($_POST['file_array'])) {
			$attach_list = unserialize(base64_decode($_POST['file_array']));
		}
	}

	if ($attach_list) {
		$enc = base64_encode(serialize($attach_list));
		foreach ($attach_list as $v) {
			if ($v) {
				$attach_count++;
			}
		}
		/* Remove file attachment. */
		if (isset($_POST['file_del_opt'], $attach_list[$_POST['file_del_opt']])) {
			if ($attach_list[$_POST['file_del_opt']]) {
				$attach_list[$_POST['file_del_opt']] = 0;
				/* Remove any reference to the image from the body to prevent broken images. */
				if (strpos($msg_body, '[img]index.php?t=getfile&id='. $_POST['file_del_opt'] .'[/img]') !== false) {
					$msg_body = str_replace('[img]index.php?t=getfile&id='. $_POST['file_del_opt'] .'[/img]', '', $msg_body);
				}
				if (strpos($msg_body, '[img]'.$GLOBALS['WWW_ROOT'].'index.php?t=getfile&id='. $_POST['file_del_opt'] .'[/img]') !== false) {
					$msg_body = str_replace('[img]'.$GLOBALS['WWW_ROOT'].'index.php?t=getfile&id='. $_POST['file_del_opt'] .'[/img]', '', $msg_body);
				}
				$attach_count--;
			}
		}
	}

	/* Deal with newly uploaded files. */
	if ($PRIVATE_ATTACHMENTS > 0 && isset($_FILES['attach_control']) && $_FILES['attach_control']['size'] > 0) {
		if ($_FILES['attach_control']['size'] > $PRIVATE_ATTACH_SIZE) {
			$MAX_F_SIZE = $PRIVATE_ATTACH_SIZE;
			$attach_control_error = 'Файл слишком велик (превышает установленный предел в '.$MAX_F_SIZE.' байт)';
		} else {
			if (filter_ext($_FILES['attach_control']['name'])) {
				$attach_control_error = 'Загружаемый файл не принадлежит к одному из допустимых типов.';
			} else {
				if (($attach_count+1) <= $PRIVATE_ATTACHMENTS) {
					$val = attach_add($_FILES['attach_control'], _uid, 1);
					$attach_list[$val] = $val;
					$attach_count++;
				} else {
					$attach_control_error = 'Вы пытаетесь загрузить большее допустимого количество файлов.';
				}
			}
		}
	}

	if ((isset($_POST['btn_submit']) && !check_ppost_form($_POST['msg_subject'])) || isset($_POST['btn_draft'])) {
		$msg_p = new fud_pmsg;
		$msg_p->pmsg_opt = (int) $msg_smiley_disabled | (int) $msg_show_sig | (int) $msg_track;
		$msg_p->attach_cnt = $attach_count;
		$msg_p->icon = $msg_icon;
		$msg_p->body = $msg_body;
		$msg_p->subject = $msg_subject;
		$msg_p->fldr = isset($_POST['btn_submit']) ? 3 : 4;
		$msg_p->to_list = $_POST['msg_to_list'];

		$msg_p->body = apply_custom_replace($msg_p->body);
		if ($FUD_OPT_1 & 4096) {
			$msg_p->body = char_fix(tags_to_html($msg_p->body, $FUD_OPT_1 & 16384));
		} else if ($FUD_OPT_1 & 2048) {
			$msg_p->body = char_fix(nl2br(htmlspecialchars($msg_p->body)));
		}

		if (!($msg_p->pmsg_opt & 2)) {
			$msg_p->body = smiley_to_post($msg_p->body);
		}
		fud_wordwrap($msg_p->body);

		$msg_p->ouser_id = _uid;

		$msg_p->subject = char_fix(htmlspecialchars(apply_custom_replace($msg_p->subject)));

		if (empty($_POST['msg_id'])) {
			$msg_p->pmsg_opt = $msg_p->pmsg_opt &~ 96;
			if ($_POST['reply']) {
				$msg_p->ref_msg_id = 'R'. $_POST['reply'];
				$msg_p->pmsg_opt |= 64;
			} else if ($_POST['forward']) {
				$msg_p->ref_msg_id = 'F'. $_POST['forward'];
			} else {
				$msg_p->ref_msg_id = null;
				$msg_p->pmsg_opt |= 32;
			}

			$msg_p->add();
		} else {
			$msg_p->id = (int) $_POST['msg_id'];
			$msg_p->sync();
		}

		if ($attach_list) {
			attach_finalize($attach_list, $msg_p->id, 1);

			/* We need to add attachments to all copies of the message. */
			if (!isset($_POST['btn_draft'])) {
				$atl = array();
				$c = uq('SELECT id, original_name, mime_type, fsize FROM fud26_attach WHERE message_id='. $msg_p->id .' AND attach_opt=1');
				while ($r = db_rowarr($c)) {
					$atl[$r[0]] = _esc($r[1]) .', '. $r[2] .', '. $r[3];
				}
				unset($c);
				if ($atl) {
					$aidl = array();

					foreach ($GLOBALS['send_to_array'] as $mid) {
						foreach ($atl as $k => $v) {
							$aid = db_qid('INSERT INTO fud26_attach (owner, attach_opt, message_id, original_name, mime_type, fsize, location) VALUES('. $mid[0] .', 1, '. $mid[1] .', '. $v .', \'placeholder\')');
							$aidl[] = $aid;
							copy($FILE_STORE . $k .'.atch', $FILE_STORE . $aid .'.atch');
							@chmod($FILE_STORE . $aid .'.atch', ($FUD_OPT_2 & 8388608 ? 0600 : 0666));
						}
					}
					$cc = q_concat(_esc($FILE_STORE), 'id', _esc('.atch'));
					q('UPDATE fud26_attach SET location='. $cc .' WHERE id IN('. implode(',', $aidl) .')');
				}
			}
		}

		if ($usr->returnto) {
			check_return($usr->returnto);
		}

		if ($FUD_OPT_2 & 32768) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/pdm/1/'. _rsidl);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=pmsg&'. _rsidl .'&fldr=1');
		}
		exit;
	}

	$no_spell_subject = ($reply && $old_subject == $msg_subject);

	if (isset($_POST['btn_spell'])) {
		$text = apply_custom_replace($_POST['msg_body']);
		$text_s = apply_custom_replace($_POST['msg_subject']);

		if ($FUD_OPT_1 & 4096) {
			$text = char_fix(tags_to_html($text, $FUD_OPT_1 & 16384));
		} else if ($FUD_OPT_1 & 2048) {
			$text = char_fix(htmlspecialchars($text));
		}

		if ($FUD_OPT_1 & 8192 && !$msg_smiley_disabled) {
			$text = smiley_to_post($text);
		}

	 	if ($text) {
			$text = spell_replace(tokenize_string($text), 'body');

			if ($FUD_OPT_1 & 8192 && !$msg_smiley_disabled) {
				$msg_body = post_to_smiley($text);
			}

			if ($FUD_OPT_1 & 4096) {
				$msg_body = html_to_tags($msg_body);
			} else if ($FUD_OPT_1 & 2048) {
				$msg_body = reverse_fmt($msg_body);
			}
			$msg_body = apply_reverse_replace($msg_body);
		}

		if ($text_s && !$no_spell_subject) {
			$text_s = char_fix(htmlspecialchars($text_s));
			$text_s = spell_replace(tokenize_string($text_s), 'subject');
			$msg_subject = apply_reverse_replace(reverse_fmt($text_s));
		}
	}

	ses_update_status($usr->sid, 'Личная почта', 0, 1);

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
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

	$spell_check_button = ($FUD_OPT_1 & 2097152 && extension_loaded('pspell') && $usr->pspell_lang) ? '<input accesskey="k" type="submit" class="button" value="Проверить орфографию" name="spell" />&nbsp;' : '';

	if (isset($_POST['preview']) || isset($_POST['spell'])) {
		$text = apply_custom_replace($_POST['msg_body']);
		$text_s = apply_custom_replace($_POST['msg_subject']);

		if ($FUD_OPT_1 & 4096) {
			$text = char_fix(tags_to_html($text, $FUD_OPT_1 & 16384));
		} else if ($FUD_OPT_1 & 2048) {
			$text = char_fix(nl2br(htmlspecialchars($text)));
		}

		if ($FUD_OPT_1 & 8192 && !$msg_smiley_disabled) {
			$text = smiley_to_post($text);
		}
		$text_s = char_fix(htmlspecialchars($text_s));

		$spell = $spell_check_button && isset($_POST['spell']);

		if ($spell && strlen($text)) {
			$text = check_data_spell($text, 'body', $usr->pspell_lang);
		}
		fud_wordwrap($text);

		$subj = ($spell && !$no_spell_subject && $text_s) ? check_data_spell($text_s, 'subject', $usr->pspell_lang) : $text_s;

		$signature = ($FUD_OPT_1 & 32768 && $usr->sig && $msg_show_sig) ? '<br /><br /><div class="signature">'.$usr->sig.'</div>' : '';
		$apply_spell_changes = $spell ? '<input accesskey="a" type="submit" class="button" name="btn_spell" value="Утвердить проверку правописания" />&nbsp;' : '';
		$preview_message = '<div id="preview" class="ctb"><table cellspacing="1" cellpadding="2" class="PreviewTable">
<tr><th colspan="2">Предварительный просмотр сообщения</th></tr>
<tr><td class="RowStyleA MsgSubText">'.$subj.'</td></tr>
<tr><td class="RowStyleA MsgBodyText">'.$text.$signature.'</td></tr>
<tr><td class="al RowStyleB">'.$apply_spell_changes.'<input type="submit" class="button" name="btn_submit" value="Отправить" tabindex="5" onclick="document.post_form.btn_action.value=\'send\';">&nbsp;<input type="submit" tabindex="4" class="button" value="Предварительный просмотр" name="preview" />&nbsp;'.$spell_check_button.'<input type="submit" class="button" name="btn_draft" value="Сохранить" onclick="document.post_form.btn_action.value=\'draft\';" /></td></tr>
</table></div><br />';
	} else {
		$preview_message = '';
	}

	$post_error = is_post_error() ? '<h4 class="ErrorText ac">Произошла ошибка</h4>' : '';
	$session_error = get_err('msg_session');
	if ($session_error) {
		$post_error = $session_error;
	}

	$msg_body = $msg_body ? char_fix(htmlspecialchars(str_replace("\r", '', $msg_body))) : '';
	if ($msg_subject) {
		$msg_subject = char_fix(htmlspecialchars($msg_subject));
	}

	if ($PRIVATE_ATTACHMENTS > 0) {
		$file_attachments = draw_post_attachments($attach_list, round($PRIVATE_ATTACH_SIZE / 1024), $PRIVATE_ATTACHMENTS, $attach_control_error, ($FUD_OPT_2 & 32768 ? '1' : '&amp;private=1'), $msg_id ? $msg_id : (isset($_GET['forward']) ? (int)$_GET['forward'] : 0));
	} else {
		$file_attachments = '';
	}

	if ($reply && ($mm = db_sab('SELECT p.*, u.id AS user_id, u.sig, u.alias, u.users_opt, u.posted_msg_count, u.join_date, u.last_visit FROM fud26_pmsg p INNER JOIN fud26_users u ON p.ouser_id=u.id WHERE p.duser_id='. _uid .' AND p.id='. $reply))) {
		fud_use('drawpmsg.inc');
		$dpmsg_prev_message = $dpmsg_next_message = '';
		$reference_msg = '<br /><br />
<div class="ac">сообщение, которое вы пересылаете или на которое отвечаете</div>
<table cellspacing="0" cellpadding="3" class="dashed wa">
<tr><td>
<table cellspacing="1" cellpadding="2" class="ContentTable">
'.tmpl_drawpmsg($mm, $usr, true).'
</table>
</td></tr>
</table>';
	} else {
		$reference_msg = '';
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
<?php echo $tabs; ?>
<br /><?php echo tmpl_cur_ppage('', $folders); ?>
<form action="index.php?t=ppost" method="post" id="post_form" name="post_form" enctype="multipart/form-data" onsubmit="document.post_form.btn_submit.disabled = true; document.post_form.btn_draft.disabled = true;">
<?php echo _hs; ?>
<input type="hidden" name="btn_action" value="" />
<input type="hidden" name="msg_id" value="<?php echo $msg_id; ?>" />
<input type="hidden" name="reply" value="<?php echo $reply; ?>" />
<input type="hidden" name="forward" value="<?php echo $forward; ?>" />
<input type="hidden" name="old_subject" value="<?php echo $old_subject; ?>" />
<input type="hidden" name="msg_ref_msg_id" value="<?php echo $msg_ref_msg_id; ?>" />
<input type="hidden" name="prev_loaded" value="1" />
<?php echo $post_error; ?>
<?php echo $preview_message; ?>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th colspan="2">Отправка сообщения<a name="ptop"> </a></th></tr>
<tr class="RowStyleB"><td class="GenText nw">Пользователь:</td><td class="GenText wa"><?php echo $usr->alias; ?> [<a href="index.php?t=login&amp;<?php echo _rsid; ?>&amp;logout=1&amp;SQ=<?php echo $GLOBALS['sq']; ?>">выход</a>]</td></tr>
<tr class="RowStyleB"><td class="GenText">Кому:</td><td class="GenText"><input type="text" name="msg_to_list" value="<?php echo $msg_to_list; ?>" tabindex="1" /> <?php echo ($FUD_OPT_1 & (8388608|4194304) ? '[<a href="javascript://" onclick="window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=pmuserloc&amp;'._rsid.'&amp;js_redr=post_form.msg_to_list\',\'user_list\',400,250);">Поиск участника</a>]' : ''); ?> [<a href="javascript://" onclick="window_open('<?php echo $GLOBALS['WWW_ROOT']; ?>index.php?t=qbud&amp;<?php echo _rsid; ?>&amp;1=1', 'buddy_list',275,300);">Выбор из списка контактов</a>]<?php echo get_err('msg_to_list'); ?></td></tr>
<tr class="RowStyleB"><td class="GenText">Заголовок:</td><td class="GenText"><input type="text" spellcheck="true" maxlength="100" name="msg_subject" value="<?php echo $msg_subject; ?>" size="50" tabindex="2" /> <?php echo get_err('msg_subject'); ?></td></tr>
<?php echo draw_post_icons($msg_icon); ?>
<?php echo ($FUD_OPT_1 & 8192 ? draw_post_smiley_cntrl().'' : ''); ?>
<?php echo ($FUD_OPT_1 & 4096 ? '<tr class="RowStyleA"><td class="GenText nw">Форматирования:</td><td>
<table border="0" cellspacing="0" cellpadding="0">
<tr><td>
<table cellspacing="1" cellpadding="2" class="FormattingToolsBG">
<tr>
<td class="FormattingToolsCLR"><a title="Жирный" accesskey="b" href="javascript: insertTag(document.post_form.msg_body, \'[b]\', \'[/b]\');"><img alt="" src="theme/velopiter/images/b_bold.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Наклонный" accesskey="i" href="javascript: insertTag(document.post_form.msg_body, \'[i]\', \'[/i]\');"><img alt="" src="theme/velopiter/images/b_italic.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Подчеркнутый" accesskey="u" href="javascript: insertTag(document.post_form.msg_body, \'[u]\', \'[/u]\');"><img alt="" src="theme/velopiter/images/b_underline.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Выравнивание слева" href="javascript: insertTag(document.post_form.msg_body, \'[ALIGN=left]\', \'[/ALIGN]\');"><img alt="" src="theme/velopiter/images/b_aleft.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Выравнивание по центру" href="javascript: insertTag(document.post_form.msg_body, \'[ALIGN=center]\', \'[/ALIGN]\');"><img alt="" src="theme/velopiter/images/b_acenter.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Выравнивание справа" href="javascript: insertTag(document.post_form.msg_body, \'[ALIGN=right]\', \'[/ALIGN]\');"><img alt="" src="theme/velopiter/images/b_aright.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Вставить ссылку" accesskey="w" href="javascript: url_insert();"><img alt="" src="theme/velopiter/images/b_url.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Вставить адрес E-mail" accesskey="e" href="javascript: email_insert();"><img alt="" src="theme/velopiter/images/b_email.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Вставить изображение" accesskey="m" href="javascript: image_insert();"><img alt="" src="theme/velopiter/images/b_image.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Добавить нумерованный список" accesskey="l" href="javascript: window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=mklist&amp;'._rsid.'&amp;tp=OL:1\', \'listmaker\', 350, 350);"><img alt="" src="theme/velopiter/images/b_numlist.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Добавить бюллетень" href="javascript: window_open(\''.$GLOBALS['WWW_ROOT'].'index.php?t=mklist&amp;'._rsid.'&amp;tp=UL:square\', \'listmaker\', 350, 350);"><img alt="" src="theme/velopiter/images/b_bulletlist.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Добавить цитату" accesskey="q" href="javascript: insertTag(document.post_form.msg_body, \'[quote]\', \'[/quote]\');"><img alt="" src="theme/velopiter/images/b_quote.gif" /></a></td>
<td class="FormattingToolsCLR"><a title="Добавить исходный текст" accesskey="c" href="javascript: insertTag(document.post_form.msg_body, \'[code]\', \'[/code]\');"><img alt="" src="theme/velopiter/images/b_code.gif" /></a></td>
</tr>
</table>
</td>
<td>&nbsp;&nbsp;
<select name="fnt_size" onchange="insertTag(document.post_form.msg_body, \'[size=\'+document.post_form.fnt_size.options[this.selectedIndex].value+\']\', \'[/size]\'); document.post_form.fnt_size.options[0].selected=true">
<option value="" selected="selected">Размер</option>
<option value="1">1</option>
<option value="2">2</option>
<option value="3">3</option>
<option value="4">4</option>
<option value="5">5</option>
<option value="6">6</option>
<option value="7">7</option>
</select>
<select name="fnt_color" onchange="insertTag(document.post_form.msg_body, \'[color=\'+document.post_form.fnt_color.options[this.selectedIndex].value+\']\', \'[/color]\'); document.post_form.fnt_color.options[0].selected=true">
<option value="">Цвет</option>
<option value="skyblue" style="color:skyblue">Sky Blue</option>
<option value="royalblue" style="color:royalblue">Royal Blue</option>
<option value="blue" style="color:blue">Blue</option>
<option value="darkblue" style="color:darkblue">Dark Blue</option>
<option value="orange" style="color:orange">Orange</option>
<option value="orangered" style="color:orangered">Orange Red</option>
<option value="crimson" style="color:crimson">Crimson</option>
<option value="red" style="color:red">Red</option>
<option value="firebrick" style="color:firebrick">Firebrick</option>
<option value="darkred" style="color:darkred">Dark Red</option>
<option value="green" style="color:green">Green</option>
<option value="limegreen" style="color:limegreen">Lime Green</option>
<option value="seagreen" style="color:seagreen">Sea Green</option>
<option value="deeppink" style="color:deeppink">Deep Pink</option>
<option value="tomato" style="color:tomato">Tomato</option>
<option value="coral" style="color:coral">Coral</option>
<option value="purple" style="color:purple">Purple</option>
<option value="indigo" style="color:indigo">Indigo</option>
<option value="burlywood" style="color:burlywood">Burly Wood</option>
<option value="sandybrown" style="color:sandybrown">Sandy Brown</option>
<option value="sienna" style="color:sienna">Sienna</option>
<option value="chocolate" style="color:chocolate">Chocolate</option>
<option value="teal" style="color:teal">Teal</option>
<option value="silver" style="color:silver">Silver</option>
</select>
<select name="fnt_face" onchange="insertTag(document.post_form.msg_body, \'[font=\'+document.post_form.fnt_face.options[this.selectedIndex].value+\']\', \'[/font]\'); document.post_form.fnt_face.options[0].selected=true">
<option value="">Шрифт</option>
<option value="Arial" style="font-family:Arial">Arial</option>
<option value="Times" style="font-family:Times">Times</option>
<option value="Courier" style="font-family:Courier">Courier</option>
<option value="Century" style="font-family:Century">Century</option>
</select>
</td></tr></table></td></tr>' : ''); ?>

<tr class="RowStyleA"><td class="nw vt GenText">Текст:<br /><br /><?php echo tmpl_post_options('private'); ?></td><td><?php echo get_err('msg_body',1); ?><span style="float:left;"><textarea id="txtb" name="msg_body" rows="20" cols="65" wrap="virtual" tabindex="3" onkeyup="storeCaret(this);" onclick="storeCaret(this);" onselect="storeCaret(this);"><?php echo $msg_body; ?></textarea></span>
<a href="javascript://" onclick="rs_txt_box(50, 0);"><img alt="⇨" src="theme/velopiter/images/rs_hb.gif" style="margin: 2px" /></a><br />
<a href="javascript://" onclick="rs_txt_box(0, 100);"><img alt="⇩" src="theme/velopiter/images/rs_vb.gif" style="margin: 2px" /></a><br />
<a href="javascript://" onclick="rs_txt_box(-50, 0);"><img alt="⇦" src="theme/velopiter/images/rs_hs.gif" style="margin: 2px" /></a><br />
<a href="javascript://" onclick="rs_txt_box(0, -100);"><img alt="⇧" src="theme/velopiter/images/rs_vs.gif" style="margin: 2px" /></a><br />
</td></tr>

<?php echo $file_attachments; ?>
<tr class="RowStyleB vt">
<td class="GenText">Параметры:</td>
<td>
<table border="0" cellspacing="0" cellpadding="1">
<tr><td><input type="checkbox" name="msg_track" id="msg_track" value="Y"<?php echo ($msg_track ? ' checked="checked"' : ''); ?> /></td><td class="GenText fb"><label for="msg_track">Извещение о прочтении</label></td></tr>
<tr><td>&nbsp;</td><td class="SmallText">Известить меня, когда данное сообщение будет прочитано адресатом.</td></tr>
<tr><td><input type="checkbox" name="msg_show_sig" id="msg_show_sig" value="Y"<?php echo ($msg_show_sig ? ' checked="checked"' : ''); ?> /></td><td class="GenText fb"><label for="msg_show_sig">Включить подпись в сообщение</label></td></tr>
<tr><td>&nbsp;</td><td class="SmallText">Включает в сообщение заданную вами в персональных настройках подпись.</td></tr>
<?php echo ($FUD_OPT_1 & 8192 ? '<tr><td><input type="checkbox" name="msg_smiley_disabled" id="msg_smiley_disabled" value="Y" '.($msg_smiley_disabled ? ' checked="checked"' : '' )  .' /></td><td class="GenText"><b><label for="msg_smiley_disabled">Отключить смайлики</label></b></td></tr>' : ''); ?>
</table>
</td>
</tr>
<tr class="RowStyleA"><td class="GenText ar" colspan="2"><input accesskey="r" type="submit" tabindex="4" class="button" value="Предварительный просмотр" name="preview" />&nbsp;<?php echo $spell_check_button; ?><input type="submit" accesskey="d" class="button" name="btn_draft" value="Сохранить" onclick="document.post_form.btn_action.value='draft';" />&nbsp;<input type="submit" class="button" name="btn_submit" value="Отправить" tabindex="5" onclick="document.post_form.btn_action.value='send';" accesskey="s" /></td></tr>
</table>
</form>
<?php echo $reference_msg; ?>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
<script type="text/javascript">
/* <![CDATA[ */
quote_selected_text('Цитировать выбранный текст');

if (!document.getElementById('preview')) {
	if (!document.post_form.msg_subject.value.length) {
		document.post_form.msg_subject.focus();
	} else {
		document.post_form.msg_body.focus();
	}
}
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
