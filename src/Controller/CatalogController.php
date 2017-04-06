<?php
namespace VVC\Controller;

use VVC\Model\Database\Reader;

const ILLS_PER_PAGE = 8;

class CatalogController extends BaseController
{
    protected $template = 'catalog.twig';

    public function showCatalogPage()
    {
        try {
            $dbReader = new Reader();
            $catalog = $dbReader->getAllIllnesses();
        } catch (\Exception $e ) {
            Logger::log('db', 'error', 'Failed to get all illnesses', $e);
            $this->flash('fail', 'Database operation failed');
            Router::redirect('/');
        }

        $this->addTwigVar('catalog', $catalog->getRecords());
        $this->setTemplate('frontend/catalog.twig');
        $this->render();
    }

    public function showClassPage(string $class, int $page)
    {
        $offset = ($page - 1) * ILLS_PER_PAGE;
        $limit = ILLS_PER_PAGE;

        try {
            $dbReader = new Reader();
            $illnesses = $dbReader->getIllnessesByClass($class, $limit, $offset);

            $count = count($illnesses);
            foreach ($illnesses as $ill) {
                $count++;
            }
            $totalPages = $count / ILLS_PER_PAGE;

        } catch (\Exception $e ) {
            Logger::log('db', 'error', 'Failed to get all illnesses', $e);
            $this->flash('fail', 'Database operation failed');
            Router::redirect('/');
        }

        $this->addTwigVar('class', $class);
        $this->addTwigVar('ills', $illnesses);
        $this->addTwigVar('page', $page);
        $this->addTwigVar('totalPages', $totalPages);
        $this->setTemplate('frontend/class_ills.twig');
        $this->render();
    }

    public function showIllnessPage($illnessId)
    {
        try {
            $dbReader = new Reader();
            $illness = $dbReader->getFullIllnessById($illnessId);
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get full illness by id', $e);
            $this->flash('fail', 'Database operation failed');
            return $this->showCatalogPage();
        }

        if (empty($illness)) {
            $this->flash('fail', 'Could not find illness record');
            $this->showCatalogPage();
        }

        $this->setTemplate('illness.twig');
        $this->addTwigVar('ill', $illness);
        $this->render();
    }

    public function showSearchPage(string $search, int $page)
    {
        $offset = ($page - 1) * ILLS_PER_PAGE;
        $limit = ILLS_PER_PAGE;

        try {
            $dbReader = new Reader();
            $illnesses = $dbReader->searchIllnesses($search, $limit, $offset);

            $count = count($illnesses);
            foreach ($illnesses as $ill) {
                $count++;
            }
            $totalPages = $count / ILLS_PER_PAGE;

        } catch (\Exception $e ) {
            Logger::log('db', 'error', 'Failed to search illnesses', $e);
            $this->flash('fail', 'Database operation failed');
            Router::redirect('/catalog');
        }

        $this->addTwigVar('search', $search);
        $this->addTwigVar('ills', $illnesses);
        $this->addTwigVar('page', $page);
        $this->addTwigVar('totalPages', $totalPages);
        $this->setTemplate('frontend/search_results.twig');
        $this->render();
    }
}
