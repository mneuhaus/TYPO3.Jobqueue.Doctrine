<?php
namespace TYPO3\Jobqueue\Doctrine\Queue;

/*                                                                            *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Beanstalkd". *
 *                                                                            *
 * It is free software; you can redistribute it and/or modify it under        *
 * the terms of the GNU General Public License, either version 3 of the       *
 * License, or (at your option) any later version.                            *
 *                                                                            *
 * The TYPO3 project - inspiring people to share!                             *
 *                                                                            */

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\PersistenceManagerInterface;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Jobqueue\Common\Exception as JobqueueException;
use TYPO3\Jobqueue\Common\Queue\Message;
use TYPO3\Jobqueue\Common\Queue\QueueInterface;
use TYPO3\Jobqueue\Doctrine\Domain\Model\Message as DoctrineMessage;
use TYPO3\Jobqueue\Doctrine\Domain\Repository\MessageRepository;

/**
 * A queue implementation using doctrine as a backend
 */
class DoctrineQueue implements QueueInterface {

	/**
	 * @var string
	 */
	protected $name;

	/**
	 * @Flow\Inject
	 * @var MessageRepository
	 */
	protected $messageRepository;

	/**
	 * @Flow\Inject
	 * @var PersistenceManagerInterface
	 */
	protected $persistenceManager;

	/**
	 * @param string $name
	 * @param array $options
	 */
	public function __construct($name, array $options = array()) {
		$this->name = $name;
		$this->options = $options;
	}

	/**
	 * Publish a message to the queue
	 *
	 * @param Message $message
	 * @return void
	 */
	public function submit(Message $message) {
		$doctrineMessage = new DoctrineMessage();
		$doctrineMessage->setQueue($this->name);
		$doctrineMessage->setState(Message::STATE_SUBMITTED);
		$doctrineMessage->setPayload($message->getPayload());
		$this->messageRepository->add($doctrineMessage);
		$this->persistenceManager->persistAll();
		$message->setIdentifier($doctrineMessage->getIdentifier());
	}

	/**
	 * Wait for a message in the queue and return the message for processing
	 * (without safety queue)
	 *
	 * @param integer $timeout in seconds
	 * @return Message The received message or NULL if a timeout occurred
	 */
	public function waitAndTake($timeout = NULL) {
		if ($timeout === NULL) {
			sleep($timeout);
		}

		$doctrineMessage = $this->messageRepository->findOneInQueue($this->name);

		if (!$doctrineMessage instanceof DoctrineMessage) {
			return NULL;
		}

		$message = new Message();
		$message->setIdentifier($doctrineMessage->getIdentifier());
		$message->setState($doctrineMessage->getState());
		$message->setPayload($doctrineMessage->getPayload());

		$this->messageRepository->remove($doctrineMessage);
		$this->persistenceManager->persistAll();

		return $message;
	}

	/**
	 * Wait for a message in the queue and save the message to a safety queue
	 *
	 * TODO: Idea for implementing a TTR (time to run) with monitoring of safety queue. E.g.
	 * use different queue names with encoded times? With brpoplpush we cannot modify the
	 * queued item on transfer to the safety queue and we cannot update a timestamp to mark
	 * the run start time in the message, so separate keys should be used for this.
	 *
	 * @param integer $timeout in seconds
	 * @return Message
	 */
	public function waitAndReserve($timeout = NULL) {
		if ($timeout === NULL) {
			sleep($timeout);
		}

		$doctrineMessage = $this->messageRepository->findOneInQueue($this->name);

		if (!$doctrineMessage instanceof DoctrineMessage) {
			return NULL;
		}

		$doctrineMessage->setState(Message::STATE_RESERVED);
		$this->messageRepository->update($doctrineMessage);
		$this->persistenceManager->persistAll();

		$message = new Message();
		$message->setIdentifier($doctrineMessage->getIdentifier());
		$message->setState($doctrineMessage->getState());
		$message->setPayload($doctrineMessage->getPayload());

		return $message;
	}

	/**
	 * Mark a message as finished
	 *
	 * @param Message $message
	 * @return boolean TRUE if the message could be removed
	 */
	public function finish(Message $message) {
		$doctrineMessage = $this->messageRepository->findByIdentifier($message->getIdentifier());
		if ($doctrineMessage instanceof DoctrineMessage) {
			$this->messageRepository->remove($doctrineMessage);
		}
		$message->setState(Message::STATE_DONE);
		return TRUE;
	}

	/**
	 * Peek for messages
	 * NOTE: The beanstalkd implementation only supports to peek the UPCOMING job, so this will throw an exception for
	 * $limit != 1.
	 *
	 * @param integer $limit
	 * @return array Messages or empty array if no messages were present
	 * @throws JobqueueException
	 */
	public function peek($limit = 1) {
		$messages = array();
		foreach ($this->messageRepository->findInQueue($this->name, $limit) as $doctrineMessage) {
			$message = new Message();
			$message->setIdentifier($doctrineMessage->getIdentifier());
			$message->setState($doctrineMessage->getState());
			$message->setPayload($doctrineMessage->getPayload());
			$messages[] = $message;
		}
		return $messages;
	}

	/**
	 * Count messages in the queue
	 *
	 * @return integer
	 */
	public function count() {
		return $this->messageRepository->findInQueue($this->name)->count();
	}

	/**
	 *
	 * @param string $identifier
	 * @return Message
	 */
	public function getMessage($identifier) {
		$doctrineMessage = $this->messageRepository->findByIdentifier($identifier);
		$message = new Message();
		$message->setIdentifier($doctrineMessage->getIdentifier());
		$message->setState($doctrineMessage->getState());
		$message->setPayload($doctrineMessage->getPayload());
		return $message;
	}


}
