<?php
/**
  *
  * Change user's SSH authorized keys
  *
  * SourceForge: Breaking Down the Barriers to Open Source Development
  * Copyright 1999-2001 (c) VA Linux Systems
  * http://sourceforge.net
  *
  * @version   $Id$
  *
  */

require_once('pre.php');
require_once('common/include/account.php');

session_require(array('isloggedin'=>1));
$u =& user_get_object(user_getid());
exit_assert_object($u, 'User');

if ($submit) {
	if (!$u->setAuthorizedKeys($authorized_keys)) {
		exit_error(
			'Error',
			'Could not update SSH authorized keys: '.db_error()
		);
	}
	session_redirect("/account/");

} else { 
	// not valid registration, or first time to page
	site_user_header(array(title=>"Change Authorized Keys",'pagename'=>'account_editsshkeys'));

	?>

	<P>
	To avoid having to type your password every time for your CVS/SSH
	developer account, you may upload your public key(s) here and they
	will be placed on the CVS server in your ~/.ssh/authorized_keys file.
	<p>
	To generate a public key, run the program 'ssh-keygen' (or ssh-keygen1).
	The public key will be placed at '~/.ssh/identity.pub'. Read the ssh
	documentation for further information on sharing keys.
	</p>

<form action="<?php echo $PHP_SELF; ?>" method="POST">
<p>Authorized keys:
<BR><I>Important: Make sure there are no line breaks except between keys.
After submitting, verify that the number of keys in your file is what you expected.</I>
<br>
<TEXTAREA rows=10 cols=60 name="authorized_keys">
<?php echo $u->getAuthorizedKeys(); ?>
</TEXTAREA>
<p><input type="submit" name="submit" value="Update">
</form>

	<?php
}
site_user_footer(array());

?>
