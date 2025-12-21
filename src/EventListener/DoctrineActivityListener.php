<?php

namespace App\EventListener;

use App\Entity\ActivityLog;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Symfony\Bundle\SecurityBundle\Security;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
class DoctrineActivityListener
{
    public function __construct(
        private Security $security
    ) {}

    /* =========================
     * CREATE
     * ========================= */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->log(
            action: 'CREATE',
            entity: $args->getObject(),
            em: $args->getObjectManager()
        );
    }

    /* =========================
     * UPDATE
     * ========================= */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $oldValues = [];
        $newValues = [];

        foreach ($args->getEntityChangeSet() as $field => [$old, $new]) {
            $oldValues[$field] = $old;
            $newValues[$field] = $new;
        }

        $this->log(
            action: 'UPDATE',
            entity: $args->getObject(),
            em: $args->getObjectManager(),
            oldValues: $oldValues,
            newValues: $newValues
        );
    }

    /* =========================
     * DELETE
     * ========================= */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        $entity = $args->getObject();

        $this->log(
            action: 'DELETE',
            entity: $entity,
            em: $args->getObjectManager(),
            oldValues: $this->extractEntityData($entity)
        );
    }

    /* =========================
     * LOG COMMON
     * ========================= */
    private function log(
        string $action,
        object $entity,
        $em,
        ?array $oldValues = null,
        ?array $newValues = null
    ): void {
        // éviter boucle infinie
        if ($entity instanceof ActivityLog) {
            return;
        }

        $user = $this->security->getUser();
        if (!$user || !method_exists($entity, 'getId')) {
            return;
        }

        $log = new ActivityLog();
        $log->setAction($action);
        $log->setEntityClass($entity::class);
        $log->setEntityId($entity->getId());
        $log->setUserConnected($user);
        $log->setOldValues($oldValues);
        $log->setNewValues($newValues);

        $em->persist($log);
        $em->flush();
    }

    /* =========================
     * HELPERS
     * ========================= */
    private function extractEntityData(object $entity): array
    {
        $data = [];

        foreach (get_object_vars($entity) as $property => $value) {
            if (is_object($value)) {
                continue;
            }
            $data[$property] = $value;
        }

        return $data;
    }
}
