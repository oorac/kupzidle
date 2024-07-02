<?php declare(strict_types=1);

namespace App\Utils;

final class CSVSerializer
{
    /**
     * @var string
     */
    private string $separator = ';';

    /**
     * @var string
     */
    private string $enclosure = '"';

    /**
     * @var string
     */
    private string $escape = '\\';

    /**
     * @var bool
     */
    private bool $headers = false;

    /**
     * @var bool
     */
    private bool $webalizeHeaders = false;

    /**
     * @param array $items
     * @return string
     */
    public function serialize(array $items): string
    {
        $items = $this->unite($items);
        $resource = fopen('php://memory', 'rb+');

        if ($this->headers) {
            $headers = array_keys($items[array_key_first($items)]);
            if ($this->webalizeHeaders) {
                $headers = array_map([Strings::class, 'webalize'], $headers);
            }

            fputcsv($resource, $headers, $this->separator, $this->enclosure, $this->escape);
        }

        foreach ($items as $row) {
            $row = array_map([$this, 'convertValue'], $row);
            fputcsv($resource, $row, $this->separator, $this->enclosure, $this->escape);
        }

        rewind($resource);
        $csv = stream_get_contents($resource);
        fclose($resource);

        return $csv;
    }

    /**
     * @param string $separator
     * @return $this
     */
    public function setSeparator(string $separator): self
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * @param string $enclosure
     * @return $this
     */
    public function setEnclosure(string $enclosure): self
    {
        $this->enclosure = $enclosure;

        return $this;
    }

    /**
     * @param string $escape
     * @return $this
     */
    public function setEscape(string $escape): self
    {
        $this->escape = $escape;

        return $this;
    }

    /**
     * @param bool $headers
     * @return $this
     */
    public function useHeaders(bool $headers): self
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param bool $webalizeHeaders
     * @return $this
     */
    public function webalizeHeaders(bool $webalizeHeaders): self
    {
        $this->webalizeHeaders = $webalizeHeaders;

        return $this;
    }

    /**
     * @param array $items
     * @return array
     */
    private function unite(array $items): array
    {
        $keys = [];
        foreach ($items as $row) {
            foreach (array_keys($row) as $key) {
                if (! in_array($key, $keys, true)) {
                    $keys[] = $key;
                }
            }
        }

        $filler = array_combine($keys, array_fill(0, count($keys), null));

        return array_map(static function (array $row) use ($filler) {
            return array_merge($filler, $row);
        }, $items);
    }

    /**
     * @param string|int|float|bool|array|object|null $value
     * @return float|int|string|null
     */
    private function convertValue(string|int|float|bool|array|object|null $value): float|int|string|null
    {
        if (is_bool($value)) {
            return (int) $value;
        }

        if (is_array($value)) {
            return json_encode($value);
        }

        if (is_object($value)) {
            return serialize($value);
        }

        return $value;
    }
}
