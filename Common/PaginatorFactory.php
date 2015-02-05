<?php
namespace Asgard\Common;

/**
 * Paginator factory.
 * @author Michel Hognerud <michel@hognerud.com>
 */
class PaginatorFactory implements PaginatorFactoryInterface {
	/**
	 * HTTP request.
	 * @var \Asgard\Http\HttpKernel
	 */
	protected $httpKernel;

	/**
	 * Constructor.
	 * @param \Asgard\Http\HttpKernel $httpKernel
	 */
	public function __construct(\Asgard\Http\HttpKernel $httpKernel=null) {
		$this->httpKernel = $httpKernel;
	}

	/**
	 * {@inheritDoc}
	 */
	public function create($total, $page=1, $per_page=10, \Asgard\Http\Request $request=null) {
		return new Paginator($total, $page, $per_page, $request!==null ? $request:$this->httpKernel ? $this->httpKernel->getRequest():null);
	}
}