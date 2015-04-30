<?php

/*
 * This file is part of the Sonatra package.
 *
 * (c) François Pluchino <francois.pluchino@sonatra.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonatra\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Acl\Domain\UserSecurityIdentity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Util\ClassUtils;
use Sonatra\Bundle\SecurityBundle\Exception\InvalidArgumentException;
use FOS\UserBundle\Model\GroupInterface;
use FOS\UserBundle\Model\GroupableInterface;

/**
 * A SecurityIdentity implementation used for actual groups.
 *
 * For used the standard ACL Provider, the group security identity is a
 * UserSecurityIdentity with the group class name.
 *
 * @author François Pluchino <francois.pluchino@sonatra.com>
 */
final class GroupSecurityIdentity
{
    /**
     * Creates a group security identity from a GroupInterface.
     *
     * @param GroupInterface $group
     * @param string|null    $suffix
     *
     * @return UserSecurityIdentity
     */
    public static function fromAccount(GroupInterface $group, $suffix = null)
    {
        $suffix = null === $suffix ? '' : '__'.$suffix;
        $name = strtoupper($group->getName().$suffix);

        return new UserSecurityIdentity($name, ClassUtils::getRealClass($group));
    }

    /**
     * Creates a group security identity from a TokenInterface.
     *
     * @param TokenInterface $token
     *
     * @return UserSecurityIdentity[]
     *
     * @throws InvalidArgumentException When the user class not implements "FOS\UserBundle\Model\GroupableInterface"
     */
    public static function fromToken(TokenInterface $token)
    {
        $user = $token->getUser();

        if ($user instanceof GroupableInterface) {
            $sids = array();
            $groups = $user->getGroups();

            foreach ($groups as $group) {
                $sids[] = self::fromAccount($group);
            }

            return $sids;
        }

        throw new InvalidArgumentException('The user class must implement "FOS\UserBundle\Model\GroupableInterface"');
    }
}
