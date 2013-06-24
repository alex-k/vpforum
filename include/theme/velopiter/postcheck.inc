<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: postcheck.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

$GLOBALS['__error__'] = 0;
$GLOBALS['__err_msg__'] = array();

function set_err($err, $msg)
{
	$GLOBALS['__err_msg__'][$err] = $msg;
	$GLOBALS['__error__'] = 1;
}

function is_post_error()
{
	return $GLOBALS['__error__'];
}

function get_err($err, $br=0)
{
	if (isset($err, $GLOBALS['__err_msg__'][$err])) {
		return ($br ? '<span class="ErrorText">'.$GLOBALS['__err_msg__'][$err].'</span><br />' : '<br /><span class="ErrorText">'.$GLOBALS['__err_msg__'][$err].'</span>');
	}
}

function post_check_images()
{
	if (!empty($_POST['msg_body']) && $GLOBALS['MAX_IMAGE_COUNT'] && $GLOBALS['MAX_IMAGE_COUNT'] < count_images((string)$_POST['msg_body'])) {
		return -1;
	}

	return 0;
}

function check_post_form()
{
	/* Make sure we got a valid subject. */
	if (!strlen(trim((string)$_POST['msg_subject']))) {
		set_err('msg_subject', 'Указание темы является обязательным');
	}

	/* Make sure the number of images [img] inside the body do not exceed the allowed limit. */
	if (post_check_images()) {
		set_err('msg_body', 'Пожалуйста сократите количество картинок в сообщении до максимально допустимого, которое составляет '.$GLOBALS['MAX_IMAGE_COUNT'].'');
	}

	/* Captcha check for anon users. */
	if (!_uid && $GLOBALS['FUD_OPT_3'] & 8192 ) {
		if (empty($_POST['turing_test']) || empty($_POST['turing_res']) || !test_turing_answer($_POST['turing_test'], $_POST['turing_res'])) {
			set_err('reg_turing', 'Invalid validation code.');
		}
	}

	if (defined('fud_bad_sq')) {
		unset($_POST['submitted']);
		set_err('msg_session', '<h4 class="ErrorText ac">Ваш сеанс работы истек, пожалуйста, отправьте форму заново. Приносим свои извинения за неудобство.</h4>');
	}

	/* Check for duplicate topics (exclude replies and edits). */
	if (($GLOBALS['FUD_OPT_3'] & 67108864) && $_POST['reply_to'] == 0 && $_POST['msg_id'] == 0) {
		$c = q_singleval('SELECT count(*) FROM fud26_msg WHERE subject='. _esc($_POST['msg_subject']) .' AND reply_to=0 AND poster_id='. _uid .' AND post_stamp >= '. (__request_timestamp__ - 86400));
		if ( $c > 0 ) {
			set_err('msg_body', 'Пожалуйста, не создавайте дубликатов тем.');
		}
	}

	/* Check against minimum post length. */
	if ($GLOBALS['POST_MIN_LEN']) {
		$body_without_bbcode = preg_replace('/\[(.*?)\]|\s+/', '', $_POST['msg_body']);	// Remove tags and whitespace.
		if (strlen($body_without_bbcode) < $GLOBALS['POST_MIN_LEN']) {
			$post_min_len = $GLOBALS['POST_MIN_LEN'];
			set_err('msg_body', 'Ваше сообщение слишком коротко. Минимальный размер составляет '.convertPlural($post_min_len, array(''.$post_min_len.' символ',''.$post_min_len.' символа',''.$post_min_len.' символов')).'.');
		}
		unset($body_without_bbcode);
	}

	/* Check if user is allowed to post links. */
	if ($GLOBALS['POSTS_BEFORE_LINKS'] && !empty($_POST['msg_body'])) {
		if (preg_match('?(\[url)|(http://)|(https://)?i', $_POST['msg_body'])) {
			$c = q_singleval('SELECT posted_msg_count FROM fud26_users WHERE id='. _uid);
			if ( $GLOBALS['POSTS_BEFORE_LINKS'] > $c ) {
				$posts_before_links = $GLOBALS['POSTS_BEFORE_LINKS'];
				set_err('msg_body', 'Вы не можете использовать ссылки, пока вами отправлено менее '.convertPlural($posts_before_links, array(''.$posts_before_links.' сообщения',''.$posts_before_links.' сообщений',''.$posts_before_links.' сообщений')).'.');
			}
		}
	}

	return $GLOBALS['__error__'];
}

function check_ppost_form($msg_subject)
{
	if (!strlen(trim($msg_subject))) {
		set_err('msg_subject', 'Указание темы является обязательным');
	}

	if (post_check_images()) {
		set_err('msg_body', 'Пожалуйста сократите количество картинок в сообщении до максимально допустимого, которое составляет '.$GLOBALS['MAX_IMAGE_COUNT'].'');
	}

	if (empty($_POST['msg_to_list'])) {
		set_err('msg_to_list', 'Невозможно послать сообщение если не указан получатель');
	} else {
		$GLOBALS['recv_user_id'] = array();
		/* Hack for login names containing HTML entities ex. &#123; */
		if (($hack = strpos($_POST['msg_to_list'], '&#')) !== false) {
			$hack_str = preg_replace('!&#([0-9]+);!', '&#\1#', $_POST['msg_to_list']);
		} else {
			$hack_str = $_POST['msg_to_list'];
		}
		foreach(explode(';', $hack_str) as $v) {
			$v = trim($v);
			if (strlen($v)) {
				if ($hack !== false) {
					$v = preg_replace('!&#([0-9]+)#!', '&#\1;', $v);
				}
				if (!($obj = db_sab('SELECT u.users_opt, u.id, ui.ignore_id FROM fud26_users u LEFT JOIN fud26_user_ignore ui ON ui.user_id=u.id AND ui.ignore_id='. _uid .' WHERE u.alias='. ssn(char_fix(htmlspecialchars($v)))))) {
					set_err('msg_to_list', 'В этом форуме нет участника с именем "'.char_fix(htmlspecialchars($v)).'"');
					break;
				}
				if (!empty($obj->ignore_id)) {
					set_err('msg_to_list', 'Вы не можете отправить личное сообщение для "'.char_fix(htmlspecialchars($v)).'", поскольку этот пользователь вас игнорирует.');
					break;
				} else if (!($obj->users_opt & 32) && !$GLOBALS['is_a']) {
					set_err('msg_to_list', 'Вы не можете отправить личное сообщение для "'.htmlspecialchars($v).'", потому что этот участник запретил прием личных сообщений.');
					break;
				} else {
					$GLOBALS['recv_user_id'][] = $obj->id;
				}
			}
		}
	}

	if (defined('fud_bad_sq')) {
		unset($_POST['btn_action']);
		set_err('msg_session', '<h4 class="ErrorText ac">Ваш сеанс работы истек, пожалуйста, отправьте форму заново. Приносим свои извинения за неудобство.</h4>');
	}

	return $GLOBALS['__error__'];
}

function check_femail_form()
{
	if (empty($_POST['femail']) || validate_email($_POST['femail'])) {
		set_err('femail', 'Пожалуйста введите правильный адрес e-mail.');
	}
	if (empty($_POST['subj'])) {
		set_err('subj', 'Невозможно послать сообщение если не указана тема.');
	}
	if (empty($_POST['body'])) {
		set_err('body', 'Невозможно послать сообщение если текст сообщения отсутствует.');
	}
	if (defined('fud_bad_sq')) {
		unset($_POST['posted']);
		set_err('msg_session', '<h4 class="ErrorText ac">Ваш сеанс работы истек, пожалуйста, отправьте форму заново. Приносим свои извинения за неудобство.</h4>');
	}

	return $GLOBALS['__error__'];
}

function count_images($text)
{
	$text = strtolower($text);
	$a = substr_count($text, '[img]');
	$b = substr_count($text, '[/img]');

	return (($a > $b) ? $b : $a);
}
?>
