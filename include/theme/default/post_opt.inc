<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: post_opt.inc.t 5023 2010-10-07 20:29:05Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

function tmpl_post_options($arg, $perms=0)
{
	$post_opt_html		= '<b>HTML</b>  - <b>выключен</b>';
	$post_opt_fud		= '<b>BBcode</b> - <b>выключен</b>';
	$post_opt_images 	= '<b>Картинки</b> - <b>выключены</b>';
	$post_opt_smilies	= '<b>Смайлики</b> - <b>выключены</b>';
	$edit_time_limit	= '';

	if (is_int($arg)) {
		if ($arg & 16) {
			$post_opt_fud = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank"><b>BBcode</b> - <b>включен</b></a>';
		} else if (!($arg & 8)) {
			$post_opt_html = '<b>HTML</b> - <b>включен</b>';
		}
		if ($perms & 16384) {
			$post_opt_smilies = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>Смайлики</b> - <b>включен</b></a>';
		}
		if ($perms & 32768) {
			$post_opt_images = '<b>Картинки</b> - <b>включены</b>';
		}
		if ($GLOBALS['EDIT_TIME_LIMIT'] >= 0) {	// Time limit enabled,
			$edit_time_limit = $GLOBALS['EDIT_TIME_LIMIT'] ? '<br /><b>Период возможности редактирования</b>: <b>'.$GLOBALS['EDIT_TIME_LIMIT'].'</b> мин.' : '<br /><b>Период возможности редактирования</b>: <b>Неограниченно</b>';
		}
	} else if ($arg == 'private') {
		$o =& $GLOBALS['FUD_OPT_1'];

		if ($o & 4096) {
			$post_opt_fud = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank"><b>BBcode</b> - <b>включен</b></a>';
		} else if (!($o & 2048)) {
			$post_opt_html = '<b>HTML</b> - <b>включен</b>';
		}
		if ($o & 16384) {
			$post_opt_images = '<b>Картинки</b> - <b>включены</b>';
		}
		if ($o & 8192) {
			$post_opt_smilies = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>Смайлики</b> - <b>включен</b></a>';
		}
	} else if ($arg == 'sig') {
		$o =& $GLOBALS['FUD_OPT_1'];

		if ($o & 131072) {
			$post_opt_fud = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#style" target="_blank"><b>BBcode</b> - <b>включен</b></a>';
		} else if (!($o & 65536)) {
			$post_opt_html = '<b>HTML</b> - <b>включен</b>';
		}
		if ($o & 524288) {
			$post_opt_images = '<b>Картинки</b> - <b>включены</b>';
		}
		if ($o & 262144) {
			$post_opt_smilies = '<a href="index.php?section=readingposting&amp;t=help_index&amp;'._rsid.'#sml" target="_blank"><b>Смайлики</b> - <b>включен</b></a>';
		}
	}

	return '<span class="SmallText"><b>Параметры форума</b><br />
'.$post_opt_html.'<br />
'.$post_opt_fud.'<br />
'.$post_opt_images.'<br />
'.$post_opt_smilies.$edit_time_limit.'</span>';
}
?>
