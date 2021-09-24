<?php

namespace Scienta\Sanny\UriSchemeValidator;

use Psr\Http\Message\UriInterface as Psr7UriInterface;

class SchemelessUriValidator implements UriSchemeValidatorInterface
{
	public function isValidUri(Psr7UriInterface $uri): bool
	{
		return !$uri->getScheme();
	}
}
