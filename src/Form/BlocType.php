<?php

namespace App\Form;

use App\Controller\GLPIController;
use App\Entity\Bloc;
use App\Repository\BlocRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlocType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('libelle')
            ->add('parent', EntityType::class, [
                'required' => false,
                'class' => Bloc::class,
                'choice_label' => 'libelle'
            ])
            ->add('description')
            ->add('imageFile', FileType::class, [
                'required' => false
            ])
            ->add('ordre', null, [
                'required' => false
            ])
            ->add('glpicategory', ChoiceType::class, [
                'choices' => $this->getItilCategories(),
                'required' => false
            ])
            ->add('information')
            ->add('type', ChoiceType::class, [
                'choices' => $this->getTypes()
            ])
            ->add('affiche')
            ->add('logiciel')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Bloc::class,
            'translation_domain' => 'forms'
        ]);
    }

    private function getTypes()
    {
        $types = Bloc::TYPETICKET;
        $output = [];
        foreach ($types as $k => $v) {
            $output[$v] = $k;
        }
        return $output;
    }

    private function getItilCategories()
    {
        $glpi = new GLPIController();
        $cat = $glpi->getItem('ItilCategory', null, 'name', 'ASC');
        $cat = is_array($cat) ? $cat : [$cat];

        $output = [];
        foreach ($cat as $item) {
            $output[$item->name] = $item->id;
        }
        return $output;
    }

}
