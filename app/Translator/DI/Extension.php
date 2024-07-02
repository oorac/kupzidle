<?php declare(strict_types=1);

namespace App\Translator\DI;

use App\Helpers\LocalesHelper;
use App\Translator\Latte\Macros;
use App\Translator\Tracy\Panel;
use App\Translator\Translator;
use Nette\Application\UI\TemplateFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Localization\Translator as NetteTranslator;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\Literal;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use RuntimeException;
use Tracy\IBarPanel;

class Extension extends CompilerExtension
{
    private const DEFAULT_LOCALE = LocalesHelper::DEFAULT;

    /**
     * @return Schema
     */
	public function getConfigSchema(): Schema
	{
		$builder = $this->getContainerBuilder();

		return Expect::structure([
			'debug' => Expect::bool($builder->parameters['debugMode']),
			'debugger' => Expect::bool(interface_exists(IBarPanel::class)),
			'locales' => Expect::structure([
				'whitelist' => Expect::array()->default([self::DEFAULT_LOCALE]),
				'default' => Expect::string(self::DEFAULT_LOCALE),
			]),
			'dirs' => Expect::array()->default([]),
            'storage' => Expect::string($builder->parameters['tempDir'] . '/cache/translations.php'),
		]);
	}

    /**
     * @return void
     */
	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();

		// Translator
		if ($this->config->locales->default === null) {
		    throw new RuntimeException('Default locale must be set.');
		}

        // Tracy Panel
        $tracyPanel = null;
		if ($this->config->debug && $this->config->debugger) {
			$tracyPanel = $builder->addDefinition($this->prefix('tracyPanel'))->setFactory(Panel::class);
		}

		$builder->addDefinition($this->prefix('translator'))
			->setType(NetteTranslator::class)
			->setFactory(Translator::class, [
                'directories' => $this->config->dirs,
                'storage' => $this->config->storage,
                'debug' => $this->config->debug,
                'tracyPanel' => $tracyPanel,
            ]);
	}

    /**
     * @return void
     */
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var ServiceDefinition $translator */
		$translator = $builder->getDefinition($this->prefix('translator'));
		$templateFactoryName = $builder->getByType(TemplateFactory::class);

		if ($templateFactoryName !== null) {
			/** @var ServiceDefinition $templateFactory */
			$templateFactory = $builder->getDefinition($templateFactoryName);
			$templateFactory->addSetup('
                $service->onCreate[] = function (Nette\\Bridges\\ApplicationLatte\\Template $template): void {
                    $template->setTranslator(?);
                };', [$translator]);
		}

		if ($builder->hasDefinition('latte.latteFactory')) {
			/** @var FactoryDefinition $latteFactory */
			$latteFactory = $builder->getDefinition('latte.latteFactory');
			$latteFactory->getResultDefinition()
				->addSetup('?->onCompile[] = function (Latte\\Engine $engine): void { ?::install($engine->getCompiler()); }', ['@self', new Literal(Macros::class)])
				->addSetup('addProvider', ['translator', $builder->getDefinition($this->prefix('translator'))]);
		}
	}

    /**
     * @param ClassType $class
     */
	public function afterCompile(ClassType $class): void
	{
		if ($this->config->debug && $this->config->debugger) {
			$initialize = $class->getMethod('initialize');
			$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', ['tracy.bar', $this->prefix('tracyPanel')]);
		}
	}
}
