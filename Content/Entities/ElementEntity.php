<?php

/**
 * This file is part of the Venne:CMS (https://github.com/Venne)
 *
 * Copyright (c) 2011, 2012 Josef Kříž (http://www.josef-kriz.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace CmsModule\Content\Entities;

use Venne;
use DoctrineModule\Entities\IdentifiedEntity;

/**
 * @author Josef Kříž <pepakriz@gmail.com>
 * @Entity(repositoryClass="\DoctrineModule\Repositories\BaseRepository")
 * @Table(name="element", indexes={@index(name="name_idx", columns={"name"})})
 * @InheritanceType("JOINED")
 * @DiscriminatorColumn(name="type", type="string")
 * @DiscriminatorMap({"base" = "ElementEntity"})
 */
abstract class ElementEntity extends IdentifiedEntity
{

	const MODE_LAYOUT = 0;

	const MODE_PAGE = 1;

	const MODE_ROUTE = 2;

	/** @var array */
	protected static $modes = array(self::MODE_LAYOUT => 'Layout', self::MODE_PAGE => 'page', self::MODE_ROUTE => 'route');

	/**
	 * @var \CmsModule\Content\Entities\LayoutconfigEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\LayoutconfigEntity")
	 * @JoinColumn(onDelete="CASCADE")
	 */
	protected $layoutconfig;

	/**
	 * @var \CmsModule\Content\Entities\PageEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\PageEntity")
	 * @JoinColumn(onDelete="CASCADE")
	 */
	protected $page;

	/**
	 * @var \CmsModule\Content\Entities\RouteEntity
	 * @ManyToOne(targetEntity="\CmsModule\Content\Entities\RouteEntity")
	 * @JoinColumn(onDelete="CASCADE")
	 */
	protected $route;

	/**
	 * @var int
	 * @Column(type="string")
	 */
	protected $name;

	/**
	 * @var int
	 * @Column(type="integer")
	 */
	protected $mode;


	/**
	 * @param LayoutconfigEntity $layoutconfigEntity
	 * @param RouteEntity $route
	 */
	final public function setDefaults($name, RouteEntity $route)
	{
		$this->name = $name;
		$this->route = $route;
		$this->page = $route->getPage();
		$this->layoutconfig = $route->getLayoutconfig();

		$this->mode = self::MODE_LAYOUT;
	}


	/**
	 * @param int $mode
	 */
	public function setMode($mode)
	{
		if (!isset(self::$modes[$mode])) {
			throw new \Nette\InvalidArgumentException;
		}

		$this->mode = $mode;
	}


	/**
	 * @return int
	 */
	public function getMode()
	{
		return $this->mode;
	}


	/**
	 * @return array
	 */
	public static function getModes()
	{
		return self::$modes;
	}


	/**
	 * @return int
	 */
	public function getName()
	{
		return $this->name;
	}


	/**
	 * @return \CmsModule\Content\Entities\PageEntity
	 */
	public function getPage()
	{
		return $this->page;
	}


	/**
	 * @return \CmsModule\Content\Entities\RouteEntity
	 */
	public function getRoute()
	{
		return $this->route;
	}


	/**
	 * @return \CmsModule\Content\Entities\LayoutconfigEntity
	 */
	public function getLayoutconfig()
	{
		return $this->layoutconfig;
	}
}