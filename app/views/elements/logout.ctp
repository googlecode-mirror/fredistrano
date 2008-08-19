<?php
$user = $session->read('User');
echo "<center><p>".__('Welcome', true)." ".$user['User']['login']." ";
echo "<br />[ ".$html->link(__('Settings', true), '/users/change_password/'.$user['User']['id'])."  -  ".$html->link(__('Logout', true),'/users/logout')." ]</p></center>";
?>