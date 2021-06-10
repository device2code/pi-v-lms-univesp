<?php



use Adianti\Wrapper\BootstrapFormBuilder;
use Adianti\Widget\Form\TLabel;
use Adianti\Widget\Form\TRadioGroup;
use Adianti\Control\TPage;
use Adianti\Widget\Container\TVBox;
use Adianti\Control\TAction;
use Adianti\Widget\Form\THidden;
use Adianti\Widget\Util\TImage;


class Quiz extends TPage
{
    private $form;
    protected $value;

    public function __construct()
    {
        parent::__construct();

        // creates the form
        $this->form = new BootstrapFormBuilder('form_Quiz');
        $this->form->setFormTitle('Quiz | Responda às Questões abaixo e clique no botão ENVIAR');
//        $this->form->setFieldSizes('100%');

        $this->getListQuestions();

        $vbox = new TVBox();
        $vbox->style = 'width: 100%';            
        $vbox->add( $this->form );
        parent::add($vbox);        
    }

    private function getListQuestions()
    {
        try
        {
            TTransaction::open('communication');

            $question_type = [
                1 => Question::where('subject_id', '=', '1')->where('image', 'IS',NULL)->getIndexedArray('id'),
                2 => Question::where('subject_id', '=', '2')->where('image', 'IS',NULL)->getIndexedArray('id'),
                3 => Question::where('subject_id', '=', '3')->where('image', 'IS',NULL)->getIndexedArray('id'),
                4 => Question::where('subject_id', '=', '4')->where('image', 'IS',NULL)->getIndexedArray('id'),
                5 => Question::where('subject_id', '=', '5')->where('image', 'IS',NULL)->getIndexedArray('id'),
                6 => Question::where('subject_id', '=', '6')->where('image', 'IS',NULL)->getIndexedArray('id'),
                7 => Question::where('subject_id', '=', '7')->where('image', 'IS',NULL)->getIndexedArray('id'),
            ];

            $questoes_id = $this->getQuestionsFromArray($question_type);

            $questions_quiz = $this->getQuestionsToQuiz($questoes_id);

            $this->getHowMuchQuestionsToAnswer($questions_quiz);

            TTransaction::close();
        }
        catch (Exception $e)
        {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }
    }

    private function getQuestionsIDs($questions)
    {
        $q = [];
        foreach ($questions as $k => $v) { $q[] = $k; }

        return $q;
    }

    private function getRandomArrayPositions($questions_ids)
    {
        $x1 = 0;
        $x2 = 0;
        $questions = [];

        while($x1 === $x2) {
            $x1 = rand(1, count($questions_ids)) - 1;
            $x2 = rand(1, count($questions_ids)) - 1;
        }

        $questions[0] = $x1;
        $questions[1] = $x2;

        return $questions;
    }

    private function getQuestionsFaffle($index, $questions_array)
    {
        $questions = [];
        foreach ($index as $k => $v) {
            $questions[] = $questions_array[$v];
        }

        return $questions;
    }

    private function getQuestionsFromArray($questions_array) {
        $questoes_quiz = [];

        foreach ($questions_array as $k => $v) {
            $array = "a_{$k}";
            $position = "pt_{$k}";
            $questions = "qt_{$k}";

            $$array = $this->getQuestionsIDs($v);
            $$position = $this->getRandomArrayPositions($$array);
            $$questions = $this->getQuestionsFaffle($$position, $$array);

            $questoes_quiz[] = $$questions;
        }

        $q = [];
        foreach ($questoes_quiz as $k => $v) {
            $q[] = $v[0];
            $q[] = $v[1];
        }

        return $q;
    }

    private function getQuestionsToQuiz($questions) {
        $questions_quiz = [];

        foreach ($questions as $k => $v) {
            $questions_quiz[] = new Question($v);
        }

        return $questions_quiz;
    }

    private function getHowMuchQuestionsToAnswer($questions)
    {
        $questions_to_answer = [];
        foreach ($questions as $k => $v) {
            $questions_to_answer[$k + 1] =
                [
                    'description' => $v->name,
                    'A' => $v->a,
                    'B' => $v->b,
                    'C' => $v->c,
                    'D' => $v->d,
                    'E' => $v->e,
                    'correct' => $v->correct,
                    'image' => $v->image,
                    'level' => $v->level,
                ];
        }

        foreach ($questions_to_answer as $key => $value) {
            $question = "q_{$key}";
            $image = "i_{$key}";
            $alternative = "r_{$key}";
            $correct = "c_{$key}";
            $level = "l_{$key}";

            $this->form->appendPage("Questão {$key} ");

            $$question = new TLabel($value["description"]);
            if (!empty($value['image'])) {
                $$image = new TImageCropper($image);
                $$image->setSize('100%','150');
                $$image->src = $value['image'];
            }
            $$alternative = new TRadioGroup($alternative);
            $$alternative->addItems(['A' => $value['A'], 'B' => $value['B'], 'C' => $value['C'], 'D' => $value['D'], 'E' => $value['E']]);
            $$correct = new THidden($correct);
            $$correct->setValue($value['correct']);
            $$level = new THidden($level);
            $$level->setValue($value['level']);

            $this->form->addFields([$$question]);
            $this->form->addFields([$$image]);
            $this->form->addFields([$$alternative]);
            $this->form->addFields([$$correct]);
            $this->form->addFields([$$level]);
        }

        $this->form->addAction('Enviar', new TAction(array($this, 'onSend')), 'far:check-circle green');
    }

    public function onSend($param)
    {
        $data = $this->form->getData();
//        $this->form->setData($data);

        $resultado = 0;
        $max = 0;

        for($i = 1; $i <= 14; $i++)
        {
            $r = "r_{$i}";
            $c = "c_{$i}";
            $l = "l_{$i}";

            $max += (int) $data->$l;

            if ($data->$r == $data->$c)
                $resultado += (int) $data->$l;
        }

        new \Adianti\Widget\Dialog\TMessage('info', "Você atingiu a pontuação de <b>" . $resultado . "</b> sendo <b>" . $max . "</b> a pontuação máxima");
    }
}