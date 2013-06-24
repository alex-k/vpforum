<?php

include_once 'GLOBALS.php';
include_once 'scripts/fudapi.inc.php';


$func=key($_REQUEST);
$arg=current($_REQUEST);


if (in_array($func,array('fud_fetch_cats','fud_fetch_cat','fud_fetch_cat_forums','fud_fetch_cat_forums2'))) {
		print_result(call_user_func($func,$arg));
}

$req=trim($_SERVER['PATH_INFO'],'/');

list($req,$arg)=explode('/',$req,2);


if ($_SERVER['REQUEST_METHOD']=='GET')  {
	switch ($req) {
		case 'groups':
				print_result(fud_fetch_cats());
			break;
		case 'forum':
				print_result(fud_fetch_forums());
			break;
		case 'forums':
				print_result(fud_fetch_forums());
			break;
		case 'topicmessages':
				print_result(array_map('get_object_vars',fud_fetch_full_topic($arg)));
			break;
		case 'topics':
				print_result(fud_fetch_forum_topics($arg));
			break;
		case 'message':
				print_result(fud_fetch_msg2($arg));
			break;
		case 'user':
				print_result(fud_fetch_user2($arg));
			break;
	}
}


function print_result($res) {
	/*
    if (is_array($res)) {
        $res=array_map('get_object_vars',$res);
    }
	*/
    $jsstr=json_encode($res);
    if (isset($_GET['D'])) {
        echo "<pre>";
        var_dump($res);
    } else {
		header('Content-type: application/json');
		//header('Content-length: '.strlen($jsstr));

	}
	echo $jsstr;
	die();
}

function fud_fetch_cats() {
    $cats = array();
    foreach (_fud_simple_fetch_query(0, "SELECT id,name,view_order FROM {$GLOBALS['DBHOST_TBL_PREFIX']}cat order by view_order") as $c) {
        $cats[]=$c;
    }
    return $cats;
}
function fud_fetch_forums() {
    $cats = array();
    foreach (_fud_simple_fetch_query(0, "SELECT id,name,cat_id,view_order FROM {$GLOBALS['DBHOST_TBL_PREFIX']}forum where cat_id>0 order by cat_id,view_order") as $c) {
        $cats[]=$c;
    }
    return $cats;
}
function fud_fetch_cat_forums2($id) {
    $cats = array();
	$que=sprintf("SELECT * FROM {$GLOBALS['DBHOST_TBL_PREFIX']}forum where cat_id=%d",$id);
    foreach (_fud_simple_fetch_query(0, $que) as $c) {
        $cats[$c->id]=$c;
    }
    return $cats;
}
function fud_get_current_user() {
    $cookie = $_COOKIE[ $GLOBALS['COOKIE_NAME'] ] or 0;
    $ses = _fud_simple_fetch_query(0, "SELECT * FROM {$GLOBALS['DBHOST_TBL_PREFIX']}ses WHERE ses_id = '$cookie'");
    $user=fud_fetch_user($ses->user_id);
    return $user;
}
function fud_fetch_forum_topics($arg)
{

	$ret=array();
	$arg=intval($arg);
	$topics = _fud_simple_fetch_query(0, "SELECT id FROM {$GLOBALS['DBHOST_TBL_PREFIX']}thread WHERE forum_id=$arg  order by last_post_date desc limit 10 ");
	foreach ($topics as $t) {
		$topic=fud_fetch_topic($t->id);
		$topic->forum_id=$arg;
		$msg=fud_fetch_msg($topic->root_msg_id);
		//var_dump(array_keys(get_object_vars($msg)));
		//$topic=array_merge(get_object_vars($msg),get_object_vars($topic));
		$topic->root_login=$msg->login;
		$topic->root_post_stamp=$msg->post_stamp;
		$ret[]=$topic;
	}
	return array_map('get_object_vars',$ret);
	return $ret;
}
function fud_fetch_user2($arg) {
		$user=fud_fetch_user($arg);
		unset($user->passwd);
		unset($user->email);
		return $user;
}
function fud_fetch_msg2($arg) {
	$msg=fud_fetch_msg($arg);
	//$msg->user=fud_fetch_user2($msg->poster_id);
	return $msg;
}
