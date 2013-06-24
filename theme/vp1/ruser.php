<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: ruser.php.t 5030 2010-10-08 18:27:42Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	define('plain_form', 1);

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}function logaction($user_id, $res, $res_id=0, $action=null)
{
	q('INSERT INTO fud26_action_log (logtime, logaction, user_id, a_res, a_res_id)
		VALUES('. __request_timestamp__ .', '. ssn($action) .', '. $user_id .', '. ssn($res) .', '. (int)$res_id .')');
}class fud_user
{
	var $id, $login, $alias, $passwd, $salt, $plaintext_passwd,
	    $name, $email, $location, $occupation, $interests, $topics_per_page,
	    $icq, $aim, $yahoo, $msnm, $jabber, $affero, $google, $skype, $twitter,
	    $avatar, $avatar_loc, $posts_ppg, $time_zone, $birthday, $home_page,
	    $sig, $bio, $posted_msg_count, $last_visit, $last_event, $conf_key,
	    $user_image, $join_date, $theme, $last_read,
	    $mod_list, $mod_cur, $level_id, $u_last_post_id, $users_opt, $cat_collapse_status,
	    $ignore_list, $buddy_list,
	    $custom_fields;
}

function make_alias($text)
{
	if (strlen($text) > $GLOBALS['MAX_LOGIN_SHOW']) {
		$text = substr($text, 0, $GLOBALS['MAX_LOGIN_SHOW']);
	}
	return char_fix(str_replace(array(']', '['), array('&#93;','&#91;'), htmlspecialchars($text)));
}

function generate_salt()
{
	return substr(md5(uniqid(mt_rand(), true)), 0, 9);
}

class fud_user_reg extends fud_user
{
	function html_fields()
	{
		foreach(array('name', 'location', 'occupation', 'interests', 'bio') as $v) {
			if ($this->{$v}) {
				$this->{$v} = char_fix(htmlspecialchars($this->$v));
			}
		}
	}

	function add_user()
	{
		// Track referer.
		if (isset($_COOKIE['frm_referer_id']) && (int)$_COOKIE['frm_referer_id']) {
			$ref_id = (int)$_COOKIE['frm_referer_id'];
		} else {
			$ref_id = 0;
		}

		// Geneate salt & password (if not supplied).
		if (empty($this->passwd) && empty($this->plaintext_passwd)) {
			$this->plaintext_passwd = substr(md5(get_random_value()), 0, 8);
		}
		if (!empty($this->plaintext_passwd)) {
			$this->salt  = generate_salt();
			$this->passwd = sha1($this->salt . sha1($this->plaintext_passwd));
		}

		$o2 =& $GLOBALS['FUD_OPT_2'];
		$this->alias = make_alias((!($o2 & 128) || !$this->alias) ? $this->login : $this->alias);

		/* This is used when utilities create users (aka nntp/mlist/xmlagg imports). */
		if ($this->users_opt == -1) {
			$this->users_opt = 4|16|128|256|512|2048|4096|8192|16384|131072|4194304;
			$this->theme = q_singleval('SELECT id FROM fud26_themes WHERE theme_opt>=2 AND '. q_bitand('theme_opt', 2) .' > 0 LIMIT 1');
			$this->time_zone =& $GLOBALS['SERVER_TZ'];
			$this->posts_ppg =& $GLOBALS['POSTS_PER_PAGE'];
			if (!($o2 & 4)) {
				$this->users_opt ^= 128;
			}
			if (!($o2 & 8)) {
				$this->users_opt ^= 256;
			}
			if ($o2 & 1) {
				$o2 ^= 1;
			}
			$reg_ip = '127.0.0.1';
			$this->topics_per_page = $GLOBALS['THREADS_PER_PAGE'];
		} else {
			$reg_ip = get_ip();
		}

		if (empty($this->join_date)) {
			$this->join_date = __request_timestamp__;
		}

		if ($o2 & 1) {	// EMAIL_CONFIRMATION
			$this->conf_key = md5(implode('', (array)$this) . __request_timestamp__ . getmypid());
		} else {
			$this->conf_key = '';
			$this->users_opt |= 131072;
		}
		$this->icq = (int)$this->icq ? (int)$this->icq : 'NULL';

		$this->html_fields();

		$flag = ret_flag($reg_ip);

		$this->id = db_qid('INSERT INTO
			fud26_users (
				login,
				alias,
				passwd,
				salt,
				name,
				email,
				avatar, 
				avatar_loc,
				icq,
				aim,
				yahoo,
				msnm,
				jabber,
				affero,
				google,
				skype,
				twitter,
				posts_ppg,
				time_zone,
				birthday,
				last_visit,
				conf_key,
				user_image,
				join_date,
				location,
				theme,
				occupation,
				interests,
				referer_id,
				last_read,
				sig,
				home_page,
				bio,
				users_opt,
				reg_ip,
				topics_per_page,
				flag_cc,
				flag_country,
				custom_fields
			) VALUES (
				'. _esc($this->login) .',
				'. _esc($this->alias) .',
				\''. $this->passwd .'\',
				\''. $this->salt .'\',
				'. _esc($this->name) .',
				'. _esc($this->email) .',
				'. (int)$this->avatar .',
				'. ssn($this->avatar_loc) .',
				'. $this->icq .',
				'. ssn(urlencode($this->aim)) .',
				'. ssn(urlencode($this->yahoo)) .',
				'. ssn($this->msnm) .',
				'. ssn(htmlspecialchars($this->jabber)) .',
				'. ssn(urlencode($this->affero)) .',
				'. ssn($this->google) .',
				'. ssn(urlencode($this->skype)) .',
				'. ssn(urlencode($this->twitter)) .',
				'. (int)$this->posts_ppg .',
				'. _esc($this->time_zone) .',
				'. ssn($this->birthday) .',
				'. __request_timestamp__ .',
				\''. $this->conf_key .'\',
				'. ssn(htmlspecialchars($this->user_image)) .',
				'. $this->join_date .',
				'. ssn($this->location) .',
				'. (int)$this->theme .',
				'. ssn($this->occupation) .',
				'. ssn($this->interests) .',
				'. (int)$ref_id .',
				'. __request_timestamp__ .',
				'. ssn($this->sig) .',
				'. ssn(htmlspecialchars($this->home_page)) .',
				'. ssn($this->bio) .',
				'. $this->users_opt .',
				'. ip2long($reg_ip) .',
				'. (int)$this->topics_per_page .',
				'. ssn($flag[0]) .',
				'. ssn($flag[1]) .',
				'. _esc($this->custom_fields) .'
			)
		');

		return $this->id;
	}

	function sync_user()
	{
		if (!empty($this->plaintext_passwd)) {
			if (empty($this->salt)) {
				$this->salt = generate_salt();
			}
			$passwd = 'passwd=\''. sha1($this->salt . sha1($this->plaintext_passwd)) .'\', salt=\''. $this->salt .'\', ';
		} else {
			$passwd = '';
		}

		$this->alias = make_alias((!($GLOBALS['FUD_OPT_2'] & 128) || !$this->alias) ? $this->login : $this->alias);
		$this->icq = (int)$this->icq ? (int)$this->icq : 'NULL';

		$rb_mod_list = (!($this->users_opt & 524288) && ($is_mod = q_singleval('SELECT id FROM fud26_mod WHERE user_id='. $this->id)) && (q_singleval('SELECT alias FROM fud26_users WHERE id='. $this->id) == $this->alias));

		$this->html_fields();

		q('UPDATE fud26_users SET '. $passwd .'
			name='. _esc($this->name) .',
			alias='. _esc($this->alias) .',
			email='. _esc($this->email) .',
			icq='. $this->icq .',
			aim='. ssn(urlencode($this->aim)) .',
			yahoo='. ssn(urlencode($this->yahoo)) .',
			msnm='. ssn($this->msnm) .',
			jabber='. ssn(htmlspecialchars($this->jabber)) .',
			affero='. ssn(urlencode($this->affero)) .',
			google='. ssn($this->google) .',
			skype='. ssn(urlencode($this->skype)) .',
			twitter='. ssn(urlencode($this->twitter)) .',
			posts_ppg='. (int)$this->posts_ppg .',
			time_zone='. _esc($this->time_zone) .',
			birthday='. ssn($this->birthday) .',
			user_image='. ssn(htmlspecialchars($this->user_image)) .',
			location='. ssn($this->location) .',
			occupation='. ssn($this->occupation) .',
			interests='. ssn($this->interests) .',
			avatar='. (int)$this->avatar .',
			theme='. (int)$this->theme .',
			avatar_loc='. ssn($this->avatar_loc) .',
			sig='. ssn($this->sig) .',
			home_page='. ssn(htmlspecialchars($this->home_page)) .',
			bio='. ssn($this->bio) .',
			users_opt='. (int)$this->users_opt .',
			topics_per_page='. (int)$this->topics_per_page .',
			custom_fields='. _esc($this->custom_fields) .'
		WHERE id='. $this->id);

		if ($rb_mod_list) {
			rebuildmodlist();
		}
	}
}

function get_id_by_email($email)
{
	return q_singleval('SELECT id FROM fud26_users WHERE email='. _esc($email));
}

function get_id_by_login($login)
{
	return q_singleval('SELECT id FROM fud26_users WHERE login='. _esc($login));
}

function usr_email_unconfirm($id)
{
	$conf_key = md5(__request_timestamp__ . $id . get_random_value());
	q('UPDATE fud26_users SET users_opt='. q_bitand('users_opt', ~131072) .', conf_key=\''. $conf_key .'\' WHERE id='. $id);

	return $conf_key;
}

function &usr_reg_get_full($id)
{
	if (($r = db_sab('SELECT * FROM fud26_users WHERE id='. $id))) {
		if (!extension_loaded('overload')) {
			$o = new fud_user_reg;
			foreach ($r as $k => $v) {
				$o->{$k} = $v;
			}
			$r = $o;
		} else {
			aggregate_methods($r, 'fud_user_reg');
		}
	}
	return $r;
}

function user_login($id, $cur_ses_id, $use_cookies)
{
	if (!$use_cookies && isset($_COOKIE[$GLOBALS['COOKIE_NAME']])) {
		/* Remove cookie so it does not confuse us. */
		setcookie($GLOBALS['COOKIE_NAME'], '', __request_timestamp__-100000, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
	}
	if ($GLOBALS['FUD_OPT_2'] & 256 && ($s = db_saq('SELECT ses_id, sys_id FROM fud26_ses WHERE user_id='.$id))) {
		if ($use_cookies) {
			setcookie($GLOBALS['COOKIE_NAME'], $s[0], __request_timestamp__+$GLOBALS['COOKIE_TIMEOUT'], $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
		}
		if ($s[1]) {
			q('UPDATE fud26_ses SET sys_id=\'\' WHERE ses_id=\''. $s[0] .'\'');
		}
		return $s[0];
	}

	/* If we can only have 1 login per account, 'remove' all other logins. */
	q('DELETE FROM fud26_ses WHERE user_id='. $id .' AND ses_id!=\''. $cur_ses_id .'\'');
	q('UPDATE fud26_ses SET user_id='. $id .', sys_id=\''. ses_make_sysid() .'\' WHERE ses_id=\''. $cur_ses_id .'\'');
	$GLOBALS['new_sq'] = regen_sq($id);
	if ($GLOBALS['FUD_OPT_3'] & 2097152) {
		$flag = ret_flag();
	} else {
		$flag = '';	
	}
	q('UPDATE fud26_users SET last_known_ip='. ip2long(get_ip()) .', '. $flag .' sq=\''. $GLOBALS['new_sq'] .'\' WHERE id='. $id);

	return $cur_ses_id;
}

function rebuildmodlist()
{
	$tbl =& $GLOBALS['DBHOST_TBL_PREFIX'];
	$lmt =& $GLOBALS['SHOW_N_MODS'];
	$c = uq('SELECT u.id, u.alias, f.id FROM '. $tbl .'mod mm INNER JOIN '. $tbl .'users u ON mm.user_id=u.id INNER JOIN '. $tbl .'forum f ON f.id=mm.forum_id ORDER BY f.id,u.alias');
	$u = $ar = array();

	while ($r = db_rowarr($c)) {
		$u[] = $r[0];
		if ($lmt < 1 || (isset($ar[$r[2]]) && count($ar[$r[2]]) >= $lmt)) {
			continue;
		}
		$ar[$r[2]][$r[0]] = $r[1];
	}
	unset($c);

	q('UPDATE '. $tbl .'forum SET moderators=NULL');
	foreach ($ar as $k => $v) {
		q('UPDATE '. $tbl .'forum SET moderators='. ssn(serialize($v)) .' WHERE id='. $k);
	}
	q('UPDATE '. $tbl .'users SET users_opt='. q_bitand('users_opt', ~524288) .' WHERE users_opt>=524288 AND '. q_bitand('users_opt', 524288) .'>0');
	if ($u) {
		q('UPDATE '. $tbl .'users SET users_opt='. q_bitor('users_opt', 524288) .' WHERE id IN('. implode(',', $u) .') AND '. q_bitand('users_opt', 1048576) .'=0');
	}
}

function ret_flag($raw=0)
{
	if ($raw) {
		$ip = $raw;
	} else {
		$ip = get_ip();
	}

	if ($GLOBALS['FUD_OPT_3'] & 524288) {	// ENABLE_GEO_LOCATION.
		$val = db_saq('SELECT cc, country FROM fud26_geoip WHERE '. sprintf('%u', ip2long($ip)) .' BETWEEN ips AND ipe');
		if ($raw) {
			return $val ? $val : array(null,null);
		}
		if ($val) {
			return 'flag_cc='. _esc($val[0]) .', flag_country='. _esc($val[1]).',';
		}
	}
	if ($raw) {
		return array(null, null);
	}
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
}

	if (!__fud_real_user__) {
		std_error('login');
	}
	if (!($FUD_OPT_4 & 1)) {	// ALLOW_LOGIN_CHANGES is disabled.
                std_error('disabled');
        }

	/* Change current userid to nlogin. */
	if (isset($_POST['btn_submit'], $_POST['nlogin'], $_POST['cpasswd']) && is_string($_POST['nlogin'])) {
		if (!($r = db_sab('SELECT id, login, passwd, salt FROM fud26_users WHERE login='. _esc($usr->login)))) {
			exit('Go away!');
		}

		if (__fud_real_user__ != $r->id || !((empty($r->salt) && $r->passwd == md5((string)$_POST['cpasswd'])) || $r->passwd == sha1($r->salt . sha1((string)$_POST['cpasswd'])))) {
			$ruser_error_msg = 'Неверный пароль';
		} else if (strlen($_POST['nlogin']) < 4) {
			$ruser_error_msg = 'Выбрано слишком короткое имя учётной записи — оно должно быть не короче 4-х символов.';
		} else if (is_login_blocked($_POST['nlogin'])) {
			$ruser_error_msg = 'Недопустимое имя участника.';
		} else if (get_id_by_login($_POST['nlogin'])) {
			$ruser_error_msg = 'Имена участников форума должны быть уникальными, а заданное вами имя учётной записи уже занято другим участником.';
		} else {
			q('UPDATE fud26_users SET login='. _esc($_POST['nlogin']) .' WHERE id='. $r->id);
			if (!($GLOBALS['FUD_OPT_2'] & 128)) {	// USE_ALIASES diabled, set alias = nlogin.
				q('UPDATE fud26_users SET alias='. _esc($_POST['nlogin']) .' WHERE id='. $r->id);
			}
			logaction(__fud_real_user__, 'CHANGE_USER', 0, $r->login);
			exit('<html><script>window.close();</script></html>');
		}

		$ruser_error = '<tr><td class="MsgR3 ErrorText ac" colspan="2">'.$ruser_error_msg.'</td></tr>';
	} else {
		$ruser_error = '';
	}

	$TITLE_EXTRA = ': Форма изменения логина';



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
<base href="<?php echo $GLOBALS['WWW_ROOT']; ?>" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="theme/vp1/forum.css" type="text/css" />
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground">
<form method="post" action="index.php?t=ruser"><div class="ac">
<table cellspacing="1" cellpadding="2" class="MiniTable" width="100%">
<?php echo $ruser_error; ?>
<tr><th colspan="2">Изменение вашего форумного логина</th></tr>
<tr class="RowStyleB"><td>Текущий логин:</td><td><?php echo htmlspecialchars($usr->login); ?></td></tr>
<tr class="RowStyleB"><td>Новый логин:</td><td><input type="text" name="nlogin" value="" /></td></tr>
<tr class="RowStyleB"><td>Текущий пароль:</td><td><input type="password" name="cpasswd" id="passwd" value="" /></td></tr>
<tr class="RowStyleB"><td align="right" colspan="2"><input type="submit" class="button" value="Переход" name="btn_submit" /></td></tr>
</table></div><?php echo _hs; ?></form>
</td></tr></table></body></html>
