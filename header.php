<?
srand(time());
function Banner($num)
 {  
   $seed=rand(0,10000000);
   print "<iframe src=http://10e2.linkexchange.ru/cgi-bin/erle.cgi?49449-bn=$num?$seed frameborder=0 vspace=0 hspace=0 width=100 height=100 marginwidth=0 marginheight=0 scrolling=no>";
   print "<a href=http://10e2.linkexchange.ru/users/49449/goto.map?bn=$num target=_top>";
   print "<img src=http://10e2.linkexchange.ru/cgi-bin/rle.cgi?49449-bn=$num?$seed alt=\"RLE Banner Network\" border=0 height=100 width=100></a></iframe>";
 }

function PrintHeader($dn)
 {
   global $userID,$printFunctions,$db; 

   $linksDir=array("club","trips","forums","equip","haggle","tech","misc","activeinfo");
   $hl=array("","","","","","","");
   $hl[$dn]='s';
?>

<script>
//<!--
var dm="ВелоПитер - виртуальный велосипедный клуб"
window.defaultStatus=dm
function tip(m) {window.status=m}
function off() {window.status=dm}
//-->
</script>
<style type="text/css">
td {font-family: arial, helvetica, sans-serif;}
input {font-size: 10pt}
textarea {font-size: 10pt}
a {text-decoration: none; font-weight: bold; color: #333399}
a:hover {text-decoration: underline;}
.wh {color: #FFFFFF}
.hd {color: #333399}
.hl a {color: #FFFFFF; font-size: 9pt}
.hp {font-size: 9pt}
.hp a {color: #FFFFFF}
.hp a:hover {color: #FFFF66}
</style>

<center>
<table width=100% height=100 cellspacing=0 cellpadding=0>
<tr><td>
<table background="/hb.gif" width=100% border=0 cellspacing=0 cellpadding=0 height=100>
<tr><td nowrap width=456><a href="/"><img src="/logo.jpg" border=0 width=456 height=100 alt="ВелоПитер - На главную"></a></td>
<td align=right nowrap>
<img src="/s.gif" border="0" width="3" height="100"><a href="http://velopiter.spb.ru/activeinfo/info.php?fid=56&c=1"><img src="/optima.gif" width=200 height=100 border=0 alt="Старк в Питере"></a>
<img src="/s.gif" border="0" width="3" height="100"><a href="http://www.sportnika.ru"><img src="/sportnika.gif" width=100 height=100 border=0 alt="Магазин СпортНика"></a>

</td></tr>
</table></td></tr>
<tr><td height=2></td></tr>
<tr><td bgcolor=#EEEEEE height=19>
<table width=756 border=0 cellspacing=0 cellpadding=0 height=19>
<tr valign=middle>
<td class=hl background="/p<?=$hl[0]?>.gif" width=108 nowrap align=center><a href="/sys/viewboard.php">КЛУБ</a></td>
<td class=hl background="/p<?=$hl[1]?>.gif" width=108 nowrap align=center><a href="/sys/viewboard.php?board=1">ПОХОДЫ</a></td>
<td class=hl background="/p<?=$hl[2]?>.gif" width=108 nowrap align=center><a href="http://velopiter.spb.ru/forum/">ФОРУМЫ</a></td>
<td class=hl background="/p<?=$hl[3]?>.gif" width=108 nowrap align=center><a href="/equip/velobags.htm">СНАРЯЖЕНИЕ</a></td>
<td class=hl background="/p<?=$hl[4]?>.gif" width=108 nowrap align=center><a href="/drive/training.htm">ДРАЙВ</a></td>
<td class=hl background="/p<?=$hl[5]?>.gif" width=108 nowrap align=center><a href="/tech/repair.php">ВЕЛОТЕХНИКА</a></td>
<td class=hl background="/p<?=$hl[6]?>.gif" width=108 nowrap align=center><a href="/misc/life.php">РАЗНОЕ</a></td>
</tr></table>
</td></tr>
<tr bgcolor=#336699><td>
<table width=100% cellspacing=0 cellpadding=4>
<tr><td colspan=3 height=1><img src="/s.gif" border=0 width=1 height=1></td></tr>
<tr><td class=hp nowrap valign=top>
<?
if($dn!=7)
 {
   virtual("/$linksDir[$dn]/links.htm");
   if($printFunctions)
    {
      print "<hr><a href=\"/sys/login.php?action=main\">Для членов клуба</a><br>";
      print "<a href=\"/sys/".S_LOGOUT."\">Выход</a>";
    }
 }
else
 { 
   $members=1;
 ?>
<font class=wh>
<a href="/activeinfo/">Новости магазинов</a> / <a href="/shops/">Каталог</a><br>
<hr>
Участники:<br>
<table width=100% border=0 cellpadding=2 cellspacing=1 nowrap>
<?
$curdate=date("Y-m-d");
$db->Query("SELECT u.id,u.name FROM ai_users AS u,ai_usercat AS uc,ai_category AS c WHERE (uc.category=$members AND u.id=uc.uid AND c.id=$members AND c.flag AND !c.disable) AND u.ok AND !u.nocatalog AND u.id!=1 AND u.endtime>'$curdate' ORDER BY u.pos DESC,u.addtime");
$num=$db->NumRows();
for($i=0; $i<$num; $i++)
 {
   $f=$db->FetchArray();
   print "<tr><td valign=top><img src=\"/arrow.gif\" width=7 height=13 border=0></td><td class=hp valign=middle nowrap><a href=\"/activeinfo/info.php?fid=".$f['id']."&c=$members\">".$f['name']."</a></td></tr>\n";
 }
$db->free();
?>
</table>
<hr>
<a href="/activeinfo/sys/login.php">Вход для участников</a><br>
<a href="/activeinfo/member.php">Стать участником</a>
</font>
<? 
 }
?>
<br><br><center>
<br><img src="/s.gif" border=0 height=5><br>
<a href="http://gpepper.ru"><img src="/gp.gif" width=100 height=100 border=0 alt="Магазин Горький Перец"></a>
<br><br>
<iframe src="http://b.traf.spb.ru/b/b176448702.php" width=100 height=200 marginwidth=0 marginheight=0 scrolling=no frameborder=0></iframe><br><br>

<a href="http://activeinfo.ru/"><img src="http://activeinfo.ru/ai.gif" width=88 height=31 border=0 alt="ActivEInfo - каталог магазинов и свежая информация о товарах и новинках для активного отдыха"></a>
</td><td valign=top width=100% rowspan=2 bgcolor=#FFFFFF>
<table align=center width=99% cellpadding=6><tr><td><font size=2>
<?
 }
?>

