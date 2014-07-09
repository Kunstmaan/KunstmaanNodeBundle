<?php
/**
 * Created by Kunstmaan.
 * Date: 09/07/14
 * Time: 14:21
 */

namespace Kunstmaan\NodeBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MigrateSoftDeletesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        parent::configure();

        $this->setName('kuma:node:migrate-soft-deletes')
            ->setDescription('Migrate nodes for soft deletes.')
            ->setHelp(
                'The <info>kuma:node:migrate-soft-deletes</info> will loop over all nodes and set soft delete timestamps for deleted nodes.'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $this->updateNodes($output, $em);
    }

    /**
     * @param OutputInterface $output
     * @param EntityManager   $em
     */
    protected function updateNodes(OutputInterface $output, $em)
    {
        $sql = 'UPDATE kuma_nodes SET deleted_at = NOW() WHERE deleted = true';
        $stmt = $em->getConnection()->prepare($sql);
        $stmt->execute();
        $output->writeln('Updated all nodes');
    }
}