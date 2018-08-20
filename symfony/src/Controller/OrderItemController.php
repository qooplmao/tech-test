<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\OrderItemRepository;
use App\Entity\OrderRepository;
use App\Form\Type\OrderItemType;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\GoneHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderItemController
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
     * @param FormFactoryInterface $formFactory
     * @param UrlGeneratorInterface $router
     * @param EngineInterface $templating
     * @param Request $request
     * @param int $orderId
     * @return Response
     */
    public function create(
        FormFactoryInterface $formFactory,
        UrlGeneratorInterface $router,
        EngineInterface $templating,
        Request $request,
        int $orderId
    ) : Response {
        /** @var Order $order */
        if (null === $order = $this->getOrderRepository()->find($orderId)) {
            throw new NotFoundHttpException(sprintf(
                'Order with id "%s" can not be found',
                $orderId
            ));
        }

        $orderItem = new OrderItem();
        $orderItem->setOrder($order);

        $form = $formFactory->createBuilder(OrderItemType::class, $orderItem)->getForm();

        if ($form->handleRequest($request)->isSubmitted() && $form->isValid()) {
            $this->getOrderItemRepository()->save($orderItem);

            return new RedirectResponse($router->generate('orders.show', ['id' => $orderId]));
        }

        return $templating->renderResponse('app/show.html.twig', [
            'order'     => $order,
            'form'      => $form->createView(),
        ]);
    }

    /**
     * @param UrlGeneratorInterface $router
     * @param int $id
     * @param int $orderId
     * @return Response
     */
    public function delete(
        UrlGeneratorInterface $router,
        int $id,
        int $orderId
    ) : Response {
        /** @var OrderItem $orderItem */
        if (null === $orderItem = $this->getOrderItemRepository()->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'Order with id "%s" can not be found',
                $id
            ));
        }

        if ($orderItem->getOrder()->getId() !== $orderId) {
            throw new BadRequestHttpException('Order id in request does not match order connected to order item');
        }

        $this->getOrderItemRepository()->delete($orderItem);

        return new RedirectResponse($router->generate('orders.show', ['id' => $orderId]));
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