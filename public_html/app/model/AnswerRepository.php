<?php

namespace App\Model;
/**
 * Tabulka question
 */
class AnswerRepository extends Repository
{	
	public function addAnswer($data)
	{
		return $this->getTable()->insert(array('time'=>new \Nette\Database\SqlLiteral('NOW()'), 'text'=>$data['text'], 'user_id'=>$data['user_id'], 'question_id'=>$data['question_id']));
	}
	
	public function getAnswer($id)
	{
		return $this->getTable()->where(array("id"=>$id))->fetch();
	}
	
	public function getAnswers($id)
	{
		return $this->getTable()->where(array("question_id"=>$id))->order("time ASC");
	}
	
	public function getLastId($question_id = 0)
	{
		$row = $this->getTable()->where(array("question_id"=>$question_id))->order("id DESC")->limit(1)->fetch();
		if($row){
			return $row->id;
		}
		else {
			return 0;
		}
	}
}