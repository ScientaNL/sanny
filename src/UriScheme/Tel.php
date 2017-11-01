<?php


namespace Syslogic\Sanny\UriScheme;

use League\Uri\Schemes\AbstractUri;

class Tel extends AbstractUri
{
	protected static $supported_schemes = [
		'tel' => null,
	];

	protected function isValidUri(): bool
	{
		// We're validating an attribute, so it could be url-encoded. Decode it and validate
		$path = rawurldecode($this->path);

		return $this->scheme === "tel"
			&& $this->authority === null
			&& $this->host === null
			&& $this->port === null
			&& $this->isValidTel($path)
			&& $this->query === null
			&& $this->fragment === null;
	}

	private function isValidTel(string $value): bool
	{
		// Thanks to: http://snipplr.com/view/11540/regex-for-tel-uris/
		// ^((?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*;phone-context=(?:\+[\d().-]*\d[\d().-]*|(?:[a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*(?:[a-z]|[a-z][a-z0-9-]*[a-z0-9])))(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*(?:,(?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*;phone-context=\+[\d().-]*\d[\d().-]*)(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*'().-]|%[\dA-F]{2})+)?)*)*)$
		return !!preg_match(
			'/^((?:\+[\d().-]*\d[\d().\- ]*|[0-9A-F*#().\- ]*[0-9A-F*#][0-9A-F*#().\- ]*(?:;[a-z\d\- ]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().\- ]|%[\dA-F]{2})+)?)*;phone-context=(?:\+[\d().-]*\d[\d().-]*|(?:[a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)*(?:[a-z]|[a-z][a-z0-9-]*[a-z0-9])))(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*(?:,(?:\+[\d().-]*\d[\d().-]*|[0-9A-F*#().-]*[0-9A-F*#][0-9A-F*#().-]*(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*;phone-context=\+[\d().-]*\d[\d().-]*)(?:;[a-z\d-]+(?:=(?:[a-z\d\[\]\/:&+$_!~*\'().-]|%[\dA-F]{2})+)?)*)*)$/',
			$value
		);
	}
}
