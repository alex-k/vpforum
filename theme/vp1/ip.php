<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: ip.php.t 5030 2010-10-08 18:27:42Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
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
}function alt_var($key)
{
	if (!isset($GLOBALS['_ALTERNATOR_'][$key])) {
		$args = func_get_args(); unset($args[0]);
		$GLOBALS['_ALTERNATOR_'][$key] = array('p' => 2, 't' => func_num_args(), 'v' => $args);
		return $args[1];
	}
	$k =& $GLOBALS['_ALTERNATOR_'][$key];
	if ($k['p'] == $k['t']) {
		$k['p'] = 1;
	}
	return $k['v'][$k['p']++];
}

	/* Permissions check, this form is only allowed for moderators & admins unless public.
	 * Check if IP display is allowed.
	 */
	if (!($usr->users_opt & (524288|1048576)) && !($FUD_OPT_1 & 134217728)) {
		invl_inp_err();
	}

function __fud_whois($ip, $whois_server='')
{
	if (!$whois_server) {
		$whois_server = $GLOBALS['FUD_WHOIS_SERVER'];
	}

	if (!$sock = @fsockopen($whois_server, 43, $errno, $errstr, 20)) {
		$errstr = preg_match('/WIN/', PHP_OS) ? utf8_encode($errstr) : $errstr;	// Windows silliness.
		return 'Не удалось подключиться к WHOIS-серверу ('.$whois_server.'): '.$errstr;
	}
	fputs($sock, $ip ."\n");
	$buffer = '';
	do {
		$buffer .= fread($sock, 10240);
	} while (!feof($sock));
	fclose($sock);

	return $buffer;
}

function fud_whois($ip)
{
	$result = __fud_whois($ip);

	/* Check if ARIN can handle the request or if we need to
	 * request information from another server.
	 */
	if (($p = strpos($result, 'ReferralServer: whois://')) !== false) {
		$p += strlen('ReferralServer: whois://');
		$e = strpos($result, "\n", $p);
		$whois = substr($result, $p, ($e - $p));
		if ($whois) {
			$result = __fud_whois($ip, $whois);
		}
	}

	return ($result ? $result : 'Whois-информация для IP <b>'.$ip.'</b> не доступна.');
}

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}

	if (isset($_POST['ip'])) {
		$_GET['ip'] = $_POST['ip'];
	}
	$ip = isset($_GET['ip']) ? long2ip(ip2long($_GET['ip'])) : '';
	if (isset($_POST['user'])) {
		$_GET['user'] = $_POST['user'];
	}
	if (isset($_GET['user'])) {
		if (($user_id = (int) $_GET['user'])) {
			$user = q_singleval('SELECT alias FROM fud26_users WHERE id='. $user_id);
		} else {
			list($user_id, $user) = db_saq('SELECT id, alias FROM fud26_users WHERE alias='. _esc(char_fix(htmlspecialchars($_GET['user']))));
		}
	} else {
		$user = '';
	}

	$TITLE_EXTRA = ': IP Браузер';

	if ($ip) {
		if (substr_count($ip, '.') == 3) {
			$cond = 'm.ip_addr=\''. $ip .'\'';
		} else {
			$cond = 'm.ip_addr LIKE \''. $ip .'%\'';
		}

		$o = uq('SELECT DISTINCT(m.poster_id), u.alias FROM fud26_msg m INNER JOIN fud26_users u ON m.poster_id=u.id WHERE '. $cond);
		$user_list = '';
		$i = 0;
		while ($r = db_rowarr($o)) {
			$user_list .= '<tr><td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a></td></tr>';
		}
		unset($o);
		$o = uq('SELECT id, alias FROM fud26_users WHERE reg_ip='. ip2long($ip));
		while ($r = db_rowarr($o)) {
			$user_list .= '<tr><td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a></td></tr>';
		}
		unset($o);
		$page_data = '<table cellspacing="2" cellpadding="2" class="MiniTable">
<tr>
	<td class="vt"><table cellspacing="0" cellpadding="2" class="ContentTable">
		<tr><th>Участники, которые использовали IP '.$ip.'</th></tr>'.$user_list.'
	</table></td>
	<td width="50"> </td>
	<td class="vt"><b>Информация о провайдере</b><br /><div class="ip"><pre>'.fud_whois($ip).'</pre></div></td>
</tr>
</table>';
	} else if ($user) {
		$o = uq('SELECT DISTINCT(ip_addr) FROM fud26_msg WHERE poster_id='. $user_id);
		$ip_list = '';
		$i = 0;
		while ($r = db_rowarr($o)) {
			$ip_list .= '<tr><td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="index.php?t=ip&amp;ip='.$r[0].'&amp;'._rsid.'">'.$r[0].'</a></td></tr>';
		}
		unset($o);
		
		$o = uq('SELECT reg_ip FROM fud26_users WHERE id='. $user_id);
		while ($r = db_rowarr($o)) {
			$r[0] = long2ip($r[0]);
			$ip_list .= '<tr><td class="'.alt_var('ip_alt','RowStyleA','RowStyleB').'">'.++$i.'. <a href="index.php?t=ip&amp;ip='.$r[0].'&amp;'._rsid.'">'.$r[0].'</a></td></tr>';
		}
		unset($o);

		$page_data = '<table cellspacing="2" cellpadding="2" class="MiniTable">
<tr><th>Все IP-адреса, которые использует участник &#39;'.$user.'&#39;</th></tr>
'.$ip_list.'
</table>';
	} else {
		$page_data = '';
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

<div class="ctb"><table cellspacing="0" cellpadding="0" class="MiniTable"><tr><td><fieldset>
	<legend>Искать пользователей по IP-адресу</legend>
<form method="post" action="index.php?t=ip"><?php echo _hs; ?>
<span class="SmallText">Поддерживаемый синтаксис: 1.2.3.4, 1.2.3, 1.2, 1<br /></span>
<input type="text" name="ip" value="<?php echo $ip; ?>" size="20" maxlength="15" /> <input type="submit" value="Поиск" />
</form>
</fieldset></td><td width="50"> </td><td><fieldset>
	<legend>Анализировать использование IP-адреса</legend>
<form method="post" action="index.php?t=ip"><?php echo _hs; ?>
<span class="SmallText">Пожалуйста, введите точное имя учётной записи.<br /></span>
<input type="text" name="user" value="<?php echo $user; ?>" size="20" /> <input type="submit" value="Поиск" />
</form>
</fieldset></td>
</tr></table>
<br /><br />
<?php echo $page_data; ?>
</div>
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
