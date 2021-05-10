<?php
/**
 * QuestionForm Form
 * @author  André C. Scherrer
 */
class QuestionForm extends TPage
{
    protected $form; // form
    
    /**
     * Form constructor
     * @param $param Request
     */
    public function __construct( $param )
    {
        parent::__construct();
       
        // creates the form
        $this->form = new BootstrapFormBuilder('form_Question');
        $this->form->setFormTitle('Cadastro de Perguntas');
        // $this->form->setFieldSizes('100%');

        // create the form fields
        $id = new THidden('id');
        $subject_id = new TDBCombo('subject_id', 'communication', 'Subject', 'id', 'name', 'name');

        $image = new TImageCropper('image');
        $image->setSize('100%','150');        
        $image->setCropSize(300, 150);
        $image->setAllowedExtensions( ['png', 'jpg', 'jpeg'] );

        $name = new TText('name');
        $a = new TText('a');
        $b = new TText('b');
        $c = new TText('c');
        $d = new TText('d');
        $e = new TText('e');
        
        $user_id = new THidden('user_id');
        $user_logged = new THidden('user_logged');
        $user_logged->setValue(TSession::getValue('userid'));        
        
        $correct = new TRadioGroup('correct');
        $correct->addItems(['A' => 'A', 'B' => 'B', 'C' => 'C', 'D' => 'D', 'E' => 'E']);
        $correct->setLayout('horizontal');
        $correct->setUseButton(true);
        
        $level = new TRadioGroup('level'); 
        $level->addItems([1 => 'Muito Fácil', 2 => 'Fácil', 5 => 'Média', 8 => 'Difícil', 10 => 'Muito Difícil']);
        $level->setLayout('horizontal');
        $level->setUseButton(true);

        // add the fields
        $this->form->addFields( [ new TLabel('Pergunta'), $name ] );
        $this->form->addFields( [ new TLabel('Assunto')] );
        $this->form->addFields( [ $subject_id ] );
        $this->form->addFields( [ new TLabel('Image (Opcional)')] );
        $this->form->addFields( [ $image] );
        $this->form->addFields( [ new TLabel('A'), $a ] );
        $this->form->addFields( [ new TLabel('B'), $b ] );
        $this->form->addFields( [ new TLabel('C'), $c ] );
        $this->form->addFields( [ new TLabel('D'), $d ] );
        $this->form->addFields( [ new TLabel('E'), $e ] );
        $this->form->addFields( [ new TLabel('Nível')]);
        $this->form->addFields( [ $level ]);
        $this->form->addFields( [ new TLabel('Correta?')] );
        $this->form->addFields( [ $correct ], [$user_id, $user_logged, $id] );

        $name->addValidation('Pergunta', new TRequiredValidator);
        $a->addValidation('A', new TRequiredValidator);
        $b->addValidation('B', new TRequiredValidator);
        $c->addValidation('C', new TRequiredValidator);
        $d->addValidation('D', new TRequiredValidator);
        $e->addValidation('E', new TRequiredValidator);
        $correct->addValidation('Correta?', new TRequiredValidator);
        $level->addValidation('Nível', new TRequiredValidator);

        // set sizes
        $name->setSize('100%', '80');
        $a->setSize('100%', '50');
        $b->setSize('100%', '50');
        $c->setSize('100%', '50');
        $d->setSize('100%', '50');
        $e->setSize('100%', '50');
        $correct->setSize('100%');



        if (!empty($id))
        {
            $id->setEditable(FALSE);
        }
        
        /** samples
         $fieldX->addValidation( 'Field X', new TRequiredValidator ); // add validation
         $fieldX->setSize( '100%' ); // set size
         **/
         
        // create the form actions
        $btn = $this->form->addAction(_t('Save'), new TAction([$this, 'onSave']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary';
        $this->form->addActionLink(_t('New'),  new TAction([$this, 'onEdit']), 'fa:eraser red');
        
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        // $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }

    /**
     * Save form data
     * @param $param Request
     */
    public function onSave( $param )
    {
        try
        {
            TTransaction::open('communication'); // open a transaction
            
            /**
            // Enable Debug logger for SQL operations inside the transaction
            TTransaction::setLogger(new TLoggerSTD); // standard output
            TTransaction::setLogger(new TLoggerTXT('log.txt')); // file
            **/
            
            $this->form->validate(); // validate form data
            $data = $this->form->getData(); // get form data as array
            
            if (!isset($data->user_id) || $data->user_id == 0)
            {
                $data->user_id = $data->user_logged;
            }   
                            
            $object = new Question;  // create an empty object            
            $object->fromArray( (array) $data); // load the object with data            
            
            if ($object->id) {
                $obj_db = new Question($object->id);
                unlink($obj_db->image);
            }

            $object->store(); // save the object

            if ($object->image)
            {
                $old_name = 'tmp/'.$object->image;
                $ext = explode('.',$old_name);
                $i = count($ext);
                $extensao = $ext[--$i];
                $new_name = 'app/images/questions/'.time().'-'.$object->id.'.'.$extensao;
                $new = copy($old_name, $new_name);
                unlink($old_name);
                $object->image = $new_name;
                $object->store();
            }
                        
            // get the generated id
            $data->id = $object->id;
            
            $this->form->setData($object); // fill form data
            TTransaction::close(); // close the transaction
            
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            $this->form->setData( $this->form->getData() ); // keep form data
            TTransaction::rollback(); // undo all pending operations
        }
    }
    
    /**
     * Clear form data
     * @param $param Request
     */
    public function onClear( $param )
    {
        $this->form->clear(TRUE);
    }
    
    /**
     * Load object to form data
     * @param $param Request
     */
    public function onEdit( $param )
    {
        try
        {
            if (isset($param['key']))
            {
                $key = $param['key'];  // get the parameter $key
                TTransaction::open('communication'); // open a transaction
                $object = new Question($key); // instantiates the Active Record
                $this->form->setData($object); // fill the form
                TTransaction::close(); // close the transaction
            }
            else
            {
                $this->form->clear(TRUE);
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
            TTransaction::rollback(); // undo all pending operations
        }
    }
}
