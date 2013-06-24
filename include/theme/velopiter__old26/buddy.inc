<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: buddy.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

function buddy_add($user_id, $bud_id)
{
	q('INSERT INTO fud26_buddy (bud_id, user_id) VALUES ('. $bud_id .', '. $user_id .')');
	return buddy_rebuild_cache($user_id);
}

function buddy_delete($user_id, $bud_id)
{
	q('DELETE FROM fud26_buddy WHERE user_id='. $user_id .' AND bud_id='. $bud_id);
	return buddy_rebuild_cache($user_id);
}

function buddy_rebuild_cache($uid)
{
	$arr = array();
	$q = uq('SELECT bud_id FROM fud26_buddy WHERE user_id='. $uid);
	while ($ent = db_rowarr($q)) {
		$arr[$ent[0]] = 1;
	}
	unset($q);

	if ($arr) {
		q('UPDATE fud26_users SET buddy_list='. _esc(serialize($arr)) .' WHERE id='. $uid);
		return $arr;
	}
	q('UPDATE fud26_users SET buddy_list=NULL WHERE id='. $uid);
}
?>
