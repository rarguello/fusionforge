/* soapServer.cpp
   Generated by gSOAP 2.3 rev 1 from SoapAPI.h
   Copyright (C) 2001-2003 Genivia inc.
   All Rights Reserved.
*/
#include "soapH.h"

SOAP_BEGIN_NAMESPACE(soap)

SOAP_SOURCE_STAMP("@(#) soapServer.cpp ver 2.3 rev 1 2003-08-03 13:30:10 GMT")


SOAP_FMAC5 int SOAP_FMAC6 soap_serve(struct soap *soap)
{
	unsigned int n = SOAP_MAXKEEPALIVE;
	do
	{	soap_begin(soap);
		if (!--n)
			soap->keep_alive = 0;
		if (soap_begin_recv(soap))
		{	if (soap->error < SOAP_STOP)
				return soap_send_fault(soap);
			else
				continue;
		}
		if (soap_envelope_begin_in(soap) || soap_recv_header(soap) || soap_body_begin_in(soap))
			return soap_send_fault(soap);
		soap->error = soap_serve_tns__user(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__logout(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__hello(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__getNumberOfActiveUsers(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__bugList(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__bugUpdate(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__group(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__getPublicProjectNames(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__getSiteStats(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__login(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__bugAdd(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__getNumberOfHostedProjects(soap);
		if (soap->error == SOAP_NO_METHOD)
			soap_serve_tns__bugFetch(soap);
		if (soap->error)
			return soap_send_fault(soap);
	} while (soap->keep_alive);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__user(struct soap *soap)
{	struct tns__user soap_tmp_tns__user;
	tns__userResponse out;
	out.soap_default(soap);
	soap_default_tns__user(soap, &soap_tmp_tns__user);
	soap_get_tns__user(soap, &soap_tmp_tns__user, "tns:user", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__user(soap, soap_tmp_tns__user.func, soap_tmp_tns__user.params, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:userResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:userResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__logout(struct soap *soap)
{	struct tns__logout soap_tmp_tns__logout;
	tns__logoutResponse out;
	out.soap_default(soap);
	soap_default_tns__logout(soap, &soap_tmp_tns__logout);
	soap_get_tns__logout(soap, &soap_tmp_tns__logout, "tns:logout", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__logout(soap, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:logoutResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:logoutResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__hello(struct soap *soap)
{	struct tns__hello soap_tmp_tns__hello;
	tns__helloResponse out;
	out.soap_default(soap);
	soap_default_tns__hello(soap, &soap_tmp_tns__hello);
	soap_get_tns__hello(soap, &soap_tmp_tns__hello, "tns:hello", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__hello(soap, soap_tmp_tns__hello.parm, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:helloResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:helloResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__getNumberOfActiveUsers(struct soap *soap)
{	struct tns__getNumberOfActiveUsers soap_tmp_tns__getNumberOfActiveUsers;
	tns__getNumberOfActiveUsersResponse out;
	out.soap_default(soap);
	soap_default_tns__getNumberOfActiveUsers(soap, &soap_tmp_tns__getNumberOfActiveUsers);
	soap_get_tns__getNumberOfActiveUsers(soap, &soap_tmp_tns__getNumberOfActiveUsers, "tns:getNumberOfActiveUsers", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__getNumberOfActiveUsers(soap, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:getNumberOfActiveUsersResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:getNumberOfActiveUsersResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__bugList(struct soap *soap)
{	struct tns__bugList soap_tmp_tns__bugList;
	tns__bugListResponse out;
	out.soap_default(soap);
	soap_default_tns__bugList(soap, &soap_tmp_tns__bugList);
	soap_get_tns__bugList(soap, &soap_tmp_tns__bugList, "tns:bugList", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__bugList(soap, soap_tmp_tns__bugList.sessionkey, soap_tmp_tns__bugList.project, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:bugListResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:bugListResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__bugUpdate(struct soap *soap)
{	struct tns__bugUpdate soap_tmp_tns__bugUpdate;
	tns__bugUpdateResponse out;
	out.soap_default(soap);
	soap_default_tns__bugUpdate(soap, &soap_tmp_tns__bugUpdate);
	soap_get_tns__bugUpdate(soap, &soap_tmp_tns__bugUpdate, "tns:bugUpdate", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__bugUpdate(soap, soap_tmp_tns__bugUpdate.sessionkey, soap_tmp_tns__bugUpdate.project, soap_tmp_tns__bugUpdate.bugid, soap_tmp_tns__bugUpdate.comment, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:bugUpdateResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:bugUpdateResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__group(struct soap *soap)
{	struct tns__group soap_tmp_tns__group;
	tns__groupResponse out;
	out.soap_default(soap);
	soap_default_tns__group(soap, &soap_tmp_tns__group);
	soap_get_tns__group(soap, &soap_tmp_tns__group, "tns:group", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__group(soap, soap_tmp_tns__group.func, soap_tmp_tns__group.params, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:groupResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:groupResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__getPublicProjectNames(struct soap *soap)
{	struct tns__getPublicProjectNames soap_tmp_tns__getPublicProjectNames;
	tns__getPublicProjectNamesResponse out;
	out.soap_default(soap);
	soap_default_tns__getPublicProjectNames(soap, &soap_tmp_tns__getPublicProjectNames);
	soap_get_tns__getPublicProjectNames(soap, &soap_tmp_tns__getPublicProjectNames, "tns:getPublicProjectNames", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__getPublicProjectNames(soap, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:getPublicProjectNamesResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:getPublicProjectNamesResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__getSiteStats(struct soap *soap)
{	struct tns__getSiteStats soap_tmp_tns__getSiteStats;
	tns__getSiteStatsResponse out;
	out.soap_default(soap);
	soap_default_tns__getSiteStats(soap, &soap_tmp_tns__getSiteStats);
	soap_get_tns__getSiteStats(soap, &soap_tmp_tns__getSiteStats, "tns:getSiteStats", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__getSiteStats(soap, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:getSiteStatsResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:getSiteStatsResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__login(struct soap *soap)
{	struct tns__login soap_tmp_tns__login;
	tns__loginResponse out;
	out.soap_default(soap);
	soap_default_tns__login(soap, &soap_tmp_tns__login);
	soap_get_tns__login(soap, &soap_tmp_tns__login, "tns:login", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__login(soap, soap_tmp_tns__login.userid, soap_tmp_tns__login.passwd, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:loginResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:loginResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__bugAdd(struct soap *soap)
{	struct tns__bugAdd soap_tmp_tns__bugAdd;
	tns__bugAddResponse out;
	out.soap_default(soap);
	soap_default_tns__bugAdd(soap, &soap_tmp_tns__bugAdd);
	soap_get_tns__bugAdd(soap, &soap_tmp_tns__bugAdd, "tns:bugAdd", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__bugAdd(soap, soap_tmp_tns__bugAdd.sessionkey, soap_tmp_tns__bugAdd.project, soap_tmp_tns__bugAdd.summary, soap_tmp_tns__bugAdd.details, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:bugAddResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:bugAddResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__getNumberOfHostedProjects(struct soap *soap)
{	struct tns__getNumberOfHostedProjects soap_tmp_tns__getNumberOfHostedProjects;
	tns__getNumberOfHostedProjectsResponse out;
	out.soap_default(soap);
	soap_default_tns__getNumberOfHostedProjects(soap, &soap_tmp_tns__getNumberOfHostedProjects);
	soap_get_tns__getNumberOfHostedProjects(soap, &soap_tmp_tns__getNumberOfHostedProjects, "tns:getNumberOfHostedProjects", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__getNumberOfHostedProjects(soap, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:getNumberOfHostedProjectsResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:getNumberOfHostedProjectsResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_FMAC5 int SOAP_FMAC6 soap_serve_tns__bugFetch(struct soap *soap)
{	struct tns__bugFetch soap_tmp_tns__bugFetch;
	tns__bugFetchResponse out;
	out.soap_default(soap);
	soap_default_tns__bugFetch(soap, &soap_tmp_tns__bugFetch);
	soap_get_tns__bugFetch(soap, &soap_tmp_tns__bugFetch, "tns:bugFetch", NULL);
	if (soap->error == SOAP_TAG_MISMATCH && soap->level == 2)
		soap->error = SOAP_NO_METHOD;
	if (soap->error)
		return soap->error;
	
	if (soap_body_end_in(soap)
	 || soap_envelope_end_in(soap)
#ifndef WITH_LEANER
	 || soap_getattachments(soap)
#endif
	 || soap_end_recv(soap))
		return soap->error;
	soap->error = tns__bugFetch(soap, soap_tmp_tns__bugFetch.sessionkey, soap_tmp_tns__bugFetch.project, soap_tmp_tns__bugFetch.bugid, &out);
	if (soap->error)
		return soap->error;
	soap_serializeheader(soap);
	out.soap_serialize(soap);
	soap_begin_count(soap);
	if (soap->mode & SOAP_IO_LENGTH)
	{	soap_envelope_begin_out(soap);
		soap_putheader(soap);
		soap_body_begin_out(soap);
		out.soap_put(soap, "tns:bugFetchResponse", "");
		soap_body_end_out(soap);
		soap_envelope_end_out(soap);
	};
	if (soap_response(soap, SOAP_OK)
	 || soap_envelope_begin_out(soap)
	 || soap_putheader(soap)
	 || soap_body_begin_out(soap)
	 || out.soap_put(soap, "tns:bugFetchResponse", "")
	 || soap_body_end_out(soap)
	 || soap_envelope_end_out(soap)
#ifndef WITH_LEANER
	 || soap_putattachments(soap)
#endif
	 || soap_end_send(soap))
		return soap->error;
	soap_closesock(soap);
	return SOAP_OK;
}

SOAP_END_NAMESPACE(soap)

/* end of soapServer.cpp */
