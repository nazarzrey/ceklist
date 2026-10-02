<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('tracelog')) {
    function tracelog($lognya, $page = '') {
        $CI =& get_instance();
        $CI->load->model('M_tracelog');
        return $CI->M_tracelog->log($lognya, $page);
    }
}
