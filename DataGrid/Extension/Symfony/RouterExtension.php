<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony;

use FSi\Component\DataGrid\DataGridAbstractExtension;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\EventSubscriber;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class RouterExtension extends DataGridAbstractExtension
{
    /**
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(UrlGeneratorInterface $urlGenerator, RequestStack $requestStack)
    {
        $this->urlGenerator = $urlGenerator;
        $this->requestStack = $requestStack;
    }

    protected function loadColumnTypes(): array
    {
        return [
            new ColumnType\Action($this->urlGenerator, $this->requestStack),
        ];
    }

    protected function loadSubscribers(): array
    {
        return [
            new EventSubscriber\BindRequest(),
        ];
    }
}
