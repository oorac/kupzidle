<?php declare(strict_types=1);

namespace App\Translator\Latte;

use App\Translator\Helpers;
use Contributte;
use Latte\CompileException;
use Latte\Compiler;
use Latte\MacroNode;
use Latte\Macros\MacroSet;
use Latte\PhpWriter;

class Macros extends MacroSet
{
    /**
     * @param Compiler $compiler
     */
	public static function install(Compiler $compiler): void
	{
		$me = new static($compiler);

		$me->addMacro('_', [$me, 'macroTranslate'], [$me, 'macroTranslate']);
		$me->addMacro('translator', [$me, 'macroPrefix'], [$me, 'macroPrefix']);
	}

    /**
	 * {_$var |modifiers}
	 * {_$var, $count |modifiers}
	 * {_"Sample message", $count |modifiers}
	 * {_some.string.id, $count |modifiers}
     *
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string|void
     * @throws CompileException
     */
	public function macroTranslate(MacroNode $node, PhpWriter $writer)
    {
        if ($node->closing) {
            if (! str_contains($node->content, '<?php')) {
                $value = var_export($node->content, true);
                $node->content = '';

            } else {
                $node->openingCode = '<?php ob_start(function () {}) ?>' . $node->openingCode;
                $value = 'ob_get_clean()';
            }

            return $writer->write('$_fi = new LR\FilterInfo(%var); echo %modifyContent($this->filters->filterContent("translate", $_fi, %raw))', $node->context[0], $value);

        }

        if ($node->empty = ($node->args !== '')) {

            if (Helpers::macroWithoutParameters($node)) {
                return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word))');
            }

            return $writer->write('echo %modify(call_user_func($this->filters->translate, %node.word, %node.args))');
        }
    }

    /**
     * @param MacroNode $node
     * @param PhpWriter $writer
     * @return string|void
     * @throws CompileException
     */
	public function macroPrefix(MacroNode $node, PhpWriter $writer)
    {
		if ($node->closing) {
			if ($node->content !== null && $node->content !== '') {
				return $writer->write('$this->global->translator->prefix = $this->global->translator->prefixTemp;');
			}

		} else {
			if ($node->args === '') {
				throw new CompileException('Expected message prefix, none given.');
			}

			return $writer->write('$this->global->translator->prefix = [%node.word];');
		}
	}
}
