<?php declare(strict_types=1);

namespace App\Utils;

final class CSVParser
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
     * @var array|callable[]
     */
    private array $filters = [];

    /**
     * @param string $csv
     * @return array
     */
    public function parse(string $csv): array
    {
        $rows = array_map(function (string $line) {
            return str_getcsv($line, $this->separator, $this->enclosure, $this->escape);
        }, array_filter(explode(PHP_EOL, $this->fixEncoding($csv))));

        if ($this->headers) {
            $headers = array_shift($rows);

            if ($this->webalizeHeaders) {
                $headers = array_map([Strings::class, 'webalize'], $headers);
            }

            $rows = array_map(static function ($values) use ($headers) {
                return array_combine($headers, $values);
            }, $rows);
        }

        if (! empty($this->filters)) {
            foreach ($rows as &$row) {
                foreach ($row as $property => &$value) {
                    if ($filter = $this->filters[$property] ?? null) {
                        $value = $filter($value);
                    }
                }
            }
        }

        return $rows;
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
     * @param string|int $property
     * @param callable $filter
     * @return $this
     */
    public function addFilter(string|int $property, callable $filter): self
    {
        $this->filters[$property] = $filter;

        return $this;
    }

    /**
     * @param string $input
     * @return string
     */
    private function fixEncoding(string $input): string
    {
        // UTF-8
        if (preg_match('#[\x80-\x{1FF}\x{2000}-\x{3FFF}]#u', $input)) {
            return $input;
        }

        // WINDOWS-1250
        if (preg_match('#[\x7F-\x9F\xBC]#', $input)) {
            return iconv('WINDOWS-1250', 'UTF-8', $input);
        }

        return iconv('ISO-8859-2', 'UTF-8', $input);
    }
}
