<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\EntityRepository;

class OrderItemRepository extends EntityRepository
{
    /**
     * Truncate table
     */
    public function truncate() : void
    {
        // DELETE FROM wp_order_item
        $this->createQueryBuilder('i')->delete()->getQuery()->execute();
    }

    /**
     * @param OrderItem $orderItem
     */
    public function save(OrderItem $orderItem) : void
    {
        // INSERT INTO wp_order_item (`item_name`, `item_price`, `item_postage`, `item_method`, `order_id`)
        //   VALUES ($name, $price, $postage, $method, $orderId)
        // $name = $orderItem->getName();
        // $price = $orderItem->getPrice();
        // $postage = $orderItem->getPostage();
        // $method = $orderItem->getMethod();
        // $orderId = $orderItem->getOrder()->getId();
        if (!$this->_em->contains($orderItem)) {
            $this->_em->persist($orderItem);
        }
        
        $this->_em->flush();
    }

    /**
     * @param OrderItem $orderItem
     */
    public function delete(OrderItem $orderItem) : void
    {
        // DELETE FROM wp_order_item WHERE id = $orderItemId
        // $orderItemId = $orderItem->getId()
        $this->_em->remove($orderItem);
        $this->_em->flush();
    }
}