<?php
/*
 *
 * Novaforge is a registered trade mark from Bull S.A.S
 * Copyright (C) 2007 Bull S.A.S.
 * 
 * http://novaforge.org/
 *
 *
 * This file has been developped within the Novaforge(TM) project from Bull S.A.S
 * and contributed back to GForge community.
 *
 * GForge is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GForge is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this file; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

require_once ("common/novaforge/log.php");
require_once ("common/novaforge/proxy/ProxyConfig.class.php");

/*
 * Parse the http header
 */ 
class ProxyHttpHeader
{

	var $headerString;      // The header string of a http reponse
	var $headerArray;       // Array of the http header reponse
	var $confProxy;         // a ConfigProxy object

	// Constructor
	function ProxyHttpHeader ($confProxy, $headerString)
	{
		$this->confProxy = $confProxy;
		$this->headerString = $headerString;
	}

	// Fetch headers in an internal array
	function fetchHeader ()
	{
		$this->headerArray = array ();
		$headerLine = explode ("\n", $this->headerString);
		foreach ($headerLine as $line)
		{
			@list ($name, $value) = explode (":", $line, 2); 
			if (isset ($this->headerArray [$name]) == false)
			{
				$this->headerArray [$name] = array ();
			}
			$this->headerArray [$name] [] = $value;
		}
	}
    
	// Return true if the header is in the internal array
	function isSetHeaderParam ($name)
	{
		return isset ($this->headerArray [$name]);
	}
    
	// Return the value of a header in the internal array
	function getHeaderParam ($name)
	{
		if ($this->isSetHeaderParam ($name))
		{
			return $this->headerArray [$name];
		}
		else
		{
			return null;
		}
	}
    
	// Send one header
	function sendOneHeader ($name, $value)
	{
		switch (strtolower ($name))
		{
			case "content-disposition" :
			case "date" : 
			case "cache-control" :
			case "expires" :
			case "pragma" :
			case "content-description" :
				// Send header without change
				header ($name . ":" . $value);
				break;
			case "content-type" :
				// Send header without change, only if not a html page
				if ($this->isHeaderOfHtmlPage () == false)
				{
					header ($name . ":" . $value);
				}
				break;
			case "content-length" :
			case "last-modified" :
				// Not send, else bugs with IE...
				break;
			case "location" :
				// Translate location
				header ($name . ":" . $this->confProxy->translateUrl ($value));
				break;
			case "set-cookie" :
				$results = explode (";", $value);
				$i = 0;
				while ($i < count ($results))
				{
					$results [$i] = explode ("=", $results [$i]);
					$i++;
				}
				$cookieName = $this->confProxy->getCookiesPrefix () . trim ($results [0] [0]);
				$cookieValue = trim ($results [0] [1]);
				$cookieExpires = 0;
				$cookiePath = "";
				$cookieDomain = "";
				$cookieSecure = false;
				$i = 1;			
				while ($i < count ($results))
				{
					switch (strtolower (trim ($results [$i] [0])))
					{
						case "expires" :
							$cookieExpires = strtotime (trim ($results [$i] [1]));
							break;
						case "domain" :
							$cookieDomain = $this->confProxy->getLocalDomain ();
							break;
						case "path" :
							$cookiePath = $this->confProxy->getLocalPath ();
							break;
						case "secure" :
							$cookieSecure = true;
							break;
						case "httponly" :
							// Ignored in PHP 4
							log_info ("Attribute 'httponly' of Set-Cookie header is ignored", __FILE__, __FUNCTION__, __CLASS__);
							break;
						default :
							// Unknown
							log_info ("Unknown attribute '" . trim ($results [$i] [0]) . "' for Set-Cookie header", __FILE__, __FUNCTION__, __CLASS__);
					}
					$i++;
				}
				setcookie ($cookieName, $cookieValue, $cookieExpires, $cookiePath, $cookieDomain, $cookieSecure);
				break;
            		default:
                		// Other headers are ignored
				log_info ("HTTP header ignored: '" . $name . ":" . $value . "'", __FILE__, __FUNCTION__, __CLASS__);
		}
	}

	// Send header
	function sendHeader ()
	{
		foreach ($this->headerArray as $name => $values)
		{
			foreach ($values as $value)
			{
				$this->sendOneHeader ($name, $value);
			}
		}
		// Force cache control if not specified (else NovaForge set a "no cache" by default and IE bug...)
		if ($this->getHeaderParam ("Cache-control") === null)
		{
			header ("Cache-control: public");
		}
	} 

	// Return true if the content type is in $contentTypeArr array
	function isContentTypeOneOf ($contentTypeArr)
	{
		foreach ($this->headerArray as $name => $valueArr)
		{
			if (strtolower ($name) == "content-type")
			{
				foreach ($valueArr as $value)
				{
					if (strpos ($value, ";" ))
					{
						list ($content, $foo) = explode (";", $value, 2);
					}
					else
					{
						$content = $value;
					}
					if (in_array (strtolower (trim ($content)), $contentTypeArr))
					{
						return true;
					}
				}
			}
		}
		return false;
	}

	// Return true if the header is a header for a html page (detect with "content-type" parameter)
	function isHeaderOfHtmlPage ()
	{
		return $this->isContentTypeOneOf (array ("text/html", "application/xml+xhtml", "application/xhtml+xml"));
	}

	// Return true if the header is a header for a css style page (detect with "content-type" parameter)
	function isHeaderOfCssPage ()
	{
		return $this->isContentTypeOneOf (array ("text/css"));
	}

	// Convert encoding of the response to the specified charset
	function changeCharset ($response, $to_charset)
	{
		$from_charset = "";
		if (array_key_exists ("Content-Type", $this->headerArray) == true)
		{
			$parts = explode (";", $this->headerArray ["Content-Type"] [0]);
			if ((is_array ($parts) == true) && (count ($parts) > 1))
			{
				$index = 1;
				$found = false;
				while (($index < count ($parts)) && ($found == false))
				{
					$parts2 = explode ("=", $parts [$index]);
					if ((is_array ($parts2) == true) && (count ($parts2) == 2))
					{
						if (trim ($parts2 [0]) == "charset")
						{
							$from_charset = trim ($parts2 [1]);
							if (strlen ($from_charset) > 0)
							{
								$found = true;
							}
						}
					}
					$index++;
				}
			}
		}
		if (strlen ($from_charset) <= 0)
		{
			$from_charset = mb_detect_encoding ($response);
		}
		if (strlen ($from_charset) > 0)
		{
			if (strcasecmp ($to_charset, $from_charset) != 0)
			{
				$response = mb_convert_encoding ($response, $to_charset, $from_charset);
			}
		}
		else
		{
			 $response = mb_convert_encoding ($response, $to_charset);
		}
		return $response;
	}

}

?>
