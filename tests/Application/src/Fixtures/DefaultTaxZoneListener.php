<?php

declare(strict_types=1);

namespace Setono\SyliusToggleVatPlugin\Tests\Application\src\Fixtures;

use Doctrine\Persistence\ManagerRegistry;
use Setono\Doctrine\ORMTrait;
use Sylius\Bundle\FixturesBundle\Listener\AbstractListener;
use Sylius\Bundle\FixturesBundle\Listener\AfterSuiteListenerInterface;
use Sylius\Bundle\FixturesBundle\Listener\SuiteEvent;
use Sylius\Component\Addressing\Model\Zone;
use Sylius\Component\Core\Model\Channel;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Webmozart\Assert\Assert;

class DefaultTaxZoneListener extends AbstractListener implements AfterSuiteListenerInterface
{
    use ORMTrait;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function afterSuite(SuiteEvent $suiteEvent, array $options): void
    {
        Assert::keyExists($options, 'zone');
        Assert::stringNotEmpty($options['zone']);

        $zone = $this->getRepository(Zone::class)->findOneBy(['code' => $options['zone']]);
        Assert::notNull($zone);

        $this->getManager(Channel::class)
            ->createQueryBuilder()
                ->update(Channel::class, 'o')
                ->set('o.defaultTaxZone', ':zone')
                ->setParameter('zone', $zone)
                ->getQuery()
                ->execute()
        ;
    }

    public function getName(): string
    {
        return 'default_tax_zone';
    }

    protected function configureOptionsNode(ArrayNodeDefinition $optionsNode): void
    {
        $optionsNode
            ->children()
                ->scalarNode('zone')
                    ->isRequired()
        ;
    }
}
