<?php
/**
 * Subjects Active Record
 * @author  André C. Scherrer
 */
class Subject extends TRecord
{
    const TABLENAME = 'subjects';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
    }


}
