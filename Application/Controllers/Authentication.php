<?php
class AuthenticationController extends Controller
{
    public function indexAction()
    {
        if (Oxygen_Auth::getIdentity())
            Oxygen_Utils::redirect('/');

        if ($this->getRequest()->isPost())
        {
            $user = null;

            if (!empty($_POST['login']) && !empty($_POST['password']))
            {
                $paramLogin = $_POST['login'];
                $paramPassword = $_POST['password'];

                $username = strtolower($paramLogin);
                $password = $paramPassword;

                $userList = (new Config())->loadFromJSONFile(APPLICATION_DIR.'/Config/users.json');

                $checkedUser = $userList->getOption($username, false);

                if (!empty($checkedUser) && !empty($checkedUser['password']))
                {
                    if (empty($checkedUser['hashType']))
                        $checkedUser['hashType'] = 'sha1';

                    $allowAccess = false;

                    switch ($checkedUser['hashType'])
                    {
                        case 'clear':
                            $allowAccess = ($password === $checkedUser['password']);
                        break;

                        case 'bcrypt':
                            $allowAccess = password_verify($password, $checkedUser['password']);
                        break;

                        default:
                            $allowAccess = (sha1($password) === $checkedUser['password']);
                        break;

                    }

                    if ($allowAccess)
                    {
                        $user = $checkedUser;
                        $user['login'] = $username;
                        unset($user['password']);
                    }
                }
            }

            // Test if user was found
            if (!empty($user))
            {
                Oxygen_Auth::setIdentity($user);
                Oxygen_Utils::redirect($_SERVER['REQUEST_URI']);
            }
            else
            {
                $this->view->loginError = 'Login failed: Incorrect login / password';
            }
        }
    }

    public function logoutAction()
    {
        $this->view->disableRender();
        $session = new Oxygen_Session();

        if (Oxygen_Auth::getIdentity())
        {
            Oxygen_Auth::setIdentity();
            $session->destroy();
        }

        Oxygen_Utils::redirect('/');
    }
}
