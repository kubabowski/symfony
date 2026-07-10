<?php

namespace App\Form;

use App\Entity\ShopOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('customerName', TextType::class, [
                'label' => 'Full name',
                'constraints' => [new Assert\NotBlank(), new Assert\Length(max: 255)],
            ])
            ->add('customerEmail', EmailType::class, [
                'label' => 'Email',
                'constraints' => [new Assert\NotBlank(), new Assert\Email()],
            ])
            ->add('customerPhone', TelType::class, [
                'label' => 'Phone',
                'required' => false,
            ])
            ->add('addressLine1', TextType::class, [
                'label' => 'Address',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('addressLine2', TextType::class, [
                'label' => 'Address (cont.)',
                'required' => false,
            ])
            ->add('city', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'Postal code',
                'constraints' => [new Assert\NotBlank()],
            ])
            ->add('country', TextType::class, [
                'constraints' => [new Assert\NotBlank()],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ShopOrder::class]);
    }
}