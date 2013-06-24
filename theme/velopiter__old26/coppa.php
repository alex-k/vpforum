<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: coppa.php.t 4898 2010-01-25 21:30:30Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}
	$TITLE_EXTRA = ': Подтверждение COPPA';
if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw"><img src="theme/velopiter__old26/images/top_pm'.img_ext.'" alt="Личная почта" /> У вас есть непрочитанные сообщения (<span class="GenText" style="color: #ff0000">('.$c.')</span>)</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw"><img src="theme/velopiter__old26/images/top_pm'.img_ext.'" alt="Личная почта" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}
	$coppa = __request_timestamp__-409968000;

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<html>
<head>
<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
<meta http-equiv="Content-Type" content="text/html; charset=koi8-r" />
<BASE HREF="<?php echo $GLOBALS['WWW_ROOT']; ?>">
<link rel="StyleSheet" href="theme/velopiter__old26/forum.css" type="text/css" media="screen" title="Default FUDforum Theme">
</head>
<body>
<script language="javascript" src="lib.js" type="text/javascript"></script>
<link rel="StyleSheet" href="theme/velopiter__old26/forum.css" type="text/css" media="screen" title="Default FUDforum Theme">
</head>
<body>
<script language="javascript" src="lib.js" type="text/javascript"></script>

<table class="wa" border="0" cellspacing="3" cellpadding="5">
<tr ><td bgcolor=#6699cc>
<table width=100% cellspacing=0 cellpadding=0 border=0>
<tr height=100><td width=320>
<a href="/"><img src="/forum/logo.gif" width=312 height=79 border=0 alt="бЕКНоХРЕП"></a>
</td>
<td align=right valign=bottom>
<table cellspacing=0 height=100% cellpadding=5>
<tr valign=top><td>
<? include("../newstape_inc.php"); ?>
</td></td></table>
</td>
<td align=right width=350>
<!-- banners start -->
<div align=right>
<table cellspacing=0 cellpadding=5>
<tr valign=top>

<td>

<a href="http://velodrive.ru/" target="_blank"><img src="http://velopiter.spb.ru/vdr.gif" border="0" width="200" height="100" alt="бЕКНЯХОЕДШ бЕКНДПЮИБ"></a>

</td>

<td><a href="http://pk-99.ru/" target="_blank">
  <img border="0" src="http://velopiter.spb.ru/pk.gif" alt="охй-99"
width="100" height="100"></a></td>


<td width="100" height=100 align=right>
<a href="http://www.alienbike.ru"></a><object classid="clsid:d27cdb6e-ae6d-11cf-96b8-444553540000" codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0" width="100" height="100" id="090406_5" align="right">
<param name="allowScriptAccess" value="sameDomain" />
<param name="movie" value="http://velopiter.spb.ru/090406_5.swf" /><param name="loop" value="false" /><param name="menu" value="false" /><param name="quality" value="high" /><param name="bgcolor" value="#000000" /><embed src="http://velopiter.spb.ru/090406_5.swf" loop="false" menu="false" quality="high" bgcolor="#000000" width="100" height="100" name="../090406_5" align="middle" allowScriptAccess="sameDomain" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer" />
</object></td>


</tr></table></div>
<!--- banners end-->

</td></tr></table>
</td>
</tr>

<tr><td class="ForumBackground">
<div class="UserControlPanel">

<a class="UserControlPanel nw" 
href="index.php?t=msg&th=102972&start=0&rid=691">
<img border=0 src="images/message_icons/icon4.gif">опюбхкю</a> 
 
<?php echo $private_msg; ?> <?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<a class="UserControlPanel nw" href="index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'"><img src="theme/velopiter__old26/images/top_members'.img_ext.'" alt="Пользователи" /> Пользователи</a>&nbsp;&nbsp;' : ''); ?> <?php echo ($FUD_OPT_1 & 16777216 ? '<a class="UserControlPanel nw" href="index.php?t=search&amp;'._rsid.'"><img src="theme/velopiter__old26/images/top_search'.img_ext.'" alt="Поиск" /> Поиск</a>&nbsp;&nbsp;' : ''); ?> <a class="UserControlPanel nw" accesskey="h" href="index.php?t=help_index&amp;<?php echo _rsid; ?>"><img src="theme/velopiter__old26/images/top_help<?php echo img_ext; ?>" alt="F.A.Q." /> F.A.Q.</a> <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=uc&amp;'._rsid.'"><img src="theme/velopiter__old26/images/top_profile'.img_ext.'" title="Нажмите для перехода в панель управления" alt="Настройки" /> Настройки</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=register&amp;'._rsid.'"><img src="theme/velopiter__old26/images/top_register'.img_ext.'" alt="Регистрация" /> Регистрация</a>'); ?> <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'"><img src="theme/velopiter__old26/images/top_logout'.img_ext.'" alt="Выход" /> Выход [ '.$usr->alias.' ]</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'"><img src="theme/velopiter__old26/images/top_login'.img_ext.'" alt="Вход" /> Вход</a>'); ?>&nbsp;&nbsp; <a class="UserControlPanel nw" href="index.php?t=index&amp;<?php echo _rsid; ?>"><img src="theme/velopiter__old26/images/top_home<?php echo img_ext; ?>" alt="Начало" /> Начало</a> <?php echo ($is_a ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="adm/admglobal.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'"><img src="theme/velopiter__old26/images/top_admin'.img_ext.'" alt="Административный центр" /> Административный центр</a>' : ''); ?></div>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<tr class="RowStyleA GenText ac"><td>
Выберите ссылку, правильно указывающую на дату вашего рождения<br /><br />
[<a href="index.php?t=pre_reg&amp;coppa=&amp;<?php echo _rsid; ?>">После <?php echo strftime("%B %e, %Y", $coppa); ?></a>]&nbsp;
[<a href="index.php?t=pre_reg&amp;coppa=1&amp;<?php echo _rsid; ?>">Ранее <?php echo strftime("%B %e, %Y", $coppa); ?></a>]
<hr>
Для завершения регистрации детей в возрасте до 13 лет требуется получение факсом или по почте подписанного разрешения от родителей или опекунов.<p>Адрес для получения дополнительной информации: <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>"><?php echo $GLOBALS['ADMIN_EMAIL']; ?></a>
</td></tr>
</table>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %e %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
</td></tr></table><div class="ForumBackground ac foot">
<b>.::</b> <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>">Обратная связь</a> <b>::</b> <a href="index.php?t=index&amp;<?php echo _rsid; ?>">Начало</a> <b>::.</b>
<p>
<span class="SmallText">Powered by: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br />Copyright &copy;2001-2006 <a href="http://fudforum.org/">FUD Forum Bulletin Board Software</a></span>

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
alt="щЙЯРПЕЛЮКЭМШИ ОНПРЮК VVV.RU"></a>
</span>

</div>
</body></html>