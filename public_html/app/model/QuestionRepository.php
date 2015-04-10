<?php

namespace App\Model;
/**
 * Tabulka question
 */
class QuestionRepository extends Repository
{	
	public function addQuestion ($data)
	{
		return $this->getTable()->insert(array('time'=>new \Nette\Database\SqlLiteral('NOW()'), 'title'=>$data['title'], 'user_id'=>$data['user_id'], 'mentor_id'=>$data['mentor_id']));
	}
	
	public function getQuestion($id)
	{
		return $this->getTable()->where(array("id"=>$id))->fetch();
	}
	
	public function getQuestions()
	{
		return $this->getTable();
	}
	
	public function canView($id, \Nette\Security\User $user)
	{
		$question = $this->getQuestion($id);
		if($user->isLoggedIn() and ($question->user_id == $user->getId() or $question->mentor_id == $user->getId())){
			return true;
		}
		else {
			throw new ViewException("Neopravnene");
		}
	}
}

/**
 * Exception pre chybu v suvislosti s nedostatkom pravomoci na odpovede.
 */
class ViewException extends \Exception
{
	public function __construct($message="Neopravnene", $code=null, $previous=null) {
		parent::__construct($message, $code, $previous);
	}
}