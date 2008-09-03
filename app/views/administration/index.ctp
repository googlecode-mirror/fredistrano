<h1><?php __('Administration') ?></h1>
<p><?php __('Use the links provided on this page to configure Fredistrano.') ?></p>

<h2><?php __('Authorization management') ?></h2>
<p><?php __('To adapt Fredistrano to your needs, simply create new users and add them to the predefined groups (REGULAR, PREMIUM, ADMIN).') ?></p>
<ul>
	<li><?php echo $html->link( __('Users', true),'/users'); ?> - <?php __('manage users and group memberships') ?></li>
	<li><?php echo $html->link( __('Groups', true),'/groups'); ?> - <?php __('manage groups (for advanced users)') ?></li>
	<li><?php echo $html->link( __('Authorizations', true),'/aclite/acl_management'); ?> - <?php __('manage permissions (for advanced users)') ?></li>
	<li><?php echo $html->link( __('Control objects', true),'/control_objects'); ?> - <?php __('manage control objects (for developers only)') ?></li>
</ul>
<!--
	TODO F:implement configuration management
-->
<!--

<h2><?php __('Application configuration') ?></h2>
<ul>
	<li><?php echo $html->link( __('Modify configuration', true), '/configurations'); ?> - <?php __('Not Yet Implemented') ?></li>
	<li><?php echo $html->link( __('Import ', true), '/administration/import').' / '.$html->link( __('Export', true), '/administration/export').' configuration'; ?> - <?php __('Not Yet Implemented') ?></li>
	<li><?php echo $html->link( __('Reset RSS token', true), '/administration/resetRssToken'); ?> - <?php __('Not Yet Implemented') ?></li>
</ul>
-->