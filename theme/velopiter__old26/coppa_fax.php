<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: coppa_fax.php.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}

	/* this form is for printing, therefore it lacks any advanced layout */
	if (!__fud_real_user__) {
		if ($FUD_OPT_2 & 32768) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/i/'. _rsidl);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=index&'. _rsidl);
		}
		exit;
	}
	$name = q_singleval('SELECT name FROM fud26_users WHERE id='. __fud_real_user__);


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head><title> </title></head>
<body bgcolor="#ffffff">
<b>Инструкции для родителей или опекунов</b><br /><br />
Пожалуйста распечатайте и заполните приведенную форму, подпишите ее и отошлите по адресу:
<pre>
<?php echo @file_get_contents($FORUM_SETTINGS_PATH."coppa_maddress.msg"); ?>
</pre>
<table border=1 cellspacing=1 cellpadding=3>
<tr><td colspan=2>Регистрационная форма</td></tr>
<tr><td>Имя пользователя</td><td><?php echo $usr->login; ?></td></tr>
<tr><td>Пароль</td><td>&lt;HIDDEN&gt;</td></tr>
<tr><td>Адрес E-mail</td><td><?php echo $usr->email; ?></td></tr>
<tr><td>Имя</td><td><?php echo $name; ?></td></tr>
<tr><td colspan=2>
Пожалуйста распечатайте и заполните приведенную форму и отошлите ее нам<br />
Я ознакомился с предоставленной мне ребенком информацией и частными правилами указанного web-сайта. Я понимаю что персональные данные могут быть изменены, используя пароль, и понимаю, что в любой момент могу требовать аннулировать регистрацию.
</td></tr>
<tr><td>Подпишите, если вы даете разрешение</td><td><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td></tr>
<tr><td>Подпишите здесь, если хотите, чтобы регистрация была аннулирована</td><td><u>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</u></td></tr>
<tr><td>Полное имя родителя/опекуна:</td><td>&nbsp;</td></tr>
<tr><td>Степень родства с ребенком:</td><td>&nbsp;</td></tr>
<tr><td>Телефон:</td><td>&nbsp;</td></tr>
<tr><td>Адрес E-mail:</td><td>&nbsp;</td></tr>
<tr><td>Дата:</td><td>&nbsp;</td></tr>
<tr><td colspan=2>Консультация по любым вопросам по адресу <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>"><?php echo $GLOBALS['ADMIN_EMAIL']; ?></a></td></tr>
</table>
</body>
</html>
