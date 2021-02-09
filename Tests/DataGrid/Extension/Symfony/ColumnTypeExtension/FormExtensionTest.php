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
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension;
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
        $objectManager->method('getConfiguration')->willReturn($configuration);
        $objectManager->method('getExpressionBuilder')->willReturn(new Expr());

        $query = $this->getMockBuilder(AbstractQuery::class)
            ->setConstructorArgs([$objectManager])
            ->setMethods(['execute', '_doExecute', 'getSql', 'setFirstResult', 'setMaxResults'])
            ->getMock();
        $query->method('execute')->willReturn($entities);
        $query->method('setFirstResult')->willReturn($query);
        $query->method('setMaxResults')->willReturn($query);

        $objectManager->method('createQuery')->withAnyParameters()->willReturn($query);

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

        $repository->method('createQueryBuilder')->withAnyParameters()->willReturn($queryBuilder);
        $repository->method('findAll')->willReturn($entities);

        $objectManager->method('getClassMetadata')->withAnyParameters()->willReturn($classMetadata);
        $objectManager->method('getRepository')->willReturn($repository);
        $objectManager->method('contains')->willReturn(true);

        if (interface_exists(PersistenceManagerRegistry::class)) {
            $managerRegistry = $this->createMock(PersistenceManagerRegistry::class);
        } elseif (interface_exists(CommonManagerRegistry::class)) {
            $managerRegistry = $this->createMock(CommonManagerRegistry::class);
        } else {
            throw new RuntimeException();
        }
        $managerRegistry->method('getManagerForClass')->willReturn($objectManager);
        $managerRegistry->method('getManagers')->willReturn([]);

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

        /** @var DataGridInterface&MockObject $dataGrid */
        $dataGrid = $this->createMock(DataGridInterface::class);
        $this->dataGrid = $dataGrid;
        $this->dataGrid->method('getName')->willReturn('grid');

        $this->extension = new FormExtension($formFactory);
    }

    public function testSimpleBindData(): void
    {
        $column = $this->createColumnMock();
        $this->setColumnId($column, 'text');
        $this->setColumnOptions($column, [
            'field_mapping' => ['name', 'author'],
            'editable' => true,
            'form_options' => [],
            'form_type' => [
                'name' => ['type' => TextType::class],
                'author' => ['type' => TextType::class],
            ]
        ]);

        $object = new Entity('old_name');
        $data = [
            'name' => 'object',
            'author' => 'norbert@fsi.pl',
            'invalid_data' => 'test'
        ];

        $this->extension->bindData($column, $data, $object, 1);

        self::assertSame('norbert@fsi.pl', $object->getAuthor());
        self::assertSame('object', $object->getName());
    }


    public function testAvoidBindingDataWhenFormIsNotValid(): void
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
                'name' => ['type' => TextType::class],
                'author' => ['type' => TextType::class],
            ]
        ]);

        $object = new Entity('old_name');

        $data = [
            'name' => 'object',
            'author' => 'invalid_value',
        ];

        $this->extension->bindData($column, $data, $object, 1);

        self::assertNull($object->getAuthor());
        self::assertSame('old_name', $object->getName());
    }

    public function testEntityBindData(): void
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

        self::assertNull($object->getCategory());

        $this->extension->bindData($column, $data, $object, 1);

        self::assertInstanceOf($nestedEntityClass, $object->getCategory());
        self::assertSame('category name 1', $object->getCategory()->getName());
    }

    /**
     * @return ColumnTypeInterface&MockObject
     */
    private function createColumnMock(): ColumnTypeInterface
    {
        /** @var ColumnTypeInterface&MockObject $column */
        $column = $this->createMock(ColumnTypeInterface::class);
        $column->method('getDataMapper')->will($this->getDataMapperReturnCallback());
        $column->method('getDataGrid')->willReturn($this->dataGrid);

        return $column;
    }

    private function setColumnId($column, $id): void
    {
        $column->method('getId')->willReturn($id);
    }

    private function setColumnOptions($column, $optionsMap): void
    {
        $column->method('getOption')
            ->willReturnCallback(
                function ($option) use ($optionsMap) {
                    return $optionsMap[$option];
                }
            );
    }

    private function getDataMapperReturnCallback(): ReturnCallback
    {
        $dataMapper = $this->createMock(DataMapperInterface::class);
        $dataMapper->method('getData')
            ->willReturnCallback(
                function ($field, $object) {
                    $method = 'get' . ucfirst($field);

                    return $object->$method();
                }
            );

        $dataMapper->method('setData')
            ->willReturnCallback(
                function ($field, $object, $value) {
                    $method = 'set' . ucfirst($field);

                    return $object->$method($value);
                }
            );

        return self::returnCallback(function () use ($dataMapper) {
            return $dataMapper;
        });
    }
}
