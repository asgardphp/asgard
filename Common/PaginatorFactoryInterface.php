<?php
namespace Asgard\Common;

/**
 * Paginator factory interface.
 * @author Michel Hognerud <michel@hognerud.com>
 */
interface PaginatorFactoryInterface {
	/**
	 * Create a new instance.
	 * @param  integer            $total
	 * @param  integer            $page
	 * @param  integer            $per_page
	 * @return PaginatorInterface
	 */
	public function create($total, $page=1, $per_page=10, $request=null);
}