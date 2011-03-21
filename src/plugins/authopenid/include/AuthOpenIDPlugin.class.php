<?php
/** External authentication via OpenID for FusionForge
 * Copyright 2011, Roland Mas
 * Copyright 2011, Olivier Berger & Institut Telecom
 *
 * This program was developped in the frame of the COCLICO project
 * (http://www.coclico-project.org/) with financial support of the Paris
 * Region council.
 *
 * This file is part of FusionForge
 *
 * This plugin, like FusionForge, is free software; you can redistribute it
 * and/or modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2 of
 * the License, or (at your option) any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with FusionForge; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  US
 * 
 */

require_once $GLOBALS['gfcommon'].'include/User.class.php';

// from lightopenid (http://code.google.com/p/lightopenid/)
//require_once 'openid.php';

/**
 * Authentication manager for FusionForge CASification
 *
 */
class AuthOpenIDPlugin extends ForgeAuthPlugin {
	var $openid;
	
	function AuthOpenIDPlugin () {
		global $gfconfig;
		$this->ForgeAuthPlugin() ;
		$this->name = "authopenid";
		$this->text = "OpenID authentication";

		$this->_addHook('display_auth_form');
		$this->_addHook("check_auth_session");
		$this->_addHook("fetch_authenticated_user");
		$this->_addHook("close_auth_session");

		$this->saved_login = '';
		$this->saved_user = NULL;

		//$this->openid = new LightOpenID;
		$this->openid = FALSE;
			
		$this->declareConfigVars();
	}

	/*
	private static $init = false;

	function initCAS() {
		if (self::$init) {
			return;
		}

		phpCAS::client(forge_get_config('cas_version', $this->name),
			       forge_get_config('cas_server', $this->name),
			       intval(forge_get_config('cas_port', $this->name)),
			       '');
		if (forge_get_config('validate_server_certificate', $this->name)) {
			// TODO
		} else {
			phpCAS::setNoCasServerValidation();
		}

		self::$init = true;
	}
*/
	/**
	 * Display a form to input credentials
	 * @param unknown_type $params
	 * @return boolean
	 */
	function displayAuthForm(&$params) {
		if (!$this->isRequired() && !$this->isSufficient()) {
			return true;
		}
		$return_to = $params['return_to'];

		//$this->initCAS();

		$result = '';

		$result .= '<p>';
		$result .= _('Cookies must be enabled past this point.');
		$result .= '</p>';
		
		$result .= '<form action="' . util_make_url('/plugins/authopenid/post-login.php') . '" method="post">
<input type="hidden" name="form_key" value="' . form_generate_key() . '"/>
<input type="hidden" name="return_to" value="' . htmlspecialchars(stripslashes($return_to)) . '" />
Your OpenID identifier: <input type="text" name="openid_identifier" /> 
<input type="submit" name="login" value="' . _('Login via OpenID') . '" />
</form>';
/*
		$result .= '<form action="' . util_make_url('/plugins/authcas/post-login.php') . '" method="get">
<input type="hidden" name="form_key" value="' . form_generate_key() . '"/>
<input type="hidden" name="return_to" value="' . htmlspecialchars(stripslashes($return_to)) . '" />
<p><input type="submit" name="login" value="' . _('Login via CAS') . '" />
</p>
</form>' ;
*/
		$params['html_snippets'][$this->name] = $result;

		//$params['transparent_redirect_urls'][$this->name] = util_make_url('/plugins/authcas/post-login.php?return_to='.htmlspecialchars(stripslashes($return_to)).'&login=1');
	}

    /**
	 * Is there a valid session?
	 * @param unknown_type $params
	 */
	/*
	function checkAuthSession(&$params) {
		$this->initCAS();

		$this->saved_user = NULL;
		$user = NULL;

		$user_id_from_cookie = $this->checkSessionCookie();
		if ($user_id_from_cookie) {
			$user = user_get_object($user_id_from_cookie);
			$this->saved_user = $user;
			$this->setSessionCookie();
		} elseif (phpCAS::isAuthenticated()) {
			$user = $this->startSession(phpCAS::getUser());
		}
		
		if ($user) {
			if ($this->isSufficient()) {
				$this->saved_user = $user;
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_ACCEPT;
				
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		} else {
			if ($this->isRequired()) {
				$params['results'][$this->name] = FORGE_AUTH_AUTHORITATIVE_REJECT;
			} else {
				$params['results'][$this->name] = FORGE_AUTH_NOT_AUTHORITATIVE;
			}
		}
	}
*/
	/**
	 * What GFUser is logged in?
	 * @param unknown_type $params
	 */
	/*
	function fetchAuthUser(&$params) {
		if ($this->saved_user && $this->isSufficient()) {
			$params['results'] = $this->saved_user;
		}
	}

	function closeAuthSession($params) {
		$this->initCAS();

		if ($this->isSufficient() || $this->isRequired()) {
			$this->unsetSessionCookie();
			// logs user out from CAS
			// TODO : make it optional to not mess with other apps' SSO sessions with CAS
			phpCAS::logoutWithRedirectService(util_make_url('/'));
		} else {
			return true;
		}
	}
*/
	/**
	 * Terminate an authentication session
	 * @param unknown_type $params
	 * @return boolean
	 */
	protected function declareConfigVars() {
		parent::declareConfigVars();
/*
		forge_define_config_item ('cas_server', $this->name, 'cas.example.com');
		forge_define_config_item ('cas_port', $this->name, 443);
		forge_define_config_item ('cas_version', $this->name, '2.0');

		forge_define_config_item('validate_server_certificate', $this->name, 'no');
		forge_set_config_item_bool('validate_server_certificate', $this->name);
		*/
	}

}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:

?>
