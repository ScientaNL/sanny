<?php

namespace Scienta\Sanny\UriSchemeValidator;

use Psr\Http\Message\UriInterface as Psr7UriInterface;
use function rawurldecode;

class TelValidator implements UriSchemeValidatorInterface
{
	public function isValidUri(Psr7UriInterface $uri): bool
	{
		return $uri->getScheme() === "tel"
			&& !$uri->getAuthority()
			&& !$uri->getHost()
			&& !$uri->getHost()
			&& $this->isValidTel(rawurldecode($uri->getPath()))
			&& !$uri->getQuery()
			&& !$uri->getFragment();
	}

	private function isValidTel(string $value): bool
	{
		return !!preg_match(
			'/^[+]?[0-9- ()]+$/',
			$value
		);
	}
}
