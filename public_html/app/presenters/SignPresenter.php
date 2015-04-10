<?php

namespace App\Presenters;

use Nette,
    App\Model;


/**
 * Sign in/out presenters.
 */
class SignPresenter extends BasePresenter
{
	/** @var QA\Model\UserManager */
	protected $usermanager;
	
	/**
	 * 
	 * @param Model\UserManager $userManager
	 */
	public function inject(Model\UserManager $userManager){
		$this->usermanager = $userManager;
	}
	
	/**
	 * Form na prihlasenie
	 * @return Nette\Application\UI\Form
	 */
	protected function createComponentSignInForm()
	{
		$form = new Nette\Application\UI\Form;
		$form->addText('username', 'Prihlasovacie meno:')
			->setRequired('Prosim zadajte meno.')
			->setAttribute("placeholder","Prihlasovacie meno");

		$form->addPassword('password', 'Heslo:')
			->setRequired('Prosim zadajte heslo.')
			->setAttribute("placeholder","Heslo");
		
		$form->addSubmit('send', 'Prihlásiť');

		$form->onSuccess[] = $this->signInFormSucceeded;
		return $form;
	}


	public function signInFormSucceeded($form, $values)
	{
		$this->getUser()->setExpiration('40 minutes', TRUE, TRUE);

		try {
			$this->getUser()->login($values->username, $values->password);
			$this->redirect('Homepage:');

		} catch (Nette\Security\AuthenticationException $e) {
			$form->addError("Nesprávne meno alebo heslo");
		}
	}


	public function actionOut()
	{
		$this->getUser()->logout();
		$this->flashMessage('Boli ste odhlasený.',"success");
		$this->redirect('Sign:in');
	}

}
