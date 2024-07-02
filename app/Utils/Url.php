<?php declare(strict_types=1);

namespace App\Utils;

use App\Helpers\UrlHelper;

final class Url
{
    /**
     * @var string
     */
    private string $scheme = '';

    /**
     * @var string
     */
    private string $host = '';

    /**
     * @var string
     */
    private string $path = '';

    /**
     * @var array
     */
    private array $queries = [];

    /**
     * @return static
     */
    public static function current(): self
    {
        return self::parse(UrlHelper::getFullUrl());
    }

    /**
     * @return static
     */
    public static function parse(string $url): self
    {
        $parts = parse_url($url) + [
            'scheme' => '',
            'host' => '',
            'path' => '',
            'query' => '',
        ];

        $self = new self();
        $self->scheme = $parts['scheme'];
        $self->host = $parts['host'];
        $self->path = $parts['path'];

        if ($parts['query']) {
            parse_str($parts['query'], $self->queries);
        }

        return $self;
    }

    /**
     * @return string
     */
    public function getScheme(): string
    {
        return $this->scheme;
    }

    /**
     * @param string $scheme
     * @return $this
     */
    public function setScheme(string $scheme): self
    {
        $this->scheme = $scheme;

        return $this;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     * @return $this
     */
    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     * @return $this
     */
    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return array
     */
    public function getQueries(): array
    {
        return $this->queries;
    }

    /**
     * @param array $query
     * @return $this
     */
    public function setQueries(array $query): self
    {
        $this->queries = $query;

        return $this;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function getQuery(string $name)
    {
        return $this->queries[$name] ?? null;
    }

    /**
     * @param string $name
     * @param $value
     * @return $this
     */
    public function setQuery(string $name, $value): self
    {
        $this->queries[$name] = $value;

        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function removeQuery(string $name): self
    {
        unset($this->queries[$name]);

        return $this;
    }

    /**
     * @return $this
     */
    public function removeQueries(): self
    {
        $this->queries = [];

        return $this;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $url = '';
        if ($this->scheme) {
            $url .= $this->scheme . '://';
        }

        $url .= $this->host;
        $url .= $this->path;

        if ($this->queries && $query = http_build_query($this->queries)) {
            $url .= '?' . $query;
        }

        return $url;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->toString();
    }
}
