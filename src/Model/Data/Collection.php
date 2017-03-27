<?php
namespace VVC\Model\Data;

class Collection
{
    protected function sortByName($first, $second)
    {
        return strnatcmp($first->getName(), $second->getName());
    }
}
