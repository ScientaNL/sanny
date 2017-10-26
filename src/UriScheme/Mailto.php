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
		return $this->scheme === "mailto"
			&& $this->authority === null
			&& $this->host === null
			&& $this->port === null
			&& (\filter_var($this->path, \FILTER_SANITIZE_EMAIL) == $this->path)
			&& (\filter_var($this->query, \FILTER_SANITIZE_URL) == $this->query)
			&& $this->fragment === null;
	}
}
