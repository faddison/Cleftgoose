<?php
/**
 * @version     $Id$
 * @package     JSNExtension
 * @subpackage  TPLFramework
 * @author      JoomlaShine Team <support@joomlashine.com>
 * @copyright   Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * CSS Compression engine
 *
 * @package     TPLFramework
 * @subpackage  Plugin
 * @since       1.0.0
 */
abstract class JSNTplCompressCss
{
	/**
	 * Method to parse all link to css files from the html markup
	 * and compress it
	 *
	 * @param   string  $htmlMarkup  HTML Content to response to browser
	 * @return  void
	 */
	public static function compress ($htmlMarkup)
	{
		$groupIndex	= 0;
		$groupType	= 'screen';
		$groupFiles	= array();
		$compress	= array();

		// Sometime, stylesheet file need to be stored in the original file name
		$document = JFactory::getDocument();
		$leaveAlone = preg_split('/[\r\n]+/', $document->params->get('compressionExclude'));

		// We already know that the file galleria.classic.css must be excluded
		$leaveAlone[] = 'galleria.classic.css';

		// Goto each link tag to parse attributes and add parsed file
		// to compress list
		foreach (explode('>', $htmlMarkup[0]) as $line)
		{
			$attributes = JSNTplCompressHelper::parseAttributes($line);

			// Set default media attribute
			$attributes['media'] = !isset($attributes['media']) ? '' : strtolower($attributes['media']);

			// Skip if not have attibute href
			if (!isset($attributes['href']))
				continue;

			// Add to result list if is external file
			if (!JURI::isInternal($attributes['href'])) {
				$compress[] = array(
					'href' => $attributes['href'],
					'media' => $attributes['media']
				);

				continue;
			}

			// Set a special media attribute
			$stylesheetName = basename(
				($pos = strpos($attributes['href'], '?')) !== false ? substr($attributes['href'], 0, $pos) : $attributes['href']
			);

			if (in_array($stylesheetName, $leaveAlone))
			{
				$attributes['media'] .= '|reserve|' . $stylesheetName;
			}

			// Create new compression group when curent media
			// attribute different with group type
			if ($attributes['media'] != $groupType)
			{
				// Add collected files to compress list
				if (isset($groupFiles[$groupIndex]) && !empty($groupFiles[$groupIndex]))
					$compress[] = array(
						'files' => $groupFiles[$groupIndex],
						'media' => $groupType
					);

				// Increase index number of the group
				$groupIndex++;
				$groupType = $attributes['media'];
			}

			// Initial group
			if (!isset($groupFiles[$groupIndex]))
				$groupFiles[$groupIndex] = array();

			$href = $attributes['href'];
			$queryStringIndex = strpos($href, '?');

			if ($queryStringIndex !== false) {
				$href = substr($href, 0, $queryStringIndex);
			}

			// Add file to the group
			$groupFiles[$groupIndex][] = $href;
		}

		// Add collected files to result list
		if (isset($groupFiles[$groupIndex]) && !empty($groupFiles[$groupIndex])) {
			$compress[] = array(
				'files' => $groupFiles[$groupIndex],
				'media' => $groupType
			);
		}

		// Initial compress result
		$compressResult = array();

		// Loop to each compress element to compress file
		foreach ($compress as $group)
		{
			// Ignore compress when group is a external file
			if (isset($group['href']))
			{
				$link = "<link rel=\"stylesheet\" href=\"{$group['href']}\" ";
				if (isset($group['media']) && !empty($group['media']))
					$link.= "media=\"{$group['media']}\" ";
				$link.= "/>";
				$compressResult[] = $link;
				continue;
			}

			// Template information
			$application = JFactory::getApplication();
			$templateName = $application->getTemplate();

			// Generate compressed file name
			$compressPath = JPATH_ROOT . '/tmp/' . $templateName . '/';
			$lastModified = 0;

			// Check if reserving stylesheet file name is required
			if (isset($group['media']) AND preg_match('/\|reserve\|(.+)$/', $group['media'], $m))
			{
				$compressFile = $m[1];
			}
			else
			{
				$compressFile = md5(implode('', $group['files'])) . '.css';
			}

			// Create temporary file if not exists
			if (!is_dir($compressPath))
				mkdir($compressPath);

			// Check last modified time for each file in the group
			foreach ($group['files'] as $file)
			{
				$path = JSNTplCompressHelper::getFilePath($file);
				$lastModified = (is_file($path) && filemtime($path) > $lastModified) ? filemtime($path) : $lastModified;
			}

			// Compress group when expired
			if (!is_file($compressPath . $compressFile) || filemtime($compressPath . $compressFile) < $lastModified)
			{
				// Open cache file in write mode
				$fileHandle = fopen($compressPath . $compressFile, 'w+');
				$remoteFiles = array();

				// Go to each file for read content of the file
				// and write it to the cache file
				foreach ($group['files'] as $file)
				{
					$filePath = JSNTplCompressHelper::getFilePath($file);

					// Skip when cannot access to file
					if (!is_file($filePath) || !is_readable($filePath))
						continue;

					self::_loadFileInto($fileHandle, $filePath, $remoteFiles);
				}

				// Close the file
				fclose($fileHandle);

				if (!empty($remoteFiles))
					foreach ($remoteFiles as $file)
						self::_prepend("@import url({$file});", $compressPath . $compressFile);
			}

			// Add compressed file to the compress result list
			$compressUrl = JURI::root(true) . '/tmp/' . $templateName . '/' . $compressFile;
			$link = "<link rel=\"stylesheet\" href=\"{$compressUrl}\" ";
			if (isset($group['media']) && !empty($group['media']))
				$link.= 'media="' . preg_replace('/\|reserve\|(.+)$/', '', $group['media']) . '" ';
			$link.= "/>";
			$compressResult[] = $link;
		}

		return implode("\r\n", $compressResult);
	}

	/**
	 * Load content from a file and append into existing opened file
	 *
	 * @param   resource  $fileHandle  Resource to handle opended pointer
	 * @param   string    $sourcePath  Path to file
	 *
	 * @return  void
	 */
	private static function _loadFileInto ($fileHandle, $sourcePath, &$remoteFilesImport)
	{
		// Read source file
		$source = file_get_contents($sourcePath);

		if (preg_match_all('/(@import\s+|[a-z0-9\-]+\s*:\s*)([^;]*)url\s*\(([^\)]+)\)/i', $source, $matches, PREG_SET_ORDER))
		{
			foreach ($matches AS $match)
			{
				$fileUrl = JSNTplCompressHelper::getRelativeFilePath(dirname($sourcePath), trim($match[3], '"\''));

				if (trim($match[1]) != '@import')
				{
					$fileUrl = ltrim(str_replace('\\', '/', $fileUrl), '/');

					if (strpos($match[3], '://') === false && strpos($match[3], '//') !== 0)
					{
						$source = str_replace($match[0], $match[1] . $match[2] . 'url(/' . $fileUrl . ')', $source);
					}
				}
				elseif (strpos($match[3], 'http://') === false)
				{
					$filePath = getenv('DOCUMENT_ROOT') . $fileUrl;
					self::_loadFileInto($fileHandle, $filePath, $remoteFilesImport);

					// Remove @import file inclusion for local file
					$source = str_replace($match[0], '', $source);
				}
				else
				{
					// Store @import file inclusion for remote file
					$remoteFilesImport[] = $match[3];
				}
			}
		}

		// Write processed content to cache file
		fwrite($fileHandle, preg_replace('/[\t\r\n]/', '', $source));
	}

	/**
	 * Method to write content to top of file
	 *
	 * @param   string    $string    Content will be appended to file
	 * @param   resource  $filename  File to prepend
	 * @return  void
	 */
	private static function _prepend ($string, $filename)
	{
		$context = stream_context_create();
		$fp = fopen($filename, 'r', 1, $context);
		$tmpname = dirname($filename) . '/' . md5($string) . '.tmp';

		file_put_contents($tmpname, $string);
		file_put_contents($tmpname, $fp, FILE_APPEND);

		fclose($fp);
		unlink($filename);
		rename($tmpname, $filename);
	}
}
