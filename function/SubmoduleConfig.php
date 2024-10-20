<?php
/** op-unit-git:/function/SubmoduleConfig.php
 *
 * @created    2023-02-14
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
use Exception;

/** Get submodule config.
 *
 * @created    2023-01-02
 * @moved      2023-01-30  op-cd:/Git.class.php
 * @param      bool        $current
 * @throws     Exception
 * @return     array
 */
function SubmoduleConfig(string $file_path='.gitmodules') : array
{
	//	Get submodule settings.
	if(!file_exists($file_path) ){
		throw new Exception("This file does not exist. ($file_path)");
	}

	//	Get submodule settings from file.
	if(!$file = file_get_contents($file_path) ){
		throw new Exception("Could not read this file. ($file_path)");
	}

	//	Parse submodule settings.
	$file   = trim($file);
	$source = explode("\n", $file);

	//	Parse the submodule settings.
	$configs = [];
	while( $line = array_shift($source) ){
        //  ...
		if(!$line = trim($line) ){
			continue;
		}
		//	...
        if( $line[0] === '[' ){
            $name = substr($line, 12, -2);
            $name = str_replace('/', '-', $name);
        }else{
            list($key, $var) = explode("=", $line);
            $configs[$name][ trim($key) ] = trim($var);
        }
	}

	//	...
	return $configs;
}
