<?php

class Oxygen_Auth
{
	/**
	 * Get the user identity
	 *
	 * @return mixed
	 */
    public static function getIdentity()
    {
        $session = new Oxygen_Session();

        return $session->_OXYGEN_SESSION_IDENTITY;
    }

    /**
     * Set the user identity
     *
     */
    public static function setIdentity($user = null)
    {
        $session = new Oxygen_Session();

        if ($user)
            $session->_OXYGEN_SESSION_IDENTITY = $user;
        else
            unset($session->_OXYGEN_SESSION_IDENTITY);
    }
}