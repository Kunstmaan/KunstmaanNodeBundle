<?php

namespace Kunstmaan\AdminNodeBundle\Repository;
use Kunstmaan\AdminNodeBundle\Entity\HasNodeInterface;
use Kunstmaan\AdminBundle\Entity\User as Baseuser;
use Kunstmaan\AdminNodeBundle\Entity\Node;
use Kunstmaan\AdminBundle\Helper\ClassLookup;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * NodeRepository
 *
 */
class NodeRepository extends EntityRepository
{
    /**
     * @param string  $lang                 The locale
     * @param User    $user                 The user
     * @param string  $permission           The pemission (read, write, ...)
     * @param boolean $includehiddenfromnav include the hiddenfromnav nodes or not
     *
     * @return array
     */
    public function getTopNodes($lang, $user, $permission, $includehiddenfromnav = false)
    {
        return $this->getChildNodes(null, $lang, $user, $permission, $includehiddenfromnav);
    }

    /**
     * @param HasNodeInterface $hasNode
     *
     * @return Node|NULL
     */
    public function getNodeFor(HasNodeInterface $hasNode)
    {
        $nodeVersion = $this->getEntityManager()->getRepository('KunstmaanAdminNodeBundle:NodeVersion')->getNodeVersionFor($hasNode);
        if (!is_null($nodeVersion)) {
            $nodeTranslation = $nodeVersion->getNodeTranslation();
            if (!is_null($nodeTranslation)) {
                return $nodeTranslation->getNode();
            }
        }

        return null;
    }

    /**
     * @param integer $id         The id
     * @param string  $entityName The classname
     *
     * @return Node|NULL
     */
    public function getNodeForIdAndEntityname($id, $entityName)
    {
        $nodeVersion = $this->getEntityManager()->getRepository('KunstmaanAdminNodeBundle:NodeVersion')->findOneBy(array('refId' => $id, 'refEntityname' => $entityName));
        if ($nodeVersion) {
            return $nodeVersion->getNodeTranslation()->getNode();
        }

        return null;
    }

    /**
     * @param Node   $parentNode The parent node (may be null)
     * @param string $slug       The slug
     *
     * @return Node|NULL
     */
    public function getNodeForSlug($parentNode, $slug)
    {
        $slugparts = explode("/", $slug);
        $result = null;
        foreach ($slugparts as $slugpart) {
            if ($parentNode) {
                if ($r = $this->findOneBy(array('slug' => $slugpart, 'parent.parent' => $parentNode->getId()))) {
                    $result = $r;
                }
            } else {
                if ($r = $this->findOneBy(array('slug' => $slugpart))) {
                    $result = $r;
                }
            }
        }

        return $result;
    }

    /**
     * @param HasNodeInterface $hasNode      The object to link to
     * @param string           $lang         The locale
     * @param Baseuser         $owner        The user
     * @param string           $internalName The internal name (may be null)
     *
     * @throws \Exception
     * @return \Kunstmaan\AdminNodeBundle\Entity\Node
     */
    public function createNodeFor(HasNodeInterface $hasNode, $lang, Baseuser $owner, $internalName = null)
    {
        $em = $this->getEntityManager();
        $classname = ClassLookup::getClass($hasNode);
        if (!$hasNode->getId() > 0) {
            throw new \Exception("the entity of class " . $classname . " has no id, maybe you forgot to flush first");
        }
        $entityrepo = $em->getRepository($classname);
        $node = new Node();
        $node->setRefEntityname($classname);
        $node->setDeleted(false);
        $node->setInternalName($internalName);
        $parent = $hasNode->getParent();
        if ($parent) {
            $parentNodeVersion = $em->getRepository('KunstmaanAdminNodeBundle:NodeVersion')->findOneBy(array('refId' => $parent->getId(), 'refEntityname' => ClassLookup::getClass($parent)));
            if ($parentNodeVersion) {
                $node->setParent($parentNodeVersion->getNodeTranslation()->getNode());
                $node->setRoles($parentNodeVersion->getNodeTranslation()->getNode()->getRoles());
            }
        }
        $em->persist($node);
        $em->flush();
        $em->refresh($node);
        $nodeTranslation = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->createNodeTranslationFor($hasNode, $lang, $node, $owner);

        return $node;
    }

    /**
     * @param integer $parentid             The parent id
     * @param string  $lang                 The locale
     * @param User    $user                 The user
     * @param string  $permission           The permission (read, write, ...)
     * @param boolean $includehiddenfromnav Include hiddenfromnav nodes or not
     *
     * @return array:
     */
    public function getChildNodes($parentid, $lang, $user, $permission, $includehiddenfromnav = false)
    {
        $qb = $this->createQueryBuilder('b')->select('b')->innerJoin("b.nodeTranslations", "t")->where('b.deleted = 0');

        if (!$includehiddenfromnav) {
            $qb->andWhere('b.hiddenfromnav != true');
        }

        $qb->andWhere('b.id IN (
	            SELECT p.refId FROM Kunstmaan\AdminBundle\Entity\Permission p WHERE p.refEntityname = ?1 AND p.permissions LIKE ?2 AND p.refGroup IN(?3)
	    )')->andWhere("t.lang = :lang");

        if (is_null($parentid)) {
            $qb->andWhere("b.parent is NULL");
        } else {
            $qb->andWhere("b.parent = :parent")->setParameter("parent", $parentid);
        }

        $qb->addOrderBy('t.weight', 'ASC')->addOrderBy('t.title', 'ASC')->setParameter(1, 'Kunstmaan\\AdminNodeBundle\\Entity\\Node')->setParameter(2, '%|' . $permission . ':1|%');

        $groupIds = $user->getGroupIds();
        if (!empty($groupIds)) {
            $qb->setParameter(3, $groupIds);
        } else {
            $qb->setParameter(3, null);
        }
        $qb->setParameter("lang", $lang);

        $result = $qb->getQuery()->getResult();

        return $result;
    }

    /**
     * @return array:
     */
    public function getAllTopNodes()
    {
        $qb = $this->createQueryBuilder('b')->select('b')->where('b.deleted = 0')->andWhere("b.parent IS NULL");

        $result = $qb->getQuery()->getResult();

        return $result;
    }
}
