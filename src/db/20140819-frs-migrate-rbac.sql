-- migrate RBAC FRS settings
CREATE OR REPLACE FUNCTION FRSmigrateRBAC() RETURNS int4 AS '
DECLARE r RECORD;
DECLARE s RECORD;
DECLARE t RECORD;
DECLARE u RECORD;

BEGIN
	FOR r IN select pfo_role_setting.role_id as roleid, pfo_role_setting.ref_id as refid, pfo_role_setting.perm_val as permval from pfo_role_setting where pfo_role_setting.section_name = ''frs'' LOOP
		CASE r.permval
			WHEN 0 THEN
				update pfo_role_setting set perm_val = 0, section_name = ''new_frs'' where section_name = ''frs'' and role_id = r.roleid and ref_id = r.refid;
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs_admin'', r.refid, 0);
				FOR s IN select frs_package.package_id as packid from frs_package where frs_package.group_id = r.refid LOOP
					insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs'', s.packid, 0);
				END LOOP;
			WHEN 1, 2 THEN
				update pfo_role_setting set perm_val = 1, section_name = ''new_frs'' where section_name = ''frs'' and role_id = r.roleid and ref_id = r.refid;
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs_admin'', r.refid, 1);
				FOR t IN select frs_package.package_id as packid from frs_package where frs_package.group_id = r.refid LOOP
					insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs'', t.packid, 1);
				END LOOP;
			WHEN 3 THEN
				update pfo_role_setting set perm_val = 2, section_name = ''new_frs'' where section_name = ''frs'' and role_id = r.roleid and ref_id = r.refid;
				insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs_admin'', r.refid, 2);
				FOR u IN select frs_package.package_id as packid from frs_package where frs_package.group_id = r.refid LOOP
					insert into pfo_role_setting (role_id, section_name, ref_id, perm_val) values (r.roleid, ''frs'', u.packid, 4);
				END LOOP;
		END CASE;
	END LOOP;
	return 1;
END;
' LANGUAGE plpgsql;

SELECT FRSmigrateRBAC() as output;
