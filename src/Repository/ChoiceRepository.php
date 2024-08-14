<?php

namespace App\Repository;

use App\Entity\Choice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Choice>
 *
 * @method Choice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Choice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Choice[]    findAll()
 * @method Choice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ChoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Choice::class);
    }

    /**
     * Finds choices for a player within a range of weeks.
     *
     * @param int $playerId
     * @param array $weekIds
     * @param int $userId
     *
     * @return Choice[] Returns an array of Choice objects
     */
    public function findChoicesForPlayerInWeeks(int $playerId, array $weekIds, int $userId): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.week', 'w')
            ->where('c.player = :playerId')
            ->andWhere('c.user = :userId')
            ->andWhere('w.id IN (:weekIds)')
            ->setParameter('playerId', $playerId)
            ->setParameter('userId', $userId)
            ->setParameter('weekIds', $weekIds)
            ->getQuery()
            ->getResult();
    }
}
