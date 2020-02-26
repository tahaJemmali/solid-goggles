<?php

namespace LoginBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use EventBundle\Entity\Evenement;
use ECommerceBundle\Entity\Product;
use FOS\UserBundle\Model\User as BaseUser;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="LoginBundle\Repository\UserRepository")
 * @ORM\Table(name="user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $photo;

    /**
     * @ORM\Column(type="integer",nullable=true, options={"default" : 0})
     */
    protected $score;

    /**
     * @ORM\Column(type="string",nullable=false,length=255)
     */
    protected $address;


    /**
     * @ORM\Column(type="string",nullable=false,length=255)
     */
    protected $phone;

    /**
     * @ORM\Column(type="integer",nullable=true, options={"default" : 0})
     */
    protected  $pts_fidelite;

    /**
     * @ORM\ManyToMany(targetEntity="EventBundle\Entity\Evenement", mappedBy="users")
     */
    private $events;

    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    public function addEvents(Evenement $event)
    {
        $this->events[]=$event;
    }



    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }


    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address)
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * @param mixed $score
     */
    public function setScore($score)
    {
        $this->score = $score;
    }

    /**
     * @return mixed
     */
    public function getPtsFidelite()
    {
        return $this->pts_fidelite;
    }

    /**
     * @param mixed $pts_fidelite
     */
    public function setPtsFidelite($pts_fidelite)
    {
        $this->pts_fidelite = $pts_fidelite;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone)
    {

        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getPhoto()
    {
        return $this->photo;
    }

    /**
     * @param mixed $photo
     */
    public function setPhoto($photo)
    {
        $this->photo = $photo;
    }

    // for cart
    /**
     * @ORM\OneToMany(targetEntity="\ECommerceBundle\Entity\Cart", mappedBy="user",cascade={"persist","merge"} )
     */
    protected $product;
    // for cart



}
