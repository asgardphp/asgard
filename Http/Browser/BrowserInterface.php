<?php
namespace Asgard\Http\Browser;

/**
 * Browser.
 */
interface BrowserInterface {
	/**
	 * Return cookies.
	 * @return \Asgard\Common\BagInterface
	 */
	public function getCookies();

	/**
	 * Return session.
	 * @return \Asgard\Common\BagInterface
	 */
	public function getSession();

	/**
	 * Get last response.
	 * @return Response
	 */
	public function getLast();

	/**
	 * Execute a GET request.
	 * @param  string $url
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function get($url='', $body='', array $headers=[]);

	/**
	 * Execute a POST request.
	 * @param  string $url
	 * @param  array  $post
	 * @param  arra   $files
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function post($url='', array $post=[], array $files=[], $body='', array $headers=[]);

	/**
	 * Execute a PUT request.
	 * @param  string $url
	 * @param  array  $post
	 * @param  arra   $files
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function put($url='', array $post=[], array $files=[], $body='', array $headers=[]);

	/**
	 * Execute a DELETE request.
	 * @param  string $url
	 * @param  string $body
	 * @param  array  $headers
	 * @return Response
	 */
	public function delete($url='', $body='', array $headers=[]);

	/**
	 * Execute a request.
	 * @param  string $url
	 * @param  string $method
	 * @param  array  $post
	 * @param  arra   $file
	 * @param  string $body
	 * @param  array  $headers
	 * @param  array  $server
	 * @return Response
	 */
	public function req(
			$url='',
			$method='GET',
			array $post=[],
			array $file=[],
			$body='',
			array $headers=[],
			array $server=[]
		);

	/**
	 * Set the catchException parameter.
	 * @param  boolean $catchException
	 */
	public function catchException($catchException);

	/**
	 * Submit a form.
	 * @param  string $xpath   path to submit button.
	 * @param  string $to      destination url.
	 * @param  array $override override post attributes.
	 * @return Response
	 */
	public function submit($xpath='//form', $to=null, array $override=[]);
}