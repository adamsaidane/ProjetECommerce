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

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5]
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix',
                'currency' => 'EUR',
                'attr' => ['class' => 'form-control']
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
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
                'attr' => ['class' => 'form-control']
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
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
                'attr' => ['class' => 'form-control']
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
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
