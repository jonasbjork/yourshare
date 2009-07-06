<?php
/**
	Your Share - a Drupal module that shows members contribution ratio on a Drupal site
	Written by Jonas Björk <jonas@jonasbjork.net> 2009-07-03
	
	Licensed under European Union Public License (EUPL) version 1.1
	that can be found at http://ec.europa.eu/idabc/eupl 
	
*/
include_once './includes/bootstrap.inc';
drupal_bootstrap(DRUPAL_BOOTSTRAP_FULL);

function tot_nodes() {
        $r = db_query("SELECT COUNT(nid) AS total FROM node");
        $o = db_fetch_object($r);
        return $o->total;
}

function tot_comments() {
        $r = db_query("SELECT COUNT(cid) AS total FROM comments");
        $o = db_fetch_object($r);
        return $o->total;
}

function get_username($uid) {
        $r = db_query("SELECT name FROM users WHERE uid='%d' LIMIT 1", $uid);
        $o = db_fetch_object($r);
        return $o->name;
}

function get_user_nodes($uid) {
        $r = db_query("SELECT COUNT(nid) AS usernodes FROM node WHERE uid='%d'", $uid);
        $o = db_fetch_object($r);
        return $o->usernodes;
}

function get_user_comments($uid) {
        $r = db_query("SELECT COUNT(cid) AS usercomments FROM comments WHERE uid='%d'", $uid);
        $o = db_fetch_object($r);
        return $o->usercomments;
}

function avrunda($t, $d = 2) {
        $a = pow(10, $d);
        return round($t*$a)/$a;
}

// mysql> select count(cid) FROM comments WHERE length(comment) >= 1000;
// mysql> select count(cid) FROM comments WHERE uid=4 and length(comment) >= 1000;

// mysql> select count(*) from node_revisions;
// mysql> select count(*) from node_revisions where length(body) > 1000;

// räkna ut hur många veckor en användare varit medlem
// mysql> select name, (datediff(now(),from_unixtime(created))/7)/10 as weekpnt from users where uid=4;

// mysql> select uid from users where length(signature) >20;

// hur många publiceringar är på framsidan?
//mysql> select count(promote) from node where promote=1 and uid=4;

// dela dessa med 100 för poäng
//mysql> select count(nid) from node where type='blog' and uid=4;
//mysql> select count(nid) from node where type='forum' and uid=4;
// mysql> select count(nid) from node where type='story' and uid=4;





$tot_nodes = tot_nodes();
$tot_comments = tot_comments();

echo "<h1>Your share</h1>";
echo "<p><strong>Totalt p&aring; webbplatsen</strong></p>";
echo "<p>Antal noder: ".$tot_nodes."<br />Antal kommentarer: ".$tot_comments."</p>";

echo "<p>Visa information om en anv&auml;ndare, skriv anv&auml;ndarnamnet h&auml;r:</p>";
echo "<form method='post' action='yourshare.php'>\n";
echo "<input type='text' name='username' />\n";
echo "<input type='submit' value='Visa' name='subUserName' />\n";
echo "</form>\n";

if(isset($_POST['subUserName'])) {
        $username = $_POST['username'];
        $r = db_query("SELECT uid FROM users WHERE name='%s' LIMIT 1", $username);
        $o = db_fetch_object($r);
        $uid = $o->uid;
}
if(isset($_GET['uid'])) {
        $uid = $_GET['uid'];
}
if(!is_numeric($uid)) {
        exit();
}


$usernodes = get_user_nodes($uid);
$usercomments = get_user_comments($uid);


echo "<h2>".get_username($uid)."</h2>";
echo "<p>Antal kommentarer: ".avrunda(($usercomments/$tot_comments)*100)."%";
echo "<br />Antal noder: ".avrunda(($usernodes/$tot_nodes)*100)."%</p>";

echo "<h2>Tio i topp - noder</h2>";
$r = db_query("select node.uid,count(node.nid) AS antal, users.name AS username FROM node INNER JOIN users ON node.uid=users.uid WHERE node.uid != 0 GROUP BY uid ORDER BY antal DESC LIMIT 10");

while($o = db_fetch_object($r)) {
if($o->uid == $uid) {
echo "<div style='background: yellow'>";
}
        echo $o->username." : ".$o->antal." noder (".avrunda(($o->antal/$tot_nodes)*100)."%)<br />";
if($o->uid == $uid) {
echo "</div>";
}

}

echo "<h2>Tio i topp - kommentarer</h2>";
$r = db_query("select comments.uid,count(comments.cid) AS antal, users.name AS username FROM comments INNER JOIN users ON comments.uid=users.uid WHERE comments.uid != 0 GROUP BY uid ORDER BY antal DESC LIMIT 10");

while($o = db_fetch_object($r)) {
if($o->uid == $uid) {
echo "<div style='background: yellow'>";
}

        echo $o->username." : ".$o->antal." kommentarer (".avrunda(($o->antal/$tot_comments)*100)."%)<br />";
if($o->uid == $uid) {
echo "</div>";
}

}
