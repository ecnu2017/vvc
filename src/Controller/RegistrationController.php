<?php
namespace VVC\Controller;

use VVC\Model\Database\Reader;
use VVC\Model\Database\Creator;

/**
 * Processes user registration
 */
class RegistrationController extends BaseController
{
    protected $template = 'registration.twig';

    public function showRegistrationPage()
    {
        $this->render();
    }

    public function showRegistrationFailPage(string $username)
    {
        $this->addTwigVar('username', $username);

        $this->render();
    }

    /**
     * Verifies inputs, registers user and redirects to homepage
     * OR stays on registration page and displays errors
     * @param  array  $post - [username, password, confirm_password]
     */
    public function register(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('fail', 'Username or password contain invalid characters');
            return $this->showRegistrationFailPage($post['username']);
        }

        $username = $post['username'];
        $password = $post['password'];
        $confirmPassword = $post['confirm_password'];

        if ($password != $confirmPassword) {
            $this->flash('fail', 'Passwords do not match');
            return $this->showRegistrationFailPage($post['username']);
        }

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByUsername($username);

            if (!empty($user)) {
                $this->flash('fail', 'This username is already registered');
                return $this->showRegistrationFailPage($username);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($username, $hashed);

            $this->flash('success', 'Registration complete');
            $authToken = Auth::encodeToken($user->getId(), $user->getRoleId());
            Router::redirect('/', $authToken);
            
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to register new user', $e);
            $this->flash('fail', 'Registration failed, please try again');
            return $this->showRegistrationFailPage($username);
        }
    }
}
