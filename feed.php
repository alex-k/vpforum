<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: feed.php.t 5057 2010-10-24 10:37:40Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	if (function_exists('mb_internal_encoding')) {
		mb_internal_encoding('utf-8');
	}
	require('./GLOBALS.php');
	fud_use('err.inc');

	/* Before we go on, we need to do some very basic activation checks. */
	if (!($FUD_OPT_1 & 1)) {	// FORUM_ENABLED
		fud_use('errmsg.inc');
		exit_forum_disabled('xml');
	}

	/* Control options. */
	$mode = (isset($_GET['mode']) && in_array($_GET['mode'], array('m', 't', 'u'))) ? $_GET['mode'] : 'm';
	$basic = isset($_GET['basic']);
	$format = 'rdf';	// Default syndication type.
	if (isset($_GET['format'])) {
		if (strtolower(substr($_GET['format'], 0, 4)) == 'atom') {
			$format = 'atom';
		} else if (strtolower(substr($_GET['format'], 0, 3)) == 'rss') {
			$format = 'rss';
		}
	}
	if (!isset($_GET['th'])) {
	   $_GET['l'] = 1;	// Unless thread is syndicated, we will always order entries from newest to oldest.
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
}

	if (!($FUD_OPT_2 & 16777216) || (!($FUD_OPT_2 & 67108864) && $mode == 'u')) {
		fud_use('cookies.inc');
		fud_use('users.inc');
		std_error('disabled');
	}

	if ($FUD_OPT_2 & 16384) {
		ob_start(array('ob_gzhandler', $PHP_COMPRESSION_LEVEL));
	}

function sp($data)
{
	return '<![CDATA['. str_replace(array('[', ']'), array('&#91;', '&#93;'), $data) .']]>';
}

function email_format($data)
{
	return str_replace(array('.', '@'), array(' dot ', ' at '), $data);
}

function multi_id($data)
{
	$out = array();
	foreach (explode(',', (string)$data) as $v) {
		$out[] = (int) $v;
	}
	return implode(',', $out);
}

$enc_src = array('<br>', '&', "\r", '&nbsp;', '<', '>', chr(0));
$enc_dst = array('<br />', '&amp;', '&#13;', ' ', '&lt;', '&gt;', '&#0;');

function fud_xml_encode($str)
{
	return str_replace($GLOBALS['enc_src'], $GLOBALS['enc_dst'], $str);
}

function feed_cache_cleanup()
{
	foreach (glob($GLOBALS['FORUM_SETTINGS_PATH'].'feed_cache_*') as $v) {
		if (filemtime($v) + $GLOBALS['FEED_CACHE_AGE'] < __request_timestamp__) {
			unlink($v);
		}
	}
}

// change relative smiley URLs to full ones
function smiley_full(&$data)
{
	if (strpos($data, '<img src="images/smiley_icons/') !== false) {
		$data = str_replace('<img src="images/smiley_icons/', '<img src="'. $GLOBALS['WWW_ROOT'] .'images/smiley_icons/', $data);
	}
}



	/* supported modes of output
	 * m 		- messages
	 * t 		- threads
	 * u		- users
	 */

	if (@count($_GET) < 2) {
		$_GET['ds'] = __request_timestamp__ - 86400;
		$_GET['l'] = 1;
		$_GET['n'] = 10;
	}

	define('__ROOT__', $WWW_ROOT .'index.php');

	$res = 0;
	$offset = isset($_GET['o']) ? (int)$_GET['o'] : 0;

	if ($FEED_CACHE_AGE) {
		register_shutdown_function('feed_cache_cleanup');

		$key = $_GET; 
		if ($FEED_AUTH_ID) {
			$key['auth_id'] = $FEED_AUTH_ID;
		}
		unset($key['S'], $key['rid'], $key['SQ']);	// Remove irrelavent components.
		$key = array_change_key_case($key, CASE_LOWER);	// Cleanup the key.
		$key = array_map('strtolower', $key);
		ksort($key);

		$file_name = $FORUM_SETTINGS_PATH .'feed_cache_'. md5(serialize($key));
		if (file_exists($file_name) && (($t = filemtime($file_name)) + $FEED_CACHE_AGE) > __request_timestamp__) {
			$mod = gmdate('D, d M Y H:i:s', $t) .' GMT';
			if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && !isset($_SERVER['HTTP_RANGE']) && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $mod) {
				header('HTTP/1.1 304 Not Modified');
				header('Status: 304 Not Modified');
				return;
			}
			header('Content-Type: application/'.$format.'+xml');
			header('Last-Modified: '. $mod);
			readfile($file_name);
			return;
		}
		ob_start();
	}

	if ($FEED_MAX_N_RESULTS < 1) {	// Handler for events when the value is not set.
		$FEED_MAX_N_RESULTS = 20;
	}
	$limit  = (isset($_GET['n']) && $_GET['n'] <= $FEED_MAX_N_RESULTS) ? (int)$_GET['n'] : $FEED_MAX_N_RESULTS;

	$feed_data = $feed_header = $join = '';
	switch ($mode) {
		case 'm':
			$lmt = ' t.moved_to=0 AND m.apr=1';
			/* check for various supported limits
			 * cat		- category
			 * frm		- forum
			 * th		- thread
			 * id		- message id
			 * ds		- start date
			 * de		- date end
			 * o		- offset
			 * n		- number of rows to get
			 * l		- latest
			 * sf		- subcribed forums based on user id
			 * st		- subcribed topics based on user id
			 * basic	- output basic info parsable by all rdf parsers
			 */
			if (isset($_GET['sf'])) {
				$_GET['frm'] = db_all('SELECT forum_id FROM fud26_forum_notify WHERE user_id='. (int)$_GET['sf']);
			} else if (isset($_GET['st'])) {
				$_GET['th'] = db_all('SELECT thread_id FROM fud26_thread_notify WHERE user_id='. (int)$_GET['sf']);
			}
			if (isset($_GET['cat'])) {
			 	$lmt .= ' AND f.cat_id IN('. multi_id($_GET['cat']) .')';
			}
			if (isset($_GET['frm'])) {
			 	$lmt .= ' AND t.forum_id IN('. multi_id($_GET['frm']) .')';
			}
			if (isset($_GET['th'])) {
				$lmt .= ' AND m.thread_id IN('. multi_id($_GET['th']) .')';
			}
			if (isset($_GET['id'])) {
			 	$lmt .= ' AND m.id IN('. multi_id($_GET['id']) .')';
			}
			if (isset($_GET['ds'])) {
				$lmt .= ' AND m.post_stamp >='. (int)$_GET['ds'];
			}
			if (isset($_GET['de'])) {
				$lmt .= ' AND m.post_stamp <='. (int)$_GET['de'];
			}

			/* This is an optimization so that the forum does not need to
			 * go through the entire message db to fetch latest messages.
			 * So, instead we set an arbitrary search limit of 5 days.
			 */
			if (isset($_GET['l']) && $lmt == ' t.moved_to=0 AND m.apr=1') {
				$lmt .= ' AND t.last_post_date >='. (__request_timestamp__ - 86400 * 5);
			}

			if ($FUD_OPT_2 & 33554432) {	// FEED_AUTH
				if ($FEED_AUTH_ID) {
					$join = '	INNER JOIN fud26_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=f.id
							LEFT JOIN fud26_group_cache g2 ON g2.user_id='. $FEED_AUTH_ID .' AND g2.resource_id=f.id
							LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. $FEED_AUTH_ID .' ';
					$lmt .= ' AND (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0)';
				} else {
					$join = ' INNER JOIN fud26_group_cache g1 ON g1.user_id=0 AND g1.resource_id=f.id ';
					$lmt .= ' AND '. q_bitand('g1.group_cache_opt', 2) .' > 0';
				}
			}

			$c = q(q_limit('SELECT
					m.*,
					u.alias,
					t.forum_id,
					p.name AS poll_name, p.total_votes,
					m2.subject AS th_subject,
					m3.subject AS reply_subject,
					f.name AS frm_name,
					c.name AS cat_name
				FROM
					fud26_msg m
					INNER JOIN fud26_thread t ON m.thread_id=t.id
					INNER JOIN fud26_forum f ON t.forum_id=f.id
					INNER JOIN fud26_cat c ON c.id=f.cat_id
					INNER JOIN fud26_msg m2 ON t.root_msg_id=m2.id
					LEFT JOIN fud26_msg m3 ON m3.id=m.reply_to
					LEFT JOIN fud26_users u ON m.poster_id=u.id
					LEFT JOIN fud26_poll p ON m.poll_id=p.id
					'. $join .'
				WHERE
					'. $lmt  .' ORDER BY m.post_stamp '. (isset($_GET['l']) ? 'DESC' : 'ASC'),
				$limit, $offset));
			while ($r = db_rowobj($c)) {
				if (!$res) {
					header('Content-Type: application/'.$format.'+xml');
					$res = 1;
				}

				$body = read_msg_body($r->foff, $r->length, $r->file_id);
				smiley_full($body);

				if ($format == 'rdf') {
					$feed_header .= '<rdf:li rdf:resource="'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;goto='.$r->id.'&amp;th='.$r->thread_id.'#msg_'.$r->id.'" />';
					$feed_data .= ($basic ? '
<item rdf:about="'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;goto='.$r->id.'&amp;th='.$r->thread_id.'#msg_'.$r->id.'">
	<title>'.htmlspecialchars($r->subject).'</title>
	<link>'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;goto='.$r->id.'&amp;th='.$r->thread_id.'#msg_'.$r->id.'</link>
	<description>'.sp($body).'</description>
	<dc:subject></dc:subject>
	<dc:creator>'.$r->alias.'</dc:creator>
	<dc:date>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</dc:date>
</item>
' : '
<item>
	<title>'.sp($r->subject).'</title>
	<topic_id>'.$r->thread_id.'</topic_id>
	<topic_title>'.sp($r->th_subject).'</topic_title>
	<message_id>'.$r->id.'</message_id>
	<reply_to_id>'.$r->reply_to.'</reply_to_id>
	<reply_to_title>'.$r->reply_subject.'</reply_to_title>
	<forum_id>'.$r->forum_id.'</forum_id>
	<forum_title>'.sp($r->frm_name).'</forum_title>
	<category_title>'.sp($r->cat_name).'</category_title>
	<author>'.sp($r->alias).'</author>
	<author_id>'.$r->poster_id.'</author_id>
	<date>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</date>
	<body>'.str_replace("\n", "", sp($body)).'</body>
	'.($rdf_message_attachments ? '
	<content:items><rdf:Bag>
	'.$rdf_message_attachments.'
	</rdf:Bag></content:items>
	' : '' ) .'
	'.($rdf_message_polls ? '
	<content:items><rdf:Bag><poll_name>'.sp($r->poll_name).'</poll_name><total_votes>'.$r->total_votes.'</total_votes>
	'.$rdf_message_polls.'
	</rdf:Bag></content:items>
	' : '' ) .'
</item>
' ) ;

					$rdf_message_attachments = '';
					if ($r->attach_cnt && $r->attach_cache) {
						if (($al = unserialize($r->attach_cache))) {
							foreach ($al as $a) {
								$rdf_message_attachments .= '<rdf:li>
	<content:item rdf:about="attachments">
		<a_title>'.sp($a[1]).'</a_title>
		<a_id>'.$a[0].'</a_id>
		<a_size>'.$a[2].'</a_size>
		<a_nd>'.$a[3].'</a_nd>
	</content:item>
</rdf:li>';
							}
						}
					}

					$rdf_message_polls = '';	
					if ($r->poll_name) {
						if ($r->poll_cache) {
							if (($pc = unserialize($r->poll_cache))) {
								foreach ($pc as $o) {
									$rdf_message_polls .= '<rdf:li>
	<content:item rdf:about="poll_opt">
		<opt_title>'.sp($o[0]).'</opt_title>
		<opt_votes>'.$o[1].'</opt_votes>
	</content:item>
</rdf:li>';
								}
							}
						}
					}
				}
				if ($format == 'rss' ) $feed_data .= '<item>
	<title>'.htmlspecialchars($r->subject).'</title>
	<link>'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;goto='.$r->id.'&amp;th='.$r->thread_id.'#msg_'.$r->id.'</link>
	<author>'.$r->alias.'</author>
	<pubDate>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</pubDate>
	<description>'.sp($body).'</description>
</item>';
				if ($format == 'atom') $feed_data .= '<entry>
	<title>'.htmlspecialchars($r->subject).'</title>
	<link href="'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;goto='.$r->id.'&amp;th='.$r->thread_id.'#msg_'.$r->id.'" />
	<id>'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;goto='.$r->id.'&amp;th='.$r->thread_id.'#msg_'.$r->id.'</id>
	<author><name>'.$r->alias.'</name></author>
	<published>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</published>
	'.($r->update_stamp ? '<updated>'.gmdate('Y-m-d\TH:i:s', $r->update_stamp).'-00:00</updated>' : '' ) .'
	<content type="html">'.sp($body).'</content>
</entry>';
			}
			if ($res) {
				if ($format == 'rdf')  echo '<?xml version="1.0" encoding="utf-8"?>
'.($basic ? '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns="http://purl.org/rss/1.0/">
' : '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns="http://purl.org/rss/1.0/">
' ) .'
<channel rdf:about="'.$GLOBALS['WWW_ROOT'].'">
	<title>'.$FORUM_TITLE.' - RDF-канал</title>
	<link>'.$GLOBALS['WWW_ROOT'].'</link>
	<description>'.$FORUM_DESCR.'</description>
'.($basic && $feed_header ? '
	<items>
		<rdf:Seq>
		'.$feed_header.'
		</rdf:Seq>
	</items>
' : '' ) .'
</channel>
'.$feed_data.'
</rdf:RDF>';
				if ($format == 'rss')  echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
	<title>'.$FORUM_TITLE.' - RSS2-канал</title>
	<link>'.$GLOBALS['WWW_ROOT'].'</link>
	<description>'.$GLOBALS['FORUM_DESCR'].'</description>
	<language>ru</language>
	<pubDate>'.gmdate('Y-m-d\TH:i:s', __request_timestamp__).'-00:00</pubDate>
	<generator>FUDforum '.$FORUM_VERSION.'</generator>
	'.$feed_data.'
</channel>
</rss>';
				if ($format == 'atom') echo '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>'.$FORUM_TITLE.' - ATOM-канал</title>
	<subtitle>'.$GLOBALS['FORUM_DESCR'].'</subtitle>
	<link href="'.$GLOBALS['WWW_ROOT'].'" />
	<updated>'.gmdate('Y-m-d\TH:i:s', __request_timestamp__).'-00:00</updated>
	<id>'.$GLOBALS['WWW_ROOT'].'</id>
	<generator uri="http://fudforum.org/" version="'.$FORUM_VERSION.'">FUDforum</generator>
	'.$feed_data.'
</feed>';
			}
			unset($c);
			break;

		case 't':
			/* check for various supported limits
			 * cat		- category
			 * frm		- forum
			 * id		- topic id
			 * ds		- start date
			 * de		- date end
			 * o		- offset
			 * n		- number of rows to get
			 * l		- latest
			 */
			$lmt = ' t.moved_to=0 AND m.apr=1';
			if (isset($_GET['cat'])) {
				$lmt .= ' AND f.cat_id IN('. multi_id($_GET['cat']) .')';
			}
			if (isset($_GET['frm'])) {
				$lmt .= ' AND t.forum_id IN('. multi_id($_GET['frm']) .')';
			}
			if (isset($_GET['id'])) {
			 	$lmt .= ' AND t.id IN ('. multi_id($_GET['id']) .')';
			}
			if (isset($_GET['ds'])) {
				$lmt .= ' AND t.last_post_date >='. (int)$_GET['ds'];
			}
			if (isset($_GET['de'])) {
				$lmt .= ' AND t.last_post_date <='. (int)$_GET['de'];
			}

			/* This is an optimization so that the forum does not need to
			 * go through the entire message db to fetch latest messages.
			 * So, instead we set an arbitrary search limit if 5 days.
			 */
			if (isset($_GET['l']) && $lmt == ' t.moved_to=0 AND m.apr=1') {
				$lmt .= ' AND t.last_post_date >='. (__request_timestamp__ - 86400 * 5);
			}

			if ($FUD_OPT_2 & 33554432) {	// FEED_AUTH
				if ($FEED_AUTH_ID) {
					$join = '	INNER JOIN fud26_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=f.id
							LEFT JOIN fud26_group_cache g2 ON g2.user_id='. $FEED_AUTH_ID .' AND g2.resource_id=f.id
							LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. $FEED_AUTH_ID .' ';
					$lmt .= ' AND (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0)';
				} else {
					$join = ' INNER JOIN fud26_group_cache g1 ON g1.user_id=0 AND g1.resource_id=f.id ';
					$lmt .= ' AND '. q_bitand('g1.group_cache_opt', 2) .' > 0';
				}
			}
			$c = q(q_limit('SELECT
					t.*,
					f.name AS frm_name,
					c.name AS cat_name,
					m.subject, m.post_stamp, m.poster_id, m.foff, m.length, m.file_id,
					m2.subject AS lp_subject,
					u.alias
				FROM
					fud26_thread t
					INNER JOIN fud26_forum f ON t.forum_id=f.id
					INNER JOIN fud26_cat c ON c.id=f.cat_id
					INNER JOIN fud26_msg m ON t.root_msg_id=m.id
					INNER JOIN fud26_msg m2 ON t.last_post_id=m2.id
					LEFT JOIN fud26_users u ON m.poster_id=u.id
					'. $join .'
				WHERE
					'. $lmt . (isset($_GET['l']) ? ' ORDER BY m.post_stamp DESC' : ''),
				$limit, $offset));

			$data = '';
			while ($r = db_rowobj($c)) {
				if (!$res) {
					header('Content-Type: application/'.$format.'+xml');
					$res = 1;
				}
				if ($r->root_msg_id == $r->last_post_id) {
					$r->last_post_id = $r->lp_subject = $r->last_post_date = '';
				}

				$body = read_msg_body($r->foff, $r->length, $r->file_id);
				smiley_full($body);

				if ($format == 'rdf') {
					$feed_header .= '<rdf:li rdf:resource="'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;th='.$r->id.'" />';
					$feed_data .= ($basic ? '
<item rdf:about="'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;th='.$r->id.'">
	<title>'.htmlspecialchars($r->subject).'</title>
	<link>'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;th='.$r->id.'</link>
	<description>'.sp($body).'</description>
	<dc:subject>'.sp($r->frm_name).'</dc:subject>
	<dc:creator>'.sp($r->alias).'</dc:creator>
	<dc:date>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</dc:date>
</item>
' : '
<item>
	<topic_id>'.$r->id.'</topic_id>
	<topic_title>'.sp($r->subject).'</topic_title>
	<topic_creation_date>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</topic_creation_date>
	<forum_id>'.$r->forum_id.'</forum_id>
	<forum_title>'.sp($r->frm_name).'</forum_title>
	<category_title>'.sp($r->cat_name).'</category_title>
	<author>'.sp($r->alias).'</author>
	<author_id>'.$r->poster_id.'</author_id>
	<replies>'.$r->replies.'</replies>
	<views>'.$r->views.'</views>
	'.($r->last_post_id ? '<last_post_id>'.$r->last_post_id.'</last_post_id>' : '' ) .'
	'.($r->lp_subject ? '<last_post_subj>'.sp($r->lp_subject).'</last_post_subj>' : '' ) .'
	'.($r->last_post_date ? '<last_post_date>'.gmdate('Y-m-d\TH:i:s', $r->last_post_date).'-00:00</last_post_date>' : '' ) .'
	<body>'.str_replace("\n", "", sp($body)).'</body>
</item>
' ) ;
				}
				if ($format == 'rss' ) $feed_data .= '<item>
	<title>'.htmlspecialchars($r->subject).'</title>
	<link>'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;th='.$r->id.'</link>
	<author>'.sp($r->alias).'</author>
	<pubDate>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</pubDate>
	<description>'.sp($body).'</description>
</item>';
				if ($format == 'atom') $feed_data .= '<entry>
	<title>'.htmlspecialchars($r->subject).'</title>
	'.($r->tdescr ? '<subtitle>'.sp($r->tdescr).'</subtitle>' : '' ) .'
	<link href="'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;th='.$r->id.'" />
	<id>'.$GLOBALS['WWW_ROOT'].'index.php?t=rview&amp;th='.$r->id.'</id>
	<author><name>'.sp($r->alias).'</name></author>
	<published>'.gmdate('Y-m-d\TH:i:s', $r->post_stamp).'-00:00</published>
	'.($r->last_post_date ? '<updated>'.gmdate('Y-m-d\TH:i:s', $r->last_post_date).'-00:00</updated>' : '' ) .'
	<content type="html">'.sp($body).'</content>
</entry>';
			}
			if ($res) {
				if ($format == 'rdf')  echo '<?xml version="1.0" encoding="utf-8"?>
'.($basic ? '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns="http://purl.org/rss/1.0/">
' : '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns="http://purl.org/rss/1.0/">
' ) .'
<channel rdf:about="'.$GLOBALS['WWW_ROOT'].'">
	<title>'.$FORUM_TITLE.' - RDF-канал</title>
	<link>'.$GLOBALS['WWW_ROOT'].'</link>
	<description>'.$FORUM_DESCR.'</description>
'.($basic && $feed_header ? '
	<items>
		<rdf:Seq>
		'.$feed_header.'
		</rdf:Seq>
	</items>
' : '' ) .'
</channel>
'.$feed_data.'
</rdf:RDF>';
				if ($format == 'rss')  echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
	<title>'.$FORUM_TITLE.' - RSS2-канал</title>
	<link>'.$GLOBALS['WWW_ROOT'].'</link>
	<description>'.$GLOBALS['FORUM_DESCR'].'</description>
	<language>ru</language>
	<pubDate>'.gmdate('Y-m-d\TH:i:s', __request_timestamp__).'-00:00</pubDate>
	<generator>FUDforum '.$FORUM_VERSION.'</generator>
	'.$feed_data.'
</channel>
</rss>';
				if ($format == 'atom') echo '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>'.$FORUM_TITLE.' - ATOM-канал</title>
	<subtitle>'.$GLOBALS['FORUM_DESCR'].'</subtitle>
	<link href="'.$GLOBALS['WWW_ROOT'].'" />
	<updated>'.gmdate('Y-m-d\TH:i:s', __request_timestamp__).'-00:00</updated>
	<id>'.$GLOBALS['WWW_ROOT'].'</id>
	<generator uri="http://fudforum.org/" version="'.$FORUM_VERSION.'">FUDforum</generator>
	'.$feed_data.'
</feed>';
			}
			unset($c);
			break;

		case 'u':
			/* check for various supported limits
			 * pc	-	order by post count
			 * rd	-	order by registration date
			 * cl	-	show only currently online users
			 * l	-	limit to 'l' rows
			 * o	- 	offset
			 * n	-	max rows to fetch
			 */
			$lmt .= ' u.id>1 ';
			if (isset($_GET['pc'])) {
				$order_by = 'u.posted_msg_count';
			} else if (isset($_GET['rd'])) {
				$order_by = 'u.join_date';
			} else {
				$order_by = 'u.alias';
			}
			if (isset($_GET['cl'])) {
				$lmt .= ' AND u.last_visit>='. (__request_timestamp__ - $LOGEDIN_TIMEOUT * 60);
			}
			if ($FUD_OPT_2 & 33554432) {	// FEED_AUTH
				if ($FEED_AUTH_ID) {
					$join = '	INNER JOIN fud26_group_cache g1 ON g1.user_id=2147483647 AND g1.resource_id=f.id
							LEFT JOIN fud26_group_cache g2 ON g2.user_id='. $FEED_AUTH_ID .' AND g2.resource_id=f.id
							LEFT JOIN fud26_mod mm ON mm.forum_id=f.id AND mm.user_id='. $FEED_AUTH_ID .' ';
					$perms = ', (CASE WHEN (mm.id IS NOT NULL OR '. q_bitand('COALESCE(g2.group_cache_opt, g1.group_cache_opt)', 2) .' > 0) THEN 1 ELSE 0 END) AS can_show_msg';
				} else {
					$join = ' INNER JOIN fud26_group_cache g1 ON g1.user_id=0 AND g1.resource_id=f.id ';
					$perms = ', '. q_bitand('g1.group_cache_opt', 2) .' > 0 AS can_show_msg';
				}
			} else {
				$perms = ', 1 AS can_show_msg';
			}
			$c = q(q_limit('SELECT
						u.id, u.alias, u.join_date, u.posted_msg_count, u.avatar_loc, u.users_opt,
						u.home_page, u.birthday, u.last_visit, u.icq, u.aim, u.yahoo, u.msnm, u.jabber, u.google, u.skype, u.twitter, u.affero,
						u.name, u.email,
						m.id AS msg_id, m.subject, m.thread_id,
						t.forum_id,
						f.name AS frm_name,
						c.name AS cat_name
						'. $perms .'

					FROM fud26_users u
					LEFT JOIN fud26_msg m ON m.id=u.u_last_post_id
					LEFT JOIN fud26_thread t ON m.thread_id=t.id
					LEFT JOIN fud26_forum f ON f.id=t.forum_id
					LEFT JOIN fud26_cat c ON c.id=f.cat_id
					'. $join .'
					WHERE
						'. $lmt .' ORDER BY '. $order_by .' DESC',
					$limit, $offset));
			while ($r = db_rowobj($c)) {
				if (!$res) {
					header('Content-Type: application/'.$format.'+xml');
					$res = 1;
				}

				if ($r->birthday) {
					$y = substr($r->birthday, 4);
					$m = substr($r->birthday, 0, 2);
					$d = substr($r->birthday, 2, 2);
					$r->birthday = gmdate('r', gmmktime(1, 1, 1, $m, $d, $y));
				} else {
					$r->birthday = '';
				}
				$r->last_visit = ($r->last_visit && $r->last_visit > 631155661) ? $r->last_visit : '';
				$r->join_date = ($r->join_date && $r->join_date > 631155661) ? $r->join_date : '';

				if ($r->users_opt >= 16777216) {
					$r->avatar_loc = '';
				}

				if ($format == 'rdf' ) $feed_data .= '<item>
	<user_id>'.$r->id.'</user_id>
	<user_login>'.sp($r->alias).'</user_login>
	<user_name>'.sp($r->name).'</user_name>
	<user_email>'.sp(email_format($r->email)).'</user_email>
	<post_count>'.$r->posted_msg_count.'</post_count>
	<avatar_img>'.sp($r->avatar_loc).'</avatar_img>
	<homepage>'.sp(htmlspecialchars($r->homepage)).'</homepage>
	<birthday>'.$r->birthday.'</birthday>
	'.($r->last_visit ? '<last_visit>'.gmdate('Y-m-d\TH:i:s', $r->last_visit).'</last_visit>' : '' ) .'
	'.($r->join_date ? '<reg_date>'.gmdate('Y-m-d\TH:i:s', $r->join_date).'</reg_date>' : '' ) .'
	<im_icq>'.$r->icq.'</im_icq>
	<im_aim>'.sp($r->aim).'</im_aim>
	<im_yahoo>'.sp($r->yahoo).'</im_yahoo>
	<im_msnm>'.sp($r->msnm).'</im_msnm>
	<im_jabber>'.sp($r->msnm).'</im_jabber>
	<im_google>'.sp($r->google).'</im_google>
	<im_skype>'.sp($r->skype).'</im_skype>
	<im_twitter>'.sp($r->twitter).'</im_twitter>
	<im_affero>'.sp($r->affero).'</im_affero>
'.($r->subject && $r->can_show_msg ? '
	<m_subject>'.sp($r->subject).'</m_subject>
	<m_id>'.$r->msg_id.'</m_id>
	<m_thread_id>'.$r->thread_id.'</m_thread_id>
	<m_forum_id>'.$r->forum_id.'</m_forum_id>
	<m_forum_title>'.sp($r->frm_name).'</m_forum_title>
	<m_cat_title>'.sp($r->cat_name).'</m_cat_title>
' : '' ) .'
</item>';
				if ($format == 'rss' ) $feed_data .= '<item>
	<title>'.sp($r->alias).'</title>
	<link>'.$GLOBALS['WWW_ROOT'].'index.php?t=usrinfo&amp;id='.$r->id.'</link>
	<author>'.sp($r->name).'</author>
	'.($r->last_visit ? '<pubDate>'.gmdate('Y-m-d\TH:i:s', $r->last_visit).'</pubDate>' : '' ) .'
</item>';
				if ($format == 'atom') $feed_data .= '<entry>
	<title>'.sp($r->alias).'</title>
	<link href="'.$GLOBALS['WWW_ROOT'].'index.php?t=usrinfo&amp;id='.$r->id.'" />
	<id>'.$GLOBALS['WWW_ROOT'].'index.php?t=usrinfo&amp;id='.$r->id.'</id>
	<author>
		<name>'.sp($r->name).'</name>
		<email>'.sp(email_format($r->email)).'</email>
		'.($r->homepage ? '<uri>'.sp(htmlspecialchars($r->homepage)).'</uri>' : '' ) .'
	</author>
	'.($r->last_visit ? '<published>'.gmdate('Y-m-d\TH:i:s', $r->last_visit).'</published>' : '' ) .'
	'.($r->join_date ? '<updated>'.gmdate('Y-m-d\TH:i:s', $r->join_date).'</updated>' : '' ) .'
</entry>';
			}
			if ($res) {
				if ($format == 'rdf')  echo '<?xml version="1.0" encoding="utf-8"?>
'.($basic ? '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:sy="http://purl.org/rss/1.0/modules/syndication/" xmlns:admin="http://webns.net/mvcb/" xmlns="http://purl.org/rss/1.0/">
' : '
<rdf:RDF xmlns:rdf="http://www.w3.org/1999/02/22-rdf-syntax-ns#" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns="http://purl.org/rss/1.0/">
' ) .'
<channel rdf:about="'.$GLOBALS['WWW_ROOT'].'">
	<title>'.$FORUM_TITLE.' - RDF-канал</title>
	<link>'.$GLOBALS['WWW_ROOT'].'</link>
	<description>'.$FORUM_DESCR.'</description>
'.($basic && $feed_header ? '
	<items>
		<rdf:Seq>
		'.$feed_header.'
		</rdf:Seq>
	</items>
' : '' ) .'
</channel>
'.$feed_data.'
</rdf:RDF>';
				if ($format == 'rss')  echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
<channel>
	<title>'.$FORUM_TITLE.' - RSS2-канал</title>
	<link>'.$GLOBALS['WWW_ROOT'].'</link>
	<description>'.$GLOBALS['FORUM_DESCR'].'</description>
	<language>ru</language>
	<pubDate>'.gmdate('Y-m-d\TH:i:s', __request_timestamp__).'-00:00</pubDate>
	<generator>FUDforum '.$FORUM_VERSION.'</generator>
	'.$feed_data.'
</channel>
</rss>';				
				if ($format == 'atom') echo '<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>'.$FORUM_TITLE.' - ATOM-канал</title>
	<subtitle>'.$GLOBALS['FORUM_DESCR'].'</subtitle>
	<link href="'.$GLOBALS['WWW_ROOT'].'" />
	<updated>'.gmdate('Y-m-d\TH:i:s', __request_timestamp__).'-00:00</updated>
	<id>'.$GLOBALS['WWW_ROOT'].'</id>
	<generator uri="http://fudforum.org/" version="'.$FORUM_VERSION.'">FUDforum</generator>
	'.$feed_data.'
</feed>';
			}
			unset($c);
			break;
	} // switch ($mode)

	if ($res) {
		if ($FEED_CACHE_AGE) {
			echo ($out = ob_get_clean());
			$fp = fopen($file_name, 'w');
			fwrite($fp, $out);
			fclose($fp);
		}
	} else {
		exit('<?xml version="1.0" encoding="utf-8"?>
<errors>
	<error>
		<message>Не найдено соответствующих данных.</message>
	</error>
</errors>');
	}
?>
