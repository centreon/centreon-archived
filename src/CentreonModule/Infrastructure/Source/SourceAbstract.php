<?php
namespace CentreonModule\Infrastructure\Source;

use Psr\Container\ContainerInterface;
use CentreonModule\Infrastructure\Entity\Module;
use CentreonModule\Infrastructure\Source\SourceInterface;

abstract class SourceAbstract implements SourceInterface
{

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    protected $db;

    /**
     * @var \Symfony\Component\Finder\Finder
     */
    protected $finder;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get('centreon.db-manager');
        $this->finder = $services->get('finder');
    }

    public function isEligible(Module $entity, string $search = null, bool $installed = null, bool $updated = null): bool
    {
        if ($search !== null && stripos($entity->getKeywords() . $entity->getName(), $search) === false) {
            return false;
        } elseif ($installed !== null && $entity->isInstalled() !== $installed) {
            return false;
        } elseif ($updated !== null && $entity->isUpdated() !== $updated) {
            return false;
        }

        return true;
    }
}
