<?php

/**
 * (c) FSi sp. z o.o. <info@fsi.pl>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace FSi\Bundle\DatagridBundle\Tests\DataGrid\Extension\Symfony\ColumnTypeExtension;

use Doctrine\Common\Persistence\ManagerRegistry as CommonManagerRegistry;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;
use FSi\Bundle\DataGridBundle\DataGrid\Extension\Symfony\ColumnTypeExtension\FormExtension;
use FSi\Bundle\DataGridBundle\Tests\Fixtures\Entity;
use FSi\Bundle\DataGridBundle\Tests\Fixtures\EntityCategory;
use FSi\Component\DataGrid\Column\ColumnTypeInterface;
use FSi\Component\DataGrid\DataGridInterface;
use FSi\Component\DataGrid\DataMapper\DataMapperInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\RuntimeException;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\CoreExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormRegistry;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\ValidatorBuilder;

use function interface_exists;

class FormExtensionTest extends TestCase
{
    /**
     * @var FormExtension
     */
    private $extension;

    /**
     * @var DataGridInterface&MockObject
     */
    private $dataGrid;

    protected function setUp(): void
    {
        $entities = [
            new EntityCategory(1, 'category name 1'),
            new EntityCategory(2, 'category name 2'),
        ];

        $configuration = $this->createMock(Configuration::class);

        $objectManager = $this->getMockBuilder(EntityManager::class)->disableOriginalConstructor()->getMock();
        $objectManager->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($configuration));

        $objectManager->expects($this->any())
            ->method('getExpressionBuilder')
            ->will($this->returnValue(new Expr()));

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setConstructorArgs([$objectManager])
            ->setMethods(['execute', '_doExecute', 'getSql', 'setFirstResult', 'setMaxResults'])
            ->getMock();
        $query->expects($this->any())
            ->method('execute')
            ->will($this->returnValue($entities));

        $query->expects($this->any())
            ->method('setFirstResult')
            ->will($this->returnValue($query));

        $query->expects($this->any())
            ->method('setMaxResults')
            ->will($this->returnValue($query));

        $objectManager->expects($this->any())
            ->method('createQuery')
            ->withAnyParameters()
            ->will($this->returnValue($query));

        $queryBuilder = new QueryBuilder($objectManager);

        $entityClass = EntityCategory::class;
        $classMetadata = new ClassMetadata($entityClass);
        $classMetadata->identifier = ['id'];
        $classMetadata->fieldMappings = [
            'id' => [
                'type' => 'integer',
                'fieldName' => 'id',
                'columnName' => 'id',
                'inherited' => $entityClass,
            ]
        ];
        $classMetadata->reflFields = [
            'id' => new \ReflectionProperty($entityClass, 'id'),
        ];

        $repository = $this->getMockBuilder(EntityRepository::class)
            ->setConstructorArgs([$objectManager, $classMetadata])
            ->getMock();
        $repository->expects($this->any())
            ->method('createQueryBuilder')
            ->withAnyParameters()
            ->will($this->returnValue($queryBuilder));
        $repository->expects($this->any())
            ->method('findAll')
            ->will($this->returnValue($entities));

        $objectManager->expects($this->any())
            ->method('getClassMetadata')
            ->withAnyParameters()
            ->will($this->returnValue($classMetadata));
        $objectManager->expects($this->any())
            ->method('getRepository')
            ->will($this->returnValue($repository));
        $objectManager->expects($this->any())
            ->method('contains')
            ->will($this->returnValue(true));

        if (interface_exists(PersistenceManagerRegistry::class)) {
            $managerRegistry = $this->createMock(PersistenceManagerRegistry::class);
        } elseif (interface_exists(CommonManagerRegistry::class)) {
            $managerRegistry = $this->createMock(CommonManagerRegistry::class);
        } else {
            throw new RuntimeException();
        }
        $managerRegistry->expects($this->any())
            ->method('getManagerForClass')
            ->will($this->returnValue($objectManager));
        $managerRegistry->expects($this->any())
            ->method('getManagers')
            ->will($this->returnValue([]));

        $validatorBuilder = new ValidatorBuilder();
        $resolvedTypeFactory = new ResolvedFormTypeFactory();
        $formRegistry = new FormRegistry(
            [
                new CoreExtension(),
                new DoctrineOrmExtension($managerRegistry),
                new CsrfExtension(new CsrfTokenManager()),
                new ValidatorExtension($validatorBuilder->getValidator())
            ],
            $resolvedTypeFactory
        );

        $formFactory = new FormFactory($formRegistry);

        $this->dataGrid = $this->createMock(DataGridInterface::class);
        $this->dataGrid->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('grid'));

        $this->extension = new FormExtension($formFactory);
    }

    public function testSimpleBindData()
    {
        $column = $this->createColumnMock();
        $this->setColumnId($column, 'text');
        $this->setColumnOptions($column, [
            'field_mapping' => ['name', 'author'],
            'editable' => true,
            'form_options' => [],
            'form_type' => [
                'name' => ['type' => $this->isSymfonyForm28() ? TextType::class : 'text'],
                'author' => ['type' => $this->isSymfonyForm28() ? TextType::class : 'text'],
            ]
        ]);

        $object = new Entity('old_name');
        $data = [
            'name' => 'object',
            'author' => 'norbert@fsi.pl',
            'invalid_data' => 'test'
        ];

        $this->extension->bindData($column, $data, $object, 1);

        $this->assertSame('norbert@fsi.pl', $object->getAuthor());
        $this->assertSame('object', $object->getName());
    }


    public function testAvoidBindingDataWhenFormIsNotValid()
    {
        $column = $this->createColumnMock();
        $this->setColumnId($column, 'text');
        $this->setColumnOptions($column, [
            'field_mapping' => ['name', 'author'],
            'editable' => true,
            'form_options' => [
                'author' => [
                    'constraints' => [
                        new Email()
                    ]
                ]
            ],
            'form_type' => [
                'name' => ['type' => $this->isSymfonyForm28() ? TextType::class : 'text'],
                'author' => ['type' => $this->isSymfonyForm28() ? TextType::class : 'text'],
            ]
        ]);

        $object = new Entity('old_name');

        $data = [
            'name' => 'object',
            'author' => 'invalid_value',
        ];

        $this->extension->bindData($column, $data, $object, 1);

        $this->assertNull($object->getAuthor());
        $this->assertSame('old_name', $object->getName());
    }

    public function testEntityBindData()
    {
        $nestedEntityClass = EntityCategory::class;

        $column = $this->createColumnMock();
        $this->setColumnId($column, 'entity');
        $this->setColumnOptions($column, [
            'editable' => true,
            'relation_field' => 'category',
            'field_mapping' => ['name'],
            'form_options' => [
                'category' => [
                    'class' => $nestedEntityClass,
                ]
            ],
            'form_type' => [],
        ]);

        $object = new Entity('name123');
        $data = [
            'category' => 1,
        ];

        $this->assertNull($object->getCategory());

        $this->extension->bindData($column, $data, $object, 1);

        $this->assertInstanceOf($nestedEntityClass, $object->getCategory());
        $this->assertSame('category name 1', $object->getCategory()->getName());
    }

    private function createColumnMock()
    {
        $column = $this->createMock(ColumnTypeInterface::class);

        $column->expects($this->any())
            ->method('getDataMapper')
            ->will($this->getDataMapperReturnCallback());

        $column->expects($this->any())
            ->method('getDataGrid')
            ->will($this->returnValue($this->dataGrid));

        return $column;
    }

    private function setColumnId($column, $id)
    {
        $column->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));
    }

    private function setColumnOptions($column, $optionsMap)
    {
        $column->expects($this->any())
            ->method('getOption')
            ->will($this->returnCallback(function ($option) use ($optionsMap) {
                return $optionsMap[$option];
            }));
    }

    private function getDataMapperReturnCallback()
    {
        $dataMapper = $this->createMock(DataMapperInterface::class);
        $dataMapper->expects($this->any())
            ->method('getData')
            ->will($this->returnCallback(function ($field, $object) {
                $method = 'get' . ucfirst($field);
                return $object->$method();
            }));

        $dataMapper->expects($this->any())
            ->method('setData')
            ->will($this->returnCallback(function ($field, $object, $value) {
                $method = 'set' . ucfirst($field);
                return $object->$method($value);
            }));

        return $this->returnCallback(function () use ($dataMapper) {
            return $dataMapper;
        });
    }

    private function isSymfonyForm28(): bool
    {
        return method_exists(AbstractType::class, 'getBlockPrefix');
    }
}
