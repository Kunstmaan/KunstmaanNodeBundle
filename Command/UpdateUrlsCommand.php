<?php

namespace Kunstmaan\AdminNodeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateUrlsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        parent::configure();

        $this->setName('kuma:nodes:updateurls')
            ->setDescription('Update all urls for all translations.')
            ->setHelp("The <info>kuma:nodes:updateurls</info> will loop over all node translation entries and update the urls for the entries.");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $mainNodes = $em->getRepository('KunstmaanAdminNodeBundle:NodeTranslation')->getTopNodeTranslations();
        if (count($mainNodes)) {
            foreach ($mainNodes as $mainNode) {
                $mainNode->setUrl('');
                $em->persist($mainNode);
                $em->flush();
            }
        }

        $output->writeln('Updated all nodes');
    }
}
