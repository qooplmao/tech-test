<?php declare(strict_types=1);

namespace App\Form\Type;

use App\Entity\OrderItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('price', MoneyType::class, [
                'currency'  => 'GBP',
                'divisor'   => 100,
            ])
            ->add('postage', MoneyType::class, [
                'currency'  => 'GBP',
                'divisor'   => 100,
                'required'  => false,
            ])
            ->add('method', ChoiceType::class, [
                'choices'   => [
                    'Accessory' => 'accessory',
                    'Assembled' => 'assembled',
                    'Boxed'     => 'boxed',
                    'Service'   => 'service',
                ],
                'placeholder'   => 'FREE',
                'required'  => false,
            ])

            ->add('submit', SubmitType::class)
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefault('data_class', OrderItem::class)
        ;
    }
}