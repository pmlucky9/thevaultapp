<?php


namespace TheVaultApp\Magento2\Model;

use TheVaultApp\Magento2\Api\Data\SubscriptionInterface;
use Magento\Framework\Model\AbstractModel;

class Subscription extends AbstractModel implements SubscriptionInterface
{
    const CACHE_TAG = 'cko_m2_subscriptions';

    protected $_cacheTag = 'cko_m2_subscriptions';

    protected $_eventPrefix = 'cko_m2_subscriptions';

    protected function _construct()
    {
        $this->_init('TheVaultApp\Magento2\Model\ResourceModel\Subscription');
    }

    /**
     * Get EntityId.
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
 
    /**
     * Set EntityId.
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get TrackId.
     *
     * @return string
     */
    public function getTrackId()
    {
        return $this->getData(self::TRACK_ID);
    }
 
    /**
     * Set TrackId.
     */
    public function setTrackId($entityId)
    {
        return $this->setData(self::TRACK_ID, $entityId);
    }

    /**
     * Get Status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
 
    /**
     * Set Status.
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
 
    /**
     * Get UpdatedAt.
     *
     * @return timestamp
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }
 
    /**
     * Set UpdatedAt.
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
 
    /**
     * Get CreatedAt.
     *
     * @return varchar
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }
 
    /**
     * Set CreatedAt.
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
}