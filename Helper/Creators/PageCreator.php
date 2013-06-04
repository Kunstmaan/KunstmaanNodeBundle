<?php

namespace Kunstmaan\NodeBundle\Helper\Services;

use Doctrine\ORM\EntityManager;

use Kunstmaan\NodeBundle\Entity\HasNodeInterface;
use Kunstmaan\NodeBundle\Entity\Node;
use Kunstmaan\NodeBundle\Event\Events;
use Kunstmaan\NodeBundle\Event\NodeEvent;
use Kunstmaan\NodeBundle\Repository\NodeRepository;
use Kunstmaan\NodeBundle\Repository\NodeTranslationRepository;
use Kunstmaan\AdminBundle\Entity\User as Baseuser;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Service to create new pages.
 */
class PageCreatorService
{

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param EntityManager            $em
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(EntityManager $em, EventDispatcherInterface $eventDispatcher)
    {
        $this->em = $em;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param string           $locale
     * @param HasNodeInterface $pageInstance
     * @param array            $options
     *        possible options are:
     *          owner* (User), parent (Node|HasNodeInterface), node (Node), online (Boolean), internal_name (string)
     *          page_setter (Callable), node_setter (Callable), node_translation_setter (Callable)
     *
     * @return array (page, node, nodeTranslation, nodeVersion)
     * @throws \InvalidArgumentException
     */
    public function createPage($locale, HasNodeInterface $pageInstance, $options)
    {
        $options = array_merge(array(
            'owner' => null,
            'parent' => null,
            'node' => null,
            'online' => false,
            'internal_name' => '',
            'page_setter' => null,
            'node_setter' => null,
            'node_translation_setter' => null,
        ), $options);

        if (is_null($options['owner'])) {
            throw new \InvalidArgumentException('Owner must be specified in the options array');
        }

        /** @var BaseUser $owner */
        $owner = $options['owner'];
        /** @var HasNodeInterface|Node $parent */
        $parent = $options['parent'];
        /** @var Node $node */
        $node = $options['node'];
        $online = $options['online'];
        $internalName = $options['internal_name'];

        $newPage = clone $pageInstance;
        if (is_callable($options['page_setter'])) {
            $options['page_setter']($newPage, $locale);
        }
        $this->em->persist($newPage);
        $this->em->flush();

        if (!is_null($parent)) {
            if ($parent instanceof Node) {
                $parent = $parent->getNodeTranslation($locale)->getRef($this->em);
            }
            if ($parent instanceof HasNodeInterface) {
                $newPage->setParent($parent);
            } else {
                throw new \InvalidArgumentException('Parent must be an instance of HasNodeInterface or Node');
            }
        }

        if (is_null($node)) {
            $node = $this->getNodeRepository()->createNodeFor($newPage, $locale, $owner, $internalName);
            $nodeTranslation = $node->getNodeTranslation($locale, true);
        } else if ($node instanceof Node) {
            $nodeTranslation = $node->getNodeTranslation($locale, true);
            if (is_null($nodeTranslation)) {
                $nodeTranslation = $this->getNodeTranslationRepository()->createNodeTranslationFor($newPage, $locale, $node, $owner);
            }
        } else {
            throw new \InvalidArgumentException('Node should null or an instanceof Node');
        }

        if ($online) {
            $nodeTranslation->setOnline($online);
            $this->em->persist($nodeTranslation);
        }
        if ($newPage->isStructureNode()) {
            $nodeTranslation->setSlug('');
            $this->em->persist($nodeTranslation);
        }

        if (is_callable($options['node_setter'])) {
            $options['node_setter']($node, $locale);
            $this->em->persist($node);
        }

        if (is_callable($options['node_translation_setter'])) {
            $options['node_translation_setter']($nodeTranslation, $locale);
            $this->em->persist($nodeTranslation);
        }

        $this->em->flush();

        $nodeVersion = $nodeTranslation->getPublicNodeVersion();

        $this->eventDispatcher->dispatch(Events::ADD_NODE, new NodeEvent($node, $nodeTranslation, $nodeVersion, $newPage));

        return array($newPage, $node, $nodeTranslation, $nodeVersion);
    }

    /**
     * @param HasNodeInterface $translationInstance
     * @param array            $locales
     * @param array            $options
     *        possible options are:
     *          owner* (User), parent (Node|HasNodeInterface), node (Node), online (Boolean), internal_name (string)
     *          page_setter (Callable), node_setter (Callable), node_translation_setter (Callable)
     *
     * @return array (page, node, nodeTranslation, nodeVersion)
     */
    public function createMultiLanguagePage(HasNodeInterface $translationInstance, $locales, $options)
    {
        $result = array();
        $baseNode = null;

        foreach ($locales as $locale) {
            list ($page, $node, $nodeTranslation, $nodeVersion) = $this->createPage($locale, $translationInstance, array_merge(array(
                'node' => $baseNode
            ), $options));

            $baseNode = $node;
            $result[$locale] = array($page, $node, $nodeTranslation, $nodeVersion);
        }

        return $result;
    }

    /**
     * @var NodeRepository
     */
    private $nodeRepo;

    /**
     * @return NodeRepository
     */
    private function getNodeRepository()
    {
        if (is_null($this->nodeRepo)) {
            $this->nodeRepo = $this->em->getRepository('KunstmaanNodeBundle:Node');
        }

        return $this->nodeRepo;
    }

    /**
     * @var NodeTranslationRepository
     */
    private $nodeTranslationRepo;

    /**
     * @return NodeTranslationRepository
     */
    private function getNodeTranslationRepository()
    {
        if (is_null($this->nodeRepo)) {
            $this->nodeTranslationRepo = $this->em->getRepository('KunstmaanNodeBundle:NodeTranslation');
        }

        return $this->nodeTranslationRepo;
    }

}
