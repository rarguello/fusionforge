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

/*
 * Config class for a proxy
 */
class ProxyConfig
{

	var $remoteUrl;
	var $localSsl;
	var $localDomain;
	var $localPath;
	var $cookiesPrefix;
	var $urlExceptions;
	var $parsedRemoteUrl;
	var $skipEncoding;

	// Constructor
	function ProxyConfig ($remoteUrl, $localSsl, $localDomain, $localPath, $cookiesPrefix, $encodingSkip = false)
	{
		$this->remoteUrl = $remoteUrl;
		$this->localSsl = $localSsl;
		$this->localDomain = $localDomain;
		$this->localPath = $localPath;
		$this->cookiesPrefix = $cookiesPrefix;
		$this->urlExceptions = array ("mailto:", "javascript:");
		$this->parsedRemoteUrl = parse_url ($this->remoteUrl);
		$this->skipEncoding = $encodingSkip;
	}

  // Get the skip encoding
  function getSkipEncoding ()
  {
    return $this->skipEncoding;
  }
	
  // Get the remote URL
	function getRemoteUrl ()
	{
		return $this->remoteUrl;
	}

	// Get the local SSL
	function getLocalSsl ()
	{
		return $this->localSsl;
	}

	// Get the local domain
	function getLocalDomain ()
	{
		return $this->localDomain;
	}

	// Get the local path
	function getLocalPath ()
	{
		return $this->localPath;
	}

	// Get the prefix for cookies
	function getCookiesPrefix ()
	{
		return $this->cookiesPrefix; 
	}

	// Translate an URL
	function translateUrl ($url)
	{
		$translate = false;
		$exception = false;
		$url = trim ($url);
		// Process URL exceptions
		foreach ($this->urlExceptions as $urlException)
		{
			if (strpos ($url, $urlException) === 0)
			{
				$exception = true;
			}
		}
		if ($exception == false)
		{
			$parsedUrl = parse_url ($url);
			if ((isset ($parsedUrl ["scheme"]) == true) &&  (isset ($parsedUrl ["host"]) == true))
			{
				// Scheme and host are defined
				if (($parsedUrl ["scheme"] === $this->parsedRemoteUrl ["scheme"])
				&&  ($parsedUrl ["host"] === $this->parsedRemoteUrl ["host"])
				&&  ($parsedUrl ["port"] === $this->parsedRemoteUrl ["port"])
				&&  (strpos ($parsedUrl ["path"], $this->parsedRemoteUrl ["path"]) === 0))
				{
					// This is the remote URL ...
					/// ... translate it
					$translate = true;
				}
				else
				{
					// This is not the remote URL ...
					// ... nothing to do
				}
			}
			else
			{
				// Scheme and host not defined
				if (isset($parsedUrl ["path"]) && strpos ($parsedUrl ["path"],$this->parsedRemoteUrl ["path"]) === 0)
				{
					// This is the remote URL ...
					// ... translate it
					$translate = true;
				}
				else
				{
					// This is a relative URL, or an URL outside the remote URL ...
					// ... nothing to do
				}
			}
			if ($translate == true)
			{
				// Translate the remote URL to a local URL without scheme/hostname/port
				$url = $this->localPath;
				$temp = substr ($parsedUrl ["path"], strlen ($this->parsedRemoteUrl ["path"]));
				if (($url [strlen ($url) - 1] != "/") && ($temp [0] != "/"))
				{
					$url .= "/";
				}
				$url .= $temp;
				if (isset ($parsedUrl ["query"]) == true)
				{
					$url .= "?" . $parsedUrl ["query"];
				}
				if (isset ($parsedUrl ["fragment"]) == true)
				{
					$url .= "#" . $parsedUrl ["fragment"];
				}
			}
		}
		return $url;
	}

}

?>
