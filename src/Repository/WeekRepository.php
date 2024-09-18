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
    private $datesLimites = [
        1 => '2024-09-28 19:00:00',  // Semaine 1
        2 => '2024-10-4 19:00:00',  // Semaine 2
        3 => '2024-10-11 19:00:00',  // Semaine 3
        4 => '2024-10-19 18:00:00',  // Semaine 4
        5 => '2024-11-02 19:00:00',  // Semaine 5
        6 => '2024-11-16 19:00:00',  // Semaine 6
        7 => '2024-11-23 19:00:00',  // Semaine 7
        8 => '2024-11-30 18:00:00',  // Semaine 8
        9 => '2024-12-06 19:00:00',  // Semaine 9
        10 => '2024-12-14 18:00:00', // Semaine 10
        11 => '2024-12-21 18:00:00', // Semaine 11

        12 => '2025-01-04 19:00:00', // Semaine 12
        13 => '2025-01-11 19:00:00', // Semaine 13
        14 => '2025-01-24 19:00:00', // Semaine 14

        15 => '2025-01-31 19:00:00', // Semaine 15
        16 => '2025-02-15 20:00:00', // Semaine 16
        17 => '2025-02-21 19:00:00', // Semaine 17
        18 => '2025-03-08 18:00:00', // Semaine 18
        19 => '2025-03-14 19:00:00', // Semaine 19
        20 => '2025-03-21 19:00:00', // Semaine 20
        21 => '2025-03-29 23:00:00', // Semaine 21
        22 => '2025-04-05 23:00:00', // Semaine 22

        23 => '2024-10-05 16:00:00', // Semaine 23
        24 => '2024-10-11 19:00:00', // Semaine 24
        25 => '2024-10-17 23:00:00', // Semaine 25
        26 => '2024-10-31 23:00:00', // Semaine 26
        27 => '2024-11-15 23:00:00', // Semaine 27
        28 => '2024-11-22 19:00:00', // Semaine 28
        29 => '2024-11-30 19:00:00', // Semaine 29
        30 => '2024-12-06 23:00:00', // Semaine 30
        31 => '2024-12-11 19:00:00', // Semaine 31

        32 => '2024-12-20 17:00:00', // Semaine 32

        33 => '2025-01-11 19:00:00', // Semaine 33
        34 => '2025-01-18 19:00:00', // Semaine 34
        35 => '2025-01-24 17:00:00', // Semaine 35
        36 => '2025-02-01 19:00:00', // Semaine 36
        37 => '2025-02-15 17:00:00', // Semaine 37
        38 => '2025-02-22 19:00:00', // Semaine 38
        39 => '2025-03-01 15:00:00', // Semaine 39
        40 => '2025-03-07 19:00:00', // Semaine 40
        41 => '2025-03-15 19:00:00', // Semaine 41
        42 => '2025-03-22 15:00:00', // Semaine 42
        43 => '2025-03-29 19:00:00', // Semaine 43
        44 => '2025-04-05 19:00:00', // Semaine 44
    ];
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Week::class);
    }

    public function findLatestFilledWeek(int $startWeek, int $endWeek): ?Week
{
    return $this->createQueryBuilder('w')
        ->where('w.id >= :startWeek')
        ->andWhere('w.id <= :endWeek')
        ->andWhere('EXISTS (
            SELECT c.id FROM App\Entity\Choice c WHERE c.week = w.id
        )') // Semaine remplie
        ->setParameter('startWeek', $startWeek)
        ->setParameter('endWeek', $endWeek)
        ->orderBy('w.id', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

public function findLatestElapsedWeek(int $startWeek, int $endWeek): ?Week
{
    $currentDateTime = new \DateTime(); // Obtenir la date et heure actuelles

    $lastElapsedWeek = null; // Variable pour stocker la dernière semaine écoulée

    foreach ($this->datesLimites as $weekNumber => $dateLimite) {
        $dateLimite = new \DateTime($dateLimite);
        if ($weekNumber >= $startWeek && $weekNumber <= $endWeek && $dateLimite < $currentDateTime) {
            $lastElapsedWeek = $this->find($weekNumber); // Mettre à jour avec la dernière semaine écoulée
        }
    }

    return $lastElapsedWeek; // Retourner la dernière semaine trouvée ou null si aucune
}
    
}
