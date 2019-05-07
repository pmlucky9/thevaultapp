<?php


namespace TheVaultApp\Magento2\Api\Data;

interface UserCardsInterface
{
    /**
     * Constants for keys of data array.
     */
    const ENTITY_ID = 'id';
    const STATUS = 'card_status';
    const CREATED_AT = 'created_at';
 
    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId();
 
    /**
     * Set EntityId.
     */
    public function setEntityId($entityId);
 
    /**
     * Get Status.
     *
     * @return int
     */
    public function getStatus();

    /**
     * Set Status.
     */
    public function setStatus($status);

    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt();
 
    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt);
}