<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: errmsg.inc.t 5057 2010-10-24 10:37:40Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (!defined('_hs'))   define('_hs', '');
if (!defined('_rsid')) define('_rsid', '');

function exit_forum_disabled($format='html')
{
	$TITLE_EXTRA = $RSS = null;

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');
	header('Retry-After: 1800');	// 30 minutes.
	header('Connection: Close');
	header('Content-type: text/'. $format .'; charset=utf-8');

	if ($format == 'xml') {
		exit('<?xml version="1.0" encoding="utf-8"?>
<error>
  <message>'.$GLOBALS['DISABLED_REASON'].'</message>
</error>');
	} else {
		exit('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'.$GLOBALS['FORUM_TITLE'].$TITLE_EXTRA.'</title>
<meta name="description" content="'.(!empty($META_DESCR) ? ''.$META_DESCR.'' : ''.$GLOBALS['FORUM_DESCR'].'' ) .'" />
<base href="'.$GLOBALS['WWW_ROOT'].'" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="theme/default/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
<link rel="search" type="application/opensearchdescription+xml" title="'.$GLOBALS['FORUM_TITLE'].' Search" href="'.$GLOBALS['WWW_ROOT'].'open_search.php" />
'.$RSS.'
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground" valign="top">
<!-- <td class="ForumBackground" valign="top"> -->
<div class="ForumBackground header">
'.($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="index.php">'._hs.'
      <br /><label accesskey="f" title="Поиск в форумах">Поиск в форумах:<br />
      <input type="text" name="srch" value="" size="15" placeholder="Поиск в форумах" /></label>
      <input type="hidden" name="t" value="search" />
      <input type="submit" name="btn_submit" value="Поиск" class="headbutton" />&nbsp;
    </form>
  </div>
' : '' ) .'
<a href="index.php/.." title="Начало"><img src="theme/default/images/header.gif" alt="" align="left" height="80" />
  <span class="headtitle">'.$GLOBALS['FORUM_TITLE'].'</span>
</a><br />
<span class="headdescr">'.$GLOBALS['FORUM_DESCR'].'<br /><br /></span>
</div>
<br />
<h1>'.$GLOBALS['DISABLED_REASON'].'</h2>
<br />



<small>Администраторы могут войти <a href="'.$GLOBALS['WWW_ROOT'].'adm/admloginuser.php">здесь</a></small>
<br /><br />
</td>
<!-- <td class="ForumBackground" valign="top"></td> -->
</tr></table>

<div class="ForumBackground ac foot">
<b>.::</b> <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">Обратная связь</a> 
<b>::</b> <a href="index.php?t=index&amp;'._rsid.'">Начало</a> 
<b>::</b> <a href="http://www.phpbee.org/">Создание и поддержка сайта www.phpbee.org</a> 

<b>::.</b>
<p>
<span class="SmallText">При поддержке: FUDforum '.$GLOBALS['FORUM_VERSION'].'.<br /> Copyright © 2001-2010 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></span>
</p>
</div>
</body></html>');
	}
}

function exit_user_banned()
{
	$TITLE_EXTRA = $RSS = null;

	header('Content-type: text/html; charset=utf-8');
	exit('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>'.$GLOBALS['FORUM_TITLE'].$TITLE_EXTRA.'</title>
<meta name="description" content="'.(!empty($META_DESCR) ? ''.$META_DESCR.'' : ''.$GLOBALS['FORUM_DESCR'].'' ) .'" />
<base href="'.$GLOBALS['WWW_ROOT'].'" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="theme/default/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
<link rel="search" type="application/opensearchdescription+xml" title="'.$GLOBALS['FORUM_TITLE'].' Search" href="'.$GLOBALS['WWW_ROOT'].'open_search.php" />
'.$RSS.'
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground" valign="top">
<!-- <td class="ForumBackground" valign="top"> -->
<div class="ForumBackground header">
'.($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="index.php">'._hs.'
      <br /><label accesskey="f" title="Поиск в форумах">Поиск в форумах:<br />
      <input type="text" name="srch" value="" size="15" placeholder="Поиск в форумах" /></label>
      <input type="hidden" name="t" value="search" />
      <input type="submit" name="btn_submit" value="Поиск" class="headbutton" />&nbsp;
    </form>
  </div>
' : '' ) .'
<a href="index.php/.." title="Начало"><img src="theme/default/images/header.gif" alt="" align="left" height="80" />
  <span class="headtitle">'.$GLOBALS['FORUM_TITLE'].'</span>
</a><br />
<span class="headdescr">'.$GLOBALS['FORUM_DESCR'].'<br /><br /></span>
</div>
<br />
Ваши действия привели к тому, что вы исключены из этого форума. До свидания.
<br /><br />
<a href="/forum/adm/ban/rules">Правила форума</a>

<br/>

<a href="/forum/adm/ban">Список забаненых пользователей</a>

<br/>

</td>
<!-- <td class="ForumBackground" valign="top"></td> -->
</tr></table>

<div class="ForumBackground ac foot">
<b>.::</b> <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">Обратная связь</a> 
<b>::</b> <a href="index.php?t=index&amp;'._rsid.'">Начало</a> 
<b>::</b> <a href="http://www.phpbee.org/">Создание и поддержка сайта www.phpbee.org</a> 

<b>::.</b>
<p>
<span class="SmallText">При поддержке: FUDforum '.$GLOBALS['FORUM_VERSION'].'.<br /> Copyright © 2001-2010 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></span>
</p>
</div>
</body></html>');
}

?>
