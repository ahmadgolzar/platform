<?php

namespace Oro\Bundle\QueryDesignerBundle\Validator;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\ORMException;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

use Oro\Bundle\DataGridBundle\Datagrid\Builder;
use Oro\Bundle\DataGridBundle\Datagrid\ParameterBag;
use Oro\Bundle\DataGridBundle\Datasource\Orm\OrmDatasource;
use Oro\Bundle\DataGridBundle\Provider\ChainConfigurationProvider;
use Oro\Bundle\DataGridBundle\Provider\ConfigurationProviderInterface;
use Oro\Bundle\QueryDesignerBundle\Exception\InvalidConfigurationException;
use Oro\Bundle\QueryDesignerBundle\Grid\BuilderAwareInterface;
use Oro\Bundle\QueryDesignerBundle\Grid\DatagridConfigurationBuilder;
use Oro\Bundle\QueryDesignerBundle\Model\AbstractQueryDesigner;
use Oro\Bundle\QueryDesignerBundle\Model\GridQueryDesignerInterface;
use Oro\Bundle\QueryDesignerBundle\Validator\Constraints\QueryConstraint;

class QueryValidator extends ConstraintValidator
{
    /**
     * @var ChainConfigurationProvider
     */
    protected $configurationProvider;

    /**
     * @var Builder
     */
    protected $gridBuilder;

    /**
     * @var bool
     */
    protected $isDebug;

    /**
     * Constructor
     *
     * @param ChainConfigurationProvider $configurationProvider
     * @param Builder                    $gridBuilder
     * @param bool                       $isDebug
     */
    public function __construct(
        ChainConfigurationProvider $configurationProvider,
        Builder $gridBuilder,
        $isDebug
    ) {
        $this->configurationProvider = $configurationProvider;
        $this->gridBuilder           = $gridBuilder;
        $this->isDebug               = $isDebug;
    }

    /**
     * @param GridQueryDesignerInterface|AbstractQueryDesigner $value
     * @param QueryConstraint|Constraint                       $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$value instanceof GridQueryDesignerInterface) {
            return;
        }

        $gridPrefix = $value->getGridPrefix();
        $builder    = $this->getBuilder($gridPrefix);

        $builder->setGridName($gridPrefix);
        $builder->setSource($value);

        $dataGrid = $this->gridBuilder->build(
            $builder->getConfiguration(),
            new ParameterBag()
        );

        $dataSource = $dataGrid->getDatasource();
        if ($dataSource instanceof OrmDatasource) {
            $qb = $dataSource->getQueryBuilder();
            $qb->setMaxResults(1);
        }

        try {
            $dataSource->getResults();
        } catch (DBALException $e) {
            $this->context->addViolation($this->isDebug ? $e->getMessage() : $constraint->message);
        } catch (ORMException $e) {
            $this->context->addViolation($this->isDebug ? $e->getMessage() : $constraint->message);
        } catch (InvalidConfigurationException $e) {
            $this->context->addViolation($constraint->message);
        }
    }

    /**
     * @param string $gridName
     *
     * @return DatagridConfigurationBuilder
     */
    protected function getBuilder($gridName)
    {
        foreach ($this->configurationProvider->getProviders() as $provider) {
            /** @var ConfigurationProviderInterface|BuilderAwareInterface $provider */
            if (!$provider instanceof BuilderAwareInterface) {
                continue;
            }

            if ($provider->isApplicable($gridName)) {
                return $provider->getBuilder();
            }
        }

        throw new InvalidConfigurationException('Builder is missing');
    }
}
