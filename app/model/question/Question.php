<?php
/**
 * Question Active Record
 * @author  André C. Scherrer
 */
class Question extends TRecord
{
    const TABLENAME = 'questions';
    const PRIMARYKEY= 'id';
    const IDPOLICY =  'max'; // {max, serial}
    
    private $system_user;
    private $subject;
        
    /**
     * Constructor method
     */
    public function __construct($id = NULL, $callObjectLoad = TRUE)
    {
        parent::__construct($id, $callObjectLoad);
        parent::addAttribute('name');
        parent::addAttribute('a');
        parent::addAttribute('b');
        parent::addAttribute('c');
        parent::addAttribute('d');
        parent::addAttribute('e');
        parent::addAttribute('correct');
        parent::addAttribute('level');
        parent::addAttribute('image');
        parent::addAttribute('user_id');
        parent::addAttribute('subject_id');
    }

    /**
     * Method set_system_user
     * Sample of usage: $questions->system_user = $object;
     * @param $object Instance of SystemUser
     */
    public function set_system_user(SystemUser $object)
    {
        $this->system_user = $object;
        $this->user_id = $object->id;
    }
    
    /**
     * Method get_system_user
     * Sample of usage: $questions->system_user->attribute;
     * @returns SystemUser instance
     */
    public function get_system_user()
    {
        // loads the associated object
        if (empty($this->system_user))
            $this->system_user = new SystemUser($this->user_id);
    
        // returns the associated object
        return $this->system_user;
    }
    
    /**
     * Method set_subject
     * Sample of usage: $questions->subject = $object;
     * @param $object Instance of Subject
     */
    public function set_subject(Subject $object)
    {
        $this->subject = $object;
        $this->subject_id = $object->id;
    }
    
    /**
     * Method get_subject
     * Sample of usage: $questions->subject->attribute;
     * @returns Subject instance
     */
    public function get_subject()
    {
        // loads the associated object
        if (empty($this->subject))
            $this->subject = new Subject($this->subject_id);
    
        // returns the associated object
        return $this->subject;
    }


}
