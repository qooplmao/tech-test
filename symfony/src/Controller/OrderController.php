<?php declare(strict_types=1);

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItemRepository;
use App\Entity\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OrderController
{
    /**
     * @param OrderRepository $orderRepository
     * @param EngineInterface $templating
     * @param Request $request
     * @return Response
     */
    public function index(
        OrderRepository $orderRepository,
        EngineInterface $templating,
        Request $request
    ) : Response {
        $page = (int) $request->query->get('page', 1);

        return $templating->renderResponse('app/index.html.twig', [
            'orders'    => $orderRepository->createFilterPaginator($page),
        ]);
    }

    /**
     * @param OrderRepository $orderRepository
     * @param EngineInterface $templating
     * @param int $id
     * @return Response
     */
    public function show(
        OrderRepository $orderRepository,
        EngineInterface $templating,
        int $id
    ) : Response {
        if (null === $order = $orderRepository->find($id)) {
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
     * @param OrderRepository $orderRepository
     * @param UrlGeneratorInterface $router
     * @return Response
     */
    public function create(
        OrderRepository $orderRepository,
        UrlGeneratorInterface $router
    ) : Response {
        $order = $orderRepository->create();
        $orderRepository->save($order);

        return new RedirectResponse($router->generate('orders.show', ['id' => $order->getId()]));
    }

    /**
     * @param OrderRepository $orderRepository
     * @param OrderItemRepository $orderItemRepository
     * @param UrlGeneratorInterface $router
     * @param int $id
     * @return RedirectResponse
     */
    public function delete(
        OrderRepository $orderRepository,
        OrderItemRepository $orderItemRepository,
        UrlGeneratorInterface $router,
        int $id
    ) : RedirectResponse {
        /** @var Order $order */
        if (null === $order = $orderRepository->find($id)) {
            throw new NotFoundHttpException(sprintf(
                'Order with id "%s" can not be found',
                $id
            ));
        }

        foreach ($order->getItems() as $orderItem) {
            $orderItemRepository->delete($orderItem);
        }

        $orderRepository->delete($order);

        return new RedirectResponse($router->generate('orders.index'));
    }
}