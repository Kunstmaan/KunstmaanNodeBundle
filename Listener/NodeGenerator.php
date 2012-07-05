<?php

namespace Kunstmaan\AdminNodeBundle\Listener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Kunstmaan\AdminNodeBundle\Entity\Node;
use Kunstmaan\AdminNodeBundle\Entity\HasNodeInterface;
use Kunstmaan\AdminBundle\Modules\ClassLookup;
use Kunstmaan\AdminBundle\Modules\Slugifier;
// see http://inchoo.net/tools-frameworks/symfony2-event-listeners/

class NodeGenerator {

    public function postUpdate(LifecycleEventArgs $args) {
        $this->updateNode($args);
    }

    public function postPersist(LifecycleEventArgs $args) {
    }

    public function updateNode(LifecycleEventArgs $args){
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $classname = ClassLookup::getClass($entity);
        if($entity instanceof HasNodeInterface){
            $entityrepo = $em->getRepository($classname);
            $nodeVersion = $em->getRepository('KunstmaanAdminNodeBundle:NodeVersion')->getNodeVersionFor($entity);
            if($nodeVersion!=null){
	             $nodeTranslation = $nodeVersion->getNodeTranslation();
                 $publicNodeVersion = $nodeTranslation->getPublicNodeVersion();
	             if( $publicNodeVersion && $publicNodeVersion->getId() == $nodeVersion->getId()){
		             $nodeTranslation->setTitle($entity->__toString());
		             $nodeTranslation->setOnline($entity->isOnline());
		             $em->persist($nodeTranslation);
		             $em->flush();
	             }
            }
        }
    }

    public function prePersist(LifecycleEventArgs $args) {

    }

    public function preRemove(LifecycleEventArgs $args) {
	    /*$entity = $args->getEntity();
		$em = $args->getEntityManager();
		$classname = ClassLookup::getClass($entity);
		if($entity instanceof HasNodeInterface){
		$entityrepo = $em->getRepository($classname);
		$node = $this->getNode($em, $entity->getId(), $classname);
		$em->remove($node);
		}*/
    }

    public function postLoad(LifecycleEventArgs $args) {
     	$entity = $args->getEntity();
     	$em = $args->getEntityManager();
     	$classname = ClassLookup::getClass($entity);
     	if($entity instanceof HasNodeInterface){
     		$nodeVersion = $em->getRepository('KunstmaanAdminNodeBundle:NodeVersion')->findOneBy(array('refId' => $entity->getId(), 'refEntityname' => $classname));
     		if($nodeVersion){
     			$nodeTranslation = $nodeVersion->getNodeTranslation();
     			$node = $nodeTranslation->getNode();
     			$parentNode = $node->getParent();
     			if($parentNode){
     				$parentNodeTranslation = $parentNode->getNodeTranslation($nodeTranslation->getLang());
     				if($parentNodeTranslation){
     					$parentNodeVersion = $parentNodeTranslation->getPublicNodeVersion();
     					$parent = $em->getRepository($parentNode->getRefEntityname())->find($parentNodeVersion->getRefId());
     					if($parent){
     						$entity->setParent($parent);
     					}
     				}
     			}
     		}
     	}
    }

}
