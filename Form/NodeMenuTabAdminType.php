<?php

namespace Kunstmaan\NodeBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * NodeMenuTabAdminType
 */
class NodeMenuTabAdminType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('hiddenFromNav', 'checkbox', array('label' => 'Hidden from menu', 'required' => false));
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'menu';
    }

    /**
     * @param array $options
     *
     * @return array
     */
    public function getDefaultOptions(array $options)
    {
        return array(
            'data_class' => 'Kunstmaan\NodeBundle\Entity\Node',
        );
    }
}
