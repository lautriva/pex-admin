<?php
class Oxygen_Cypher
{
	/**
	 * hash a password for a provided username
	 * 
	 * @param string $username
	 * @param string $password
	 * @param string $algo
	 * @param array $options
	 * @return string 
	 */
    public static function hash_password($username, $password, $algo = '', $options = array())
    {
        $password = substr($password, 0 , 100); // Truncate passwords longer than 100 chars
        $salt = substr(bin2hex(str_pad('', 22, strtolower($username))), 0 , 22);

        $defaultOptions = [
            'cost' => 10,
            'salt' => $salt,
        ];

        // Merge provided options with defaults ones
        $options = array_merge($defaultOptions, $options);

        $result = password_hash($password, !empty($algo) ? $algo : PASSWORD_BCRYPT, $options);

        return $result;
    }

    /**
     * XOR the provided string with the key 
     * 
     * @param string $text the text to encrypt
     * @param string $key the cypher key
     * @return string|boolean
     */
    public static function xor_string($text, $key)
    {
        $outText = '';

        for($i=0;$i<strlen($text);)
        {
            for($j=0;($j<strlen($key) && $i<strlen($text));$j++,$i++)
            {
                $outText .= $text{$i} ^ $key{$j};
            }
        }
        return $outText;
    }
}
