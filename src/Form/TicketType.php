<?php

namespace App\Form;

use App\Controller\GLPIController;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TicketType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Objet'
            ])
            ->add('logiciel', ChoiceType::class, [
                'choices' => $this->getSoftware(),
                'required' => true
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('piece', FileType::class, [
                'required' => false,
                'label' => 'Pièce jointe'
            ])
            ->add('note', TextareaType::class, [
                'label' => 'Ajouter une note :'
            ])
        ;
    }

    private function getSoftware()
    {
        try{
            $glpi = new GLPIController();
        $cat = $glpi->getItem('Software', null, 'name', 'ASC');
        $cat = is_array($cat) ? $cat : [$cat];

        $output = [];
        foreach ($cat as $item) {
            $output[$item->name] = $item->id;
        }
        return $output;
        }
        catch (RequestException $e) {
            return $e->getResponse();
        }
    }

}
