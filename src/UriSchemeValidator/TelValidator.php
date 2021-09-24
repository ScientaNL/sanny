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
		// Thanks to: http://snipplr.com/view/11540/regex-for-tel-uris/
		// ^((?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*;phone-context=(?:\+[\d().-]*\d[\d().-]*|(?:[a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*(?:[a-z]|[a-z][a-z0-9-]*[a-z0-9])))(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*(?:,(?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*;phone-context=\+[\d().-]*\d[\d().-]*)(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*)*)$
		return !!preg_match(
			'/^((?:\+[\d().-]*\d[\d().\- ]*|[0-9A-F*#().\- ]*[0-9A-F*#][0-9A-F*#().\-– ]*(?:;[a-z\d\- ]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().\- ]|%[\dA-F]{2})+)?)*;phone-context=(?:\+[\d().-]*\d[\d().-–]*|(?:[a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*(?:[a-z]|[a-z][a-z0-9-]*[a-z0-9])))(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*(?:,(?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*;phone-context=\+[\d().-]*\d[\d().-]*)(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*)*)$/',
			$value
		);
	}
}
