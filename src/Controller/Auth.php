<?php
namespace VVC\Controller;

use Firebase\JWT\JWT;

/**
 * Handles authentication & authorization
 */
class Auth
{
    /**
     * Wraps authentication token in a jwt
     * @param  int    $userId
     * @param  int    $userRole
     * @return Cookie
     */
    public static function encodeToken(int $userId, int $userRole)
    {
        global $req;

        try {
            $expireTime = time() + 24 * 60 * 60;    // cookie lives for one hour

            $jwt = \Firebase\JWT\JWT::encode([
                'iss'   =>  $req->getBaseUrl(),     // issuer (domain)
                'exp'   =>  $expireTime,            // user id
                'iat'   =>  time(),                 // issued at
                'nbf'   =>  time(),                 // not before (delay)
                'uid'   =>  $userId,                // user id
                'adm'   =>  $userRole == 1          // is admin or not
            ], getenv('SECRET_KEY'), 'HS256');

            $token = new \Symfony\Component\HttpFoundation\Cookie(
                'auth_token',
                $jwt,
                $expireTime,
                '/',
                getenv('COOKIE_DOMAIN')
            );

            return $token;

        } catch (\Exception $e) {
            // TODO logError($e)
            // throw $e;
            return null;
        }
    }

    /**
     * Makes token content available
     * @param  string $field - uid, adm
     * @return whole jwt OR single field value
     */
    public static function decodeToken(string $field = null)
    {
        global $req;
        JWT::$leeway = 1;   // for sync issues

        try {
            $jwt = JWT::decode(
            $req->cookies->get('auth_token'),
                getenv('SECRET_KEY'),
                ['HS256']
            );

            if ($field === null) {
                return $jwt;
            }

            return $jwt->{$field};

        } catch (\Exception $e) {
            // TODO logError($e)
            // throw $e;
            return false;
        }
    }

    public static function killToken(string $token)
    {
        return new \Symfony\Component\HttpFoundation\Cookie(
            $token,
            'Expired',
            time() - 24 * 60 * 60,
            '/',
            getenv('COOKIE_DOMAIN')
        );
    }

    public static function isAuthenticated() : bool
    {
        // stub for testing
        if (ACCESS_RIGHTS == 0) {
            return false;
        } elseif (ACCESS_RIGHTS > 0) {
            return true;
        }

        global $req;

        if (!$req->cookies->has('auth_token')) {
            // TODO log this, might be a bug,
            // but probably that's cookie running out of time (1 hour)
            return false;
        }

        if (!self::decodeToken()) {
            // TODO log this.
            return false;
        }

        return true;
    }

    public static function isAdmin() : bool
    {
        // stub for tests
        if (ACCESS_RIGHTS == 1) {
            return true;
        } elseif (ACCESS_RIGHTS == 0 || ACCESS_RIGHTS == 2) {
            return false;
        }

        if (!self::isAuthenticated()) {
            return false;
        }

        $isAdmin = self::decodeToken('adm');
        return (boolean)$isAdmin;
    }

    /**
     * Redirects user to login page if not signed in
     * @return true - if signed in
     */
    public static function requireAuth()
    {
        global $session;

        // stub for tests
        if (ACCESS_RIGHTS == 0) {
            $token = self::killToken('auth_token');
            $session->getFlashBag()->add('success', 'Please sign in first');
            return Router::redirect('/login', $token);
        } elseif (ACCESS_RIGHTS > 0) {
            return true;
        }

        if (!self::isAuthenticated()) {
            //$token = self::killToken('auth_token');
            $session->getFlashBag()->add('success', 'Please sign in first');
            return Router::redirect('/login', $token);
        }

        return true;
    }

    /**
     * Redirects user to homepage if not admin
     * @return true - if admin
     */
    public static function requireAdmin()
    {
        global $session;

        // stub for tests
        if (ACCESS_RIGHTS == 1) {
            return true;
        } elseif (ACCESS_RIGHTS == 0 || ACCESS_RIGHTS == 2) {
            $session->getFlashBag()->add(
                'fail', 'Not authorized to view this page contents'
            );
            return Router::redirect('/');
        }

        self::requireAuth();

        if (!self::isAdmin()) {
            $session->getFlashBag()->add(
                'fail', 'Not authorized to view this page contents'
            );
            return Router::redirect('/');
        }

        return true;
    }

    public static function logout()
    {
        global $session;

        $token = self::killToken('auth_token');
        $session->getFlashBag()->add('success', 'Logged out successfully');

        return Router::redirect('/login', $token);
    }

    public static function getUserId()
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        $uid = self::decodeToken('uid');
        return $uid;
    }
}
