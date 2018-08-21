<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemRepository;
use App\Entity\OrderRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppController
{
    /**
     * @param OrderRepository $orderRepository
     * @param OrderItemRepository $orderItemRepository
     * @param UrlGeneratorInterface $router
     * @param string $csvFile
     * @return Response
     */
    public function init(
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        UrlGeneratorInterface $router,
        string $csvFile
    ) : Response {
        // Clear database (order matters due to foreign key checks)
        $orderItemRepository->truncate();
        $orderRepository->truncate();

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
            if (null === $order = $orderRepository->find($orderId)) {
                $order = Order::create(
                    (int)$orderId,
                    \DateTimeImmutable::createFromFormat('Ymd', $createdAt)->setTime(0, 0, 0)
                );
                $orderRepository->save($order);
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
            $orderItemRepository->save($orderItem);
        }

        return new RedirectResponse($router->generate('orders.index'));
    }
}