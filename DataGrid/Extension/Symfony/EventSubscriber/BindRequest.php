<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\EventSubscriber;

use FSi\Component\DataGrid\DataGridEventInterface;
use FSi\Component\DataGrid\DataGridEvents;
use FSi\Component\DataGrid\Exception\DataGridException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BindRequest implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [DataGridEvents::PRE_BIND_DATA => ['preBindData', 128]];
    }

    public function preBindData(DataGridEventInterface $event): void
    {
        $dataGrid = $event->getDataGrid();
        $request = $event->getData();

        if (false === $request instanceof Request) {
            return;
        }

        $name = $dataGrid->getName();
        switch ($request->getMethod()) {
            case 'POST':
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                $data = $request->request->get($name, []);
                break;
            case 'GET':
                $data = $request->query->get($name, []);
                break;

            default:
                throw new DataGridException(sprintf(
                    'The request method "%s" is not supported',
                    $request->getMethod()
                ));
        }

        $event->setData($data);
    }
}
