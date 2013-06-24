<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: adm_acc.inc.t 4936 2010-03-28 22:11:50Z JanRei $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	$login = $GLOBALS['r']->login;
	$GLOBALS['account_accepted'] = 'Ваша учетная запись &#39;'.$login.'&#39; была утверждена администратором и в дальнейшем вы можете участвовать в форумах &#39;'.$GLOBALS['FORUM_TITLE'].'&#39; как зарегистрированный участник.';
	$GLOBALS['account_rejected'] = 'Ваш запрос на регистрацию в форуме &#39;'.$login.'&#39; был отклонен администратором. Узнать подробнее о причинах отказа в регистрации вы можете, связавшись с администратором форума по адресу &#39;'.$GLOBALS['ADMIN_EMAIL'].'&#39;.';

	$GLOBALS['account_accepted_s'] = 'Ваш запрос на регистрацию в форуме &#39;'.$GLOBALS['FORUM_TITLE'].'&#39; был удовлетворен.';
	$GLOBALS['account_rejected_s'] = 'Ваш запрос на регистрацию в форуме &#39;'.$GLOBALS['FORUM_TITLE'].'&#39; отклонен.';
?>
