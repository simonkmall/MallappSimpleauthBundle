<?php

namespace Mallapp\SimpleauthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * SimpleUserEntity
 *
 * @ORM\Table(name="simpleuserentity")
 * @ORM\Entity
 */
class SimpleUserEntity extends BaseUser
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

}

