<?php
namespace AppVentus\AwesomeBundle\Persister;

use Doctrine\ORM\Persisters\BasicEntityPersister;
/**
 * Description of AwesomePersister
 *
 * @author lenybernard
 */
class AwesomePersister extends BasicEntityPersister{
    
    /**
     * Loads a list of entities by a list of field criteria.
     *
     * @param array $criteria
     * @param array $orderBy
     * @param int $limit
     * @param int $offset
     * @return array
     */
    public function loadAll(array $criteria = array(), array $orderBy = null, $limit = null, $offset = null, $queryMode = null)
    {
        if($queryMode){
            $entities = array();
            $sql = $this->_getSelectEntitiesSQL($criteria, null, 0, $limit, $offset, $orderBy);
            list($params, $types) = $this->expandParameters($criteria);
            return array($sql,$params, $types);
        }else{
            parent::loadAll($criteria, $orderBy, $limit, $offset);
        }
    }
    
        /**
     * Expand the parameters from the given criteria and use the correct binding types if found.
     *
     * @param  array $criteria
     * @return array
     */
    private function expandParameters($criteria)
    {
        $params = $types = array();

        foreach ($criteria AS $field => $value) {
            if ($value === null) {
                continue; // skip null values.
            }

            $types[]  = $this->getType($field, $value);
            $params[] = $this->getValue($value);
        }

        return array($params, $types);
    }
}

?>
