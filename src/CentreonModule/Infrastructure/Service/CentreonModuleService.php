<?php
namespace CentreonModule\Infrastructure\Service;

use Psr\Container\ContainerInterface;
use CentreonModule\Infrastructure\Source;

class CentreonModuleService
{

    /**
     * @var array
     */
    protected $sources = [];

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->_initSources($services);
    }

    public function getList(string $search = null, bool $installed = null, bool $updated = null, array $typeList = null)
    {
        $result = [];

        if ($typeList !== null && $typeList) {
            $sources = [];

            foreach ($this->sources as $type => $source) {
                if (!in_array($type, $typeList)) {
                    continue;
                }

                $sources[$type] = $source;
            }
        } else {
            $sources = $this->sources;
        }

        foreach ($sources as $type => $source) {
            $result[$type] = $source->getList($search, $installed, $updated);
        }

        return $result;
    }

    /**
     * Init list of sources
     *
     * @param ContainerInterface $services
     */
    protected function _initSources(ContainerInterface $services)
    {
        $this->sources = [
            Source\ModuleSource::TYPE => new Source\ModuleSource($services),
            Source\WidgetSource::TYPE => new Source\WidgetSource($services),
        ];
    }
}
