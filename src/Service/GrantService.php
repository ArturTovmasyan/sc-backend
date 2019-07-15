<?php

namespace App\Service;

use App\Exception\InvalidGrantConfigException;
use App\Model\Grant;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Class GrantService
 * @package App\Api\V1\Service
 */
class GrantService
{
    private static $GRANT_CONFIG_PATH = '/srv/_vcs/backend/src/Api/V1/Common/Resources/config/grants.yaml';
//    private static $GRANT_CONFIG_PATH = 'D:/SVN/senior-care.backend/src/Api/V1/Common/Resources/config/grants.yaml';

    /** @var ContainerInterface */
    private $container;

    /** @var array */
    private $config;
    /** @var array */
    private $config_flat; // TODO: review

    /** @var EntityManagerInterface */
    private $em;

    /** @var User */
    private $current_user;

    /**
     * GrantService constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->em = $this->container->get('doctrine')->getManager();

        $this->load();
    }

    /**
     * @return User
     */
    public function getCurrentUser(): ?User
    {
        return $this->current_user;
    }

    /**
     * @param User $user
     */
    public function setCurrentUser(?User $user): void
    {
        $this->current_user = $user;
    }

    /**
     * @return Space|null
     */
    public function getCurrentSpace(): ?Space
    {
        return $this->current_user ? $this->current_user->getSpace() : null;
    }

    /**
     * @param string $entity_name
     * @return array|null
     */
    public function getCurrentUserEntityGrants(string $entity_name): ?array
    {
        return $this->getEntityGrants($this->current_user, $entity_name);
    }

    /**
     * @return array
     */
    public function getCurrentUserGrants(): array
    {
        return $this->current_user ? $this->getEffectiveGrants($this->current_user->getRoleObjects()) : [];
    }

    /**
     * @param string $grant
     * @return bool
     */
    public function hasCurrentUserGrant(string $grant): bool
    {
        $grants = $this->getCurrentUserGrants();
        return array_key_exists($grant, $grants) && $grants[$grant]['enabled'] === true;
    }

    /**
     * @param string $entity_name
     * @param int $level
     * @return bool
     */
    public function hasCurrentUserEntityGrant(string $entity_name, int $level): bool
    {
        $user = $this->current_user;
        if($user === null) {
            return null;
        }

        $role_grants = $this->getEffectiveGrants($user->getRoleObjects());

        $required_grants = array_filter($this->config_flat, function ($value) use ($entity_name) {
            return array_key_exists('class', $value) && $value['class'] === $entity_name;
        });

        if (\count($required_grants) > 1) {
            throw new InvalidGrantConfigException();
        }

        $user_level = 0;
        foreach ($required_grants as $key => $required_grant) {
            if (array_key_exists($key, $role_grants) && $role_grants[$key]['enabled'] === true) {
                $user_level = $role_grants[$key]['level'];
            }
        }

        return $user_level >= $level;
    }

    /**
     * @param User $user
     * @param string $entity_name
     * @return array|null
     */
    public function getEntityGrants(?User $user, string $entity_name): ?array
    {
        if($user === null) {
            return null;
        }

        $user_grants = $user->getGrants();
        $role_grants = $this->getEffectiveGrants($user->getRoleObjects());

        $required_grants = array_filter($this->config_flat, function ($value) use ($entity_name) {
            return array_key_exists('class', $value) && $value['class'] === $entity_name;
        });

        if (\count($required_grants) > 1) {
            throw new InvalidGrantConfigException();
        }

        /** @var array|null $allowed */
        $allowed = null;

        foreach ($required_grants as $key => $required_grant) {
            if (array_key_exists($key, $role_grants)) {
                switch ($role_grants[$key]['identity']) {
                    case Grant::$IDENTITY_ALL:
                        $allowed = null;
                        break;
                    case Grant::$IDENTITY_OWN:
                        $allowed = null; // TODO: review
                        break;
                    case Grant::$IDENTITY_SEVERAL:
                        $allowed = array_key_exists($key, $user_grants) ? $user_grants[$key] : [];
                        break;
                }
            }
        }

        return $allowed;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    /**
     * @param array|Collection $roles
     * @return array
     */
    public function getEffectiveGrants($roles) : array
    {
        $effective_role_grants = [];

        /** Role $role */
        foreach ($roles as $role) {
            $role_grants = $role->getGrants();

            foreach ($role_grants as $key => $grant) {
                if (!array_key_exists($key, $effective_role_grants)) {
                    $effective_role_grants[$key] = [];
                }

                if (array_key_exists('enabled', $grant)) {
                    if (array_key_exists('enabled', $effective_role_grants[$key])) {
                        if ($grant['enabled']) {
                            $effective_role_grants[$key]['enabled'] = $grant['enabled'];
                        }
                    } else {
                        $effective_role_grants[$key]['enabled'] = $grant['enabled'];
                    }
                }

                if (array_key_exists('identity', $grant)) {
                    if (array_key_exists('identity', $effective_role_grants[$key])) {
                        if ($grant['identity'] < $effective_role_grants[$key]['identity']) {
                            $effective_role_grants[$key]['identity'] = $grant['identity'];
                        }
                    } else {
                        $effective_role_grants[$key]['identity'] = $grant['identity'];
                    }
                }

                if (array_key_exists('level', $grant)) {
                    if (array_key_exists('level', $effective_role_grants[$key])) {
                        if ($grant['level'] > $effective_role_grants[$key]['level']) {
                            $effective_role_grants[$key]['level'] = $grant['level'];
                        }
                    } else {
                        $effective_role_grants[$key]['level'] = $grant['level'];
                    }
                }
            }
        }

        return $effective_role_grants;
    }


    public function getGrantsByRoleIds(array $ids)
    {
        $role_grants = [];

        foreach ($ids as $id) {
            $grants = $this->getGrantsOfRole($id);

            if (\is_array($grants)) {
                $role_grants = array_merge($role_grants, $grants);
            }
        }

        return $role_grants;
    }

    public function getGrantsOfRole($id)
    {
        /** @var Role $role */
        $role = $this->em->getRepository(Role::class)->find($id);

        $grants = [];

        if ($role) {
            $grants = $role->getGrants() ?? [];
        }

        $identity_grants = array_filter($grants, function ($value) {
            return array_key_exists('enabled', $value) && $value['enabled'] === true
                && array_key_exists('identity', $value) && $value['identity'] === 1;
        });

        foreach ($identity_grants as $key => &$identity_grant) {
            $identity_grant['title'] = $this->config_flat[$key]['title'];
            $identity_grant['url'] = $this->config_flat[$key]['url'];
        }

        return $identity_grants;
    }

    public function getGrants($values, $tree = null, $parent_key = '', $parent_fields = [])
    {
        $grid_config = [];

        if ($tree === null) {
            $tree = $this->config;
        }

        foreach ($tree as $key => $grant_node) {
            if (array_key_exists('fields', $grant_node)) {
                $fields = $grant_node['fields'];
            } else {
                $fields = $parent_fields;
            }

            $key_path = $parent_key !== '' ? $parent_key . '-' . $key : $key;
            $children = array_key_exists('children', $grant_node) ? $this->getGrants($values, $grant_node['children'], $key_path, $fields) : [];

            $node = [
                'key' => $key_path,
                'title' => $grant_node['title'] ?? ''
            ];

            if (\count($children) > 0) {
                $node['children'] = $children;
            } else {
                if (array_key_exists($key_path, $values)) {
                    if (\in_array('enabled', $fields, false)) {
                        $node['enabled'] = $values[$key_path]['enabled'] ?? false;
                    }
                    if (\in_array('level', $fields, false)) {
                        $node['level'] = $values[$key_path]['level'] ?? 0;
                    }
                    if (\in_array('identity', $fields, false)) {
                        $node['identity'] = $values[$key_path]['identity'] ?? 0;
                    }
                } else {
                    if (\in_array('enabled', $fields, false)) {
                        $node['enabled'] = false;
                    }
                    if (\in_array('level', $fields, false)) {
                        $node['level'] = 0;
                    }
                    if (\in_array('identity', $fields, false)) {
                        $node['identity'] = 0;
                    }
                }
            }

            $grid_config[] = $node;
        }

        return $grid_config;
    }


    private function load()
    {
        $this->config = Yaml::parseFile(self::$GRANT_CONFIG_PATH);

        /** @var RouterInterface $router */
        $router = $this->container->get('router');

        self::update_url_info($router, $this->config);

        self::flatten(['children' => $this->config], $this->config_flat);
    }

    private function update_url_info(RouterInterface $router, &$tree)
    {
        foreach ($tree as $key => &$node) {
            if (array_key_exists('children', $node)) {
                self::update_url_info($router, $node['children']);
            } else {
                if (array_key_exists('route', $node)) {
                    if ($router->getRouteCollection()->get($node['route']) !== null) {
                        $node['url'] = $router->generate($node['route']/*, array('slug' => 'my-blog-post')*/);
                    }
                }
            }
        }
    }

    private static function flatten($array, &$flat = [], $keySeparator = '-', $parent_key = '')
    {
        if (array_key_exists('children', $array)) {
            foreach ($array['children'] as $name => $value) {
                $key_path = $parent_key !== '' ? $parent_key . '-' . $name : $name;
                self::flatten($value, $flat, $keySeparator, $key_path);
            }
        } else {
            $flat[$parent_key] = $array;
        }
    }
}
