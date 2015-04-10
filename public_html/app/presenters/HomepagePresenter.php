<?php

namespace App\Presenters;

use Nette,
    App\Model,
    Nette\Application\UI\Form;

/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter {

	/**
	 *
	 * @var Model\QuestionRepository
	 */
	protected $questionRepository;

	/**
	 *
	 * @var Model\AnswerRepository
	 */
	protected $answerRepository;

	/**
	 *
	 * @var Model\UserManager
	 */
	protected $userManager;

	public function inject(Model\QuestionRepository $questionRepository, Model\AnswerRepository $answerRepository, Model\UserManager $userManager) {
		$this->questionRepository = $questionRepository;
		$this->answerRepository = $answerRepository;
		$this->userManager = $userManager;
	}

	public function renderDefault($id = 0) {
		$this->template->questions = $this->questionRepository->getQuestions();
		$this->template->question = $this->questionRepository->getQuestion($id);
		$this->template->answers = false;
		if ($id) {
			try {
				$this->questionRepository->canView($id, $this->getUser());
				if (!$this->getUser()->isLoggedIn()) {
					throw new \App\Model\ViewException("Neopravnene");
				}

				$this->template->answers = $this->answerRepository->getAnswers($id);
			} catch (\App\Model\ViewException $ex) {
				$this->flashMessage("Na prezeranie odpovedí nemáte oprávnenie.", "warning");
			}
		}
	}

	protected function createComponentAddQuestionForm() {
		$form = new Form;
		$form->addText("title", "Otázka")
			->setRequired("Prosím vyplňte otázku.")
			->setAttribute("placeholder", "Otázka");
		$form->addSelect("mentor_id", "Radca", $this->userManager->getUsers())
			->setPrompt("Radca");
		$form->addSubmit("send", "Pridať");

		$form->onSuccess[] = $this->addQuestionFormSubmit;
		return $form;
	}

	public function addQuestionFormSubmit($form) {
		$values = $form->values;
		$values->user_id = $this->getUser()->getId();

		try {
			if ($this->user->isLoggedIn()) {
				$id = $this->questionRepository->addQuestion($values);
				$this->redirect("Homepage:", $id);
			} else {
				$this->flashMessage("Na pridávanie otázok musíte byť prihlásneý.");
				$this->redirect("Homepage:");
			}
		} catch (\PDOException $ex) {
			$this->flashMessage("Chyba pridania otázky. Skúste znova.");
		}
	}

	protected function createComponentAddAnswerForm() {
		$form = new Form;
		$form->addTextArea("text", "Odpoveď")
			->setRequired("Prosím vyplňte odpoveď.")
			->setAttribute("placeholder", "Odpoveď");
		$form->addHidden("question_id");
		$form->addHidden("last_id")
			->setValue($this->answerRepository->getLastId($this->presenter->getRequest()->getParameters()['id']));
		$form->addSubmit("send","Pridať");

		$form->onSuccess[] = $this->addAnswerFormSubmit;
		return $form;
	}

	public function addAnswerFormSubmit($form) {
		$values = $form->values;
		$values->user_id = $this->getUser()->getId();

		try {
			$this->questionRepository->canView($values->question_id, $this->getUser());

			if ($this->isAjax() and $values->last_id != $this->answerRepository->getLastId($values->question_id)) {
				$form->addError("Od Vašej poslednej aktualizácie pribudli nové odpovede. Možno si ich chcete prečítať pred odoslaním Vašej správy.");
				$this->redrawControl("answers");
				$form->setValues(array("last_id"=>$this->answerRepository->getLastId($values->question_id)));
				$this->redrawControl("addanswerform");
			} else {
				$this->answerRepository->addAnswer($values);

				if ($this->isAjax()) {
					$this->redrawControl("answers");
					$form->setValues(array("last_id"=>$this->answerRepository->getLastId($values->question_id)), TRUE);
					$this->redrawControl("addanswerform");
				} else {
					$id = $values->question_id;
					$this->redirect("Homepage:", $id);
				}
			}
		} catch (\App\Model\ViewException $ex) {
			$this->flashMessage("Odpoveď nebolo možné pridať.");
		}
	}

}
