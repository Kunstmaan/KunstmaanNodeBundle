<?php

namespace Kunstmaan\NodeBundle\Command;

use Kunstmaan\NodeBundle\Entity\NodeTranslation;
use Kunstmaan\NodeBundle\Entity\QueuedNodeTranslationAction;
use Kunstmaan\NodeBundle\Event\Events;
use Kunstmaan\NodeBundle\Event\NodeEvent;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * CronUpdateNodeCommand
 */
class CronUpdateNodeCommand extends ContainerAwareCommand
{

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('kuma:nodes:cron')
            ->setDescription('Do everything that needs to be run in a cron job.')
            ->setHelp("The <info>kuma:nodes:cron</info> will loop over all queued node translation action entries and update the nodetranslations if needed.");
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $queuedNodeTranslationActions = $em->getRepository('KunstmaanNodeBundle:QueuedNodeTranslationAction')->findAll();

        if (count($queuedNodeTranslationActions)) {
            foreach ($queuedNodeTranslationActions as $queuedNodeTranslationAction) {
                $now = new \DateTime();
                if ($queuedNodeTranslationAction->getDate()->getTimestamp() < $now->getTimestamp()) {
                    $action = $queuedNodeTranslationAction->getAction();
                    {
                        //set user security context
                        $securityContext = $this->getContainer()->get('security.context');
                        $user = $em->getRepository('KunstmaanAdminBundle:User')->findOneBy(array('id' => $queuedNodeTranslationAction->getUserId()));
                        $runAsToken = new UsernamePasswordToken($user, null, 'foo', $user->getRoles());
                        $this->getContainer()->get('security.context')->setToken($runAsToken);
                    }
                    $nodeTranslation = $queuedNodeTranslationAction->getNodeTranslation();
                    switch ($action) {
                        case QueuedNodeTranslationAction::ACTION_PUBLISH:
                            $this->getContainer()->get('kunstmaan_node.admin_node.publisher')->publish($nodeTranslation);
                            $output->writeln("Published the page " . $nodeTranslation->getTitle());
                            break;
                        case QueuedNodeTranslationAction::ACTION_UNPUBLISH:
                            $this->getContainer()->get('kunstmaan_node.admin_node.publisher')->unPublish($nodeTranslation);
                            $output->writeln("Unpublished the page " . $nodeTranslation->getTitle());
                            break;
                        default:
                            $output->writeln("Don't understand the action " . $action);
                    }
                }
            }
            $output->writeln('Done');
        } else {
            $output->writeln('No queued jobs');
        }
    }
}
