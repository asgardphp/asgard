<?php
namespace Asgard\Common;

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