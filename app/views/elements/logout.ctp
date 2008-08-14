<?php
$user = $session->read('User');
echo "<center><p>".__('Welcome', true)." ".$user['User']['login']." ";
echo "<br />[ ".$html->link('Settings', '/users/change_password/'.$user['User']['id'])."  -  ".$html->link('Logout','/users/logout')." ]</p></center>";
?>