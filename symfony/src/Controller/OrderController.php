<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemRepository;
use App\Entity\OrderRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderController
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
     * @param EngineInterface $templating
     * @param Request $request
     * @return Response
     */
    public function index(
        EngineInterface $templating,
        Request $request
    ) : Response {
        $page = (int) $request->query->get('page', 1);

        return $templating->renderResponse('app/index.html.twig', [
            'orders'    => $this->getOrderRepository()->createFilterPaginator($page),
        ]);
    }

    /**
     * @param EngineInterface $templating
     * @param int $id
     * @return Response
     */
    public function show(
        EngineInterface $templating,
        int $id
    ) : Response {
        if (null === $order = $this->getOrderRepository()->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'Order with id "%s" can not be found',
                $id
            ));
        }

        return $templating->renderResponse('app/show.html.twig', [
            'order'     => $order,
        ]);
    }

    /**
     * @param UrlGeneratorInterface $router
     * @return Response
     * @throws \Exception
     */
    public function create(
        UrlGeneratorInterface $router
    ) : Response {
        $order = $this->getOrderRepository()->create();
        $this->getOrderRepository()->save($order);

        return new RedirectResponse($router->generate('orders.show', ['id' => $order->getId()]));
    }

    /**
     * @param UrlGeneratorInterface $router
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(
        UrlGeneratorInterface $router,
        int $id
    ) : RedirectResponse {
        /** @var Order $order */
        if (null === $order = $this->getOrderRepository()->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'Order with id "%s" can not be found',
                $id
            ));
        }

        foreach ($order->getItems() as $orderItem) {
            $this->getOrderItemRepository()->delete($orderItem);
        }

        $this->getOrderRepository()->delete($order);

        return new RedirectResponse($router->generate('orders.index'));
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