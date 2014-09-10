<?php
/**
 * Klasa reprezentująca użytkownika. Rozszerzenie klasy User z fosuserbundle
 */

namespace tsj\memsBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;

/**
 * User
 */
class User extends BaseUser
{
    private $favourites;

    private $facebook_id;

    protected $facebook_access_token;

    public function __construct()
    {
        parent::__construct();

        //tablica ulubionych wpisow
        $this->favourites = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Dodawanie "ulubionego" wpisu uzytkownikowi
     *
     * @param Item $item
     * @return $this
     */
    public function addFavourite(\tsj\memsBundle\Entity\Item $item)
    {
        if (!$this->favourites->contains($item))
            $this->favourites[] = $item;
        return $this;
    }

    /**
     * Metoda zwracająca ulubione wpisy uzytkownika
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFavourites()
    {
        return $this->favourites;
    }

    public function setFacebookId($fbId)
    {
        $this->facebook_id = $fbId;
    }

    public function getFacebookId()
    {
        return $this->facebook_id;
    }

    public function setFacebookAccessToken($token)
    {
        $this->facebook_access_token = $token;
    }

    public function getFacebookAccessToken()
    {
        return $this->facebook_access_token;
    }



}