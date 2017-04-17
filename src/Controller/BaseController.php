<?php
namespace VVC\Controller;

use Symfony\Component\HttpFoundation\Response;

const TEMPLATES_DIR = __DIR__ . '/../View/templates';

/**
 * Basic template for all page controllers
 * Loads twig, wraps all needed variables via render()
 * and transfers response via Router
 *
 * Interfaces: replacing template, replacing http response code,
 * adding variables to twig, adding flash messages to twig,
 * cleaning up input variables
 */
class BaseController
{
    protected $twig;
    protected $template = 'frontend/home-navi.twig';
    protected $httpCode = Response::HTTP_FOUND;
    protected $vars = [];

    protected $logger;

    public function __construct()
    {
        global $logger;
        $this->logger = $logger;

        $this->loadTwig();
    }

    public function showHomepage()
    {
        $this->render();
    }

    /**
     * Prepares twig environment and loads default functions to twig,
     * e.g. auth functions
     */
    public function loadTwig()
    {
        $loader = new \Twig_Loader_Filesystem(TEMPLATES_DIR);
        $this->twig = new \Twig_Environment($loader);

        $this->addTwigFunc('authenticated', 'isAuthenticated', 'VVC\Controller\Auth');
        $this->addTwigFunc('admin', 'isAdmin', 'VVC\Controller\Auth');
        $this->addTwigFunc('short', 'short', $this);
        $this->addTwigFunc('active', 'active', $this);

        $this->twig->addFilter(new \Twig_Filter(
            'name', [$this, 'name']
        ));
    }

    /**
     * Renders current template, wraps variables for twig and sends response
     */
    public function render()
    {
        $page = $this->twig->load($this->template);
        $this->prepareFlashMessages();
        $html = $page->render($this->vars);
        Router::sendResponse($html);
    }

    public function setTemplate(string $template)
    {
        $this->template = $template;
    }

    public function setHttpCode(int $httpCode)
    {
        $this->httpCode = $httpCode;
    }

    /**
     * Adds a key-value pair for use in a twig template
     * @param string $varName
     * @param any $varValue
     */
    public function addTwigVar(string $varName, $varValue)
    {
        $this->vars[$varName] = $varValue;
    }

    /**
     * Load function for use in twig template
     * @param string $twigFuncName - how to refer inside twig
     * @param string $funcName     - real function name
     * @param $handle
     *        class name if using static function
     *        item instance if using non static function
     *        null if using standalone function
     */
    public function addTwigFunc(
        string $twigFuncName,
        string $funcName,
        $handle = null)
    {
        if ($handle) {
            $this->twig->addFunction(new \Twig_Function(
                $twigFuncName, [$handle, $funcName]
            ));
        } else {
            $this->twig->addFunction(new \Twig_Function(
                $twigFuncName, $funcName
            ));
        }
    }

    /**
     * Shortcut for adding flash messages to
     * the session's flashBag
     * @param string $msgType   - 'success', 'fail'
     * @param string $msg
     */
    public function flash(string $msgType, string $msg)
    {
        global $session;

        $session->getFlashBag()->add($msgType, $msg);
    }

    /**
     * Extracts and prepares flash messages for future use in a twig template
     */
    public function prepareFlashMessages()
    {
        global $session;

        $messages = [];
        foreach ($session->getFlashBag()->all() as $msgType => $msg) {
            $messages[$msgType] = $msg;
        }
        //print_r($messages);
        //exit;

        $this->addTwigVar('messages', $messages);
    }

    /**
     * Cleans up input data and returns true if it has not changed
     * @param  array $vars - by reference -> possibly changed after cleanup
     * @return bool
     */
    public function isClean(array &$vars) : bool
    {
        $beforeCleanup = $vars;
        $this->cleanupVars($vars);
        // pe($vars);
        return $beforeCleanup == $vars;
    }

    public function cleanupVars(array &$vars)
    {
        foreach ($vars as $key => &$val) {
            if (is_array($val)) {
                // p($val);
                $this->cleanupVars($val);
            } else {
                // p($val); n();
                switch (strtolower($key)) {
                    case 'password' :
                        $val = trim(filter_var($val, FILTER_SANITIZE_STRING));
                        $vars[$key] = str_replace(' ', '', $val);
                        break;
                    case 'username' :
                        $val = trim(filter_var($val, FILTER_SANITIZE_STRING));
                        $vars[$key] = str_replace(' ', '', $val);
                        break;
                    case 'text' :
                    case 'description' :
                    // case 'name' :
                        $val = trim(filter_var($val, FILTER_SANITIZE_STRING));
                        break;
                    case 'roleid'  :
                    case 'role_id' :
                    case 'number' :
                        $val = trim(filter_var($val, FILTER_SANITIZE_NUMBER_INT));
                        break;
                    case 'cost' :
                        $val = trim(filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION));
                        break;
                    default :
                        $vars[$key] = trim(filter_var($val, FILTER_SANITIZE_STRING));
                }
            }
        }
    }

    /**
     * Returns first $limit characters of a string
     * @param  string  full
     * @param  int     limit
     * @return string
     */
     public function short(string $full, $limit = 20) : string
     {
         preg_match(PATTERN_CHINESE, $full, $matches);

         if (!empty($matches)) {
             if (mb_strlen($full) >= $limit) {
                 return mb_substr($full, 0, $limit-1) . "...";
             } else {
                 return mb_substr($full, 0, $limit);
             }
         } else {
             if (strlen($full) >= $limit) {
                 return substr($full, 0, $limit-1) . "...";
             } else {
                 return substr($full, 0, $limit);
             }
         }
     }

    /**
     * Returns only the filename in the path
     * @param  string  $path - full path
     * @return string
     */
    public function name(string $path) : string
    {
        $arr = explode('/', $path);
        return $arr[count($arr) - 1];
    }

    public function active(string $section)
    {
        if ($section == Router::$route['section']) {
            return 'active';
        } else {
            return '';
        }
    }

    /**
     * Processes and adds to flashes successful batch action results
     * @param  array  $good   - succeeded entries
     * @param  array  $data   - all entries
     * @param  array  $fields - column headers
     * @param  string $msg    - e.g. Successful:
     * @return void
     */
    public function prepareGoodBatchResults(
        array $good, array $data, array $fields, $msg = "Successful: "
    ) {
        $total = count($data);
        // p($data);pe($good);
        if (!empty($good)) {
            $goodOut = $msg . count($good) . "/$total\n\n";
            for ($i = 0; $i < count($fields); $i++) {
                if ($i != 0) $goodOut .= " - ";
                $goodOut .= "[" . $fields[$i] . "]";
            }

            foreach ($good as $item) {
                $goodOut .= $this->getBatchResultsRow($item, $fields);
            }

            $this->flash('success', $goodOut);
        }
    }

    /**
     * Processes and adds to flashes failed batch action results
     * @param  array  $bad    - failed entries
     * @param  array  $data   - all entries
     * @param  array  $fields - column headers
     * @param  string $msg    - e.g. Failed:
     * @return void
     */
    public function prepareBadBatchResults(
        array $bad, array $data, array $fields, $msg = "Failed: "
    ) {
        $total = count($data);
        // p($data);pe($bad);
        if (!empty($bad)) {
            $badCount = 0;
            foreach ($bad as $reason) {
                foreach ($reason as $item) {
                    $badCount++;
                }
            }

            $badOut = $msg . $badCount . "/$total";

            foreach ($bad as $reason => $items) {
                switch ($reason) {

                    case 'data' :
                        $badOut .= "\n\nBad input data:\n";
                        for ($i = 0; $i < count($fields); $i++) {
                            if ($i != 0) $badOut .= " - ";
                            $badOut .= "[" . $fields[$i] . "]";
                        }
                        foreach ($items as $item) {
                            $badOut .= $this->getBatchResultsRow($item, $fields);
                        }
                        break;

                    case 'duplicate' :
                        $badOut .= "\n\nDuplicates:\n";
                        for ($i = 0; $i < count($fields); $i++) {
                            if ($i != 0) $badOut .= " - ";
                            $badOut .= "[" . $fields[$i] . "]";
                        }
                        foreach ($items as $item) {
                            $badOut .= $this->getBatchResultsRow($item, $fields);
                        }
                        break;

                    case 'db' :
                        $badOut .= "\n\nDatabase failure:\n";
                        for ($i = 0; $i < count($fields); $i++) {
                            if ($i != 0) $badOut .= " - ";
                            $badOut .= "[" . $fields[$i] . "]";
                        }
                        foreach ($items as $item) {
                            $badOut .= $this->getBatchResultsRow($item, $fields);
                        }
                        break;

                    case 'fs' :
                        $badOut .= "\n\nFile System failure:\n";
                        for ($i = 0; $i < count($fields); $i++) {
                            if ($i != 0) $badOut .= " - ";
                            $badOut .= "[" . $fields[$i] . "]";
                        }
                        foreach ($items as $item) {
                            $badOut .= $this->getBatchResultsRow($item, $fields);
                        }
                        break;

                    case 'ext' :
                        $badOut .= "\n\nWrong file extension\n";
                        for ($i = 0; $i < count($fields); $i++) {
                            if ($i != 0) $badOut .= " - ";
                            $badOut .= "[" . $fields[$i] . "]";
                        }
                        foreach ($items as $item) {
                            $badOut .= $this->getBatchResultsRow($item, $fields);
                        }
                        break;
                }
            }

            $this->flash('warning', $badOut);
        }
    }

    /**
     * Returns results entry output, used both for bad and goor results
     * @param  array OR object OR scalar $item    - entry
     * @param  array  $fields          - array values OR class properties
     * @return string
     */
    public function getBatchResultsRow($item, array $fields) : string
    {
        $out = "\n";
        for ($i = 0; $i < count($fields); $i++) {
            if ($i != 0) $out .= " - ";

            if (is_array($item)) {
                // if item is array, output uses array fields values
                $out .= $item[$fields[$i]];
            } elseif (is_object($item)) {
                // if item is object, output uses class getters
                $getter = 'get' . ucfirst($fields[$i]);
                $out .= $item->$getter();
            } else {
                // if item is scalar type, just print it out
                $out .= $item;
            }
        }
        return $out;
    }
}
