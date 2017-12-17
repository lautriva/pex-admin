<?php
class PexAdmin_Acl
{
    // Roles
    CONST ROLE_WRITE = 1;
    CONST ROLE_READ = 2;

    public static $ROLES_NAMES = array(
        self::ROLE_WRITE     => 'write',
        self::ROLE_READ      => 'read'
    );

    public static $WRITE_PRIVILEGES = [
        'CREATE',
        'UPDATE',
        'DELETE',
    ];

    /**
     * Basic ACL check function to check access to a functionality
     * The $ressource parameter is reserved for future use
     *
     * @param string $ressource
     * @param string $privilege (CREATE, READ, UPDATE, DELETE)
     * @return boolean
     */
    public static function isAllowed($ressource, $privilege)
    {
        $result = false;

        $userData = Oxygen_Auth::getIdentity();

        if ($privilege == 'READ')
            $result = true;
        else if (!empty($userData['role']) && $userData['role'] == 'write')
            $result = true;

        return $result;
    }
}
