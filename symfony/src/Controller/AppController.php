<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemRepository;
use App\Entity\OrderRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppController
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @param ManagerRegistry $managerRegistry
     */
    public function __construct(
        ManagerRegistry $managerRegistry
    ) {
        $this->managerRegistry = $managerRegistry;
    }

    /**
     * @param UrlGeneratorInterface $router
     * @param string $csvFile
     * @return Response
     */
    public function init(
        UrlGeneratorInterface $router,
        string $csvFile
    ) : Response {
        // Clear database
        $this->getOrderItemRepository()->truncate();
        $this->getOrderRepository()->truncate();

        if (!file_exists($csvFile)) {
            throw new GoneHttpException(sprintf(
                'Test data file at path "%s" can not be found',
                $csvFile
            ));
        }

        if (false === $handle = fopen($csvFile, 'r')) {
            throw new GoneHttpException(sprintf(
                'Test data file at path "%s" can not be opened',
                $csvFile
            ));
        }

        // Loop through lines in csv file
        while (false !== $row = fgetcsv($handle, 1000, ',')) {
            // Destructure row to variables
            [
                $orderId,
                $name,
                $createdAt,
                $price,
                $postage,
                $method
            ] = $row;

            // ignore header row
            if ('id' === $orderId) {
                continue;
            }

            // Get order or create new
            if (null === $order = $this->getOrderRepository()->find($orderId)) {
                $order = Order::create(
                    (int)$orderId,
                    \DateTimeImmutable::createFromFormat('Ymd', $createdAt)->setTime(0, 0, 0)
                );
                $this->getOrderRepository()->save($order);
            }

            // Convert price and postage to integer rather than deal with floats
            // Ideally a Value Object like moneyphp/money would be used
            $price = (int) ($price * 100);
            $postage = $postage !== '-' && '0' !== $postage ? (int) ($postage * 100) : null;
            $method = $method !== '-' ? $method : null;

            // Create order item and store
            $orderItem = OrderItem::create(
                $order,
                $name,
                $price,
                $postage,
                $method
            );
            $this->getOrderItemRepository()->save($orderItem);
        }

        return new RedirectResponse($router->generate('index'));
    }

    /**
     * @param string $className
     * @return OrderRepository|OrderItemRepository|ObjectRepository
     */
    private function getRepository(string $className) : EntityRepository
    {
        return $this->managerRegistry->getManagerForClass($className)->getRepository($className);
    }

    /**
     * @return OrderRepository
     */
    private function getOrderRepository() : OrderRepository
    {
        return $this->getRepository(Order::class);
    }

    /**
     * @return OrderItemRepository
     */
    private function getOrderItemRepository() : OrderItemRepository
    {
        return $this->getRepository(OrderItem::class);
    }
}