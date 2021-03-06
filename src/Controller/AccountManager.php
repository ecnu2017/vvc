<?php
namespace VVC\Controller;

use VVC\Model\Database\Creator;
use VVC\Model\Database\Deleter;
use VVC\Model\Database\Reader;
use VVC\Model\Database\Updater;

/**
 * Admin controller for managing user accounts
 */
class AccountManager extends AdminController
{
    public function showAccountListPage()
    {
        try {
            $dbReader = new Reader();
            $users = $dbReader->getAllUsers();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all users', $e);
            $this->flash('fail', 'Database operation failed');
            $this->showDashboardPage();
        }

        $ymls = Uploader::getFiles(YML_DIRECTORY, ['yml']);
        $this->addTwigVar('files', $ymls);

        $this->setTemplate('admin_accs.twig');
        $this->addTwigVar('users', $users);
        $this->render();
    }

    public function showAddAccountPage(
        string $username = null, int $roleId = null
    ) {
        $this->setTemplate('add_account.twig');
        $this->addTwigVar('username', $username);
        $this->addTwigVar('role', $roleId);
        $this->render();
    }

    public function addAccount(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('fail', 'Username or password contain invalid characters');
            return $this->showAddAccountPage($post['username'], $post['role_id']);
        }

        $username = $post['username'];
        $password = $post['password'];
        $roleId = $post['role_id'] ?? 2;

        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserByUsername($username);

            if (!empty($user)) {
                $this->flash('fail', 'This username is already registered');
                return $this->showAddAccountPage($username, $roleId);
            }

            $hashed = password_hash($password, PASSWORD_DEFAULT);

            $dbCreator = new Creator();
            $user = $dbCreator->createUser($username, $hashed, $roleId);

            $this->flash('success', 'Added successfully');
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to create user (single)', $e);
            $this->flash('fail', 'Database operation failed');
            return $this->showAddAccountPage($username, $roleId);
        }
    }

    public function batchAddAccounts(array $users)
    {
        try {
            $dbReader = new Reader();
            $dbCreator = new Creator();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to open connection', $e);
            $this->flash('fail', 'Database connection failed');
            return Router::redirect('/admin/accounts');
        }

        $good = [];
        $bad = [];

        foreach ($users as $user) {
            if (empty($user['username'])
                || empty($user['roleId'])
            ) {
                $this->flash('fail', 'Some data is wrong or missing');
                return Router::redirect('/admin/accounts');
            }

            if (!$this->isClean($user)) {
                $bad['data'][] = $user;
                continue;
            }

            try {
                $duplicate = $dbReader->findUserByUsername($user['username']);

                if ($duplicate) {
                    $bad['duplicate'][] = $user;
                    continue;
                }

                $password = password_hash(BATCH_USER_PASSWORD, PASSWORD_DEFAULT);
                $newUser = $dbCreator->createUser(
                    $user['username'], $password, $user['roleId']
                );

                $good[] = $newUser;

            } catch (\Exception $e) {
                Logger::log(
                    'db', 'error', 'Failed to create user (batch)', $e
                );
                $bad['db'][] = $user;
                continue;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'username']);
        $this->prepareBadBatchResults($bad, $users, ['username']);

        return Router::redirect('/admin/accounts');
    }

    public function showChangeAccountPage(int $userId)
    {
        try {
            $dbReader = new Reader();
            $user = $dbReader->findUserById($userId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to find user by id', $e);
            $this->flash('fail', 'Database operation failed');
            Router::redirect('/admin/accounts');
        }

        if (empty($user)) {
            $this->flash('fail', 'User not found');
            Router::redirect('/admin/accounts');
        }

        $this->setTemplate('change_account.twig');
        $this->addTwigVar('user', $user);
        $this->render();
    }

    public function changeAccount(int $userId, array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('fail', 'Username or password contain invalid characters');
            return $this->showChangeAccountPage($userId);
        }

        $username = $post['username'];
        $roleId = $post['role_id'];
        $password = $post['password'];

        try {
            $dbReader = new Reader();

            $old = $dbReader->findUserById($userId);
            if (empty($old)) {
                $this->flash('fail', 'Some problem occurred, please try again');
                return $this->showChangeAccountPage($userId);
            }

            if ($old->getUsername() != $username) {
                // check for duplicate username
                $duplicate = $dbReader->findUserByUsername($username);
                if (!empty($duplicate)) {
                    $this->flash('fail', 'This username is already registered');
                    return $this->showChangeAccountPage($userId);
                }
            }

            $password = empty($password)
                ? $old->getPassword()
                : password_hash($password, PASSWORD_DEFAULT);

            $dbUpdater = new Updater();
            $dbUpdater->updateUser(
                $userId,
                $username,
                $password,
                $roleId,
                $old->getCreatedAt()
            );

            $this->flash('success', 'Account Updated');
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                'Failed to change user account', $e, [
                'user id' => $userId,
                'username' => $username,
            ]);
            $this->flash('fail', 'Database operation failed');
            return $this->showChangeAccountPage($userId);
        }
    }

    public function deleteAccount(int $userId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteUser($userId);
            if (!$deleted) {
                $this->flash('fail',
                    "Could not delete user <b>$userId</b>, try again"
                );
                return Router::redirect('/admin/accounts');
            }

            $name = $deleted->getUsername();

            $this->flash('success', "User <b>$name</b> deleted");
            return Router::redirect('/admin/accounts');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                "Failed to delete user (single)", $e,
                ['user id' => $userId]
            );
            $this->flash('fail', 'Database operation failed');
            return Router::redirect('/admin/accounts');
        }
    }

    public function deleteAccounts(array $users)
    {
        $good = [];
        $bad = [];

        foreach ($users as $userId) {
            try {
                $dbDeleter = new Deleter();
                $deleted = $dbDeleter->deleteUser($userId);

                if (!$deleted) {
                    $bad['db'][] = $userId;
                } else {
                    $good[] = $deleted;
                }
            } catch (\Exception $e) {
                Logger::log('db', 'error',
                    "Failed to delete user (batch)", $e,
                    ['user id' => $userId]
                );
                $bad['db'][] = $userId;
            }
        }

        $this->prepareGoodBatchResults($good, $users, ['id', 'username']);
        $this->prepareBadBatchResults($bad, $users, ['id']);

        return Router::redirect('/admin/accounts');
    }
}
