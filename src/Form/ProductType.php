<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Positive;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un titre']),
                    new Length([
                        'min' => 3,
                        'max' => 255,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le titre ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une description']),
                    new Length([
                        'min' => 10,
                        'minMessage' => 'La description doit contenir au moins {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un prix']),
                    new Positive(['message' => 'Le prix doit être positif'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer un auteur']),
                    new Length([
                        'min' => 2,
                        'max' => 100,
                        'minMessage' => 'Le nom de l\'auteur doit contenir au moins {{ limit }} caractères',
                        'maxMessage' => 'Le nom de l\'auteur ne peut pas dépasser {{ limit }} caractères'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('publicationYear', IntegerType::class, [
                'label' => 'Année de publication',
                'required' => false,
                'constraints' => [
                    new GreaterThanOrEqual([
                        'value' => 1000,
                        'message' => 'L\'année de publication doit être supérieure à {{ compared_value }}'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('publisher', TextType::class, [
                'label' => 'Éditeur',
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('pages', IntegerType::class, [
                'label' => 'Nombre de pages',
                'required' => false,
                'constraints' => [
                    new Positive(['message' => 'Le nombre de pages doit être positif'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('bookcondition', ChoiceType::class, [
                'label' => 'État',
                'choices' => [
                    'Neuf' => 'neuf',
                    'Très bon état' => 'tres_bon',
                    'Bon état' => 'bon',
                    'État correct' => 'correct',
                    'Mauvais état' => 'mauvais'
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner un état'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez entrer une quantité en stock']),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'Le stock ne peut pas être négatif'
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input']
            ])
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'constraints' => [
                    new NotBlank(['message' => 'Veuillez sélectionner une catégorie'])
                ],
                'attr' => ['class' => 'form-control']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger une image valide (JPEG, PNG, WebP)',
                    ])
                ],
                'attr' => ['class' => 'form-control']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
