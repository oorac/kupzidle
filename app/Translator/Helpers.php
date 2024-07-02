<?php declare(strict_types=1);

namespace App\Translator;

use App\Utils\Strings;
use Latte\MacroNode;

class Helpers
{
    /**
     * @param MacroNode $node
     * @return bool
     */
	public static function macroWithoutParameters(MacroNode $node): bool
	{
		$result = Strings::trim($node->tokenizer->joinUntil(',')) === Strings::trim($node->args);
		$node->tokenizer->reset();

		return $result;
	}

}
