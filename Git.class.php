<?php
/** op-unit-git:/Git.class.php
 *
 * @created    2023-01-30
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
namespace OP\UNIT;

/** use
 *
 */
use Exception;
use OP\IF_UNIT;
use OP\OP_CORE;
use OP\OP_CI;

/** Git
 *
 * @created    2023-01-30
 * @version    1.0
 * @package    op-unit-git
 * @author     Tomoaki Nagahara <tomoaki.nagahara@gmail.com>
 * @copyright  Tomoaki Nagahara All right reserved.
 */
class Git implements IF_UNIT
{
	/** use
	 *
	 */
	use OP_CORE, OP_CI;

	/** Get git path.
	 *
	 */
	static function Path():string
	{
		static $_path;
		if(!$_path ){
			$_path = include(__DIR__.'/include/search_path.php');
		}
		return $_path;
	}

	/** Get submodule config.
	 *
	 * @created    2023-01-02
	 * @moved      2023-01-30  op-cd:/Git.class.php
	 * @param      bool        $current
	 * @throws     Exception
	 * @return     array
	 */
	static function SubmoduleConfig(string $file_name='.gitmodules') : array
	{
		//	...
		require_once(__DIR__.'/function/SubmoduleConfig.php');

		//	...
		$file_path = OP()->MetaRoot('git') . $file_name;

		//	...
		return GIT\SubmoduleConfig($file_path);
	}

	/** Working tree is clean?
	 *
	 * @return bool
	 */
	static function Status():bool
	{
		//	...
		$result = `git status 2>&1`;

		//	...
		if(!$io = strpos(' '.$result, 'nothing to commit, working tree clean') ? true: false ){
			$io = strpos(' '.$result, 'no changes added to commit') ? true: false ;
		}

		//	...
		return $io;
	}

	/** Stash save
	 *
	 * @deprecated	2023-11-27
	 * @return bool
	 */
	static function Save():bool
	{
		//	...
		$result = `git stash save 2>&1`;
		//	...
		if(!$io = strpos(' '.$result, 'No local changes to save') ? true: false ){
			$io = strpos(' '.$result, 'Saved working directory and index state WIP') ? true: false ;
		}
		//	...
		return $io;
	}

	/** Stash pop
	 *
	 * <pre>
	 * Git::Stash()->Pop();
	 * </pre>
	 *
	 * @deprecated	2023-11-27
	 * @return bool
	 */
	static function Pop():bool
	{
		//	...
		$result = `git stash pop 2>&1`;
		//	...
		if(!$io = strpos(' '.$result, 'No stash entries found.') ? true: false ){
			$io = strpos(' '.$result, 'Dropped refs/stash') ? true: false ;
		}
		//	...
		return $io;
	}

	/** Fetch repository.
	 *
	 * <pre>
	 * Git::Remote()->Fetch($remote='origin');
	 * </pre>
	 *
	 * @deprecated	2023-12-24
	 * @created    2023-02-13
	 * @param      string      $remote
	 * @return     string
	 */
	static function Fetch(string $remote=''):?string
	{
		return `git fetch {$remote}`;
	}

	/** Get branch name list
	 *
	 * <pre>
	 * Git::Branch()->List();
	 * </pre>
	 *
	 * @deprecated 2023-02-17
	 * @created    2023-02-05
	 * @return     array       $branches
	 */
	static function Branches():array
	{
		return self::Branch()->List();
	}

	/** Return Commit ID by branch name.
	 *
	 * @see https://prograshi.com/general/git/show-ref-and-rev-parse/
	 * @created    2023-02-05
	 * @param      string      $branch_name
	 * @return     string
	 */
	static function CommitID(string $branch_name) : string
	{
		//	...
		$branches = self::Branch()->List();
		//	...
		if( array_search($branch_name, $branches) === false ){
			throw new Exception("This branch name is not exists. ($branch_name)");
		}
		//	...
		return trim(`git rev-parse {$branch_name}`);
	}

	/** Switch to branch
	 *
	 * @created    2023-02-05
	 * @param      string      $branch_name
	 * @return     boolean
	 */
	static function Switch(string $branch_name):bool
	{
		//	...
		if( self::CurrentBranch() === $branch_name ){
			return true;
		}

		//	`switch` is 2.23.0 later.
		$command = true ? 'checkout':'switch';
		$result  = `git {$command} {$branch_name} 2>&1`;

		//	...
		if( 0 !== strpos($result, "Switched to branch '{$branch_name}'") ){
			D('Git switch was failed.');
			echo($result);
			return false;
		}

		//	...
		return true;
	}

	/** Rebase
	 *
	 * @created    2023-02-05
	 * @param      string      $remote_name
	 * @param      string      $branch_name
	 * @return     boolean|string
	 */
	static function Rebase(string $remote_name, string $branch_name)
	{
		/*
		//	...
		if(!self::Switch($branch_name) ){
			return false;
		}
		*/

		//	...
		/*
		$commit_id  = self::CommitID($branch_name);
		*/
		$commit_id  = `git rev-parse {$remote_name}/{$branch_name}`;
		$current_id = self::CurrentCommitID();

		//	...
		if( $commit_id === $current_id ){
			return true;
		}

		//	...
		$result = `git rebase {$remote_name}/{$branch_name} 2>&1`;

		//	...
		return $result;
	}

	/** Push of branch
	 *
	 * @created    2023-02-05
	 * @param      string      $remote_name
	 * @param      string      $branch_name
	 * @param      boolean     $force
	 * @return     string
	 */
	static function Push(string $remote_name, string $branch_name, bool $force=false):string
	{
		//	Already pushed?
		if( trim(`git rev-parse {$branch_name}`) === trim(`git rev-parse {$remote_name}/{$branch_name}`) ){
			return '';
		}

		//	...
		$force = $force ? '-f': '';

		//	...
		return trim(`git push {$remote_name} {$branch_name} {$force} 2>&1`);
	}

	/** Get current branch name.
	 *
	 * <pre>
	 * $current_branch_name = OP()->Unit('git')->Branch()->Current();
	 * </pre>
	 *
	 * @deprecated 2023-11-27
	 * @created    2023-01-06
	 * @return     string
	 */
	static function CurrentBranch():string
	{
		/*
		return trim(`git rev-parse --abbrev-ref HEAD 2>&1`);
		*/
		return self::Branch()->Current();
	}

	/** Get current commit ID.
	 *
	 * @created    2023-01-06
	 * @return     string
	 */
	static function CurrentCommitID():string
	{
		return trim(`git show --format='%H' --no-patch 2>&1`);
	}

	/** Return GitRemote instance.
	 *
	 * @created    2023-02-13
	 * @return    \OP\UNIT\GIT\GitRemote
	 */
	static function Remote():\OP\UNIT\GIT\GitRemote
	{
		//	...
		require_once(__DIR__.'/GitRemote.class.php');

		//	...
		static $_remote;
		if(!$_remote ){
			$_remote = new GIT\GitRemote();
		}

		//	...
		return $_remote;
	}

	/** Return GitBranch instance.
	 *
	 * @created    2023-02-13
	 * @return    \OP\UNIT\GIT\GitBranch
	 */
	static function Branch():\OP\UNIT\GIT\GitBranch
	{
		//	...
		require_once(__DIR__.'/GitBranch.class.php');

		//	...
		static $_branch;
		if(!$_branch ){
			$_branch = new GIT\GitBranch();
		}

		//	...
		return $_branch;
	}

    /** Return GitStash instance.
     *
     * @created     2022-11-12
     * @return     \OP\UNIT\GIT\GitStash
     */
    static function Stash() : \OP\UNIT\GIT\GitStash
    {
        //	...
        require_once(__DIR__.'/GitStash.class.php');

        //	...
        static $_stash;
        if(!$_stash ){
            $_stash = new GIT\GitStash();
        }

        //	...
        return $_stash;
    }

    /** Get current git cli version.
     *
     * @created     2023-07-13
     * @return      string      $version
     */
    static function Version():string
    {
        static $_version;
        if(!$_version ){
            $_version = `git --version`;
            /* @var $match array */
            if( preg_match('|(\d+\.\d+\.\d+)|', $_version, $match) ){
                $_version = $match[1];
            }
        }
        return $_version;
    }
}
