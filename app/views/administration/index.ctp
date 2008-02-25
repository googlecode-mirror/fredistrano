<h1>Administration</h1>
<p>Use the links provided on this page to configure Fredistrano.</p>
<h2>Authorization management</h2>
<p>To adapt Fredistrano to your needs, simply create new users and add them to the predefined groups (REGULAR, PREMIUM, ADMIN).</p>
<ul>
	<li><?php echo $html->link( __('Users', true),'/users'); ?> - manage users and group memberships</li>
	<li><?php echo $html->link( __('Groups', true),'/groups'); ?> - manage groups (for advanced users)</li>
	<li><?php echo $html->link( __('Authorizations', true),'/aclite/acl_management'); ?> - manage permissions (for advanced users)</li>
	<li><?php echo $html->link( __('Control objects', true),'/control_objects'); ?> - manage control objects (for developers only)</li>
</ul>