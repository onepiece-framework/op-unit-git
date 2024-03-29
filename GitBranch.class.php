<?php
/** op-unit-git:/GitBranch.class.php
 *
 * @created    2023-02-17
 * @version    1.0
 * @package    op-unit-git
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */

 /** Declare strict
 *
 */
declare(strict_types=1);

/** namespace
 *
 */
namespace OP\UNIT\GIT;

/** use
 *
 */
use OP\OP_CI;
use OP\OP_CORE;
use OP\IF_UNIT;

/** GitBranch
 *
 * @created    2023-02-17
 * @version    1.0
 * @package    op-unit-git
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class GitBranch implements IF_UNIT
{
	/** use
	 *
	 */
	use OP_CORE, OP_CI;

	/** Return main branch name.
	 *
	 * @created		2023-11-29
	 * @return		string
	 */
	static function Main() : string
	{
		return trim(`git remote show origin | grep 'HEAD branch' | cut -d' ' -f5 2>&1`);
	}

	/** Return current branch name.
	 *
	 * @created		2023-11-27
	 * @return		string
	 */
	static function Current() : string
	{
		return trim(`git rev-parse --abbrev-ref HEAD 2>&1`);
	}

	/** Add branch repository.
	 *
	 * @created    2023-02-13
	 * @param      string      $name
	 * @param      string      $url
	 * @return     string
	 */
	static function Add(string $name, string $url)
	{
		return `git branch add {$name} $url`;
	}

	/** Delete branch repository.
	 *
	 * @created    2023-02-13
	 * @param      string      $name
	 * @param      string      $url
	 * @return     string
	 */
	static function Delete(string $name)
	{
		return `git branch rm {$name}`;
	}

	/** Rename branch repository.
	 *
	 * @created    2023-02-13
	 * @param      string      $from
	 * @param      string      $to
	 * @return     string
	 */
	static function Rename(string $from, string $to)
	{
		return `git branch rename {$from} {$to}`;
	}

	/** Return branch repository name list.
	 *
	 * @created    2023-02-13
	 * @return     string
	 */
	static function List()
	{
		//	...
		$return = [];
		//	...
		foreach( explode("\n", `git branch`) as $branch ){
			//	...
			if(empty($branch)){
				continue;
			}
			//	...
			$return[] = substr($branch, 2);
		}
		//	...
		return $return;
	}

	/** Check if exists branch name.
	 *
	 * @created    2023-02-13
	 * @param      string      $name
	 * @param      string      $url
	 * @return     string
	 */
	static function isExists(string $name)
	{
		//	...
		$list = self::List();

		//	...
		return (array_search($name, $list) !== false) ? true: false;
	}
}
