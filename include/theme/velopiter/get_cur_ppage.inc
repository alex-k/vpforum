<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: get_cur_ppage.inc.t 4898 2010-01-25 21:30:30Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

$folders = array(1=>'Входящие', 2=>'Сохранено', 4=>'Черновики', 3=>'Отправленные', 5=>'Корзина');

function tmpl_cur_ppage($folder_id, $folders, $msg_subject='')
{
	if (!$folder_id || (!$msg_subject && $_GET['t'] == 'ppost')) {
		$user_action = 'Создание личного письма';
	} else {
		$user_action = $msg_subject ? '<a href="index.php?t=pmsg&amp;folder_id='.$folder_id.'&amp;'._rsid.'">'.$folders[$folder_id].'</a> &raquo; '.$msg_subject.'' : 'Просмотр папки <b>'.$folders[$folder_id].'</b>';
	}

	return '<span class="GenText"><a href="index.php?t=pmsg&amp;'._rsid.'">Личная почта</a>&nbsp;&raquo;&nbsp;'.$user_action.'</span><br /><img src="blank.gif" alt="" height="4" width="1" /><br />';
}
?>