<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\ActivityLog;

class DashboardService
{
    public function __construct(private EntityManagerInterface $em) {}

    // -----------------------------
    // KPIs simples
    // -----------------------------
    public function getKPIs(): array
    {
        return [
            'users' => (int) $this->em->getRepository('App\Entity\User')->count([]),
            'artists' => (int) $this->em->getRepository('App\Entity\Artist')->count([]),
            'artworks' => (int) $this->em->getRepository('App\Entity\Artwork')->count([]),
            'ratings' => (int) $this->em->getRepository('App\Entity\Rating')->count([]),
            'connections30d' => (int) $this->em->getRepository('App\Entity\UserLoginLog')
                ->createQueryBuilder('u')
                ->select('COUNT(u.id)')
                ->where('u.createdAt >= :date')
                ->setParameter('date', new \DateTime('-30 days'))
                ->getQuery()
                ->getSingleScalarResult()
        ];
    }

    // -----------------------------
    // Graphiques / stats
    // -----------------------------
    public function getArtworksByMonth(): array
    {
        $conn = $this->em->getConnection();
        $sql = "SELECT TO_CHAR(created_at, 'YYYY-MM') AS month, COUNT(id) AS total
                FROM artwork
                GROUP BY month
                ORDER BY month ASC";
        $result = $conn->executeQuery($sql); // <-- executeQuery() retourne un Result
        return $result->fetchAllAssociative(); // <-- maintenant c'est correct
    }

    public function getConnectionsByDay(): array
    {
        $conn = $this->em->getConnection();
        $sql = "SELECT TO_CHAR(created_at, 'YYYY-MM-DD') AS day, COUNT(id) AS total
                FROM user_login_log
                GROUP BY day
                ORDER BY day ASC";
        $result = $conn->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

    public function getRatingsOverTime(): array
    {
        $conn = $this->em->getConnection();
        $sql = "SELECT TO_CHAR(created_at, 'YYYY-MM') AS month, COUNT(id) AS total
                FROM rating
                GROUP BY month
                ORDER BY month ASC";
        $result = $conn->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

    public function getArtworksDisplayedStats(): array
    {
        $results = $this->em
            ->getRepository('App\Entity\Artwork')
            ->createQueryBuilder('a')
            ->select('a.isDisplay AS isDisplay, COUNT(a.id) AS total')
            ->groupBy('a.isDisplay')
            ->getQuery()
            ->getArrayResult();

        $stats = [
            'displayed' => 0,
            'hidden' => 0,
        ];

        foreach ($results as $row) {
            if ($row['isDisplay'] === true || $row['isDisplay'] === 1) {
                $stats['displayed'] = (int) $row['total'];
            } else {
                $stats['hidden'] = (int) $row['total'];
            }
        }

        return $stats;
    }

    public function getStylesStats(): array
    {
        $qb = $this->em->getRepository('App\Entity\Artwork')->createQueryBuilder('a');
        $qb->select('a.style, COUNT(a.id) as total')
           ->groupBy('a.style');
        return $qb->getQuery()->getArrayResult();
    }

    public function getNationalitiesStats(): array
    {
        $qb = $this->em->getRepository('App\Entity\Artist')->createQueryBuilder('a');
        $qb->select('a.nationality, COUNT(a.id) as total')
           ->groupBy('a.nationality');
        return $qb->getQuery()->getArrayResult();
    }

    public function getGalleriesPublicPrivate(): array
    {
        $qb = $this->em->getRepository('App\Entity\Gallery')->createQueryBuilder('g');
        $qb->select('g.isPublic, COUNT(g.id) as total')
           ->groupBy('g.isPublic');
        return $qb->getQuery()->getArrayResult();
    }

    // -----------------------------
    // Tables "latest"
    // -----------------------------
    public function getLatestConnections(int $limit = 10): array
    {
        return $this->em->getRepository('App\Entity\UserLoginLog')
            ->findBy([], ['createdAt' => 'DESC'], $limit);
    }

    public function getLatestArtworks(int $limit = 10): array
    {
        return $this->em->getRepository('App\Entity\Artwork')
            ->findBy([], ['createdAt' => 'DESC'], $limit);
    }

    public function getLatestAdminActions(int $limit = 10): array
    {
        $sql = '
            SELECT a.*
            FROM activity_log a
            INNER JOIN "user" u ON a.user_connected_id = u.id
            WHERE u.roles @> :role
            ORDER BY a.created_at DESC
            LIMIT '.$limit.'
        ';

        $conn = $this->em->getConnection();
        $result = $conn->executeQuery($sql, 
        [
            'role' => json_encode(['ROLE_ADMIN'])
        ],
        [
            'role' => \PDO::PARAM_STR
        ]);

        return $result->fetchAllAssociative();
    }
}
