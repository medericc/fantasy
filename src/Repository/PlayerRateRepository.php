<?php

namespace App\Repository;

use App\Entity\PlayerRate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PlayerRate>
 *
 * @method PlayerRate|null find($id, $lockMode = null, $lockVersion = null)
 * @method PlayerRate|null findOneBy(array $criteria, array $orderBy = null)
 * @method PlayerRate[]    findAll()
 * @method PlayerRate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PlayerRateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlayerRate::class);
    }

    // Ajoute ici des méthodes personnalisées si nécessaire

    public function findRateForPlayerInWeek(int $playerId, int $weekId): ?PlayerRate
    {
        return $this->createQueryBuilder('pr')
            ->andWhere('pr.player = :playerId')
            ->andWhere('pr.week = :weekId')
            ->setParameter('playerId', $playerId)
            ->setParameter('weekId', $weekId)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
