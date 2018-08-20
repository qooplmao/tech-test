<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Entity\OrderItemRepository")
 * @ORM\Table(name="wb_order_item")
 */
class OrderItem
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="item_name", type="string", length=64)
     * @Assert\NotBlank
     * @Assert\Length(min=1, max=64)
     *
     * @var string
     */
    private $name;

    /**
     * @ORM\Column(name="item_price", type="integer", nullable=true)
     * @Assert\NotBlank
     * @Assert\GreaterThan(0);
     *
     * @var int|null
     */
    private $price;

    /**
     * @ORM\Column(name="item_postage", type="integer", nullable=true)
     * @Assert\GreaterThan(0)
     *
     * @var int|null
     */
    private $postage;

    /**
     * @ORM\Column(name="item_method", type="string", length=32, nullable=true)
     *
     * @var string|null
     */
    private $method;

    /**
     * @ORM\ManyToOne(targetEntity=Order::class, inversedBy="items")
     *
     * @var Order
     */
    private $order;

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName() : ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName(string $name) : void
    {
        $this->name = $name;
    }

    /**
     * @return int|null
     */
    public function getPrice() : ?int
    {
        return $this->price;
    }

    /**
     * @param int|null $price
     * @return void
     */
    public function setPrice(int $price = null) : void
    {
        $this->price = $price;
    }

    /**
     * @return int|null
     */
    public function getPostage() : ?int
    {
        return $this->postage;
    }

    /**
     * @param int|null $postage
     * @return void
     */
    public function setPostage(int $postage = null) : void
    {
        $this->postage = $postage;
    }

    /**
     * @return null|string
     */
    public function getMethod() : ?string
    {
        return $this->method;
    }

    /**
     * @param null|string $method
     * @return void
     */
    public function setMethod(string $method = null) : void
    {
        $this->method = $method;
    }

    /**
     * @return Order
     */
    public function getOrder() : Order
    {
        return $this->order;
    }

    /**
     * @param Order $order
     * @return void
     */
    public function setOrder(Order $order) : void
    {
        $this->order = $order;
    }

    /**
     * @param Order $order
     * @param string $name
     * @param int|null $price
     * @param int|null $postage
     * @param string|null $method
     * @return OrderItem
     */
    public static function create(
        Order $order,
        string $name,
        int $price = null,
        int $postage = null,
        string $method = null
    ) : OrderItem {
        $orderItem = new static();
        $orderItem->setName($name);
        $orderItem->setPrice($price);
        $orderItem->setPostage($postage);
        $orderItem->setMethod($method);
        $orderItem->setOrder($order);

        $order->addItem($orderItem);

        return $orderItem;
    }
}