<?php
namespace VVC\Model\Database;

use VVC\Model\Data\Drug;
use VVC\Model\Data\IllnessCollection;
use VVC\Model\Data\IllnessRecord;
use VVC\Model\Data\Payment;
use VVC\Model\Data\Stay;
use VVC\Model\Data\Step;
use VVC\Model\Data\User;

/**
 * Processes SELECT queries
 */
class Reader extends Connection
{
    /**
     * Returns existing user data
     * @param  string $username
     * @return User OR false
     */
    public function findUserByUsername(string $username)
    {
        // test stub
        if (NO_DATABASE) {
            return $this->findUserByUsername_stub($username);
        }

        $sql = "SELECT * FROM users WHERE user_name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$username]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new User(
            $result['user_id'],
            $result['user_name'],
            $result['password'],
            $result['role_id'],
            $result['createdAt']
        );
    }

    /**
     * Returns existing user data
     * @param  int $userId
     * @return User OR false
     */
    public function findUserById(int $userId)
    {
        // test stub
        if (NO_DATABASE) {
            return false;
        }

        $sql = "SELECT * FROM users WHERE user_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        return new User(
            $result['user_id'],
            $result['user_name'],
            $result['password'],
            $result['role_id'],
            $result['createdAt']
        );
    }

    /**
     * Returns array with all users, full details
     * @return array of Users, empty or not
     */
    public function getAllUsers() : array
    {
        if (NO_DATABASE) {
            return [];
        }

        $sql = "SELECT * FROM users";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $users = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $users[] = new User(
                $row['user_id'],
                $row['user_name'],
                $row['password'],
                $row['role_id'],
                $row['createdAt']
            );
        }

        return $users;
    }

    /**
     * Returns collection of alll illnesses
     * Only BASIC details are required, no steps array
     * @return IllnessCollection, empty or not
     */
    public function getAllIllnesses() : IllnessCollection
    {
        if (NO_DATABASE) {
            $collection = new IllnessCollection();
            $collection->addRecord(new IllnessRecord(
                1,
                'Illness 1',
                'Class 1',
                'Illness 1 description.'
            ));
            $collection->addRecord(new IllnessRecord(
                2,
                'Illness 2',
                'Class 1',
                'Illness 2 description.'
            ));
            $collection->addRecord(new IllnessRecord(
                3,
                'Illness 3',
                'Class 2',
                'Illness 3 very long description that goes on and on and on and yet it goes on still on'
            ));

            return $collection;
        }

        $sql = "SELECT * FROM illness ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $collection = new IllnessCollection();

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $collection->addRecord(new IllnessRecord(
                 $row['ill_id'],
                 $row['ill_name'],
                 $row['class_name'],
                 $row['ill_describe']
            ));
        }

        return $collection;
    }

    public function getIllnessesByClass(
        string $class, $limit = null, $offset = null
    ) {
        $sql = "SELECT * FROM illness WHERE class_name = ?
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, $class);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $illnesses = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $illnesses[] = new IllnessRecord(
                 $row['ill_id'],
                 $row['ill_name'],
                 $row['class_name'],
                 $row['ill_describe']
            );
        }

        return $illnesses;
    }

    /**
     * Returns array with all drugs, full details
     * @return array of Drugs, empty or not
     */
    public function getAllDrugs() : array
    {
        if (NO_DATABASE) {
            return [
                new Drug(1, 'AB-01', 'Drug for...', '/uploads/img/drugs/drug.jpg', 30),
                new Drug(2, 'BC-02', 'Drug for...', '/uploads/img/drugs/jkl.jpg', 40),
                new Drug(3, 'CD-03', 'Drug for...', '/uploads/img/drugs/drug.jpg', 50)
            ];
        }

        $sql = "SELECT * FROM drug ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $drugs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $drugs[] = new Drug(
                 $row['drug_id'],
                 $row['drug_name'],
                 $row['drug_text'],
                 $row['drug_picture'],
                 $row['drug_cost']
            );
        }

        return $drugs;
    }

    /**
     * Returns array with all payments, full details
     * @return array of Payments, empty or not
     */
    public function getAllPayments() : array
    {
        if (NO_DATABASE) {
            return [];
        }

        $sql = "SELECT * FROM payments ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $payments = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $payments[] = new Payment(
                 $row['pay_id'],
            	 $row['ill_id'],
                 $row['pay_name'],
                 $row['pay_cost'],
            	 $row['number']
            );
        }

        return $payments;
    }

    /**
     * Returns FULL illness details,
     * including all steps and details of each step
     * @param  int  $id     - illness id
     * @return IllnessRecord OR false if not found
     */
    public function getFullIllnessById(int $illnessId)
    {
        if (NO_DATABASE) {
            if ($illnessId <= 3) {    // just for tests
                $illness = new IllnessRecord(
                    $illnessId,
                    "Illness $illnessId",
                    'Class ' . ($illnessId < 3) ? 1 : 2,
                    "Illness $illnessId description."
                );

                if ($illnessId == 3) {
                    $illness->setDescription(
                        'Illness 3 very long description that goes on and on and on and yet it goes on still on'
                    );
                }

                $steps = $this->getStepsByIllnessId($illnessId);
                $illness->addSteps($steps);

                $drugs = $this->getDrugsByIllnessId($illnessId);
                $illness->addDrugs($drugs);

                $payments = $this->getPaymentsByIllnessId($illnessId);
                $illness->addPayments($payments);

                $days = $this->getStayByIllnessId($illnessId);
                if ($days > 0) {
                    $illness->setStay(new Stay($days));
                }

                return $illness;
            } else {
                return false;
            }
        }

        $sql = "SELECT * FROM illness WHERE ill_id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            return false;
        }

        $illness = new IllnessRecord(
                $result['ill_id'],
                $result['ill_name'],
        	    $result['class_name'],
        		$result['ill_describe']
        );

        // Find and add all steps
        $steps = $this->getStepsByIllnessId($illnessId);
        $illness->addSteps($steps);

        // Add everything else
        $drugs = $this->getDrugsByIllnessId($illnessId);
        $illness->addDrugs($drugs);

        $payments = $this->getPaymentsByIllnessId($illnessId);
        $illness->addPayments($payments);

        $days = $this->getStayByIllnessId($illnessId);
        if ($days > 0) {
            $illness->setStay(new Stay($days));
        }

        return $illness;
    }

    /**
     * Returns all illness steps with full details
     * @param  int $illnessId
     * @return array of steps, empty or not
     */
    public function getStepsByIllnessId(int $illnessId) : array
    {
        if (NO_DATABASE) {
            $steps = [];
            $steps[] = new Step(1, '接诊');
            $steps[] = new Step(2, '检查');
            $steps[] = new Step(3, '诊断');
            $steps[] = new Step(4, '治疗方案');
        } else {
            $steps = $this->findIllnessSteps($illnessId);
        }

        foreach ($steps as $step) {
            $stepNum = $step->getNum();

            $text = $this->getStepText($illnessId, $stepNum);
            $step->setText($text);

            $pictures = $this->getStepPictures($illnessId, $stepNum);
            $step->addPictures($pictures);

            $videos = $this->getStepVideos($illnessId, $stepNum);
            $step->addVideos($videos);
        }

        return $steps;
    }

    /**
     * Returns illness steps BASIC details, no pictures, etc
     * @param  int    $illnessId
     * @return array of steps, empty or not
     */
    public function findIllnessSteps(int $illnessId) : array
    {
        $sql = "SELECT s.step_num,n.step_name,s.step_text
                FROM steps s INNER JOIN stepname n
                ON s.step_num=n.step_num
                WHERE s.ill_id=?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);

        $steps = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $steps[] = new Step(
                $row['step_num'],
            	$row['step_name']
            );
        }

        return $steps;
    }

    /**
     * Returns step text
     * @param  int $illnessId
     * @param  int $stepNum
     * @return string
     */
    public function getStepText(int $illnessId, int $stepNum) : string
    {
        if (NO_DATABASE) {
            return "Description of step $stepNum for illness $illnessId";
        }

        $sql = "SELECT step_text FROM steps
                WHERE step_num=? And ill_id=?  ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stepNum, $illnessId]);

        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        return $result['step_text'];
    }

    /**
     * Returns all step pictures, if any
     * @param  int  $illnessId
     * @param  int  $stepNum
     * @return array of pictures, empty or not
     */
    public function getStepPictures(int $illnessId, int $stepNum) : array
    {
        if (NO_DATABASE) {
            return ["/img/step$stepNum.png"];
        }

        $sql = "SELECT pic_path FROM illpic
                WHERE step_num=? And ill_id=? ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stepNum, $illnessId]);

        $pics = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $pics[] = $row['pic_path'];
        }

        return $pics;
    }

    /**
     * Returns all step videos, if any
     * @param  int  $illnessId
     * @param  int  $stepNum
     * @return array of videos, empty or not
     */
    public function getStepVideos(int $illnessId, int $stepNum) : array
    {
        if (NO_DATABASE) {
            return [];
        }

        $sql = "SELECT vid_path FROM illvid
                WHERE step_num=? And ill_id=? ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$stepNum, $illnessId]);

        $vids = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $vids[] = $row['vid_path'];
        }

        return $vids;
    }

    /**
     * Returs all drugs associated with illness, if any
     * @param  int  $illnessId
     * @return array of drugs, empty or not
     */
    public function getDrugsByIllnessId(int $illnessId) : array
    {
        if (NO_DATABASE) {
            return [new Drug(
                rand(1,3),
                'Drug',
                'Drug description',
                '/img/drug.jpg',
                rand(30, 100)
            )];
        }

        $sql = "SELECT
            drug.drug_id,
            drug.drug_name,
            drug.drug_text,
            drug.drug_picture,
            drug.drug_cost
		        FROM drug INNER JOIN illdrug
            ON illdrug.ill_id=? AND drug.drug_id=illdrug.drug_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);

        $drugs = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
        	$drugs[] = new Drug(
                    $row['drug_id'],
        			$row['drug_name'],
        			$row['drug_text'],
        			$row['drug_picture'],
        			$row['drug_cost']
        			);
        }

        return $drugs;
    }

    /**
     * Returs all payments associated with illness, if any
     * @param  int  $illnessId
     * @return array of payments, empty or not
     */
    public function getPaymentsByIllnessId(int $illnessId) : array
    {
        if (NO_DATABASE) {
            return [new Payment(
                1, $illnessId, 'Payment for drugs', rand(25, 50), rand(1,3)
            )];
        }

        $sql = "SELECT p.pay_id,p.pay_name,p.pay_cost,p.number
                FROM  payments p
                WHERE ill_id=? ";
        //$sql="SELECT SUM(pay_cost) FROM payments WHERE ill_id='$illnessId'";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);

        $payments = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $payments[] = new Payment(
                $row['pay_id'],
            	  $illnessId,
            	  $row['pay_name'],
            	  $row['pay_cost'],
            	  $row['number']
            );
        }

        return $payments;
    }

    /**
     * Returs number of days for staying at clinic for the illness
     * @param  int  $illnessId
     * @return int  days OR false
     */
    public function getStayByIllnessId(int $illnessId)
    {
        if (NO_DATABASE) {
            return rand(1, 5);
        }

        $sql = "SELECT number FROM payments
                WHERE ill_id=? AND pay_name='stay' ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);

        return $stmt->fetchColumn(0);   // returns false if no columns
    }

    /**
     * Returns all illnesses associated with the drug
     * @param  string $drugId
     * @return array of illnesses, empty or not
     */
    public function findIllnessesByDrugId(string $drugId) : array
    {
        if (NO_DATABASE) {
            return [
                new IllnessRecord(
                    1,
                    'Illness 1',
                    'Class 1',
                    'Illness 1 description.'
                ),
                new IllnessRecord(
                    2,
                    'Illness 2',
                    'Class 1',
                    'Illness 2 description.'
                ),
                new IllnessRecord(
                    3,
                    'Illness 3',
                    'Class 2',
                    'Illness 3 very long description that goes on and on and on and yet it goes on still on'
            )];
        }

        $sql = "SELECT id.ill_id,i.ill_name,i.class_name,i.ill_describe
        		    FROM illness i INNER JOIN illdrug id
                ON id.drug_id=? AND i.ill_id=id.ill_id
                ORDER BY i.ill_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$drugId]);

        $illnesses = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $illnesses[] = new IllnessRecord(
            	$row['ill_id'],
                $row['ill_name'],
            	$row['class_name'],
                $row['ill_describe']
            );
        }

        return $illnesses;
    }

    /**
     * Returns all illnesses associated with the payment
     * @param  string $paymentId
     * @return array of illnesses, empty or not
     */
    public function findIllnessesByPaymentId(string $paymentId) : array
    {
        if (NO_DATABASE) {
            return [];
        }

        $sql = "SELECT i.ill_id,i.ill_name,i.class_name,i.ill_describe
        		    FROM illness i INNER JOIN payments p
                 ON p.pay_id=? AND i.ill_id=p.ill_id
                 ORDER BY i.ill_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);

        $illnesses = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
        	$illnesses[] = new IllnessRecord(
                $row['ill_id'],
    			$row['ill_name'],
    			$row['class_name'],
                $row['ill_describe']
        	);
        }

        return $illnesses;
    }

    /**
     * Returns illness by its id
     * @param  int $illnessId
     * @return IllnessRecord OR false
     */
    public function findIllnessById(int $illnessId)
    {
        $sql = "SELECT * FROM illness WHERE ill_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new IllnessRecord(
            $result['ill_id'],
            $result['ill_name'],
            $result['class_name'],
            $result['ill_describe']
        );
    }

    /**
     * Returns illness by its name
     * @param  string $username
     * @return User OR false
     */
    public function findIllnessByName(string $name)
    {
        $sql = "SELECT * FROM illness WHERE ill_name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new IllnessRecord(
            $result['ill_id'],
            $result['ill_name'],
            $result['class_name'],
            $result['ill_describe']
        );
    }

    public function findDrugById(string $drugId)
    {
        $sql = "SELECT * FROM drug WHERE drug_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$drugId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new Drug(
            $result['drug_id'],
            $result['drug_name'],
            $result['drug_text'],
            $result['drug_picture'],
            $result['drug_cost']
        );
    }

    public function findDrugByName(string $name)
    {
        $sql = "SELECT * FROM drug WHERE drug_name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$name]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new Drug(
            $result['drug_id'],
            $result['drug_name'],
            $result['drug_text'],
            $result['drug_picture'],
            $result['drug_cost']
        );
    }

    public function findPaymentById(int $paymentId)
    {
        $sql = "SELECT * FROM payments WHERE pay_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$paymentId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new Payment(
             $result['pay_id'],
             $result['ill_id'],
             $result['pay_name'],
             $result['pay_cost'],
             $result['number']
        );
    }

    public function findIllnessNameById(string $illnessId)
    {
        $sql = "SELECT ill_name FROM illness WHERE ill_id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessId]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return $result['ill_name'];
    }

    public function findIllnessIdByName(string $illnessName)
    {
        $sql = "SELECT ill_id FROM illness WHERE ill_name = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$illnessName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return $result['ill_id'];
    }

    public function getAllSteps()
    {
        $sql = "SELECT * FROM stepname ORDER BY step_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        $steps = [];
        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $steps[] = new Step(
                $row['step_num'],
            	$row['step_name']
            );
        }

        return $steps;
    }

    public function findIllnessesByPicture(string $path)
    {
        $sql = "SELECT i.ill_id,i.ill_name,i.class_name,i.ill_describe
                FROM illness i INNER JOIN illpic p
                ON p.ill_id = i.ill_id AND p.pic_path = ?
                GROUP BY i.ill_id
                ORDER BY i.ill_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$path]);

        $illnesses = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
        	$illnesses[] = new IllnessRecord(
                $row['ill_id'],
    			$row['ill_name'],
    			$row['class_name'],
                $row['ill_describe']
        	);
        }

        return $illnesses;
    }

    public function searchIllnesses(
        string $search, $limit = null, $offset = null
    ) {
        $sql = "SELECT * FROM illness WHERE ill_name LIKE ?
                LIMIT ? OFFSET ?";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(1, '%' . $search . '%');
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->bindValue(3, $offset, \PDO::PARAM_INT);
        $stmt->execute();

        $illnesses = [];

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)){
            $illnesses[] = new IllnessRecord(
                 $row['ill_id'],
                 $row['ill_name'],
                 $row['class_name'],
                 $row['ill_describe']
            );
        }

        return $illnesses;
    }

    public function findDrugByPicture(string $path)
    {
        $sql = "SELECT * FROM drug WHERE drug_picture = ?
                ORDER BY drug_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$path]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        if (!$result) {
            return false;
        }

        return new Drug(
            $result['drug_id'],
            $result['drug_name'],
            $result['drug_text'],
            $result['drug_picture'],
            $result['drug_cost']
        );
    }

    public function findUserByUsername_stub($username)
    {
        if ($username == ADMIN_NAME){
            return new User(
                1,
                ADMIN_NAME,
                password_hash(ADMIN_PASSWORD, PASSWORD_DEFAULT),
                1,
                date("Y-m-d H:i:s")
            );
        } elseif ($username == USER_NAME) {
            return new User(
                2,
                USER_NAME,
                password_hash(USER_PASSWORD, PASSWORD_DEFAULT),
                2,
                date("Y-m-d H:i:s")
            );
        } else {
            return false;
        }
    }
}
