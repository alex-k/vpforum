<?php
/**
* copyright            : (C) 2001-2010 Advanced Internet Designs Inc.
* email                : forum@prohost.org
* $Id: glob.inc 5030 2010-10-08 18:27:42Z naudefj $
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; version 2 of the License.
**/

/* Read forum settings and descriptions from GLOBALS_HELP. */
function read_help()
{
	$data = file_get_contents($GLOBALS['INCLUDE'] .'GLOBALS_HELP');
	$p = 0;

	while (($p = strpos($data, '{{{', $p)) !== false) {
		$p += 3;
		if (($e = strpos($data, '}}}', $p)) === false) {
			break;
		}
		$t = strpos($data, "\n", $p);
		$tmp = explode("\t", trim(substr($data, $p, ($t - $p))));
		$name = array_shift($tmp);
		if ($tmp) {
			if (isset($tmp[2])) {
				$tmp[1] = substr($tmp[1], 1, -1);
				$tmp[2] = substr($tmp[2], 1, -1);
			}
		} else {
			$tmp = null;
		}

		$help_ar[$name] = array(nl2br(htmlspecialchars(substr($data, ++$t, ($e - $t)))), $tmp);
		$p = $e + 3;
	}

	return (isset($help_ar) ? $help_ar : null);
}

function print_bit_field($descr, $field)
{
	$h =& $GLOBALS['help_ar'][$field];
	$o =& $h[1];

	echo '<tr class="field"><td><label for="'. $o[0] .'_'. $field .'">'. $descr .': <br /><font size="-1">'. $h[0] .'</font></label></td><td valign="top">';

	if (!isset($o[2])) {
		echo create_select($o[0] .'_'. $field, "Yes\nNo", "{$o[1]}\n0", ($GLOBALS[$o[0]] & (int) $o[1]));
	} else {
		$tmp = explode('\n', $o[2]);
		rsort($tmp);
		$val = 0;
		foreach ($tmp as $v) {
			if (($GLOBALS[$o[0]] & (int) $v) == $v) {
				$val = $v;
				break;
			}
		}
		echo create_select($o[0] .'_'. $field, str_replace('\n', "\n", $o[1]), str_replace('\n', "\n", $o[2]), $val);
	}

	echo '</td></tr>';
}

function print_reg_field($descr, $field, $is_int=0, $pass=0)
{
	if (!isset($GLOBALS[$field])) {
		$val = !$is_int ? '' : '0';
	} else {
		$val = !$is_int ? htmlspecialchars($GLOBALS[$field]) : (int)$GLOBALS[$field];
	}
	$extra = ($is_int) ? 'type="number" ' : '';	// HTML5 type spec.
	echo '<tr class="field"><td><label for="CF_'. $field .'">'. $descr .': <br /><font size="-1">'. $GLOBALS['help_ar'][$field][0] .'</font></label></td><td valign="top"><input type="'. (!$pass ? 'text' : 'password') .'" name="CF_'. $field .'" id="CF_'. $field .'" value="'. $val .'" size="30" autocomplete="off" '. $extra .'/></td></tr>';
}

function print_txt_field($descr, $field, $rows=2, $cols=30)
{
	if (!isset($GLOBALS[$field])) {
		$val = '';
	} else {
		$val = htmlspecialchars($GLOBALS[$field]);
	}

	echo '<tr class="field"><td><label for="CF_'. $field .'">'. $descr .': <br /><font size="-1">'. $GLOBALS['help_ar'][$field][0] .'</font></label></td><td valign="top"><textarea rows="'. $rows .'" cols="'. $cols .'" id="CF_'. $field .'" name="CF_'. $field .'">'. $val .'</textarea></td></tr>';
}

/* Read settings from GLOBALS.php without invoking core.inc. */
function read_global_settings()
{
	$settings_path = dirname(__FILE__) .'/GLOBALS.php';
	$settings = file($settings_path);
	$settings = preg_grep('/^\s+\$\w+\s+=\s+.+;/', $settings);      // Filter out variables.
	foreach($settings as $setting) {
		preg_match('/\s+\$(\w+)\s*=\s+(.*);/', $setting, $m);
		eval('$GLOBALS[\''. $m[1] .'\'] = '. $m[2] .';');	// Export them to GLOBAL scope.
	}
}

/* Change forum settings in GLOBALS.php. */
function change_global_settings($list)
{
	$settings = file_get_contents($GLOBALS['INCLUDE'] .'GLOBALS.php');
	foreach ($list as $k => $v) {
		if (($p = strpos($settings, '$'. $k)) === false) {	// New or existing setting?
			$p = strpos($settings, '$ADMIN_EMAIL');			// Insert position for new settings.
			if (is_int($v)) {
				$settings = substr_replace($settings, "\${$k}\t= {$v};\n\t", $p, 0);
			} else {
				$v = str_replace("\n", '<br />', $v);	// Get rid of newlines (they will break this function).
				$v = addcslashes($v, '\\\'');
				$settings = substr_replace($settings, "\${$k}\t= '{$v}';\n\t", $p, 0);
			}
		} else {
			$p = strpos($settings, '=', $p) + 1;
			$e = $p + strrpos(substr($settings, $p, (strpos($settings, "\n", $p) - $p)), ';');

			if (is_int($v)) {
				$settings = substr_replace($settings, ' '. $v, $p, ($e - $p));
			} else {
				$v = addcslashes($v, '\\\'');
				$settings = substr_replace($settings, ' \''. $v .'\'', $p, ($e - $p));
			}
		}
	}

	$fp = fopen($GLOBALS['INCLUDE'] .'GLOBALS.php', 'w');
	fwrite($fp, $settings);
	fclose($fp);
}

/* Enable (status=1) or disable (status=0) maintenace mode. */
function maintenance_status($reason, $status)
{
	if ($status) {
		if ($GLOBALS['FUD_OPT_1'] & 1) {
			change_global_settings(array('FUD_OPT_1' => $GLOBALS['FUD_OPT_1'] ^1, 'DISABLED_REASON' => $reason));
		} else {
			change_global_settings(array('FUD_OPT_1' => $GLOBALS['FUD_OPT_1'], 'DISABLED_REASON' => $reason));
		}
	} else {
		change_global_settings(array('FUD_OPT_1' => $GLOBALS['FUD_OPT_1']|1, 'DISABLED_REASON' => $reason));
	}
}

/* Create symbolic links to the forum's GLOBALS.php file. */
function fud_symlink($src, $dest)
{
	$WINDOWS = DIRECTORY_SEPARATOR != '/';

	/* Ensure we don't have any bogus symlinks (i.e. re-installing over old forum). */
	@unlink($dest);

	/* Older windows systems doesn't have symlinks and some hosts disable them - use crude emulation. */	
	if ($WINDOWS || !function_exists('symlink')) {
		if (!($fp = fopen($dest, 'wb'))) {
			return FALSE;
		}
		fwrite($fp, '<?php include_once \''. $src .'\'; ?>');
		fclose($fp);
	} else {
		symlink($src, $dest);
	}
}

?>
