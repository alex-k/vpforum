<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: help_index.php.t 5030 2010-10-08 18:27:42Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}

	$section = isset($_GET['section']) ? $_GET['section'] : '';
	switch ($section) {
		case 'usermaintance':
		case 'boardusage':
		case 'readingposting':
			$file = '/home/activeinfo/velopiter.spb.ru/public_html/forum/theme/default/help/'. $section .'.hlp';
			$return_top = '<div class="GenText ac">[ <a href="index.php?t=help_index&amp;'._rsid.'">Возврат на главную страницу помощи</a> ]</div>';
			break;
		default:
			$file = '/home/activeinfo/velopiter.spb.ru/public_html/forum/theme/default/help/faq_index.hlp';
			$return_top = '';
	}

	ses_update_status($usr->sid, 'Чтение <a href="index.php?t=help_index">F.A.Q.</a>');
	$TITLE_EXTRA = ': F.A.Q.';

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}

	$str = file_get_contents($file);

	$tt_len = strlen('TOPIC_TITLE:');
	$th_len = strlen('TOPIC_HELP:');
	$help_section_data = '';
	while (($str = strstr($str, 'TOPIC_TITLE:')) !== false) {
		$end_of = strpos($str, "\n");
		$topic_title = substr($str, $tt_len, $end_of-$tt_len);
		$str = strstr($str, 'TOPIC_HELP:');
		$str = substr($str, $th_len);
		$end_of_str = strstr($str, 'TOPIC_TITLE:');
		$topic_help = substr($str, 0, strlen($str)-strlen($end_of_str));
		$str = $end_of_str;
		if ($FUD_OPT_2 & 32768 && !empty($_SERVER['PATH_INFO'])) {
			$rs = 'S='. str_replace(array('/', '?'), array('&amp;', ''), _rsid);
		} else {
			$rs = _rsid;
		}
		$topic_help = str_replace(array('%_rsid%', '&amp;#', '&#'), array($rs, '#', '#'), $topic_help);

		$help_section_data .= '<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr><th>'.$topic_title.'</th></tr>
<tr><td class="ForumBackground">
	<table cellspacing="2" cellpadding="2" width="100%" class="dashed"><tr><td class="GenText">
	'.$topic_help.'
	</td></tr><tr><td class="GenText ar">
		<a href="javascript://" onclick="chng_focus(\'top\');">возврат наверх</a>
	</td></tr></table>
</td></tr>
</table>
<br />';
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
<link rel="stylesheet" href="theme/default/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
<link rel="search" type="application/opensearchdescription+xml" title="<?php echo $GLOBALS['FORUM_TITLE']; ?> Search" href="<?php echo $GLOBALS['WWW_ROOT']; ?>open_search.php" />
<?php echo $RSS; ?>
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground" valign="top">
<!-- <td class="ForumBackground" valign="top"> -->
<div class="ForumBackground header">
<?php echo ($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="index.php">'._hs.'
      <br /><label accesskey="f" title="Поиск в форумах">Поиск в форумах:<br />
      <input type="text" name="srch" value="" size="15" placeholder="Поиск в форумах" /></label>
      <input type="hidden" name="t" value="search" />
      <input type="submit" name="btn_submit" value="Поиск" class="headbutton" />&nbsp;
    </form>
  </div>
' : ''); ?>
<a href="index.php/.." title="Начало"><img src="theme/default/images/header.gif" alt="" align="left" height="80" />
  <span class="headtitle"><?php echo $GLOBALS['FORUM_TITLE']; ?></span>
</a><br />
<span class="headdescr"><?php echo $GLOBALS['FORUM_DESCR']; ?><br /><br /></span>
</div>
<div class="UserControlPanel">
<a href="/forum/index.php?t=msg&th=102972" class="UserControlPanel nw" title="Правила"><img src="/forum/images/message_icons/icon4.gif" alt=""> Правила форума </a>&nbsp;&nbsp;
  <?php echo $private_msg; ?> 
  <?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<a class="UserControlPanel nw" href="index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Участники"><img src="theme/default/images/top_members'.img_ext.'" alt="" /> Участники</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_3 & 134217728 ? '<a class="UserControlPanel nw" href="index.php?t=cal&amp;'._rsid.'" title="Календарь"><img src="theme/default/images/calendar'.img_ext.'" alt="" /> Календарь</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_1 & 16777216 ? '<a class="UserControlPanel nw" href="index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Поиск"><img src="theme/default/images/top_search'.img_ext.'" alt="" /> Поиск</a>
&nbsp;&nbsp;
<a class="UserControlPanel nw" href="/search.html" title="Yandex поиск"><img src="theme/default/images/top_search'.img_ext.'" alt="" /> Поиск через Yandex</a>
&nbsp;&nbsp;' : ''); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" accesskey="h" href="index.php?t=help_index&amp;<?php echo _rsid; ?>" title="F.A.Q."><img src="theme/default/images/top_help<?php echo img_ext; ?>" alt="" /> F.A.Q.</a>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=uc&amp;'._rsid.'" title="Доступ к панели управления пользователя"><img src="theme/default/images/top_profile'.img_ext.'" alt="" /> Настройки</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=register&amp;'._rsid.'" title="Регистрация"><img src="theme/default/images/top_register'.img_ext.'" alt="" /> Регистрация</a>'); ?>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Выход"><img src="theme/default/images/top_logout'.img_ext.'" alt="" /> Выход [ '.$usr->alias.' ]</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'" title="Вход"><img src="theme/default/images/top_login'.img_ext.'" alt="" /> Вход</a>'); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=index&amp;<?php echo _rsid; ?>" title="Начало"><img src="theme/default/images/top_home<?php echo img_ext; ?>" alt="" /> Начало</a>
  <?php echo ($is_a || ($usr->users_opt & 268435456) ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Административный центр"><img src="theme/default/images/top_admin'.img_ext.'" alt="" /> Административный центр</a>' : ''); ?>
</div>
<a name="top"></a>
<?php echo $return_top; ?>
<?php echo $help_section_data; ?>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
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
</body></html>
