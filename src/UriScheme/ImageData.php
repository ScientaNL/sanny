<?php

namespace Syslogic\Sanny\UriScheme;

use League\Uri\Schemes\Data;

class ImageData extends Data
{
	protected $allowedMimeTypes = ["image/png", "image/jpeg", "image/gif"];

	protected function isValidUri(): bool
	{
		if (parent::isValidUri() === false || count($parts = explode(',', $this->path, 2)) !== 2) {
			return false;
		}

		list($header, $data) = $parts;

		//@todo Weird case with an image of 1.2MB, resulting in a broken DOM Document. Needs checking
		if (strlen($data) > 1024 * 1024 * 0.75) {
			return false;
		} elseif (count($dataHeaderParts = explode(';', $header, 2)) !== 2) {
			return false;
		}

		list($mimeType, $encoding) = $dataHeaderParts;

		if ($encoding !== "base64" || in_array($mimeType, $this->allowedMimeTypes) === false) {
			return false;
		}

		$deducedMimeType = (new \finfo())->buffer(
			base64_decode($data, true),
			FILEINFO_MIME_TYPE
		);

		if (in_array($deducedMimeType, $this->allowedMimeTypes) === false) {
			return false;
		}

		// There are cases in which a jpeg is given a png mimetype. No problem, but lets correct it in the process...
		if ($deducedMimeType !== $mimeType) {
			$this->path = $deducedMimeType.";base64,".$parts[1];
		}

		return true;
	}
}
