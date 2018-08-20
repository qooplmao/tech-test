<?php declare(strict_types=1);

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Entity\OrderRepository")
 * @ORM\Table(name="wb_order")
 */
class Order
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(name="created_at", type="date_immutable")
     *
     * @var \DateTimeImmutable
     */
    private $createdAt;

    /**
     * @ORM\OneToMany(targetEntity=OrderItem::class, mappedBy="order")
     *
     * @var Collection|OrderItem[]
     */
    private $items;

    /**
     * @var array|int[]
     */
    private $prices = [];

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt() : \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return int
     */
    public function getTotal() : int
    {
        $this->initPrices();

        return array_sum($this->prices);
    }

    /**
     * @return int
     */
    public function getMeanPrice() : int
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        return (int) ($this->getTotal() / $this->items->count());
    }

    /**
     * @return int
     */
    public function getMedianPrice() : int
    {
        if ($this->items->isEmpty()) {
            return 0;
        }

        $this->initPrices();

        // If number of items is odd get middle value
        if (1 === count($this->prices) % 2) {
            $key = (int) round(count($this->prices) / 2, 0, PHP_ROUND_HALF_DOWN);

            return $this->prices[$key];
        }

        // If number of items is even get the mean of the middle 2 values
        $key1 = (int) (count($this->prices) / 2) - 1;
        $key2 = $key1 + 1;

        return (int) (($this->prices[$key1] + $this->prices[$key2]) / 2);
    }

    /**
     * @return Collection|OrderItem[]
     */
    public function getItems() : Collection
    {
        return $this->items;
    }

    /**
     * @param OrderItem $orderItem
     */
    public function addItem(OrderItem $orderItem) : void
    {
        if (!$this->items->contains($orderItem)) {
            $this->items->add($orderItem);
        }
    }

    /**
     * @param int $id
     * @param \DateTimeImmutable $createdAt
     * @return Order
     */
    public static function create(
        int $id,
        \DateTimeImmutable $createdAt
    ) : Order {
        $order = new static();
        $order->id = $id;
        $order->createdAt = $createdAt;

        return $order;
    }

    /**
     * Initialise array of prices for order items
     */
    private function initPrices() : void
    {
        if (!empty($this->prices) || $this->items->isEmpty()) {
            return;
        }

        foreach ($this->items as $orderItem) {
            $this->prices[] = $orderItem->getPrice();
        }

        sort($this->prices);
    }
}