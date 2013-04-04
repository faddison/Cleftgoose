<?php
/**
 * @version    $Id$
 * @package    JSN_Framework
 * @author     JoomlaShine Team <support@joomlashine.com>
 * @copyright  Copyright (C) 2012 JoomlaShine.com. All Rights Reserved.
 * @license    GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 *
 * Websites: http://www.joomlashine.com
 * Technical Support:  Feedback - http://www.joomlashine.com/contact-us/get-support.html
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

/**
 * System plugin for initializing JSN Framework.
 *
 * @package  JSN_Framework
 * @since    1.0.0
 */
class PlgSystemJSNFramework extends JPlugin
{
	/**
	 * @var JApplication
	 */
	private $_app = null;

	/**
	 * Register JSN Framework initialization.
	 *
	 * @return  void
	 */
	public function onAfterInitialise()
	{
		// Initialize JSN Framework
		require_once dirname(__FILE__) . '/libraries/loader.php';
		require_once dirname(__FILE__) . '/jsnframework.defines.php';

		// Get application object
		$this->_app = JFactory::getApplication();

		// Get requested component, view and task
		$this->option	= $this->_app->input->getCmd('option');
		$this->view		= $this->_app->input->getCmd('view');
		$this->task		= $this->_app->input->getCmd('task');

		// Redirect to update page if necessary
		if ($this->option == 'com_installer' AND $this->view == 'update' AND $this->task == 'update.update' AND count($cid = (array) $this->_app->input->getVar('cid', array())))
		{
			// Check if extension to updated is JoomlaShine product
			$db	= JFactory::getDbo();
			$q	= $db->getQuery(true);

			$q->select('element');
			$q->from('#__updates');
			$q->where('update_id = ' . (int) $cid[0]);

			$db->setQuery($q);
			$ext = $db->loadResult();

			if (in_array($ext, JSNVersion::$products))
			{
				return $this->_app->redirect('index.php?option=' . $ext . '&view=update');
			}
		}

		// Get active language
		$lang = JFactory::getLanguage();

		// Check if language file exists for active language
		if ( ! file_exists(JPATH_ROOT . '/administrator/language/' . $lang->get('tag') . '/' . $lang->get('tag') . '.plg_system_jsnframework.ini'))
		{
			// If requested component has the language file, install then load it
			if (file_exists(JPATH_ROOT . '/administrator/components/' . $this->option . '/language/admin/' . $lang->get('tag') . '/' . $lang->get('tag') . '.plg_system_jsnframework.ini'))
			{
				JSNLanguageHelper::install((array) $lang->get('tag'), false, true);
				$lang->load('plg_system_jsnframework', JPATH_ADMINISTRATOR, null, true);
			}
			// Otherwise, try to load language file from plugin directory
			else
			{
				$lang->load('plg_system_jsnframework', JSN_PATH_FRAMEWORK, null, true);
			}
		}
		else
		{
			$lang->load('plg_system_jsnframework', JPATH_ADMINISTRATOR, null, true);
		}

		// Disable notice and warning by default for our products.
		// The reason for doing this is if any notice or warning appeared then handling JSON string will fail in our code.
		if (function_exists('error_reporting') AND in_array($this->option, JSNVersion::$products))
		{
			error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_STRICT);
		}

		// Set event handlers to detect and update dependency installation / removal
		if ($this->option == 'com_installer')
		{
			$this->_app->registerEvent('onExtensionAfterInstall',		'jsnExtFwUpdateDependencyAfterInstallExtension');
			$this->_app->registerEvent('onExtensionBeforeUninstall',	'jsnExtFwUpdateDependencyBeforeUninstallExtension');
		}
	}

	/**
	 * Before render needs using this function to make format of HTML of modules
	 *
	 * @return  Changed HTML format
	 */
	public function onBeforeRender()
	{
		if ( ! JSNVersion::isJoomlaCompatible(JSN_FRAMEWORK_REQUIRED_JOOMLA_VER))
		{
			return;
		}

		if ($this->_app->isAdmin())
		{
			// Ask user to review JoomlaShine product on JED
			$this->askForReview();
		}
		elseif ($this->_app->input->getCmd('poweradmin', 0) == 1)
		{
			$jsnHelper = JSNPositionsModel::_getInstance();
			$jsnHelper->renderEmptyModule();
		}
	}

	/**
	 * Do some output manipulation.
	 *
	 * Auto-inject <b>jsn-master tmpl-nameOfDefaultTemplate</b> into the class
	 * attribute of <b>&lt;body&gt;</b> tag if not already exists. This
	 * automation only affects backend page.
	 *
	 * @return  void
	 */
	public function onAfterRender()
	{
		// Get the rendered HTML code
		$html = JResponse::getBody();

		if ($this->_app->input->getVar('poweradmin'))
		{
			preg_match_all('#<a[^\>]*href\s*=\s*[\'"]([^"]*[^"]+)[\'"]#i', $html, $ms, PREG_SET_ORDER);
			if (count($ms))
			{
				foreach ($ms as $m)
				{
					$html	=  str_replace($m[0], str_replace($m[1], 'javascript:void(0)', $m[0]), $html) ;
				}
			}
		}

		// Continue only if this is admin page
		if ($this->_app->isAdmin())
		{
			// Fix asset links for Joomla 2.5
			if (JSNVersion::isJoomlaCompatible('2.5') AND strpos($html, JSN_URL_ASSETS) !== false)
			{
				// Get asset link
				if (preg_match_all('#<(link|script)([^\>]*)(href|src)="([^"]*' . JSN_URL_ASSETS . '[^"]+)"#i', $html, $matches, PREG_SET_ORDER))
				{
					foreach ($matches AS $match)
					{
						// Do replace
						$html = str_replace(
							$match[0],
							'<' . $match[1] . $match[2] . $match[3] . '="' . dirname(dirname($match[4])) . '/' . str_replace('.', '-', basename(dirname($match[4]))) . '/' . basename($match[4]) . '"',
							$html
						);
					}
				}
			}

			// Remove our extensions from the Joomla 3.0's global config page
			if ($this->option == 'com_config' AND JSNVersion::isJoomlaCompatible('3.'))
			{
				$html = preg_replace(
					'#[\s\t]*<li[^\r\n]+index.php\?option=com_config&view=component&component=(' . implode('|', JSNVersion::$products) . ')[^\r\n]+/li>[\r\n]#',
					'',
					$html
				);
			}

			// Alter body tag
			if (preg_match('/<body[^>]*>/i', $html, $match) AND strpos($match[0], 'jsn-master tmpl-' . $this->_app->getTemplate()) === false)
			{
				if (strpos($match[0], 'class=') === false)
				{
					$match[1] = substr($match[0], 0, -1) . ' class=" jsn-master tmpl-' . $this->_app->getTemplate() . ' ">';
				}
				else
				{
					$match[1] = str_replace('class="', 'class=" jsn-master tmpl-' . $this->_app->getTemplate() . ' ', $match[0]);
				}

				$html = str_replace($match[0], $match[1], $html);
			}
		}

		// Attach JS declaration
		$html = preg_replace('/<\/head>/i', JSNHtmlAsset::buildHeader() . '</head>', $html);

		// Set new response body
		JResponse::setBody($html);

		// Execute update checker
		$this->checkUpdate();
	}

	/**
	 * Proceed positions rendering
	 *
	 * Remove default tp=1 layout, replace by jsn style to
	 * show page positions
	 *
	 * @return  void
	 */
	public function onAfterDispatch()
	{
		if ( ! JSNVersion::isJoomlaCompatible(JSN_FRAMEWORK_REQUIRED_JOOMLA_VER))
		{
			return;
		}

		// Keep this for joomla 2.5. Will be deprecated.
		if (JSNVersion::isJoomlaCompatible('2.5'))
		{
			if ($this->_app->isAdmin()
				AND $this->_app->input->getVar('format', '') != 'raw'
				AND $this->_app->input->getVar('option', '') == 'com_poweradmin'
				AND $this->_app->input->getVar('view') != 'update'
				AND $this->_app->input->getVar('view') != 'changeposition')
			{
				$version = PoweradminHelper::getVersion();

				if (version_compare($version, '1.1.3', '>='))
				{
					$JSNMedia = JSNFactory::getMedia();
					$JSNMedia->addMedia();
				}
			}
		}

		if ($this->_app->input->getCmd('poweradmin', 0) == 1)
		{
			$jsnHelper = JSNPositionsModel::_getInstance();
			$jsnHelper->renderEmptyComponent();
			$jsnHelper->renderEmptyModule();
		}
	}

	/**
	 * Ask user to review JoomlaShine product on JED.
	 *
	 * @return  void
	 */
	private function askForReview()
	{
		// Continue only if this is admin page of JoomlaShine product
		if ($this->_app->isAdmin() AND in_array($this->option, JSNVersion::$products))
		{
			// Get product options
			$config = JSNConfigHelper::get($this->option);

			if ($config->get('review_popup', 1))
			{
				// Get time difference
				$time = time();
				$last = $config->get('last_ask_for_review', 0);

				if ($last == 0)
				{
					$last = filemtime(JPATH_ROOT . "/administrator/components/{$this->option}/" . substr($this->option, 4) . '.xml');
				}

				// Check if it's time to ask for review
				if ($time - $last >= REVIEW_POPUP_PERIOD)
				{
					// Load script to popup a modal ask user for review
					JSNHtmlAsset::loadScript(
						'jsn/askforreview',
						array(
							'url'		=> JUri::root() . "plugins/system/jsnframework/libraries/joomlashine/choosers/review/index.php?component={$this->option}",
							'language'	=> JSNUtilsLanguage::getTranslated(array('JSN_EXTFW_CHOOSERS_REVIEW_ON_JED'))
						)
					);

					// Get config model
					$model = new JSNConfigModel;

					// Store time of last popup
					$form = $model->getForm(array(), true, JPATH_ROOT . '/administrator/components/' . $this->option . '/config.xml');
					$data = array('last_ask_for_review' => $time);

					try
					{
						// Save new configuration
						$model->save($form, $data);
					}
					catch (Exception $e)
					{
						// Do nothing as this is a background process
					}
				}
			}
		}
	}

	/**
	 * Check if there is new update for installed JoomlaShine product.
	 *
	 * @return  void
	 */
	private function checkUpdate()
	{
		// Get Joomla version
		$joomlaVersion = new JVersion;

		// Get list of JoomlaShine product need to be checked for new update
		$checks = $products = array();

		foreach (JSNVersion::$products AS $product)
		{
			// Instantiate JSN Config model
			$this->_app->input->set('option', $product);
			$model = new JSNConfigModel;

			// Only continue if product is installed
			if (is_readable(JPATH_ROOT . '/administrator/components/' . $product . '/config.xml'))
			{
				// Get product configuration
				$cfg = JSNConfigHelper::get($product);

				if ( ! $cfg->get('config-table-not-exists') AND ! $cfg->get('live_update_checking', 0))
				{
					if (time() - $cfg->get('live_update_last_check', 0) >= ((int) $cfg->get('live_update_check_interval', 6) * 60 * 60))
					{
						// Store necessary data to config table
						$form = $model->getForm(array(), true, JPATH_ROOT . '/administrator/components/' . $product . '/config.xml');
						$data = array('live_update_checking' => 1);

						try
						{
							$model->save($form, $data);

							// Store product for checking new update later
							file_exists($define = JPATH_ROOT . '/administrator/components/' . $product . '/' . substr($product, 4) . '.defines.php')
								OR file_exists($define = JPATH_ROOT . '/administrator/components/' . $product . '/defines.' . substr($product, 4) . '.php')
								OR file_exists($define = JPATH_ROOT . '/administrator/components/' . $product . '/defines.php')
								OR $define = null;

							if ( ! empty($define))
							{
								if ($product == 'com_imageshow' AND ! class_exists('JSNISFactory'))
								{
									// JSN ImageShow specific fix
									if (file_exists(JPATH_ROOT . '/administrator/components/com_imageshow/classes/jsn_is_factory.php'))
									{
										require_once JPATH_ROOT . '/administrator/components/com_imageshow/classes/jsn_is_factory.php';
									}
									else
									{
										// Do nothing as this is a background process
										continue;
									}
								}

								require_once $define;
								$products[JSNUtilsText::getConstant('IDENTIFIED_NAME')] = $product;
								$checks[JSNUtilsText::getConstant('IDENTIFIED_NAME')] = JSNUtilsText::getConstant('VERSION', $product);
							}
						}
						catch (Exception $e)
						{
							// Do nothing as this is a background process
							continue;
						}
					}
				}
			}
		}

		// Check update for installed JoomlaShine product
		if (count($products))
		{
			if ($checks = JSNUpdateHelper::check($checks, $joomlaVersion->RELEASE))
			{
				// Get database object
				$db = JFactory::getDbo();

				foreach ($checks AS $id => & $result)
				{
					// Instantiate JSN Config model
					$this->_app->input->set('option', $products[$id]);
					$model = new JSNConfigModel;

					// Get product configuration
					$cfg = JSNConfigHelper::get($products[$id]);

					// Decode last update info
					$last = is_string($last = $cfg->get('live_update_last_notified', '{"version":"0.0.0"}')) ? json_decode($last) : $last;

					if ($result AND version_compare($result->version, $last->version, '>'))
					{
						// Get product details
						if ($info = JSNUtilsXml::loadManifestCache($products[$id]))
						{
							$name = 'JSN ' . preg_replace('/^JSN\s*/i', '', $info->name) . (($edition = JSNUtilsText::getConstant('EDITION', $products[$id])) ? ' ' . $edition : '');

							// Get extension ID
							$q = $db->getQuery(true);

							$q->select('extension_id');
							$q->from('#__extensions');
							$q->where("element = '" . $products[$id] . "'");
							$q->where("type = 'component'", 'AND');

							$db->setQuery($q);

							$eid = $db->loadResult();

							// Check if update is stored before
							$q = $db->getQuery(true);

							$q->select('version');
							$q->from('#__updates');
							$q->where("extension_id = '{$eid}'");

							$db->setQuery($q);

							// Store update info to Joomla updates table
							if ($current = $db->loadResult())
							{
								$q = $db->getQuery(true);

								$q->update('#__updates');
								$q->set("version = '{$result->version}'");
								$q->where("extension_id = '{$eid}'");
								$q->where("version = '{$current}'", 'AND');
							}
							else
							{
								$q = $db->getQuery(true);

								$q->insert('#__updates');
								$q->columns('extension_id, name, element, type, version');
								$q->values("{$eid}, '{$name}', '{$products[$id]}', 'component', '{$result->version}'");
							}

							$db->setQuery($q);
							$db->execute();

							if ($cfg->get('live_update_notification', 0))
							{
								// Get super administrator account to notify about update available
								if ( ! isset($admins))
								{
									// Get Joomla config
									$joomlaConfig = JFactory::getConfig();

									// Build query to get super administrator account
									$q	= $db->getQuery(true);

									$q->select('u.id, u.email');
									$q->from('#__user_usergroup_map AS g');
									$q->join('INNER', '#__users AS u ON g.user_id = u.id');
									$q->where('g.group_id = 8 AND u.sendEmail = 1');

									$db->setQuery($q);

									try
									{
										if ( ! ($admins = $db->loadObjectList()))
										{
											return;
										}
									}
									catch (Exception $e)
									{
										return;
									}
								}

								// Generate replacement
								$substitutions = array(
									'__SITE_NAME__'	=> $joomlaConfig->get('sitename'),
									'__SITE_URL__'	=> JUri::root(),
									'__PRODUCT__'	=> $name,
									'__CURRENT__'	=> JSNUtilsText::getConstant('VERSION', $products[$id]),
									'__NEW__'		=> $result->version,
									'__LINK__'		=> JUri::root() . 'administrator/index.php?option=' . $products[$id] . '&view=update'
								);

								// Initialize email subject and message
								$subject = JText::_('JSN_EXTFW_UPDATE_NOTIFICATION_SUBJECT');
								$message = JText::_('JSN_EXTFW_UPDATE_NOTIFICATION_MESSAGE');

								foreach ($substitutions AS $k => $v)
								{
									$subject = str_replace($k, $v, $subject);
									$message = str_replace($k, $v, $message);
								}

								foreach ($admins AS $admin)
								{
									// Initialize mailer then send update notification email
									try
									{
										$mailer = JFactory::getMailer();
										$mailer->setSender(array($joomlaConfig->get('mailfrom'), $joomlaConfig->get('fromname')));
										$mailer->addRecipient($admin->email);
										$mailer->setSubject($subject);
										$mailer->setBody($message);
										$mailer->Send();
									}
									catch (Exception $e)
									{
										return;
									}
								}
							}
						}
					}

					// Store last check update time
					$form = $model->getForm(array(), true, JPATH_ROOT . '/administrator/components/' . $products[$id] . '/config.xml');
					$data = array('live_update_checking' => '0', 'live_update_last_check' => time());

					if ($result)
					{
						// Store last version notified
						$data['live_update_last_notified'] = json_encode($result);
					}

					try
					{
						$model->save($form, $data);
					}
					catch (Exception $e)
					{
						// Do nothing as this is a background process
						continue;
					}
				}
			}
		}

		// Restore current option
		$this->_app->input->set('option', $this->option);
	}
}

/**
 * Update dependency after an extension is installed.
 *
 * @param   object  $installer   Joomla installer object.
 * @param   mixed   $identifier  Extension ID on installation success, boolean FALSE otherwise.
 *
 * @return  void
 */
function jsnExtFwUpdateDependencyAfterInstallExtension($installer, $identifier)
{
	if (is_integer($identifier))
	{
		// Get installed extension
		$ext = basename($installer->getPath('extension_administrator'));

		// Check if our product is installed
		if (in_array($ext, JSNVersion::$products))
		{
			// Build query to get dependency installation status
			$db	= JFactory::getDbo();
			$q	= $db->getQuery(true);

			$q->select('manifest_cache, custom_data');
			$q->from('#__extensions');
			$q->where("element = 'jsnframework'");
			$q->where("type = 'plugin'", 'AND');
			$q->where("folder = 'system'", 'AND');

			$db->setQuery($q);

			// Load dependency installation status
			$status = $db->loadObject();

			$ext = substr($ext, 4);
			$dep = ! empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

			// Update dependency list
			in_array($ext, $dep) OR $dep[] = $ext;
			$status->custom_data = array_unique($dep);

			// Build query to update dependency data
			$q = $db->getQuery(true);

			$q->update('#__extensions');
			$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

			// Backward compatible: keep data in this column for older product to recognize
			$manifestCache = json_decode($status->manifest_cache);
			$manifestCache->dependency = $status->custom_data;

			$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

			// Backward compatible: keep data in this column also for another old product to recognize
			$q->set("params = '" . json_encode((object) array_combine($status->custom_data, $status->custom_data)) . "'");

			$q->where("element = 'jsnframework'");
			$q->where("type = 'plugin'", 'AND');
			$q->where("folder = 'system'", 'AND');

			$db->setQuery($q);
			$db->execute();
		}
	}
}

/**
 * Update dependency before an extension is being removed.
 *
 * @param   integer  $identifier  Extension ID.
 *
 * @return  boolean
 */
function jsnExtFwUpdateDependencyBeforeUninstallExtension($identifier)
{
	// Get extension being removed
	$ext = JTable::getInstance('Extension');
	$ext->load($identifier);
	$ext = $ext->element;

	// Check if our product is being removed
	if (in_array($ext, JSNVersion::$products))
	{
		// Build query to get dependency installation status
		$db	= JFactory::getDbo();
		$q	= $db->getQuery(true);

		$q->select('manifest_cache, custom_data');
		$q->from('#__extensions');
		$q->where("element = 'jsnframework'");
		$q->where("type = 'plugin'", 'AND');
		$q->where("folder = 'system'", 'AND');

		$db->setQuery($q);

		// Load dependency installation status
		$status = $db->loadObject();

		$ext	= substr($ext, 4);
		$deps	= ! empty($status->custom_data) ? (array) json_decode($status->custom_data) : array();

		// Update dependency tracking
		$status->custom_data = array();

		foreach ($deps AS $dep)
		{
			// Backward compatible: ensure that product is not removed
			// if ($dep != $ext)
			if ($dep != $ext AND is_dir(JPATH_BASE . '/components/com_' . $dep))
			{
				$status->custom_data[] = $dep;
			}
		}

		// Build query to update dependency data
		$q = $db->getQuery(true);

		$q->update('#__extensions');
		$q->set("custom_data = '" . json_encode($status->custom_data) . "'");

		// Backward compatible: keep data in this column for older product to recognize
		$manifestCache = json_decode($status->manifest_cache);
		$manifestCache->dependency = $status->custom_data;

		$q->set("manifest_cache = '" . json_encode($manifestCache) . "'");

		// Backward compatible: keep data in this column also for another old product to recognize
		$q->set("params = '" . (count($status->custom_data) ? json_encode((object) array_combine($status->custom_data, $status->custom_data)) : '') . "'");

		$q->where("element = 'jsnframework'");
		$q->where("type = 'plugin'", 'AND');
		$q->where("folder = 'system'", 'AND');

		$db->setQuery($q);
		$db->execute();
	}

	// Always return TRUE so the extension can be removed
	return true;
}
