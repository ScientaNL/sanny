<?php

namespace Syslogic\Sanny;

use League\Uri\Schemes\Data;
use League\Uri\Schemes\File;
use League\Uri\Schemes\Http;
use Syslogic\Sanny\AttributeEvaluator\AlignmentEvaluator;
use Syslogic\Sanny\AttributeEvaluator\AllowedClassesEvaluator;
use Syslogic\Sanny\AttributeEvaluator\ColorEvaluator;
use Syslogic\Sanny\AttributeEvaluator\DimensionsEvaluator;
use Syslogic\Sanny\AttributeEvaluator\IdentifierEvaluator;
use Syslogic\Sanny\AttributeEvaluator\NumberEvaluator;
use Syslogic\Sanny\AttributeEvaluator\StyleWhiteListEvaluator;
use Syslogic\Sanny\AttributeEvaluator\TrustedStringEvaluator;
use Syslogic\Sanny\AttributeEvaluator\UriEvaluator;
use Syslogic\Sanny\ElementHandler\IframeEmbedHandler;
use Syslogic\Sanny\UriScheme\Mailto;
use Syslogic\Sanny\UriScheme\Tel;

class SanitizationConfig
{
	const ELEMENT_ALLOW = "allow";
	const ELEMENT_STRIP = "strip";
	const ELEMENT_REMOVE = "remove";
	const ELEMENT_CUSTOM = "custom";

	const DEFAULT_KEY = "*";

	private $entitiesMap = [
		"&nbsp" => "{{@nbsp;}}",
		"&quot;" => "{{@quot;}}",
	];

	private $nodeSettings = [];
	private $attributeSettings = [];

	public function __construct()
	{
		//Default behaviour
		$this->addElement(self::DEFAULT_KEY, self::ELEMENT_STRIP);
		$this->addAttribute(
			self::DEFAULT_KEY,
			function () {
				return false;
			}
		);

		$this->addElement('a');
		$this->addElement('abbr');
		$this->addElement('acronym');
		$this->addElement('address');
		$this->addElement('b');
		$this->addElement('bdi');
		$this->addElement('bdo');
		$this->addElement('big');
		$this->addElement('blockquote');
		$this->addElement('br');
		$this->addElement('caption');
		$this->addElement('center');
		$this->addElement('cite');
		$this->addElement('code');
		$this->addElement('col');
		$this->addElement('colgroup');
		$this->addElement('dd');
		$this->addElement('del');
		$this->addElement('details');
		$this->addElement('dfn');
		$this->addElement('dir');
		$this->addElement('div');
		$this->addElement('dl');
		$this->addElement('dt');
		$this->addElement('em');
		$this->addElement('figcaption');
		$this->addElement('figure');
		$this->addElement('font');
		$this->addElement('h1');
		$this->addElement('h2');
		$this->addElement('h3');
		$this->addElement('h4');
		$this->addElement('h5');
		$this->addElement('h6');
		$this->addElement('hgroup');
		$this->addElement('hr');
		$this->addElement('i');
		$this->addElement('img');
		$this->addElement('ins');
		$this->addElement('kbd');
		$this->addElement('label');
		$this->addElement('li');
		$this->addElement('mark');
		$this->addElement('meter');
		$this->addElement('ol');
		$this->addElement('optgroup');
		$this->addElement('option');
		$this->addElement('output');
		$this->addElement('p');
		$this->addElement('pre');
		$this->addElement('progress');
		$this->addElement('q');
		$this->addElement('rp');
		$this->addElement('rt');
		$this->addElement('s');
		$this->addElement('samp');
		$this->addElement('section');
		$this->addElement('small');
		$this->addElement('source');
		$this->addElement('span');
		$this->addElement('strike');
		$this->addElement('strong');
		$this->addElement('sub');
		$this->addElement('summary');
		$this->addElement('sup');
		$this->addElement('table');
		$this->addElement('tbody');
		$this->addElement('td');
		$this->addElement('tfoot');
		$this->addElement('th');
		$this->addElement('thead');
		$this->addElement('time');
		$this->addElement('tr');
		$this->addElement('tt');
		$this->addElement('u');
		$this->addElement('ul');
		$this->addElement('var');
		$this->addElement('wbr');
		$this->addElement('script', self::ELEMENT_REMOVE);
		$this->addElement('style', self::ELEMENT_REMOVE);
		$this->addElement('embed', self::ELEMENT_REMOVE);
		$this->addElement('object', self::ELEMENT_REMOVE);

//		$this->addNode('audio');
//		$this->addNode('button');
//		$this->addNode('datalist');
//		$this->addNode('fieldset');
//		$this->addNode('input');
//		$this->addNode('legend');
//		$this->addNode('nav');
//		$this->addNode('ruby');
//		$this->addNode('textarea');
//		$this->addNode('title');
//		$this->addNode('track');
//		$this->addNode('video');

		$dimensionsEvaluator = new DimensionsEvaluator();
		$alignmentEvaluator = new AlignmentEvaluator();
		$numberEvaluator = new NumberEvaluator();
		$colorEvaluator = new ColorEvaluator();
		$trustedStringEvaluator = new TrustedStringEvaluator();
		$identifierEvaluator = new IdentifierEvaluator();
		$uriEvaluator = new UriEvaluator([
			"http" => Http::class,
			"https" => Http::class,
			"mailto" => Mailto::class,
			"tel" => Tel::class,
			"data" => Data::class,
			"file" => File::class,
			UriEvaluator::RELATIVE_URI => Http::class
		]);

		$styleEvaluator = new StyleWhiteListEvaluator([
			"background*",
			"border*",
			"caption-side",
			"clear",
			"color",
			"float",
			"font*",
			"height",
			"letter-spacing",
			"line-height",
			"list-style*",
			"margin*",
			"max-height",
			"max-width",
			"min-height",
			"min-width",
			"padding*",
			"table-layout",
			"text-align",
			"text-decoration",
			"text-indent",
			"text-transform",
			"vertical-align",
			"white-space",
			"width",
			"word-spacing",
		]);

		$allowedClassesEvaluator = new AllowedClassesEvaluator(
			[
				"contentTooltip",

				"CodeMirror-scroll",
				"hljs",
				"hljs-comment",
				"hljs-template_comment",
				"diff",
				"hljs-header",
				"hljs-javadoc",
				"hljs-keyword",
				"css",
				"rule",
				"hljs-winutils",
				"javascript",
				"hljs-title",
				"nginx",
				"hljs-subst",
				"hljs-request",
				"hljs-status",
				"hljs-number",
				"hljs-hexcolor",
				"ruby",
				"hljs-constant",
				"hljs-string",
				"hljs-tag",
				"hljs-value",
				"hljs-phpdoc",
				"tex",
				"hljs-formula",
				"hljs-id",
				"coffeescript",
				"hljs-params",
				"scss",
				"hljs-preprocessor",
				"lisp",
				"clojure",
				"hljs-class",
				"haskell",
				"hljs-type",
				"vhdl",
				"hljs-literal",
				"hljs-command",
				"hljs-rules",
				"hljs-property",
				"django",
				"hljs-attribute",
				"hljs-variable",
				"hljs-body",
				"hljs-regexp",
				"hljs-symbol",
				"hljs-special",
				"hljs-prompt",
				"hljs-built_in",
				"hljs-pragma",
				"hljs-pi",
				"hljs-doctype",
				"hljs-shebang",
				"hljs-cdata",
				"hljs-deletion",
				"hljs-addition",
				"hljs-change",
				"hljs-chunk",
			]
		);

		//Dimensions
		$this->addAttribute('width', $dimensionsEvaluator);
		$this->addAttribute("height", $dimensionsEvaluator);

		//Alignment
		$this->addAttribute("align", $alignmentEvaluator);
		$this->addAttribute("valign", $alignmentEvaluator);

		//Numbers
		$this->addAttribute("border", $numberEvaluator);
		$this->addAttribute("cellpadding", $numberEvaluator);
		$this->addAttribute("cellspacing", $numberEvaluator);
		$this->addAttribute("colspan", $numberEvaluator);
		$this->addAttribute("colspan", $numberEvaluator);
		$this->addAttribute("hspace", $numberEvaluator);
		$this->addAttribute("rowspan", $numberEvaluator);
		$this->addAttribute("tabindex", $numberEvaluator);
		$this->addAttribute("vspace", $numberEvaluator);
		$this->addAttribute("start", $numberEvaluator);
		$this->addAttribute("span", $numberEvaluator);
		$this->addAttribute("value", $numberEvaluator, ["li"]);

		//Styling & colors
		$this->addAttribute("style", $styleEvaluator);

		$this->addAttribute("bgcolor", $colorEvaluator);
		$this->addAttribute("color", $colorEvaluator);
		$this->addAttribute("class", $allowedClassesEvaluator);

		//Deprecated font
		$this->addAttribute("size", $numberEvaluator, ["font"]);
		$this->addAttribute("face", $trustedStringEvaluator, ["font"]);

		$this->addAttribute("alt", $trustedStringEvaluator);
		$this->addAttribute("abbr", $trustedStringEvaluator);
		$this->addAttribute("cite", $trustedStringEvaluator);
		$this->addAttribute("datetime", $trustedStringEvaluator);
		$this->addAttribute("dir", $trustedStringEvaluator);
		$this->addAttribute("download", $trustedStringEvaluator);
		$this->addAttribute("for", $trustedStringEvaluator);
		$this->addAttribute("headers", $trustedStringEvaluator);
		$this->addAttribute("hreflang", $trustedStringEvaluator);
		$this->addAttribute("label", $trustedStringEvaluator);
		$this->addAttribute("lang", $trustedStringEvaluator);
		$this->addAttribute("nowrap", $trustedStringEvaluator);
		$this->addAttribute("placeholder", $trustedStringEvaluator);
		$this->addAttribute("reversed", $trustedStringEvaluator);
		$this->addAttribute("summary", $trustedStringEvaluator);
		$this->addAttribute("title", $trustedStringEvaluator);
		$this->addAttribute("type", $trustedStringEvaluator);
		$this->addAttribute("wrap", $trustedStringEvaluator);

		$this->addAttribute("id", $identifierEvaluator);
		$this->addAttribute("name", $identifierEvaluator);
		$this->addAttribute("target", $identifierEvaluator, ["a"]);

		$this->addAttribute("href", $uriEvaluator, ["a"]);
		$this->addAttribute("src", $uriEvaluator, ["img"]);

		// ^(https?:)?//(www\.youtube(?:-nocookie)?\.com/embed/|player\.vimeo\.com/video/|docs\.google\.com/forms/|cdns\.snacktools\.net/flipsnack)

		$this->addElement(
			"iframe",
			self::ELEMENT_CUSTOM,
			new IframeEmbedHandler(
				$uriEvaluator,
				'%^(https?:)?//(www\\.youtube(?:-nocookie)?\\.com/embed/|player\\.vimeo\\.com/video/|docs\\.google\\.com/forms/|cdns\\.snacktools\\.net/flipsnack)%i'
			)
		);
		$this->addAttribute("frameborder", $uriEvaluator, ["iframe"]);
	}

	public function addElement(string $tagName, string $mode = self::ELEMENT_ALLOW, callable $customHandler = null)
	{
		$this->nodeSettings[$tagName] = ["mode" => $mode, "handler" => $customHandler];
	}

	public function addAttribute(string $attributeName, callable $evaluator, array $onlyOnElements = [])
	{
		$this->attributeSettings[$attributeName] = [$evaluator, $onlyOnElements];
	}

	public function getNodeSettings(string $tagName): array
	{
		return isset($this->nodeSettings[$tagName]) === true
			? $this->nodeSettings[$tagName]
			: $this->nodeSettings[self::DEFAULT_KEY];
	}

	public function getAttributeEvaluator(string $attributeName, string $tagName): callable
	{
		if (isset($this->attributeSettings[$attributeName]) === true) {
			list($evaluator, $onlyOnElements) = $this->attributeSettings[$attributeName];

			if (empty($onlyOnElements) === true || in_array($tagName, $onlyOnElements) === true) {
				return $evaluator;
			}
		}

		list($evaluator) = $this->attributeSettings[self::DEFAULT_KEY];

		return $evaluator;
	}

	public function getEntitiesMap()
	{
		return $this->entitiesMap;
	}
}
