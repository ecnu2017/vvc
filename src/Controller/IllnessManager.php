<?php
namespace VVC\Controller;

use VVC\Model\Database\Creator;
use VVC\Model\Database\Deleter;
use VVC\Model\Database\Reader;
use VVC\Model\Database\Updater;

/**
 * Admin controller to manage illnesses
 */
class IllnessManager extends AdminController
{
    public function showIllnessListPage()
    {
        try {
            $dbReader = new Reader();
            $list = $dbReader->getAllIllnesses();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get all illnesses', $e);
            $this->flash('fail', 'Database operation failed');
            $this->showDashboardPage();
        }

        $ymls = Uploader::getFiles(YML_DIRECTORY, ['yml']);
        $this->addTwigVar('files', $ymls);

        $this->setTemplate('admin_ills.twig');
        $this->addTwigVar('list', $list->getJustIllnesses());
        $this->render();
    }

    public function showIllnessPage($illnessId)
    {
        try {
            $dbReader = new Reader();
            $illness = $dbReader->getFullIllnessById($illnessId);
        } catch (\Exception $e) {
            Logger::log('db', 'error',
                'Failed to get full illness by id', $e, [
                'ill id' => $illnessId
            ]);
            $this->flash('fail', 'Database operation failed');
            $this->showIllnessListPage();
        }

        if (empty($illness)) {
            $this->flash('fail', 'Could not find illness record');
            $this->showIllnessListPage();
        }

        $this->setTemplate('illness.twig');
        $this->addTwigVar('ill', $illness);
        $this->render();
    }

    public function showAddIllnessPage()
    {
        $this->setTemplate('add_illness.twig');
        $this->render();
    }

    public function addIllness(array $post)
    {
        if (!$this->isClean($post)) {
            $this->flash('fail', 'Input contains invalid characters');
            return $this->showAddIllnessPage();
        }

        $name = $post['name'];
        $class = $post['class'];
        $text = $post['text'];

        try {
            $dbReader = new Reader();
            $illness = $dbReader->findIllnessByName($name);

            if (!empty($illness)) {
                $this->flash('fail', "This illness already exists - $name");
                return $this->showAddIllnessPage();
            }

            $dbCreator = new Creator();
            $illness = $dbCreator->createIllness($name, $class, $text);

            $this->flash('success', "$name added successfully");
            return Router::redirect('/admin/illnesses');

        } catch (\Exception $e) {
            Logger::log('db', 'error', "Failed to create illness $name (single)", $e);
            $this->flash('fail', 'Database operation failed');
            return $this->showAddIllnessPage();
        }
    }

    public function batchAddIllnesses(array $ills)
    {
        try {
            $dbReader = new Reader();
            $dbCreator = new Creator();
        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to open connection', $e);
            $this->flash('fail', 'Database connection failed');
            return Router::redirect('/admin/illnesses');
        }

        $good = [];
        $bad = [];

        foreach ($ills as $ill) {
            if (empty($ill['name'])
                || empty($ill['class'])
                || empty($ill['description'])
                || empty($ill['steps'])
            ) {
                $this->flash('fail', 'Some data is wrong or missing');
                return Router::redirect('/admin/illnesses');
            }

            if (!$this->isClean($ill)) {
                $bad['data'][] = $ill;
                continue;
            }

            $name = $ill['name'];
            $class = $ill['class'];
            $description = $ill['description'];

            foreach ($ill['steps'] as &$step) {
                foreach ($step['pictures'] as &$pic) {
                    $pic = PIC_DIRECTORY . $pic;
                }
                foreach ($step['videos'] as &$vid) {
                    if (!Uploader::isEmbedded($vid)) {
                        $vid = VID_DIRECTORY . $vid;
                    }
                }
            }
            $steps = $ill['steps'];

            foreach ($ill['drugs'] as &$drug) {
                $drug['picture'] = DRUG_DIRECTORY . $drug['picture'];
            }
            $drugs = $ill['drugs'];
            $payments = $ill['payments'];

            $duplicate = $dbReader->findIllnessByName($name);

            if ($duplicate) {
                $bad['duplicate'][] = $ill;
                continue;
            }

            $newIll = $dbCreator->createFullIllness(
                $name,
                $class,
                $description,
                $steps,
                $drugs,
                $payments
            );

            if (!$newIll) {
                Logger::log(
                    'db', 'error',
                    'Failed to create illness from batch file', $e, [
                    'ill name' => $name
                ]);
                $bad['db'][] = $ill;
                continue;
            } else {
                $good[] = $newIll;
            }
        }

        $this->prepareGoodBatchResults($good, $ills, ['id', 'name']);
        $this->prepareBadBatchResults($bad, $ills, ['name']);

        return Router::redirect('/admin/illnesses');
    }

    public function deleteIllness(int $illnessId)
    {
        try {
            $dbDeleter = new Deleter();
            $deleted = $dbDeleter->deleteIllness($illnessId);
            if (!$deleted) {
                $this->flash('fail',
                    "Could not delete illness <b>$illnessId</b>, try again"
                );
                return Router::redirect('/admin/illnesses');
            }

            $name = $deleted->getName();

            $this->flash('success', "Illness <b>$name</b> deleted");
            return Router::redirect('/admin/illnesses');

        } catch (\Exception $e) {
            Logger::log('db', 'error',
                "Failed to delete illness (single)", $e,
                ['ill id' => $illnessId]
            );
            $this->flash('fail', 'Database operation failed');
            return Router::redirect('/admin/illnesses');
        }
    }

    public function deleteIllnesses(array $ills)
    {
        $good = [];
        $bad = [];

        foreach ($ills as $illnessId) {
            try {
                $dbDeleter = new Deleter();
                $deleted = $dbDeleter->deleteIllness($illnessId);

                if (!$deleted) {
                    $bad['db'][] = $illnessId;
                } else {
                    $good[] = $deleted;
                }
            } catch (\Exception $e) {
                Logger::log('db', 'error',
                    "Failed to delete illness (batch)", $e,
                    ['ill id' => $illnessId]
                );
                $bad['db'][] = $illnessId;
            }
        }

        $this->prepareGoodBatchResults($good, $ills, ['id', 'name']);
        $this->prepareBadBatchResults($bad, $ills, ['id']);

        return Router::redirect('/admin/illnesses');
    }
}
