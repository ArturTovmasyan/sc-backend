<?php
namespace App\Annotation;

use App\Util\StringUtil;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

/**
 * @Annotation
 * @Target({"CLASS"})
 */
class Grid
{
    /**
     * Field options
     */
    const FIELD_OPTION_ID               = 'id';
    const FIELD_OPTION_TYPE             = 'type';
    const FIELD_OPTION_SORTABLE         = 'sortable';
    const FIELD_OPTION_FILTERABLE       = 'filterable';
    const FIELD_OPTION_ORIGINAL         = 'field';
    const FIELD_OPTION_AVAILABLE_VALUES = 'values';
    const FIELD_OPTION_LINK             = 'link';
    const FIELD_OPTION_HIDDEN           = 'hidden';
    const FIELD_OPTION_SORT_TYPE        = 'sort_type';

    /**
     * Field types
     */
    const FIELD_TYPE_ADDITIONAL         = ':additional';
    const FIELD_TYPE_ID                 = 'id';
    const FIELD_TYPE_TEXT               = 'string';
    const FIELD_TYPE_BOOLEAN            = 'boolean';
    const FIELD_TYPE_NUMBER             = 'number';
    const FIELD_TYPE_NUMBER_ADDITIONAL  = 'number' . self::FIELD_TYPE_ADDITIONAL;
    const FIELD_TYPE_DATE               = 'date';
    const FIELD_TYPE_TIME               = 'time';
    const FIELD_TYPE_TIMETICK           = 'timetick';
    const FIELD_TYPE_DATETIME           = 'datetime';
    const FIELD_TYPE_ENUM               = 'enum';

    /**
     * Field options listing
     */
    public const FIELD_OPTIONS = [
        self::FIELD_OPTION_ID,
        self::FIELD_OPTION_TYPE,
        self::FIELD_OPTION_SORTABLE,
        self::FIELD_OPTION_FILTERABLE,
        self::FIELD_OPTION_ORIGINAL,
        self::FIELD_OPTION_AVAILABLE_VALUES,
        self::FIELD_OPTION_LINK,
        self::FIELD_OPTION_HIDDEN,
        self::FIELD_OPTION_SORT_TYPE
    ];

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var QueryBuilder
     */
    private $queryBuilder;

    /**
     * @var array
     */
    private $groups = [];

    /**
     * @var array
     */
    private $groupsById = [];

    /**
     * @var int
     */
    private $page = 1;

    /**
     * @var int
     */
    private $perPage = 10;

    /**
     * @param $options
     */
    public function __construct($options = null)
    {
        if (empty($options)) {
            return;
        }

        foreach ($options as $groupName => $groupOptions) {
            foreach ($groupOptions as $index => $groupOption) {
                $this->groups[$groupName][$index] = [
                    self::FIELD_OPTION_ID               => null,
                    self::FIELD_OPTION_TYPE             => null,
                    self::FIELD_OPTION_SORTABLE         => true,
                    self::FIELD_OPTION_FILTERABLE       => true,
                    self::FIELD_OPTION_ORIGINAL         => null,
                    self::FIELD_OPTION_AVAILABLE_VALUES => null,
                    self::FIELD_OPTION_LINK             => null,
                    self::FIELD_OPTION_HIDDEN           => false,
                    self::FIELD_OPTION_SORT_TYPE        => null,
                ];

                foreach ($groupOption as $key => $fieldOption) {
                    if (in_array($key, self::FIELD_OPTIONS)) {
                        if ($key == 'values') {
                            [$className, $methodName] = explode("::", $fieldOption);
                            $fieldOption = $className::$methodName();
                        }

                        $this->groups[$groupName][$index][$key] = $fieldOption;
                    }
                }

                $this->groups[$groupName][$index] = array_filter($this->groups[$groupName][$index], function ($value) {
                    return $value !== null;
                });
                $this->groupsById[$groupName][$groupOption['id']] = $this->groups[$groupName][$index];
            }
        }
    }

    /**
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param $groupName
     * @return array|boolean
     */
    public function getGroupOptions($groupName)
    {
        if (isset($this->groups[$groupName])) {
            return $this->groups[$groupName];
        }

        return false;
    }

    /**
     * @param $groupName
     * @return array|boolean
     */
    public function getGroupOptionsById($groupName)
    {
        if (isset($this->groupsById[$groupName])) {
            return $this->groupsById[$groupName];
        }

        return false;
    }

    /**
     * @param EntityManagerInterface $em
     * @return $this
     */
    public function setEntityManager(EntityManagerInterface $em)
    {
        $this->em = $em;

        return $this;
    }

    /**
     * @return QueryBuilder
     */
    public function getQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param $text
     * @param $prefix
     * @return string
     */
    private function removePrefix($text, $prefix)
    {
        if (0 === strpos($text, $prefix)) {
            $text = substr($text, strlen($prefix)) . '';
        }

        return $text;
    }

    /**
     * @param array $params
     * @param $groupName
     * @return $this|bool
     * @throws \Throwable
     */
    public function renderByGroup(array $params, $groupName)
    {
        /** @todo remove **/
        /*$params = array_merge($params, [
            'sort' => [
               'name'  => 'asc',
            ],
            'filter' => [
                'name' => [
                    'c' => 1,
                    'v' => [
                        ''
                    ]
                ],
                'default' => [
                    'c' => 3,
                    'v' => [
                        1
                    ]
                ],
                'last_activity_at' => [
                    'c' => 3,
                    'v' => [
                        '2018-11-07T17:21:22',
                        '2018-11-21T17:21:24',
                    ]
                ]
            ]
        ]);*/

        $this->queryBuilder = $this->em->createQueryBuilder();
        $options            = $this->getGroupOptionsById($groupName);
        $fields             = $this->getGroupOptions($groupName);

        if (!$fields || !$options) {
            return false;
        }

        foreach ($fields as $field) {
            if(!StringUtil::ends_with($field['type'], self::FIELD_TYPE_ADDITIONAL)) {
                $this->queryBuilder->addSelect(sprintf("%s as %s", $field['field'], $field['id']));
            }
        }

        // set page
        if (isset($params['page']) && (int) $params['page'] >= 1) {
            $this->page = (int) $params['page'];
        }

        // set limitation
        if (isset($params['per_page']) && (int) $params['per_page'] > 1) {
            $this->perPage = (int) $params['per_page'];
        }

        // set sorting
        if (!empty($params['sort'])) {
            foreach ($params['sort'] as $key => $sortType) {
                if (!in_array(strtolower($sortType), ['asc', 'desc'])) {
                    continue;
                }

                $key = strtolower($key);

                if (!isset($options[$key][self::FIELD_OPTION_SORTABLE])
                    || !$options[$key][self::FIELD_OPTION_SORTABLE]
                    || !isset($options[$key]['field'])
                ) {
                    continue;
                }

                if (isset($options[$key][self::FIELD_OPTION_SORT_TYPE])) {
                    switch ($options[$key][self::FIELD_OPTION_SORT_TYPE]) {
                        case "natural":
                            $this->queryBuilder->addOrderBy("NATURAL_SORT(".$options[$key]['field'] . ", 10, '.')", $sortType);
                            break;
                        default:
                            $this->queryBuilder->addOrderBy($options[$key]['field'], $sortType);
                            break;
                    }
                } else {
                    $this->queryBuilder->addOrderBy($options[$key]['field'], $sortType);
                }
            }
        }

        // set filters
        if (!empty($params['filter'])) {
            foreach ($params['filter'] as $key => $filter) {
                $key = strtolower($key);

                if (!isset($options[$key]['field']) || !$options[$key]['field']) {
                    continue;
                }

                if (!isset($options[$key]['filterable']) || !$options[$key]['filterable']) {
                    continue;
                }

//                if (!isset($filter['c']) || !isset($filter['v'])) {
//                    continue;
//                }

                $fieldKey    = $options[$key]['field'];
                $suffix = 0;

                switch ($options[$key]['type']) {
                    case self::FIELD_TYPE_TEXT:
                        switch ($filter['c']) {
                            case '0':
                                $this->queryBuilder->andHaving("$fieldKey = :text_$suffix");
                                $this->queryBuilder->setParameter("text_$suffix", $filter['v'][0]);
                                break;
                            case '1':
                                $this->queryBuilder->andHaving("$fieldKey LIKE :text_$suffix");
                                $this->queryBuilder->setParameter("text_$suffix", "%" . $filter['v'][0] . "%");
                                break;
                            /*case '2':
                                $regex = $filter['v'][0];
                                $valid = !(@preg_match("/".$regex."/", null) === false);

                                if (!$valid) {
                                    $regex = preg_quote($filter['v'][0]);
                                }

                                $this->queryBuilder->andHaving("REGEXP($key, :text_$suffix) = TRUE");
                                $this->queryBuilder->setParameter("text_$suffix", $regex);
                                break;*/
                        }
                        break;
                    case self::FIELD_TYPE_ID:
                    case self::FIELD_TYPE_NUMBER:
                    case self::FIELD_TYPE_NUMBER_ADDITIONAL:
                        switch ($filter['c']) {
                            case '0': // =
                                $this->queryBuilder->andHaving("$fieldKey = :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '1': // <
                                $this->queryBuilder->andHaving("$fieldKey < :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '2': // >
                                $this->queryBuilder->andHaving("$fieldKey > :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '3': // <=
                                $this->queryBuilder->andHaving("$fieldKey <= :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '4': // =>
                                $this->queryBuilder->andHaving("$fieldKey >= :num_$suffix");
                                $this->queryBuilder->setParameter("num_$suffix", $filter['v'][0]);
                                break;
                            case '5': // ><
                                $this->queryBuilder->andHaving("$fieldKey >= :num_from_$suffix AND $fieldKey <= :num_to_$suffix");
                                $this->queryBuilder->setParameter("num_from_$suffix", $filter['v'][0]);
                                $this->queryBuilder->setParameter("num_to_$suffix", $filter['v'][1]);
                                break;
                        }
                        break;
                    case self::FIELD_TYPE_DATE:
                    case self::FIELD_TYPE_TIME:
                    case self::FIELD_TYPE_DATETIME:
                        switch ($filter['c']) {
                            case '0': // =
                                $this->queryBuilder->andHaving("$fieldKey = :date_$suffix");
                                $this->queryBuilder->setParameter("date_$suffix", $filter['v'][0]);
                                break;
                            case '1': // <=
                                $this->queryBuilder->andHaving("$fieldKey <= :date_$suffix");
                                $this->queryBuilder->setParameter("date_$suffix", new \DateTime($filter['v'][0]));
                                break;
                            case '2': // =>
                                $this->queryBuilder->andHaving("$fieldKey >= :date_$suffix");
                                $this->queryBuilder->setParameter("date_$suffix", new \DateTime($filter['v'][0]));
                                break;
                            case '3': // ><
                                $this->queryBuilder->andHaving("$fieldKey BETWEEN :date_from_$suffix AND :date_to_$suffix");
                                $this->queryBuilder->setParameter("date_from_$suffix", new \DateTime($filter['v'][0]));
                                $this->queryBuilder->setParameter("date_to_$suffix", new \DateTime($filter['v'][1]));
                                break;
                        }
                        break;
                    case self::FIELD_TYPE_ENUM:
                        if (count($filter['v']) > 0) {
                            $enumHaving = "";
                            foreach ($filter['v'] as $idx => $item) {
                                $enumHaving .= " OR $fieldKey = :enum_$suffix" . "_$idx";
                                $this->queryBuilder->setParameter("enum_$suffix" . "_$idx", $idx);
                            }
                            $this->queryBuilder->andHaving($this->removePrefix($enumHaving, " OR "));
                        }
                        break;
                    case self::FIELD_TYPE_BOOLEAN:
                        if (count($filter['v']) > 0) {
                            $enumHaving = "";
                            foreach ($filter['v'] as $idx => $item) {
                                $enumHaving .= " OR $fieldKey = :enum_$suffix" . "_$idx";
                                $this->queryBuilder->setParameter("enum_$suffix" . "_$idx", boolval($item));
                            }
                            $this->queryBuilder->andHaving($this->removePrefix($enumHaving, " OR "));
                        }
                        break;
                }
            }
        }

        return $this;
    }
}
