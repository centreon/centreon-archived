<?php
namespace CentreonRemote\Infrastructure\Service;

class ExporterCacheService
{

    /**
     * @var mixed
     */
    private $data;

    /**
     * Get info if exists and if not add it using callback function
     *
     * @param string $key
     * @param callable $data
     * @return mixed
     */
    public function getIf(string $key, callable $data)
    {
        if (!$this->has($key)) {
            $this->data[$key] = $data();
        }

        $result = $this->data[$key];

        return $result;
    }

    /**
     * Setter
     *
     * @param string $key
     * @param mixed $data
     */
    public function set(string $key, $data): void
    {
        $this->data[$key] = $data;
    }

    /**
     * Merge
     *
     * @param string $key
     * @param mixed $data
     */
    public function merge(string $key, $data): void
    {
        if (!$this->has($key)) {
            $this->set($key, $data);
        } else {
            $this->data[$key] = array_merge($data, $this->data[$key]);
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        $result = $this->data === null ? false : array_key_exists($key, $this->data);

        return $result;
    }

    /**
     * Getter
     *
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        if (!$this->has($key)) {
            return null;
        }

        $result = $this->data[$key];

        return $result;
    }

    /**
     * Destroy data
     */
    public function destroy(): void
    {
        $this->data = null;
    }
}
