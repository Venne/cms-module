<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Administration\Presenters;

use CmsModule\Administration\Components\AdminGrid\AdminGrid;
use CmsModule\Components\Table\Form;
use CmsModule\Content\Entities\UserPageEntity;
use CmsModule\Content\Repositories\PageRepository;
use CmsModule\Forms\UserFormFactory;
use CmsModule\Forms\UserSocialFormFactory;
use CmsModule\Pages\Users\UserManager;
use CmsModule\Security\Repositories\UserRepository;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 *
 * @secured
 */
class UsersPresenter extends BasePresenter
{

	/** @persistent */
	public $page;

	/** @persistent */
	public $type;

	/** @var UserRepository */
	protected $userRepository;

	/** @var PageRepository */
	protected $pageRepository;

	/** @var UserFormFactory */
	protected $form;

	/** @var UserSocialFormFactory */
	protected $socialForm;

	/** @var UserPageEntity */
	protected $extendedPage;

	/** @var UserManager */
	protected $userManager;


	/**
	 * @param UserRepository $userRepository
	 */
	public function injectUserRepository(UserRepository $userRepository)
	{
		$this->userRepository = $userRepository;
	}


	/**
	 * @param PageRepository $pageRepository
	 */
	public function injectPageRepository(PageRepository $pageRepository)
	{
		$this->pageRepository = $pageRepository;
	}


	/**
	 * @param UserFormFactory $form
	 */
	public function injectForm(UserFormFactory $form)
	{
		$this->form = $form;
	}


	/**
	 * @param UserSocialFormFactory $socialForm
	 */
	public function injectSocialForm(UserSocialFormFactory $socialForm)
	{
		$this->socialForm = $socialForm;
	}


	/**
	 * @param UserManager $userManager
	 */
	public function injectUserManager(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}


	/**
	 * @return UserManager
	 */
	public function getUserManager()
	{
		return $this->userManager;
	}


	protected function startup()
	{
		parent::startup();

		if (($page = $this->pageRepository->findOneBy(array('special' => 'users'))) === NULL) {
			$this->flashMessage('User page does not exist.', 'warning');
		} else {
			$this->extendedPage = $this->getEntityManager()->getRepository($page->class)->findOneBy(array('page' => $page));
		}

		if (!$this->type) {
			$this->type = key($this->userManager->getUserTypes());
		}
	}


	/**
	 * @secured(privilege="show")
	 */
	public function actionDefault()
	{
		$this->template->extendedPage = $this->extendedPage;
	}


	/**
	 * @secured
	 */
	public function actionCreate()
	{
	}


	/**
	 * @secured
	 */
	public function actionEdit()
	{
	}


	/**
	 * @secured
	 */
	public function actionRemove()
	{
	}


	protected function createComponentTable()
	{
		$_this = $this;
		$repository = $this->entityManager->getRepository($this->type);
		$admin = new AdminGrid($repository);

		// columns
		$table = $admin->getTable();
		$table->setTranslator($this->context->translator->translator);
		$table->addColumn('email', 'E-mail')
			->setCustomRender(function ($entity) {
				return $entity->user->email;
			})
			->setSortable()
			->getCellPrototype()->width = '60%';
		$table->getColumn('email')
			->setFilter()->setSuggestion();

		$table->addColumn('roles', 'Roles')
			->setSortable()
			->getCellPrototype()->width = '40%';
		$table->getColumn('roles')
			->setCustomRender(function ($entity) {
				return implode(", ", $entity->user->roles);
			});

		// actions
		if ($this->isAuthorized('edit')) {
			$table->addAction('edit', 'Edit')
				->getElementPrototype()->class[] = 'ajax';

			$table->addAction('socialLogins', 'Social Logins')
				->getElementPrototype()->class[] = 'ajax';

			$extendedPage = $this->extendedPage;
			$type = $this->type;
			$form = $admin->createForm($this->getUserType()->getFormFactory(), 'User', function () use ($extendedPage, $type) {
				return new $type($extendedPage);
			}, Form::TYPE_LARGE);
			$socialForm = $admin->createForm($this->socialForm, 'Social Logins', NULL, Form::TYPE_LARGE);

			$admin->connectFormWithAction($form, $table->getAction('edit'), $admin::MODE_PLACE);
			$admin->connectFormWithAction($socialForm, $table->getAction('socialLogins'));

			// Toolbar
			$toolbar = $admin->getNavbar();
			$toolbar->addSection('new', 'Create', 'file');
			$admin->connectFormWithNavbar($form, $toolbar->getSection('new'), $admin::MODE_PLACE);
		}

		if ($this->isAuthorized('remove')) {
			$table->addAction('delete', 'Delete')
				->getElementPrototype()->class[] = 'ajax';
			$admin->connectActionAsDelete($table->getAction('delete'));
		}

		return $admin;
	}


	/**
	 * @return \CmsModule\Pages\Users\UserType
	 */
	private function getUserType()
	{
		return $this->userManager->getUserTypeByClass($this->type);
	}
}
