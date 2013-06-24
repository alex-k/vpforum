<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: pre_reg.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}

	if (isset($_POST['disagree'])) {
		if ($FUD_OPT_2 & 32768) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/i/'. _rsidl);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?'. _rsidl);
		}
		exit;
	} else if (isset($_POST['agree'])) {
		if ($FUD_OPT_2 & 32768) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/re/'. ($FUD_OPT_1 & 1048576 ?(int)$_POST['coppa'] : 0) .'/'. _rsidl);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=register&'. _rsidl .'&reg_coppa='. ($FUD_OPT_1 & 1048576 ?(int)$_POST['coppa'] : 0));
		}
		exit;
	}

	ses_update_status($usr->sid, 'Прочтите правила форума', 0, 0);

	$TITLE_EXTRA = ': Правила форума';

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}

	$_GET['coppa'] = isset($_GET['coppa']) ? (int) $_GET['coppa'] : 0;


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
<form method="post" action="index.php?t=pre_reg" id="sub"><?php echo _hs; ?>
<div class="ctb">
<table cellspacing="1" cellpadding="2" class="DialogTable">
<tr><th>Информация</th></tr>
<tr class="RowStyleA ac GenText"><td>
<?php echo ($_GET['coppa'] ? '<p>Все участники в возрасте до 13 лет должны получить разрешение от своих родителей или опекунов на участие в форуме. Мы приветствуем участие в форуме детей до 13, но, тем не менее, прежде чем предоставим вам право участия, мы ожидаем получения подписанного разрешения факсом или по почте.</p><p>Если вы желаете продолжить регистрацию до момента получения нами разрешения, нажмите кнопку <b>Согласен</b>. Если вы желаете прервать процесс регистрации — нажмите <b>Не согласен</b> и вы вернётесь к списку форумов.</p><p>Копия разрешения может быть загружена здесь. Для получения дополнительной информации о процессе регистрации, отправьте, пожалуйста, письмо на адрес <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">'.$GLOBALS['ADMIN_EMAIL'].'</a>.</p>' : '<p>Регистрация на этом форуме является свободной. Мы надеемся, что вы ознакомитесь с приведенными ниже правилами и положениями. Если вы согласны с ними — нажмите кнопку в конце этой страницы. Замечание. Нажимая на кнопку, вы подтверждаете, что ваш возраст превышает 13 лет. Если вам 13 лет или и менее, пожалуйста, используйте эту регистрационную форму.</p><p>Несмотря на то, что администраторы и модераторы этого форума будут стараться не допускать на форуме вызывающих возражения сообщений, нам невозможно проверить каждое сообщение. Все сообщения выражают точку зрения их авторов и ни владелец ни разработчики форума не несут никакой ответственности за опубликованную информацию.</p><p>Нажимая кнопку Согласен, вы гарантируете, что не будете публиковать сообщений непристойного, грубого, сексуально-ориентированного, вызывающего ненависть, угрожающего характера или каким-либо образом нарушающих законодательство.</p><p>Владельцы форума имеют право удалить, изменить, перенести или закрыть любую тему по любой причине.</p>'); ?>
<input type="hidden" name="coppa" value="<?php echo $_GET['coppa']; ?>" />
<input type="submit" class="button" name="agree" value="Согласен" />
<input type="submit" class="button" name="disagree" value="Не согласен" />
<br /><br />
</td></tr>
</table></div></form>
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
