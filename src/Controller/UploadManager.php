<?php
namespace VVC\Controller;

use VVC\Model\Database\Reader;
use VVC\Model\Database\Updater;
use VVC\Model\Database\Creator;
use VVC\Model\Database\Deleter;

class UploadManager extends AdminController
{
    public function showUploadsPage($tab = '', string $picsNum = null)
    {
        // How many pictures to display
        if ($picsNum === 'low') {
            $picsPerRow = 4;
            $picsPerPage = $picsPerRow * 3;
        } else {
            $picsNum = 'high';
            $picsPerRow = 6;
            $picsPerPage = $picsPerRow * 4;
        }

        $tab = ($tab == '') ? 'pictures' : $tab;

        // List of pictures from uploads folder
        $illFiles = Uploader::getFiles(PIC_DIRECTORY, ['png', 'jpg', 'gif']);
        $drugFiles = Uploader::getFiles(DRUG_DIRECTORY, ['png', 'jpg', 'gif']);

        $illPics = [];
        for ($i = 0; $i < count($illFiles); $i++) {
            $illPics[($i/$picsPerPage) + 1][] = $illFiles[$i];
        }

        $drugPics = [];
        for ($i = 0; $i < count($drugFiles); $i++) {
            $drugPics[($i/$picsPerPage) + 1][] = $drugFiles[$i];
        }

        try {
            $dbReader = new Reader();

            // These are for display when linking picture to new illlness
            $ills = $dbReader->getAllIllnesses();
            $steps = $dbReader->getAllSteps();
            // $drugs = $dbReader->getAllDrugs();

            $assocIllnesses = [];
            foreach ($illFiles as $pic) {
                // Find what illnesses the picture is linked to
                $assocIllnesses[$pic] = $dbReader->findIllnessesByPicture($pic);
            }

            $assocDrugs = [];
            foreach ($drugFiles as $pic) {
                // Find what drug the picture is linked to
                $assocDrugs[$pic] = $dbReader->findDrugByPicture($pic);
            }

        } catch (\Exception $e) {
            Logger::log('db', 'error', 'Failed to get info from DB', $e);
            $this->flash('fail', 'Database operation failed');
            return $this->showDashboardPage();
        }

        $this->addTwigVar('illPics', $illPics);
        $this->addTwigVar('drugPics', $drugPics);

        $this->addTwigVar('ills', $ills->getJustIllnesses());
        $this->addTwigVar('steps', $steps);
        // $this->addTwigVar('drugs', $drugs);

        $this->addTwigVar('assocIlls', $assocIllnesses);
        $this->addTwigVar('assocDrugs', $assocDrugs);

        $this->addTwigVar('picsNum', $picsNum);
        $this->addTwigVar('picsPerRow', $picsPerRow);
        $this->addTwigVar('tab', $tab);

        $this->setTemplate('admin_uploads.twig');
        $this->render();
    }

    public function deletePictures(string $dir, array $pics, string $redirect)
    {
        $good = [];
        $bad = [];

        foreach ($pics as $key => $pic) {
            if (empty($pic)) {
                unset($pics[$key]);
            }
        }

        foreach ($pics as $pic) {
            try {
                $dbDeleter = new Deleter();
                $deletedFromDb = $dbDeleter->deletePicture($dir . $pic);

                if (!$deletedFromDb) {
                    $bad['db'][] = $pic;
                    continue;
                }

                $deletedFromFs = Uploader::deleteFile($dir . $pic);
                if (!$deletedFromFs) {
                    $bad['fs'][] = $pic;
                    continue;
                }

                $good[] = $pic;

            } catch (\Exception $e) {
                Logger::log('db', 'error',
                    "Failed to delete picture $pic", $e
                );
                $bad['db'][] = $pic;
            }
        }

        $this->prepareGoodBatchResults($good, $pics, ['name']);
        $this->prepareBadBatchResults($bad, $pics, ['name']);

        return Router::redirect($redirect);
    }

    public function addPicturesToIllness(array $post)
    {
        if (empty($post['illnessId']) ||
            empty($post['stepNum'])
        ) {
            $this->flash('fail', 'Some details are missing');
            return $this->showUploadsPage();
        }

        foreach ($post['pics'] as $key => $pic) {
            if (empty($pic)) {
                unset($post['pics'][$key]);
            }
        }

        if (empty($post['pics'])) {
            $this->flash('fail', 'No pictures selected');
            return $this->showUploadsPage();
        }

        $illnessId = $post['illnessId'];
        $stepNum = $post['stepNum'];
        $pics = $post['pics'];

        $good = [];
        $bad = [];

        foreach ($pics as $pic) {
            try {
                $dbReader = new Reader();
                $duplicate = $dbReader->getStepPictures($illnessId, $stepNum);
                if (in_array(PIC_DIRECTORY . $pic, $duplicate)) {
                    $bad['duplicate'][] = $pic;
                    continue;
                }

                $dbCreator = new Creator();
                $dbCreator->addPictureToStep(
                    $illnessId, $stepNum, PIC_DIRECTORY . $pic
                );

                $good[] = $pic;

            } catch (\Exception $e) {
                Logger::log('db', 'error', "Failed to add pic $pic to illness $illnessId, step $stepNum", $e);
                $bad['db'][] = $pic;
                continue;
            }
        }

        $this->prepareGoodBatchResults($good, $pics, ['name']);
        $this->prepareBadBatchResults($bad, $pics, ['name']);

        return Router::redirect('/admin/uploads');
    }

}
