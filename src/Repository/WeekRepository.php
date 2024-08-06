<?php

namespace App\Repository;

use App\Entity\Week;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Week>
 *
 * @method Week|null find($id, $lockMode = null, $lockVersion = null)
 * @method Week|null findOneBy(array $criteria, array $orderBy = null)
 * @method Week[]    findAll()
 * @method Week[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WeekRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Week::class);
    }
}
