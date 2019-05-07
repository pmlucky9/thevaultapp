<?php

 
namespace TheVaultApp\Magento2\Api\Data;

interface SubscriptionInterface
{
    /**
     * Constants for keys of data array.
     */
    const ENTITY_ID = 'id';
    const STATUS = 'subscription_status';
    const CREATED_AT = 'created_at';
    const UPDATED_AT = 'updated_at';
    const TRACK_ID = 'track_id';
 
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
     * Get TrackId.
     */
    public function getTrackId();
 
    /**
     * Set TrackId.
     */
    public function setTrackId($entityId);

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
     * Get UpdatedAt.
     *
     * @return timestamp
     */
    public function getUpdatedAt();

    /**
     * Set UpdatedAt.
     */
    public function setUpdatedAt($updatedAt);

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