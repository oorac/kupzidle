<?php declare(strict_types=1);

namespace App\Utils;

use Exception;
use SimpleXMLElement;

final class XMLString
{
    /**
     * @var SimpleXMLElement|null
     */
    private ?SimpleXMLElement $xml = null;

    /**
     * @var array|null
     */
    private ?array $array = null;

    /**
     * @param string $string
     */
    public function __construct(
        private readonly string $string
    ) {}

    /**
     * @return array
     * @throws Exception
     */
    public function toArray(): array
    {
        if ($this->array === null) {
            $this->array = $this->exportElement($this->toXML());
        }

        return $this->array;
    }

    /**
     * @return SimpleXMLElement
     * @throws Exception
     */
    private function toXML(): SimpleXMLElement
    {
        if ($this->xml === null) {
            $this->xml = new SimpleXMLElement($this->string);
        }

        return $this->xml;
    }

    /**
     * @param SimpleXMLElement $element
     * @return array
     */
    private function exportElement(SimpleXMLElement $element): array
    {
        $output = [
            '__name' => $element->getName(),
        ];

        foreach ($element->children() as $child) {
            $output['__children'][] = $this->exportElement($child);
        }

        foreach ($element->attributes() as $property => $value) {
            $output[$property] = (string) $value;
        }

        return $output;
    }
}
