<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: stats.inc.t 5052 2010-10-21 20:48:03Z janrei $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if ($FUD_OPT_2 & 2 || $is_a) {	// PUBLIC_STATS is enabled or Admin user.
	$page_gen_time = number_format(microtime(true) - __request_timestamp_exact__, 5);
	$page_stats = $FUD_OPT_2 & 2 ? '<br /><div class="SmallText al">�����, ����������� �� ��������� ��������: '.$page_gen_time.' ���.</div>' : '<br /><div class="SmallText al">�����, ����������� �� ��������� ��������: '.$page_gen_time.' ���.</div>';
} else {
	$page_stats = '';
}
?>
