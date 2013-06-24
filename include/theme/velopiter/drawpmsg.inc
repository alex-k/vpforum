<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: drawpmsg.inc.t 4994 2010-09-02 17:33:29Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

$GLOBALS['affero_domain'] = parse_url($GLOBALS['WWW_ROOT']);

function tmpl_drawpmsg($obj, $usr, $mini)
{
	$o1 =& $GLOBALS['FUD_OPT_1'];
	$o2 =& $GLOBALS['FUD_OPT_2'];
	$a = (int) $obj->users_opt;
	$b =& $usr->users_opt;

	if (!$mini) {
		$custom_tag = $obj->custom_status ? '<br />'.$obj->custom_status.'' : '';
		$c = (int) $obj->level_opt;

		if ($obj->avatar_loc && $a & 8388608 && $b & 8192 && $o1 & 28 && !($c & 2)) {
			if (!($c & 1)) {
				$level_name =& $obj->level_name;
				$level_image = $obj->level_img ? '&nbsp;<img src="images/'.$obj->level_img.'" alt="" />' : '';
			} else {
				$level_name = $level_image = '';
			}
		} else {
			$level_image = $obj->level_img ? '&nbsp;<img src="images/'.$obj->level_img.'" alt="" />' : '';
			$obj->avatar_loc = '';
			$level_name =& $obj->level_name;
		}
		$avatar = ($obj->avatar_loc || $level_image) ? '<td class="avatarPad wo">'.$obj->avatar_loc.$level_image.'</td>' : '';
		$dmsg_tags = ($custom_tag || $level_name) ? '<div class="ctags">'.$level_name.$custom_tag.'</div>' : '';

		if (($o2 & 32 && !($a & 32768)) || $b & 1048576) {
			$obj->login = $obj->alias;
			$online_indicator = (($obj->last_visit + $GLOBALS['LOGEDIN_TIMEOUT'] * 60) > __request_timestamp__) ? '<img src="theme/velopiter/images/online'.img_ext.'" alt="онлайн" title="онлайн" />' : '<img src="theme/velopiter/images/offline'.img_ext.'" alt="оффлайн" title="оффлайн" />';
		} else {
			$online_indicator = '';
		}

		if ($obj->location) {
			if (strlen($obj->location) > $GLOBALS['MAX_LOCATION_SHOW']) {
				$location = substr($obj->location, 0, $GLOBALS['MAX_LOCATION_SHOW']) .'...';
			} else {
				$location = $obj->location;
			}
			$location = '<br /><b>Город:</b> '.$location;
		} else {
			$location = '';
		}
		$usr->buddy_list = $usr->buddy_list ? unserialize($usr->buddy_list) : array();
		if ($obj->user_id != _uid && $obj->user_id > 0) {
			$buddy_link = !isset($usr->buddy_list[$obj->user_id]) ? '<a href="index.php?t=buddy_list&amp;'._rsid.'&amp;add='.$obj->user_id.'&amp;SQ='.$GLOBALS['sq'].'">в контакты</a><br />' : '<br />[<a href="index.php?t=buddy_list&amp;del='.$obj->user_id.'&amp;redr=1&amp;'._rsid.'&amp;SQ='.$GLOBALS['sq'].'">удалить из контактов</a>]';
		} else {
			$buddy_link = '';
		}
		/* Show im buttons if need be. */
		if ($b & 16384) {
			$im = '';
			if ($obj->icq) {
				$im .= '<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'#icq_msg"><img src="theme/velopiter/images/icq'.img_ext.'" alt="" title="'.$obj->icq.'" /></a>&nbsp;';
			}
			if ($obj->aim) {
				$im .= '<a href="aim:goim?screenname='.$obj->aim.'&amp;message=Hi.+Are+you+there?"><img src="theme/velopiter/images/aim'.img_ext.'" title="'.$obj->aim.'" alt="" /></a>&nbsp;';
			}
			if ($obj->yahoo) {
				$im .= '<a href="http://edit.yahoo.com/config/send_webmesg?.target='.$obj->yahoo.'&amp;.src=pg"><img src="theme/velopiter/images/yahoo'.img_ext.'" alt="" title="'.$obj->yahoo.'" /></a>&nbsp;';
			}
			if ($obj->msnm) {
				$im .= '<a href="mailto:'.$obj->msnm.'"><img src="theme/velopiter/images/msnm'.img_ext.'" title="'.$obj->msnm.'" alt="" /></a>';
			}
			if ($obj->jabber) {
				$im .=  '<img src="theme/velopiter/images/jabber'.img_ext.'" title="'.$obj->jabber.'" alt="" />';
			}
			if ($obj->google) {
				$im .= '<img src="theme/velopiter/images/google'.img_ext.'" title="'.$obj->google.'" alt="" />';
			}
			if ($obj->skype) {
				$im .=  '<a href="callto://'.$obj->skype.'"><img src="theme/velopiter/images/skype'.img_ext.'" title="'.$obj->skype.'" alt="" /></a>';
			}
			if ($obj->twitter) {
				$im .=  '<a href="http://twitter.com/'.$obj->twitter.'"><img src="theme/velopiter/images/twitter'.img_ext.'" title="'.$obj->twitter.'" alt="" /></a>';
			}
			if ($o2 & 2048) {
				if ($obj->affero) {
					$im .= '<a href="http://svcs.affero.net/rm.php?r='.$obj->affero.'&amp;ll=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;lp=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;ls='.urlencode($obj->subject).'" target=_blank><img alt="" src="theme/velopiter/images/affero_reg.gif" /></a>';
				} else {
					$im .= '<a href="http://svcs.affero.net/rm.php?m='.urlencode($obj->email).'&amp;ll=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;lp=0.'.urlencode($GLOBALS['affero_domain']['host']).'&amp;ls='.urlencode($obj->subject).'" target=_blank><img alt="" src="theme/velopiter/images/affero_noreg.gif" /></a>';
				}
			}
			if ($im) {
				$dmsg_im_row = ''.$im.'<br />';
			} else {
				$dmsg_im_row = '';
			}
		} else {
			$dmsg_im_row = '';
		}
		if ($obj->ouser_id != _uid) {
			$user_profile = '<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_about.gif" alt="" /></a>';
			$email_link = ($o1 & 4194304 && $a & 16) ? '<a href="index.php?t=email&amp;toi='.$obj->user_id.'&amp;'._rsid.'" rel="nofollow"><img src="theme/velopiter/images/msg_email.gif" alt="" /></a>' : '';
			$private_msg_link = '<a href="index.php?t=ppost&amp;toi='.$obj->user_id.'&amp;'._rsid.'"><img title="Отправить личное сообщение этому участнику" src="theme/velopiter/images/msg_pm.gif" alt="" /></a>';
		} else {
			$user_profile = $email_link = $private_msg_link = '';
		}
		$msg_toolbar = '<tr><td colspan="2" class="MsgToolBar"><table border="0" cellspacing="0" cellpadding="0" class="wa"><tr>
<td class="nw al">'.$user_profile.'&nbsp;'.$email_link.'&nbsp;'.$private_msg_link.'</td>
<td class="nw ar"><a href="index.php?t=pmsg&amp;'._rsid.'&amp;btn_delete=1&amp;sel='.$obj->id.'&amp;SQ='.$GLOBALS['sq'].'"><img src="theme/velopiter/images/msg_delete.gif" alt="delete" /></a>&nbsp;'.($obj->fldr == 4 ? '<a href="index.php?t=ppost&amp;msg_id='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_edit.gif" alt="" /></a>&nbsp;&nbsp;&nbsp;&nbsp;' : '' )  .($obj->fldr == 1 ? '<a href="index.php?t=ppost&amp;reply='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_reply.gif" alt="reply" /></a>&nbsp;<a href="index.php?t=ppost&amp;quote='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_quote.gif" alt="quote" /></a>&nbsp;' : '' )  .'<a href="index.php?t=ppost&amp;forward='.$obj->id.'&amp;'._rsid.'"><img src="theme/velopiter/images/msg_forward.gif" alt="forward" /></a></td>
</tr></table></td></tr>';
	} else {
		$dmsg_tags = $dmsg_im_row = $user_profile = $msg_toolbar = $buddy_link = $avatar = $online_indicator = $host_name = $location = '';
	}
	if ($obj->length > 0) {
		$msg_body = read_pmsg_body($obj->foff, $obj->length);
	} else {
		$msg_body = 'Нет текста сообщения';
	}

	$msg_body = $obj->length ? read_pmsg_body($obj->foff, $obj->length) : 'Нет текста сообщения';

	$file_attachments = '';
	if ($obj->attach_cnt) {
		$c = uq('SELECT a.id, a.original_name, a.dlcount, m.icon, a.fsize FROM fud26_attach a LEFT JOIN fud26_mime m ON a.mime_type=m.id WHERE a.message_id='. $obj->id .' AND attach_opt=1');
		while ($r = db_rowobj($c)) {
			$sz = $r->fsize/1024;
			$sz = $sz<1000 ? number_format($sz, 2) .'KB' : number_format($sz / 1024 ,2) .'MB';
			if(!$r->icon) {
				$r->icon = 'unknown.gif';
			}
			$file_attachments .= '<li />
<a href="index.php?t=getfile&amp;id='.$r->id.'&amp;'._rsid.'&amp;private=1"><img src="images/mime/'.$r->icon.'" class="at" alt="" /></a>
<span class="GenText fb">Вложение:</span> <a href="index.php?t=getfile&amp;id='.$r->id.'&amp;'._rsid.'&amp;private=1">'.$r->original_name.'</a><br />
<span class="SmallText">(Размер: '.$sz.', Загружено '.convertPlural($r->dlcount, array(''.$r->dlcount.' раз',''.$r->dlcount.' раза',''.$r->dlcount.' раз')).')<br /><br /></span>';
		}
		unset($c);
		if ($file_attachments) {
			$file_attachments = '<ul class="AttachmentsList">
'.$file_attachments.'
</ul>';
			/* Append session to getfile. */
			if ($o1 & 128 && !isset($_COOKIE[$GLOBALS['COOKIE_NAME']])) {
				$msg_body = str_replace('<img src="index.php?t=getfile', '<img src="index.php?t=getfile&amp;S='. s, $msg_body);
				$tap = 1;
			}
			if ($o2 & 32768 && (isset($tap) || $o2 & 8192)) {
				$pos = 0;
				while (($pos = strpos($msg_body, '<img src="index.php/fa/', $pos)) !== false) {
					$pos = strpos($msg_body, '"', $pos + 11);
					$msg_body = substr_replace($msg_body, _rsid, $pos, 0);
				}
			}
		}
	}

	return '<tr><td><table cellspacing="0" cellpadding="0" class="MsgTable">
<tr>
<td colspan="2">
  <table cellspacing="0" cellpadding="0" class="ContentTable"><tr>
  <td class="MsgR1 vt al MsgSubText">'.(!$mini && $obj->icon ? '<img src="images/message_icons/'.$obj->icon.'" alt="" />&nbsp;&nbsp;' : '' )  .$obj->subject.'</td>
  <td class="MsgR1 vt ar DateText">'.strftime("%a, %d %B %Y %H:%M", $obj->post_stamp).'</td>
  </tr></table>
</tr>

<tr class="MsgR2">
<td class="MsgR2" width="15%" valign="top">
<table cellspacing="0" cellpadding="0" class="ContentTable"><tr class="MsgR2">'.$online_indicator.'<a href="index.php?t=usrinfo&amp;id='.$obj->user_id.'&amp;'._rsid.'">'.$obj->alias.'</a>'.(!$mini ? ''.($obj->user_id ? '<br />'.$avatar.'<tr class="MsgR2"><td class="msgud">'.$dmsg_tags.'</td></tr><tr class="MsgR2"> <td class="msgud">Сообщений:'.$obj->posted_msg_count.'<br />
Зарегистрирован:'.strftime("%B %Y", $obj->join_date).' '.$location.'' : '' )  .'' : '' )  .'<br />'.$buddy_link.'<br />'.(($b & 1048576 || $usr->md || $o1 & 134217728) ? 'IP: <a href="index.php?t=ip&amp;ip='.$obj->ip_addr.'&amp;'._rsid.'" target="_blank">'.$obj->ip_addr.'</a>' : '' )  .'</td></tr><tr class="MsgR2"><td class="msgud">'.$dmsg_im_row.'</td></tr></table></td>

<td class="MsgR3" width="85%" valign="top">'.$msg_body.$file_attachments.(($obj->sig && $o1 & 32768 && $obj->pmsg_opt & 1 && $b & 4096) ? '<br /><br /><hr class="sig" />'.$obj->sig.'' : '' )  .'</td></tr>'.$msg_toolbar.'
<tr><td class="MsgR2 ac" colspan="2">'.$GLOBALS['dpmsg_prev_message'].' '.$GLOBALS['dpmsg_next_message'].'</td></tr>
</table></td></tr>';
}
?>
