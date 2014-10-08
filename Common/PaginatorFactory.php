<?php
namespace Asgard\Common;

class PaginatorFactory implements PaginatorFactoryInterface {
	/**
	 * HTTP request.
	 * @var \Asgard\Http\Request
	 */
	protected $request;

	/**
	 * Constructor.
	 * @param \Asgard\Http\Request $request
	 */
	public function __construct(\Asgard\Http\Request $request=null) {
		$this->request = $request;
	}

	/**
	 * {@inheritDoc}
	 * @param \Asgard\Http\Request       $request
	 * @return Paginator
	 */
	public function create($total, $page=1, $per_page=10, $request=null) {
		return new Paginator($total, $page, $per_page, $request!==null ? $request:$this->request);
	}
}