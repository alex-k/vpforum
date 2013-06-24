<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: th_nav.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

function get_prev_next_th_id($frm_id, $th, &$prev, &$next)
{
	$next = $prev = '';
	$id = q_singleval('SELECT seq FROM fud26_tv_'. $frm_id .' WHERE thread_id='. $th);
	if (!$id) {
		return;
	}

	$nn = $np = 0;

	$c = uq('SELECT m.id, m.subject, tv.seq, t.moved_to FROM fud26_tv_'. $frm_id .' tv INNER JOIN fud26_thread t ON tv.thread_id=t.id INNER JOIN fud26_msg m ON t.root_msg_id=m.id WHERE tv.seq IN('. ($id - 1) .', '. ($id + 1) .')');
	while ($r = db_rowarr($c)) {
		if ($r[2] < $id) {
			if ($r[3]) { /* Moved topic, let's try to find another, */
				$np = 1; continue;
			}
			$prev = '<tr><td class="ar GenText">Предыдущая тема:</td><td class="GenText al"><a href="index.php?t='.$_GET['t'].'&amp;goto='.$r[0].'&amp;'._rsid.'#msg_'.$r[0].'">'.$r[1].'</a></td></tr>';
		} else {
			if ($r[3]) { /* Moved topic, let's try to find another, */
				$nn = 1; continue;
			}
			$next = '<tr><td class="GenText ar">Следующая тема:</td><td class="GenText al"><a href="index.php?t='.$_GET['t'].'&amp;goto='.$r[0].'&amp;'._rsid.'#msg_'.$r[0].'">'.$r[1].'</a></td></tr>';
		}		
	}
	unset($c);

	if ($np) {
		$r = db_saq('SELECT m.id, m.subject FROM fud26_tv_'. $frm_id .' tv INNER JOIN fud26_thread t ON tv.thread_id=t.id INNER JOIN fud26_msg m ON t.root_msg_id=m.id WHERE tv.seq IN('. ($id - 10) .', '. ($id - 2) .') ORDER BY tv.seq ASC  LIMIT 1');
		$prev = '<tr><td class="ar GenText">Предыдущая тема:</td><td class="GenText al"><a href="index.php?t='.$_GET['t'].'&amp;goto='.$r[0].'&amp;'._rsid.'#msg_'.$r[0].'">'.$r[1].'</a></td></tr>';
	}
	if ($nn) {
		$r = db_saq('SELECT m.id, m.subject FROM fud26_tv_'. $frm_id .' tv INNER JOIN fud26_thread t ON tv.thread_id=t.id INNER JOIN fud26_msg m ON t.root_msg_id=m.id WHERE tv.seq IN('. ($id + 2) .', '. ($id + 10) .') ORDER BY tv.seq DESC LIMIT 1');
		$next = '<tr><td class="GenText ar">Следующая тема:</td><td class="GenText al"><a href="index.php?t='.$_GET['t'].'&amp;goto='.$r[0].'&amp;'._rsid.'#msg_'.$r[0].'">'.$r[1].'</a></td></tr>';
	}
}
?>