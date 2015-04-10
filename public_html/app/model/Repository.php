<?php

namespace App\Model;
use Nette;

abstract class Repository extends Nette\Object
{
    /** @var Nette\Database\Connection */
    protected $connection;

    public function __construct(Nette\Database\Context $db)
    {
        $this->connection = $db;
    }

    /**
     * Vracia tabulku
     * @return Nette\Database\Table\Selection
     */
    protected function getTable()
    {
        // n�zev tabulky odvod�me z n�zvu t��dy
        preg_match('#(\w+)Repository$#', get_class($this), $m);
        return $this->connection->table(lcfirst($m[1]));
    }

    /**
     * Vracia vsetky riadky z tabulky
     * @return Nette\Database\Table\Selection
     */
    public function findAll()
    {
        return $this->getTable();
    }

    /**
     * Vracia riadky - s podmienkou
     * @return Nette\Database\Table\Selection
     */
    public function findBy(array $by, $order = "id DESC")
    {
        return $this->getTable()->where($by)->order($order);
    }
    
    public function getConnection(){
	    return $this->connection;
    }
	
}