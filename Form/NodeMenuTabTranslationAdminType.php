<?php

namespace Kunstmaan\NodeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * NodeMenuTabTranslationAdminType
 */
class NodeMenuTabTranslationAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('slug', 'slug', array('required' => false));
        $builder->add(
            'weight',
            'choice',
            array(
                'choices'     => array_combine(range(-50, 50), range(-50, 50)),
                'empty_value' => false,
                'required' => false,
                'attr' => array('title' => 'Used to reorder the pages.')
            )
        );
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'menutranslation';
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Kunstmaan\NodeBundle\Entity\NodeTranslation',
        );
    }
}
