<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: err.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

function error_dialog($title, $msg, $level='WARN', $ses=null)
{
	if (!$ses) {
		$ses = (int) $GLOBALS['usr']->sid;
	}

	$error_msg = '[Error] '.$title.'<br />';
	$error_msg .= '[Message Sent to User] '. trim($msg) .'<br />';
	$error_msg .= '[User IP] '. get_ip() .'<br />';
	$error_msg .= '[Requested URL] http://';
	$error_msg .= isset($_SERVER['HTTP_HOST']) ? htmlspecialchars($_SERVER['HTTP_HOST']) : '';
	$error_msg .= isset($_SERVER['REQUEST_URI']) ? htmlspecialchars($_SERVER['REQUEST_URI']) : '';
	$error_msg .= !empty($_POST) ? '<br />[Post-Data] '. base64_encode(serialize($_POST)) : '';
	$error_msg .= '<br />';

	if (isset($_SERVER['HTTP_REFERER'])) {
		$error_msg .= '[Referring Page] '. htmlspecialchars($_SERVER['HTTP_REFERER']) .'<br />';
	}

	fud_logerror($error_msg, 'fud_errors');

	/* No need to redirect, we just want to log the error. */
	if ($level == 'ATCH') {
		return;
	}

	ses_putvar($ses, array('er_msg' => $msg, 'err_t' => $title));

	if (is_int($ses)) {
		if ($GLOBALS['FUD_OPT_2'] & 32768) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/e/'. _rsidl);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=error&'. _rsidl);
		}
	} else {
		if ($GLOBALS['FUD_OPT_2'] & 32768) {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php/e/0/'. $ses);
		} else {
			header('Location: '.$GLOBALS['WWW_ROOT'].'index.php?t=error&S='. $ses);
		}
	}
	exit;
}

function std_error($type)
{
	if (!isset($_SERVER['HTTP_REFERER'])) {
		$_SERVER['HTTP_REFERER'] = 'unknown';
	}

	$ses_id = s;
	$usr_d = new stdClass();
	$usr_d->email = $GLOBALS['usr']->email;

	if ($type == 'login') {
		if (__fud_real_user__) {
			$type = 'perms';
		} else {
			ses_anonuser_auth($GLOBALS['usr']->sid, '<fieldset><legend>ОШИБКА: Вы не вошли на форум</legend>Чтобы получить доступ к этой странице вы должны войти на форум.</fieldset><br />');
		}
	}

	$err_array = array(
'ERR_disabled'=>array('Действие невозможно', 'Данная возможность была отключена системным администратором.'),
'ERR_access'=>array('ОШИБКА: Недостаточен уровень доступа', 'У вас недостаточно прав для выполнения этого действия.'),
'ERR_registration_disabled'=>array('Регистрация отключена', 'Администратор запретил регистрацию пользователей, попробуйте еще раз через некоторое время'),
'ERR_user'=>array('ОШИБКА: Несуществующий пользователь', 'Такой пользователь не существует'),
'ERR_perms'=>array('В доступе отказано!', 'Для выполнения запрошенной вами операции вам не хватает полномочий.'),
'ERR_systemerr'=>array('ОШИБКА: Системная ошибка', 'Произошла системная ошибка. Пожалуйста, сообщите о ней <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">вебмастеру</a>.'),
'ERR_emailconf'=>array('ОШИБКА: Ваша регистрация не была подтверждена', 'Вы пока не подтвердили свою регистрацию по электронной почте<br /><table border="0"><tr><td><ol><li>Если вы не получили сообщения о подтверждении вашей регистрации — <a href="index.php?t=reset&amp;email='.$usr_d->email.'&amp;S='.$ses_id.'">нажмите здесь</a><li>Если '.$usr_d->email.' не является вашим адресом — <a href="index.php?t=register&amp;S='.$ses_id.'">нажмите здесь</a></ol></td></tr></table>')
);

	if (isset($err_array['ERR_'. $type])) {
		$err = $err_array['ERR_'. $type];
		error_dialog($err[0], $err[1]);
	}
	error_dialog('Критическая ошибка', 'Вы обнаружили проблему, но, к сожалению, в системе сообщений о проблемах также произошла ошибка');
}

function invl_inp_err()
{
	error_dialog('Недопустимый ввод', 'Вы попали на эту страницу по ошибочной ссылке, известите об этом <a href="mailto:'.$GLOBALS['ADMIN_EMAIL'].'">webmaster</a>&#39;а');
}
?>
