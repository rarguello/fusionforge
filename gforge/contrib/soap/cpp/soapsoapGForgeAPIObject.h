/* soapsoapGForgeAPIObject.h
   Generated by gSOAP 2.3 rev 1 from SoapAPI.h
   Copyright (C) 2001-2003 Genivia inc.
   All Rights Reserved.
*/

#ifndef soapsoapGForgeAPI_H
#define soapsoapGForgeAPI_H
#include "soapH.h"
SOAP_BEGIN_NAMESPACE(soap)
class soapGForgeAPI : public soap
{    public:
	soapGForgeAPI() { soap_init(this); };
	~soapGForgeAPI() { soap_destroy(this); soap_end(this); soap_done(this); };
	int serve() { return soap_serve(this); };
};
SOAP_END_NAMESPACE(soap)
#endif
