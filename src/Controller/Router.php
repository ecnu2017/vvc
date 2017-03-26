<?php
namespace VVC\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Processes http request and sends http response
 */
class Router
{
    public static $cookies = [];
    public static $route;

    /**
     * Finds appropriate controller based on request uri
     * and decides what method to call based on get&post data
     * @return void
     */
    public static function run()
    {
        global $req;
        global $session;

        $get = $req->query->all();
        $post = $req->request->all();
        $files = $req->files->all();

        if (!empty($req->cookies->get('file_to_overwrite'))) {
            print_r($req->cookies->get('file_to_overwrite'));
        }

        $route = self::getRoute();
        self::$route = $route;

        switch ($route['base']) {
            case '' :

                $controller = new BaseController();
                $controller->showHomepage();
                break;

            case 'login' :

                $controller = new LoginController();

                if (empty($post)) {
                    $controller->showLoginPage();
                } else {
                    $controller->login($post);
                }
                break;

            case 'registration' :

                $controller = new RegistrationController();

                if (empty($post)) {
                    $controller->showRegistrationPage();
                } else {
                    $controller->register($post);
                }
                break;

            case 'logout' :

                Auth::requireAuth();
                Auth::logout();
                break;

            case 'account' :

                Auth::requireAuth();
                $controller = new AccountController();

                if (empty($post)) {
                    $controller->showChangePasswordPage();
                } else {
                    $controller->changePassword($post);
                }
                break;

            case 'admin' :

                self::routeAdmin($route, $get, $post, $files);
                break;

            case '3d' :

                Auth::requireAuth();
                $controller = new NavigationController();
                $controller->showSelectRolePage();
                break;

            case 'catalog' :

                Auth::requireAuth();
                $controller = new CatalogController();

                if (is_numeric($route['page'])) {
                    $controller->showIllnessPage($route['page']);
                } else {
                    $controller->showCatalogPage();
                }
                break;

            default:
                // 404 NOT_FOUND
        }
    }

    /**
     *               uri                   action              tepmlate
     * -------------------------------------------------------------------------
     * /                                homepage            homepage.twig
     * /login                           login               login.twig
     * /catalog/1                       show illness        illness.twig
     * /catalog?s=                      search              catalog.twig
     * /admin/illnesses                 manage illnesses    admin_ill.twig
     * /admin/illnesses/1               view illness 1      view_ill.twig
     * /admin/illnesses/change/1        change illness 1    change_ill.twig
     *
     */

    /**
     * Extracts route from the request uri
     * @return array - processed route
     */
    public static function getRoute()
    {
        global $req;

        $route = [];
        $uri = trim($req->getPathInfo(), '/');
        $uri = explode('/', $uri);

        $route['base'] = $uri[0];
        $route['section'] = empty($uri[1]) ? '' : $uri[1];
        $route['action'] = empty($uri[2]) ? '' : $uri[2];
        $route['page'] = $uri[count($uri)-1];

        return $route;
    }

    /**
     * Adds cookie to put in the response
     * @param  string $name
     * @param  any $value
     * @return void
     */
    public static function addCookie(string $name, $value)
    {
        self::$cookies[] = [
            'name'  => $name,
            'value' => $value
        ];
    }

    /**
     * Prepares and sends http response and exits script
     * @param  [type] $html     - rendered twig output
     * @param  int $httpCode    - HTTP_FOUND, HTTP_NOT_FOUND, etc
     * @param  array $headers   - optional http headers
     * @param  array $authToken - optional authentication token
     * @return void
     */
    public static function sendResponse(
        $html,
        $httpCode = Response::HTTP_FOUND,
        array $headers = [],
        $authToken = null
    ) {
        $response = new Response($html, $httpCode, $headers);

        $response->headers->clearCookie('file_to_overwrite');

        if ($authToken) {
            $response->headers->setCookie($authToken);
        }

        foreach (self::$cookies as $cookie) {
            $newCookie = new \Symfony\Component\HttpFoundation\Cookie(
                $cookie['name'], $cookie['value']
            );

            $response->headers->setCookie($newCookie);
        }

        $response->send();
        exit;   // close current script execution after redirect
    }

    /**
     * Internal redirect to process request at another Location
     * @param  string $uri
     * @param  Cookie Object $authToken
     * @return void
     */
    public static function redirect(string $uri, $authToken = null)
    {
        self::sendResponse(
            null,
            Response::HTTP_FOUND,
            ['location' => $uri],
            $authToken
        );
    }

    /**
     * Handles routing inside Admin Dashboard
     * @param  array  $route
     * @param  array  $get   - GET data
     * @param  array  $post  - POST data
     * @param  array  $files - file uploads
     * @return void
     */
    public static function routeAdmin(
        array $route, array $get, array $post, array $files
    ) {
        Auth::requireAdmin();
        $controller = new AdminController();

        switch ($route['section']) {
            case '' :

                $controller->showDashboardPage();
                break;

            case 'accounts' :
                $controller = new AccountManager();

                // Check if user id in uri is valid
                if (in_array($route['action'], ['change'])
                    && (empty($route['page'])
                    || !is_numeric($route['page']))) {
                    self::redirect('/admin/accounts');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showAccountListPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            $controller->showAddAccountPage();
                        } else {
                            $controller->addAccount($post);
                        }
                        break;

                    case 'add-many' :
                        $accs = Uploader::readYml(
                            $controller, $post['yml'], '/admin/accounts'
                        );
                        $controller->batchAddAccounts($accs);
                        break;

                    case 'change' :
                        if (empty($post)) {
                            $controller->showChangeAccountPage($route['page']);
                        } else {
                            $controller->changeAccount($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        // print_r($post);exit;
                        if (empty($post['id'])) {
                            $controller->showAccountListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteAccount($post['id'][0]);
                        } else {
                            $controller->deleteAccounts($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/accounts');
                }
                break;

            case 'illnesses' :

                $controller = new IllnessManager();

                // Check if illness id in uri is valid
                if (in_array($route['action'], ['view', 'change'])
                    && (empty($route['page'])
                    || !is_numeric($route['page']))) {
                    self::redirect('/admin/illnesses');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showIllnessListPage();
                        break;

                    case 'view' :
                        $controller->showIllnessPage($route['page']);
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                          $controller->showAddIllnessPage();
                        } else {
                          $controller->addIllness($post);
                        }
                        break;

                    case 'add-many' :
                        $ills = Uploader::readYml(
                            $controller, $post['yml'], '/admin/illnesses'
                        );
                        $controller->batchAddIllnesses($ills);
                        break;

                    case 'change' :
                        // TODO
                        Router::redirect('/admin/illnesses');
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showIllnessListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteIllness($post['id'][0]);
                        } else {
                            $controller->deleteIllnesses($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/illnesses');
                }
                break;

            case 'drugs' :

                $controller = new DrugManager();

                // Check if drug id in uri is valid
                if (in_array($route['action'], ['view', 'change'])
                    && (empty($route['page']))) {
                    self::redirect('/admin/drugs');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showDrugListPage();
                        break;


                    case 'add-single' :
                        if (empty($post)) {
                            $controller->showAddDrugPage();
                        } else {
                            if (!empty($files['pic'])) {
                                $pic = Uploader::uploadPicture(
                                    DRUG_DIRECTORY,
                                    $controller,
                                    $files['pic'],
                                    '/admin/drugs/add-single'
                                );
                                $post['picture'] = $pic;
                            }
                            $controller->addDrug($post);
                        }
                        break;

                    case 'add-many' :
                        $drugs = Uploader::readYml(
                            $controller, $post['yml'], '/admin/drugs'
                        );
                        $controller->batchAddDrugs($drugs);
                        break;

                    case 'change' :
                        if (empty($post)) {
                            $controller->showChangeDrugPage($route['page']);
                        } else {
                            if (!empty($files['pic'])) {
                                $pic = Uploader::uploadPicture(
                                    DRUG_DIRECTORY,
                                    $controller,
                                    $files['pic'],
                                    '/admin/drugs/change/' . $route['page']
                                );
                                $post['picture'] = $pic;
                            }
                            $controller->changeDrug($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showDrugListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deleteDrug($post['id'][0]);
                        } else {
                            $controller->deleteDrugs($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/drugs');
                }
                break;

            case 'hospitalization' :

                self::redirect('/admin');
                break;

            case 'payments' :

                $controller = new PaymentManager();

                // Check if payment id in uri is valid
                if (in_array($route['action'], ['change'])
                    && (empty($route['page']))) {
                    self::redirect('/admin/payments');
                }

                switch ($route['action']) {
                    case '' :
                        $controller->showPaymentListPage();
                        break;

                    case 'add-single' :
                        if (empty($post)) {
                            $controller->showAddPaymentPage();
                        } else {
                            $controller->addPayment($post);
                        }
                        break;

                    case 'add-many' :
                        $payments = Uploader::readYml(
                            $controller, $post['yml'], '/admin/payments'
                        );
                        $controller->batchAddPayments($payments);
                        break;

                    case 'change' :
                        if (empty($post)) {
                            $controller->showChangePaymentPage($route['page']);
                        } else {
                            $controller->changePayment($route['page'], $post);
                        }
                        break;

                    case 'delete' :
                        if (empty($post['id'])) {
                            $controller->showPaymentListPage();
                        } elseif (count($post['id']) == 1){
                            $controller->deletePayment($post['id'][0]);
                        } else {
                            $controller->deletePayments($post['id']);
                        }
                        break;

                    default :
                        self::redirect('/admin/payments');
                }
                break;

            case 'uploads' :

                $controller = new UploadManager();

                // Check if tab in uri is valid
                if (!in_array($route['action'], ['', 'pictures', 'drugs'])) {
                    self::redirect('/admin/uploads');
                }

                switch ($route['action']) {
                    case '' :
                    case 'pictures' :
                        switch ($route['page']) {
                            case 'add' :
                                if (!empty($post)) {
                                    $controller->addPicturesToIllness($post);
                                } else {
                                    $controller->showUploadsPage();
                                }
                                break;

                            case 'delete' :
                                if (!empty($post['pics'])) {
                                    $controller->deletePictures(
                                        PIC_DIRECTORY,
                                        $post['pics'],
                                        '/admin/uploads/pictures'
                                    );
                                } else {
                                    $controller->showUploadsPage();
                                }
                                break;

                            case 'upload' :
                                if (!empty($files['uploads'])) {
                                    Uploader::uploadPictures(
                                        PIC_DIRECTORY,
                                        $controller,
                                        $files['uploads'],
                                        '/admin/uploads/pictures'
                                    );
                                } else {
                                    $controller->showUploadsPage();
                                }
                                break;

                            default :
                                if (!empty($post['pics_per_page'])) {
                                    $controller->showUploadsPage(
                                        $route['action'],
                                        $post['pics_per_page']
                                    );
                                } else {
                                    $controller->showUploadsPage();
                                }
                        }
                        break;

                    case 'drugs' :
                        switch ($route['page']) {
                            case 'delete' :
                                if (!empty($post['drugPics'])) {
                                    $controller->deletePictures(
                                        DRUG_DIRECTORY,
                                        $post['drugPics'],
                                        '/admin/uploads/drugs'
                                    );
                                } else {
                                    $controller->showUploadsPage('drugs');
                                }
                                break;

                            case 'upload' :
                                if (!empty($files['drugUploads'])) {
                                    Uploader::uploadPictures(
                                        DRUG_DIRECTORY,
                                        $controller,
                                        $files['drugUploads'],
                                        '/admin/uploads/drugs'
                                    );
                                } else {
                                    $controller->showUploadsPage('drugs');
                                }
                                break;

                            default :
                                if (!empty($post['pics_per_page'])) {
                                    $controller->showUploadsPage(
                                        $route['action'],
                                        $post['pics_per_page']
                                    );
                                } else {
                                    $controller->showUploadsPage('drugs');
                                }
                        }
                        break;

                }
                break;

            default :
                self::redirect('/admin');
        }
    }
}
