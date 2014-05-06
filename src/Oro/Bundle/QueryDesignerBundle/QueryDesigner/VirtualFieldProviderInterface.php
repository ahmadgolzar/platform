<?php

namespace Oro\Bundle\QueryDesignerBundle\QueryDesigner;

interface VirtualFieldProviderInterface
{
    /**
     * Indicates whether the given field is a virtual one or not
     *
     * @param string $className
     * @param string $fieldName
     * @return bool
     */
    public function isVirtualField($className, $fieldName);

    /**
     * Gets a query associated with the given virtual field
     *
     * @param string $className
     * @param string $fieldName
     * @return array
     */
    public function getVirtualFieldQuery($className, $fieldName);
}