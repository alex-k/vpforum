<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: calendar.php.t 5071 2010-11-10 18:32:04Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

if (_uid === '_uid') {
		exit('Sorry, you can not access this page.');
	}

if (!($FUD_OPT_3 & 134217728)) {	// Calender is disabled.
	std_error('disabled');
}

ses_update_status($usr->sid, 'Просмотр форумного календаря');

$TITLE_EXTRA = ': Календарь';

/* Draw a calendar.
 * This function is called from a template to inject a calender where it's needed.
 */
function draw_calendar($year, $month, $events = array(), $size = 'large', $highlight_y = '', $highlight_m = '', $highlight_d = '') {
	if ($size == 'large') {
		$weekdays = array('Воскресенье','Понедельник','Вторник','Среда','Четверг','Пятница','Суббота');
	} else {
		$weekdays = array('вс','пн','вт','ср','чт','пт','сб');
	}
	// MONDAY $weekdays = array('Понедельник','Вторник','Среда','Четверг','Пятница','Суббота', 'Воскресенье');

	/* Table headings. */
	$calendar = '<table cellpadding="0" cellspacing="0" class="calendar">';
	$calendar .= '<tr class="calendar-row"><td class="calendar-day-head">'. implode('</td><td class="calendar-day-head">', $weekdays).'</td></tr>';
	$calendar .= '<tr class="calendar-row">';

	/* Days and weeks vars. */
	$running_day = date('w', mktime(0, 0, 0, $month, 1, $year));
	// MONDAY $running_day = date('w', mktime(0, 0, 0, $month, 1, $year)) - 1;
	$days_in_month = date('t', mktime(0, 0, 0, $month, 1, $year));
	$days_in_this_week = 1;
	$day_counter = 0;

	/* Print "blank" days until the first of the current week. */
	for($x = 0; $x < $running_day; $x++) {
		$calendar .= '<td class="calendar-day-np">&nbsp;</td>';
		$days_in_this_week++;
	}

	/* Keep going with days. */
	for ($day = 1; $day <= $days_in_month; $day++) {
		if ($size == 'large') {
			$calendar .= '<td class="calendar-day"><div style="position:relative; height:100px;">';
		} else {
			$calendar .= '<td class="calendar-day"><div style="position:relative;">';
		}

		/* Add in the day number. */
		if ($year == $highlight_y && $month == $highlight_m && $day == $highlight_d) {
			$calendar .= '<div class="day-number"><b><i>*<a href="index.php?t=cal&amp;view=d&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">'. $day .'</a></i></b></div>';
		} else {
			$calendar .= '<div class="day-number"><a href="index.php?t=cal&amp;view=d&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">'. $day .'</a></div>';
		}

		$event_day = sprintf('%04d%02d%02d', $year, $month, $day);
		if (isset($events[$event_day])) {
			$event_count = 0;		
			foreach($events[$event_day] as $event) {
				if ($size == 'large') {
					$calendar .= '<div class="event">'. $event .'</div>';
				} else {
					$event_count++;
				}
			}
			if ($size != 'large' && $event_count) {
				$calendar .= '<div class="event">'. $event_count .'</div>';
			}
		} else {
			$calendar.= str_repeat('<p>&nbsp;</p>',2);
		}

		$calendar .= '</div></td>';
		if ($running_day == 6) {
			$calendar .= '</tr>';
			if (($day_counter+1) != $days_in_month) {
				$calendar .= '<tr class="calendar-row">';
			}
			$running_day = -1;
			$days_in_this_week = 0;
		};
		$days_in_this_week++; $running_day++; $day_counter++;
	};

	/* Finish the rest of the days in the week. */
	if($days_in_this_week < 8) {
		for($x = 1; $x <= (8 - $days_in_this_week); $x++) {
			$calendar .= '<td class="calendar-day-np">&nbsp;</td>';
		}
	}

	/* Finalize and return calendar. */
	$calendar .= '</tr></table>';
	return $calendar;
}

/* Query events from database.
 */
function get_events($year, $month, $day = 0) {
	/* Fetch events to display from DB. */
	$events = array();

	/* Display birthdays (DDMMYYYY) on day view. */
	if ($GLOBALS['FUD_OPT_3'] & 268435456 && $day != 0) {
		$c = uq('SELECT id, alias, birthday FROM fud26_users WHERE birthday LIKE \''. sprintf('%02d%02d', $month, $day) .'%\'');
		while ($r = db_rowarr($c)) {
			$yyyy = substr($r[2], 4);
			$mm   = substr($r[2], 0, 2);
			$dd   = substr($r[2], 2, 2);
			$age  = ($yyyy > 0) ? $year - $yyyy : 0;
			$user = '<a href="index.php?t=usrinfo&amp;id='.$r[0].'&amp;'._rsid.'">'.$r[1].'</a>';
			$events[ $year . $mm . $dd ][] = 'День рождения: '.$user.' '.($age ? '(возраст '.convertPlural($age, array(''.$age.' год',''.$age.' года',''.$age.' лет')).').' : '' ) ; // Replace birth year with current year.
		}
	}

	/* Defined events. */
	$c = uq('SELECT day, descr, link FROM fud26_calendar WHERE (month=\''. $month .'\' AND year=\''. $year .'\') OR (month=\'*\' AND year=\''. $year .'\') OR (month=\''. $month .'\' AND year=\'*\') OR (month=\'*\' AND year=\'*\')');
	while ($r = db_rowarr($c)) {
		if (empty($r[2])) {
			$events[ sprintf('%04d%02d%02d', $year, $month, $r[0]) ][] = $r[1];
		} else {
			$events[ sprintf('%04d%02d%02d', $year, $month, $r[0]) ][] = '<a href="'. $r[2] .'">'. $r[1] .'</a>';
		}
	}

	return $events;
}

if (__fud_real_user__ && $FUD_OPT_1 & 1024) {	// PM_ENABLED
		$c = q_singleval('SELECT count(*) FROM fud26_pmsg WHERE duser_id='. _uid .' AND fldr=1 AND read_stamp=0');
		$private_msg = $c ? '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> У вас <span class="GenTextRed">'.$c.'</span> '.convertPlural($c, array('непрочитанное личное сообщение','непрочитанных личных сообщения','непрочитанных личных сообщений')).'</a>&nbsp;&nbsp;' : '<a href="index.php?t=pmsg&amp;'._rsid.'" class="UserControlPanel nw" title="Личная почта"><img src="theme/default/images/top_pm'.img_ext.'" alt="" /> Личная почта</a>&nbsp;&nbsp;';
	} else {
		$private_msg = '';
	}

/* Get calendar settings. */
$day   = isset($_GET['day'])   ? (int)$_GET['day']   : (int)date('d');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$view  = isset($_GET['view'])  ? $_GET['view']  : 'm';	// Default to month view.
$months = array('Январь','Февраль','Март','Апрель','Май','Июнь','Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь');

/* Build a 'month dropdown' that can be used in templates. */
$select_month_control = '<select name="month" id="month">';
for($m = 1; $m <= 12; $m++) {
	$select_month_control .= '<option value="'. $m .'"'. ($m != $month ? '' : ' selected="selected"') .'>'. $months[ date('n',mktime(0,0,0,$m,1,$year)) - 1 ] .'</option>';
}
$select_month_control .= '</select>';

/* Build a 'year dropdown' that can be used in templates. */
$year_range = 10;
$select_year_control = '<select name="year" id="year">';
for($x = ($year-floor($year_range/2)); $x <= ($year+floor($year_range/2)); $x++) {
	$select_year_control .= '<option value="'. $x .'"'. ($x != $year ? '' : ' selected="selected"') .'>'. $x .'</option>';
}
$select_year_control .= '</select>';

if ($view == 'y') {
	$next_year  = $year + 1;
	$prev_year  = $year - 1;
}

if ($view == 'm') {
	$next_year  = $month != 12 ? $year : $year + 1;
	$prev_year  = $month !=  1 ? $year : $year - 1;
	$next_month = $month != 12 ? $month + 1 : 1;
	$prev_month = $month !=  1 ? $month - 1 : 12;
	
	$events = get_events($year, $month);
}

if ($view == 'd') {
	$tomorrow  = mktime(0, 0, 0, $month, $day+1, $year);
	$yesterday = mktime(0, 0, 0, $month, $day-1, $year);
	
	$next_day   = date('d', $tomorrow);
	$prev_day   = date('d', $yesterday);
	$next_month = date('m', $tomorrow);
	$prev_month = date('m', $yesterday);
	$next_year  = date('Y', $tomorrow);
	$prev_year  = date('Y', $yesterday);

	$events = get_events($year, $month, $day);

	$event_day = sprintf('%04d%02d%02d', $year, $month, $day);
	$events_for_day = '';
	if (isset($events[$event_day])) {
		foreach($events[$event_day] as $event) {
			$events_for_day .= '<li><div class="event">'.$event.'</div></li>';
		}
	}
}

if ($FUD_OPT_2 & 2 || $is_a) {	// PUBLIC_STATS is enabled or Admin user.
	$page_gen_time = number_format(microtime(true) - __request_timestamp_exact__, 5);
	$page_stats = $FUD_OPT_2 & 2 ? '<br /><div class="SmallText al">Общее время, затраченное на создание страницы: '.convertPlural($page_gen_time, array(''.$page_gen_time.' секунда',''.$page_gen_time.' секунд')).'</div>' : '<br /><div class="SmallText al">Общее время, затраченное на создание страницы: '.convertPlural($page_gen_time, array(''.$page_gen_time.' секунда',''.$page_gen_time.' секунд')).'</div>';
} else {
	$page_stats = '';
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="ru" xml:lang="ru">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $GLOBALS['FORUM_TITLE'].$TITLE_EXTRA; ?></title>
<meta name="description" content="<?php echo (!empty($META_DESCR) ? $META_DESCR.'' : $GLOBALS['FORUM_DESCR'].''); ?>" />
<base href="<?php echo $GLOBALS['WWW_ROOT']; ?>" />
<script type="text/javascript" src="js/jquery.js"></script>
<script type="text/javascript" src="js/lib.js"></script>
<link rel="stylesheet" href="theme/default/forum.css" type="text/css" media="screen" title="Default Forum Theme" />
<link rel="search" type="application/opensearchdescription+xml" title="<?php echo $GLOBALS['FORUM_TITLE']; ?> Search" href="<?php echo $GLOBALS['WWW_ROOT']; ?>open_search.php" />
<?php echo $RSS; ?>
</head>
<body>
<table class="wa" border="0" cellspacing="3" cellpadding="5"><tr><td class="ForumBackground" valign="top">
<!-- <td class="ForumBackground" valign="top"> -->
<div class="ForumBackground header">
<?php echo ($GLOBALS['FUD_OPT_1'] & 1 && $GLOBALS['FUD_OPT_1'] & 16777216 ? '
  <div class="headsearch">
    <form id="headsearch" method="get" action="index.php">'._hs.'
      <br /><label accesskey="f" title="Поиск в форумах">Поиск в форумах:<br />
      <input type="text" name="srch" value="" size="15" placeholder="Поиск в форумах" /></label>
      <input type="hidden" name="t" value="search" />
      <input type="submit" name="btn_submit" value="Поиск" class="headbutton" />&nbsp;
    </form>
  </div>
' : ''); ?>
<a href="index.php/.." title="Начало"><img src="theme/default/images/header.gif" alt="" align="left" height="80" />
  <span class="headtitle"><?php echo $GLOBALS['FORUM_TITLE']; ?></span>
</a><br />
<span class="headdescr"><?php echo $GLOBALS['FORUM_DESCR']; ?><br /><br /></span>
</div>
<div class="UserControlPanel">
<a href="/forum/index.php?t=msg&th=102972" class="UserControlPanel nw" title="Правила"><img src="/forum/images/message_icons/icon4.gif" alt=""> Правила форума </a>&nbsp;&nbsp;
  <?php echo $private_msg; ?> 
  <?php echo (($FUD_OPT_1 & 8388608 || (_uid && $FUD_OPT_1 & 4194304) || $usr->users_opt & 1048576) ? '<a class="UserControlPanel nw" href="index.php?t=finduser&amp;btn_submit=Find&amp;'._rsid.'" title="Участники"><img src="theme/default/images/top_members'.img_ext.'" alt="" /> Участники</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_3 & 134217728 ? '<a class="UserControlPanel nw" href="index.php?t=cal&amp;'._rsid.'" title="Календарь"><img src="theme/default/images/calendar'.img_ext.'" alt="" /> Календарь</a>&nbsp;&nbsp;' : ''); ?>
  <?php echo ($FUD_OPT_1 & 16777216 ? '<a class="UserControlPanel nw" href="index.php?t=search'.(isset($frm->forum_id) ? '&amp;forum_limiter='.(int)$frm->forum_id.'' : '' )  .'&amp;'._rsid.'" title="Поиск"><img src="theme/default/images/top_search'.img_ext.'" alt="" /> Поиск</a>
&nbsp;&nbsp;
<a class="UserControlPanel nw" href="/search.html" title="Yandex поиск"><img src="theme/default/images/top_search'.img_ext.'" alt="" /> Поиск через Yandex</a>
&nbsp;&nbsp;' : ''); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" accesskey="h" href="index.php?t=help_index&amp;<?php echo _rsid; ?>" title="F.A.Q."><img src="theme/default/images/top_help<?php echo img_ext; ?>" alt="" /> F.A.Q.</a>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=uc&amp;'._rsid.'" title="Доступ к панели управления пользователя"><img src="theme/default/images/top_profile'.img_ext.'" alt="" /> Настройки</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=register&amp;'._rsid.'" title="Регистрация"><img src="theme/default/images/top_register'.img_ext.'" alt="" /> Регистрация</a>'); ?>
  <?php echo (__fud_real_user__ ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'&amp;logout=1&amp;SQ='.$GLOBALS['sq'].'" title="Выход"><img src="theme/default/images/top_logout'.img_ext.'" alt="" /> Выход [ '.$usr->alias.' ]</a>' : '&nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=login&amp;'._rsid.'" title="Вход"><img src="theme/default/images/top_login'.img_ext.'" alt="" /> Вход</a>'); ?>
  &nbsp;&nbsp;<a class="UserControlPanel nw" href="index.php?t=index&amp;<?php echo _rsid; ?>" title="Начало"><img src="theme/default/images/top_home<?php echo img_ext; ?>" alt="" /> Начало</a>
  <?php echo ($is_a || ($usr->users_opt & 268435456) ? '&nbsp;&nbsp;<a class="UserControlPanel nw" href="adm/index.php?S='.s.'&amp;SQ='.$GLOBALS['sq'].'" title="Административный центр"><img src="theme/default/images/top_admin'.img_ext.'" alt="" /> Административный центр</a>' : ''); ?>
</div>
<table cellspacing="1" cellpadding="2" class="ContentTable">
<?php echo ($view == 'y' ? '
<tr><th colspan="3">
	<h2>&nbsp;<a href="index.php?t=cal&amp;view=y&amp;year='.$prev_year.'" class="control">&laquo;</a>&nbsp; '.$year.' &nbsp;<a href="index.php?t=cal&amp;view=y&amp;year='.$next_year.'" class="control">&raquo;</a>&nbsp;</h2>
</th></tr>
<tr>
	<td width="33%" class="vt"><h4>'.$months[0].' '.$year.'</a></h4>'.draw_calendar($year, 1, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[1].' '.$year.'</h4>'.draw_calendar($year, 2, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[2].' '.$year.'</h4>'.draw_calendar($year, 3, null, 'small', $year, $month, $day).'</td>
</tr><tr>
	<td width="33%" class="vt"><h4>'.$months[3].' '.$year.'</h4>'.draw_calendar($year, 4, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[4].' '.$year.'</h4>'.draw_calendar($year, 5, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[5].' '.$year.'</h4>'.draw_calendar($year, 6, null, 'small', $year, $month, $day).'</td>
</tr><tr>
	<td width="33%" class="vt"><h4>'.$months[6].' '.$year.'</h4>'.draw_calendar($year, 7, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[7].' '.$year.'</h4>'.draw_calendar($year, 8, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[8].' '.$year.'</h4>'.draw_calendar($year, 9, null, 'small', $year, $month, $day).'</td>
</tr><tr>
	<td width="33%" class="vt"><h4>'.$months[9].' '.$year.'</h4>'.draw_calendar($year, 10, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[10].' '.$year.'</h4>'.draw_calendar($year, 11, null, 'small', $year, $month, $day).'</td>
	<td width="33%" class="vt"><h4>'.$months[11].' '.$year.'</h4>'.draw_calendar($year, 12, null, 'small', $year, $month, $day).'</td>
</tr>
' : ''); ?>

<?php echo ($view == 'm' ? '
<tr><th width="35%" class="al">
	<a href="index.php?t=cal&amp;view=m&amp;year='.$prev_year.'&amp;month='.$prev_month.'" class="control">&laquo;</a>
</th><th class="ac">
	<h2>'.$months[$month-1].' <a href="index.php?t=cal&amp;view=y&amp;year='.$year.'" class="control">'.$year.'</a></h2>
</th><th width="35%" class="ar">
	<a href="index.php?t=cal&amp;view=m&amp;year='.$next_year.'&amp;month='.$next_month.'" class="control">&raquo;</a>
</th></tr>
<tr class="ac"><td colspan="3">
'.draw_calendar($year, $month, $events, 'large', $year, $month, $day).'
</td></tr>
<tr>
	<td class="ac" colspan="3">
		<form method="get" action="index.php">
		<b>Перейти к:</b><input type="hidden" name="t" value="cal" />
		<br />'.$select_month_control.' '.$select_year_control.' <input type="submit" name="submit" value="Переход" />
		</form></td>
</tr>
' : ''); ?>

<?php echo ($view == 'd' ? '
<tr><th colspan="2">
	<h2><a href="index.php?t=cal&amp;view=d&amp;year='.$prev_year.'&amp;month='.$prev_month.'&amp;day='.$prev_day.'" class="control">&laquo;</a>
		'.$day.' <a href="index.php?t=cal&amp;view=m&amp;month='.$month.'&amp;year='.$year.'"class="control">'.$months[$month-1].'</a> <a href="index.php?t=cal&amp;view=y&amp;year='.$year.'" class="control">'.$year.'</a>
		<a href="index.php?t=cal&amp;view=d&amp;year='.$next_year.'&amp;month='.$next_month.'&amp;day='.$next_day.'" class="control">&raquo;</a></h2>
</th></tr>
<tr><td class="RowStyleB vt" width="55%">
		<h3>События за день</h3>
		'.($events_for_day ? '<ul>'.$events_for_day.'</ul>' : '<p>Нет событий в этот день.</p>' )  .'
		<br /><br />
		<form method="get" action="index.php">
		Перейти к: <input type="hidden" name="t" value="cal" /><input type="hidden" name="view" value="'.$view.'" />
		'.$select_month_control.' '.$select_year_control.' 
		<input type="hidden" name="day" value="'.$day.'" /><input type="submit" name="submit" value="Переход" />
		</form>
    </td>
    <td class="ac" width="45%"> 
		<h4><a href="index.php?t=cal&amp;view=m&amp;month='.$month.'&amp;year='.$year.'" class="control">'.$months[$month-1].' '.$year.'</a></h4>
		    '.draw_calendar($year, $month, $events, 'small', $year, $month, $day).'
    </td>
</tr>
' : ''); ?>

</table>
<br /><div class="ac"><span class="curtime"><b>Текущее время:</b> <?php echo strftime("%a %b %#d %H:%M:%S %Z %Y", __request_timestamp__); ?></span></div>
<?php echo $page_stats; ?>
</td>
<!-- <td class="ForumBackground" valign="top"></td> -->
</tr></table>

<div class="ForumBackground ac foot">
<b>.::</b> <a href="mailto:<?php echo $GLOBALS['ADMIN_EMAIL']; ?>">Обратная связь</a> 
<b>::</b> <a href="index.php?t=index&amp;<?php echo _rsid; ?>">Начало</a> 
<b>::</b> <a href="http://www.phpbee.org/">Создание и поддержка сайта www.phpbee.org</a> 

<b>::.</b>
<p>
<span class="SmallText">При поддержке: FUDforum <?php echo $GLOBALS['FORUM_VERSION']; ?>.<br /> Copyright © 2001-2010 <a href="http://fudforum.org/">FUDforum Bulletin Board Software</a></span>
</p>
</div>
</body></html>
