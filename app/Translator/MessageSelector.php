<?php declare(strict_types=1);

namespace App\Translator;

class MessageSelector
{
    /**
     * @var array
     */
    private static array $cache = [];

    /**
     * @param string $translation
     * @param float $numeric
     * @return string
     */
    public function choose(string $translation, float $numeric): string
    {
        return $this->resolveDefinition(
            $this->getDefinitions($translation),
            $numeric
        );
    }

    /**
     * @param array $definitions
     * @param float $numeric
     * @return string
     */
    private function resolveDefinition(array $definitions, float $numeric): string
    {
        $all = null;
        if (array_key_exists('*', $definitions)) {
            $all = $definitions['*'];
            unset($definitions['*']);
        }

        $current = $all ?? reset($definitions);
        foreach ($definitions as $border => $definition) {
            if ($numeric < $border) {
                return $current;
            }

            $current = $definition;
        }

        return $all;
    }

    /**
     * @param string $translation
     * @return array
     */
    private function getDefinitions(string $translation): array
    {
        if (! array_key_exists($translation, self::$cache)) {
            self::$cache[$translation] = $this->resolveDefinitions($translation);
        }

        return self::$cache[$translation];
    }

    /**
     * @param string $translation
     * @return array
     */
    private function resolveDefinitions(string $translation): array
    {
        $definitions = [];
        foreach (explode('|', $translation) as $item) {
            if (str_contains($item, ' >> ')) {
                [$value, $definition] = explode(' >> ', $item, 2);
                $value = trim($value);
                $value = $value === '*' ? '*' : (float) $value;

                $definitions[$value] = $definition;
                continue;
            }

            // deprecated
            preg_match('/^[\{\[]([^\[\]\{\}]*)[\}\]](.*)/s', $item, $matches);
            [, $limits, $definition] = $matches;
            $definition = trim($definition);
            $limits = array_map(static function (string $limit) {
                $limit = trim($limit);

                return $limit === '*' ? '*' : (float) $limit;
            }, explode(',', $limits));

            if (in_array('*', $limits, true)) {
                $definitions['*'] = $definition;
                $definitions[max($limits)] = $definition;

                continue;
            }

            $definitions[min($limits)] = $definition;
        }

        ksort($definitions);

        return $definitions;
    }
}
