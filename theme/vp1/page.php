<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: page.php.t 5046 2010-10-17 12:28:28Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}

if (isset($_GET['id'])) {
	$page = db_sab('SELECT id, slug, title, body FROM fud26_pages WHERE id='. (int)$_GET['id']);
	$TITLE_EXTRA = ': '. $page->title;
} else {
	$page_list = '';
	$i = 0;
	$c = q('SELECT id, slug, title, body FROM fud26_pages');
	while ($r = db_rowobj($c)) {
		$page_list .= '<li><a href="index.php?t=page&page='.$r->id.'">'.$r->title.'</a>';
		$i++;
	}
}

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/vp1/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
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
<table cellspacing="1" cellpadding="2" class="ContentTable"><tr><td>
<?php echo (!empty($page->title) ? '
	<h2>'.$page->title.'</h2>
	'.$page->body.'
' : '
	<h2>Available pages</h2>
	<ul>
		'.$page_list.'
	</ul>
	<i>'.$i.' pages listed.</i>
'); ?>
</td></tr></table>
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
