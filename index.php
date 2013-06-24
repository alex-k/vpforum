<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: root_index.php.t 5057 2010-10-24 10:37:40Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	if (function_exists('mb_internal_encoding')) {
		mb_internal_encoding('utf-8');
	}
	require('./GLOBALS.php');

	/* Before we go on, we need to do some very basic activation checks. */
	if (!($FUD_OPT_1 & 1)) {	// FORUM_ENABLED
		fud_use('errmsg.inc');
		exit_forum_disabled();
	}

// define('fud_query_stats', 1);

if (!defined('fud_sql_lnk')) {
	$connect_func = $GLOBALS['FUD_OPT_1'] & 256 ? 'mysql_pconnect' : 'mysql_connect';

	$conn = @$connect_func($GLOBALS['DBHOST'], $GLOBALS['DBHOST_USER'], $GLOBALS['DBHOST_PASSWORD']) or fud_sql_error_handler('Initiating '. $connect_func, mysql_error(), mysql_errno(), 'Unknown');
	define('fud_sql_lnk', $conn);
	@mysql_select_db($GLOBALS['DBHOST_DBNAME'], fud_sql_lnk) or fud_sql_error_handler('Opening database '. $GLOBALS['DBHOST_DBNAME'], mysql_error(fud_sql_lnk), mysql_errno(fud_sql_lnk), db_version());
	if (function_exists('mysql_set_charset')) {	// Requires PHP 5.2.3 and MySQL 5.0.7 or later.
		mysql_set_charset('utf8');
	} else {
		mysql_query('SET NAMES \'utf8\' COLLATE \'utf8_unicode_ci\'');
	}

	define('__dbtype__', 'mysql');
}

function db_version()
{
	if (!defined('__FUD_SQL_VERSION__')) {
		$ver = mysql_fetch_row(mysql_query('SELECT VERSION()', fud_sql_lnk));
		define('__FUD_SQL_VERSION__', $ver[0]);
	}
	return __FUD_SQL_VERSION__;
}

function db_lock($tables)
{
	if (!empty($GLOBALS['__DB_INC_INTERNALS__']['db_locked'])) {
		fud_sql_error_handler('Recursive Lock', 'internal', 'internal', db_version());
	} else {
		q('LOCK TABLES '.$tables);
		$GLOBALS['__DB_INC_INTERNALS__']['db_locked'] = 1;
	}
}

function db_unlock()
{
	if (empty($GLOBALS['__DB_INC_INTERNALS__']['db_locked'])) {
		unset($GLOBALS['__DB_INC_INTERNALS__']['db_locked']);
		fud_sql_error_handler('DB_UNLOCK: no previous lock established', 'internal', 'internal', db_version());
	}
	
	if (--$GLOBALS['__DB_INC_INTERNALS__']['db_locked'] < 0) {
		unset($GLOBALS['__DB_INC_INTERNALS__']['db_locked']);
		fud_sql_error_handler('DB_UNLOCK: unlock overcalled', 'internal', 'internal', db_version());
	}
	unset($GLOBALS['__DB_INC_INTERNALS__']['db_locked']);
	q('UNLOCK TABLES');
}

function db_locked()
{
	return isset($GLOBALS['__DB_INC_INTERNALS__']['db_locked']);
}

function db_affected()
{
	return mysql_affected_rows(fud_sql_lnk);	
}

if (!defined('fud_query_stats')) {
	function q($query)
	{
		$r = mysql_query($query, fud_sql_lnk) or fud_sql_error_handler($query, mysql_error(fud_sql_lnk), mysql_errno(fud_sql_lnk), db_version());
		return $r;
	}
	function uq($query)
	{
		$r = mysql_unbuffered_query($query,fud_sql_lnk) or fud_sql_error_handler($query, mysql_error(fud_sql_lnk), mysql_errno(fud_sql_lnk), db_version());
		return $r;
	}
} else {
	function q($query)
	{
		if (!isset($GLOBALS['__DB_INC_INTERNALS__']['query_count'])) {
			$GLOBALS['__DB_INC_INTERNALS__']['query_count'] = 1;
		} else {
			++$GLOBALS['__DB_INC_INTERNALS__']['query_count'];
		}

		if (!isset($GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'])) {
			$GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] = 0;
		}

		$s = microtime(true);
		$result = mysql_query($query, fud_sql_lnk) or fud_sql_error_handler($query, mysql_error(fud_sql_lnk), mysql_errno(fud_sql_lnk), db_version());
		$e = microtime(true);

		$GLOBALS['__DB_INC_INTERNALS__']['last_time'] = ($e - $s);
		$GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] += $GLOBALS['__DB_INC_INTERNALS__']['last_time'];
		$GLOBALS['__DB_INC_INTERNALS__']['last_query'] = $query;

		echo '<pre>'. preg_replace('!\s+!', ' ', $query) .'</pre>';
		echo '<pre>query count: '. $GLOBALS['__DB_INC_INTERNALS__']['query_count'] .' time taken: '. $GLOBALS['__DB_INC_INTERNALS__']['last_time'] .'</pre>';
		echo '<pre>Affected rows: '. db_affected() .'</pre>';
		echo '<pre>total sql time: '. $GLOBALS['__DB_INC_INTERNALS__']['total_sql_time'] .'</pre>';

		return $result; 
	}

	function uq($query)
	{
		return q($query);
	}
}

function db_rowobj($result)
{
	return mysql_fetch_object($result);
}

function db_rowarr($result)
{
	return mysql_fetch_row($result);
}

function q_singleval($query)
{
	if (($res = mysql_fetch_row(q($query))) !== false) {
		return $res[0];
	}
}

function q_limit($query, $limit, $off=0)
{
	// OLD SYNTAX: return $query .' LIMIT '. $off .','. $limit;
	return $query .' LIMIT '. $limit .' OFFSET '. $off;
}

function q_concat($arg)
{
	// MySQL badly breaks the SQL standard by redefining || to mean OR. 
	$tmp = func_get_args();
	return 'CONCAT('. implode(',', $tmp) .')';
}

function q_rownum() {
	q('SET @seq=0');		// For simulating rownum.
	return '(@seq:=@seq+1)';
}

function q_bitand($fieldLeft, $fieldRight) {
	return $fieldLeft .' & '. $fieldRight;
}

function q_bitor($fieldLeft, $fieldRight) {
	return $fieldLeft .' | '. $fieldRight;
}

function q_bitnot($bitField) {
	return '~'. $bitField;
}

function db_saq($q)
{
	return mysql_fetch_row(q($q));
}
function db_sab($q)
{
	return mysql_fetch_object(q($q));
}
function db_qid($q)
{
	q($q);
	return mysql_insert_id(fud_sql_lnk);
}
function db_arr_assoc($q)
{
	return mysql_fetch_array(q($q), MYSQL_ASSOC);
}

function db_fetch_array($q)
{
        return mysql_fetch_array($q,  MYSQL_ASSOC);
}

function db_li($q, &$ef, $li=0)
{
	$r = mysql_query($q, fud_sql_lnk);
	if ($r) {
		return ($li ? mysql_insert_id(fud_sql_lnk) : $r);
	}

	/* Duplicate key. */
	if (mysql_errno(fud_sql_lnk) == 1062) {
		$ef = ltrim(strrchr(mysql_error(fud_sql_lnk), ' '));
		return null;
	} else {
		fud_sql_error_handler($q, mysql_error(fud_sql_lnk), mysql_errno(fud_sql_lnk), db_version());
	}
}

function ins_m($tbl, $flds, $types, $vals)
{
	q('INSERT IGNORE INTO '. $tbl .' ('. $flds .') VALUES ('. implode('),(', $vals) .')');
}

function db_all($q)
{
	$f = array();
	$c = uq($q);
	while ($r = mysql_fetch_row($c)) {
		$f[] = $r[0];
	}
	return $f;
}

function _esc($s)
{
	return '\''. mysql_real_escape_string($s, fud_sql_lnk) .'\'';
}function ses_make_sysid()
{
	if ($GLOBALS['FUD_OPT_2'] & 256) {	// MULTI_HOST_LOGIN
		return;
	}

	$keys = array('HTTP_USER_AGENT', 'SERVER_PROTOCOL', 'HTTP_ACCEPT_CHARSET', 'HTTP_ACCEPT_ENCODING', 'HTTP_ACCEPT_LANGUAGE');
	if ($GLOBALS['FUD_OPT_3'] & 16) {	// SESSION_IP_CHECK
		$keys[] = 'HTTP_X_FORWARDED_FOR';
		$keys[] = 'REMOTE_ADDR';
	}
	$pfx = '';
	foreach ($keys as $v) {
		if (isset($_SERVER[$v])) {
			$pfx .= $_SERVER[$v];
		}
	}
	return md5($pfx);
}

function ses_get($id=0)
{
	if (!$id) {
		if (!empty($_COOKIE[$GLOBALS['COOKIE_NAME']])) {
			$q_opt = 's.ses_id='. _esc($_COOKIE[$GLOBALS['COOKIE_NAME']]);
		} else if ((isset($_GET['S']) || isset($_POST['S'])) && $GLOBALS['FUD_OPT_1'] & 128) {
			$url_s = 1;
			$q_opt = 's.ses_id='._esc((isset($_GET['S']) ? (string) $_GET['S'] : (string) $_POST['S']));
			/* Do not validate against expired URL sessions. */
			$q_opt .= ' AND s.time_sec > '. (__request_timestamp__ - $GLOBALS['SESSION_TIMEOUT']);
		} else {
			return;
		}
		if ($GLOBALS['FUD_OPT_3'] & 4 && isset($_SERVER['HTTP_REFERER']) && strncmp($_SERVER['HTTP_REFERER'], $GLOBALS['WWW_ROOT'], strlen($GLOBALS['WWW_ROOT']))) {
			/* more checks, we need those because some proxies mangle referer field */
			$host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
			/* $p > 8 https:// or http:// */
			if (($p = strpos($_SERVER['HTTP_REFERER'], $host)) === false || $p > 8) {
				$q_opt .= ' AND s.user_id > 2000000000 ';
			}
		}
	} else {
		$q_opt = 's.id='. $id;
	}

	$u = db_sab('SELECT
		s.id AS sid, s.ses_id, s.data, s.returnto, s.sys_id,
		t.id AS theme_id, t.lang, t.name AS theme_name, t.locale, t.theme, t.pspell_lang, t.theme_opt,
		u.alias, u.posts_ppg, u.time_zone, u.sig, u.last_visit, u.last_read, u.cat_collapse_status, u.users_opt, u.posted_msg_count, u.topics_per_page,
		u.ignore_list, u.ignore_list, u.buddy_list, u.id, u.group_leader_list, u.email, u.login, u.sq, u.ban_expiry, u.flag_cc
	FROM fud26_ses s
		INNER JOIN fud26_users u ON u.id=(CASE WHEN s.user_id>2000000000 THEN 1 ELSE s.user_id END)
		LEFT OUTER JOIN fud26_themes t ON t.id=u.theme
	WHERE '. $q_opt);

	/* Anon user, no session or login. */
	if (!$u || $u->id == 1 || $id) {
		return $u;
	}

	if ($u->sys_id == ses_make_sysid()) {
		return $u;
	} else if ($GLOBALS['FUD_OPT_3'] & 16 || isset($url_s)) {
		/* URL sessions must validate sys_id check and SESSION_IP_CHECK must be disabled */
		return;
	}

	/* Try doing a strict SQ match in last-ditch effort to make things 'work'. */
	if (isset($_POST['SQ']) && $_POST['SQ'] == $u->sq) {
		return $u;
	}

	return;
}

function ses_anon_make()
{
	do {
		$uid = 2000000000 + mt_rand(1, 147483647);
		$ses_id = md5($uid . __request_timestamp__ . getmypid());
	} while (!($id = db_li('INSERT INTO fud26_ses (ses_id, time_sec, sys_id, user_id) VALUES (\''. $ses_id .'\', '. __request_timestamp__ .', \''. ses_make_sysid() .'\', '. $uid .')', $ef, 1)));

	/* When we have an anon user, we set a special cookie allowing us to see who referred this user. */
	if (isset($_GET['rid']) && !isset($_COOKIE['frm_referer_id']) && $GLOBALS['FUD_OPT_2'] & 8192) {
		setcookie($GLOBALS['COOKIE_NAME'] .'_referer_id', $_GET['rid'], __request_timestamp__+31536000, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
	}
	setcookie($GLOBALS['COOKIE_NAME'], $ses_id, __request_timestamp__+$GLOBALS['COOKIE_TIMEOUT'], $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);

	return ses_get($id);
}

function ses_update_status($ses_id, $str=null, $forum_id=0, $ret='')
{
	if (empty($ses_id)) {
		die('FATAL ERROR: No session, cannot update status!');
	}
	q('UPDATE fud26_ses SET sys_id=\''. ses_make_sysid() .'\', forum_id='. $forum_id .', time_sec='. __request_timestamp__ .', action='. ($str ? _esc($str) : 'NULL') .', returnto='. (!is_int($ret) ? (isset($_SERVER['QUERY_STRING']) ? _esc($_SERVER['QUERY_STRING']) : 'NULL') : 'returnto') .' WHERE id='. $ses_id);
}

function ses_putvar($ses_id, $data)
{
	$cond = is_int($ses_id) ? 'id='. (int)$ses_id : 'ses_id=\''. $ses_id .'\'';

	if (empty($data)) {
		q('UPDATE fud26_ses SET data=NULL WHERE '. $cond);
	} else {
		q('UPDATE fud26_ses SET data='. _esc(serialize($data)) .' WHERE '. $cond);
	}
}

function ses_delete($ses_id)
{
	if (!($GLOBALS['FUD_OPT_2'] & 256)) {	// MULTI_HOST_LOGIN
		q('DELETE FROM fud26_ses WHERE id='. $ses_id);
	}
	setcookie($GLOBALS['COOKIE_NAME'], '', __request_timestamp__-100000, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);

	return 1;
}

function ses_anonuser_auth($id, $error)
{
	if (!empty($_POST)) {
		$_SERVER['QUERY_STRING'] = '';
	}
	q('UPDATE fud26_ses SET data='. _esc(serialize($error)) .', returnto='. ssn($_SERVER['QUERY_STRING']) .' WHERE id='. $id);
	if ($GLOBALS['FUD_OPT_2'] & 32768) {	// USE_PATH_INFO
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/l/'. _rsidl);
	} else {
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=login&'. _rsidl);
	}
	exit;
}function &init_user()
{
	$o1 =& $GLOBALS['FUD_OPT_1'];
	$o2 =& $GLOBALS['FUD_OPT_2'];

	if ($o2 & 32768 && empty($_SERVER['PATH_INFO']) && !empty($_SERVER['ORIG_PATH_INFO'])) {
		$_SERVER['PATH_INFO'] = $_SERVER['ORIG_PATH_INFO'];
	}

	/* We need to parse S & rid right away since they are used during user init. */
	if ($o2 & 32768 && !empty($_SERVER['PATH_INFO'])) {
		$pb = $p = explode('/', substr($_SERVER['PATH_INFO'], 1, -1));
		if ($o1 & 128) {
			$_GET['S'] = array_pop($p);
		}
		if ($o2 & 8192) {
			$_GET['rid'] = array_pop($p);
		}
		$_SERVER['QUERY_STRING'] = htmlspecialchars($_SERVER['PATH_INFO']) .'?'. $_SERVER['QUERY_STRING'];

		/* Continuation of path info parsing. */
		if (!isset($p[0])) {
			$p[0] = 'i';
		}
		/* Notice prevention code. */
		for ($i = 1; $i < 5; $i++) {
			if (!isset($p[$i])) {
				$p[$i] = null;
			}
		}
		
		switch ($p[0]) {
			case 'm': /* goto specific message */
				$_GET['t'] = 0;
				$_GET['goto'] = $p[1];
				if (isset($p[2])) {
					$_GET['th'] = $p[2];
					if (isset($p[3])) {
						$_GET['start'] = $p[3];
						if ($p[3]) {
							$_GET['t'] = 'msg';
							unset($_GET['goto']);
						}

						if (isset($p[4])) {
							if ($p[4] === 'prevloaded') {
								$_GET['prevloaded'] = 1;
								$i = 5;
							} else {
								$i = 4;
							}

							if (isset($p[$i])) {
								$_GET['rev'] = $p[$i];
								if (isset($p[$i+1])) {
									$_GET['reveal'] = $p[$i+1];
								}
							}
						}
					}
				}
				break;

			case 't': /* view thread */
				$_GET['t'] = 0;
				$_GET['th'] = $p[1];
				if (isset($p[2])) {
					$_GET['start'] = $p[2];
					if (!empty($p[3])) {
						$_GET[$p[3]] = 1;
					}
				}
				break;

			case 'f': /* view forum */
				$_GET['t'] = 1;
				$_GET['frm_id'] = $p[1];
				if (isset($p[2])) {
					$_GET['start'] = $p[2];
					if (isset($p[3])) {
						if ($p[3] === '0') {
							$_GET['sub'] = 1;
						} else {
							$_GET['unsub'] = 1;
						}
					}
				}
				break;

			case 'r':
				$_GET['t'] = 'post';
				$_GET[$p[1]] = $p[2];
				if (isset($p[3])) {
					$_GET['reply_to'] = $p[3];
					if (isset($p[4])) {
						if ($p[4]) {
							$_GET['quote'] = 'true';
						}
						if (isset($p[5])) {
							$_GET['start'] = $p[5];
						}
					}
				}
				break;

			case 'u': /* view user's info */
				$_GET['t'] = 'usrinfo';
				$_GET['id'] = $p[1];
				break;

			case 'i':
				$_GET['t'] = 'index';
				if (isset($p[1])) {
					$_GET['cat'] = (int) $p[1];
				}
				break;

			case 'fa':
				$_GET['t'] = 'getfile';
				$_GET['id'] = isset($p[1]) ? $p[1] : $pb[1];
				if (!empty($p[2])) {
					$_GET['private'] = 1;
				}
				break;

			case 'sp': /* show posts */
				$_GET['t'] = 'showposts';
				$_GET['id'] = $p[1];
				if (isset($p[2])) {
					$_GET['so'] = $p[2];
					if (isset($p[3])) {
						$_GET['start'] = $p[3];
					}
				}
				break;

			case 'l': /* login/logout */
				$_GET['t'] = 'login';
				if (isset($p[1])) {
					$_GET['logout'] = 1;
				}
				break;

			case 'e':
				$_GET['t'] = 'error';
				break;

			case 'st':
				$_GET['t'] = $p[1];
				$_GET['th'] = $p[2];
				$_GET['notify'] = $p[3];
				$_GET['opt'] = $p[4] ? 'on' : 'off';
				if (isset($p[5])) {
					$_GET['start'] = $p[5];
				}
				break;

			case 'sf':
				$_GET['t'] = $p[1];
				$_GET['frm_id'] = $p[2];
				$_GET[$p[3]] = 1;
				$_GET['start'] = $p[4];
				break;

			case 'sl': /* subscribed topic list */
				$_GET['t'] = 'subscribed';
				if ($p[1] == 'start') {
					$_GET['start'] = $p[2];
				} else {
					if (isset($p[2])) {
						$_GET['th'] = $p[2];
					} else if (isset($p[1])) {
						$_GET['frm_id'] = $p[1];
					}
				}
				break;

			case 'bml': /* bookmark list */
				$_GET['t'] = 'bookmarked';
				if ($p[1] == 'start') {
					$_GET['start'] = $p[2];
				} else {
					if (isset($p[2])) {
						$_GET['th'] = $p[2];
					}
				}
				break;

			case 'pmm':
				$_GET['t'] = 'ppost';
				if (isset($p[1], $p[2])) {
					$_GET[$p[1]] = $p[2];
					if (isset($p[3])) {
						$_GET['rmid'] = $p[3];
					}
				}
				break;

			case 'pmv':
				$_GET['t'] = 'pmsg_view';
				$_GET['id'] = $p[1];
				if (isset($p[2])) {
					$_GET['dr'] = 1;
				}
				break;

			case 'pdm':
				$_GET['t'] = 'pmsg';
				if (isset($p[1])) {
					if ($p[1] !== 'btn_delete') {
						$_GET['folder_id'] = $p[1];
					} else {
						$_GET['btn_delete'] = 1;
						$_GET['sel'] = $p[2];
					}
					if (isset($p[3])) {
						$_GET['s'] = $p[3];
						$_GET['o'] = $p[4];
						$_GET['start'] = $p[5];
					}
				}
				break;

			case 'pl': /* poll list */
				$_GET['t'] = 'polllist';
				if (isset($p[1])) {
					$_GET['uid'] = $p[1];
					if (isset($p[2])) {
						$_GET['start'] = $p[2];
						if (isset($p[3])) {
							$_GET['oby'] = $p[3];
						}
					}
				}
				break;

			case 'ml': /* member list */
				$_GET['t'] = 'finduser';
				if (isset($p[1])) {
					switch ($p[1]) {
						case 1: case 2: $_GET['pc'] = $p[1]; break;
						case 3: case 4: $_GET['us'] = $p[1]; break;
						case 5: case 6: $_GET['rd'] = $p[1]; break;
						case 7: case 8: $_GET['fl'] = $p[1]; break;
						case 9: case 10: $_GET['lv'] = $p[1]; break;
					}
					if (isset($p[2])) {
						$_GET['start'] = $p[2];
						if (isset($p[3])) {
							$_GET['usr_login'] = urldecode($p[3]);
							if (isset($p[4])) {
								$_GET['js_redr'] = $p[5];
							}
						}
					}
				}
				break;

			case 'h': /* help */
				$_GET['t'] = 'help_index';
				if (isset($p[1])) {
					$_GET['section'] = $p[1];
				}
				break;

			case 'cv': /* change thread view mode */
				$_GET['t'] = $p[1];
				$_GET['frm_id'] = $p[2];
				break;

			case 'mv': /* change message view mode */
				$_GET['t'] = $p[1];
				$_GET['th'] = $p[2];
				if (isset($p[3])) {
					if ($p[3] !== '0') {
						$_GET['goto'] = $p[3];
					} else {
						$_GET['prevloaded'] = 1;
						$_GET['start'] = $p[4];
						if (isset($p[5])) {
							$_GET['rev'] = $p[5];
							if (isset($p[6])) {
								$_GET['reveal'] = $p[6];
							}
						}
					}
				}
				break;

			case 'pv':
				$_GET['t'] = 0;
				if (isset($p[1])) {
					$_GET['goto'] = q_singleval('SELECT id FROM fud26_msg WHERE poll_id='.(int)$p[1]);
					$_GET['pl_view'] = empty($p[2]) ? 0 : (int)$p[2];
				}
				break;

			case 'rm': /* report message */
				$_GET['t'] = 'report';
				$_GET['msg_id'] = $p[1];
				break;

			case 'rl': /* list of reported messages */
				$_GET['t'] = 'reported';
				if (isset($p[1])) {
					$_GET['del'] = $p[1];
				}
				break;

			case 'd': /* delete thread/message */
				$_GET['t'] = 'mmod';
				$_GET['del'] = $p[1];
				if (isset($p[2])) {
					$_GET['th'] = $p[2];
				}
				break;

			case 'em': /* email forum member */
				$_GET['t'] = 'email';
				$_GET['toi'] = $p[1];
				break;

			case 'mar': /* mark all/forum read */
				$_GET['t'] = 'markread';
				if (isset($p[1])) {
					$_GET['id'] = $p[1];
					if (isset($p[2])) {
						$_GET['cat'] = $p[2];
					}
				}
				break;

			case 'bl': /* buddy list */
				$_GET['t'] = 'buddy_list';
				if (isset($p[1])) {
					if (!empty($p[2])) {
						$_GET['add'] = $p[1];
					} else {
						$_GET['del'] = $p[1];
					}
					if (isset($p[3])) {
						$_GET['redr'] = 1;
					}
				}
				break;

			case 'il': /* ignore list */
				$_GET['t'] = 'ignore_list';
				if (isset($p[1])) {
					if (!empty($p[2])) {
						$_GET['add'] = $p[1];
					} else {
						$_GET['del'] = $p[1];
					}
					if (isset($p[3])) {
						$_GET['redr'] = 1;
					}
				}
				break;

			case 'lk': /* lock/unlock thread */
				$_GET['t'] = 'mmod';
				$_GET['th'] = $p[1];
				$_GET[$p[2]] = 1;
				break;

			case 'stt': /* split thread */
				$_GET['t'] = 'split_th';
				if (isset($p[1])) {
					$_GET['th'] = $p[1];
				}
				break;

			case 'ef': /* email to friend */
				$_GET['t'] = 'remail';
				$_GET['th'] = $p[1];
				break;

			case 'lr': /* list referers */
				$_GET['t'] = 'list_referers';
				if (isset($p[1])) {
					$_GET['start'] = $p[1];
				}
				break;

			case 'a':
				$_GET['t'] = 'actions';
				if (isset($p[1], $p[2])) {
					$_GET['o'] = $p[1];
					$_GET['s'] = $p[2];
				}
				break;

			case 's':
				$_GET['t'] = 'search';
				if (isset($p[1])) {
					$_GET['srch'] = urldecode($p[1]);
					$_GET['field'] = isset($p[2]) ? $p[2] : '';
					$_GET['search_logic'] = isset($p[3]) ? $p[3] : '';
					$_GET['sort_order'] = isset($p[4]) ? $p[4] : '';
					$_GET['forum_limiter'] = isset($p[5]) ? $p[5] : '';
					$_GET['start'] = isset($p[6]) ? $p[6] : '';
					$_GET['author'] = isset($p[7]) ? $p[7] : '';
				}
				break;

			case 'p':
				if (!is_numeric($p[1])) {
					$_GET[$p[1]] = $p[2];
				} else {
					$_GET['frm'] = $p[1];
					$_GET['page'] = $p[2];
				}
				break;

			case 'ot':
				$_GET['t'] = 'online_today';
				if (isset($p[1], $p[2])) {
					$_GET['o'] = $p[1];
					$_GET['s'] = $p[2];
				}
				break;

			case 're':
				$_GET['t'] = 'register';
				if (isset($p[1])) {
					$_GET['reg_coppa'] = $p[1];
				}
				break;

			case 'tt':
				$_GET['t'] = $p[1];
				$_GET['frm_id'] = $p[2];
				break;

			case 'mh':
				$_GET['t'] = 'mvthread';
				$_GET['th'] = $p[1];
				if (isset($p[2], $p[3])) {
					$_GET[$p[2]] = $p[3];
				}
				break;

			case 'mn':
				$_GET['t'] = $p[1];
				$_GET['th'] = $p[2];
				$_GET['notify'] = $p[3];
				$_GET['opt'] = $p[4];
				if (isset($p[5])) {
					if ($p[1] == 'msg') {
						$_GET['start'] = $p[5];
					} else {
						$_GET['mid'] = $p[5];
					}
				}
				break;

			case 'bm': /* bookmark/unbookmark a topic */
				$_GET['t'] = $p[1];
				$_GET['th'] = $p[2];
				$_GET['bookmark'] = $p[3];
				$_GET['opt'] = $p[4];
				if (isset($p[5])) {
					if ($p[1] == 'msg') {
						$_GET['start'] = $p[5];
					} else {
						$_GET['mid'] = $p[5];
					}
				}
				break;

			case 'tr':
				$_GET['t'] = 'ratethread';
				break;

			case 'gm':
				$_GET['t'] = 'groupmgr';
				if (isset($p[1], $p[2], $p[3])) {
					$_GET[$p[1]] = $p[2];
					$_GET['group_id'] = $p[3];
				}
				break;

			case 'te':
				$_GET['t'] = 'thr_exch';
				if (isset($p[1], $p[2])) {
					$_GET[$p[1]] = $p[2];
				}
				break;

			case 'mq':
				$_GET['t'] = 'modque';
				if (isset($p[1], $p[2])) {
					$_GET[$p[1]] = $p[2];
				}
				break;

			case 'pr':
				$_GET['t'] = 'pre_reg';
				$_GET['coppa'] = $p[1];
				break;

			case 'qb':
				$_GET['t'] = 'qbud';
				break;

			case 'po':
				$_GET['t'] = 'poll';
				$_GET['frm_id'] = $p[1];
				if (isset($p[2])) {
					$_GET['pl_id'] = $p[2];
					if (isset($p[3], $p[4])) {
						$_GET[$p[3]] = $p[4];
					}
				}
				break;

			case 'sm':
				$_GET['t'] = 'smladd';
				break;

			case 'mk':
				$_GET['t'] = 'mklist';
				$_GET['tp'] = $p[1];
				break;

			case 'rp':
				$_GET['t'] = 'rpasswd';
				break;

			case 'as':
				$_GET['t'] = 'avatarsel';
				break;

			case 'sel':
				$_GET['t'] = 'selmsg';
				$c = count($p) - 1;
				if ($c % 2) {
					--$c;
				}
				$c /= 2;
				$i = 0;
				while ($c--) {
					$_GET[$p[++$i]] = $p[++$i];
				}
				break;

			case 'pml':
				$_GET['t'] = 'pmuserloc';
				$_GET['js_redr'] = $p[1];
				if (isset($p[2])) {
					$_GET['overwrite'] = 1;
				}
				break;

			case 'rst':
				$_GET['t'] = 'reset';
				if (isset($p[1])) {
					$_GET['email'] = urldecode($p[1]);
				}
				break;

			case 'cpf':
				$_GET['t'] = 'coppa_fax';
				break;

			case 'cp':
				$_GET['t'] = 'coppa';
				break;

			case 'rc':
				$_GET['t'] = 'reg_conf';
				break;

			case 'ma':
				$_GET['t'] = 'mnav';
				if (isset($p[1])) {
					$_GET['rng'] = isset($p[1]) ? $p[1] : 0;
					$_GET['rng2'] = isset($p[2]) ? $p[2] : 0;
					$_GET['u'] = isset($p[3]) ? $p[3] : 0;
					$_GET['start'] = isset($p[4]) ? $p[4] : 0;
					$_GET['sub'] = !empty($p[5]);
				}
				break;

			case 'ip':
				$_GET['t'] = 'ip';
				if (isset($p[1])) {
					$_GET[($p[1][0] == 'i' ? 'ip' : 'user')] = isset($p[2]) ? $p[2] : '';
				}
				break;

			case 'met':
				$_GET['t'] = 'merge_th';
				if (isset($p[1])) {
					$_GET['frm_id'] = $p[1];
				}
				break;

			case 'uc':
				$_GET['t'] = 'uc';
				if (isset($p[1], $p[2])) {
					$_GET[$p[1]] = $p[2];
				}
				break;

			case 'mmd':
				$_GET['t'] = 'mmd';
				break;
				
			case 'cal':	/* Calendar */
				$_GET['t'] = 'calendar';
				break;

			case 'page':	/* Static page */
				$_GET['t'] = 'page';
				if (isset($p[1])) {
					$_GET['id'] = $p[1];
				}
				break;

			default:
				$_GET['t'] = 'index';
				break;
		}
		$GLOBALS['t'] = $_GET['t'];
	} else if (isset($_GET['t'])) {
		$GLOBALS['t'] = (string) $_GET['t'];
	} else if (isset($_POST['t'])) {
		$GLOBALS['t'] = (string) $_POST['t'];
	} else {
		$GLOBALS['t'] = 'index';
	}

	if ($GLOBALS['t'] == 'register') {
		$GLOBALS['THREADS_PER_PAGE_F'] = $GLOBALS['THREADS_PER_PAGE']; // Store old value.
	}

	header('P3P: CP="ALL CUR OUR IND UNI ONL INT CNT STA"'); /* P3P Policy. */

	$sq = 0;
	/* Fetch an object with the user's session, profile & theme info. */
	if (!($u = ses_get())) {
		/* New anon user. */
		$u = ses_anon_make();
	} else if ($u->id != 1 && (!$GLOBALS['is_post'] || sq_check(1, $u->sq, $u->id, $u->ses_id))) { /* Store the last visit date for registered user. */
		q('UPDATE fud26_users SET last_visit='. __request_timestamp__ .' WHERE id='. $u->id);
		if ($GLOBALS['FUD_OPT_3'] & 1) {
			setcookie($GLOBALS['COOKIE_NAME'], $u->ses_id, 0, $GLOBALS['COOKIE_PATH'], $GLOBALS['COOKIE_DOMAIN']);
		}
		if (!$u->sq || __request_timestamp__ - $u->last_visit > 180) {
			$u->sq = $sq = regen_sq($u->id);
			if (!$GLOBALS['is_post']) {
				$_GET['SQ'] = $sq;
			} else {
				$_POST['SQ'] = $sq;
			}
		} else {
			$sq =& $u->sq;
		}
	}
	if ($GLOBALS['is_post'] || $u->id > 1) {
		header('Cache-Control: no-store, private, must-revalidate, proxy-revalidate, post-check=0, pre-check=0, max-age=0, s-maxage=0');
		header('Expires: Mon, 21 Jan 1980 06:01:01 GMT');
		header('Pragma: no-cache');
	}

	if ($u->data) {
		$u->data = unserialize($u->data);
	}
	$uo = $u->users_opt = (int) $u->users_opt;

	/* This should allow path_info & normal themes to work properly within 1 forum. */
	if ($o2 & 32768 && !($u->theme_opt & 4)) {
		$o2 ^= 32768;
	}

	/* Handle PM disabling for users. */
	if (!($GLOBALS['is_a'] = $uo & 1048576) && $uo & 33554432) {
		$o1 = $o1 &~ 1024;
	}

	/* Set timezone. */
	if (empty($u->time_zone) || @date_default_timezone_set($u->time_zone) === FALSE) {
		date_default_timezone_set($GLOBALS['SERVER_TZ']);
	}

	/* Set locale. */
	$GLOBALS['good_locale'] = setlocale(LC_ALL, $u->locale);

	/* Call inituser plugins. */
	if (defined('plugins')) {
		plugin_call_hook('INITUSER', $u);
	}

	/* View format for threads & messages. */
	define('d_thread_view', $uo & 256 ? 'msg' : 'tree');
	define('t_thread_view', $uo & 128 ? 'thread' : 'threadt');
	if ($GLOBALS['t'] === 0) {
		$GLOBALS['t'] = $_GET['t'] = d_thread_view;
	} else if ($GLOBALS['t'] === 1) {
		$GLOBALS['t'] = $_GET['t'] = t_thread_view;
	}

	/* Theme path. */
	if (!defined('fud_theme')) {
		define('fud_theme', 'theme/'. ($u->theme_name ? $u->theme_name : 'default') .'/');
	}

	/* Define _uid, which, will tell us if this is a 'real' user or not. */
	define('__fud_real_user__', ($u->id != 1 ? $u->id : 0));
	define('_uid', __fud_real_user__ && ($uo & 131072) && !($uo & 2097152) ? $u->id : 0);

	/* Allow user to set their own topics per page value, as long as it is smaller then the max. */
	if (__fud_real_user__ && $GLOBALS['THREADS_PER_PAGE'] > $u->topics_per_page) {
		$GLOBALS['THREADS_PER_PAGE'] = (int) $u->topics_per_page;
	}

	$GLOBALS['sq'] = $sq;

	/* Define constants used to track URL sessions & referrals. */
	if ($o1 & 128) {
		define('s', $u->ses_id); define('_hs', '<input type="hidden" name="S" value="'. s .'" /><input type="hidden" name="SQ" value="'. $sq .'" />');
		if ($o2 & 8192) {
			if ($o2 & 32768) {
				define('_rsid', __fud_real_user__ .'/'. s .'/');
			} else {
				define('_rsid', 'rid='. __fud_real_user__ .'&amp;S='. s);
			}
		} else {
			if ($o2 & 32768) {
				define('_rsid', s .'/');
			} else {
				define('_rsid', 'S='. s);
			}
		}
	} else {
		define('s', ''); define('_hs', '<input type="hidden" name="SQ" value="'. $sq .'" />');
		if ($o2 & 8192) {
			if ($o2 & 32768) {
				define('_rsid', __fud_real_user__ .'/');
			} else {
				define('_rsid', 'rid='. __fud_real_user__);
			}
		} else {
			define('_rsid', '');
		}
	}
	define('_rsidl', ($o2 & 32768 ? _rsid : str_replace('&amp;', '&', _rsid)));

	return $u;
}

function user_register_forum_view($frm_id)
{
	if (__dbtype__ == 'mysql') {	// MySQL optimization.
		q('INSERT INTO fud26_forum_read (forum_id, user_id, last_view) VALUES ('. $frm_id .', '. _uid .', '. __request_timestamp__ .') ON DUPLICATE KEY UPDATE last_view=VALUES(last_view)');
		return;
	}
	
	if (!db_li('INSERT INTO fud26_forum_read (forum_id, user_id, last_view) VALUES ('. $frm_id .', '. _uid .', '. __request_timestamp__ .')', $ef)) {
		q('UPDATE fud26_forum_read SET last_view='. __request_timestamp__ .' WHERE forum_id='. $frm_id .' AND user_id='. _uid);
	}
}

function user_register_thread_view($thread_id, $tm=__request_timestamp__, $msg_id=0)
{
	if (__dbtype__ == 'mysql') {    // MySQL optimization.
		q('INSERT INTO fud26_read (last_view, msg_id, thread_id, user_id) VALUES('. $tm .', '. $msg_id .', '. $thread_id .', '. _uid .') ON DUPLICATE KEY UPDATE last_view=VALUES(last_view), msg_id=VALUES(msg_id)');
		return;
	}

	if (!db_li('INSERT INTO fud26_read (last_view, msg_id, thread_id, user_id) VALUES('. $tm .', '. $msg_id .', '. $thread_id .', '. _uid .')', $ef)) {
		q('UPDATE fud26_read SET last_view='. $tm .', msg_id='. $msg_id .' WHERE thread_id='. $thread_id .' AND user_id='. _uid);
	}
}

function user_set_post_count($uid)
{
	$pd = db_saq('SELECT MAX(id),count(*) FROM fud26_msg WHERE poster_id='. $uid .' AND apr=1');
	$level_id = (int) q_singleval('SELECT id FROM fud26_level WHERE post_count <= '. $pd[1] .' ORDER BY post_count DESC LIMIT 1');
	q('UPDATE fud26_users SET u_last_post_id='. (int)$pd[0] .', posted_msg_count='. (int)$pd[1] .', level_id='. $level_id .' WHERE id='. $uid);
}

function user_mark_all_read($id)
{
	q('UPDATE fud26_users SET last_read='. __request_timestamp__ .' WHERE id='. $id);
	q('DELETE FROM fud26_read WHERE user_id='. $id);
	q('DELETE FROM fud26_forum_read WHERE user_id='. $id);
}

function user_mark_forum_read($id, $fid, $last_view)
{
	if (__dbtype__ == 'mysql') {	// MySQL optimization.
		q('INSERT INTO fud26_read (user_id, thread_id, msg_id, last_view) SELECT '. $id .', id, last_post_id, '. __request_timestamp__ .' FROM fud26_thread WHERE forum_id='. $fid .' AND last_post_date > '. $last_view .' ON DUPLICATE KEY UPDATE last_view=VALUES(last_view), msg_id=VALUES(msg_id)');
	} else if (__dbtype__ == 'sqlite') {	// SQLite optimization.
		q('REPLACE INTO fud26_read (user_id, thread_id, msg_id, last_view) SELECT '. $id .', id, last_post_id, '. __request_timestamp__ .' FROM fud26_thread WHERE forum_id='. $fid .' AND last_post_date > '. $last_view);
	} else {	// Other databases.
		if (!db_li('INSERT INTO fud26_read (user_id, thread_id, msg_id, last_view) SELECT '. $id .', id, last_post_id, '. __request_timestamp__ .' FROM fud26_thread WHERE forum_id='. $fid .' AND last_post_date > '. $last_view, $ef)) {
			q('UPDATE fud26_read SET user_id='. $id .', msg_id=t.last_post_id, last_view='. __request_timestamp__ .' FROM (SELECT id, last_post_id FROM fud26_thread WHERE forum_id='. $fid .' AND last_post_date > '. $last_view .') t WHERE user_id='. $id .' AND thread_id=t.id');
		}
	}
	user_register_forum_view($fid);
}

function sq_check($post, &$sq, $uid=__fud_real_user__, $ses=s)
{
	/* No sequence # check for anonymous users. */
	if (!$uid) {
		return 1;
	}

	if ($post && isset($_POST['SQ'])) {
		$s = $_POST['SQ'];
	} else if (!$post && isset($_GET['SQ'])) {
		$s = $_GET['SQ'];
	} else {
		$s = 0;
	}

	if ($sq !== $s) {
		if ($GLOBALS['t'] == 'post' || $GLOBALS['t'] == 'ppost') {
			define('fud_bad_sq', 1);
			$sq = regen_sq($uid);
			return 1;
		}
		header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?S='. $ses);
		exit;
	}

	return 1;
}

function regen_sq($uid=__fud_real_user__)
{
	$sq = md5(get_random_value(128));
	q('UPDATE fud26_users SET sq=\''. $sq .'\' WHERE id='. $uid);
	return $sq;
}

if (isset($_SERVER['REMOTE_ADDR']) || !defined('forum_debug')) {
	$GLOBALS['usr'] = init_user();
}


	fud_use('err.inc');

	if ($FUD_OPT_2 & 16384 && $t != 'getfile') {
		ob_start(array('ob_gzhandler', (int)$PHP_COMPRESSION_LEVEL));
	}

	if ($t == 'rview') {
		if (isset($_GET['th']) || isset($_GET['goto'])) {
			$t = $_GET['t'] = d_thread_view;
		} else if (isset($_GET['frm_id'])) {
			$t = $_GET['t'] = t_thread_view;
		} else {
			$t = $_GET['t'] = 'index';
		}
	} else if (preg_match('/[^a-z_]/', $t) || !@file_exists($WWW_ROOT_DISK . fud_theme . $t .'.php')) {
		$t = 'index';
	}

	if ($FUD_OPT_2 & 524288 && isset($_COOKIE[$COOKIE_NAME .'1']) && $t != 'error') {
		fud_use('errmsg.inc');
		exit_user_banned();
	}

	/* This is needed to determine what extension to use for alpha-transparency images. */
	if (!empty($_SERVER['HTTP_USER_AGENT']) && 
		strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false && 
		strpos($_SERVER['HTTP_USER_AGENT'], 'Opera') === false) {
		define('img_ext', '.gif');
	} else {
		define('img_ext', '.png');
	}
	
	require($WWW_ROOT_DISK . fud_theme .'language.inc');	// Initialize theme's language helper functions.
	require($WWW_ROOT_DISK . fud_theme . $t .'.php');
?>
