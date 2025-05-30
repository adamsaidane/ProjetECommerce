<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\NotBlank;

class CheckoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations de livraison
            ->add('shipping_first_name', TextType::class, [
                'label' => 'Prénom',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('shipping_last_name', TextType::class, [
                'label' => 'Nom',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('shipping_address', TextareaType::class, [
                'label' => 'Adresse',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('shipping_postal_code', TextType::class, [
                'label' => 'Code postal',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('shipping_city', TextType::class, [
                'label' => 'Ville',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control']
            ])
            ->add('shipping_country', ChoiceType::class, [
                'label' => 'Pays',
                'choices' => [
                    'Tunisia' => 'Tunisia',
                    'Morocco' => 'Morocco',
                    'Algeria' => 'Algeria',
                ],
                'data' => 'Tunisia',
                'attr' => ['class' => 'form-control']
            ])
            ->add('shipping_phone', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])

            // Case à cocher pour utiliser la même adresse pour la facturation
            ->add('same_billing_address', CheckboxType::class, [
                'label' => 'Utiliser la même adresse pour la facturation',
                'required' => false,
                'data' => true,
                'attr' => ['class' => 'form-check-input']
            ])

            // Informations de facturation (optionnelles si même adresse)
            ->add('billing_first_name', TextType::class, [
                'label' => 'Prénom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('billing_last_name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('billing_address', TextareaType::class, [
                'label' => 'Adresse',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3]
            ])
            ->add('billing_postal_code', TextType::class, [
                'label' => 'Code postal',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('billing_city', TextType::class, [
                'label' => 'Ville',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('billing_country', ChoiceType::class, [
                'label' => 'Pays',
                'choices' => [
                    'Tunisia' => 'Tunisia',
                    'Morocco' => 'Morocco',
                    'Algeria' => 'Algeria',
                ],
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])

            // Méthode de paiement
            ->add('payment_method', ChoiceType::class, [
                'label' => 'Méthode de paiement',
                'choices' => [
                    'Carte bancaire' => 'card',
                    'PayPal' => 'paypal',
                    'Virement bancaire' => 'transfer',
                ],
                'expanded' => true,
                'data' => 'card',
                'attr' => ['class' => 'form-check-input']
            ])

            // Notes de commande
            ->add('order_notes', TextareaType::class, [
                'label' => 'Notes de commande (optionnel)',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 3,
                    'placeholder' => 'Instructions spéciales pour la livraison...'
                ]
            ])

            // Acceptation des conditions
            ->add('accept_terms', CheckboxType::class, [
                'label' => 'J\'accepte les conditions générales de vente',
                'constraints' => [
                    new IsTrue(['message' => 'Vous devez accepter les conditions générales de vente.'])
                ],
                'attr' => ['class' => 'form-check-input']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
