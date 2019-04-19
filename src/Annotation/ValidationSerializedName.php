<?php

namespace App\Annotation;

use Doctrine\Common\Annotations\Reader;
use ReflectionProperty;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class ValidationSerializedName
{
    /**
     * @var string
     */
    private $names = [];

    /**
     * Worker constructor.
     * @param $options
     */
    public function __construct($options = [])
    {
        if (!isset($options)) {
            return;
        }

        if (is_array($options)) {
            $this->names = $options;
        } else {
            $this->names[] = $options;
        }
    }

    /**
     * @return array|string
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @return array|string
     */
    public function getName($groupName)
    {
        return array_key_exists($groupName, $this->names) ? $this->names[$groupName] : null;
    }

    /**
     * @param $entityClass
     * @param $group
     * @param $original_property_path
     * @return string
     */
    public static function convert(Reader $reader, $entityClass, $group, $originalPropertyPath)
    {
        $propertyPath = preg_replace_callback('/([A-Z])/', function ($matches) {
            return '_' . lcfirst($matches[1]);
        }, $originalPropertyPath);

        $propertyPath = str_replace('[', '.', $propertyPath);
        $propertyPath = str_replace(']', '', $propertyPath);

        if (!empty($group)) {
            try {
                $property = new ReflectionProperty($entityClass, $originalPropertyPath);
                if ($property != null) {

                    /** @var ValidationSerializedName $propertyAnnotation */
                    $propertyAnnotation = $reader->getPropertyAnnotation($property, ValidationSerializedName::class);

                    if ($propertyAnnotation != null) {
                        $propertyPath = $propertyAnnotation->getName($group);
                    }
                }
            } catch (\ReflectionException $e) {

            }
        }

        return $propertyPath;
    }
}
