<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: usercp.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

	if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw"><img src="theme/velopiter__old26/images/top_pm'.img_ext.'" alt="Личная почта" /> У вас есть непрочитанные сообщения (<span class="GenText" style="color: #ff0000">('.$c.')</span>)</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw"><img src="theme/velopiter__old26/images/top_pm'.img_ext.'" alt="Личная почта" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}
?>