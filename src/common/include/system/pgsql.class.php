<?php
/**
 * FusionForge system users integration
 *
 * Copyright 2004, Christian Bayle
 * Copyright 2010, Roland Mas
 *
 * This file is part of FusionForge. FusionForge is free software;
 * you can redistribute it and/or modify it under the terms of the
 * GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the Licence, or (at your option)
 * any later version.
 *
 * FusionForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with FusionForge; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

require_once $gfcommon.'include/System.class.php';

class pgsql extends System {
	/*
 	* Constants
 	*/

	/**
 	* Value to add to unix_uid to get unix uid
 	*
 	* @var	constant	$UID_ADD
 	*/
	var $UID_ADD = 20000;

	/**
 	* Value to add to group_id to get unix gid
 	*
 	* @var	constant	$GID_ADD
 	*/
	var $GID_ADD = 10000;

	/**
 	* Value to add to unix gid to get unix uid of anoncvs special user
 	*
 	* @var	constant	$SCM_UID_ADD
 	*/
	var $SCM_UID_ADD = 50000;

	/**
	* pgsql() - CONSTRUCTOR
	*
	*/
	function pgsql() {
		$this->System();
		return true;
	}

	/**
	* sysUseUnixName() - Check if user/group used the unix_name
	*
	* @param	string	$unix_name	The unix_name to check
	* @return	boolean	true if used/false is free
	*/
	function sysUseUnixName($unix_name) {
		$res1 = db_query_params('SELECT user_id FROM users
								 WHERE user_name=$1',array($unix_name));
		$res2 = db_query_params('SELECT group_id FROM groups
								 WHERE unix_group_name=$1',array($unix_name));
		if ( db_numrows($res1) == 0 && db_numrows($res2) == 0 ){
			return true;
		}
		return false;
	}

	/*
 	* User management functions
 	*/

	/**
 	* sysCheckUser() - Check for the existence of a user
 	*
 	* @param	int	$user_id	The user ID of the user to check
 	* @return	bool			true on success/false on error
 	*
 	*/
	function sysCheckUser($user_id) {
		$user = user_get_object($user_id);
		if (!$user) {
			return false;
		}
		return true;
	}

	/**
 	* sysCreateUser() - Create a user
 	*
 	* @param	int	$user_id	The user ID of the user to create
 	* @return	bool			success or not
 	*
 	*/
	function sysCreateUser($user_id) {
		$user = user_get_object($user_id);
		if (!$user) {
			return false;
		} else {
			$res = db_query_params ('UPDATE users SET
			unix_uid=user_id+$1,
			unix_gid=user_id+$2,
			unix_status=$3
			WHERE user_id=$4',
						array ($this->UID_ADD,
							   $this->UID_ADD,
							   'A',
							   $user_id)) ;
					if (!$res) {
							$this->setError('Error: Cannot Update User UID/GID: '.db_error());
							return false;
			}
			$res1 = db_query_params ('DELETE FROM nss_usergroups WHERE user_id=$1',
						 array ($user_id)) ;
			if (!$res1) {
				$this->setError('Error: Cannot Delete Group Member(s): '.db_error());
				return false;
			}
			// This is group used for user, not a real project
			$res2 = db_query_params ('DELETE FROM nss_groups WHERE name IN
					(SELECT user_name FROM users WHERE user_id=$1)',
						 array ($user_id));
			if (!$res2) {
				$this->setError('Error: Cannot Delete Group GID: '.db_error());
				return false;
			}
			$res3 = db_query_params ('INSERT INTO nss_groups
					(user_id, group_id,name, gid)
					SELECT user_id, 0, user_name, unix_gid
					FROM users WHERE user_id=$1',
						 array ($user_id));
			if (!$res3) {
				$this->setError('Error: Cannot Update Group GID: '.db_error());
				return false;
			}

			$pids = array () ;
			foreach ($user->getGroups() as $p) {
				$pids[] = $p->getID() ;
			}
			foreach ($user->getRoles() as $r) {
				foreach ($r->getLinkedProjects() as $p) {
					if (forge_check_perm_for_user ($user, 'scm', $p->getID(), 'write')) {
						$pids[] = $p->getID() ;
					}
				}
			}
			foreach (array_unique($pids) as $pid) {
				$this->sysGroupAddUser($pid, $user_id) ;
			}
		}
		return true;
	}

	/**
 	* sysCheckCreateUser() - Check that a user has been created
 	*
 	* @param	int		$user_id	The ID of the user to check
 	* @return	bool	true on success/false on error
 	*
 	*/
	function sysCheckCreateUser($user_id) {
		return $this->sysCreateUser($user_id);
	}

	/**
 	* sysCheckCreateGroup() - Check that a group has been created
 	*
 	* @param	int	$user_id	The ID of the user to check
 	* @return	bool			true on success/false on error
 	*
 	*/
	function sysCheckCreateGroup($user_id) {
		return $this->sysCreateGroup($user_id);
	}

	/**
 	* sysRemoveUser() - Remove a user
 	*
 	* @param	int	$user_id	The user ID of the user to remove
 	* @return	bool			true on success/false on failure
 	*
 	*/
	function sysRemoveUser($user_id) {
		$res = db_query_params('UPDATE users SET unix_status=$1 WHERE user_id=$2',
					array ('D',
						   $user_id));
		if (!$res) {
			$this->setError('Error: Cannot Update User Unix Status: '.db_error());
			return false;
		} else {
			$res1 = db_query_params ('DELETE FROM nss_usergroups WHERE user_id=$1',
						 array ($user_id));
			if (!$res1) {
				$this->setError('Error: Cannot Delete Group Member(s): '.db_error());
				return false;
			}
			// This is group used for user, not a real project
			$res2 = db_query_params ('DELETE FROM nss_groups WHERE name IN
				(SELECT user_name FROM users WHERE user_id=$1)',
						 array ($user_id)) ;
			if (!$res2) {
				$this->setError('Error: Cannot Delete Group GID: '.db_error());
				return false;
			}
		}
		return true;
	}

	/**
 	* sysUserSetAttribute() - Set an attribute for a user
 	*
 	* @param	int		$user_id	The user ID
 	* @param	string	$attr		The attribute to set
 	* @param	string	$value		The new value of the attribute
 	* @return	bool				true on success/false on error
 	*
 	*/
	function sysUserSetAttribute($user_id,$attr,$value) {
		return true;
	}

	/*
 	* Group management functions
 	*/

	/**
 	* sysCheckGroup() - Check for the existence of a group
 	*
 	* @param	int		$group_id	The ID of the group to check
 	* @return	bool				true on success/false on error
 	*
 	*/
	function sysCheckGroup($group_id) {
		$group = group_get_object($group_id);
		if (!$group){
			return false;
		} else {
			$res = db_query_params('SELECT group_id FROM nss_groups WHERE group_id=$1',
						array($group_id));
			if (db_numrows($res) == 0){
				return false;
			} else {
				return true;
			}
		}
	}

	/**
 	* sysCreateGroup() - Create a group
 	*
 	* @param		int		$group_id	The ID of the group to create
	* @return bool			true on success/false on error
 	*
 	*/
	function sysCreateGroup($group_id) {
		$group = group_get_object($group_id);
		if (!$group) {
			return false;
		}

		$res1 = db_query_params ('DELETE FROM nss_usergroups WHERE group_id=$1',
					 array ($group_id));
		if (!$res1) {
			$this->setError('Error: Cannot Delete Group Member(s): '.db_error());
			return false;
		}
		$res3 = db_query_params ('DELETE FROM nss_groups WHERE group_id=$1',
					 array ($group_id)) ;
		if (!$res3) {
			$this->setError('Error: Cannot Delete Group GID: '.db_error());
			return false;
		}
		$res4 = db_query_params ('INSERT INTO nss_groups
					(user_id, group_id, name, gid)
						SELECT 0, group_id, unix_group_name, group_id + $1
					FROM groups
					WHERE group_id=$2',
					 array ($this->GID_ADD,
						$group_id)) ;
		if (!$res4) {
			$this->setError('Error: Cannot Insert Group GID: '.db_error());
			return false;
		}
		$res5 = db_query_params ('INSERT INTO nss_groups
					(user_id, group_id, name, gid)
						SELECT 0, group_id, $1 || unix_group_name, group_id + $2
					FROM groups
					WHERE group_id=$3',
					 array ('scm_',
						$this->SCM_UID_ADD,
						$group_id)) ;

		if (!$res5) {
			$this->setError('Error: Cannot Insert SCM Group GID: '.db_error());
			return false;
		}

		foreach ($group->getUsers(false) as $u) {
			$this->sysGroupAddUser ($group_id, $u->getID()) ;
		}

		return true;
	}

	/**
 	* sysRemoveGroup() - Remove a group
 	*
 	* @param		int		$group_id	The ID of the group to remove
	* @return bool			true on success/false on error
 	*
 	*/
	function sysRemoveGroup($group_id) {
		$res1 = db_query_params ('DELETE FROM nss_usergroups WHERE group_id=$1',
					 array ($group_id)) ;
		if (!$res1) {
			$this->setError('Error: Cannot Delete Group Member(s): '.db_error());
			return false;
		}
		$res3 = db_query_params ('DELETE FROM nss_groups WHERE group_id=$1',
					 array ($group_id)) ;
		if (!$res3) {
			$this->setError('Error: Cannot Delete Group GID: '.db_error());
			return false;
		}
		return true;
	}

 	/**
	 * sysGroupAddUser() - Add a user to a group
	 *
	 * @param	int		$group_id	The ID of the group two which the user will be added
	 * @param	int		$user_id	The ID of the user to add
	 * @param	bool	$foo		ignored
	 * @return	bool				true on success/false on error
	 */
	function sysGroupAddUser($group_id,$user_id,$foo=NULL) {
		return $this->sysGroupCheckUser($group_id,$user_id) ;
	}

	/**
 	* sysGroupCheckUser() - Sync user's Unix permissions with their FF permissions within a group
 	*
 	* @param	int	$group_id	The ID of the group
 	* @param	int	$user_id	The ID of the user
 	* @return	bool			true on success/false on error
 	*/
	function sysGroupCheckUser($group_id,$user_id) {
		db_begin () ;
		if (! $this->sysGroupRemoveUser($group_id,$user_id)) {
			db_rollback () ;
			return false;
		}

		$u = user_get_object($user_id) ;
		$p = group_get_object($group_id) ;
		if (forge_check_perm_for_user($u,'scm',$group_id,'write')) {
			$res = db_query_params ('INSERT INTO nss_usergroups (
SELECT users.unix_uid AS uid,
	   groups.group_id + $1 AS gid,
	   users.user_id AS user_id,
	   groups.group_id AS group_id,
	   users.user_name AS user_name,
	   $2 || groups.unix_group_name AS unix_group_name
FROM users,groups
WHERE users.user_id=$3
  AND users.status=$4
  AND users.unix_status=$5
  AND groups.status=$6
  AND groups.group_id=$7)',
						array($this->SCM_UID_ADD,
							  'scm_',
							  $user_id,
							  'A', 'A', 'A',
							  $group_id));
			if (!$res) {
				db_rollback();
				$this->setError('Error: Cannot Update Group Member(s): '.db_error());
				return false;
			}
		}

		if ($u->isMember($p)) {
			$res = db_query_params ('INSERT INTO nss_usergroups (
SELECT users.unix_uid AS uid,
	   groups.group_id + $1 AS gid,
	   users.user_id AS user_id,
	   groups.group_id AS group_id,
	   users.user_name AS user_name,
	   groups.unix_group_name AS unix_group_name
FROM users,groups
WHERE users.user_id=$2
  AND users.status=$3
  AND users.unix_status=$4
  AND groups.status=$5
  AND groups.group_id=$6)',
						array ($this->GID_ADD,
							   $user_id,
							   'A', 'A', 'A',
							   $group_id)) ;
			if (!$res) {
				$this->setError('Error: Cannot Update Group Member(s): '.db_error());
				db_rollback () ;
				return false;
			}
		}

		db_commit () ;
		return true;
	}

	/**
 	* sysGroupRemoveUser() - Remove a user from a group
 	*
 	* @param	int		$group_id	The ID of the group from which to remove the user
 	* @param	int		$user_id	The ID of the user to remove
	* @param	bool	$unused		Compatibility issue but not used : pgsql extends System...
 	* @return	bool	true on success/false on error
 	*/
	function sysGroupRemoveUser($group_id, $user_id, $unused = false) {
		$res = db_query_params ('DELETE FROM nss_usergroups WHERE user_id=$1 AND group_id=$2',
					 array ($user_id,
						$group_id)) ;
		if (!$res) {
			$this->setError('Error: Cannot Delete Group Member(s): '.db_error());
			return false;
		}
		return true;
	}
}

// Local Variables:
// mode: php
// c-file-style: "bsd"
// End:
