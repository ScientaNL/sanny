<?php

namespace Scienta\Sanny\UriSchemeValidator;

use Psr\Http\Message\UriInterface as Psr7UriInterface;
use function filter_var;
use const FILTER_VALIDATE_EMAIL;

class MailtoValidator implements UriSchemeValidatorInterface
{
	public function isValidUri(Psr7UriInterface $uri): bool
	{
		return $uri->getScheme() === "mailto"
			&& !$uri->getAuthority()
			&& !$uri->getHost()
			&& !$uri->getHost()
			&& (filter_var($uri->getPath(), FILTER_VALIDATE_EMAIL) == $uri->getPath())
			&& (filter_var($uri->getQuery(), \FILTER_SANITIZE_URL) == $uri->getQuery())
			&& !$uri->getFragment();
	}
}
