<?php


namespace App\Form\Type;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ValidateCart extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label'    => 'Nom',
                'required' => true,
            ])
            ->add('prenom', TextType::class, [
                'label'    => 'Prénom',
                'required' => true,
            ])
            ->add('mail', TextType::class, [
                'label'    => 'Adresse email',
                'required' => true,
            ])
            ->add('tel', TextType::class, [
                'label'    => 'Numéro de téléphone',
                'required' => true,
            ])
            ->add('adresse', TextType::class, [
                'label'    => 'Adresse',
                'required' => true,
            ])
            ->add('codepostal', TextType::class, [
                'label'    => 'Code postal',
                'required' => true,
            ])
            ->add('ville', TextType::class, [
                'label'    => 'Ville',
                'required' => true,
            ])
            ->add('account', CheckboxType::class, [
                'label'    => 'Créer un compte client',
                'required' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Valider ma commande'
            ])
        ;
    }
}