<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\EntityRepository;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

class OrderRepository extends EntityRepository
{
    /**
     * @param int $page
     * @return Pagerfanta
     */
    public function createFilterPaginator(int $page = 1) : Pagerfanta
    {
        // SELECT id, created_at FROM wb_order ORDER BY created_at ASC LIMIT $offset, 10
        // $offset = 1 === $page ? 0 : 10 * ($page - 1)
        $queryBuilder = $this
            ->createQueryBuilder('o')
            ->orderBy('o.createdAt', 'ASC')
        ;

        $paginator = new Pagerfanta(new DoctrineORMAdapter($queryBuilder));
        $paginator->setMaxPerPage(10);
        $paginator->setCurrentPage(0 < $page ? $page : 1);

        return $paginator;
    }

    /**
     * Truncate table
     */
    public function truncate() : void
    {
        // Requires wp_order_item being truncated before due to foreign key checks
        // DELETE from wb_order
        $this->createQueryBuilder('o')->delete()->getQuery()->execute();
    }

    /**
     * @param Order $order
     */
    public function save(Order $order) : void
    {
        // INSERT INTO wp_order (`order_id`, `created_at`) VALUED ($orderId, $createdAt)
        // $orderId = $order->getId()
        // $createdAt = $order->getCreatedAt->format('Y-m-d')

        if (!$this->_em->contains($order)) {
            $this->_em->persist($order);
        }
        
        $this->_em->flush();
    }

    /**
     * @return Order
     */
    public function create() : Order
    {
        // Get last id recorded on database
        // SELECT id FROM wp_order ORDER id DESC LIMIT 1
        $latestId = $this
            ->createQueryBuilder('o')
            ->select('o.id')
            ->orderBy('o.id', 'DESC')
            ->setMaxResults('1')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (null !== $latestId) {
            $orderId = ((int) $latestId['id']) + 1;
        } else {
            $orderId = 12345;
        }

        return Order::create($orderId, new \DateTimeImmutable());
    }

    /**
     * @param Order $order
     */
    public function delete(Order $order) : void
    {
        // DELETE FROM wp_order WHERE id = $orderId
        // $orderId = $order->getId()
        $this->_em->remove($order);
        $this->_em->flush();
    }
}