<?php

namespace Scienta\Sanny\UriSchemeValidator;

use Psr\Http\Message\UriInterface as Psr7UriInterface;

interface UriSchemeValidatorInterface
{
	public function isValidUri(Psr7UriInterface $uri): bool;
}
