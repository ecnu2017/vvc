<?php
namespace VVC\Model\Data;

/**
 * [               IllnessCollection                ]
 * [class => [           IllnessRecord             ]
 *           [id => [id, name, class, description]]]
 *
 *
 * Example :    Class 1 -- id 1 -- id 1
 *                       |       - name 1
 *                       |       - class 1
 *                       |       - description 1
 *                       - id 2 -- id 2
 *                               - name 2
 *                               - class 1
 *                               - description 2
 *              Class 2 -- id 3 -- id 3
 *                               - name 3
 *                               - class 2
 *                               - description 3
 */
class IllnessCollection
{
    private $records = [];          // organized as above
    private $justIllnesses = [];    // not organized

    public function __construct($records = null)
    {
        if ($records) {
            $this->addRecords($records);
        }
    }

    public function getRecords() : array
    {
        return $this->records;
    }

    public function setRecords(array $records)
    {
        $this->records = $records;
    }

    public function addRecords(array $records)
    {
        foreach ($records as $record) {
            $this->addRecord($record);
        }
    }

    public function addRecord(IllnessRecord $record)
    {
        $this->justIllnesses[] = $record;

        $class = $record->getClass();
        $id = $record->getId();

        $this->records[$class][$id] = $record;
        // TODO sorting.
        //ksort($this->records[$class]);
    }

    public function getClasses() : array
    {
        return array_keys($this->records);
    }

    public function getJustIllnesses() : array
    {
        // TODO sorting.
        return $this->justIllnesses;
    }

}
