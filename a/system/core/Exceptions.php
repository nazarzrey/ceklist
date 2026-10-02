<?php
/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014 - 2018, British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * @package	CodeIgniter
 * @author	EllisLab Dev Team
 * @copyright	Copyright (c) 2008 - 2014, EllisLab, Inc. (https://ellislab.com/)
 * @copyright	Copyright (c) 2014 - 2018, British Columbia Institute of Technology (http://bcit.ca/)
 * @license	http://opensource.org/licenses/MIT	MIT License
 * @link	https://codeigniter.com
 * @since	Version 1.0.0
 * @filesource
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Exceptions Class
 *
 * @package		CodeIgniter
 * @subpackage	Libraries
 * @category	Exceptions
 * @author		EllisLab Dev Team
 * @link		https://codeigniter.com/user_guide/libraries/exceptions.html
 */
class CI_Exceptions {

	/**
	 * Nesting level of the output buffering mechanism
	 *
	 * @var	int
	 */
	public $ob_level;

	/**
	 * List of available error levels
	 *
	 * @var	array
	 */
	public $levels = array(
		E_ERROR			=>	'Error',
		E_WARNING		=>	'Warning',
		E_PARSE			=>	'Parsing Error',
		E_NOTICE		=>	'Notice',
		E_CORE_ERROR		=>	'Core Error',
		E_CORE_WARNING		=>	'Core Warning',
		E_COMPILE_ERROR		=>	'Compile Error',
		E_COMPILE_WARNING	=>	'Compile Warning',
		E_USER_ERROR		=>	'User Error',
		E_USER_WARNING		=>	'User Warning',
		E_USER_NOTICE		=>	'User Notice',
		E_STRICT		=>	'Runtime Notice'
	);

	/**
	 * Class constructor
	 *
	 * @return	void
	 */
	public function __construct()
	{
		$this->ob_level = ob_get_level();
		// Note: Do not log messages from this constructor.
	}

	// --------------------------------------------------------------------

	/**
	 * Exception Logger
	 *
	 * Logs PHP generated error messages
	 *
	 * @param	int	$severity	Log level
	 * @param	string	$message	Error message
	 * @param	string	$filepath	File path
	 * @param	int	$line		Line number
	 * @return	void
	 */
	public function log_exception($severity, $message, $filepath, $line)
	{
		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;
		log_message('error', 'Severity: '.$severity.' --> '.$message.' '.$filepath.' '.$line);
	}

	// --------------------------------------------------------------------

	/**
	 * Get debugging info untuk development environment
	 *
	 * @return	array
	 */
	private function _get_debug_info()
	{
		$debug_info = array();
		
		// 1. URI yang diminta
		$debug_info['Request URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : 'Tidak diketahui';
		$debug_info['Request Method'] = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'Tidak diketahui';
		$debug_info['Query String'] = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';
		
		// 2. Cek segmen URI (untuk routing)
		$uri_segments = '';
		if (function_exists('get_instance'))
		{
			$CI =& get_instance();
			if (isset($CI->uri) && method_exists($CI->uri, 'segment_array'))
			{
				$segments = $CI->uri->segment_array();
				$uri_segments = !empty($segments) ? implode(' / ', $segments) : 'Tidak ada segmen';
				$debug_info['URI Segments'] = $uri_segments;
			}
		}
		
		// 3. Cek controller yang dicoba
		if (function_exists('get_instance') && isset($CI->router))
		{
			$directory = $CI->router->directory;
			$class = $CI->router->class;
			$method = $CI->router->method;
			
			$debug_info['Controller Directory'] = !empty($directory) ? $directory : 'root';
			$debug_info['Controller Class'] = !empty($class) ? $class : 'Tidak ditemukan';
			$debug_info['Controller Method'] = !empty($method) ? $method : 'index (default)';
			
			// Cek file controller
			$controller_path = APPPATH.'controllers/'.$directory.$class.'.php';
			$debug_info['Controller File Path'] = $controller_path;
			$debug_info['Controller File Exists'] = file_exists($controller_path) ? 'Ya' : 'TIDAK';
			
			// Jika method spesifik, cek apakah ada di controller
			if (!empty($class) && file_exists($controller_path) && !empty($method))
			{
				// Include file untuk cek method (hati-hati, ini hanya untuk debug)
				$debug_info['Note'] = 'Method "'.$method.'" mungkin tidak ada di dalam class "'.$class.'"';
			}
		}
		
		// 4. Cek alternatif file yang mungkin (untuk case-sensitive issues)
		$controller_dir = APPPATH.'controllers/';
		$request_uri = ltrim(parse_url($debug_info['Request URI'], PHP_URL_PATH), '/');
		
		// Hilangkan index.php dari URI jika ada
		$request_uri = preg_replace('#^index\.php/?#', '', $request_uri);
		
		if (!empty($request_uri) && $request_uri != '/')
		{
			$uri_parts = explode('/', $request_uri);
			$possible_controller = ucfirst($uri_parts[0]) . '.php';
			$possible_controller_lower = strtolower($uri_parts[0]) . '.php';
			$possible_controller_upper = strtoupper($uri_parts[0]) . '.php';
			
			$debug_info['Mencoba Controller dari URI'] = $uri_parts[0];
			$debug_info[' - File: '.$possible_controller] = file_exists($controller_dir.$possible_controller) ? 'ADA' : 'TIDAK ADA';
			$debug_info[' - File: '.$possible_controller_lower] = file_exists($controller_dir.$possible_controller_lower) ? 'ADA' : 'TIDAK ADA';
			$debug_info[' - File: '.$possible_controller_upper] = file_exists($controller_dir.$possible_controller_upper) ? 'ADA' : 'TIDAK ADA';
		}
		
		return $debug_info;
	}

	// --------------------------------------------------------------------

	/**
	 * 404 Error Handler
	 *
	 * @uses	CI_Exceptions::show_error()
	 *
	 * @param	string	$page		Page URI
	 * @param 	bool	$log_error	Whether to log the error
	 * @return	void
	 */
	public function show_404($page = '', $log_error = TRUE)
	{
		if (is_cli())
		{
			$heading = 'Not Found';
			$message = 'The controller/method pair you requested was not found.';
			
			// Di CLI juga kasih debug info jika development
			if (defined('ENVIRONMENT') && ENVIRONMENT === 'development')
			{
				$debug_info = $this->_get_debug_info();
				$message .= "\n\n--- DEBUG INFO (Development Mode) ---\n";
				foreach ($debug_info as $key => $value)
				{
					$message .= $key . ': ' . $value . "\n";
				}
				$message .= "------------------------------------\n";
			}
		}
		else
		{
			$heading = '404 Page Not Found';
			$message = 'The page you requested was not found. please click here to <a href="login">login </a> admin';
			
			// Jika development mode, tambahkan debug info ke message
			if (defined('ENVIRONMENT') && ENVIRONMENT === 'development')
			{
				$debug_info = $this->_get_debug_info();
				
				// Buat HTML untuk debug info
				$debug_html = '<div style="background: #f0f0f0; border: 2px solid #cc0000; padding: 15px; margin-top: 20px; font-family: monospace; text-align: left;">';
				$debug_html .= '<h3 style="color: #cc0000; margin-top: 0;">🔍 DEBUG INFO (Development Mode)</h3>';
				$debug_html .= '<table style="border-collapse: collapse; width: 100%;">';
				
				foreach ($debug_info as $key => $value)
				{
					$color = (strpos($value, 'TIDAK') !== false || strpos($value, 'Tidak ditemukan') !== false) ? '#ff0000' : '#000000';
					$debug_html .= '<tr style="border-bottom: 1px solid #ddd;">';
					$debug_html .= '<td style="padding: 8px; font-weight: bold; width: 30%;">' . htmlspecialchars($key) . ':</td>';
					$debug_html .= '<td style="padding: 8px; color: ' . $color . ';">' . htmlspecialchars($value) . '</td>';
					$debug_html .= '</tr>';
				}
				
				$debug_html .= '</table>';
				$debug_html .= '<p style="margin-top: 10px; font-size: 12px; color: #666;">💡 Tips: Periksa apakah nama file controller sesuai (Case-sensitive) dan method tersedia.</p>';
				$debug_html .= '</div>';
				
				// Gabungkan dengan message asli
				$message .= $debug_html;
				
				// Juga catat ke error log dengan lebih detail
				log_message('error', '=== 404 DEBUG INFO ===');
				foreach ($debug_info as $key => $value)
				{
					log_message('error', $key . ': ' . $value);
				}
				log_message('error', '===================');
			}
		}

		// By default we log this, but allow a dev to skip it
		if ($log_error)
		{
			log_message('error', $heading.': '.$page);
		}

		echo $this->show_error($heading, $message, 'error_404', 404);
		exit(4); // EXIT_UNKNOWN_FILE
	}

	// --------------------------------------------------------------------

	/**
	 * General Error Page
	 *
	 * Takes an error message as input (either as a string or an array)
	 * and displays it using the specified template.
	 *
	 * @param	string		$heading	Page heading
	 * @param	string|string[]	$message	Error message
	 * @param	string		$template	Template name
	 * @param 	int		$status_code	(default: 500)
	 *
	 * @return	string	Error page output
	 */
	public function show_error($heading, $message, $template = 'error_general', $status_code = 500)
	{
		$templates_path = config_item('error_views_path');
		if (empty($templates_path))
		{
			$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
		}

		if (is_cli())
		{
			$message = "\t".(is_array($message) ? implode("\n\t", $message) : $message);
			$template = 'cli'.DIRECTORY_SEPARATOR.$template;
		}
		else
		{
			set_status_header($status_code);
			$message = '<p>'.(is_array($message) ? implode('</p><p>', $message) : $message).'</p>';
			$template = 'html'.DIRECTORY_SEPARATOR.$template;
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include($templates_path.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}

	// --------------------------------------------------------------------

	public function show_exception($exception)
	{
		$templates_path = config_item('error_views_path');
		if (empty($templates_path))
		{
			$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
		}

		$message = $exception->getMessage();
		if (empty($message))
		{
			$message = '(null)';
		}

		if (is_cli())
		{
			$templates_path .= 'cli'.DIRECTORY_SEPARATOR;
		}
		else
		{
			$templates_path .= 'html'.DIRECTORY_SEPARATOR;
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}

		ob_start();
		include($templates_path.'error_exception.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

	// --------------------------------------------------------------------

	/**
	 * Native PHP error handler
	 *
	 * @param	int	$severity	Error level
	 * @param	string	$message	Error message
	 * @param	string	$filepath	File path
	 * @param	int	$line		Line number
	 * @return	void
	 */
	public function show_php_error($severity, $message, $filepath, $line)
	{
		$templates_path = config_item('error_views_path');
		if (empty($templates_path))
		{
			$templates_path = VIEWPATH.'errors'.DIRECTORY_SEPARATOR;
		}

		$severity = isset($this->levels[$severity]) ? $this->levels[$severity] : $severity;

		// For safety reasons we don't show the full file path in non-CLI requests
		if ( ! is_cli())
		{
			$filepath = str_replace('\\', '/', $filepath);
			if (FALSE !== strpos($filepath, '/'))
			{
				$x = explode('/', $filepath);
				$filepath = $x[count($x)-2].'/'.end($x);
			}

			// Jika development mode, tampilkan full path
			if (defined('ENVIRONMENT') && ENVIRONMENT === 'development')
			{
				$filepath = $filepath . ' (Full path available in development mode)';
			}

			$template = 'html'.DIRECTORY_SEPARATOR.'error_php';
		}
		else
		{
			$template = 'cli'.DIRECTORY_SEPARATOR.'error_php';
		}

		if (ob_get_level() > $this->ob_level + 1)
		{
			ob_end_flush();
		}
		ob_start();
		include($templates_path.$template.'.php');
		$buffer = ob_get_contents();
		ob_end_clean();
		echo $buffer;
	}

}