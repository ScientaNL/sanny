<?php

namespace Scienta\Sanny\UriSchemeValidator;

use Psr\Http\Message\UriInterface as Psr7UriInterface;
use const FILEINFO_MIME_TYPE;

class ImageDataValidator implements UriSchemeValidatorInterface
{
	protected array $allowedMimeTypes;

	public function __construct(array $allowedMimeTypes = ["image/png", "image/jpeg", "image/gif"])
	{
		$this->allowedMimeTypes = $allowedMimeTypes;
	}

	public function isValidUri(Psr7UriInterface $uri): bool
	{
		if (2 !== count($parts = explode(',', (string)$uri->getPath(), 2))) {
			return false;
		}

		[$header, $data] = $parts;

		// @todo Weird case with an image of 1.2MB, resulting in a broken DOM Document. Needs checking
		if (strlen($data) > 1024 * 1024 * 0.75) {
			return false;
		} elseif (count($dataHeaderParts = explode(';', $header, 2)) !== 2) {
			return false;
		}

		[$mimeType, $encoding] = $dataHeaderParts;

		if ($encoding !== "base64" || !in_array($mimeType, $this->allowedMimeTypes)) {
			return false;
		}

		$deducedMimeType = (new \finfo())->buffer(
			base64_decode($data, true),
			FILEINFO_MIME_TYPE
		);

		return in_array($deducedMimeType, $this->allowedMimeTypes);
	}
}
