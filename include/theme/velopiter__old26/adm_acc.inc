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
	$GLOBALS['account_accepted'] = '���� ������� ������ \&#39;'.$GLOBALS['r']->login.'\&#39; ���� ���������� ��������������� � � ���������� �� ������ ����������� � ������� \&#39;'.$GLOBALS['FORUM_TITLE'].'\&#39; ��� ������������������ ������������.';
	$GLOBALS['account_rejected'] = '��� ������ �� ����������� � ������ \&#39;'.$GLOBALS['r']->login.'\&#39; ��� �������� ���������������. ������ ��������� � �������� ������ � ����������� �� ������, ���������� � ��������������� ������ �� ������ \&#39;'.$GLOBALS['ADMIN_EMAIL'].'\&#39;.';

	$GLOBALS['account_accepted_s'] = '��� ������ �� ����������� � ������ \&#39;'.$GLOBALS['FORUM_TITLE'].'\&#39; ��� ������������.';
	$GLOBALS['account_rejected_s'] = '��� ������ �� ����������� � ������ \&#39;'.$GLOBALS['FORUM_TITLE'].'\&#39; ��������.';
?>
