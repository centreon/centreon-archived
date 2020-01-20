<?php
/*
 * Centreon
 *
 * Source Copyright 2005-2019 Centreon
 *
 * Unauthorized reproduction, copy and distribution
 * are not allowed.
 *
 * For more informations : contact@centreon.com
 *
 */

namespace CentreonAutoDiscovery\Infrastructure\Repository;

use Centreon\Infrastructure\CentreonLegacyDB\ServiceEntityRepository;

class HostDiscoveryRuleRepository extends ServiceEntityRepository implements HostDiscoveryRuleRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function installProvider(int $pluginPackId, int $typeId, array $rule, int $commandId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO `mod_host_disco_provider` '
            . '(pluginpack_id, name, description, type_id, command_id, test_option, parameters, default_template) '
            . 'VALUES ('
            . ':pluginpack_id, :name, :description, :type_id, :command_id, :test_option, :parameters, :default_template'
            .')'
        );

        $stmt->bindValue(':pluginpack_id', $pluginPackId, \PDO::PARAM_INT);
        $stmt->bindValue(':name', $rule['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':description', $rule['description'], \PDO::PARAM_STR);
        $stmt->bindValue(':type_id', $typeId, \PDO::PARAM_INT);
        $stmt->bindValue(':command_id', $commandId, \PDO::PARAM_INT);
        $stmt->bindValue(':test_option', $rule['test_option'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':parameters', json_encode($rule['parameters']), \PDO::PARAM_STR);
        $stmt->bindValue(':default_template', $rule['default_template'] ?? null, \PDO::PARAM_STR);

        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function installProviderMapping(int $providerId, string $name, array $mapping): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO `mod_host_disco_provider_mapping` '
            . '(provider_id, name, object, filters, attributes, association, templates, macros) '
            . 'VALUES (:provider_id, :name, :object, :filters, :attributes, :association, :templates, :macros)'
        );

        $stmt->bindValue(':provider_id', $providerId, \PDO::PARAM_INT);
        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->bindValue(':object', $mapping['object'], \PDO::PARAM_STR);
        $stmt->bindValue(':filters', json_encode($mapping['filters']), \PDO::PARAM_STR);
        $stmt->bindValue(':attributes', json_encode($mapping['attributes']), \PDO::PARAM_STR);
        $stmt->bindValue(':association', json_encode($mapping['association']), \PDO::PARAM_STR);
        $stmt->bindValue(':templates', json_encode($mapping['templates']), \PDO::PARAM_STR);
        $stmt->bindValue(':macros', json_encode($mapping['macros']), \PDO::PARAM_STR);

        $stmt->execute();

        return (int)$this->db->lastInsertId();
    }

    /**
     * @inheritdoc
     */
    public function updateProvider(int $providerId, int $pluginPackId, int $typeId, array $rule, int $commandId): void
    {
        $stmt = $this->db->prepare(
            'UPDATE `mod_host_disco_provider` SET '
            . 'pluginpack_id = :pluginpack_id, '
            . 'name = :name, '
            . 'description = :description, '
            . 'type_id = :type_id, '
            . 'command_id = :command_id, '
            . 'test_option = :test_option, '
            . 'parameters = :parameters, '
            . 'default_template = :default_template '
            . 'WHERE id = :id'
        );

        $stmt->bindValue(':pluginpack_id', $pluginPackId, \PDO::PARAM_INT);
        $stmt->bindValue(':name', $rule['name'], \PDO::PARAM_STR);
        $stmt->bindValue(':description', $rule['description'], \PDO::PARAM_STR);
        $stmt->bindValue(':type_id', $typeId, \PDO::PARAM_INT);
        $stmt->bindValue(':command_id', $commandId, \PDO::PARAM_INT);
        $stmt->bindValue(':test_option', $rule['test_option'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':parameters', json_encode($rule['parameters']), \PDO::PARAM_STR);
        $stmt->bindValue(':default_template', $rule['default_template'] ?? null, \PDO::PARAM_STR);
        $stmt->bindValue(':id', $providerId, \PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * @inheritdoc
     */
    public function updateProviderMapping(int $providerId, string $name, array $mapping): void
    {
        $stmt = $this->db->prepare(
            'UPDATE `mod_host_disco_provider_mapping` SET '
            . 'name = :name, '
            . 'object = :object, '
            . 'filters = :filters, '
            . 'attributes = :attributes, '
            . 'association = :association, '
            . 'templates = :templates, '
            . 'macros = :macros '
            . 'WHERE provider_id = :provider_id'
        );

        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $stmt->bindValue(':object', $mapping['object'], \PDO::PARAM_STR);
        $stmt->bindValue(':filters', json_encode($mapping['filters']), \PDO::PARAM_STR);
        $stmt->bindValue(':attributes', json_encode($mapping['attributes']), \PDO::PARAM_STR);
        $stmt->bindValue(':association', json_encode($mapping['association']), \PDO::PARAM_STR);
        $stmt->bindValue(':templates', json_encode($mapping['templates']), \PDO::PARAM_STR);
        $stmt->bindValue(':macros', json_encode($mapping['macros']), \PDO::PARAM_STR);
        $stmt->bindValue(':provider_id', $providerId, \PDO::PARAM_INT);

        $stmt->execute();
    }

    /**
     * @inheritdoc
     */
    public function installProviderType(string $name): void
    {
        $stmt = $this->db->prepare(
            'INSERT IGNORE INTO `mod_host_disco_provider_type` (name) '
            . 'VALUES (:name)'
        );

        $stmt->bindValue(':name', $name, \PDO::PARAM_STR);

        $stmt->execute();
    }

    /**
     * @inheritdoc
     */
    public function removeProvider(int $pluginPackId)
    {
        $stmt = $this->db->prepare(
            'DELETE FROM `mod_host_disco_provider` '
            . 'WHERE pluginpack_id = :pluginpack_id'
        );

        $stmt->bindValue(':pluginpack_id', $pluginPackId, \PDO::PARAM_INT);

        $stmt->execute();
    }
}
