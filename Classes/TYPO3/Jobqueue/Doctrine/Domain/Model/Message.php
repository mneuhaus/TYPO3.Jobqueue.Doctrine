<?php
namespace TYPO3\Jobqueue\Doctrine\Domain\Model;

/*                                                                        *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Common". *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License, either version 3 of the   *
 * License, or (at your option) any later version.                        *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

use TYPO3\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * Message
 * @Flow\Entity
 */
class Message {

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var string
     */
    protected $state;

    /**
     * @var string
     * @ORM\Column(type="text")
     */
    protected $payload;

    /**
     * @var string
     */
    protected $queue;

    /**
    * TODO: Document this Method! ( __construct )
    */
    public function __construct() {
        $this->createdAt = new \DateTime();
    }

    /**
     * Gets createdAt.
     *
     * @return \DateTime $createdAt
     */
    public function getCreatedAt() {
        return $this->createdAt;
    }

    /**
     * Sets the createdAt.
     *
     * @param \DateTime $createdAt
     */
    public function setCreatedAt($createdAt) {
        $this->createdAt = $createdAt;
    }

    /**
     * Gets identifier.
     *
     * @return string $identifier
     */
    public function getIdentifier() {
        return $this->Persistence_Object_Identifier;
    }

    /**
     * Gets payload.
     *
     * @return string $payload
     */
    public function getPayload() {
        return $this->payload;
    }

    /**
     * Sets the payload.
     *
     * @param string $payload
     */
    public function setPayload($payload) {
        $this->payload = $payload;
    }

    /**
     * Gets queue.
     *
     * @return string $queue
     */
    public function getQueue() {
        return $this->queue;
    }

    /**
     * Sets the queue.
     *
     * @param string $queue
     */
    public function setQueue($queue) {
        $this->queue = $queue;
    }

    /**
     * Gets state.
     *
     * @return string $state
     */
    public function getState() {
        return $this->state;
    }

    /**
     * Sets the state.
     *
     * @param string $state
     */
    public function setState($state) {
        $this->state = $state;
    }

}