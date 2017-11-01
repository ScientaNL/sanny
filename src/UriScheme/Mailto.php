<?php

namespace Syslogic\Sanny\UriScheme;

use League\Uri\Schemes\AbstractUri;

class Mailto extends AbstractUri
{
	protected static $supported_schemes = [
		'mailto' => null,
	];

	protected function isValidUri(): bool
	{
		//We're validating an attribute, so an ampersand is html entity encoded, decode it and validate with &
		$path = str_replace("&amp;", "&", $this->path);

		return $this->scheme === "mailto"
			&& $this->authority === null
			&& $this->host === null
			&& $this->port === null
			&& (\filter_var($path, \FILTER_SANITIZE_EMAIL) == $path)
			&& (\filter_var($this->query, \FILTER_SANITIZE_URL) == $this->query)
			&& $this->fragment === null;
	}
}
