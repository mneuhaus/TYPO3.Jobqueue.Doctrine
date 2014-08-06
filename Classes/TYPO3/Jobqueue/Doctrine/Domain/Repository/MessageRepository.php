<?php
namespace TYPO3\Jobqueue\Doctrine\Domain\Repository;

/*                                                                          *
 * This script belongs to the TYPO3 Flow package "TYPO3.Jobqueue.Doctrine". *
 *                                                                          *
 *                                                                          */

use TYPO3\Docs\RenderingHub\Domain\Model\Document;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Persistence\QueryInterface;
use TYPO3\Jobqueue\Common\Queue\Message;

/**
 * A repository for Messages
 *
 * @Flow\Scope("singleton")
 */
class MessageRepository extends \TYPO3\Flow\Persistence\Repository {
	public function findOneInQueue($queueName) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('queue', $queueName),
				$query->equals('state', Message::STATE_PUBLISHED)
			)
		);
		$query->setLimit(1);
		return $query->execute()->getFirst();
	}

	public function findInQueue($queueName, $limit = NULL) {
		$query = $this->createQuery();
		$query->matching(
			$query->logicalAnd(
				$query->equals('queue', $queueName),
				$query->equals('state', Message::STATE_PUBLISHED)
			)
		);
		if ($limit !== NULL) {
			$query->setLimit(1);
		}
		return $query->execute();
	}
}
?>